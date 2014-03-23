<?php
final class TMC
{
    static private $_registry;

    public static function autoLoader($className) {
        $classFile = self::uc_words($className, DIRECTORY_SEPARATOR).'.php';
        if(file_exists($classFile)) {
            include($classFile);
        }
    }

    public static function isLocal() {
        return file_exists(BP.DS.'local');
    }

    protected function _underscore($name) {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        return $result;
    }
    
    public static function getHelper($helperClass = '', $arguments = array()) {
        $className = self::getClassName('helper',$helperClass);
        if (class_exists($className)) {            
            $obj = new $className($arguments);
            return $obj;        
        } else if(!strstr($helperClass,'/')) {
            $className = 'TMC_Helper_'.uc_words($helperClass);
            $obj = new $className();
            return $obj;
        }        
        return false;         
    }
    public static function getRegistry() {
        if(empty(self::$_registry)) {
            self::$_registry = new TMC_Registry();
        }
        return self::$_registry;
    }

    public static function getRequest() {
        $registryKey = '_helper/request';
        if (!self::registry($registryKey)) {
            self::register($registryKey, new TMC_Helper_Request());
        }
        return self::registry($registryKey);
    }

    public static function getBlockSingleton($blockClass='', array $arguments=array()) {
        $registryKey = '_singleton/'.$blockClass;
        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getBlock($blockClass, $arguments));
        }
        return self::registry($registryKey);
    }
    
    public static function getBlock($blockClass = '',$arguments = array()) {
        $className = self::getClassName('block',$blockClass);
        if (class_exists($className)) {            
            $obj = new $className($arguments);
            return $obj;
        }
        return false;
    }
    
    public static function getModel($modelClass = '', $arguments = array()) {
        $className = self::getClassName('model',$modelClass);
        if (class_exists($className)) {            
            $obj = new $className($arguments);
            return $obj;
        } else if(!strstr($modelClass,'/')) {            
            $obj = new TMC_Model($modelClass);
            return $obj;
        }
        return false;         
    }
    
    private static function getClassName($type='',$classId) {
        $classArr = explode('/', trim($classId));
        $module = $classArr[0];
        if(strtolower($module)=='tmc') $module='TMC';
        if(empty($classArr[1])) $classArr[1] = 'base';
        array_shift($classArr);
        $className = uc_words($module.'_'.$type.'_'.implode('_',$classArr));
        return $className;
    }

    private static function uc_words($str,$destSep='_',$srcSep='_') {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }

    public static function run() {
        try {
            $paths[] = BP . DS . 'lib';
            $paths[] = BP . DS . 'app';
            $appPath = implode(DS, $paths);
            $originalIncluePath = get_include_path();
            set_include_path($appPath . DS . $originalIncluePath);
            set_error_handler(array('TMC','printError'));
            set_exception_handler(array('TMC','printException'));

            self::$_config  = new TMC_Config();
            if(!file_exists(BP.DS.'config.php')) throw new Exception("No Config File");
            
            @include(BP.DS.'config.php');
            if(empty($cfg)) throw new Exception("No Config Value");
            self::$_config->addData($cfg);
            $request = array();
            if(!empty($_GET['u'])) {
                $request = explode("/",$_GET['u']);
            }
            $_module = "Index";
            $_controller = "Index";
            $_action = "index";
            if(!empty($request['0'])) $_module = ucwords($request['0']);
            if(!empty($request['1'])) $_controller = ucwords($request['1']);
            if(!empty($request['2'])) $_action = $request['2'];            
            array_shift($request);
            $raw_request = $request;
            array_shift($request);
            array_shift($request);            
            #echo uc_words($_action);
            if(!file_exists(BP.DS."modules".DS.$_module)) throw new Exception('<h1>No Module</h1>');
            #echo $_module.'_Controller_'.$_controller;
            
            if(!class_exists($_module.'_Controller_'.$_controller)) {
                $_controller = "Index";
                if(!class_exists($_module.'_Controller_Index')) throw new Exception('<h1>No Controller</h1>');
            }
            
            $className = $_module.'_Controller_'.$_controller;
            if(!method_exists($className,$_action.'Action')) $_action = 'index';            
            $controller = new $className();
            $controller->setFullSegment($raw_request);
            $controller->setSegment($request);
            if(!method_exists($className,$_action)) {
                $controller->setModule($_module)->setController($_controller)->setAction($_action);
                $controller->run();
            } else {
                throw new Exception('Bad Request');
            }
            
        } catch (Exception $e) {
            self::printException($e);
        }
    }
    
    public static function printError($errno,$errstr,$errfile,$errline) {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }
        switch ($errno) {
            case E_USER_ERROR:
                echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
                echo "  Fatal error on line $errline in file $errfile";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                exit(1);
            break;    
            case E_USER_WARNING:
                echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
            break;
            case E_USER_NOTICE:
                echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
            break;
            default:
                echo "Unknown error type: [$errno] $errstr in $errfile on $errline<br />\n";
            break;
        }    
        return true;
    }

    public static function printException(Exception $e, $extra = '') {
        print '<pre>';
        if (!empty($extra)) {
            print $extra . "\n\n";
        }
        print $e->getMessage() . "\n\n";
        print $e->getTraceAsString();
        print '</pre>';
        #die;     
    }

    function uc_words($str, $destSep='_', $srcSep='_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }

    public static function __() {
        $args = func_get_args();
        $text = array_shift($args);
        $result = @vsprintf($text, $args);
        return $result;
    }
}  
?>
