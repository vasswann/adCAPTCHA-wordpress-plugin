<?php

namespace AdCaptcha\Plugin\Registration;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;

class Registration {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("register_form");
    }
}
