<?php
/**
 * Created by PhpStorm.
 * User: trickymaster
 * Date: 3/23/14
 * Time: 1:18 AM
 */
class TMC_String {
    static private $_cache = array();
    public static function camelize($name) {
        if (isset(self::$_cache['uc_'.$name])) {
            return self::$_cache['uc_'.$name];
        }
        $result = self::uc_words($name,'');
        self::$_cache['uc_'.$name] = $result;
        return $result;
    }

    public static function uc_words($str,$destSep='_',$srcSep='_') {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }

    public static function underscore($name)
    {
        if (isset(self::$_cache['uc_'.$name])) {
            return self::$_cache['uc_'.$name];
        }
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$_cache['uc_'.$name] = $result;
        return $result;
    }
}