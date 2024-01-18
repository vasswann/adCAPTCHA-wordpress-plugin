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
require_once plugin_dir_path(__FILE__) . 'src/Settings/Settings.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Login.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Registration.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/PasswordReset.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Comments.php';
require_once plugin_dir_path(__FILE__) . 'src/Widget/AdCaptcha.php';
require_once plugin_dir_path(__FILE__) . 'src/Widget/Verify.php';

use AdCaptcha\Instantiate;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action('wp_ajax_nopriv_verify_token', 'AdCaptcha\Widget\Verify\Verify::verify_token');
add_action('wp_ajax_verify_token', 'AdCaptcha\Widget\Verify\Verify::verify_token');

$instantiate = new Instantiate();
$instantiate->setup();
