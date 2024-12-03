<?php
/**
 * ElementorTest.php
 *
 * @package AdCaptcha
 */

 namespace AdCaptcha\Tests\Plugin\Elementor;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\Elementor\Forms;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Plugin\AdCaptchaPlugin;
use AdCaptcha\Widget\Verify;
use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;

class ElementorTest extends TestCase {
    protected $forms;

    public function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        Functions\stubs([
            'is_admin' => true 
        ]);
        $this->forms = new Forms();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_instance() {
        $this->assertInstanceOf(AdCaptchaPlugin::class, $this->forms, 'Expected an instance of AdCaptcha');
    }

    public function test_get_adcaptcha_name() {
        $reflection = new \ReflectionMethod(get_class($this->forms), 'get_adcaptcha_name');
        $reflection->setAccessible(true); 
        $result = $reflection->invoke(null);

        $this->assertTrue($reflection->isProtected(), 'Method get_adcaptcha_name is not public');
        $this->assertEquals('adCAPTCHA', $result);
        $this->assertTrue(method_exists($this->forms, 'get_adcaptcha_name'), 'Method get_adcaptcha_name does not exist in Forms class');
    }

    public function test_setup() {
    
        $this->assertTrue(method_exists($this->forms, 'setup'), 'Method setup does not exist');
    }

    public function test_update_controls() {
        

        $this->assertTrue(method_exists($this->forms, 'update_controls'), 'Method update_controls does not exist');
    }

    // public function test_helper_func_get_control_from_stack_called() {
    //     // Mocking the helper_func_get_control_from_stack to return mock data
    //     Monkey\Functions\expect('helper_func_get_control_from_stack')
    //         ->with($this->isInstanceOf(\stdClass::class), 'form_fields') // Ensure proper arguments
    //         ->once()
    //         ->andReturn([
    //             'fields' => [
    //                 'width' => ['conditions' => ['terms' => []]],
    //                 'required' => ['conditions' => ['terms' => []]]
    //             ]
    //         ]);

    //     $controls_stack = $this->createMock(\stdClass::class); // Mocking the $controls_stack
    //     $args = [];

    //     $this->forms->update_controls($controls_stack, $args);
    // }

  
}