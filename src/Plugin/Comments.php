<?php

namespace AdCaptcha\Plugin\Comments;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;

class Comments {

    public function setup() {
        add_action( 'comment_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_filter( 'comment_form_submit_field', [ $this, 'captcha_trigger_filter' ] );
        add_action( 'pre_comment_approved', [ $this, 'verify' ], 20, 2 );
    }

    public function verify( $approved, array $commentdata ) {
        $verify = new Verify();
        $response = $verify->verify_token();


        if ( $response === false ) {
            wp_die(__('<strong>Error</strong>: Incomplete captcha, Please try again.', 'adcaptcha'), '', ['response' => 400, 'back_link' => true]);
        }

        return $approved;
    }

    // Renders the captcha before the submit button
    public function captcha_trigger_filter($submit_field) {
        return AdCaptcha::captcha_trigger() . $submit_field;
    }
}
