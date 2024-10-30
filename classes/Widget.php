<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 07.03.2019
 * Time: 12:19
 */

namespace plugins\lootly\classes;


use plugins\lootly\classes\base\Base;
use plugins\lootly\classes\rest\lootly;

class Widget extends Base
{
    /**
     * @var Widget
     */
    private static $_instance;

    /**
     * @return Widget
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public function init()
    {
        add_action('wp_footer', array($this, 'dynamic_data'));
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

    public function dynamic_data()
    {
        $lootly_customer_id ='';
        $lootly_customer_signature = '';
        $api_key = lootly()->options->getOption('api_key');
        $api_secret = lootly()->options->getOption('api_secret');
        $autoapply = lootly()->options->getOption('autoapply');
        $lootly_shop_domain = get_site_url();
        $lootly_shop_signature = md5($lootly_shop_domain.$api_secret);
        if(get_current_user_id()){
            $lootly_customer_id = get_current_user_id();
            $lootly_customer_signature = md5($lootly_customer_id.$api_secret);
        }
        ?>
        <div id="lootly-widget" class="lootly-init" style="display: none"
             data-provider="<?php echo esc_url(lootly()->api->endpoint);?>"
             data-api-key="<?php echo esc_html($api_key); ?>"
             data-shop-domain="<?php echo esc_url($lootly_shop_domain); ?>"
             data-shop-id="<?php echo $lootly_shop_signature; ?>"
             data-customer-id="<?php echo $lootly_customer_id; ?>"
             data-customer-signature="<?php echo $lootly_customer_signature; ?>">
        </div>
        <?php
        if ($autoapply=='1'): ?>
            <script>
                jQuery(function($){
                    var show_notice = function ( html_element, $target ) {
                        if ( ! $target ) {
                            $target =
                                $( '.wc-block-components-notices' ).first().length? $( '.wc-block-components-notices' ).first() :
                                $( '.woocommerce-notices-wrapper' ).first().length? $( '.woocommerce-notices-wrapper' ).first() :
                                $( '.wc-empty-cart-message' ).closest( '.woocommerce' ) ||
                                $( '.woocommerce-cart-form' );
                        }
                        $target.prepend( html_element );
                    };
                    window.addEventListener('widgetRewardRedeemed', (event) => {
                        if (typeof event.detail.coupon.coupon_code !="undefined"){
                            var coupon_code = event.detail.coupon.coupon_code;
                            if ($('#coupon_code').length){
                                $('#coupon_code').val(coupon_code);
                                $('[name=apply_coupon]').click();
                            } else {
                                var data = {
                                    coupon_code: coupon_code,
                                    security: '<?php echo wp_create_nonce("apply-coupon") ?>'
                                };
                                jQuery.post('<?php echo \WC_AJAX::get_endpoint('apply_coupon')?>', data).done(function(data) {
                                    jQuery(
                                        '.woocommerce-error, .woocommerce-message, .woocommerce-info, .is-error, .is-info, .is-success', $('.wc-block-components-notices')
                                    ).remove();
                                    show_notice( data );
                                    $( document.body ).trigger( 'applied_coupon', [
                                        coupon_code,
                                    ] );
                                });
                            }
                        }
                    });
                })
            </script>
        <?php endif;
    }

}