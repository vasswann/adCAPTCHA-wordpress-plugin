<?php

namespace AdCaptcha\Plugin\Comments;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;

class Comments {

    public function setup() {
        $adCaptcha = new AdCaptcha();
        $adCaptcha->setup("comment_form_submit_field");
    }
}
