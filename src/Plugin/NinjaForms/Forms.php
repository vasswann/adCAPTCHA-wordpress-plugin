<?php

namespace AdCaptcha\Plugin\NinjaForms\Froms;

use AdCaptcha\Plugin\NinjaForms\AdcaptchaField\AdcaptchaField;
use AdCaptcha\Plugin\Plugin;

class Forms extends Plugin {

    public function setup() {
		add_filter( 'ninja_forms_register_fields', [ $this, 'register_field' ] );
    }

	public function register_field( $fields ): array {
		$fields = (array) $fields;
		$fields['adCAPTCHA'] = new AdcaptchaField();

		return $fields;
	}
}