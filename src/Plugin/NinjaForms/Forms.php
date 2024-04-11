<?php

namespace AdCaptcha\Plugin\NinjaForms\Froms;

use AdCaptcha\Plugin\NinjaForms\AdcaptchaField\AdcaptchaField;

class Forms {

    public function setup() {
		add_filter( 'ninja_forms_register_fields', [ $this, 'register_field' ] );
    }

	public function register_field( $fields ): array {
		$fields = (array) $fields;
		$fields['adCAPTCHA'] = new AdcaptchaField();

		return $fields;
	}
}