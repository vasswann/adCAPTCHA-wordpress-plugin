<?php
/**
 * NinjaFormsTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\NinjaForms;

// use WP_UnitTestCase;
use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\NinjaForms\Forms;
use AdCaptcha\Plugin\NinjaForms\AdCaptchaField;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use WP_Mock;
use Mockery;

// if(!class_exists('NF_Fields_Recaptcha')) {
//     class NF_Fields_Recaptcha {
       
//     }
// }

class NinjaFormsTest extends TestCase {
    private $forms;
    private $nfMock;
    private $adcaptchaField;
    private $verifyMock;

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        WP_Mock::setUp();
        
        $this->nfMock = $this->getMockBuilder('NF_Fields_Recaptcha')
            ->disableOriginalConstructor()
            ->getMock();
        // $this->nfMock->__construct();
        $this->verifyMock = $this->getMockBuilder(Verify::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adcaptchaField = new AdCaptchaField();
        $this->forms = new Forms();   
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testSetup() {
        
        
        global $mocked_actions, $mocked_filters; 
        $this->assertTrue(method_exists($this->forms, 'setup'), 'Method setup does not exist');
        WP_Mock::userFunction('plugin_dir_path', [
            'args' => [Mockery::any()], 
            'return' => '/Applications/XAMPP/xamppfiles/htdocs/testcap/wp-content/plugins/adcaptcha/src/Plugin/NinjaForms/']);
        
        $this->forms->setup();

        // this function is coming from test_helpers.php, allow me to use it in the nested add_action inside the function, and add them to the global $mocked_actions array
        if (function_exists('execute_mocked_hook')) {
            execute_mocked_hook('plugins_loaded');
        } else {
            throw new \Exception('Function execute_mocked_hook does not exist');
        }
       
        $this->assertIsArray($mocked_actions, 'Expected result to be an array');
        $this->assertIsArray($mocked_filters, 'Expected result to be an array');

        $found = false;
        foreach ($mocked_actions as $action) {
            if (
                isset($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']) &&
                $action['hook'] === 'plugins_loaded' &&
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
        
        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [$this->forms, 'load_scripts'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'ninja_forms_register_fields', 'callback'=> [$this->forms, 'register_field'], 'priority' => 10, 'accepted_args' => 1], $mocked_filters);

        $this->assertContains(['hook' => 'ninja_forms_field_template_file_paths', 'callback'=> [$this->forms, 'register_template'], 'priority' => 10, 'accepted_args' => 1], $mocked_filters);

        $this->assertContains(['hook' => 'ninja_forms_localize_field_adcaptcha', 'callback'=> [$this->forms, 'render_field'], 'priority' => 10, 'accepted_args' => 1], $mocked_filters);

        $this->assertContains(['hook' => 'ninja_forms_localize_field_adcaptcha_preview', 'callback'=> [$this->forms, 'render_field'], 'priority' => 10, 'accepted_args' => 1], $mocked_filters);
    }

    public function testRegisterField() {
        
        $fields = $this->forms->register_field([]);

        $this->assertArrayHasKey('adcaptcha', $fields, 'Field not found');
        $this->assertInstanceOf(AdCaptchaField::class, $fields['adcaptcha'], 'Field is not an instance of AdCaptchaField');
    }

    public function testRegisterTemplate() {
        $expectedPath = "path/to/template";
        $paths = $this->forms->register_template($expectedPath);

        $this->assertIsArray($paths, 'Expected result to be an array');
        $this->assertContains($expectedPath, $paths, 'Expected path not found');
        $this->assertTrue(method_exists($this->forms, 'register_template'), 'Method register_template does not exist');
    }

    public function testRenderField() {
        $field = $this->forms->render_field([]);

        $this->assertArrayHasKey('settings', $field);
        $this->assertArrayHasKey('adcaptcha', $field['settings']);
        $this->assertStringContainsString('<div', $field['settings']['adcaptcha']);
        $this->assertTrue(method_exists($this->forms, 'render_field'), 'Method render_field does not exist');
    }

    public function testLoadScripts() {
        if(!defined('PLUGIN_VERSION_ADCAPTCHA')) {
            define('PLUGIN_VERSION_ADCAPTCHA', '1.0.0');
        }
    
        WP_Mock::userFunction('plugins_url', [
            'args' => ['AdCaptchaFieldController.js', Mockery::any()],
            'return' => 'path/to/script/AdCaptchaFieldController.js'
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'args' => [
                'adcaptcha-ninjaforms',
                'path/to/script/AdCaptchaFieldController.js',
                ['nf-front-end'],
                PLUGIN_VERSION_ADCAPTCHA,
                true
            ],
            'times' => 1, 
        ]);

        $this->forms->load_scripts();

        $this->assertTrue(defined('PLUGIN_VERSION_ADCAPTCHA'), 'PLUGIN_VERSION_ADCAPTCHA is not defined');
        $this->assertTrue(method_exists($this->forms, 'load_scripts'), 'Method load_scripts does not exist');

        

        // Verify that wp_enqueue_script was called with the correct arguments
    // WP_Mock::expectActionAdded('wp_enqueue_script', [
    //     'adcaptcha-ninjaforms',
    //     'path/to/script/AdCaptchaFieldController.js',
    //     ['nf-front-end'],
    //     PLUGIN_VERSION_ADCAPTCHA,
    //     true
    // ]);

    // Verify that wp_enqueue_script was called exactly once
    // WP_Mock::expectActionAdded('wp_enqueue_script', [
    //     'adcaptcha-ninjaforms',
    //     'path/to/script/AdCaptchaFieldController.js',
    //     ['nf-front-end'],
    //     PLUGIN_VERSION_ADCAPTCHA,
    //     true
    // ], 1);
    }

    // public function testValidate() {
    //     $field = ['value' => ''];
    //     $data = [];
    //     $this->verifyMock->expects($this->once())
    //         ->method('verify_token')
    //         ->with('token')
    //         ->willReturn(false);
    //     $this->adcaptchaField->validate($field, $data);
        
       
    // }
}