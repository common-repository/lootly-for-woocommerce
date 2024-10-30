<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 12.03.2019
 * Time: 11:54
 */

namespace plugins\lootly\classes\rest;
use plugins\lootly\classes\base\Base;
use WC_REST_Coupons_Controller;
use WC_REST_Customers_Controller;
use WC_REST_Products_Controller;


class WooClasses extends Base
{
    /**
     * @var WooClasses
     */
    private static $_instance;

    public $coupons;
    public $customers;
    public $products;
    public $categories;
    public $legacy_api;
    public $authentification;

    /**
     * @return WooClasses
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }



    /**
     * @access private
     */
    private function __construct()
    {
        $this->coupons = new WC_REST_Coupons_Controller();
        $this->customers = new WC_REST_Customers_Controller();
        $this->products = new WC_REST_Products_Controller();
        $this->categories = new \WC_REST_Product_Categories_Controller();
    }

    /**
     * @access private
     */
    private function __clone()
    {

    }
}