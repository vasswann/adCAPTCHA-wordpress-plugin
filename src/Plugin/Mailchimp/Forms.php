<?php

namespace AdCaptcha\Plugin\Mailchimp\Froms;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;

use MC4WP_Form;

class Forms {

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
        $screen = get_current_screen();
        if ($screen->id == 'mc4wp_page_mailchimp-for-wp-forms') {
            echo '<script type="text/javascript">
                setTimeout(() => {
                        document.getElementById("mc4wp-form-content").addEventListener("change", () => { document.getElementById("mc4wp-form-preview").contentWindow.adcap.setupTriggers() }); 
                }, 1000);
            </script>';
        }
    }
}
