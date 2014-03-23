<?php

class TMC_Helper_Email {    
    public function filterEmail($email)
    {
        $rule = array("\r" => '',
                      "\n" => '',
                      "\t" => '',
                      '"'  => '',
                      ','  => '',
                      '<'  => '',
                      '>'  => '',
        );

        return strtr($email, $rule);
    }

    public function filterName($name)
    {
        $rule = array("\r" => '',
                      "\n" => '',
                      "\t" => '',
                      '"'  => "'",
                      '<'  => '[',
                      '>'  => ']',
        );

        return trim(strtr($name, $rule));
    }

    public function filterOther($data)
    {
        $rule = array("\r" => '',
                      "\n" => '',
                      "\t" => '',
        );

        return strtr($data, $rule);
    }
    
    public function formatAddress($email, $name)
    {
        if ($name === '' || $name === null || $name === $email) {
            return $email;
        } else {
            $encodedName = $this->encodeHeader($name);
            if ($encodedName === $name &&
                    ((strpos($name, '@') !== false) || (strpos($name, ',') !== false))) {
                $format = '"%s" <%s>';
            } else {
                $format = '%s <%s>';
            }
            return sprintf($format, $encodedName, $email);
        }
    }
    
    public function encodeHeader($value)
    {
        if (Zend_Mime::isPrintable($value) === false) {
            if ($this->getHeaderEncoding() === Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
                $value = Zend_Mime::encodeQuotedPrintableHeader($value, $this->getCharset(), Zend_Mime::LINELENGTH, Zend_Mime::LINEEND);
            } else {
                $value = Zend_Mime::encodeBase64Header($value, $this->getCharset(), Zend_Mime::LINELENGTH, Zend_Mime::LINEEND);
            }
        }

        return $value;
    }    
}