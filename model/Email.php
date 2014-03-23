<?php

class TMC_Model_Email extends TMC_Object {
        
    public function __construct() {
        if(TMC::getConfig('global/email/smtp/hostname')) $this->setHost(TMC::getConfig('global/email/smtp/hostname'));
        if(TMC::getConfig('global/email/smtp')) $this->setConfig(TMC::getConfig('global/email/smtp'));
        $this->setFrom(TMC::getConfig('global/email/smtp/username'));
    }
    public function getAdapter() {
        if(!$this->hasAdapter()) {
            $mail = new Zend_Mail('utf-8');
            $transporter = new Zend_Mail_Transport_Smtp($this->getHost(),$this->getConfig());
            $mail->setDefaultTransport($transporter);
            $this->_data['adapter'] = $mail;
        }
        return $this->_data['adapter'];
    }
    public function setFrom($email,$name='') {
        $this->getAdapter()->setFrom($email,$name);
        return $this;
    }
    public function addCc($email,$name='') {
        $this->getAdapter()->addCc($email,$name);
        return $this;    
    }

    public function addBcc($email,$name='') {
        $this->getAdapter()->addBcc($email,$name);
        return $this;    
    }
    
    public function addTo($email,$name='') {
        $this->getAdapter()->addTo($email,$name);
        return $this;
    }
    
    public function send() {
        if($this->hasBody()) {                                
            $this->getAdapter()->setSubject($this->getSubject());                        
            $this->getAdapter()->setBodyText($this->getBody());            
            if($this->hasBodyHtml()) $this->getAdapter()->setBodyHtml($this->getBodyHtml());            
            $this->getAdapter()->send();
        }
        return $this;
    }    
}