<?php

namespace AdCaptcha\Plugin\Woocommerce\Checkout;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\AdCaptchaPlugin\AdCaptchaPlugin;

class Checkout extends AdCaptchaPlugin {

    public function setup() {   
        add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ Verify::class, 'get_success_token' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'init_trigger' ] );
        add_action( 'woocommerce_review_order_before_submit', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'woocommerce_checkout_process', [ $this, 'verify' ] );
    }

    public function verify( $error ) {
        if ( $error ) {
            return;
        }

        $successToken = sanitize_text_field(wp_unslash($_POST['adcaptcha_successToken']));
        $response = Verify::verify_token($successToken);

        if ( !$response ) {
            wc_add_notice( __( 'Incomplete captcha, Please try again.', 'adcaptcha' ), 'error' );        
        }
    }

    public function init_trigger() {
        wp_register_script('adcaptcha-wc-init-trigger', null);
        wp_add_inline_script('adcaptcha-wc-init-trigger', '
            const initTrigger = ($) => {
                function resetTrigger() {
                    window.adcap.init();
                }

                $(document.body).on("checkout_error", resetTrigger);

                $(document.body).on("updated_checkout", function () {
                    if (window.adcap) {
                        window.adcap.init();
                        resetTrigger();
                    }
                });
            };

            jQuery(document).ready(initTrigger);
        ');
        wp_enqueue_script('adcaptcha-wc-init-trigger');
    }
}
