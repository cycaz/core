<?php

class TMC_Model_Email_Adapter extends TMC_Object {
    protected $newline = "\r\n";
    protected $_errorMessage = array();
    protected $_debugMessage = array();
    protected $_data = array(
        'hostname'      => 'localhost',
        'port'          => '25',
        'timeout'       => '5',
        'username'      => '',
        'password'      => '',
        'encoding'      => '8bit',
        'crypt'         => '',
        'useragent'    => 'TMC Mailer',
        'priority'      => '3',
        'multipart'     => 'mixed',
        'charset'       => 'utf-8',
        'datatype'          => 'text'
    );
    
    public function __construct($options=array()) {
        if(!$this->hasFrom()) $this->setFrom($this->getUsername());
        if(!empty($options)) {
            $this->_data = array_merge($this->_data,$options);
        }
    }

    protected function addHeader($name,$value='',$append=false) {
        $value = TMC::getHelper('email')->filterOther($value);
        $value = TMC::getHelper('email')->encodeHeader($value);

        if (isset($this->_headers[$name])) {
            $this->_data['headers'][$name][] = $value;
        } else {
            $this->_data['headers'][$name] = array($value);
        }

        if ($append) {
            $this->_data['headers'][$name]['append'] = true;
        }
        return $this;
    }
    
    protected function prepareHeader() {
        $parsedHeader = "";
        $this->setHeader('X-Sender', $this->getFrom())
        ->setHeader('X-Mailer', $this->getUseragent())
        ->setHeader('X-Priority', $this->getPriority())
        ->setHeader('Message-ID','<'.uniqid().strstr($this->getFrom(), '@').'>')
        ->setHeader('Return-path','<'.$this->getFrom().'>')
        ->setHeader('Mime-Version', '1.0')
        ->setHeader('Content-Type','text/plain; charset='.$this->getCharset())
        ->setHeader('Date',date("D, j M Y H:i:s"))
        ->setHeader('Auto-Submitted','auto-generated')
        ->setHeader('Precedence','Bulk')
        ->setHeader('Content-Transfer-Encoding',$this->getEncoding());
        
        foreach($this->getHeaders() as $headerKey => $headerValue) {
            $parsedHeader .= $headerKey.": ".$headerValue.$this->newline;
        }
        return $parsedHeader.$this->newline;
    }
    
    protected function buildMessage() {
        return $this->getMessage();    
    }
    
    public function send() {
        return true;
    }
    
    public function error($message) {
        $this->_errorMessage[] = $message;
        return $this;
    }
    
    protected function cleanEmail($email) {
        return $email;  
    }    
    
    public function getDebugMessages() {
        return $this->_debugMessage;
    }
}