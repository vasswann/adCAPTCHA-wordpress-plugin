<?php

namespace AdCaptcha\Plugin\Login;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;

class Login {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("login_form");
    }
}
