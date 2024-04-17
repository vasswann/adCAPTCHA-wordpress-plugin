<?php

namespace AdCaptcha\Widget\Verify;

class Verify {

    // The actions are triggered after a post request is sent with action save_token
    public static function init() {
        add_action('wp_ajax_save_token', array(__CLASS__, 'save_token'));
        add_action('wp_ajax_nopriv_save_token', array(__CLASS__, 'save_token'));
    }

    // Gets the successToken from the captcha trigger post request
    public static function save_token() {
        check_ajax_referer('adcaptcha_nonce', 'nonce');
        $successToken = sanitize_text_field(wp_unslash($_POST['successToken']));
        if (isset($successToken)) {
            update_option('adcaptcha_success_token', $successToken);
            wp_send_json_success('Success');
        } else {
            wp_send_json_success('Failed');
        }
    }

    public static function verify_token() {
        $successToken = get_option('adcaptcha_success_token');
        $apiKey = get_option('adcaptcha_api_key');

        if (!$successToken || !$apiKey) {
            return false;
        }

        $url = 'https://api.adcaptcha.com/v1/verify';
        $body = wp_json_encode([
            'token' => $successToken
        ]);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body,
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $message = json_decode($body);
        if ($message && $message->message === 'Token verified') {
            return true;
        }

        return false;
    }
}

Verify::init();
