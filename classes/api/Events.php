<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 15.03.2019
 * Time: 13:00
 */

namespace plugins\lootly\classes\api;


use \Exception;
use plugins\lootly\classes\base\Base;
use WC_Customer;

class Events extends Base
{
    /**
     * @var Events
     */
    private static $_instance;

    /**
     * @return Events
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public function init()
    {
        add_action('woocommerce_order_status_changed', array($this, 'update_lootly_orders'), 10, 3);
        add_action('woocommerce_after_order_object_save', array($this, 'update_lootly_order_object_save'), 10, 3);
        add_action('woocommerce_add_to_cart', array($this, 'lootly_auto_apply_coupon'), 50, 7);

        add_action('user_register', array($this, 'customers_create'), 10, 1);
        add_action('woocommerce_refund_created', array($this, 'refunds_create'), 10, 2);
        add_action('profile_update', array($this, 'customers_update'), 10, 2 );
        add_action('wp_login', array($this, 'customers_login'), 10, 2);

    }

    private function check_user($order_id)
    {
        $order = wc_get_order($order_id);
        return $order->get_user_id() ? true : false;
    }

    /**
     * @param $order \WC_Order
     * @param $data_store
     * @return void
     */
    public function update_lootly_order_object_save($order,$data_store){
        $this->update_lootly_orders($order->get_id(), null, $order->get_status());
    }
    public function update_lootly_orders($order_id, $old_status, $new_status)
    {
        //if ($this->check_user($order_id)){
        try {
            $selected_status = lootly()->options->getOption('status');
            if (!$selected_status) $selected_status = 'completed';
            if ($new_status == 'cancelled') {
                $data = ['id' => $order_id];
                $data['key'] = lootly()->options->getOption('api_key');
                $data['hmac'] = lootly()->api->createHMAC($data);
                $data = json_encode($data);
                $args = array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                    'timeout'=>60,
                    'method'=>'POST',
                    'body' => $data,
                    'redirection'=>10,
                    'httpversion'=>'1.1',
                    'blocking'=>true
                );
                $response = wp_remote_post(lootly()->api->endpoint."/integrations/webhooks/woocommerce/orders-cancelled",$args);
                $httpcode = wp_remote_retrieve_response_code( $response );

            } elseif ($new_status == $selected_status) {
                $data = $this->get_order_data($order_id);
                $data['key'] = lootly()->options->getOption('api_key');
                $data['hmac'] = lootly()->api->createHMAC($data);
                $data = json_encode($data);
                $args = array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                    'timeout'=>60,
                    'method'=>'POST',
                    'body' => $data,
                    'redirection'=>10,
                    'httpversion'=>'1.1',
                    'blocking'=>true
                );
                $response = wp_remote_post(lootly()->api->endpoint."/integrations/webhooks/woocommerce/orders-{$selected_status}",$args);
                $httpcode = wp_remote_retrieve_response_code( $response );
                if ($httpcode==200 || $httpcode==201){
                    $this->add_order_note($order_id);
                } else {
                    if (is_object($response) && $response instanceof \WP_Error){
                        $order = wc_get_order($order_id);
                        if (!$order) return ;
                        $errorMessage = $response->get_error_message();
                        $message = "Order data cannot send to Lootly(".$errorMessage.")";
                        if (substr($errorMessage,0,14)=='cURL error 60:'){
                            $message = "Order data cannot send to Lootly, due to store missing SSL certificate (cURL error 60)";
                        }
                        $order->add_order_note($message);
                    } elseif(isset($response['response'])){
                        $order = wc_get_order($order_id);
                        if (!$order) return ;
                        $order->add_order_note("Order data cannot send to Lootly(".json_encode($response['response']).")");
                    }
                }
            } else {
                $order = wc_get_order($order_id);
                $customerId = $order->get_customer_id();
                if ($customerId){
                    $this->customers_create($customerId);
                }
            }
        //}
        } catch (\Exception $e){
            $logger = wc_get_logger();
            $logger->error(
                $e->getMessage()
            );
        }
    }
    public function add_order_note($order_id){
        $order = wc_get_order($order_id);
        if (!$order) return ;
        $order->add_order_note("Order data sent to Lootly");
    }

    public function orders_paid($processing, $order_id, $order)
    {

        //if ($this->check_user($order_id)) {
            $data = $this->get_order_data($order_id);
            $data['key'] = lootly()->options->getOption('api_key');
            $data['hmac'] = lootly()->api->createHMAC($data);
            $data = json_encode($data);
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'timeout'=>60,
                'method'=>'POST',
                'body' => $data,
                'redirection'=>10,
                'httpversion'=>'1.1',
                'blocking'=>true
            );
            $response = wp_remote_post(lootly()->api->endpoint."/integrations/webhooks/woocommerce/orders-processing",$args);
            $httpcode = wp_remote_retrieve_response_code( $response );
        //}
    }

    public function refunds_create($refund_id, $args)
    {
        $order_id = $args['order_id'];
        if ($this->check_user($order_id)) {
            $amount = $args['amount'];
            $data = [
                'id' => $order_id,
                'transactions' => [(object)['amount' => $amount]],
                'key' => lootly()->options->getOption('api_key')
            ];
            $data['hmac'] = lootly()->api->createHMAC($data);
            $data = json_encode($data);
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'timeout'=>60,
                'method'=>'POST',
                'body' => $data,
                'redirection'=>10,
                'httpversion'=>'1.1',
                'blocking'=>true
            );
            $response = wp_remote_post(lootly()->api->endpoint."/integrations/webhooks/woocommerce/refunds-create",$args);
            $httpcode = wp_remote_retrieve_response_code( $response );
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            if (isset($response['response']) && isset($response['response']['message']) && $response['response']['message'] == 'OK') {
                $order->add_order_note("Refund data sent to Lootly.");
            } else {
                $order->add_order_note("Refund data sent to Lootly. Result:" . json_encode($response['response']));
            }
        }
    }


    public function customers_create($id)
    {
        $data = $this->get_customer_data($id);
        $data['key'] = lootly()->options->getOption('api_key');
        $data['hmac'] = lootly()->api->createHMAC($data);
        $data = json_encode($data);
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout'=>60,
            'method'=>'POST',
            'body' => $data,
            'redirection'=>10,
            'httpversion'=>'1.1',
            'blocking'=>true
        );
        $response = wp_remote_post(lootly()->api->endpoint."/integrations/webhooks/woocommerce/customers-create",$args);
        $httpcode = wp_remote_retrieve_response_code( $response );
    }
    public function customers_login($user_login, $user)
    {
        $lootlyRegistered = get_user_meta($user->ID, 'lootly_registered', true);
        if (!$lootlyRegistered) {
            $this->customers_create($user->ID);
            update_user_meta($user->ID, 'lootly_registered', time());
        }
    }
    public function customers_update($user_id, $old_user_data)
    {
        $this->customers_create($user_id);
    }
    public function get_order_data($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) return [];
        $coupons = $order->get_coupon_codes();
        $discount_codes = [];
        foreach ($coupons as $coupon) {
            $discount_codes[] = (object)['code' => $coupon];
        }
        $taxes = $order->get_tax_totals();
        $taxes_included = $taxes ? 1 : 0;
        $taxTotal = 0;
        if ($taxes_included) {
            if (is_array($taxes)) {
                foreach ($taxes as $tax) {
                    if (isset($tax->amount)) {
                        $taxTotal += $tax->amount;
                    }
                }
            } else {
                $taxTotal = $taxes;
            }
        }
        $customerIdent = $order->get_user_id();
        if (!$customerIdent){
            $customerIdent = $order->get_billing_email();
        }
        $customer = (object)$this->get_customer_data($customerIdent,$order);
        $productsData = [];
        /** @var \WC_Order_Item_Product $item */
        foreach ($order->get_items() as $item) {
            /** @var \WC_Product_Simple $product */
            $product = $item->get_product();
            $categories = [];
            $cats = $product->get_category_ids();
            foreach ($cats as $categoryId) {
                $cat = get_term_by('ID', $categoryId, 'product_cat');
                if ($cat) {
                    $categories[] = [
                        'category_name' => $cat->name,
                        'category_id' => $categoryId
                    ];
                }
            }
            $productData = [];
            $productData['product_id'] = $item->get_product_id();
            $productData['product_name'] = $item->get_name();
            $price = $item->get_total() / $item->get_quantity();
            $productData['product_price'] = number_format($price,2,'.','');
            $productData['quantity'] = $item->get_quantity();
            $productData['categories'] = $categories;
            $productsData[] = $productData;
        }
        $data = [
            'id' => $order_id,
            'total_price' => number_format($order->get_total(),2,'.',''),
            'total_tax' => number_format($taxTotal,2,'.',''),
            'total_discounts' => number_format($order->get_discount_total(),2,'.',''),
            'subtotal_price' => number_format($order->get_subtotal() - $order->get_discount_total(),2,'.',''),
            'taxes_included' => $taxes_included,
            'discount_codes' => $discount_codes,
            'customer' => $customer,
            "products" => $productsData,
            'ip_address' => $order->get_customer_ip_address()
        ];
        return $data;
    }
    public function getCustomerIdByEmail($email) {
        global $wpdb;

        $customer_id = $wpdb->get_var( $wpdb->prepare( "
            SELECT user_id 
            FROM $wpdb->usermeta 
            WHERE meta_key = 'billing_email'
            AND meta_value = '%s'
        ", $email ) );
        return $customer_id;
    }
    public function get_customer_data($id, $order = null)
    {
        if (!is_numeric($id)){
            $id = $this->getCustomerIdByEmail($id);
        }
        $customer = new WC_Customer($id);

        if (!$customer->get_id()) {
            $data = [
                'email' => $order->get_billing_email(),
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'default_address' => (object)[
                    'zip' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country()
                ]
            ];
            return $data;
        }
        if(version_compare(get_bloginfo('version'),'3.0', '>=') ){
            $postcode = $customer->get_billing_postcode();
        } else {
            $postcode = $customer->get_postcode();
        }
        $firstname = $customer->get_first_name();
        if (!$firstname){
            $firstname = $customer->get_username();
        }
        $data = [
            'id' => $id,
            'email' => $customer->get_email(),
            'first_name' => $firstname,
            'last_name' => $customer->get_last_name(),
            'birthday' => '',
            'default_address' => (object)[
                'zip' => $postcode,
                'country' => $customer->get_billing_country()
            ]
        ];
        return $data;
    }
    public function lootly_auto_apply_coupon($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data){$e = 1;
        $autoapply = lootly()->options->getOption('autoapply');
        if ($autoapply=='1'){
            $customerId = WC()->cart->get_customer()->get_id();
            $couponsStored = get_user_meta( $customerId, "discount_codes");
            foreach ($couponsStored as $coupon){
                $the_coupon = new \WC_Coupon( $coupon );
                // Check it can be used with cart.
                if ( ! $the_coupon->is_valid() ) {
                    continue;
                }
                WC()->cart->apply_coupon($coupon);
            }
        }
    }
    /**
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * @access private
     */
    private function __clone()
    {

    }

}

