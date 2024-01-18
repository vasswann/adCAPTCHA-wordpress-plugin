<?php

namespace AdCaptcha\Plugin\Comments;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Comments {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("comment_form", "comment_form_submit_field");
        add_action( 'pre_comment_approved', [ $this, 'verify' ], 10, 1 );
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
