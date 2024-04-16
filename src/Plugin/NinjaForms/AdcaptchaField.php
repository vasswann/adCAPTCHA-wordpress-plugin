<?php
namespace AdCaptcha\Plugin\NinjaForms\AdcaptchaField;

use NF_Fields_Recaptcha;
use AdCaptcha\Widget\Verify\Verify;

class AdcaptchaField extends NF_Fields_Recaptcha {

    protected $_name = 'adcaptcha';

    protected $_type = 'adcaptcha';

    protected $_templates = 'adcaptcha';

    protected $_section = 'misc';

    protected $_icon = 'filter';

    protected $_nicename;

    public function __construct() {
        parent::__construct();
        $this->_nicename = esc_html__( 'adCAPTCHA', 'adcaptcha' );
    }


    public function validate( $field, $data ) {

        $verify = new Verify();
        $response = $verify->verify_token();

        if ( $response === false ) {
            return __( 'Incomplete captcha, Please try again.', 'adcaptcha' );
        }
	}
}