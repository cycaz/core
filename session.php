<?php

class TMC_Model_Session {
    protected $_adapterName = 'files';
    protected $_adapterClass = 'TMC_Model_Session_Adapter_Files';
    
    public function __construct() {
        $className = 'TMC_Model_Session_Adapter_'.$this->_adapterName;
        if(class_exists($className)) $this->_adapterClass = new $className(func_get_args());
    }

    public function start()
    {
        return $this->_adapterClass->start();
    }

    public function setOptions($options)
    {
        return $this->_adapterClass->setOptions($options);
    }
    
    public function getOptions() {
        return $this->_adapterClass->getOptions();
    }
    
    public function get($index,$defaultValue=null) {
        return $this->_adapterClass->get($index,$defaultValue);
    }
    
    public function set($index,$value) {
        return $this->_adapterClass->set($index,$value);
    }
    
    public function has($index) {
        return $this->_adapterClass->has($index);
    }
    
    public function remove($index) {
        return $this->_adapterClass->remove($index);
    }
    
    public function getId() {
        return $this->_adapterClass->getId();
    }
    
    public function isStarted() {
        return $this->_adapterClass->isStarted();
    }
    
    public function destroy() {
        return $this->_adapterClass->destroy();
    }
}