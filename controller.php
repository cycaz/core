<?php
class TMC_Controller extends TMC_Object {
    protected $_filter = array();
    protected $_siteTemplate = '';
    
    public function __construct() {
        parent::__construct();
        $this->setSiteTemplate(TMC::getConfig()->getData('site_template'));
        $this->setSiteThemes(TMC::getConfig()->getData('site_themes'));
    }
    
    public function beforeRun() {}
    public function afterRun() {}
    
    public function run() {
        $this->beforeRun();
        $_action = $this->getAction();
        if(empty($_action)) $_action = 'index';
        call_user_func(array($this,$_action.'Action'));
        $this->afterRun();
    }
    
    public function prepareFilter() {
        if(!empty($_REQUEST['filter'])) {
            
        }
    }
    
    protected function renderBlock($file="") {
        return $this->render($file,false,false);
    }
    
    public function addAdditionalCss($filename,$top=0) {
        $filenames = $filename;
        if(!is_array($filename)) {
            $filenames = array($filename);    
        }
        if(!isset($this->_data['additional_css'])) $this->_data['additional_css'] = array();
        $files = array();
        foreach($filenames as $filename) {
            if(file_exists(BP.DS.'skin'.DS.$this->getSiteThemes().DS.'css'.DS.$filename)) {
                $files[basename($filename)] = $filename;
            }
        }
        if($top == 0) {
            $this->_data['additional_css'] = array_merge($this->_data['additional_css'],$files);
        } else {
            $this->_data['additional_css'] = array_merge($files,$this->_data['additional_css']);
        }
        return $this;
    }
    
    public function addAdditionalJs($filenames,$top=0) {
        if(!is_array($filenames)) {
            $filenames = array($filenames);    
        }
        if(!isset($this->_data['additional_js'])) $this->_data['additional_js'] = array();
        $files = array();
        foreach($filenames as $filename) {
            if(file_exists(BP.DS.'skin'.DS.$this->getSiteThemes().DS.'js'.DS.$filename)) {
                $files[basename($filename)] = $filename;
            }
        }
        if($top == 0) {
            $this->_data['additional_js'] = array_merge($this->_data['additional_js'],$files);
        } else {
            $this->_data['additional_js'] = array_merge($files,$this->_data['additional_js']);
        }        
        return $this;
    }
    
    public function getRequest() {
        return TMC::getHelper('tmc/request');
    }
    public function getSiteTemplate() {
        return $this->_siteTemplate;
    }
    
    public function setSiteTemplate($value) {
        $this->_siteTemplate = $value;
        return $this;
    }
    
    public function getSiteThemes() {
        return $this->_siteThemes;
    }
    
    public function setSiteThemes($value) {
        $this->_siteThemes = $value;
        return $this;
    }    
    
    public function getImageUrl($filename) {
        if(file_exists(BP.DS.'skin'.DS.$this->getSiteThemes().DS.'img'.DS.$filename))
            return _url('skin',$this->getSiteThemes().'/img',$filename);
        return _url('skin','default/img',$filename);
    }
    
    public function getJsUrl($filename) {
        if(file_exists(BP.DS.'skin'.DS.$this->getSiteThemes().DS.'js'.DS.$filename))
            return _url('skin',$this->getSiteThemes().'/js',$filename);
        return _url('skin','default/js',$filename);
    }
    
    public function getCssUrl($filename) {
        if(file_exists(BP.DS.'skin'.DS.$this->getSiteThemes().DS.'css'.DS.$filename))
            return _url('skin',$this->getSiteThemes().'/css',$filename);
        return _url('skin','default/css',$filename);
    }

    protected function getHTML($file) {
        $html = "";
        ob_start();
        if(!empty($file)!="") {            
            if(file_exists(BP.DS."templates".DS.$this->getSiteTemplate().DS."_elements".DS.$file.".php")) 
                include_once BP.DS."templates".DS.$this->getSiteTemplate().DS."_elements".DS.$file.".php";
            else if(file_exists(BP.DS."templates".DS.$this->getSiteTemplate().DS.$file.".php")) 
                include_once BP.DS."templates".DS.$this->getSiteTemplate().DS.$file.".php";
            else if(file_exists(BP.DS.'templates'.DS.'default'.DS.$file.".php")) 
                include_once BP.DS."templates".DS.'default'.DS.$file.".php";                
            else 
                echo("<h1>Template File ".$file." Not Found</h1>");        
        }
        $html = ob_get_contents();
        ob_end_clean();
        $html = str_replace("\n","",$html);
        $html = str_replace("\r","",$html);
        $html = str_replace("\t","",$html);
        return $html;
    }
    
    protected function renderJSON($json) {        
        @header('Content-type: application/json');
        echo json_encode($json);
        if(json_last_error()) echo json_last_error_msg();
        die;
    }
    protected function render($file="",$include_header=true,$include_footer=true) {    
        ob_start(array($this,'dorender'));
        if($include_header) $this->renderBlock('header');
        if(!empty($file)!="") {            
            if(file_exists(BP.DS."templates".DS.$this->getSiteTemplate().DS."_elements".DS.$file.".php")) 
                include_once BP.DS."templates".DS.$this->getSiteTemplate().DS."_elements".DS.$file.".php";
            else if(file_exists(BP.DS."templates".DS.$this->getSiteTemplate().DS.$file.".php")) 
                include_once BP.DS."templates".DS.$this->getSiteTemplate().DS.$file.".php";
            else if(file_exists(BP.DS.'templates'.DS.'default'.DS.$file.".php")) 
                include_once BP.DS."templates".DS.'default'.DS.$file.".php";                
            else 
                echo("<h1>Template File ".$file." Not Found</h1>");        
        }
        if($include_footer) $this->renderBlock('footer');
        ob_end_flush();
    }
    
    protected function dorender($buffer) {
        //while(strstr($buffer,">  ")) {
            //$html = str_replace(">  ",">",$buffer);
        //}
        #$buffer = str_replace("\n","",$buffer);
        #$buffer = str_replace("\r","",$buffer);
        return $buffer;
    }
    
    public function setPageMessage($message) {
        $_SESSION['message']['page'] = $message;
        return $this;
    }
    
    public function hasPageMessage() {
        return !empty($_SESSION['message']['page']);    
    }
    
    public function getPageMessage() {
        $message = $_SESSION['message']['page'];
        unset($_SESSION['message']['page']);
        return $message;
    }
    
    public function getMessageRaw() {
        if(!is_string($this->getData('message'))) {
            $messages = "";
            foreach($this->getData('message') as $message) {
                $messages .= $message."\n";
            }
            return $messages;
        }
        return $this->getData('message');
    }

    public function getMessage() {
        if(!is_string($this->getData('message'))) {
            $messages = "<ul>\n";
            foreach($this->getData('message') as $message) {
                $messages .= "<li>".$message."</li>\n";
            }
            $messages .= "</ul>\n";
            return $messages;
        }
        return $this->getData('message');
    }
    public function getFullSegment($position=0) {
        if(empty($position)) return $this->getData('full_segment');
        $position = $position - 1;
        $segments = $this->getData('full_segment');
        if(!empty($segments[$position])) {            
            return $segments[$position];
        }
        return false;
    }
    public function getSegment($position=0) {
        if(empty($position)) return $this->getData('segment');
        $position = $position - 1;
        $segments = $this->getData('segment');
        if(!empty($segments[$position])) {            
            return $segments[$position];
        }
        return false;
    }
}
?>
