<?php

namespace AdCaptcha\Plugin\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\Plugin\Plugin;
use WP_Error;

class PasswordReset extends Plugin {

    public function setup() {
        global $adCAPTCHAWordpressPasswordReset;
        $adCAPTCHAWordpressPasswordReset = $this;
        add_action( 'lostpassword_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'lostpassword_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'lostpassword_post', [ $adCAPTCHAWordpressPasswordReset, 'verify' ], 10, 1 );
    }

    public function verify( $errors ) {
        $response = Verify::verify_token();

        if ( !$response ) {
            $errors = new WP_Error('adcaptcha_error', __( 'Incomplete captcha, Please try again.', 'adcaptcha' ) );
        }

        return $errors;
    }
}
