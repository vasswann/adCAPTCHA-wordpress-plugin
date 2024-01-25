<?php

namespace AdCaptcha;

use AdCaptcha\Settings\Settings;
use AdCaptcha\Plugin\Login\Login;
use AdCaptcha\Plugin\Registration\Registration;
use AdCaptcha\Plugin\PasswordReset\PasswordReset;
use AdCaptcha\Plugin\Comments\Comments;
use AdCaptcha\Plugin\Woocommerce\Login\Login as WoocommerceLogin;
use AdCaptcha\Plugin\Woocommerce\PasswordReset\PasswordReset as WoocommercePasswordReset;
use AdCaptcha\Plugin\Woocommerce\Checkout\Checkout as WoocommerceCheckout;
use AdCaptcha\Plugin\Woocommerce\Registration\Registration as WoocommerceRegistration;

class Instantiate {

    public function setup() {
        $settings = new Settings();
        $settings->setup();

        $login = new Login();
        $login->setup();

        $registration = new Registration();
        $registration->setup();

        $passwordReset = new PasswordReset();
        $passwordReset->setup();

        $comments = new Comments();
        $comments->setup();

        $woocommerceLogin = new WoocommerceLogin();
        $woocommerceLogin->setup();

        $woocommercePasswordReset = new WoocommercePasswordReset();
        $woocommercePasswordReset->setup();

        $woocommerceCheckout = new WoocommerceCheckout();
        $woocommerceCheckout->setup();

        $woocommerceRegistration = new WoocommerceRegistration();   
        $woocommerceRegistration->setup();
    }
}