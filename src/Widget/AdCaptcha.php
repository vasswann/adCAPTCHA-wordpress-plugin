<?php

namespace AdCaptcha\Widget\AdCaptcha;

class AdCaptcha {

    public function setup($string) {
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action($string, array($this, 'captcha_trigger'));
    }

    public function enqueue_scripts() {
        echo '<script src="https://widget.adcaptcha.com/index.js" defer></script>';
        echo '<script type="text/javascript">
            window.onload = function() {
                if(window.adcap) {
                    window.adcap.init();
                }
            }
        </script>';
    }

    public function captcha_trigger() {
        echo '<div data-adcaptcha="' . esc_attr(get_option('adcaptcha_placement_id')) . '" style="margin-bottom: 20px;"></div>';
    }

}