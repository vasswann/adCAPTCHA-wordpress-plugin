<?php

namespace AdCaptcha\Plugin\Login;

class Login {

    public function setup() {
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('login_form', array($this, 'login_form'));
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

    public function login_form() {
        echo '<div data-adcaptcha="PLC-01HM8Z16PFW6SY2SVWKZYAEPR6"></div>';
    }

}
