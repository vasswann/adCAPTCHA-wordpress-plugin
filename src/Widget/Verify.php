<?php

namespace AdCaptcha\Widget\Verify;

class Verify {

    public static function verify_token($successToken = null) {
        error_log(print_r($_POST, true));
        if (empty($successToken)) {
            $successToken = trim( $_POST['successToken']);
            echo $successToken;
        }

        $apiKey = get_option('adcaptcha_api_key');
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
            update_option('adcaptcha_success_token', '');
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $message = json_decode($body);
        if ($message && $message->message === 'Token verified') {
            update_option('adcaptcha_success_token', '');
            return true;
        }

        return false;
    }
}
