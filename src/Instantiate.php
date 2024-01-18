<?php

namespace AdCaptcha;

use AdCaptcha\Settings\Settings;
use AdCaptcha\Plugin\Login\Login;
use AdCaptcha\Plugin\Registration\Registration;
use AdCaptcha\Plugin\PasswordReset\PasswordReset;
use AdCaptcha\Plugin\Comments\Comments;

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
    }
}