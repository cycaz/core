<?php

class TMC_Model_Email_Adapter_Smtp extends TMC_Model_Email_Adapter {

    public function __destruct() {
        $this->disconnect();
    }    
    protected function authenticate() {    
        if ($this->getUsername() == ""  AND  $this->getPassword() == "") {
            $this->error(__('SMTP: Please check your configuration of Email Smtp User and Email Smtp Password'));
            return FALSE;
        }            
        $response = $this->query('AUTH LOGIN');
        if (strncmp($response, '334', 3) != 0) {
            $this->error(__('SMTP: AUTH LOGIN Failed.%s', $response));
            return FALSE;
        }        
        
        $response = $this->query(base64_encode($this->getUsername()));
        if (strncmp($response, '334', 3) != 0) {
            $this->error(__('SMTP: Incorrect Username.%s', $response));
            return FALSE;
        }
        
        $response = $this->query(base64_encode($this->getPassword()));
        if (strncmp($response, '235', 3) != 0) {
            $this->error(__('SMTP: Incorrect Password.%s', $response));
            return FALSE;
        }
        return TRUE;
    }
    
    protected function connect() {
        $ssl = NULL;
        if ($this->getCrypt() == 'ssl')
            $ssl = 'ssl://';
        $this->setConnection(fsockopen($ssl.$this->getHostname(),
                                                                        $this->getPort(),
                                                                        $errno,
                                                                        $errstr,
                                                                        $this->getTimeout()));
        
        if ( ! is_resource($this->getConnection())) {
                $this->error('SMTP:', $errno." ".$errstr);
                return FALSE;
        }
        
        $this->error($this->getResponse());

        if ($this->getCrypt() == 'tls')
        {
            if($this->getEncoding() == '8bit')
            $this->query(sprintf('EHLO %s',$this->getHostname()));
            $this->query('STARTTLS');
            if(false === stream_socket_enable_crypto($this->getConnection(), TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->error(__("SMTP: Can't start TLS."));
                return false;
            }
        }
        $this->query(sprintf('EHLO %s',$this->getHostname()));        
        return $this;
    }
    
    protected function disconnect() {
        if(is_resource($this->getConnection())) fclose($this->getConnection());
    }
    
    protected function query($data) {        
        if(is_resource($this->getConnection())) {
            $query = fwrite($this->getConnection(), $data . $this->newline);
            $response = $this->getResponse();
            $this->_debugMessage[] = array($data=>$response);
            if (!$query) {
                $this->error(__('SMTP: send query failed', $data));
            }
            
            return $response;
        } else {
            $this->error(__("SMTP: Timed out"));
            return false;
        }
    }
        
    protected function getResponse() {
        $data = "";
        while ($str = fgets($this->getConnection(), 512)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    }

    
    public function send() {
        $this->connect();
        if($this->authenticate()) {
            $this->query(sprintf('MAIL FROM: <%s>', $this->getFrom()));
            if(!$this->hasTo()) {
                $this->error(__('SMTP: TO params must filled.'));
                return FALSE;
            }
            $this->query(sprintf('RCPT TO: <%s>',implode(">,<",$this->getTo())));            
            if($this->hasCc() && sizeof($this->getCc()) > 0) $this->query(sprintf('RCPT TO: <%s>',implode(">,<",$this->getCc())));
            if($this->hasBcc() && sizeof($this->getBcc()) > 0) $this->query(sprintf('RCPT TO: <%s>',implode(">,<",$this->getBcc())));
            $this->query('DATA');
            $response = $this->query($this->prepareHeader() . $this->buildMessage().$this->newline.'.');            
            $this->error($response);    
            if (strncmp($response, '250', 3) != 0) {
                $this->error(__('SMTP: %s', $response));
                return FALSE;
            }    
            $this->query('QUIT');
        }
        
        return TRUE;
    }    
}