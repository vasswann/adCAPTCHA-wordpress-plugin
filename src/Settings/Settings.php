<?php

namespace AdCaptcha\Settings;

class Settings {

    public function setup() {
        add_action('admin_menu', array($this, 'add_adcaptcha_options_page'));
        add_action('admin_enqueue_scripts', [ $this, 'add_styles_to_settings' ]);
        add_filter('admin_footer_text', array($this, 'change_admin_footer_text'));
        add_filter('update_footer', array($this, 'change_admin_footer_version'), PHP_INT_MAX);
    }
    
    public function add_adcaptcha_options_page() {
        add_options_page(
            'adCAPTCHA',
            'adCAPTCHA',
            'manage_options',
            'adcaptcha',
            array($this, 'render_adcaptcha_options_page')
        );
    }

    public function add_styles_to_settings() {
        wp_enqueue_style('adcaptcha-admin-styles', plugins_url('../styles/settings.css', __FILE__));
    }

    public static function verify_input_data($api_key, $placement_id) {
        $url = 'https://api.adcaptcha.com/placements/' . $placement_id;
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ];

        $response = wp_remote_post($url, array(
            'headers' => $headers,
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $message = json_decode($body);

        return $message;
    }
     
    public function render_adcaptcha_options_page() {
        // Saves the Api Key and Placements ID in the wp db
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Verify the nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'adcaptcha_form_action')) {
                die('Invalid nonce');
            }

            $url = 'https://api.adcaptcha.com/v1/placements/' . $_POST['adcaptcha_option_name']['placement_id'];
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $_POST['adcaptcha_option_name']['api_key'],
            ];
    
            $response = wp_remote_get($url, array(
                'headers' => $headers,
            ));
            
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
            } else {
                $body = wp_remote_retrieve_body($response);
                $code = wp_remote_retrieve_response_code($response);
                $message = wp_remote_retrieve_response_message($response);
                $headers = wp_remote_retrieve_headers($response);
                $cookies = wp_remote_retrieve_cookies($response);

                echo "Body: $body";
                echo "Status Code: $code";
                echo "Status Message: $message";
                print_r($headers);
                print_r($cookies);
            }

            // $result = Settings::verify_input_data($_POST['adcaptcha_option_name']['api_key'], $_POST['adcaptcha_option_name']['placement_id']);
            // if ($result) {
            //     echo '<div class="result-message">Result: ' . $result . '</div>';
            //     return;
            // }

            update_option('adcaptcha_api_key', sanitize_text_field($_POST['adcaptcha_option_name']['api_key']));
            update_option('adcaptcha_placement_id', sanitize_text_field($_POST['adcaptcha_option_name']['placement_id']));
        }

        ?>
        <div>
            <div class="header container">
                <?php printf('<img src="%s" class="logo"/>', esc_url('https://assets.adcaptcha.com/mail/logo_gradient.png')); ?>
                <hr>
            </div>
            <div>
            <div class="integrating-description">
                <p>Before integrating, you must have an adCAPTCHA account and gone through the setup process. <a class="dashboard-link link" href="https://app.adcaptcha.com/login" target="_blank">Dashboard &rarr;</a><a class="documentation-link link" href="https://docs.adcaptcha.com/" target="_blank">Documentation &rarr;</a></p>
            </div>
            </div>
            <form method="post" class="form">
                <?php
                    echo '<label for="api_key" class="input-label">API Key</label>';
                    echo '<input type="text" id="api_key" class="input-field" name="adcaptcha_option_name[api_key]" value="' . esc_attr(get_option('adcaptcha_api_key')) . '" placeholder="API key">';
                ?>
                <?php
                    echo '<label for="placement_id" class="input-label">Placement ID</label>';
                    echo '<input type="text" id="placement_id" class="input-field" name="adcaptcha_option_name[placement_id]" value="' . esc_attr(get_option('adcaptcha_placement_id')) . '" placeholder="Placement ID">';
                ?>
                <div class="integrating-description">
                    <p>By default the captcha will be placed on the Login, Register, Lost Password and Comments forms.</p>
                </div>
                <?php wp_nonce_field('adcaptcha_form_action'); ?>
                <button type="submit" class="save-button">Save</button>
            </form>
        </div>
		<?php
    }

    public function change_admin_footer_text() {
        $current_year = gmdate('Y');
        return '© ' . $current_year . ' adCAPTCHA. All rights reserved.';
    }

    public function change_admin_footer_version() {
        return 'Version 1.0';
    }
}
