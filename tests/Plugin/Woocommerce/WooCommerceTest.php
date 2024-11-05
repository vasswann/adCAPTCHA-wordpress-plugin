<?php
/**
 * WooCommerceTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\Woocommerce;

// Load the WP_Error class from the WordPress core by navigating up six directories to the WordPress root directory.
$basedir = dirname(__DIR__, 6);
require_once($basedir . '/wp-includes/class-wp-error.php');

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\Woocommerce\Login;
use AdCaptcha\Plugin\Woocommerce\PasswordReset;
use AdCaptcha\Plugin\Woocommerce\Registration;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use WP_Mock;
use Mockery;

class WooCommerceTest extends TestCase {
    private $login;
    private $passwordReset;
    private $registration;
    private $verifyLoginMock;
    private $verifyResetMock;
    private $verifyRegistrationMock;

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions, $mocked_filters, $mocked_remove_actions;
        $mocked_actions = [];
        $mocked_filters = [];
        $mocked_remove_actions = [];
        WP_Mock::setUp();

        $this->verifyLoginMock = $this->createMock(Verify::class);
        $this->verifyResetMock = $this->createMock(Verify::class);
        $this->verifyRegistrationMock = $this->createMock(Verify::class);

        $this->login = new Login($this->verifyLoginMock);
        $this->passwordReset = new PasswordReset($this->verifyResetMock);
        $this->registration = new Registration($this->verifyRegistrationMock);
    }

    public function tearDown(): void {
        global $mocked_actions, $mocked_filters;
        $mocked_actions = null;
        $mocked_filters = null;
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    // Test the setup method of the Login class to ensure it registers the correct WooCommerce hooks and filters.
    public function testSetupLogin() {
        $this->assertTrue(method_exists($this->login, 'setup'), 'Method setup does not exist in the login class');

        global $mocked_actions, $mocked_filters;
        $this->login->setup();

        $this->assertIsArray($mocked_actions, 'Expected result to be an array');
        $this->assertIsArray($mocked_filters, 'Expected result to be an array');

        $this->assertContains(['hook' => 'woocommerce_login_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_login_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_login_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_process_login_errors', 'callback' => [$this->login, 'verify'], 'priority' => 10, 'accepted_args' => 3 ], $mocked_filters);
    }

    // Test the verify method of the Login class to ensure it correctly calls the token verification logic and handles login success, while verifying that the appropriate action is removed. Use Reflection to access the private 'verify' property of the Login class and replace it with a mock instance for testing.
    public function testVerifyLoginSuccess() {
        $this->assertTrue(method_exists($this->login, 'verify'), 'Method verify does not exist in the login class');
        $this->assertTrue(is_callable([$this->login, 'verify']), 'Method verify is not callable');
        global $mocked_remove_actions;

        $reflection = new \ReflectionClass($this->login);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->login, $this->verifyLoginMock);

        $this->verifyLoginMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        $result = $this->login->verify(null, 'username', 'password');
        $this->assertNull($result, 'Expected result to be null');
    
        $this->assertContains(['hook' => 'wp_authenticate_user', 'callback' => [null, 'verify'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_remove_actions);
    }

    // Test the verify method of the Login class to ensure it correctly handles login failure by returning a WP_Error instance when token verification fails, and checks the error code and message.
    public function testVerifyLoginFailure() {

        $reflection = new \ReflectionClass($this->login);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->login, $this->verifyLoginMock);

        $this->verifyLoginMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $result = $this->login->verify(null, 'username', 'password');
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');

        $this->assertEquals('adcaptcha_error', $result->get_error_code(), 'Expected error code to be adcaptcha_error');
        $this->assertEquals('Incomplete captcha, Please try again.', $result->get_error_message(), 'Expected error message to be Incomplete captcha, Please try again.');
    }

    // Test the setup method of the PasswordReset class to ensure it registers the correct WooCommerce hooks and filters, verifying that the expected actions and filters are added to the global arrays.
    public function testSetupPasswordReset() {
        $this->assertTrue(method_exists($this->passwordReset, 'setup'), 'Method setup does not exist in the password reset class');

        global $mocked_actions, $mocked_filters;
        $this->passwordReset->setup();

        $this->assertContains(['hook' => 'woocommerce_lostpassword_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_lostpassword_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_lostpassword_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'wp_loaded', 'callback' => [$this->passwordReset, 'remove_wp_action'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'allow_password_reset', 'callback' => [$this->passwordReset, 'verify'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_filters);
    }

    // Test the remove_wp_action method of the PasswordReset class to ensure it correctly removes the specified action from the WordPress action hooks and verifies that the appropriate action is added to the mocked remove actions.
    public function testRemoveWPAction() {
        $this->assertTrue(method_exists($this->passwordReset, 'remove_wp_action'), 'Method remove_wp_action does not exist in the password reset class');
        $this->assertTrue(is_callable([$this->passwordReset, 'remove_wp_action']), 'Method remove_wp_action is not callable');
        global $mocked_remove_actions;

        $this->passwordReset->remove_wp_action();
        $this->assertContains(['hook' => 'lostpassword_post', 'callback' => [null, 'verify'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_remove_actions);
    }

    // Test the verify method of the PasswordReset class to ensure it correctly calls the token verification logic for successful password resets, replacing the private verify property with a mock, and verifies that the result is null upon success.
    public function testVerifyPasswordResetSuccess() {
        $this->assertTrue(method_exists($this->passwordReset, 'verify'), 'Method verify does not exist in the password reset class');
        $this->assertTrue(is_callable([$this->passwordReset, 'verify']), 'Method verify is not callable');

        $reflection = new \ReflectionClass($this->passwordReset);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->passwordReset , $this->verifyLoginMock);

        $this->verifyLoginMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        $result = $this->passwordReset->verify(null);
        $this->assertNull($result, 'Expected result to be null');
    }

    // Test the verify method of the PasswordReset class to ensure it correctly handles password reset failure by returning a WP_Error instance when token verification fails, and checks the error code and message.
    public function testVerifyPasswordResetFailure() {
        $reflection = new \ReflectionClass($this->passwordReset);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->passwordReset, $this->verifyLoginMock);

        $this->verifyLoginMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $result = $this->passwordReset->verify(null);
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');

        $this->assertEquals('adcaptcha_error', $result->get_error_code(), 'Expected error code to be adcaptcha_error');
        $this->assertEquals('Incomplete captcha, Please try again.', $result->get_error_message(), 'Expected error message to be Incomplete captcha, Please try again.');
    }

    // Test the setup method of the Registration class to ensure it registers the correct WooCommerce hooks and filters, verifying that the expected actions and filters are added to the global arrays.
    public function testSetupRegistration() {
        $this->assertTrue(method_exists($this->registration, 'setup'), 'Method setup does not exist in the registration class');

        global $mocked_actions, $mocked_filters;
        $this->registration->setup();

        $this->assertContains(['hook' => 'woocommerce_register_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_register_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_register_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_actions);
        $this->assertContains(['hook' => 'woocommerce_registration_errors', 'callback' => [$this->registration, 'verify'], 'priority' => 10, 'accepted_args' => 3 ], $mocked_filters);
    }

    // Test the verify method of the Registration class to ensure it correctly calls the token verification logic for successful registrations, replacing the private verify property with a mock, and verifies that the result is null upon success while checking that the appropriate action is removed from the global mocked actions.
    public function testVerifyRegistrationSuccess() {
        $this->assertTrue(method_exists($this->registration, 'verify'), 'Method verify does not exist in the registration class');
        $this->assertTrue(is_callable([$this->registration, 'verify']), 'Method verify is not callable');

        $reflection = new \ReflectionClass($this->registration);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->registration, $this->verifyRegistrationMock );

        $this->verifyRegistrationMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        global $mocked_remove_actions;

        $result = $this->registration->verify(null, 'username', 'email');

        $this->assertContains(['hook' => 'registration_errors', 'callback' => [null, 'verify'], 'priority' => 10, 'accepted_args' => 1 ], $mocked_remove_actions);
        $this->assertNull($result, 'Expected result to be null');
    }

    // Test the verify method of the Registration class to ensure it correctly handles registration failure by returning a WP_Error instance when token verification fails, and checks the error code and message.
    public function testVerifyRegistrationFailure() {
        $reflection = new \ReflectionClass($this->registration);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->registration, $this->verifyRegistrationMock);

        $this->verifyRegistrationMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $result = $this->registration->verify(null, 'username', 'email');
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');

        $this->assertEquals('adcaptcha_error', $result->get_error_code(), 'Expected error code to be adcaptcha_error');
        $this->assertEquals('Incomplete captcha, Please try again.', $result->get_error_message(), 'Expected error message to be Incomplete captcha, Please try again.');
    }
}