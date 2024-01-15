<?php

namespace AdCaptcha\Settings;

class Settings {

    public function setup() {
        add_action('admin_menu', array($this, 'add_adcaptcha_options_page'));
    }
    
    public function add_adcaptcha_options_page() {
        add_options_page(
            'AdCaptcha Settings',
            'AdCaptcha',
            'manage_options',
            'adcaptcha',
            array($this, 'render_adcaptcha_options_page')
        );
    }
     
    public function render_adcaptcha_options_page() {
        echo '<div class="wrap">';
        echo '<h1>AdCaptcha Settings</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('adcaptcha_option_group');
        do_settings_sections('adcaptcha');
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}