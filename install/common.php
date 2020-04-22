<?php
if (!defined('OFL_INSTALLING')) die("An illegal include operation was attempted. The operation was cancelled.");
define('BASEPATH', pathinfo(__FILE__, PATHINFO_BASENAME));
define('ABSPATH', dirname(dirname(__FILE__)).'/');
function install_die($message, $title = '', $args = array()) {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../images/favicon.png" />
    <title><?php echo $title ?> - Error</title>
	<link rel="stylesheet" href="install.css" type="text/css" />
</head>
<body id="error-page">
	<?php echo $message; ?>
</body>
</html>
<?php
	die();
} // END function


// MINIMUM SUPORT VARS

/**
 * UPDATE PROD 1.0.3
 * UPDATED MINIMUM REQUIREMENTS CHECK
 * DO NOT PROCEED IF ANY OTHER THE BEASIC TECH REQUIREMENTS ARE NOT MET
 */
// CHECK PHP VERSION
$phpMinRequired = "5.6";
$php_version   = phpversion();
$php_compatible    = version_compare( $php_version, $phpMinRequired, '>=' );
if ( !$php_compatible )
	install_die( sprintf('Your server is running <strong>PHP</strong> version %s but OOTP Fantasy Leagues requires at least '.$phpMinRequired, phpversion() ) );  //END if

$mySqlMinRequired = "5.0";
$mysql_version = "0";
$use_mysql = version_compare( $php_version, "7.0", '<' );
// CHECK MYSSQL EXTENSION STATUS

// IS MYSQL Loaded on the server?
$mysql_available = extension_loaded( 'mysql' ) || extension_loaded( 'mysqli' ) || extension_loaded( 'mysqlnd' );
if ( !$mysql_available )
	install_die( 'Your server appears to be missing the <strong>MySQL extension</strong>.');  //END if

// CHECK VERSION COMPATIBILITY
if ( $use_mysql ) {
	$mysql_version = mysql_get_server_info();
	$mysql_compat  = version_compare( $mysql_version, $required_mysql_version, '>=' );
} else {
	ob_start();
	phpinfo(INFO_MODULES);
	$info = ob_get_contents();
	ob_end_clean();
	$info = stristr($info, 'Client API version');
	preg_match('/[1-9].[0-9].[1-9][0-9]/', $info, $match);
	$mysql_version = $match[0]; 
	$mysql_compat = version_compare($mysql_version, $mySqlMinRequired, '>=');
} //END if

if ( !$mysql_version )	
	install_die( sprintf( 'Your server is running <strong>MySQL</strong> version %s but OOTP Fantasy Leagues requires at least '.$mySqlMinRequired, $mysql_version ) );



$install_writable = is_writable(ABSPATH);
$root_writable = is_writable("../");
$config_writable = is_writable("../application/config/");
// UPDATE - VERSION 1.0.3
// CHECK FOR THE CURL EXTENSION
require_once('../application/libraries/Verifier.php');
$verify = new Verifier();
$has_curl = $verify->check_has_extension('curl');
