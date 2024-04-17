<?php

namespace AdCaptcha\Plugin\NinjaForms\Froms;

use AdCaptcha\Plugin\NinjaForms\AdcaptchaField\AdcaptchaField;
use AdCaptcha\Widget\AdCaptcha\AdCaptcha;
use AdCaptcha\Plugin\Plugin;

class Forms extends Plugin {

    public function setup() {
		add_action('plugins_loaded', function() {
			require_once plugin_dir_path(__FILE__) . '/AdcaptchaField.php';
			add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ]);
			add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
			add_filter( 'ninja_forms_register_fields', [ $this, 'register_field' ] );
			add_filter( 'ninja_forms_field_template_file_paths', [ $this, 'register_template' ]  );
			add_filter( 'ninja_forms_localize_field_adcaptcha', [ $this, 'render_field' ] );
			add_filter( 'ninja_forms_localize_field_adcaptcha_preview', [ $this, 'render_field' ] );
		});
    }

	public function register_field( $fields ): array {
		$fields = (array) $fields;
		$fields['adcaptcha'] = new AdcaptchaField();

		return $fields;
	}

	public function register_template( $paths ): array {
		$paths = (array) $paths;
		$paths[] = __DIR__ . '/templates/';	

		return $paths;
	}

	public function render_field( $field ): array {
		$field = (array) $field;

		$id = $field['id'] ?? 0;
		$adcaptcha = str_replace(
			'<div',
			'<div id="adcaptcha-' . $id . '"',
			AdCaptcha::ob_captcha_trigger()
		);

		$field['settings']['adcaptcha'] = $adcaptcha;

		return $field;
	}

	public function load_scripts() {
		wp_enqueue_script(
            'adcaptcha-ninjaforms',
			plugins_url('AdCaptchaFieldController.js', __FILE__),
            [ 'nf-front-end' ],
            PLUGIN_VERSION_adCAPTCHA,
            true
        );
    }
}
