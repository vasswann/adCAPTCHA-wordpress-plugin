<?php

namespace AdCaptcha\Widget\Verify;

class Verify {

    public static function init() {
        add_action('wp_ajax_verify_token', array(__CLASS__, 'save_token'));
        add_action('wp_ajax_nopriv_verify_token', array(__CLASS__, 'save_token'));
    }

    public static function save_token() {
        check_ajax_referer('adcaptcha_nonce', 'nonce');
        if (isset($_POST['successToken'])) {
            // Process the successToken here
            update_option('adcaptcha_success_token', sanitize_text_field(isset($_POST['successToken'])));
            // Perform verification logic here

            // Example: return success message
            $response = true;
        } else {
            // Example: return error message
            $response = false;
        }

        if ($response) {
            wp_send_json_success('Success');
        } else {
            wp_send_json_success('Success');
        }
    }

    public static function verify_token() {
        $successToken = get_option('adcaptcha_success_token');
        if (isset($successToken) && $successToken != '') {
            return true;
        } else {
            return false;
        }
    }
}

Verify::init();
