<?php

class TMC_Helper_String {
    
    static $_cache = array();
    
    public static function uc_words($str, $destSep='_', $srcSep='_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }
}