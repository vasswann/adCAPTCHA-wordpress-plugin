<?php

namespace AdCaptcha\Widget\AdCaptcha;

class AdCaptcha {

    public static function enqueue_scripts($enableSubmitButton = false) {
        wp_enqueue_script('adcaptcha-script', 'https://widget.adcaptcha.com/index.js', array('jquery'), null, true);
    
        $ajax_nonce = wp_create_nonce("adcaptcha_nonce");
        wp_localize_script('adcaptcha-script', 'adcaptcha_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => $ajax_nonce,
        ));
    
        echo '<script type="text/javascript">
            window.onload = function() {
                if (window.adcap) {
                    ' . self::setupScript($enableSubmitButton) . '
                }
            }
        </script>';
    }
    
    public static function setupScript($enableSubmitButton = false) {
        return 'window.adcap.init();
        window.adcap.setupTriggers({
            onComplete: () => {
                jQuery.ajax({
                    url: adcaptcha_vars.ajax_url,
                    type: "POST",
                    data: {
                        action: "save_token",
                        successToken: window.adcap.successToken,
                        nonce: adcaptcha_vars.nonce,
                    }
                });
                ' . ($enableSubmitButton ? self::enable_submit_button() : '') . '
            }
        });';
    }

    public static function enable_submit_button() {
        return 'var submitButton = document.querySelector("#wp-submit");
        if (submitButton) {
            submitButton.disabled = false;
        }';
    }

    public static function ob_captcha_trigger() {
		ob_start();
        self::captcha_trigger();

        return ob_get_clean();
    }

    public static function captcha_trigger() {
        echo '<div data-adcaptcha="' . esc_attr(get_option('adcaptcha_placement_id')) . '" style="margin-bottom: 20px; max-width: 400px;"></div>';
    }
}
