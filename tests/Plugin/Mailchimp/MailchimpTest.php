<?php
/**
 * MailchimpTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\Mailchimp;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\Mailchimp\Forms;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use WP_Mock;
use Mockery;
use MC4WP_Form;
use MC4WP_Form_Element;

class MailchimpTest extends TestCase
{
    protected Forms $forms;

    protected function setUp(): void
    {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        WP_Mock::setUp();
        $this->forms = new Forms();
    }

    protected function tearDown(): void
    {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    // We are testing the setup method of the Forms class to ensure that it correctly registers various actions and filters. We verify that the setup method exists and assert that specific hooks are registered with their associated callbacks, priorities, and accepted arguments. We check that the expected actions and filters are present in the global arrays $mocked_actions and $mocked_filters.
    public function testSetup()
    {
        $this->forms->setup();
        global $mocked_actions, $mocked_filters;

        $this->assertTrue(method_exists($this->forms, 'setup'));

        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 9, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [$this->forms, 'block_submission'], 'priority' => 9, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'mc4wp_form_content', 'callback'=> [$this->forms, 'add_hidden_input'], 'priority' => 20, 'accepted_args' => 3], $mocked_filters);

        $this->assertContains(['hook' => 'admin_enqueue_scripts', 'callback'=> [$this->forms, 'form_preview_setup_triggers'], 'priority' => 9, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'mc4wp_form_errors', 'callback'=> [$this->forms, 'verify'], 'priority' => 10, 'accepted_args' => 2], $mocked_filters);

         // Custom assertion for mc4wp_form_messages hook filter to chekc if 
         $found = false;
         foreach ($mocked_filters as $filter) {
             if ($filter['hook'] === 'mc4wp_form_messages' &&
                 $filter['priority'] === 10 &&
                 $filter['accepted_args'] === 1 &&
                 is_callable($filter['callback']) &&
                 $filter['callback'] instanceof \Closure) {
                 $found = true;
                 break;
             }
         }

        $this->assertTrue($found, 'Expected filter for mc4wp_form_messages not found.');

    }

    // We are testing that a hidden input field is correctly added before an existing submit button in the HTML. The expected output is the input field followed by the submit button. The actual output is the result of the add_hidden_input method.
    public function testAddHiddenInput()
    {
       $input_html = '<input type="submit">';
       $expected_output = '<input type="hidden" class="adcaptcha_successToken" name="adcaptcha_successToken">' . $input_html;
       $form_instance = new Forms;
       $actual_output = $form_instance->add_hidden_input($input_html, \Mockery::mock(MC4WP_Form::class), \Mockery::mock(MC4WP_Form_Element::class));
       $this->assertEquals($expected_output, $actual_output, "Expected output does not match actual output");
    }

    // We are testing that no hidden input field is added when there is no submit button present in the form HTML
    public function testAddHiddenInput_NoSubmitButton() {
        $input_html = '<form><input type="text" name="name"></form>';
        $expected_output = '<form><input type="text" name="name"></form>';
        
        $form_instance = new Forms(); 
        
        $output_html = $form_instance->add_hidden_input($input_html, \Mockery::mock(MC4WP_Form::class), \Mockery::mock(MC4WP_Form_Element::class));

        $this->assertEquals($expected_output, $output_html, "Hidden input field was added even though there was no submit button.");
    }

    // public function testVerifyValidToken()
    // {
        // $successToken = 'test-token';
        // $_POST['adcaptcha_successToken'] = $successToken; // Simulate the POST data
        
        // // Mocking Verify behavior directly
        // $verifyMock = Mockery::mock(Verify::class);
        // $verifyMock->shouldReceive('verify_token')
        //     ->with($successToken)
        //     ->andReturn(true); // Simulate a successful verification

        // // Here you would need to ensure that the Forms class uses this mocked Verify object.

        // // If you cannot inject it, you'll face issues because the Forms class will create a new instance of Verify.

        // $errors = []; // Start with an empty errors array
        // $formMock = Mockery::mock(MC4WP_Form::class); // Mock the MC4WP_Form class

        // $result = $this->forms->verify($errors, $formMock);

        // // Assert that 'invalid_captcha' is not in the result
        // $this->assertNotContains('invalid_captcha', $result, "Errors should not contain 'invalid_captcha' for valid token.");
    // }

    public function testVerifyInvalidToken()
    {
        $verifyMock = Mockery::mock(Verify::class);
        $verifyMock->shouldReceive('verify_token')
                ->andReturn(false); 

        $errors = [];
        $errors = $this->forms->verify($errors, Mockery::mock(MC4WP_Form::class));

        $this->assertContains('invalid_captcha', $errors, "Errors should contain 'invalid_captcha' for invalid token.");
    }

    // We are testing that the script 'adcaptcha-script' is registered exactly once. Testing the localization of the script to ensure the error message is correctly passed. It checks that the inline script is added and contains the necessary logic to block form submission. Assertions on captured script content verify the presence of event listeners and conditions for preventing submission. Checking method existence to ensure the block_submission method is defined.
    public function testBlockSubmission()
    {
        if (!defined('ADCAPTCHA_ERROR_MESSAGE')) {
            define('ADCAPTCHA_ERROR_MESSAGE', 'Please complete the CAPTCHA');
        }
        $capturedScript = '';

        WP_Mock::userFunction('wp_register_script', [
            'args' => ['adcaptcha-script', '', [], false, true],
            'times' => 1
        ]);

        WP_Mock::userFunction('wp_localize_script', [
            'args' => ['adcaptcha-script', 'adCaptchaErrorMessage', array(ADCAPTCHA_ERROR_MESSAGE)],
            'times' => 1
        ]);

        WP_Mock::userFunction('wp_add_inline_script', [
            'times' => 1,
            'return' => function ($handle, $script) use (&$capturedScript) {
                if($handle === 'adcaptcha-script') {
                    $capturedScript = $script;
                }
                return true;
            }
        ]);

        $this->forms->block_submission();

        $this->assertNotEmpty($capturedScript, 'No script was captured, it might not have been injected.');

        $this->assertStringContainsString('document.addEventListener("DOMContentLoaded", function() {', $capturedScript , 'Script does not contain the expected content');

        $this->assertStringContainsString('var form = document.querySelector(".mc4wp-form");', $capturedScript, 'Script does not contain the expected content');

        $this->assertStringContainsString('var submitButton =[... document.querySelectorAll("[type=\'submit\']")];', $capturedScript, 'Script does not contain the expected content');

        $this->assertStringContainsString('if (!window.adcap || !window.adcap.successToken) {', $capturedScript, 'Script does not contain the expected content');

        $this->assertStringContainsString('event.preventDefault();', $capturedScript, 'Script does not contain the expected content');

        $this->assertTrue(method_exists($this->forms, 'block_submission'), "Method block_submission does not exist");
    }

    // we are testing that the script 'adcaptcha-mc4wp-preview-script' is registered exactly once. Testing the Inline Script Injection. It checks that the registered script is enqueued exactly once, which means it will be included when the page is rendered. Assertions on Captured Script. Checking method existence.
    public function testFormPreviewSetupTriggers()
    {
        $capturedScript = '';

        WP_Mock::userFunction('wp_register_script', [
            'args' => ['adcaptcha-mc4wp-preview-script', null],
            'times' => 1
        ]);

        WP_Mock::userFunction('wp_add_inline_script', [
            'times' => 1,
            'return' => function ($handle, $script) use (&$capturedScript) {
                if($handle === 'adcaptcha-mc4wp-preview-script') {
                    $capturedScript = $script;
                }
                return true;
            }
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'args' => ['adcaptcha-mc4wp-preview-script'],
            'times' => 1
        ]);

        $this->forms->form_preview_setup_triggers();

        $this->assertNotEmpty($capturedScript, 'No script was captured, it might not have been injected.');

        $this->assertStringContainsString('window.onload = function() {', $capturedScript , 'Script does not contain the expected content');

        $this->assertStringContainsString('if (adminpage === "mc4wp_page_mailchimp-for-wp-forms")', $capturedScript, 'Script does not contain the expected content');

        $this->assertStringContainsString('document.getElementById("mc4wp-form-content").addEventListener("change", function() {', $capturedScript, 'Script does not contain the expected content');

        $this->assertStringContainsString('document.getElementById("mc4wp-form-preview").contentWindow.adcap.setupTriggers();', $capturedScript, 'Script does not contain the expected content');

        $this->assertTrue(method_exists($this->forms, 'form_preview_setup_triggers'), "Method form_preview_setup_triggers does not exist");
    }
}