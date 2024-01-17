<?php

namespace AdCaptcha\Plugin\Login;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Login {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("login_form");
        add_action( 'wp_authenticate_user', [ $this, 'verify' ], 10, 1 );
    }

    public function verify( $errors ) {
        $response = Verify::verify_token();

        if ( !$response ) {
            $errors = new WP_Error('ad_captcha_error', __( '<strong>ERROR</strong>: Invalid captcha.', 'ad-captcha' ) );
        }

        return $errors;
    }
}
