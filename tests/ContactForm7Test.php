<?php

use PHPUnit\Framework\TestCase;
use AdCaptcha\Plugin\ContactForm7\Forms;

// Mock the add_action function
if (!function_exists('add_action')) {
    function add_action($hook, $callback) {
        // You can leave this empty or add some mock behavior if needed
    }
}
// Mock the add_filter function
if (!function_exists('add_filter')) {
    function add_filter($hook, $callback) {
        // You can leave this empty or add some mock behavior if needed
    }
}


class ContactForm7Test extends TestCase
{
 public function testForms() {
   $forms = new Forms();
   $result = $forms->test();
   $this->assertEquals('hello', $result);
 }
}
