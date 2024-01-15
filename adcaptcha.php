<?php
/**
 * Plugin Name: AdCaptcha
 * Plugin URI: http://www.adcaptcha.com
 * Description: AdCaptcha revolutionises your customer’s security experience, reducing time to solve by 94.33%.
 * Version: 1.0
 * Author: AdCaptcha
 * 
 * @package AdCaptcha
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the settings file
require_once plugin_dir_path( __FILE__ ) . 'src/Settings/Settings.php';

// Instantiate the Settings class and call its setup method
$settings = new \AdCaptcha\Settings\Settings();
$settings->setup();