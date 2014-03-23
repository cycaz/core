<?php
class TMC_Block extends TMC_Object {
    
    protected $_template;
    
    public function _construct() {
        parent::_construct();        
    }
    
    public function setTemplate($value) {
        $this->_template = $value;
        return $this;
    }
    
    public function getTemplate() {
        return $this->_template;
    }
    
    public function getDefaultTemplate() {
        $className = get_class($this);
        //remove Block_
        $blockName = str_replace('Block_','',$className);
        //replace _ with /
        $blockTemplatePath = str_replace('_','/',strtolower($blockName));
        //return path with php extension
        return $this->getTemplatePath($blockTemplatePath);
    }
    
    public function getTemplatePath($template=null) {
        if(!empty($template)) $this->setTemplate($template);
        if(file_exists(BP.DS."templates".DS.TMC::getConfig()->getSiteTemplate().DS."_elements".DS.$this->getTemplate().".php")) {
            return BP.DS."templates".DS.TMC::getConfig()->getSiteTemplate().DS."_elements".DS.$this->getTemplate().".php";
        } else if(file_exists(BP.DS."templates".DS.TMC::getConfig()->getSiteTemplate().DS.$this->getTemplate().".php")) {
            return BP.DS."templates".DS.TMC::getConfig()->getSiteTemplate().DS.$this->getTemplate().".php";
        } else if(file_exists(BP.DS.'templates'.DS.'default'.DS.$this->getTemplate().".php")) {
            return BP.DS."templates".DS.'default'.DS.$this->getTemplate().".php";                
        }
        return false;
    }
    
    public function getHtml() {
        ob_start();
        if($this->getTemplate()) {
            include($this->getTemplatePath());
        } else {
            include($this->getDefaultTemplate());
        }
        $html = ob_get_contents();
        ob_end_clean();
        $html = preg_replace("/>\s+/",">",$html);
        $html = preg_replace("/\s+</","<",$html);
        $html = trim($html);
        
        return $html;
    }    
}