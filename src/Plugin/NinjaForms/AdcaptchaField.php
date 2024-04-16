<?php
namespace AdCaptcha\Plugin\NinjaForms\AdcaptchaField;

use NF_Abstracts_Field;

class AdcaptchaField extends NF_Abstracts_Field {

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
}