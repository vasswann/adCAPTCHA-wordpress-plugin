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
use WP_Mock;
use Mockery;

class FluentFormsTest extends TestCase {
    private $forms;
    private $mockedClass;
    private $adCaptchaElements;

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        WP_Mock::setUp();
       
        $baseFieldManagerMcok = $this->getMockBuilder('\FluentForm\App\Services\FormBuilder\BaseFieldManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->forms = new Forms();

        //$this->adCaptchaElements = new AdCaptchaElements();
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

    // Test that the constructor correctly registers actions and filters with the expected hooks, callbacks, priorities, and arguments
    public function testConstructor() {
        global $mocked_actions, $mocked_filters;
        $this->assertTrue(method_exists($this->adCaptchaElements, '__construct'), 'Method __construct does not exist');

        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [AdCaptcha::class, 'enqueue_scripts'], 'priority' => 9, 'accepted_args' => 1], $mocked_actions);
        
        $this->assertContains(['hook' => 'wp_enqueue_scripts', 'callback'=> [Verify::class, 'get_success_token'], 'priority' => 10, 'accepted_args' => 1], $mocked_actions);

        $this->assertContains(['hook' => 'fluentform/response_render_', 'callback'=> [$this->adCaptchaElements, 'renderResponse'], 'priority' => 10, 'accepted_args' => 3], $mocked_filters);

        $this->assertContains(['hook' => 'fluentform/validate_input_item_', 'callback'=> [$this->adCaptchaElements, 'verify'], 'priority' => 10, 'accepted_args' => 5], $mocked_filters);

        $this->assertTrue(true);
    }

    // Test that getComponent method exists, returns an array with expected structure, and matches expected values
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
        $this->assertEquals($expected, $component);
    }

    public function testRender() {
        WP_Mock::userFunction('fluentform_sanitize_html', [
            'args' => [Mockery::any()],
            'return' => Mockery::on(function($html) {
                return $html;
            }),
        ]);

        $this->adCaptchaElements = Mockery::mock(AdCaptchaElements::class);

        $this->adCaptchaElements->shouldReceive('render')
            ->with(Mockery::any(), Mockery::any())
            ->andReturn('hello');  

        // $this->adCaptchaElements->shouldReceive('printContent')
        //     ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
        //     ->andReturn(true);  
        
        $data = [
            'element' => 'adcaptcha_widget',
            'settings' => [
                'label' => 'Test Label',
                'label_placement' => 'Test top',
            ]
        ];

        $form = [];

        $this->assertTrue(method_exists($this->adCaptchaElements, 'render'), 'Method render does not exist');

        $result = $this->adCaptchaElements->render($data, $form);
        var_dump($result);
    }
}