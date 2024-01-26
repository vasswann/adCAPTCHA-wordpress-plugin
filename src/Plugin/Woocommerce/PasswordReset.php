<?php

namespace AdCaptcha\Plugin\Woocommerce\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class PasswordReset {

    public function setup() {
        add_action( 'woocommerce_lostpassword_form', [ AdCaptcha::class, 'enqueue_scripts' ] );
        add_action( 'woocommerce_lostpassword_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_filter( 'lostpassword_post', [ $this, 'verify' ], 10, 1 );
    }

    public function verify( $error ) {
        global $wordpressPassword;
        remove_action( 'lostpassword_post', [ $wordpressPassword, 'verify' ], 10 );
        $response = Verify::verify_token();

        if ( !$response ) {
            $error = new WP_Error('ad_captcha_error', __( '<strong>Error</strong>: Incomplete captcha, Please try again.', 'ad-captcha' ) );
        }

        return $error;
    }
}
