<?php

namespace AdCaptcha\Plugin\FluentForms;

use AdCaptcha\Widget\AdCaptcha;
use AdCaptcha\Widget\Verify;
use PhpParser\Error;

class AdCaptchaElements extends \FluentForm\App\Services\FormBuilder\BaseFieldManager {
    /**
     * Constructor
     *
     * @return void
     */
    private $verify;
    private $keyTest = 'adcaptcha_widget';
    private $titleTest = 'adCAPTCHA';
    public $printContentBaseFieldManager;

    public function __construct($shouldInstantiateParent = true) {
            if ($shouldInstantiateParent === true) {
                parent::__construct('adcaptcha_widget',  
                'adCAPTCHA',            
                [ 'captcha' ],
                'advanced');

                $this->keyTest = $this->key;
                $this->titleTest = $this->key;

                $this->printContentBaseFieldManager = function ($element_name, $html, $data, $form) {
                    $this->printContent( 'fluentform/rendering_field_html_' . $element_name, $html, $data, $form );
                };
            } 
            // if( !$shouldInstantiateParent === true) {
            //     var_dump('hello from false');
            //     $this->printContentBaseFieldManager = function ($element_name, $html, $data, $form ):void {
            //         echo 'Class not found';
            //     };
            // }
            $this->verify = new Verify();

        add_action( 'wp_enqueue_scripts', [ AdCaptcha::class, 'enqueue_scripts' ], 9 );
        add_action( 'wp_enqueue_scripts', [ Verify::class, 'get_success_token' ] );
        // Updated filters to use adCAPTCHA
        add_filter( 'fluentform/response_render_' . $this->keyTest, [ $this, 'renderResponse' ], 10, 3 );
        add_filter( 'fluentform/validate_input_item_' . $this->keyTest, [ $this, 'verify' ], 10, 5 );
    }

    /**
     * Get Element Component
     *
     * @return array
     */
    public function getComponent() {
        return [
            'index'          => 16,
            'element'        => $this->keyTest,
            'attributes'     => [
                'name' => $this->keyTest,
            ],
            'settings'       => [
                'label'            => '',
                'validation_rules' => [],
            ],
            'editor_options' => [
                'title'      => $this->titleTest,
                'icon_class' => 'ff-edit-adcaptcha',
                'template'   => 'inputHidden',
            ],
        ];
    }

    /**
     * Render element
     *
     * @param  mixed $data Settings.
     * @param  mixed $form Form.
     * @return void
     */
    public function render( $data, $form ) {
        $element_name = $data['element'];
        $settings = $data['settings'];

        // Handle optional label for captcha field
        $label = '';
        if ( ! empty( $settings['label'] ) ) {
            $label = "<div class='ff-el-input--label'><label>" . $settings['label'] . '</label></div>';
        }

        // Optional container class for label placement
        $container_class = '';
        if ( ! empty( $settings['label_placement'] ) ) {
            $container_class = 'ff-el-form-' . $settings['label_placement'];
        }

        // Use AdCaptcha to build the HTML for the captcha hidden input
        $adcaptcha = AdCaptcha::ob_captcha_trigger();
       
        // Render the final captcha HTML element
        $el = "<div class='ff-el-input--content'>{$adcaptcha}<input type='hidden' class='adcaptcha_successToken' name='adcaptcha_widget'></div>";
        var_dump($el);
        $html = "<div class='ff-el-group " . esc_attr( $container_class ) . "' >" . fluentform_sanitize_html( $label ) . "{$el}</div>";

        // Print the final content to Fluent Forms
        $this->printContentBaseFieldManager( $element_name, $html, $data, $form );
    }

    /**
     * Render response
     *
     * @param string|array|number|null $response Original input from form submission.
     * @param array                    $field The form field component array.
     * @param string                   $form_id Form id.
     * @return string
     */
    public function renderResponse( $response, $field, $form_id ) {
        return $response;  // No changes needed, the response is returned as-is
    }

	/**
	 * Verify input
	 *
	 * @param  mixed $error_message Error message.
	 * @param  mixed $field Field.
	 * @param  mixed $form_data Form Data.
	 * @param  mixed $fields Form fields.
	 * @param  mixed $form Form.
	 * @return array
	 */
    public function verify( $error_message, $field, $form_data, $fields, $form ) {
        $successToken = $form_data['adcaptcha_widget'];
        $response = $this->verify->verify_token($successToken);

        if ( $response === false ) {
            $error_message = [ __( 'Incomplete captcha, Please try again.', 'adcaptcha' ) ];
        }

        return  $error_message;
    }
    
}
