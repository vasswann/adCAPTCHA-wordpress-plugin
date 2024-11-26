<?php

namespace AdCaptcha\Plugin\Woocommerce\Checkout;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\AdCaptchaPlugin\AdCaptchaPlugin;

class Checkout extends AdCaptchaPlugin {

    private $hasVerified = null;

    public function setup() { 
        error_log('Checkout setup');

        $this->hasVerified = get_option('wc_adcaptcha_is_verified');

        add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ Verify::class, 'get_success_token' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'init_trigger' ] );
        add_action( 'woocommerce_review_order_before_submit', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action('woocommerce_payment_complete', [ $this, 'reset_hasVerified' ]);
        add_action( 'woocommerce_checkout_process', [ $this, 'verify' ] );
    }

    public function verify() {
        error_log('time: ' . $this->hasVerified);

        if ( $this->hasVerified && strtotime($this->hasVerified) < time() ) {
            $this->reset_hasVerified();
        }

        error_log('time: ' . $this->hasVerified);

        if ( $this->hasVerified && strtotime($this->hasVerified) > time() ) {
            error_log('Already verified');
            return;
        }

        $successToken = sanitize_text_field(wp_unslash($_POST['adcaptcha_successToken']));
        $response = Verify::verify_token($successToken);

        if ( !$response ) {
            wc_add_notice( __( 'Incomplete captcha, Please try again.', 'adcaptcha' ), 'error' );    
        }

        update_option('wc_adcaptcha_is_verified', date('Y-m-d H:i:s', strtotime('+10 minutes')));
    }

    public function reset_hasVerified() {
        update_option('wc_adcaptcha_is_verified', '');
    }

    public function init_trigger() {
        wp_register_script('adcaptcha-wc-init-trigger', null);
        wp_add_inline_script('adcaptcha-wc-init-trigger', '
            const initTrigger = ($) => {
                $(document.body).on("updated_checkout", function () {
                    if (window.adcap) {
                        window.adcap.init();

                        ' . ($this->hasVerified ? ' window.adcap.setVerificationState(true);' : '' ) . '
                    }
                });
            };

            jQuery(document).ready(initTrigger);
        ');
        wp_enqueue_script('adcaptcha-wc-init-trigger');
    }
}
