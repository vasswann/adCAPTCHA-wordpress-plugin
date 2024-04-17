<?php

namespace AdCaptcha\Plugin\WPForms\Froms;

use AdCaptcha\Plugin\WPForms\AdCAPTCHA_WPForms_Field\AdCAPTCHA_WPForms_Field;
use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\Plugin\Plugin;

class Forms extends Plugin {

        public function setup() {
            add_action('plugins_loaded', function() {
                require_once plugin_dir_path(__FILE__) . '/AdCAPTCHA_WPForms_Field.php';
                new AdCAPTCHA_WPForms_Field();
                add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ]);
                add_action( 'admin_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ]);
                // add_action('admin_enqueue_scripts', function() {
                //     // Check if we're in the form builder.
                //     $screen = get_current_screen();
                //     if ($screen->id !== 'wpforms_page_wpforms-builder') {
                //         return;
                //     }
                
                //     // Enqueue your script.
                //     wp_enqueue_script('my-script', plugin_dir_url(__FILE__) . 'js/my-script.js', ['jquery'], '1.0.0', true);
                // });

                add_filter('wpforms_load_fields', function($fields) {
                    $fields[] = 'adcaptcha';
                    return $fields;
                });

                add_filter('wpforms_fields', function($fields) {
                    $fields['adcaptcha'] = 'AdCAPTCHA_WPForms_Field';
                    return $fields;
                });
            });

            add_action( 'wpforms_display_submit_before', [ AdCaptcha::class, 'captcha_trigger'] );
            add_action( 'wpforms_process', [ $this, 'verify' ], 10, 3 );
        }

        public function verify( array $fields, array $entry, array $form_data ) {
            $verify = new Verify();
            $response = $verify->verify_token();
    
            if ( $response === false ) {
                wpforms()->get( 'process' )->errors[ $form_data['id'] ]['footer'] = __( 'Incomplete captcha, Please try again.', 'adcaptcha' );
            }
        }
}
