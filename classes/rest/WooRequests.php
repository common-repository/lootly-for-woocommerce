<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 22.03.2019
 * Time: 12:06
 */

namespace plugins\lootly\classes\rest;

use WP_REST_Request;

class WooRequests implements woocommerce
{

    public function create_coupon(WP_REST_Request $request)
    {
        $request = lootly()->wooClasses->coupons->create_item($request);
        return $request;
    }

    public function get_customer(WP_REST_Request $request)
    {
        $request = lootly()->wooClasses->customers->get_item($request);
        return $request;
    }

    public function get_products(WP_REST_Request $request)
    {
        $request = lootly()->wooClasses->products->get_items($request);
        return $request;
    }
    public function get_categories(WP_REST_Request $request)
    {
        $request = lootly()->wooClasses->categories->get_items($request);
        return $request;
    }
}