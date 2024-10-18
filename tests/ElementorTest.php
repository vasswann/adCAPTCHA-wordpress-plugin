<?php
/**
 * ElementorTest.php
 *
 * @package AdCaptcha
 */

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\Elementor\Forms;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use WP_Mock as M;

// Mocking the is_admin function
if (!function_exists('is_admin')) {
    function is_admin() {
        global $is_admin;
        return $is_admin;
    }
}


// Mocking the add_action function
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        global $mocked_actions;
        $mocked_actions[] = compact('hook', 'callback', 'priority', 'accepted_args');
    }
}

// Mocking the add_filter function
if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        global $mocked_filters;
        $mocked_filters[] = compact('hook', 'callback', 'priority', 'accepted_args');
    }
}



class ElementorTest extends TestCase
{
    private $forms;

    protected function setUp(): void
    {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        $is_admin = true; 
        M::setUp();
        $this->forms = new Forms();

    }

    protected function tearDown(): void
    {
        M::tearDown();
        parent::tearDown();
    }

    public function testGetAdcaptchaName()
    {
       
        // Use reflection to access the protected method
        $reflection = new \ReflectionMethod(get_class($this->forms), 'get_adcaptcha_name');
        
        $reflection->setAccessible(true); // Make the method accessible

        // Call the method statically since it's a static method
        $result = $reflection->invoke(null); // null because it's static

        // Assert the method is protected
        $this->assertTrue($reflection->isProtected(), 'Method get_adcaptcha_name is not public');
        // Assert the result is 'adCAPTCHA'
        $this->assertEquals('adCAPTCHA', $result);
        // Check if the method exists in the Forms class
        $this->assertTrue(method_exists($this->forms, 'get_adcaptcha_name'), 'Method get_adcaptcha_name does not exist in Forms class');
    }

    public function testGetSetupMessage()
    {
        $result = $this->forms->get_setup_message();
        // Assert that the result matches the expected error message
        $this->assertEquals('Please enter your adCAPTCHA API Key and Placement ID in the adCAPTCHA settings.', $result);
        // Assert that the 'get_setup_message' method exists in the Forms class.
        $this->assertTrue(method_exists($this->forms, 'get_setup_message'), 'Method get_setup_message does not exist in Forms class');
    }

    public function testSetup() {
        
        // Call the setup method
        $this->forms->setup();
        // Access the global variables
        global $mocked_actions, $mocked_filters; 

        // Assert that the method exists in the Forms class
        $this->assertTrue(method_exists($this->forms, 'setup'), 'Method setup does not exist in Forms class');

        // Assert that the number of actions is 14
        $this->assertCount(14, $mocked_actions);
        
        // Assert that the field_types filter is registered correctly
        $this->assertContains([
            'hook' => 'elementor_pro/forms/field_types',
            'callback' => [$this->forms, 'add_field_type'],
            'priority' => 10,
            'accepted_args' => 1
        ], $mocked_filters, 'The field_types filter is not registered correctly.');
        
        // Check if the render/item filter is registered correctly
        $this->assertContains([
            'hook' => 'elementor_pro/forms/render/item',
            'callback' => [$this->forms, 'filter_field_item'],
            'priority' => 10,
            'accepted_args' => 1
        ], $mocked_filters, 'The render/item filter is not registered correctly.'); 
        
        // Check if the render_field action is registered correctly
        $this->assertContains([
            'hook' => 'elementor_pro/forms/render_field/adCAPTCHA',
            'callback' => [$this->forms, 'render_field'],
            'priority' => 10,
            'accepted_args' => 3
        ], $mocked_actions, 'The render_field action is not registered correctly.'); 

        // Check if the update_controls action is registered correctly 
        $this->assertContains([
            'hook' => 'elementor/element/form/section_form_fields/after_section_end',
            'callback' => [$this->forms, 'update_controls'],
            'priority' => 10,
            'accepted_args' => 2
        ], $mocked_actions, 'The update_controls action is not registered correctly.'); 

        // Check if the enqueue_scripts action is registered correctly
        $this->assertContains([
            'hook' => 'wp_enqueue_scripts',
            'callback' => [AdCaptcha::class, 'enqueue_scripts'],
            'priority' => 9,
            'accepted_args' => 1
        ], $mocked_actions, 'The enqueue_scripts action is not registered correctly.');

        // Check if the reset_captcha_script action is registered correctly
        $this->assertContains([
            'hook' => 'wp_enqueue_scripts',
            'callback' => [$this->forms, 'reset_captcha_script'],
            'priority' => 9,
            'accepted_args' => 1
        ], $mocked_actions, 'The reset_captcha_script action is not registered correctly.');

        // Check if the preview/enqueue_scripts action is registered correctly
        $this->assertContains([
            'hook' => 'elementor/preview/enqueue_scripts',
            'callback' => [AdCaptcha::class, 'enqueue_scripts'],
            'priority' => 10,
            'accepted_args' => 1
        ], $mocked_actions, 'The preview/enqueue_scripts action is not registered correctly.');
        
        // Check if the get_success_token action is registered correctly
        $this->assertContains([
            'hook' => 'wp_enqueue_scripts',
            'callback' => [Verify::class, 'get_success_token'],
            'priority' => 10,
            'accepted_args' => 1
        ], $mocked_actions, 'The get_success_token action is not registered correctly.');

        // Check if the validation action is registered correctly
        $this->assertContains([
            'hook' => 'elementor_pro/forms/validation',
            'callback' => [$this->forms, 'verify'],
            'priority' => 10,
            'accepted_args' => 2
        ], $mocked_actions, 'The validation action is not registered correctly.');

        // this need to refuctored mock the is_admin function to be true and mocked the add_action function
        // Check if the admin/after_create_settings/elementor action is registered correctly
        // $this->assertContains([
        //     'hook' => 'elementor/admin/after_create_settings/elementor',
        //     'callback' => [$this->forms, 'register_admin_fields'],
        //     'priority' => 10,
        //     'accepted_args' => 1
        // ], $mocked_actions, 'The admin/after_create_settings/elementor action is not registered correctly.');
    }

    public function testRegisterAdminFields() {

        // mocking ElementorPlugin::$instance->settings->add_section

        // Assert that the method exists in the Forms class
        $this->assertTrue(method_exists($this->forms, 'register_admin_fields'), 'Method register_admin_fields does not exist in Forms class');
    }

    public function testResetCaptchaScript() {
         // Variable to hold the captured script
        $capturedScript = '';

        // Mock wp_add_inline_script to capture the injected script
        WP_Mock::userFunction('wp_add_inline_script', [
            'times' => 1, 
            'return' => function ($handle, $script) use (&$capturedScript) {
                if ($handle === 'adcaptcha-script') {
                    $capturedScript = $script;
                }
                return true; 
            },
        ]);

        // Verify that the method executes without errors.
        try {
            $this->forms->reset_captcha_script();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('reset_captcha_script method threw an exception: ' . $e->getMessage());
        } 
    
        // Assert that the captured script contains the expected content
        $this->assertStringContainsString('document.addEventListener("submit"', $capturedScript, 'Event listener registration is missing');
        $this->assertStringContainsString('window.adcap.successToken = "";', $capturedScript, 'Success token reset logic is missing');

        // Assert that the method exists in the Forms class
        $this->assertTrue(method_exists($this->forms, 'reset_captcha_script'), 'Method reset_captcha_script does not exist in Forms class');
    }

    public function testRenderField() {
        // Mock item data
        $item = [
            'custom_id' => 'test_id',
            ];

        // Call the render_field method
        ob_start();     
        $this->forms->render_field($item, 0, null);     
        $output = ob_get_clean();   

        // Check if the output contains the correct HTML structure
        // $this->assertStringContainsString('<div style="width: 100%;" class="elementor-field" id="form-field-test_id">', $output, 'The generated HTML structure is incorrect.');
        $this->assertStringContainsString(
            '<div data-adcaptcha=""',
            $output,
            'The inner <div> with data-adcaptcha is missing.'
        );
    
        $this->assertStringContainsString(
            '<input type="hidden" class="adcaptcha_successToken"',
            $output,
            'The hidden input for successToken is missing.'
        );

         // Assert that the method exists in the Forms class
         $this->assertTrue(method_exists($this->forms, 'render_field'), 'Method render_field does not exist in Forms class');
    }
}
