<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	Auth Helper
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Jeff Fox
 * @description	Various helpers for auth functions
 */
// ------------------------------------------------------------------------

if ( ! function_exists('__hash')) {
	function __hash($password, $crypt, $obscured = NULL, $algorithm = "sha1") {
	  // whether to use user specified algorithm
	  $mode = in_array($algorithm, hash_algos());
	  // generate random salt
	  $salt = $crypt;
	  // hash it
	  $salt = $mode ? hash($algorithm, $salt) : sha1($salt);
	  // get the length
	  $slen = strlen($salt);
	  // compute the actual length of salt we will use
	  // 1/8 to 1/4 of the hash, with shorter passwords producing longer salts
	  $slen = max($slen >> 3, ($slen >> 2) - strlen($password));
	  // if we are checking password against a hash, harvest the actual salt from it, otherwise just cut the salt we already have to the proper size
	  $salt = $obscured ? __harvest($obscured, $slen, $password) : substr($salt, 0, $slen);
	  // hash the password - this is maybe unnecessary
	  $hash = $mode ? hash($algorithm, $password) : sha1($password);
	  // place the salt in it
	  $hash = __scramble($hash, $salt, $password);
	  // and hash it again
	  $hash = $mode ? hash($algorithm, $hash) : sha1($hash);
	  // cut the result so we can add salt and maintain the same length
	  $hash = substr($hash, $slen);
	  // ... do that
	  $hash = __scramble($hash, $salt, $password);
	  // and return the result
	  return $obscured && $obscured !== $hash ? false : $hash;
	}
}
if ( ! function_exists('__scramble')) {
	function __scramble($hash, $salt, $password) {
	  $k = strlen($password); $j = $k = $k > 0 ? $k : 1; $p = 0; $index = array(); $out = ""; $m = 0;
	  for ($i = 0; $i < strlen($salt); $i++)
	  {
		$c = substr($password, $p, 1);
		$j = pow($j + ($c !== false ? ord($c) : 0), 2) % (strlen($hash) + strlen($salt));
		while (array_key_exists($j, $index))
		  $j = ++$j % (strlen($hash) + strlen($salt));
		$index[$j] = $i;
		$p = ++$p % $k;
	  }
	  for ($i = 0; $i < strlen($hash) + strlen($salt); $i++)
		$out .= array_key_exists($i, $index) ? $salt[$index[$i]] : $hash[$m++];
	  return $out;
	}
}
if ( ! function_exists('__harvest')) {
	function __harvest($obscured, $slen, $password) {
	  $k = strlen($password); $j = $k = $k > 0 ? $k : 1; $p = 0; $index = array(); $out = "";
	  for ($i = 0; $i < $slen; $i++)
	  {
		$c = substr($password, $p, 1);
		$j = pow($j + ($c !== false ? ord($c) : 0), 2) % strlen($obscured);
		while (in_array($j, $index))
		  $j = ++$j % strlen($obscured);
		$index[$i] = $j;
		$p = ++$p % $k;
	  }
	  for ($i = 0; $i < $slen; $i++)
		$out .= $obscured[$index[$i]];
	  return $out;
	}
}

/* End of file auth_helper.php */
/* Location: ./system/helpers/auth_helper.php */