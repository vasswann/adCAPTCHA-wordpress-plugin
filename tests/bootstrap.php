<?php
require_once __DIR__ . '/../vendor/autoload.php';

\Brain\Monkey\setUp();

register_shutdown_function(function () {
    \Brain\Monkey\tearDown();
});