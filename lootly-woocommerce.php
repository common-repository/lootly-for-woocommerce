<?php
/*
Plugin Name: Lootly for WooCommerce
Plugin URI: https://lootly.io
Description: Grow your revenue & customer happiness with a Loyalty & Referrals program.
Version: 1.29
Author: Lootly
Author URI: https://lootly.io
Text Domain: lootly
Domain Path: /i18n/
*/

use plugins\lootly\classes\base\Base;
use plugins\lootly\classes\base\Options;
use plugins\lootly\classes\Admin;
use plugins\lootly\classes\api\API;
use plugins\lootly\classes\Widget;
use plugins\lootly\classes\rest\Router;
use plugins\lootly\classes\rest\Endpoints;
use plugins\lootly\classes\rest\WooClasses;
use plugins\lootly\classes\api\Events;

define('LOOTLY_PLUGIN_URL', trailingslashit(dirname(__FILE__)));
define('LOOTLY_DOMAIN', untrailingslashit(basename(dirname(__FILE__))));
define('LOOTLY_PLUGIN_URI', trailingslashit(plugins_url() .'/'.LOOTLY_DOMAIN. '/'));
define('LOOTLY_PLUGIN_CSS_URI', trailingslashit(LOOTLY_PLUGIN_URI.'assets/css/'));
define('LOOTLY_PLUGIN_JS_URI', trailingslashit(LOOTLY_PLUGIN_URI.'assets/js/'));


require_once __DIR__ . '/autoload.php';

/**
 * Class LOOTLY
 *
 * @property wpdb db
 * @property Options options
 * @property API api
 * @property Endpoints endpoints
 * @property WooClasses wooClasses
 * @property string pluginVersion
 */
class LOOTLY extends Base {
    public function init()
    {
        register_activation_hook(__FILE__, array($this, 'activatePlugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivatePlugin'));
        add_filter( 'plugin_row_meta', array( $this, 'pluginRowMeta' ), 10, 2 );
        if ($this->isWoocommerce()) {
            add_action('plugins_loaded', array($this, 'loadPluginDomain'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'));
            add_action('wp_enqueue_scripts', array($this, 'styles'));
            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
            add_action('admin_enqueue_scripts', array($this, 'adminStyles'));

            if(is_admin()){
                Admin::instance()->init();
            }
            Widget::instance()->init();
            Router::instance()->init();
            Events::instance()->init();
        }
    }

    public function isWoocommerce()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }


    /**
     * Enqueue all required scripts
     */

    public function scripts()
    {
        wp_enqueue_script(
            'lootly-widget-config',
            LOOTLY_PLUGIN_JS_URI . 'widget.js',
            ['jquery']
        );
        wp_enqueue_script(
            'lootly-widget-js',
            'https://lootly.io/js/integrations/common/script.js',
            ['jquery'],
            '1',
            true
        );
        $this->localizeHelper('lootly-widget-config');
    }

    /**
     * Enqueue all required scripts for admin panel
     */

    public function adminScripts()
    {

    }

    /**
     * Enqueue all required styles
     */

    public function styles()
    {

    }

    /**
     * Enqueue all required styles for admin panel
     */

    public function adminStyles()
    {
        global $wp_scripts;
        $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
        wp_register_style('lootly-admin-styles', LOOTLY_PLUGIN_CSS_URI.'admin-styles.css', [], $jquery_version);
        wp_enqueue_style('lootly-admin-styles');
    }

    /**
     * @param string $handle
     */
    public function localizeHelper($handle)
    {
        wp_localize_script($handle, 'lootly_options', [
            'login' => '/my-account',
            'register' => '/my-account',
        ]);
    }

    /**
     * Activation hook handler
     */
    public function activatePlugin()
    {
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        if ( current_user_can( 'activate_plugins' ) && ! $this->isWoocommerce() ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            $error_message = '<p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">' . esc_html__( 'This plugin requires ', 'lootly' ) . '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '">WooCommerce</a>' . esc_html__( ' plugin to be active.', 'lootly' ) . '</p>';
            die( $error_message );
        }
    }

    /**
     * Deactivation hook handler
     */
    public function deactivatePlugin()
    {

    }


    public function pluginRowMeta( $links, $file ) {

        if ( plugin_basename( LOOTLY_PLUGIN_URL ) === basename(dirname($file))) {
            $row_meta = array(
                'pricing' => '<a href="https://lootly.io/pricing" target="blanc" aria-label="' . esc_attr__( 'view pricing', 'lootly' ) . '">' . esc_html__( 'View Pricing', 'lootly' ) . '</a>',
            );

            return array_merge( $links, $row_meta );
        }

        return (array) $links;
    }

    /**
     * @return wpdb
     */
    protected function getDb()
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * @return Options
     */
    protected function getOptions()
    {
        return Options::instance();
    }

    /**
     * @return API
     */
    protected function getApi()
    {
        return API::instance();
    }

    /**
     * @return Endpoints
     */
    protected function getEndpoints()
    {
        return Endpoints::instance();
    }

    /**
     * @return WooClasses
     */
    protected function getWooClasses()
    {
        return WooClasses::instance();
    }

    /**
     * Register translations directory
     * Register text domain
     */
    public function loadPluginDomain()
    {
        $path = sprintf('./%s/i18n', LOOTLY_DOMAIN);
        load_plugin_textdomain(LOOTLY_DOMAIN, false, $path);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return true;
    }


    /**
     * @return string
     */
    protected function getPluginVersion()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $pluginData = get_plugin_data(__FILE__);
        return $pluginData['Version'];
    }

    /**
     * @var LOOTLY
     */
    private static $_instance;

    /**
     * @return LOOTLY
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * LOOTLY constructor.
     *
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

LOOTLY::instance()->init();


/**
 * @return LOOTLY
 */
if(!function_exists('lootly')){
    function lootly()
    {
        return LOOTLY::instance();
    }
}
