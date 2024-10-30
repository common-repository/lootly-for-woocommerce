<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 11.03.2019
 * Time: 11:58
 */

namespace plugins\lootly\classes\rest;

use plugins\lootly\classes\base\Base;
use WP_REST_Server;
use WP_Error;

class Router extends Base
{
    /**
     * @var Router
     */
    private static $_instance;
    public $namespace = 'lootly';

    /**
     * @return Router
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * @access public
     */
    public function init()
    {
        add_action( 'parse_request', array( $this, 'authorise' ), 0 );
        add_action('rest_api_init', array($this, 'register_endpoints'));
    }



    public function register_endpoints()
    {
        $endpoints = lootly()->endpoints;
        register_rest_route(
            $this->namespace, '/price-rules/', array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $endpoints, 'price_rules' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/discount-codes/', array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $endpoints, 'discount_codes' ),
                    'permission_callback' => array( $this, 'discountCodesCheckPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/price-rules/(?P<id>[\d]+)/discount-codes', array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $endpoints, 'price_rules_codes' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/customers/(?P<id>[\d]+)', array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $endpoints, 'customers' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/products/', array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $endpoints, 'products' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/customer-saved-searches/', array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $endpoints, 'post_customer_saved_searches' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/customer-saved-searches', array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $endpoints, 'get_customer_saved_searches' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/customer-saved-searches/count', array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $endpoints, 'get_customer_saved_searches_count' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
        register_rest_route(
            $this->namespace, '/categories/', array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $endpoints, 'categories' ),
                    'permission_callback' => array( $this, 'checkPermissions'),
                )
            )
        );
    }

    public function authorise()
    {
        $auth = new Authentification();
        $user = $auth->authenticate();
        if ( is_a( $user, 'WP_User' ) ) {

            wp_set_current_user( $user->ID );

        }

    }

    public function checkPermissions()
    {
        if(get_current_user_id()) return true;
        return false;
    }
    public function discountCodesCheckPermissions()
    {
        return true;
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
