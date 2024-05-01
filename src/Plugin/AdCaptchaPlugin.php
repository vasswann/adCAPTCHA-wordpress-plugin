<?php

namespace AdCaptcha\AdCaptchaPlugin;

abstract class AdCaptchaPlugin {

    abstract public function setup();

    public function __construct() {
		$this->setup();
    }
}
