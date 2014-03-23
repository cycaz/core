<?php
class TMC_Helper_Validation {
    protected $_validationClass;
    protected $_validationType = array(
        'PresenceOf',
        'Email',
        'Identical',
        'ExclusionIn',
        'InclusionIn',
        'Regex',
        'StringLength',
        'Between',
        'Confirmation'
    );
    public function __construct() {
        if(!$this->_validationClass) $this->_validationClass = new Phalcon\Validation();
    }
    
    public function add($field,$type='',$options=array()) {
        if(is_array($field)) {
            #foreach($field as $k=>$v)
        } else {
            if(!in_array($type,$this->_validationType)) throw new Exception('Invalid Validation Type');
            $validatorClass = "\\Phalcon\\Validation\\Validator\\".$type;
            $this->_validationClass->add($field,new $validatorClass($options));            
        }
        return $this;
    }
    
    public function validate($values) {
        return $this->_validationClass->validate($values);
    }
}