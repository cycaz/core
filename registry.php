<?php
/**
 * Created by PhpStorm.
 * User: trickymaster
 * Date: 3/23/14
 * Time: 12:24 AM
 */

class TMC_Registry  {
    static private $_registry = array();

    public function has($key) {
        return isset(self::$_registry[$key]);
    }

    public function get($key) {
        return self::$_registry[$key];
    }

    public function set($key,$val=null) {
        self::$_registry[$key] = $val;
        return $this;
    }

    public function reset($key) {
        self::set($key,null);
        return $this;
    }

}