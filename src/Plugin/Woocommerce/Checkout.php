<?php

namespace AdCaptcha\Plugin\Woocommerce\Checkout;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Checkout {

    public function setup() {
        add_action( 'woocommerce_review_order_before_submit', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'woocommerce_review_order_before_submit', [ AdCaptcha::class, 'captcha_trigger' ] );
        // add_action( 'woocommerce_checkout_process', [ $this, 'verify' ] );
    }

    // public function verify( $errors ) {
    //     $response = Verify::verify_token();

    //     if ( !$response ) {
    //         $errors = new WP_Error('ad_captcha_error', __( '<strong>ERROR</strong>: Invalid captcha.', 'ad-captcha' ) );
    //     }

    //     return $errors;
    // }
}
