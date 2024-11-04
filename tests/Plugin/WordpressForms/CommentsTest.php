<?php
/**
 * WordpressForms CommentsTest
 * 
 * @package AdCaptcha
 */

namespace AdCaptcha\Tests\Plugin\WordpressForms;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use AdCaptcha\Plugin\Comments;
use WP_Mock;
use Mockery;

class CommentsTest extends TestCase {
    private $comments;
    private $verifyMock;

    public function setUp(): void {
        parent::setUp();
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        $mocked_filters = [];
        WP_Mock::setUp();

        $this->verifyMock = $this->createMock(Verify::class);
        $this->comments = new Comments(); 

        // $reflection = new \ReflectionClass($this->comments);
        // $property = $reflection->getProperty('verify');
        // $property->setAccessible(true);
        // $property->setValue($this->comments, $this->verifyMock);

        
    }

    public function tearDown(): void {
        global $mocked_actions, $mocked_filters;
        $mocked_actions = [];
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testSetup() {
        $this->assertTrue(method_exists($this->comments, 'setup'), 'Method setup does not exist');

        global $mocked_actions, $mocked_filters;
        $this->comments->setup();
    }
}
    