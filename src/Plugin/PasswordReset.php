<?php

namespace AdCaptcha\Plugin\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class PasswordReset {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("lostpassword_form", "lostpassword_form");
        add_action( 'lostpassword_post', [ $this, 'verify' ], 10, 1 );
    }

    public function verify( $errors ) {
        $response = Verify::verify_token();

        if ( !$response ) {
            $errors = new WP_Error('ad_captcha_error', __( '<strong>ERROR</strong>: Invalid captcha.', 'ad-captcha' ) );
        }

        return $errors;
    }
}
