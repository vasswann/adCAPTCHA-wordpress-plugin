<?php

namespace AdCaptcha\Plugin\WPForms\AdCAPTCHA_WPForms_Field;

use WPForms_Field;
use AdCaptcha\Widget\AdCaptcha\AdCaptcha;

class AdCAPTCHA_WPForms_Field extends WPForms_Field {

	public function init() {
		$this->name     = 'adCAPTCHA';
		$this->type     = 'adcaptcha';
		$this->icon     = 'fa-plug';
		$this->order    = 22;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 * @param array $field
	 */
	public function field_options( $field ) {
		// Options open markup
		$this->field_option( 'basic-options', $field, array( 'markup' => 'open' ) );

		// Description
		$this->field_option( 'description', $field );

		// Options close markup
		$this->field_option( 'basic-options', $field, array( 'markup' => 'close' ) );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 * @param array $field
	 */
	public function field_preview( $field ) {

		echo AdCaptcha::ob_captcha_trigger();

		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 * @param array $field
	 * @param array $form_data
	 */
	public function field_display( $field, $field_atts, $form_data ) {
		// Display the AdCaptcha widget.
		echo AdCaptcha::ob_captcha_trigger();
	}
}
