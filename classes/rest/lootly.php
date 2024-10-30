<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 22.03.2019
 * Time: 11:54
 */

namespace plugins\lootly\classes\rest;

use WP_REST_Request;

interface lootly
{
    public function set_discount_codes(WP_REST_Request $request);
    public function set_price_rules(WP_REST_Request $request);
    public function set_price_rules_codes(WP_REST_Request $request);
    public function get_customers(WP_REST_Request $request);
    public function get_products(WP_REST_Request $request);
    public function get_customer_saved_searches(WP_REST_Request $request);
    public function get_customer_saved_searches_count(WP_REST_Request $request);
    public function set_customer_saved_searches(WP_REST_Request $request);
}