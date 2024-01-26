<?php

namespace AdCaptcha\Plugin\Registration;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Registration {

    public function setup() {
        global $adCAPTCHAWordpressRegistration;
        $adCAPTCHAWordpressRegistration = $this;
        add_action( 'register_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'register_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'registration_errors', [ $adCAPTCHAWordpressRegistration, 'verify' ], 10, 1 );
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
