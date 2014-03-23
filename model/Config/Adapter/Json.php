<?php
class TMC_Model_Config_Adapter_Json extends \Phalcon\Config implements \ArrayAccess
{

    /**
     * Phalcon\Config\Adapter\Json
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        if (!extension_loaded("json") )
            throw new Exception('Json extension not loaded');

        if (false === $result =json_decode(file_get_contents($filePath), true))
            throw new Exception('Configuration file ' . $filePath . ' can\'t be loaded');

        parent::__construct($result);
    }
}
