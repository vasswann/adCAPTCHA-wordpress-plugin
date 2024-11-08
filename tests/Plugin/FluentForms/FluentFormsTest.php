<?php
/**
 * FluentForms FormsTest
 * 
 * @package AdCaptcha
*/

namespace AdCaptcha\Tests\Plugin\FluentForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\FluentForms\Forms;
use AdCaptcha\Plugin\FluentForms\AdCaptchaElements;
use AdCaptcha\Plugin\AdCaptchaPlugin;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use ReflectionClass;
use WP_Mock;
use Mockery;

class FluentFormsTest extends TestCase {
    private $forms;
    private $mockedClass;
    private $adCaptchaElements;
    private $verifyMock;
    private $key = 'adcaptcha_widget';
    private $title = 'adCAPTCHA';

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        WP_Mock::setUp();
       
        $baseFieldManagerMock = $this->getMockBuilder('\FluentForm\App\Services\FormBuilder\BaseFieldManager')
        ->disableOriginalConstructor()
        ->getMock();

        $this->verifyMock = $this->createMock(Verify::class);

        $this->forms = new Forms();

        $adCAPTCHAFieldData = false;
        $this->adCaptchaElements = new AdCaptchaElements($adCAPTCHAFieldData);

        $reflection = new \ReflectionClass($this->adCaptchaElements);
        $property = $reflection->getProperty('verify');
        $property->setAccessible(true);
        $property->setValue($this->adCaptchaElements, $this->verifyMock);
    }

    public function tearDown(): void {
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    // Verifies the setup method exists, checks instance type, mocks plugin directory path, executes hooks, and confirms ‘plugins_loaded’ and ‘fluentform/loaded’ actions are correctly registered
    public function testSetup() {
        global $mocked_actions; 
        $this->assertTrue(method_exists($this->forms, 'setup'), 'Method setup does not exist');
        $this->assertInstanceOf(AdCaptchaPlugin::class, $this->forms, 'Expected an instance of AdCaptchaPlugin');

        $basedir = dirname(__DIR__, 3);
        WP_Mock::userFunction('plugin_dir_path', [
            'args' => [Mockery::any()], 
            'return' => $basedir . '/src/Plugin/FluentForms'
            ]);

        $this->forms->setup();

        if (function_exists('execute_mocked_hook')) {
            execute_mocked_hook('plugins_loaded');
        } else {
            throw new \Exception('Function execute_mocked_hook does not exist');
        }

        $this->assertIsArray($mocked_actions, 'Expected result to be an array');

        $pluginsLoadedFound = false;
        $fluentFormLoadedFound = false;

        foreach($mocked_actions as $action) {
            if (isset($action['hook'], $action['callback'], $action['priority'], $action['accepted_args'])) {
              
                if (!$pluginsLoadedFound &&
                    $action['hook'] === 'plugins_loaded' &&
                    $action['priority'] === 10 &&
                    $action['accepted_args'] === 1 &&
                    is_object($action['callback']) && 
                    ($action['callback'] instanceof \Closure)) {
                        $pluginsLoadedFound = true;
                }
        
                if (!$fluentFormLoadedFound &&
                    $action['hook'] === 'fluentform/loaded' &&
                    $action['priority'] === 10 &&
                    $action['accepted_args'] === 1 &&
                    is_object($action['callback']) && 
                    ($action['callback'] instanceof \Closure)) {
                        $fluentFormLoadedFound = true;
                }
        
                if ($pluginsLoadedFound && $fluentFormLoadedFound) {
                    break;
                }
            }
        }
       
       $this->assertTrue($pluginsLoadedFound, 'Expected array structure was not found.');
       $this->assertTrue($fluentFormLoadedFound, 'Expected array structure was not found.');
    }

    // Verifies existence of __construct(), checks that expected actions and filters are registered with correct hooks, callbacks, priorities, and argument counts
    public function testConstructor():void {
        global $mocked_actions, $mocked_filters;
        $this->assertTrue(method_exists($this->adCaptchaElements, '__construct'), 'Method __construct does not exist');

        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 9, 'accepted_args' => 1], $mocked_actions);
        
        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'fluentform/response_render_adcaptcha_widget', 'callback'=> [$this->adCaptchaElements, 'renderResponse'], 'priority' => 10, 'accepted_args' => 3], $mocked_filters);

        $this->assertContains(['hook' => 'fluentform/validate_input_item_adcaptcha_widget', 'callback'=> [$this->adCaptchaElements, 'verify'], 'priority' => 10, 'accepted_args' => 5], $mocked_filters);
    }

    // Verifies existence of getComponent(), checks returned array structure and keys, and confirms it matches the expected component configuration
    public function testGetComponent() {
        $this->assertTrue(method_exists($this->adCaptchaElements, 'getComponent'), 'Method getComponent does not exist');
        
        $expected = [
            'index'          => 16,
            'element'        => $this->key,
            'attributes'     => [
                'name' => $this->key,
            ],
            'settings'       => [
                'label'            => '',
                'validation_rules' => [],
            ],
            'editor_options' => [
                'title'      => $this->title,
                'icon_class' => 'ff-edit-adcaptcha',
                'template'   => 'inputHidden',
            ],
        ];

        $component = $this->adCaptchaElements->getComponent();
        $this->assertIsArray($component, 'Expected result to be an array');
        $this->assertArrayHasKey('index', $component, 'Expected key not found');
        $this->assertArrayHasKey('element', $component, 'Expected key not found');
        $this->assertArrayHasKey('attributes', $component, 'Expected key not found');
        $this->assertArrayHasKey('settings', $component, 'Expected key not found');
        $this->assertArrayHasKey('editor_options', $component, 'Expected key not found');
        $this->assertEquals($expected, $component, 'Expected result does not match');
    }

    public function testRender() {
        WP_Mock::userFunction('fluentform_sanitize_html', [
            'args' => [Mockery::any()],
            'return' => Mockery::on(function($html) {
                return $html;
            }),
        ]);

        $adCaptchaMock = $this->getMockBuilder(AdCaptchaElements::class)
            ->disableOriginalConstructor()
            ->getMock();

            $adCaptchaMock->printContentBaseFieldManager = function($element_name, $html, $data, $form) {
                // In the closure, you can define how the method should behave in the test
                $this->assertEquals('adcaptcha_widget', $element_name);
                $this->assertContains('<div class="ff-el-group">', $html);
                // More assertions or actions based on the input arguments
            };
    

        $data  = [
            'element' => 'adcaptcha_widget',
            'settings' => [
                'label' => 'mocked_label',
                'validation_rules' => [],
            ],
        ];

        $form = [];

        $this->assertTrue(method_exists($this->adCaptchaElements, 'render'), 'Method render does not exist');

        // $result = $this->adCaptchaElements->render($data, $form );
        // var_dump($result);
        // $result = $this->adCaptchaElements->render($data, $form);
        // var_dump($result);
    }

    // Checks the existence of renderResponse(), calls it with a valid response, and verifies it returns the expected result
    public function testRenderResponse() {
        $this->assertTrue(method_exists($this->adCaptchaElements,'renderResponse'),' Method renderResponse does not exist');
        $result = $this->adCaptchaElements->renderResponse('valid_response', [], null);
        $this->assertEquals('valid_response', $result,' Expected result does not match');
    }

    // Verifies the existence and callability of verify(), mocks verify_token to succeed, and checks that verify() returns the original error message array unchanged
    public function testVerifySuccess() {
        $this->assertTrue(method_exists($this->adCaptchaElements,'verify'),' Method verify does not exist');
        $this->assertTrue(is_callable([$this->adCaptchaElements, 'verify']),' Method verify is not callable');

        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(true);

        $errorMessage = [];
        $formData = [
            'adcaptcha_widget' => 'mocked_token_value', 
        ];

        $result = $this->adCaptchaElements->verify($errorMessage, null, $formData, null, null);

        $this->assertEquals($errorMessage, $result,'Expected result does not match');
    }

    // Mocks verify_token to fail, tests that verify() returns an error message array when captcha verification fails
    public function testVerifyFailure() {
        $this->verifyMock->expects($this->once())
            ->method('verify_token')
            ->willReturn(false);

        $errorMessage = [];
        $formData = [
            'adcaptcha_widget' => 'mocked_token_value', 
        ];

        $result = $this->adCaptchaElements->verify($errorMessage, null, $formData, null, null);

        $this->assertIsArray($result, 'Expected result to be an array');
        $this->assertNotEmpty($result, 'Expected result to not be empty');
        $this->assertContains('Incomplete captcha, Please try again.', $result, 'Expected error message not found');
    }
}