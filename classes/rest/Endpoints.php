<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 11.03.2019
 * Time: 16:46
 */

namespace plugins\lootly\classes\rest;


use plugins\lootly\classes\base\Base;
use WP_REST_Request;
use WP_Error;

class Endpoints extends Base
{
    /**
     * @var Endpoints
     */
    private static $_instance;

    protected $adapter;

    /**
     * @return Endpoints
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public function discount_codes(WP_REST_Request $request)
    {
        return $this->adapter->set_discount_codes($request);
    }

    public function price_rules(WP_REST_Request $request)
    {
        return $this->adapter->set_price_rules($request);
    }

    public function price_rules_codes(WP_REST_Request $request)
    {
        return $this->adapter->set_price_rules_codes($request);
    }

    public function customers(WP_REST_Request $request)
    {
        return $this->adapter->get_customers($request);
    }

    public function products(WP_REST_Request $request)
    {
        return $this->adapter->get_products($request);
    }
    public function categories(WP_REST_Request $request)
    {
        return $this->adapter->get_categories($request);
    }

    public function post_customer_saved_searches(WP_REST_Request $request)
    {
        return $this->adapter->set_customer_saved_searches($request);
    }

    public function get_customer_saved_searches(WP_REST_Request $request)
    {
        return $this->adapter->get_customer_saved_searches($request);
    }

    public function get_customer_saved_searches_count(WP_REST_Request $request)
    {
        return $this->adapter->get_customer_saved_searches_count($request);
    }

    public function create_coupon(WP_REST_Request $request)
    {
        return lootly()->wooClasses->coupons->create_item($request);
    }

    public function get_coupon(WP_REST_Request $request)
    {
        return lootly()->wooClasses->coupons->get_item($request);
    }

    public function list_coupons(WP_REST_Request $request)
    {
        return lootly()->wooClasses->coupons->get_items($request);
    }

    public function get_product(WP_REST_Request $request)
    {
        return lootly()->wooClasses->products->get_item($request);

    }

    public function list_products(WP_REST_Request $request)
    {
        return lootly()->wooClasses->products->get_items($request);

    }


    /**
     * @access private
     */
    private function __construct()
    {
        $this->adapter = new Adapter();
    }

    /**
     * @access private
     */
    private function __clone()
    {

    }
}