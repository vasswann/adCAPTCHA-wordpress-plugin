<?php

namespace AdCaptcha;

use AdCaptcha\Settings\Settings;
use AdCaptcha\Plugin\Login\Login;
use AdCaptcha\Plugin\Registration\Registration;
use AdCaptcha\Plugin\PasswordReset\PasswordReset;
use AdCaptcha\Plugin\Comments\Comments;
use AdCaptcha\Plugin\Woocommerce\Login\Login as WoocommerceLogin;
use AdCaptcha\Plugin\Woocommerce\PasswordReset\PasswordReset as WoocommercePasswordReset;
use AdCaptcha\Plugin\Woocommerce\Registration\Registration as WoocommerceRegistration;
use AdCaptcha\Plugin\ContactFrom7\Froms\Forms as ContactForm7;
use AdCaptcha\Plugin\Mailchimp\Froms\Forms as MailchimpForms;
use AdCaptcha\Plugin\NinjaForms\Froms\Forms as NinjaForms;

class Instantiate {

    public function setup() {

        if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
        
        $classes = array(
            'Wordpress_Login' => new Login(),
            'Wordpress_Register' => new Registration(),
            'Wordpress_ForgotPassword' => new PasswordReset(),
            'Wordpress_Comments' => new Comments(),
            'Woocommerce_Login' => new WoocommerceLogin(),
            'Woocommerce_ForgotPassword' => new WoocommercePasswordReset(),
            'Woocommerce_Register' => new WoocommerceRegistration(),
            'ContactForm7_Forms' => new ContactForm7(),
            'Mailchimp_Forms' => new MailchimpForms(),
        );

        $selected_plugins = get_option('adcaptcha_selected_plugins') ? get_option('adcaptcha_selected_plugins') : array();

        $settings = new Settings();
        $settings->setup();

        if (get_option('adcaptcha_render_captcha') === '1') {
            foreach ($selected_plugins as $selected_plugin) {
                if (isset($classes[$selected_plugin])) {
                    $classes[$selected_plugin]->setup();
                }
            }
        }

        // if ( class_exists( 'Ninja_Forms' ) && method_exists( 'Ninja_Forms', 'instance' ) && class_exists('NF_Abstracts_Field')) {
        //     add_action('plugins_loaded', function() {
        //         $ninja = new NinjaForms();
        //         $ninja->setup();
        //     });
        // }
    }
}
