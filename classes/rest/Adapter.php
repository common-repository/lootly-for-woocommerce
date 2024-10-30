<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 22.03.2019
 * Time: 12:12
 */

namespace plugins\lootly\classes\rest;

use WP_REST_Request;
use WP_Error;

class Adapter implements lootly
{
    protected $woo_requests = null;

    public function __construct()
    {
        $this->woo_requests = new WooRequests();
    }

    public function set_discount_codes(WP_REST_Request $request)
    {
        $discount_code = $request['discount_code'];
        $data = $this->convert_request_data($discount_code);
        $request->set_body_params($data);
        $response = $this->woo_requests->create_coupon($request);
        if (!is_wp_error($response)) {
            $data = $response->get_data();
            $response_data = ['discount_code' => [
                "id" => $data['id'],
                "code" => $data['code'],
                "usage_count" => 0,
                "created_at" => $data['date_created'],
                "updated_at" => $data['date_modified'],
                "usage_limit" => $data['usage_limit'],
                "individual_use" => $data['individual_use'],
                "limit_usage_to_x_items" => $data['limit_usage_to_x_items'],
            ]];

            $response->set_data($response_data);

            $json = $request->get_body();
            $dataParsed = json_decode($json,true);
            if (isset($dataParsed['discount_code']) && isset($dataParsed['discount_code']['prerequisite_customer_ids'])){
                $customerId = reset($dataParsed['discount_code']['prerequisite_customer_ids']);
                if($customerId){
                    $dataStored = get_user_meta( $customerId, "discount_codes");
                    add_user_meta($customerId,"discount_codes",$data['code']);
                }
            }
        }
        /*if(isset($data['email_restrictions'][0])){
            $email = $data['email_restrictions'][0];
            $customer = get_user_by( 'email', $email );
            $session_handler = new \WC_Session_Handler();

    // Get the user session from its user ID:
            $session = $session_handler->get_session( $customer->ID, array() );
            $cart = new \WC_Cart();
            $session_handler->cart->set_totals( $session['cart_totals']);
            $session_handler->cart->set_applied_coupons( $session['applied_coupons']);
            $session_handler->cart->set_coupon_discount_totals($session['coupon_discount_totals']);
            $session_handler->cart->set_coupon_discount_tax_totals( $session['coupon_discount_tax_totals']);
            $session_handler->cart->set_removed_cart_contents( $session['removed_cart_contents']);

            // Get cart items array
            $cart_items = maybe_unserialize($session['cart']);
        }*/
        return $response;
    }

    public function set_price_rules(WP_REST_Request $request)
    {
        $discount_code = $request['price_rule'];
        $data = $this->convert_request_data($discount_code);
        $request->set_body_params($data);
        $response = $this->woo_requests->create_coupon($request);
        if (!is_wp_error($response)) {
            $data = $response->get_data();
            $response_data = ['price_rule' => [
                "id" => $data['id'],
                "value_type" => isset($discount_code['value_type']) ? $discount_code['value_type'] : 'fixed_amount',
                "value" => isset($discount_code['value']) ? $discount_code['value'] : 0,
                "customer_selection" => null,
                "target_type" => null,
                "target_selection" => null,
                "allocation_method" => null,
                "allocation_limit" => null,
                "once_per_customer" => isset($discount_code['once_per_customer']) ? $discount_code['once_per_customer'] : false,
                "usage_limit" => $data['usage_limit'],
                "limit_usage_to_x_items" => $data['limit_usage_to_x_items'],
                "starts_at" => $data['date_created'],
                "ends_at" => $data['date_expires'],
                "created_at" => $data['date_created'],
                "updated_at" => $data['date_modified'],
                "entitled_product_ids" => $data['product_ids'],
                "entitled_variant_ids" => $data['product_ids'],
                "entitled_collection_ids" => $data['product_categories'],
                "entitled_country_ids" => [],
                "prerequisite_product_ids" => [],
                "prerequisite_variant_ids" => [],
                "prerequisite_collection_ids" => [],
                "prerequisite_saved_search_ids" => [],
                "prerequisite_customer_ids" => isset($discount_code['prerequisite_customer_ids']) ? $discount_code['prerequisite_customer_ids'] : [],
                "prerequisite_subtotal_range" => isset($discount_code['prerequisite_subtotal_range']) ? $discount_code['prerequisite_subtotal_range'] : null,
                "prerequisite_quantity_range" => null,
                "prerequisite_shipping_price_range" => null,
                "prerequisite_to_entitlement_quantity_ratio" => [
                    "prerequisite_quantity" => null,
                    "entitled_quantity" => null
                ],
                "title" => $data['code']
            ]];

            $response->set_data($response_data);
        }

        return $response;
    }

    public function set_price_rules_codes(WP_REST_Request $request)
    {
        return new WP_Error("woocommerce_not_supported", sprintf(__('This price rules with discount codes not supported in WooCommerce', 'woocommerce')), array('status' => 400));
    }

    public function get_customers(WP_REST_Request $request)
    {
        $response = $this->woo_requests->get_customer($request);
        if (!is_wp_error($response)) {
            $data = $response->get_data();
            $last_order = wc_get_customer_last_order($data['id']);
            if ($last_order) {
                $last_order_id = $last_order->get_id();
                $last_order_title = $last_order->get_order_number();
            } else {
                $last_order_id = '';
                $last_order_title = '';
            }
            $response_data = ['customer' => [
                "id" => $data['id'],
                "email" => $data['email'],
                "accepts_marketing" => false,
                "created_at" => $data['date_created'],
                "updated_at" => $data['date_modified'],
                "first_name" => $data['first_name'],
                "last_name" => $data['last_name'],
                "orders_count" => wc_get_customer_order_count($data['id']),
                "state" => "disabled",
                "total_spent" => wc_get_customer_total_spent($data['id']),
                "last_order_id" => $last_order_id,
                "note" => null,
                "verified_email" => true,
                "multipass_identifier" => null,
                "tax_exempt" => false,
                "phone" => $data['billing']['phone'],
                "tags" => "",
                "last_order_name" => $last_order_title,
                "currency" => get_woocommerce_currency(),
                "addresses" => [
                    [
                        "id" => $data['id'],
                        "customer_id" => $data['id'],
                        "first_name" => $data['first_name'],
                        "last_name" => $data['last_name'],
                        "company" => $data['billing']['company'],
                        "address1" => $data['billing']['address_1'],
                        "address2" => $data['billing']['address_2'],
                        "city" => $data['billing']['city'],
                        "province" => $data['billing']['state'],
                        "country" => WC()->countries->countries[$data['billing']['country']],
                        "zip" => $data['billing']['postcode'],
                        "phone" => $data['billing']['phone'],
                        "name" => "",
                        "province_code" => "",
                        "country_code" => $data['billing']['country'],
                        "country_name" => WC()->countries->countries[$data['billing']['country']],
                        "default" => true
                    ]
                ],
                "default_address" => [
                    "id" => $data['id'],
                    "customer_id" => $data['id'],
                    "first_name" => $data['first_name'],
                    "last_name" => $data['last_name'],
                    "company" => $data['billing']['company'],
                    "address1" => $data['billing']['address_1'],
                    "address2" => $data['billing']['address_2'],
                    "city" => $data['billing']['city'],
                    "province" => $data['billing']['state'],
                    "country" => WC()->countries->countries[$data['billing']['country']],
                    "zip" => $data['billing']['postcode'],
                    "phone" => $data['billing']['phone'],
                    "name" => "",
                    "province_code" => "",
                    "country_code" => $data['billing']['country'],
                    "country_name" => WC()->countries->countries[$data['billing']['country']],
                    "default" => true
                ]
            ]];

            $response->set_data($response_data);
        }
        return $response;
    }

    public function get_products(WP_REST_Request $request)
    {
        $data = [];
        if (isset($query['q']) && !empty($query['q'])) {
            $data['search'] = $query['q'];
        } elseif(isset($_GET['title'])){
            $data['search'] = $_GET['title'];
        }
        $data['per_page'] = 30;
        $data['page']=1;
        $data['orderby']='id';
        $data['order']='DESC';
        $request->set_body_params($data);
        $request->set_query_params($data);
        $response = $this->woo_requests->get_products($request);
        $products = [];
        foreach ($response->get_data() as $item) {
            $tags = [];
            foreach ($item['tags'] as $tag) {
                $tags[] = $tag['name'];
            }
            $tags = implode(', ', $tags);
            $image = get_the_post_thumbnail_url($item['id']);
            $pr = wc_get_product($item['id']);
            $image_ids = $pr->get_gallery_image_ids();
            $images = [];
            foreach ($image_ids as $attachment_id) {
                $images[] = ['id' => $attachment_id, 'product_id' => $item['id'], 'src' => wp_get_attachment_url($attachment_id)];
            }
            $product = [
                "id" => $item['id'],
                "title" => $item['name'],
                "body_html" => $item['description'],
                "vendor" => "",
                "product_type" => $item['type'],
                "created_at" => $item['date_created'],
                "handle" => $item['slug'],
                "updated_at" => $item['date_modified'],
                "published_at" => $item['date_created'],
                "template_suffix" => null,
                "tags" => $tags,
                "images" => $images,
                "image" => [
                    "product_id" => $item['id'],
                    "src" => $image,
                ]
            ];
            $products[] = $product;

        }
        $response_data = [
            'products' => $products
        ];
        $response->set_data($response_data);
        return $response;
    }

    public function get_categories(WP_REST_Request $request)
    {
        $data = [];
        if (isset($query['q']) && !empty($query['q'])) {
            $data['search'] = $query['q'];
        } elseif(isset($_GET['title'])){
            $data['search'] = $_GET['title'];
        }
        $data['per_page'] = 30;
        $data['page']=1;
        $request->set_body_params($data);
        $request->set_query_params($data);
        $response = $this->woo_requests->get_categories($request);
        $categories = [];
        foreach ($response->get_data() as $item) {
            $product = [
                "id" => $item['id'],
                "title" => $item['name'],
            ];
            $categories[] = $product;
        }
        $response_data = [
            'categories' => $categories
        ];
        $response->set_data($response_data);
        return $response;
    }

    public function get_customer_saved_searches(WP_REST_Request $request)
    {
        return new WP_Error("woocommerce_not_supported", sprintf(__('The customer saved searches not supported in WooCommerce', 'woocommerce')), array('status' => 400));

    }

    public
    function get_customer_saved_searches_count(WP_REST_Request $request)
    {
        return new WP_Error("woocommerce_not_supported", sprintf(__('The customer saved searches not supported in WooCommerce', 'woocommerce')), array('status' => 400));
    }

    public
    function set_customer_saved_searches(WP_REST_Request $request)
    {
        return new WP_Error("woocommerce_not_supported", sprintf(__('The customer saved searches not supported in WooCommerce', 'woocommerce')), array('status' => 400));
    }

    private
    function convert_request_data($data_request)
    {
        $data = [];
        if (isset($data_request['allocation_method'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['created_at'])) {
            $data['date_created'] = $data_request['created_at'];
        }

        if (isset($data_request['customer_selection'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['ends_at'])) {
            $data['date_expires'] = $data_request['ends_at'];
        }

        if (isset($data_request['entitled_collection_ids'])) {
            $data['product_categories'] = $data_request['entitled_collection_ids'];
        }

        if (isset($data_request['entitled_country_ids'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['entitled_product_ids'])) {
            $data['product_ids'] = $data_request['entitled_product_ids'];
        }
        if (isset($data_request['entitled_category_ids'])) {
            $data['product_categories'] = $data_request['entitled_category_ids'];
        }
        if (isset($data_request['excluded_product_ids'])) {
            $data['excluded_product_ids'] = $data_request['excluded_product_ids'];
        }
        if (isset($data_request['excluded_category_ids'])) {
            $data['excluded_product_categories'] = $data_request['excluded_category_ids'];
        }

        if (isset($data_request['entitled_variant_ids'])) {
            $data['product_ids'] = $data_request['entitled_variant_ids'];
        }

        if (isset($data_request['id'])) {
            $data['id'] = $data_request['id'];
        }

        if (isset($data_request['once_per_customer'])) {
            if ($data_request['once_per_customer']) {
                $data['usage_limit_per_user'] = $data_request['once_per_customer'];
            }
        }

        if (isset($data_request['prerequisite_customer_ids']) && count($data_request['prerequisite_customer_ids'])) {
            $emails = [];
            $users = get_users(['include' => $data_request['prerequisite_customer_ids']]);
            foreach ($users as $user) {
                $emails[] = $user->user_email;
            }
            $data['email_restrictions'] = $emails;
        }
        if (isset($data_request['prerequisite_customer_emails'])) {
            $emails = $data_request['prerequisite_customer_emails'];
            $data['email_restrictions'] = $emails;
        }

        if (isset($data_request['prerequisite_quantity_range'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['prerequisite_saved_search_ids'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['prerequisite_shipping_price_range'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['prerequisite_subtotal_range'])) {
            $data['minimum_amount'] = $data_request['prerequisite_subtotal_range']['greater_than_or_equal_to'];
        }

        if (isset($data_request['starts_at'])) {
            $data['date_created'] = $data_request['starts_at'];
        }

        if (isset($data_request['target_selection'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['target_type'])) {
            if($data_request['target_type'] === 'shipping_line'){
                $data['free_shipping'] = true;
            }
        }

        if (isset($data_request['title'])) {
            $data['code'] = $data_request['title'];
        }

        if (isset($data_request['usage_limit'])) {
           $data['usage_limit'] = $data_request['usage_limit'];
        }else{
            $data["usage_limit"] = 1;
        }

        if (isset($data_request['prerequisite_product_ids'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['prerequisite_variant_ids'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['prerequisite_collection_ids'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['value'])) {
            $data['amount'] = abs($data_request['value']);
        }

        if (isset($data_request['value_type'])) {
            switch ($data_request['value_type']) {
                case 'fixed_amount':
                    $type = 'fixed_cart';
                    break;
                case 'percentage' :
                    $type = 'percent';
                    break;
                default :
                    $type = 'fixed_product';
                    break;
            }
            $data['discount_type'] = $type;
        }

        if (isset($data_request['prerequisite_to_entitlement_quantity_ratio'])) {
            /** TODO: WooCommerce not supported feature **/
        }

        if (isset($data_request['allocation_limit'])) {
            $data['limit_usage_to_x_items'] = $data_request['allocation_limit'];
        }else{
            $data["limit_usage_to_x_items"] = NULL;
        }

        if (isset($data_request['individual_use'])) {
            $data['individual_use'] = $data_request['individual_use'];
        }else{
            $data["individual_use"] = 1;
        }
        
        return $data;
    }
}