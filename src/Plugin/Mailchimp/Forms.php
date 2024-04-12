<?php

namespace AdCaptcha\Plugin\Mailchimp\Froms;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\Plugin\Plugin;

use MC4WP_Form;

class Forms extends Plugin {

    public function setup() {
        add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ], 9 );
        add_action( 'admin_enqueue_scripts', [ $this, 'form_preview_setup_triggers' ], 9 );
        add_filter( 'mc4wp_form_errors', [ $this, 'verify' ], 10, 2 );
        add_filter('mc4wp_form_messages', function($messages) {
            $messages = (array) $messages;
            $messages['invalid_captcha'] = [
                'type' => 'error',
                'text' => __( 'Incomplete captcha, Please try again.', 'adCAPTCHA' ),
            ];
            return $messages;
        });
    }

    public function verify( $errors, MC4WP_Form $form ) {
        $verify = new Verify();
        $response = $verify->verify_token();

        if ( $response === false ) {
            $errors     = (array) $errors;
            $errors[]   = 'invalid_captcha';
        }

        return  $errors;
    }

    public function form_preview_setup_triggers() {
        wp_register_script('adcaptcha-mc4wp-preview-script', null);
        wp_add_inline_script('adcaptcha-mc4wp-preview-script', 'window.onload = function() {
            if (adminpage === "mc4wp_page_mailchimp-for-wp-forms") {
                document.getElementById("mc4wp-form-content").addEventListener("change", function() {
                    document.getElementById("mc4wp-form-preview").contentWindow.adcap.setupTriggers();
                }); 
            }
        };');
        wp_enqueue_script('adcaptcha-mc4wp-preview-script');
    }
}
