<?php

namespace AdCaptcha\Plugin\FluentForms;

use AdCaptcha\Plugin\FluentForms\AdCaptchaElement;
use AdCaptcha\Plugin\AdCaptchaPlugin;

class Forms extends AdCaptchaPlugin {
    /**
     * Setup
     *
     * @return void
     */
    public function setup(){
      add_action('plugins_loaded', function() {
        require_once plugin_dir_path(__FILE__) . '/AdcaptchaElement.php';
        add_action('fluentform/loaded', function () {
          new AdCaptchaElement();
        });
      });
    }
}






