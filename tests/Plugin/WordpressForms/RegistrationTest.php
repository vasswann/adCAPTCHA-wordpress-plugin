<?php
/**
 * WordpressForms RegistrationTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\WordpressForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use AdCaptcha\Plugin\Registration;

class RegistrationTest extends TestCase {
    private $registration;
    private $verifyMock;

    // Set up function to prepare mock objects and inject dependencies. Calls the parent setup method to initialize the test environment. Defines a global variable for mocked actions to track calls. Creates a mock instance of the Verify class for testing. Initializes a new Registration instance for testing. Uses reflection to access and modify private properties. Sets the 'verify' property of Registration to use the mock Verify instance
    public function setUp(): void {
        parent::setUp();
        global $mocked_actions;
        $mocked_actions = [];

        $this->verifyMock = $this->createMock(Verify::class);
        $this->registration = new Registration(); 

        $reflection = new \ReflectionClass($this->registration);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->registration, $this->verifyMock);   
    }

    // Resets the global mocked actions array; calls parent teardown to clean up the test environment.
    public function tearDown(): void {
        global $mocked_actions;
        $mocked_actions = [];
        parent::tearDown();
    }

    // Checks that the Registration class has a ‘setup’ method. Calls setup on Registration and verifies global mocked actions. Confirms that expected actions (register_form and registration_errors hooks with associated callbacks) are present in mocked actions, each with specified callback, priority, and accepted arguments.
    public function testSetup() {
        $this->assertTrue(method_exists($this->registration, 'setup'), 'Method setup does not exist');

        global $mocked_actions;
        $this->registration->setup();

        $this->assertNotEmpty($mocked_actions, 'Expected result to be an array');

        $this->assertContains(['hook' => 'register_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'register_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'register_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'registration_errors', 'callback' => [$this->registration, 'verify'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');
    }

    // Checks that the Registration class has a ‘verify’ method and that it’s callable. Sets an expectation for verify_token to return true once. Creates a WP_Error object for error tracking. Calls verify on Registration and asserts the result is a WP_Error instance with no error codes (indicating verification success).
    public function testVerifySuccess() {
        $this->assertTrue(method_exists($this->registration, 'verify'), 'Method verify does not exist');
        $this->assertTrue(is_callable([$this->registration, 'verify']), 'Method verify is not callable');

        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        $errors = new \WP_Error();
        $result = $this->registration->verify($errors);

        $this->assertInstanceof(\WP_Error::class, $result, 'Expected an instance of WP_Error');
        $this->assertEmpty($result->get_error_codes(), 'Expected no errors');
    }

    // Tests verify method when verification fails. Sets expectation for verify_token to return false once. Initializes WP_Error for error tracking. Calls verify on Registration and checks result is a WP_Error with error codes. Asserts ‘adcaptcha_error’ code exists and its message matches ‘Incomplete captcha, Please try again.’
    public function testVerifyFailure() {
        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $errors = new \WP_Error();
        $result = $this->registration->verify($errors);

        $this->assertInstanceof(\WP_Error::class, $result, 'Expected an instance of WP_Error');
        $this->assertNotEmpty($result->get_error_codes(), 'Expected errors');

        $errorCodes = $result->get_error_codes();

        $this->assertContains('adcaptcha_error', $errorCodes, 'Expected error code not found');

        $this->assertEquals('Incomplete captcha, Please try again.', $result->get_error_message('adcaptcha_error'));
    }
}