<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 06.03.2019
 * Time: 15:20
 */

namespace plugins\lootly\classes;

use plugins\lootly\classes\api\Events;
use plugins\lootly\classes\base\Base;

class Admin extends Base
{
    /**
     * @var Admin
     */
    private static $_instance;

    /**
     * @return Admin
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
        add_action('admin_menu', array($this, 'add_menu_lootly'));
    }

    /**
     * @access public
     */
    public function add_menu_lootly()
    {
        if (current_user_can('manage_options')) {
            add_menu_page(
                'Lootly',
                'Lootly Loyalty & Rewards',
                'manage_options',
                'lootly',
                array($this, 'lootly_admin_template'),
                LOOTLY_PLUGIN_URI . '/assets/img/favicon.png');
        }
    }

    /**
     * @access public
     */
    public function lootly_admin_template()
    {
        if (count($_POST) && wp_verify_nonce($_POST['lootly_update_settings'], 'lootly_update_settings')) {
            $this->save_admin_settings($_POST);
            $data = $this->get_admin_options();
            if(isset($_POST['api_key']) && isset($_POST['api_secret']))
            {
                $verification = lootly()->api->verify();
                if($verification){
                    $data['installed'] = lootly()->api->appInstalled();
                }
                $data['verification'] = $verification;
            }
        }
        if(!isset($data)){
            $data = $this->get_admin_options();
        }
        $this->getTemplate('admin', $data, true);
    }

    /**
     * @access private
     *
     * @param  array
     */
    private function save_admin_settings($settings)
    {
        $options = lootly()->options;
        if (isset($settings['email'])) {
            $email = sanitize_email($settings['email']);
            $options->setOption('email', $email);
        }
        if (isset($settings['api_key'])) {
            $api_key = sanitize_text_field($settings['api_key']);
            $options->setOption('api_key', $api_key);
        }
        if (isset($settings['api_secret'])) {
            $api_secret = sanitize_text_field($settings['api_secret']);
            $options->setOption('api_secret', $api_secret);
        }
        if (isset($settings['status'])) {
            $status = sanitize_text_field($settings['status']);
            $options->setOption('status', $status);
        }
        if (isset($settings['autoapply'])) {
            $status = sanitize_text_field($settings['autoapply']);
            $options->setOption('autoapply', $status);
        }
    }

    /**
     * @access private
     *
     * @return array
     */
    private function get_admin_options()
    {
        $options = lootly()->options;
        $settings = [];
        $settings['email'] = $options->getOption('email');
        $settings['api_key'] = $options->getOption('api_key');
        $settings['api_secret'] = $options->getOption('api_secret');
        $settings['status'] = $options->getOption('status');
        $settings['autoapply'] = $options->getOption('autoapply');
        $settings['lootly_update_settings'] = wp_create_nonce('lootly_update_settings');
        return $settings;
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

