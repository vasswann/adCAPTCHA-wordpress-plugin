<?php
/**
 * NinjaFormsTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\NinjaForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\NinjaForms\Forms;
use AdCaptcha\Plugin\NinjaForms\AdCaptchaField;
use AdCaptcha\Plugin\AdCaptchaPlugin;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use Brain\Monkey\Functions;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;
use Mockery;

class NinjaFormsTest extends TestCase {
    private $forms;
    private $adCaptchaField;
    private $nfMock;

    public function setUp(): void {
        parent::setUp();
        
        // $this->nfMock = Mockery::mock('NF_Fields_Recaptcha')
        //     ->shouldIgnoreMissing();

        $this->nfMock = $this->getMockBuilder('NF_Fields_Recaptcha')
            ->disableOriginalConstructor()
            ->getMock();
        if (!defined('ADCAPTCHA_ERROR_MESSAGE')) {
            require_once './adcaptcha.php';
        }

        Functions\when('esc_html__')->justReturn('adCAPTCHA');
        $this->forms = new Forms();
        $this->adCaptchaField = new AdCaptchaField(false);

        if ($this->adCaptchaField === null) {
            echo "Failed to initialize adCaptchaField.--------------------------------------";
        }
        // Functions\when('add_action')->alias(function($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        //     Actions\expectAdded($tag)->once()->with($function_to_add, $priority, $accepted_args);
        // });
        // Functions\when('add_filter')->alias(function($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        //     Filters\expectAdded($tag)->once()->with($function_to_add, $priority, $accepted_args);
        // });
        // Functions\when('plugin_dir_path')->justReturn('/path/to/plugin');
        // Functions\when('plugins_url')->justReturn('/path/to/plugin');
       
        // Functions\when('wp_enqueue_script')->justReturn(true);

        
    }

    public function tierDown(): void {
        parent::tearDown();
    }

    public function testValidateEmptyField() {
        $field = ['value' => ''];
        $result = $this->adCaptchaField->validate($field, []);
        var_dump($result);
        $this->assertEquals(esc_html__(ADCAPTCHA_ERROR_MESSAGE), $result);
        $this->assertNotNull($this->adCaptchaField, 'AdCaptchaField should not be null after setUp');
        $this->assertTrue(method_exists($this->adCaptchaField, 'validate'), 'Method validate does not exist in AdCaptchaField');
    }
}
