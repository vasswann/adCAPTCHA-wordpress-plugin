<?php
/**
 * Plugin Name: adCAPTCHA
 * Plugin URI: http://www.adcaptcha.com
 * Description: adCAPTCHA revolutionises your customer’s security experience, reducing time to solve by 94.33%.
 * Version: 1.0
 * Author: adCAPTCHA
 * 
 * @package adCAPTCHA
 */

require_once plugin_dir_path(__FILE__) . 'src/Instantiate.php';
require_once plugin_dir_path(__FILE__) . 'src/Settings/Settings.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Login.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Registration.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/PasswordReset.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Comments.php';
require_once plugin_dir_path(__FILE__) . 'src/Widget/AdCaptcha.php';
require_once plugin_dir_path(__FILE__) . 'src/Widget/Verify.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Woocommerce/Login.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Woocommerce/PasswordReset.php';
require_once plugin_dir_path(__FILE__) . 'src/Plugin/Woocommerce/Registration.php';

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
