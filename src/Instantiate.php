<?php

namespace AdCaptcha;

require_once plugin_dir_path(__FILE__) . 'Settings/Settings.php';
use AdCaptcha\Settings\Settings;


require_once plugin_dir_path(__FILE__) . 'Plugin/Login.php';
use AdCaptcha\Plugin\Login\Login;

class Instantiate {

    public function setup() {
        $settings = new Settings();
        $settings->setup();

        $login = new Login();
        $login->setup();
    }
}