<?php
/**
 * Plugin Name: adCAPTCHA
 * Plugin URI: https://adcaptcha.com
 * Description: Secure your site. Elevate your brand. Boost Ad Revenue.
 * Version: 1.0
 * Requires at least: 6.4.2
 * Requires PHP: 7.4
 * Author: adCAPTCHA
 * Author URI: https://adcaptcha.com
 * Text Domain: adCAPTCHA-wordpress-plugin
 * Domain Path: /languages
 * 
 * @package adCAPTCHA
 * @copyright 2024 adCAPTCHA. All rights reserved.
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

// Deletes data saved in the wp db on plugin uninstall
register_uninstall_hook( __FILE__, 'ad_captcha_uninstall' );

function ad_captcha_uninstall() {
    delete_option( 'adcaptcha_api_key' );
    delete_option( 'adcaptcha_placement_id' );
    delete_option( 'adcaptcha_success_token' );
}

$instantiate = new Instantiate();
$instantiate->setup();
