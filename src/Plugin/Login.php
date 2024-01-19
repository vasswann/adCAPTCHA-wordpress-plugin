<?php

namespace AdCaptcha\Plugin\Login;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Login {

    public function setup() {
        add_action( 'login_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'login_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'wp_authenticate_user', [ $this, 'verify' ], 10, 1 );
    }

    public function verify( $errors ) {
        $verify = new Verify();
        $response = $verify->verify_token();


        if ( $response === false ) {
            $errors = new WP_Error('ad_captcha_error', __( '<strong>Error</strong>: Incomplete captcha, Please try again.', 'ad-captcha' ));
        }

        return $errors;
    }
}
