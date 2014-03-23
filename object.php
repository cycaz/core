<?php
class TMC_Object {

    protected $_data = array();

    public function addData(array $arr)
    {
        foreach($arr as $index=>$value) {
            $this->setData($index, $value);
        }
        return $this;
    }

    public function unsetData($key=null)
    {
        if (is_null($key))
            $this->_data = array();
        else
            unset($this->_data[$key]);
        return $this;
    }

    public function setData($key, $value=null)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function getData($key='', $index=null)
    {
        if (''===$key) {
            return $this->_data;
        }

        $default = null;

        if (strpos($key,'/')) {
            $keyArr = explode('/', $key);
            $data = $this->_data;
            foreach ($keyArr as $i=>$k) {
                if ($k==='') {
                    return $default;
                }
                if (is_array($data)) {
                    if (!isset($data[$k])) {
                        return $default;
                    }
                    $data = $data[$k];
                } elseif ($data instanceof TMC_Object) {
                    $data = $data->getData($k);
                } else {
                    return $default;
                }
            }
            return $data;
        }

        if (isset($this->_data[$key])) {
            if (is_null($index))
                return $this->_data[$key];

            $value = $this->_data[$key];
            if (is_array($value)) {
                if (isset($value[$index]))
                    return $value[$index];
                return null;
            } elseif (is_string($value)) {
                $arr = explode("\n", $value);
                return (isset($arr[$index]) && (!empty($arr[$index]) || strlen($arr[$index]) > 0)) ? $arr[$index] : null;
            } elseif ($value instanceof TMC_Model_Object) {
                return $value->getData($index);
            }
            return $default;
        }
        return $default;
    }

    public function hasData($key='') {
        if (empty($key) || !is_string($key))
            return !empty($this->_data);
        return array_key_exists($key, $this->_data);
    }

    public function __call($method, $args) {
        $key = TMC_String::underscore(substr($method,3));
        switch (substr($method, 0, 3)) {
            case 'get' :
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                return $data;
            case 'set' :
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                return $result;
            case 'uns' :
                $result = $this->unsetData($key);
                return $result;
            case 'has' :
                return $this->hasData($key);
        }
    }

    public function __get($var) {
        $var = TMC_String::underscore($var);
        return $this->getData($var);
    }

    public function __set($var, $value) {
        $var = TMC_String::underscore($var);
        $this->setData($var, $value);
    }

    public function isEmpty() {
        if (empty($this->_data)) return true;
        return false;
    }

    public function serialize($attributes = array(), $valueSeparator='=', $fieldSeparator=' ', $quote='"') {
        $data = array();
        if (empty($attributes)) {
            $attributes = array_keys($this->_data);
        }

        foreach ($this->_data as $key => $value) {
            if (in_array($key, $attributes)) {
                $data[] = $key . $valueSeparator . $quote . $value . $quote;
            }
        }
        $res = implode($fieldSeparator, $data);
        return $res;
    }
}