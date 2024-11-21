<?php

// Include Composer's autoloader (ensure the path to `vendor/autoload.php` is correct)
require_once 'vendor/autoload.php';

use AdCaptcha\Plugin\AdCaptchaPluginProba;

// Try instantiating the class
try {
    $plugin = new AdCaptchaPluginProba(); // This should autoload the class
    echo "Success: AdCaptchaPlugin class loaded and executed.\n";
} catch (Throwable $e) {
    // Catch any errors and display them
    echo "Error: " . $e->getMessage() . "\n";
}