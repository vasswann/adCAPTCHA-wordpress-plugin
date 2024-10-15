<?php

namespace AdCaptcha\Tests\Plugin\Test;

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\Test\Multiplier;

class MultiplierTest extends TestCase
{
    public function testMultiply()
    {
        $multiplier = new Multiplier(); // Create an instance of the Multiplier class
        $result = $multiplier->multiply(3, 4); // Call the multiply method
        $this->assertEquals(12, $result); // Check if the result is 12
    }
}