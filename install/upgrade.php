<?php
/**
 * 	OOTP Fantasy League Upgrade Script
 *	This script handles upgrading the database tables and config files externally to 
 *	avoid runtime errors due to missing data columns and/or constants.
 *	
 */
/*----------------------------------------
/ 	COMMON FUNCTIONS
/---------------------------------------*/
define('OFL_INSTALLING', true);
define('BASEPATH', pathinfo(__FILE__, PATHINFO_BASENAME));
define('ABSPATH', dirname(dirname(__FILE__)).'/');
error_reporting(0);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="../images/favicon.png" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OOTP Fantasy League Upgrade Utility</title>
<link rel="stylesheet" href="install.css" type="text/css" />

</head>
<body>
<h1><img src="../images/ootp_fantasy_150.png" width="110" height="110" align="absmiddle" />
OOTP Fantasy Leagues Upgrade Utility</h1>
    
<p>Welcome to the OOTP Fantasy League upgrade process! Click &quot;Upgrade Now&quot; to update your OOTP Fantasy Leagues game!</p>
<?php
$step = (isset($_POST['submitted'])) ? $_POST['submitted'] : -1;
if ($step == -1) {
?>
<form id="setup" method="post" action="upgrade.php?submitted=1">
	<p class="step"><div id="waitDiv" style="display:none;"><img src="../images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...</div>
    <input type="hidden" name="submitted" value="1" />
    <div id="buttonDiv"><input type="button" name="btnFinished" id="btnFinished" value="Upgrade Now" class="button" /></div></p>
</form>
<script src="../js/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){		   
	$('#btnFinished').click(function(){
		$('div#buttonDiv').css('display','none');
		$('div#waitDiv').css('display','block');
		$('form#setup').submit();
	});	
});
</script>
<?php 
} else {
	if (file_exists('../application/config/constants.php')) {
		include_once('../application/config/constants.php');
		$db_err = $error = false;
		$errors = '';
		// -------------------------------
		//	CONTINUE UPGRADE PROCEDURE BY LOADING THE DATABASE
		// -------------------------------
		if (defined('DB_CONNECTION_FILE') && file_exists('./'.DB_CONNECTION_FILE)) {
			include_once('./'.DB_CONNECTION_FILE);
		} else if (file_exists('../application/config/database.php')) {
			include_once('../application/config/database.php');
			$tmpDb = mysql_pconnect($db['default']['hostname'],$db['default']['username'],$db['default']['password']) or die('Could not connect: '.mysql_error());
			//echo("DB = ".$db['default']['database']."<br />");
			mysql_select_db($db['default']['database']);
		} else {
			$db_err = true;
			$errors .= "<li>No Database connection could be established.";
		}
		// END if
		if (!$db_err) {
			$fr = fopen('./db_update.sql',"r");
			$db_errors = '';
			$queries = '';
			$errCnt=0;
			$prevQuery = '';
			while (!feof($fr)) {
				$query=fgets($fr);
				if ($query=="") {continue;} // END if
				$query=str_replace(", , );",",1,1);",$query);
				//$query=preg_replace("/([\xC2\xC3])([\x80-\xBF])/e","chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)",$query);
				$query=str_replace(", ,",",'',",$query);
				$query=str_replace("#IND",0,$query);
				$result=mysql_query($query,$tmpDb);
				$err=mysql_error($tmpDb);
				if (($err!="") && ($query!="")) {
					$db_errors .= $err.",query=".$query.",prevQuery=".$prevQuery."<br />";
					$errCnt++;
				} // END if
				$queries .= $query;
				$prevQuery = $query;
			} // END while 
			fclose($fr);
			if ($errCnt>0) $errors .= "<strong>Database errors occured</strong>.<br />".$db_errors; // END if
			
			//echo("errCnt = ".$errCnt."<br />");
			if ($errCnt == 0) {
				if (file_exists('../application/config/')) {
					if (is_writable('../application/config/')) {
						$fcs = file_get_contents("./constants_update.php");
						$fcs = str_replace("[WEB_SITE_URL]",SITE_URL,$fcs);
						$fcs = str_replace("[SITE_DIRECTORY]","/".DIR_APP_ROOT."/",$fcs);
						$fcs = str_replace("[HTML_ROOT]",DIR_WRITE_PATH,$fcs);
						$fh = fopen('../application/config/constants.php',"w");
						fwrite($fh, $fcs);
						fclose($fh);
						unset($fcs);
						chmod('../application/config/constants.php', 0666);
						unset($fh);
		
						$config_write = true;
					} else {
						$config_write = false;
					} // END if
				} // END if file_exists()
				?>
		        <h1>Upgrade Complete!</h1>
		        <p />
		        Your OOPT Fantasy Leagues mod has been successfully updated.
		         <ul>
		                        <?php
								if (!$config_write) { ?>
		                        	<li><code><?php echo($html_root);?>application/config/constants.php</code> - Copy and 
		                            paste the following code from <code>/install/constants_update.php</code> and save 
		                            it to <code>/application/config/constants.php</code> from your fantasy leagues root folder overwritting
		                            the existing fields.
		                            <p />
		                            <pre class="brush: php">define("SITE_URL",",<?php echo(SITE_URL); ?>");
		define("DIR_APP_ROOT","/<?php echo(DIR_APP_ROOT); ?>/");
		define("DIR_APP_WRITE_ROOT","<?php echo(DIR_APP_ROOT); ?>");
		define("DIR_WRITE_PATH","<?php echo(DIR_WRITE_PATH); ?>");</pre>
		                            </li>
		                        <?php 
								} // END if
								?>
		                        <script type="text/javascript" src="shighlight/shCore.js"></script>
								<script type="text/javascript" src="shighlight/shBrushPhp.js"></script>
		                        <?php if (!$config_write) { ?>
								<script type="text/javascript">SyntaxHighlighter.all();</script>
		                        <?php } ?>
		                        </ul>
		                        Complete your upgrade by deleting all files in the <code>install</code> directory BEFORE logging into the site again.
		                        <p />
		                        Head to the league <a href="../index.php">homepage</a> or <a href="../user/login/">login</a> now.
		                	<?php
				
			} else {
				$error = true;
			} // END if
		} else {
			$error = true;
		} // END if
		if ($error) { ?>
			<h1>An error occured.</h1>
			 <p />
			The following errors were encountered: 
			<ul>
			<?php echo($errors); ?>
			</ul>
			<?php 
		} // END if
	} else { 
		$basepath = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$basepath = str_replace("install/upgrade.php","",$basepath);
		?>
		<h1>An error occured.</h1>
		 <p />
		A required configuration file could not be found. Assure that <code><?php print($basepath); ?>/application/config/constant.php</code> exists. You 
		may need to reinstall your fantasy leagues mod to correct this issue and upgrade your site.
		<?php 
	}
} // END if		
?>
</body>
</html>