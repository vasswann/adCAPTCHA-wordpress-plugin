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

require_once plugin_dir_path(__FILE__) . 'src/Instantiate.php';
use AdCaptcha\Instantiate;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$instantiate = new Instantiate();
$instantiate->setup();
