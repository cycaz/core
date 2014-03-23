<?php

class TMC_Model_Translate {
    protected $_adapter;
    protected $_locale;
    protected $_langDir;
    
    public function __construct() {
        $this->_langDir = BP.DS.'languages';
        $this->_locale = 'en_US';
        $this->_adapter = new TMC_Model_Translate_Adapter_Gettext(array(
            'locale' => $this->_locale,
            'file' => 'messages',
            'directory' => $this->_langDir
        ));
    }
    
    public function __() {
        $this->_adapter->__(func_get_args());   
    }
}