<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Date Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Jeff Fox
 * @description	Various helpers for handling data lists
 */

// ------------------------------------------------------------------------

if ( ! function_exists('isLeap')) {
	function isLeap($year) {
		return ($year % 4 == 0)? ($year % 100 == 0)? ($year % 400 == 0)? true:false:true:false;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('validDayOfMonth')) {
	function validDayOfMonth($month,$year,$day,$leap = false) {
		// TEST FOR VALID FEBRUARY DATE
		if ($month == 2) {
			$err = false;
			$cutOff = 28;
			if ($leap && $day > 29) {
				return (array(-1,'FEBRUARY',29));
				$cutoff = 29;
			} else if (!$leap && $day > 28) {
				return (array(-1,'FEBRUARY',28));
			}
		// TEST FOR VALID APRIL, JUNE, SEPT. AND NOVEMBER DATES
		} else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
			if ($day > 30) {
				$monthLabel = '';
				switch ($month) {
					case 4: $monthLabel = 'April'; break;
					case 6: $monthLabel = 'June'; break;
					case 9: $monthLabel = 'September'; break;
					case 11: $monthLabel = 'November'; break;
				}
				return array(-1,$monthLabel,30);
			}
		}
		// ALL OTHER DATES ARE 31 AND THEREFORE VALID
		return (array(1,'',''));
	}// END function
} // END if


// ------------------------------------------------------------------------

if ( ! function_exists('testValidBirthDate')) {
	function testValidBirthDate($year,$month,$day,$limit) {
		if ($year == $limit) {
			if ($month > date('m')) {
				return false;
			} else if ($month == date('m') && $day > date('d')) {
				return false;
			}
		}
		return true;		
	}
}
/* End of file dataList_helper.php */
/* Location: ./system/helpers/dataList_helper.php */