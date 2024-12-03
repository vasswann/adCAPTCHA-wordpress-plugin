<?php

namespace AdCaptcha\Tests\Plugin\FluentForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\FluentForms\Forms;
use AdCaptcha\Plugin\FluentForms\AdCaptchaElements;
use AdCaptcha\Plugin\AdCaptchaPlugin;
use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Constraint\Type;

class FluentFormsTest extends TestCase {
    protected $forms;

    public function setUp(): void {

        parent::setUp();
        \Brain\Monkey\setUp();
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn('path/to/plugin');
        $this->forms = new Forms();
    }

    protected function tearDown(): void {
        \Brain\Monkey\tearDown();

        unset($this->forms);
    }

    public function test_setup() {
        Functions\expect('add_action')
            ->with('plugins_loaded', $this->callback(fn($callback) => is_callable($callback)))
            ->once();

        $this->forms->setup();

        do_action('plugins_loaded');

        $this->assertTrue(true, 'No errors occurred during action execution.');
        $this->assertTrue(method_exists($this->forms, 'setup'), 'Method setup does not exist');
        $this->assertInstanceOf(AdCaptchaPlugin::class, $this->forms, 'Expected an instance of AdCaptchaPlugin');
    }
}