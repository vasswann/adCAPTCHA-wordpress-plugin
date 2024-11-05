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

class PasswordResetTest extends TestCase {
    private $passwordReset;
    private $verifyMock;

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

    public function tearDown(): void {
        global $mocked_actions;
        $mocked_actions = [];
        parent::tearDown();
    }

 
    public function testSetup() {
        $this->assertTrue(method_exists($this->passwordReset, 'setup'), 'Method setup does not exist');

        global $mocked_actions;
        $this->passwordReset->setup();

        $this->assertNotEmpty($mocked_actions, 'Expected result to be an array');

        $this->assertContains(['hook' => 'lostpassword_form', 'callback' => [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'lostpassword_form', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'lostpassword_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');

        $this->assertContains(['hook' => 'lostpassword_post', 'callback' => [$this->passwordReset, 'verify'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions, 'Expected action not found');
    }

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