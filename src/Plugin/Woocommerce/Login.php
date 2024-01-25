<?php

namespace AdCaptcha\Plugin\Woocommerce\Login;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Login {

    public function setup() {
        add_action( 'woocommerce_login_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'woocommerce_login_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_filter( 'woocommerce_process_login_errors', [ $this, 'verify' ], 10, 3 );
    }

    public function verify( $validation_error, $login, $password ) {
        global $wordpressLogin;
        remove_action( 'wp_authenticate_user', [ $wordpressLogin , 'verify' ], 10 );
        $response = Verify::verify_token();

        if ( $response === false ) {
            $validation_error = new WP_Error('ad_captcha_error', __( '<strong>Error</strong>: Incomplete captcha, Please try again.', 'ad-captcha' ));
        }

        return $validation_error;
    }
}
