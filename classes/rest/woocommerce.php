<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 22.03.2019
 * Time: 12:00
 */

namespace plugins\lootly\classes\rest;

use WP_REST_Request;

interface woocommerce
{
    public function create_coupon(WP_REST_Request $request);
    public function get_customer(WP_REST_Request $request);
    public function get_products(WP_REST_Request $request);
}