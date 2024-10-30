<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 13.03.2019
 * Time: 14:21
 */

namespace plugins\lootly\classes\rest;


use plugins\lootly\classes\base\Base;
use Exception;
use WP_User;
use WP_Error;

class Authentification extends Base
{
    /**
     * Setup class
     *
     * @since 2.1
     */
    public function __construct() {

    }

    /**
     * Authenticate the request. The authentication method varies based on whether the request was made over SSL or not.
     *
     * @since 2.1
     * @param WP_User $user
     * @return null|WP_Error|WP_User
     */
    public function authenticate() {
        try {

            if ($this->perform_oauth_authentication())
            {
                $user = $this->get_user();
            }else{
                $user = new WP_Error( 'woocommerce_api_authentication_error', 'hmac is incorrect', array( 'status' => 401 ) );
            }


        } catch ( Exception $e ) {
            $user = new WP_Error( 'woocommerce_api_authentication_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
        }

        return $user;
    }


    private function perform_oauth_authentication() {
        $data = json_decode(file_get_contents('php://input'), true);
        if(!isset($data)) $data = $_GET;
        if (isset($data['hmac'])){
            $costumer_hmac = $data['hmac'];
            if(lootly()->api->createHMAC($data) === $costumer_hmac){
                return true;
            } else {
                $data['product_ids'] = [];
                if (lootly()->api->createHMAC($data) === $costumer_hmac){
                    return true;
                }
            }
        }
        return false;
    }

    private function get_user()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['discount_code'])
            && isset($data['discount_code']['prerequisite_customer_ids'])
            && isset($data['discount_code']['prerequisite_customer_ids'][0])
            && $data['discount_code']['prerequisite_customer_ids'][0] > 0) {
            $id = $data['discount_code']['prerequisite_customer_ids'][0];
            $user = get_user_by('id', $id);
        } else {
            $user = false;
            $admins = get_users(array('role' => 'administrator'));
            foreach ($admins as $admin) {
                $user = get_user_by('id', $admin->ID);
                break;
            }
        }

        if (!$user) {
            throw new Exception(__('API user is invalid', 'woocommerce'), 401);
        }

        return $user;
    }


}