<?php
namespace AdCaptcha\Plugin\NinjaForms\AdcaptchaField;

use NF_Abstracts_Field;

class AdcaptchaField extends NF_Abstracts_Field {
    /**
     * Name
     *
     * @var string
     */
    protected $_name = 'adcaptcha';

    /**
     * Type
     *
     * @var string
     */
    protected $_type = 'adcaptcha';

    /**
     * Template
     *
     * @var string
     */
    protected $_templates = 'checkbox';

    /**
     * Section
     *
     * @var string
     */
    protected $_section = 'misc';

    /**
     * Icon
     *
     * @var string
     */
    protected $_icon = 'filter';

    /**
     * Nicename
     *
     * @var string
     */
    protected $_nicename;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->_nicename = esc_html__( 'adCAPTCHA', 'adcaptcha' );
    }
}