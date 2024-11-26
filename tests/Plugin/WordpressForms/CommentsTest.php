<?php
/**
 * WordpressForms CommentsTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\WordpressForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use AdCaptcha\Plugin\Comments;
use AdCaptcha\Plugin\AdcaptchaPlugin;

class CommentsTest extends TestCase {
    private $comments;
    private $verifyMock;

    // Set up method to initialize the test environment, reset global mocked actions and filters, create a mock for the Verify class, instantiate the Comments class, and use reflection to inject the mock into the 'verify' property.
    public function setUp(): void {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];

        $this->verifyMock = $this->createMock(Verify::class);
        $this->comments = new Comments(); 

        $reflection = new \ReflectionClass($this->comments);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->comments, $this->verifyMock);

        
    }

    // Tear down method to reset the global mocked_actions and mocked_filters arrays, call WP_Mock tearDown for cleanup, and finalize with the parent tearDown method.
    public function tearDown(): void {
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        parent::tearDown();
    }

    // Test setup function to ensure the Comments class initializes properly, checks for the existence of the 'setup' method, verifies that mocked_actions and mocked_filters are populated with expected actions and filters, and confirms that the Comments instance is of type AdCaptchaPlugin.
    public function testSetup() {
        $this->assertTrue(method_exists($this->comments, 'setup'), 'Method setup does not exist');

        global $mocked_actions, $mocked_filters;
        $this->comments->setup();

        $this->assertNotEmpty($mocked_actions, 'Expected result to be an array');
        $this->assertNotEmpty($mocked_filters, 'Expected result to be an array');

        $this->assertContains(['hook' => 'comment_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'comment_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'comment_form_submit_field', 'callback' => [$this->comments, 'captcha_trigger_filter'], 'priority' => 10, 'accepted_args' => 1], $mocked_filters, 'Expected filter not found');

        $this->assertContains(['hook' => 'pre_comment_approved', 'callback' => [$this->comments, 'verify'], 'priority' => 20, 'accepted_args' => 2], $mocked_actions, 'Expected action not found');

        $this->assertInstanceof(AdcaptchaPlugin::class, $this->comments , 'Expected an instance of AdCaptchaPlugin');
    }

    // Check if the 'verify' method exists in the $this->comments object. Check if the 'verify' method is callable.Expect the 'verify_token' method to be called once and return true. Call the 'verify' method with a new WP_Error instance and comment data. Assert that the result is an instance of WP_Error. Assert that the WP_Error has no error codes
    public function testVerifySuccess() {
        $this->assertTrue(method_exists($this->comments, 'verify'), 'Method verify does not exist');
        $this->assertTrue(is_callable([$this->comments, 'verify']), 'Method verify is not callable');

        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        $result = $this->comments->verify(new \WP_Error, ['comment_post_ID' => 1]);
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');
        $this->assertEmpty($result->get_error_codes(), 'Expected WP_Error to have no error codes');
    }

    // Expect the 'verify_token' method to be called once and return false. Call the 'verify' method with a new WP_Error instance and comment data. Assert that the result is an instance of WP_Error. Assert that the WP_Error has error codes. Assert that 'adcaptcha_error' is included in the error codes. Assert that the error message matches the expected message
    public function testVerifyFailure() {
        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $result = $this->comments->verify(new \WP_Error, ['comment_post_ID' => 1]);
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');
        $this->assertNotEmpty($result->get_error_codes(), 'Expected WP_Error to have error codes');
        $this->assertContains('adcaptcha_error', $result->get_error_codes(), 'Expected error code not found');
        $this->assertEquals('Incomplete captcha, Please try again', $result->get_error_message(), 'Expected error message not found');
    }

    // Check if the 'captcha_trigger_filter' method exists in the $this->comments object, method is callable. Assert that the result contains 'submit_field', the result is a string, is not empty and the result contains 'submit_field' again for confirmation
    public function testCaptchaTriggerFilter() {
        $this->assertTrue(method_exists($this->comments, 'captcha_trigger_filter'), 'Method captcha_trigger_filter does not exist');
        $this->assertTrue(is_callable([$this->comments, 'captcha_trigger_filter']), 'Method captcha_trigger_filter is not callable');

        $result = $this->comments->captcha_trigger_filter('submit_field');
        
        $this->assertStringContainsString('submit_field', $result, 'Expected result to contain submit_field');
        $this->assertIsString($result, 'Expected result to be a string');
        $this->assertNotEmpty($result, 'Expected result to not be empty');
        $this->assertStringContainsString('submit_field', $result, 'Expected result to contain the AdCaptcha captcha trigger');   
    }
}
    