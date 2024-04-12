<?php

namespace AdCaptcha\Plugin;

abstract class Plugin {

    abstract public function setup();

    public function __construct() {
		$this->setup();
    }
}