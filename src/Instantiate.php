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
        
        $classes = [
            'Wordpress_Login' => [
                'instance' => new Login(),
                'plugin' => [],
            ],
            'Wordpress_Register' => [
                'instance' => new Registration(),
                'plugin' => [],
            ],
            'Wordpress_ForgotPassword' => [
                'instance' => new PasswordReset(),
                'plugin' => [],
            ],
            'Wordpress_Comments' => [
                'instance' => new Comments(),
                'plugin' => [],
            ],
            'Woocommerce_Login' => [
                'instance' => new WoocommerceLogin(),
                'plugin' => [ 'woocommerce/woocommerce.php' ],
            ],
            'Woocommerce_ForgotPassword' => [
                'instance' => new WoocommercePasswordReset(),
                'plugin' => [ 'woocommerce/woocommerce.php' ],
            ],
            'Woocommerce_Register' => [
                'instance' => new WoocommerceRegistration(),
                'plugin' => [ 'woocommerce/woocommerce.php' ],
            ],
            'ContactForm7_Forms' => [
                'instance' => new ContactForm7(),
                'plugin' => [ 'contact-form-7/wp-contact-form-7.php' ],
            ],
            'Mailchimp_Forms' => [
                'instance' => new MailchimpForms(),
                'plugin' => [ 'mailchimp-for-wp/mailchimp-for-wp.php' ],
            ],
            'NinjaForms_Forms' => [
                'instance' => new NinjaForms(),
                'plugin' => [ 'ninja-forms/ninja-forms.php' ],
            ]
        ];

        $selected_plugins = get_option('adcaptcha_selected_plugins') ? get_option('adcaptcha_selected_plugins') : array();

        $settings = new Settings();
        $settings->setup();

        if (get_option('adcaptcha_render_captcha') === '1') {
            foreach ($selected_plugins as $selected_plugin) {
                if (isset($classes[$selected_plugin])) {
                    foreach ($classes[$selected_plugin]['plugin'] as $plugin) {
                        if (is_plugin_active($plugin)) {
                            $classes[$selected_plugin]['instance']->setup();
                        }
                    }
                }
            }
        }
    }
}
