<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Display Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Jeff Fox
 * @description	Various helpers for handling data lists
 */
// ------------------------------------------------------------------------

if ( ! function_exists('getTagArray')) {
	function getTagArray($tagStr,$table) {
		$tags = array();
		$ci =& get_instance();
		if (!empty($tagStr)) {
			$delim = " ";
			if (strpos($tagStr,',') > 0) {
				$delim = ",";
			}
			$tagArr = explode($delim,$tagStr);
			if (is_array($tagArr) && sizeof($tagArr) > 0) {
				foreach($tagArr as $tag) {
					$ci->db->like('tags', $tag);
					$ci->db->from('comics_core');
					$tagWeight = $ci->db->count_all_results();
					$ci->db->like('tags', $tag);
					$ci->db->from('comics_issues');
					$tagWeight += $ci->db->count_all_results();
					$tags = $tags + array(trim($tag) => $tagWeight);
				}
			}
		}
		return $tags;
	}
	//Example Return:
	// array('weddings' => 32, 'birthdays' => 41, 'landscapes' => 62, 'ham' => 51, 'chicken' => 23, 'food' => 91, 'turkey' => 47, 'windows' => 82, 'apple' => 27);	
}

// ------------------------------------------------------------------------

if ( ! function_exists('printTagCloud')) {
	function printTagCloud($tags,$href="#") {
        // $tags is the array
       
        arsort($tags);
       
        $max_size = 32; // max font size in pixels
        $min_size = 12; // min font size in pixels
       
        // largest and smallest array values
        $max_qty = max(array_values($tags));
        $min_qty = min(array_values($tags));
       
        // find the range of values
        $spread = $max_qty - $min_qty;
        if ($spread == 0) { // we don't want to divide by zero
                $spread = 1;
        }
       
        // set the font-size increment
        $step = ($max_size - $min_size) / ($spread);
       
        // loop through the tag array
        foreach ($tags as $key => $value) {
                // calculate font-size
                // find the $value in excess of $min_qty
                // multiply by the font-size increment ($size)
                // and add the $min_size set above
                $size = round($min_size + (($value - $min_qty) * $step));
      			if ($href != "#")
					$link = $href.$key;
                echo '<a href="'.$link.'" style="font-size: ' . $size . 'px" title="' . $value . ' things tagged with ' . $key . '">' . $key . '</a> ';
        } // END foreach
	} // END function
} // END if

// ------------------------------------------------------------------------

/**
 *	HIGHLIGHT WORDS.
 *	Search support method that applies a highlight class to any 
 *	searchterms found in the text search.
 *	@param		$string	The word string to be searched
 *	@param		$words	Array of waords to search for
 *	@param		$ajax	Flag for whrther to further escape returned markup
 *	@return		Modified String
 */
if ( ! function_exists('highlightWords')) {
	function  highlightWords($string,$words,$ajax=false){
		$words=explode(' ',$words);
		for($i=0;$i<sizeOf($words);$i++) {
			if($ajax==true){
				$string=str_ireplace($words[$i], '<span class=\"highlight\">'.$words[$i].'<\/span>', $string);
			} else {
				$string=str_ireplace($words[$i], '<span class="highlight">'.$words[$i].'</span>', $string);
			}
 		}
		return $string;
	} // END function
} // END if

/* End of file display_helper.php */
/* Location: ./system/helpers/display_helper.php */