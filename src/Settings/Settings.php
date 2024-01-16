<?php

namespace AdCaptcha\Settings;

class Settings {

    public function setup() {
        add_action('admin_menu', array($this, 'add_adcaptcha_options_page'));
        add_action( 'admin_enqueue_scripts', [ $this, 'add_styles_to_settings' ] );
    }
    
    public function add_adcaptcha_options_page() {
        add_options_page(
            'AdCaptcha',
            'AdCaptcha',
            'manage_options',
            'adcaptcha',
            array($this, 'render_adcaptcha_options_page')
        );
    }

    public function add_styles_to_settings() {
        wp_enqueue_style('adcaptcha-admin-styles', plugins_url('../styles/settings.css', __FILE__));
    }
     
    public function render_adcaptcha_options_page() {
        ?>
        <div class="admin">
            <div class="header container">
                <?php printf('<img src="%s" height="40px"/>', esc_url('https://assets.adcaptcha.com/mail/logo_gradient.png')); ?>
                <hr>
            </div>
            <form method="post" action="options.php">
                <input type="text" id="api_key" name="adcaptcha_option_name[api_key]" value="" placeholder="API key">
            </form>
        </div>
		<?php
    }
}