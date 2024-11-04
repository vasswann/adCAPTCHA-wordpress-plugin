<?php
/**
 * WordpressForms LoginTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\WordpressForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use AdCaptcha\Plugin\Login;
use WP_Mock;
use Mockery;    

class LoginTest extends TestCase {
    private $login;
    private $verifyMock;

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions;
        $mocked_actions = [];
        WP_Mock::setUp();

        $this->verifyMock = $this->createMock(Verify::class);
        $this->login = new Login(); 

        $reflection = new \ReflectionClass($this->login);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->login, $this->verifyMock);

        
    }

    public function tearDown(): void {
        global $mocked_actions;
        $mocked_actions = [];
        WP_Mock::tearDown();
        parent::tearDown();
    }

    // This test verifies that the `setup` method in the `$this->login` object: Exists and can be called without errors.Registers expected WordPress actions with correct hooks, priorities, and callbacks.Ensures actions are structured correctly, including closures and specific callback references.
    public function testSetup() {
       $this->assertTrue(method_exists($this->login, 'setup'), 'Method setup does not exist');
       global $mocked_actions;
       $this->login->setup();

       $this->assertNotEmpty($mocked_actions, 'Expected result to be an array');

       $found = false;
       foreach($mocked_actions as $action) {
           if(
            isset($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']) &&
            $action['hook'] === 'login_enqueue_scripts' &&
            $action['priority'] === 10 &&
            $action['accepted_args'] === 1 &&
            is_object($action['callback']) &&
            ($action['callback'] instanceof \Closure)
           ) {
                $found = true;
                break;
           }   
       }

       $this->assertTrue($found, 'Expected array structure was not found.');
       
       $this->assertContains(['hook' => 'login_enqueue_scripts', 'callback' => [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'login_enqueue_scripts', 'callback' => [$this->login, 'disable_safari_auto_submit'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'login_form', 'callback' => [AdCaptcha::class, 'captcha_trigger'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'wp_authenticate_user', 'callback' => [$this->login, 'verify'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);


    }

    // Checks that the `verify` method exists on the `$this->login` object and is callable. Mocks the `verify_token` method to always return `true` for this test. Calls the `verify` method and ensures it returns a `WP_Error` object. Verifies that the returned `WP_Error` object has no error codes, indicating success
    public function testVerifySuccess() {
        $this->assertTrue(method_exists($this->login, 'verify'), 'Method verify does not exist');
        $this->assertTrue(is_callable([$this->login, 'verify']), 'Method verify is not callable');

        $this->verifyMock->method('verify_token')
            ->willReturn(true);

        $result = $this->login->verify(new \WP_Error());
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');
        $this->assertEmpty($result->get_error_codes(), 'Expected WP_Error to have no errors');
    }

    // Mocks the `verify_token` method to return `false`, simulating a failed verification. Calls the `verify` method and ensures it returns a `WP_Error` object. Verifies that the `WP_Error` object contains an error message for 'adcaptcha_error'. Checks that the error code 'adcaptcha_error' is present in the `WP_Error` object. Confirms that the error message matches the expected message: 'Incomplete captcha, Please try again.'
    public function testVerifyFailure() {
        $this->verifyMock->method('verify_token')
            ->willReturn(false);

        $result = $this->login->verify(new \WP_Error());
        $this->assertInstanceOf(\WP_Error::class, $result, 'Expected result to be an instance of WP_Error');
        $this->assertNotEmpty($result->get_error_message('adcaptcha_error'), 'Expected WP_Error to have an error message');
        $this->assertEquals(['adcaptcha_error'], $result->get_error_codes(), 'Expected WP_Error to have errors');
        $this->assertEquals('Incomplete captcha, Please try again.', $result->get_error_message('adcaptcha_error'), 'Expected WP_Error to have the expected error message');   
    }

    // Checks if the `disable_safari_auto_submit` method exists on `$this->login`. Initializes `$captureScript` to capture the inline script added by `wp_add_inline_script`. Mocks `wp_add_inline_script` to intercept the script registered with the handle 'adcaptcha-script'. Calls `disable_safari_auto_submit` to trigger script injection. Verifies that `$captureScript` is not empty, confirming that a script was captured. Asserts that `$captureScript` contains specific JavaScript code snippets related to: Adding a DOMContentLoaded listener, Selecting the `#loginform` and `#wp-submit` elements, Disabling the submit button by default, Adding a form submit event listener to check `successToken`
    public function testDisableSafariAutoSubmit() {
        $this->assertTrue(method_exists($this->login, 'disable_safari_auto_submit'), 'Method disable_safari_auto_submit does not exist');

        $captureScript = '';

        WP_Mock::userFunction('wp_add_inline_script', [
            'times' => 1,
            'return' => function($handle, $script) use(& $captureScript) {
                if($handle === 'adcaptcha-script') {
                    $captureScript = $script;
                }
                return true;
            }
        ]);

        $this->login->disable_safari_auto_submit();

        $this->assertNotEmpty($captureScript, 'Expected script to be captured');

        $this->assertStringContainsString('document.addEventListener("DOMContentLoaded", function() {', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('var form = document.querySelector("#loginform");', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('var submitButton = document.querySelector("#wp-submit");', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('if (form) {', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('if (submitButton) {', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('submitButton.disabled = true;', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('form.addEventListener("submit", function(event) {', $captureScript, 'Expected script to contain the expected string');

        $this->assertStringContainsString('if (!window.adcap.successToken) {', $captureScript, 'Expected script to contain the expected string');
    }
}