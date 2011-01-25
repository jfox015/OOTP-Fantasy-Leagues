<?php
/**
 *  Date Class for handling dates beyond the 1970 and 2038 in both php 4 and 5
 *  Requires ADODB_Date library file to be in the same path as this library
 *  Written by Raymond Irving 2008
 *
 *  This Class Library is distributed under the terms of the GNU LGPL license with 
 *  the exception of the ADOdb Date Library which is distributed under it's respective license(s). 
 *  See the adodb-time.inc.php file for furthor information.
 *  
 *  @Version: 1.0.2
 */

/**
 * Creates a Date Class objects that's used to convert and format DateTime values
 */
class Date {
    
    var $_timestamp = null;
    
    /**
     * Date Class Constructor
     * @param $str (Optional) String containing a valid date in the formats: 
     * Date: dd mmm yyyy,<br />mmm dd yyyy,<br /> mm/dd/yyy,<br /> yyyy/mm/dd,<br /> dd/mm/yyyy. Also supports the delimitors "." and "-". Example: mm-dd-yyyy or mm.dd.yyyy
     * Time: hh:mm:ss - Supports the time format that is supported by PHP
     */
    function Date($str=''){
        // make sure we have the adodb date libray loaded
        if (!function_exists('adodb_mktime')) {
            $datelib = dirname(__FILE__).'/adodb-time.inc.php';
            if (file_exists($datelib)) {
                include_once ($datelib);
            }           
            if (!function_exists('adodb_mktime')) 
                die ('Date Class: Unable to load the ADOdb Date Library.');
        }

        $this->setDate($str);               
    }

    /**
     * Sets the Date/Time for the Date object
     * @return void
     * @param $str String containing a valid date
     */
    function setDate($str) {
        if (is_numeric($str)) $this->_timestamp = $str;
        else {
            $this->_timestamp = $this->_makeTimestamp($str);        
        }
    }   
    
    // internal function
    function _makeTimestamp($str){
        $d = ($str && $str!='now') ? $this->parse($str) : getdate();
        return adodb_mktime(
            $d['hours'],
            $d['minutes'],
            $d['seconds'],
            $d['mon'],$d['mday'],$d['year']);     
    }
    
    /**
     * Returns an ADODB Date timestamp
     * @return int 
     */
    function getTimestamp($date = '') {
        return ($date) ? $this->_makeTimestamp($date) : $this->_timestamp;
    }
    
    /**
     * Format and returns a date string. This function used the PHP date() format.
     * @return String
     * @param $fmt String
     * @param $dtTime Mixed [optional] DateTime String or ADODB Date TimeStamp
     */
    function format($fmt, $dtTime = ''){
        $ts = ($dtTime && is_numeric($dtTime)) ? $dtTime : $this->getTimestamp($dtTime);
        return adodb_date($fmt,$ts);
    }
    
    /**
     * Parses a date string and returns an array containing the date parts otherwise false
     * It's works great with date values returned from MSSQL, MySQL and others.
     * @return Array Returns an array that contains the date parts: year, month, mday, minutes,hours and seconds
     * @param $str String Supported Date/Time string format
     */
    function parse($str) {
        $delim = '';
        $dpart = array();
        
        $dt = preg_replace('/(\s)+/',' ',strtolower($str)); // remove extra white spaces
        
        if (strpos($dt,'-') > 0) $delim = '-';
        if (strpos($dt,'/') > 0) $delim = '/';
        if (!$delim && ($d = strpos($dt,'.'))>0) {
            $c = strpos($dt,':');
            if (!$c || ($c > $d)) $delim = '.';
        }
        
        if ($delim=='-' || $delim=='/' || $delim=='.') {
            @list($date,$time) = explode(' ',$dt);
            $date = explode($delim,$date);          
            $date[] = $time;
        }
        else {
            $date = explode(' ',$dt,4);
        }
        
        foreach ($date as $i => $v) $date[$i] = trim(trim($v,','));
        
        @list($d1,$d2,$d3,$time) = $date;
        
        $months = array(
            'jan','feb','mar','apr','may','jun',
            'jul','aug','sep','oct','nov','dec'
        );
        
        // get year
        if ($d1 > 1000) { $dpart['year'] = $d1; unset($date[0]); }
        if ($d3 > 1000) { $dpart['year'] = $d3; unset($date[2]); }
        
        // get month - defaults to mm-dd-yyyy 
        if (!is_numeric($d1)) for ($i=0; $i<12; $i++) {                     // mmm dd yyyy
            if (strstr($d1,$months[$i])!=false) {
                $dpart['mon'] = $i+1;
                unset($date[0]);
                break;
            }
        }
        else if (!is_numeric($d2)) for($i=0; $i<12; $i++) {
            if (strstr($d2,$months[$i])!=false) {
                $dpart['mon'] = $i+1;
                unset($date[1]);
                break;
            }
        }
        else {
            if ($d2 <= 12 && $d1 >= 1500) { $dpart['mon'] = $d2; unset($date[1]); } // yyyy-mm-dd
            if ($d1 <= 12 && $d3 >= 1500) { $dpart['mon'] = $d1; unset($date[0]); } // mm-dd-yyyy
            else if ($d1 > 12 && $d3 >= 1500) { $dpart['mon'] = $d2; unset($date[1]); } // dd-mm-yyyy     
        }
        
        // get day
        unset($date[3]);
        $dpart['mday'] = implode('',$date);
        if (!is_numeric($dpart['mday'])||$dpart['mday']> 31) return false;
        
        // get time info. use 1 jan 2008 as a starting date
        $t = strtotime('1-jan-2008 '.$time);
        if($t) {
            $t = getdate($t);           
            $dpart['hours'] = $t['hours'];
            $dpart['minutes'] = $t['minutes'];
            $dpart['seconds'] = $t['seconds'];
        }
                
        return $dpart;
        
    }
}
    
?>