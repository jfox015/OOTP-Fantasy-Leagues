<?php
if (!defined('OFL_INSTALLING')) die("An illegal include operation was attempted. The operation was cancelled.");
define('BASEPATH', pathinfo(__FILE__, PATHINFO_BASENAME));
define('ABSPATH', dirname(dirname(__FILE__)).'/');
error_reporting(0);
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
$phpMinRequired = "5.6";
$mySqlMinRequired = "5.0";
/**
 * MINIMUM REQUIREMENTS CHECK
 * DO NOT PROCEED IF ANY OTHER THE BEASIC TECH REQUIREMENTS ARE NOT MET
 */
// CHECK PHP VERSION
$php_compatible = version_compare(phpversion() ,$phpMinRequired, '>');
if ( !$php_compatible )
	install_die( sprintf( /*WP_I18N_OLD_PHP*/'Your server is running <strong>PHP</strong> version %s but OOTP Fantasy Leagues requires at least '.$phpMinRequired.'.'/*/WP_I18N_OLD_PHP*/, phpversion() ) );
// CHECK MYSSQL EXTENSION STATUS
$mysql_compatible = extension_loaded('mysql');
if ( !$mysql_compatible )
	install_die( /*WP_I18N_OLD_MYSQL*/'Your server appears to be missing the <strong>MySQL extension</strong>. MySQl '.$mySqlMinRequired.' or higher required by the OOTP Fantasy Leagues.'/*/WP_I18N_OLD_MYSQL*/ );
// CHECK MYSSQL VERSION
ob_start();
phpinfo(INFO_MODULES);
$info = ob_get_contents();
ob_end_clean();
$info = stristr($info, 'Client API version');
preg_match('/[1-9].[0-9].[1-9][0-9]/', $info, $match);
$gd = $match[0]; 
$mysql_version = version_compare($gd, $mySqlMinRequired, '>=');
if ( !$mysql_version )	
	install_die( sprintf( /*WP_I18N_OLD_PHP*/'Your server is running <strong>MySQL</strong> version %s but OOTP Fantasy Leagues requires at least '.$mySqlMinRequired/*/WP_I18N_OLD_PHP*/, $gd ) );
$install_writable = is_writable(ABSPATH);
$root_writable = is_writable("../");
$config_writable = is_writable("../application/config/");
// UPDATE - VERSION 1.0.3
// CHECK FOR THE CURL EXTENSION
require_once('../application/libraries/Verifier.php');
$verify = new Verifier();
$has_curl = $verify->check_has_extension('curl');
