<?php

namespace AdCaptcha\Plugin\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class PasswordReset {

    public function setup() {
        global $wordpressPasswordReset;
        $wordpressPasswordReset = $this;
        add_action( 'lostpassword_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'lostpassword_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'lostpassword_post', [ $wordpressPasswordReset, 'verify' ], 10, 1 );
    }

    public function verify( $errors ) {
        $response = Verify::verify_token();

        if ( !$response ) {
            $errors = new WP_Error('ad_captcha_error', __( '<strong>ERROR</strong>: Invalid captcha.', 'ad-captcha' ) );
        }

        return $errors;
    }
}
