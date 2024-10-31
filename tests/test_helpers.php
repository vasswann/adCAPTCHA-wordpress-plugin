<?php
// tests/test_helpers.php

if (!function_exists('is_admin')) {
    function is_admin() {
        global $is_admin;
        return $is_admin;
    }
}

// Add other mock functions here, such as add_action and add_filter


if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        global $mocked_actions;
        $mocked_actions[] = compact('hook', 'callback', 'priority', 'accepted_args');
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        global $mocked_filters;
        $mocked_filters[] = compact('hook', 'callback', 'priority', 'accepted_args');
    }
}

function execute_mocked_hook($hook_name) {
    global $mocked_actions;

    foreach ($mocked_actions as $action) {
        if ($action['hook'] === $hook_name && is_callable($action['callback'])) {
            call_user_func($action['callback']);
        }
    }
}
