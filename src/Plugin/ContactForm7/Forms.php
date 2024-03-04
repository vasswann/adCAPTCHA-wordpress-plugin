<?php

namespace AdCaptcha\Plugin\ContactFrom7\Froms;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;

class Forms {

    public function setup() {
        add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ], 9 );
        remove_action( 'wp_footer', 'wpcf7_recaptcha_onload_script', 40 );
        add_filter( 'wpcf7_form_elements', [ $this, 'captcha_trigger_filter' ], 20, 1 );
        add_filter( 'wpcf7_spam', [ $this, 'verify' ], 9, 1 );
    }

    public function verify( $spam ) {
        if ( $spam ) {
			return $spam;
		}

        $verify = new Verify();
        $response = $verify->verify_token();

        if ( $response === false ) {
            $spam = true;
            add_filter('wpcf7_display_message', function($message, $status) {
                if ($status == 'spam') {
                    $message = __( 'Incomplete captcha, Please try again.', 'adCAPTCHA' );
                }
                return $message;
            }, 10, 2);
        }

        return $spam;
    }

    // Renders the captcha before the submit button
    public function captcha_trigger_filter(string $elements) {
        return preg_replace(
            '/(<input.*?type="submit")/',
            AdCaptcha::captcha_trigger() . '$1',
            $elements
            );
    }
}
