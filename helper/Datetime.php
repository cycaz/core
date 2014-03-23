<?php

class TMC_Helper_Datetime {
    const TIME_OFFSET=7;
    
    public function format($date,$format='human') {
        if($format == 'human') {
            $formatted = $this->formatHuman($this->timestamp($date));
        } else {
            $formatted = date($format,$this->timestamp($date));
        }
        return $formatted;
    }
    
    public function timestamp($input = null) {
        if (is_null($input)) {
            $result = gmdate('U');
        } else if (is_numeric($input)) {
            $result = $input;
        } else {
            $result = strtotime($input);
        }
        return $result;
    }
    
    public function formatHuman($timestamp) {
        $difference = time() - $timestamp;
        $periods = array("second", "minute", "hour", "day", "week", "month", "years");
        $lengths = array("60","60","24","7","4.35","12");
 
        if ($difference >= 0) {
            $ending = "ago";
        } else {
            $difference = -$difference;
            $ending = "to go";
        }
 
        $arr_len = count($lengths);
        for($j = 0; $j < $arr_len && $difference >= $lengths[$j]; $j++) {
                $difference /= $lengths[$j];
        }
        $difference = round($difference);
        
        if($difference != 1) {
                $periods[$j].= "s";
        }        
        // Default format
        $text = "$difference $periods[$j] $ending";
        if($periods[$j] == 'seconds') $text = 'a few seconds ago';
        if($periods[$j] == 'minute') $text = 'about an minute ago';
        
        if($j > 2) {
            // future date over a day formate with year
            if($ending == "to go") {
                    if($j == 3 && $difference == 1)
                        $text = "Tomorrow at ". date("g:i a", $timestamp);
                    else
                        $text = date("F j, Y \a\\t g:i a", $timestamp);
                    return $text;
            }
            if($j == 3 && $difference == 1) {
                 // Yesterday
                $text = "Yesterday at ". date("g:i a", $timestamp);
            } else if($j == 3) {
                // Less than a week display -- Monday at 5:28pm
                $text = date("l \a\\t g:i a", $timestamp);
            } else if($j < 6 && !($j == 5 && $difference == 12)) {
                // Less than a year display -- June 25 at 5:23am
                $text = date("F j \a\\t g:i a", $timestamp);
            } else  {
                // if over a year or the same month one year ago -- June 30, 2010 at 5:34pm
                $text = date("F j, Y \a\\t g:i a", $timestamp);
            }
        }
        return $text;
    }    
}