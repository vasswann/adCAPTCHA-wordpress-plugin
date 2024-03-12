<?php

namespace AdCaptcha\Plugin\Login;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use WP_Error;

class Login {

    public function setup() {
        global $adCAPTCHAWordpressLogin;
        $adCAPTCHAWordpressLogin = $this;
        $enableSubmitButtonScript = true;
        add_action('login_enqueue_scripts', function() use ($enableSubmitButtonScript) {
            AdCaptcha::enqueue_scripts($enableSubmitButtonScript);
        });
        add_action( 'login_enqueue_scripts', [ $this, 'disable_safari_auto_submit' ] );
        add_action( 'login_form', [ AdCaptcha::class, 'captcha_trigger' ] );
        add_action( 'wp_authenticate_user', [ $adCAPTCHAWordpressLogin, 'verify' ], 10, 1 );
    }

    public function verify( $errors ) {
        $verify = new Verify();
        $response = $verify->verify_token();

        if ( $response === false ) {
            $errors = new WP_Error('adcaptcha_error', __( '<strong>Error</strong>: Incomplete captcha, Please try again.', 'adcaptcha' ));
        }

        return $errors;
    }

    public function disable_safari_auto_submit() {
        echo '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var form = document.querySelector("#loginform");
            var submitButton = document.querySelector("#wp-submit");

            if (form) {
                if (submitButton) {
                    submitButton.disabled = true;
                }

                form.addEventListener("submit", function(event) {
                    if (!window.adcap.successToken) {
                        event.preventDefault();
                    }
                });
            }
        });
    </script>';
    }
}
