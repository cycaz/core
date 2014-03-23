<?php
class TMC_Helper_Request {
    protected $_adapter;
    
    public function __construct() {
        if(empty($this->_adapter)) $this->_adapter = new Phalcon\Http\Request();
    }
    
    public function getPost($name='',$filter=null,$defaultValue=null) {        
        if(isset($name) && $name != '') {            
            $keyArr = explode('/', $name);
            $data = $_POST;
            foreach ($keyArr as $i=>$k) {
                if ($k==='') {
                    return $defaultValue;
                }
                if (!isset($data[$k])) {
                    return $defaultValue;
                } else {
                    $data = $data[$k];
                }
            }
            if(!empty($data) && !empty($filter)) {
                $data = TMC::getHelper('filter')->sanitize($data,$filter);
            }
            return $data;
        } else {
            return $_POST;
        }
    }
    
    public function __call($name,$args) {
        if(method_exists($this->_adapter,$name)) {
            return $this->_adapter->$name($args);
        } else {
            throw Exception(__("Method not exist"));
        }
    }
}