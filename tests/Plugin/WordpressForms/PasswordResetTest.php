<?php
/**
 * WordpressForms PasswordResetTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\WordpressForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use AdCaptcha\Plugin\PasswordReset;
use AdCaptcha\Plugin\AdcaptchaPlugin;

class PasswordResetTest extends TestCase {
    private $passwordReset;
    private $verifyMock;

    // Set up method to prepare the test environment by calling the parent setup, initializing a global mocked_actions array, creating a mock for the Verify class, instantiating PasswordReset, and using reflection to set the private 'verify' property to the mock instance.
    public function setUp(): void {
        parent::setUp();
        global $mocked_actions;
        $mocked_actions = [];

        $this->verifyMock = $this->createMock(Verify::class);
        $this->passwordReset = new PasswordReset(); 

        $reflection = new \ReflectionClass($this->passwordReset);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->passwordReset, $this->verifyMock);   
    }

    // Tear down method to clean up the global mocked_actions array and call the parent tearDown method to finalize the test cleanup process.
    public function tearDown(): void {
        global $mocked_actions;
        $mocked_actions = [];
        parent::tearDown();
    }

 
    // Test setup function to ensure the PasswordReset class initializes correctly, checks for the existence of the 'setup' method, verifies that mocked_actions contains expected hooks and callbacks for password reset functionality, and confirms that the PasswordReset instance is of type AdCaptchaPlugin.
    public function testSetup() {
        $this->assertTrue(method_exists($this->passwordReset, 'setup'), 'Method setup does not exist');

        global $mocked_actions;
        $this->passwordReset->setup();

        $this->assertNotEmpty($mocked_actions, 'Expected result to be an array');

        $this->assertContains(['hook' => 'lostpassword_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'lostpassword_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'lostpassword_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'lostpassword_post', 'callback' => [$this->passwordReset, 'verify'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertInstanceof(AdcaptchaPlugin::class, $this->passwordReset , 'Expected an instance of AdCaptchaPlugin');
    }

    // Test verify method to ensure the PasswordReset class's verify function exists and is callable, mocks the verify_token method to return true, and checks that the result is an instance of WP_Error with no error codes.
    public function testVerifySuccess() {
        $this->assertTrue(method_exists($this->passwordReset, 'verify'), 'Method verify does not exist');
        $this->assertTrue(is_callable([$this->passwordReset, 'verify']), 'Method verify is not callable');

        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        $result = $this->passwordReset->verify(new \WP_Error);
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');
        $this->assertEmpty($result->get_error_codes(), 'Expected WP_Error to have no error codes');
    }

    // Test verify method to ensure the PasswordReset class's verify function correctly handles failure by mocking the verify_token method to return false, verifying that the result is an instance of WP_Error with error codes including 'adcaptcha_error' and the appropriate error message.
    public function testVerifyFailure() {
        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $result = $this->passwordReset->verify(new \WP_Error);
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');
        $this->assertNotEmpty($result->get_error_codes(), 'Expected WP_Error to have error codes');
        $this->assertContains('adcaptcha_error', $result->get_error_codes(), 'Expected error code not found');
        $this->assertEquals('Incomplete captcha, Please try again.', $result->get_error_message(), 'Expected error message not found');
    }
}