<?php

namespace AdCaptcha\Plugin\PasswordReset;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;

class PasswordReset {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("lostpassword_form");
    }
}
