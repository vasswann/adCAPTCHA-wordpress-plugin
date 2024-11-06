<?php
/**
 * FluentForms FormsTest
 * 
 * @package AdCaptcha
*/

namespace AdCaptcha\Tests\Plugin\FluentForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\FluentForms\Forms;
use AdCaptcha\Plugin\FluentForms\AdCaptchaElement;
use AdCaptcha\Plugin\AdCaptchaPlugin;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use WP_Mock;
use Mockery;

class FluentFormsTest extends TestCase {
    private $forms;
    private $mockedClass;
    private $adCaptchaElement;

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions;
        $mocked_actions = [];
        WP_Mock::setUp();
       
        $baseFieldManagerMcok = $this->getMockBuilder(\FluentForm\App\Services\FormBuilder\BaseFieldManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->forms = new Forms();
        // $this->adCaptchaElement = new AdCaptchaElement();
    }

    public function tearDown(): void {
        global $mocked_actions;
        $mocked_actions = [];
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    // Verifies the setup method exists, checks instance type, mocks plugin directory path, executes hooks, and confirms ‘plugins_loaded’ and ‘fluentform/loaded’ actions are correctly registered
    public function testSetup() {
        global $mocked_actions; 
        $this->assertTrue(method_exists($this->forms, 'setup'), 'Method setup does not exist');
        $this->assertInstanceOf(AdCaptchaPlugin::class, $this->forms, 'Expected an instance of AdCaptchaPlugin');

        WP_Mock::userFunction('plugin_dir_path', [
            'args' => [Mockery::any()], 
            'return' => '/Applications/XAMPP/xamppfiles/htdocs/testcap/wp-content/plugins/adcaptcha/src/Plugin/FluentForms/']);

       
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

    // public function testConstructor() {
    //     global $mocked_actions;
    // //     $this->assertTrue(method_exists($this->adCaptchaElement, '__construct'), 'Method __construct does not exist');
    // }
}