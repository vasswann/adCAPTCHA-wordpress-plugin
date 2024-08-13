<?php

namespace AdCaptcha\Plugin\Elementor\Forms;

use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Widget\Verify\Verify;
use AdCaptcha\AdCaptchaPlugin\AdCaptchaPlugin;

use Elementor\Controls_Stack;
use Elementor\Plugin as ElementorPlugin;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Form_Record;

class Forms extends AdCaptchaPlugin {

    const FORM_FIELD = 'adCAPTCHA';

    public function setup() {
        add_filter( 'elementor_pro/forms/field_types', [ $this, 'add_field_type' ] );
        add_filter( 'elementor_pro/forms/render/item', [ $this, 'filter_field_item' ] );
        add_action( 'elementor_pro/forms/render_field/' . static::FORM_FIELD, [ AdCaptcha::class, 'captcha_trigger' ], 10, 3 );
        add_action(
			'elementor/element/form/section_form_fields/after_section_end',
			[ $this, 'update_controls' ],
			10,
			2
		);
        add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ], 9 );
        add_action( 'wp_enqueue_scripts', [ Verify::class, 'get_success_token' ] );
        add_action( 'elementor_pro/forms/validation', [ $this, 'verify' ], 10, 2 );
        // add_action( 'wp_enqueue_scripts', [ $this, 'block_submission' ], 9 );
    }

    public function add_field_type( $field_types ) {
		$field_types[ static::FORM_FIELD ] = esc_html__( 'adCAPTCHA', 'adcaptcha' );

		return $field_types;
	}

    public function update_controls( Controls_Stack $controls_stack, array $args ) {
		$control_id   = 'form_fields';
		$control_data = ElementorPlugin::$instance->controls_manager->get_control_from_stack(
			$controls_stack->get_unique_name(),
			$control_id
		);

		$term = [
			'name'     => 'field_type',
			'operator' => '!in',
			'value'    => [ static::FORM_FIELD ],
		];

		$control_data['fields']['width']['conditions']['terms'][]    = $term;
		$control_data['fields']['required']['conditions']['terms'][] = $term;

		ElementorPlugin::$instance->controls_manager->update_control_in_stack(
			$controls_stack,
			$control_id,
			$control_data,
			[ 'recursive' => true ]
		);
	}
}
