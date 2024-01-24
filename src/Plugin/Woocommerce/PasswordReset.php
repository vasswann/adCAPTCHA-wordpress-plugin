<?php

namespace AdCaptcha\Plugin\Woocommerce\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\Plugin\PasswordReset\PasswordReset as WordpressPasswordReset;
use WP_Error;

class PasswordReset {

    public function setup() {
        add_action( 'woocommerce_lostpassword_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'woocommerce_lostpassword_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        remove_action( 'wp_authenticate_user', [ WordpressPasswordReset::class , 'verify' ], 10 );
        add_filter( 'allow_password_reset', [ $this, 'verify' ] );
    }

    public function verify( $error ) {
        $verify = new Verify();
        $response = $verify->verify_token();

        if ( !$response ) {
            $error = new WP_Error('ad_captcha_error', __( '<strong>Error</strong>: Incomplete captcha, Please try again.', 'ad-captcha' ) );
        }

        return $error;
    }
}
