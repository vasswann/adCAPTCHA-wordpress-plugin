<?php

namespace AdCaptcha\Plugin\Woocommerce\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class PasswordReset {

    public function setup() {
        add_action( 'woocommerce_lostpassword_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'woocommerce_lostpassword_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'wp_loaded', [ $this, 'remove_wp_action' ], 10 );
        add_filter( 'allow_password_reset', [ $this, 'verify' ], 10, 1 );
    }

    public function remove_wp_action() {
        global $adCAPTCHAWordpressPasswordReset;
        remove_action( 'lostpassword_post', [ $adCAPTCHAWordpressPasswordReset, 'verify' ], 10 );
    }

    public function verify( $error ) {
        $response = Verify::verify_token();

        if ( !$response ) {
            $error = new WP_Error('adcaptcha_error', __( 'Incomplete captcha, Please try again.', 'adcaptcha' ) );
        }

        return $error;
    }
}
