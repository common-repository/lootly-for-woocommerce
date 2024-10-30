<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 06.03.2019
 * Time: 17:49
 */

namespace plugins\lootly\classes\api;


use plugins\lootly\classes\base\Base;

class API extends Base
{
    /**
     * @var API
     */
    private static $_instance;
    private $email;
    private $api_key;
    private $api_secret;
    public $endpoint;

    /**
     * @return API
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }


    /**F
     * @access private
     */
    private function __construct()
    {
        $options = lootly()->options;
        $this->email = $options->getOption('email');
        $this->api_key = $options->getOption('api_key');
        $this->api_secret = $options->getOption('api_secret');
        $this->endpoint = 'https://lootly.io';
    }

    /**
     * @access private
     */
    private function __clone()
    {

    }

    public function createHMAC($data)
    {
        if(is_array($data)){
            unset($data['hmac']);
            ksort($data);
            return base64_encode(hash_hmac('sha256', json_encode($data), $this->api_secret, true));
        } else return false;
    }

    public function verify()
    {
        $data = [
            'key' => $this->api_key,
            't' => time(),
        ];
        $data['hmac'] = $this->createHMAC($data);
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
        $response = wp_remote_post($this->endpoint."/integrations/webhooks/woocommerce/key-verify",$args);
        $httpcode = wp_remote_retrieve_response_code( $response );

        if ($httpcode == 201 || $httpcode == 200){
            return true;
        }else return false;

    }

    public function appInstalled()
    {
        $url = get_site_url();
        $data = [
            'shop_url' => $url,
            'api_endpoint' => get_site_url(null,'/index.php/wp-json/lootly/'),
            'key' => $this->api_key
        ];
        $data['hmac'] = $this->createHMAC($data);
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
        $response = wp_remote_post($this->endpoint."/integrations/webhooks/woocommerce/app-installed",$args);
        $httpcode = wp_remote_retrieve_response_code( $response );

        if ($httpcode == 201 || $httpcode == 200){
            return true;
        }else return false;
    }




}