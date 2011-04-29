<?php
/**
 * 	OOTP Fantasy League Install Script
 *	This script handles installing the database tables and requires a number of 
 *	base configuration settigns to get started.
 *	
 */
/*----------------------------------------
/ 	COMMON FUNCTIONS
/---------------------------------------*/
define('OFL_INSTALLING', true);
include_once('./common.php');
include_once('./constants_install.php');
include_once('../application/helpers/auth_helper.php');
/* DEPRECATED 
function get_config_defaults() {
	return array('sim_length'=>7,
				 'min_game_current'=>5,
				 'draft_period'=>'0000-00-00',
				 'season_start'=>'0000-00-00',
				 'min_game_last'=>20,
				 'current_period'=>1,
				 'last_sql_load_time'=>'0000-00-00',
				 'active_max'=>15,
				 'reserve_max'=>2,
				 'injured_max'=>1,
				 'draft_rounds_max'=>16,
				 'default_scoring_periods'=>24,
				 'last_process_time'=>'0000-00-00',
				 'max_sql_file_size'=>10,
				 'draft_rounds_min'=>16,
				 'useWaivers'=>1);
		
} // END function*/

function display_setup_form( $error = null ) {
?>
<form id="setup" method="post" action="install.php?step=2">
	<table class="form-table">
		<tr>
			<th scope="row"><label for="site_name">Site Name</label></th>
			<td><input name="site_name" type="text" id="site_name" size="25" 
            value="<?php if (isset($_POST['site_name'])) { echo($_POST['site_name']); } else { echo("OOTP Fantasy League"); } ?>" /></td>
            <td></td>
		</tr>
        <tr>
			<th scope="row"><label for="ootp_league_name">OOTP League Name</label></th>
			<td><input name="ootp_league_name" type="text" id="ootp_league_name" size="25" 
			value="<?php if (isset($_POST['ootp_league_name'])) { echo($_POST['ootp_league_name']); } else { echo(""); } ?>" /><br /></td>
            <td></td>
		</tr>
		<tr>
			<th scope="row"><label for="ootp_league_abbr">OOTP League Abbreviation</label></th>
			<td><input name="ootp_league_abbr" type="text" id="ootp_league_abbr" size="25" 
			value="<?php if (isset($_POST['ootp_league_abbr'])) { echo($_POST['ootp_league_abbr']); } else { echo(""); } ?>" /><br /></td>
            <td></td>
		</tr>
		<tr>
			<th scope="row"><label for="ootp_league_id">OOTP League ID</label></th>
			<td><input name="ootp_league_id" type="text" id="ootp_league_id" size="25" 
			value="<?php if (isset($_POST['ootp_league_id'])) { echo($_POST['ootp_league_id']); } else { echo("100"); } ?>" /></td>
            <td>Usually 100<br /></td>
		</tr>
        <tr>
			<td colspan="3"><h2>Paths</h2></td>
		</tr>
        <?php
		// SET THE DEFAULT PATH SEPERATOR
		if (substr(PHP_OS, 0, 3) == 'WIN') {
			$PATH_SEPERATOR = "\\";
		} else {
			$PATH_SEPERATOR = "/";
		} ?>
        <tr>
			<th scope="row"><label for="fantasy_web_root">Fantasy Web URL</label></th>
			<?php $basepath = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$basepath = str_replace("install/install.php","",$basepath);
			?>
            <td><input name="fantasy_web_root" type="text" id="fantasy_web_root" size="25" value="<?php echo($basepath); ?>" /></td>
            <td>The web url for the fantasy league<br />
            <input type="hidden" name="site_url" value="<?php echo($basepath); ?>" />
            <input type="hidden" name="site_directory" value="<?php echo(str_replace("install/install.php","",$_SERVER['REQUEST_URI'])); ?>" /></td>
		</tr>
        
        <tr>
			<th scope="row"><label for="ootp_html_report_path">OOTP HTML Report URL</label></th>
			<td><input name="ootp_html_report_path" type="text" id="ootp_html_report_path" size="25" value="<?php echo("http://".$_SERVER['HTTP_HOST']."/");?>" /></td>
            <td>The web url for OOTP HTML report files<br /></td>
		</tr>
        
        <tr>
			<th scope="row"><label for="sql_file_path">Fantasy Site Root</label></th>
			<?php $filepath = pathinfo(__FILE__, PATHINFO_BASENAME);
			$filepath = str_replace($filepath, '', __FILE__);
			$filepath = str_replace("install".$PATH_SEPERATOR,"",$filepath);
			?>
            <td><input name="html_root" type="text" id="html_root" size="25" value="<?php echo($filepath); ?>" /></td>
            <td>Complete server path to the fantasy web site directory (INCLUDING a trailing slash)<br /></td>
		</tr>
        <tr>
			<th scope="row"><label for="sql_file_path">SQL File Path</label></th>
			<td><input name="sql_file_path" type="text" id="sql_file_path" size="25" value="" /></td>
            <td>Complete server path to the OOTP MySQL export files directory (with NO trailing slash)<br /></td>
		</tr>
        <tr>
			<th scope="row"><label for="ootp_html_report_root">HTML Report Path</label></th>
			<td><input name="ootp_html_report_root" type="text" id="ootp_html_report_root" size="25" value="" /></td>
            <td>Complete server path to the OOTP Html reports directory (with NO trailing slash)</td>
		</tr>
        <tr>
			<td colspan="3"><h2>Administrator Details</h2></td>
		</tr>
         <tr>
			<th scope="row"><label for="admin_password">Email Address</label></th>
			<td><input name="admin_email" type="text" id="text" size="25" value="" /></td>
            <td></td>
		</tr>
        <tr>
			<th scope="row"><label for="admin_password">Username</label></th>
			<td><input name="admin_username" type="text" id="text" size="25" value="" /></td>
            <td></td>
		</tr>
       <tr>
			<th scope="row"><label for="admin_password">Password</label></th>
			<td><input name="admin_password" type="password" id="password" size="25" value="" /></td>
            <td>OPTIONAL.<br />Leave blank to have one created for you.</td>
		</tr>
        
	</table>
	<p class="step"><div id="waitDiv" style="display:none;"><img src="../images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...</div>
    <div id="buttonDiv"><input type="button" name="btnFinished" id="btnFinished" value="Complete Installtion" class="button" /></div></p>
</form>
<script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
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
} // END function

// CHECK IF CONFIG.php has been run

$f = file_get_contents("../application/config/database.php");
if (strpos($f, "[DB_USER]")) {
	header('Location: congif.php');
	exit();
} // END if
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="../images/favicon.png" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OOTP Fantasy League Setup Utility Step 2</title>
<link rel="stylesheet" href="install.css" type="text/css" />

</head>
<body>
<h1><img src="../images/ootp_fantasy_150.png" width="110" height="110" align="absmiddle" />
OOTP Fantasy League Setup Utility</h1>
<?php

$step = (isset($_GET['step'])) ? $_GET['step'] : 1;

switch ($step) {
	case 0:
	case 1:
	?>    
<p>Welcome to the OOTP Fantasy League installation process! Fill in the information below and you&#8217;ll be ready to create your own OOTP Fantasy Leagues based on your OOTP game!</p>
<h2 class="step">Information needed</h2>
<p>
Please provide the following information.  Don&#8217;t worry, you can always change these settings later. All fields are required unless otherwise noted.</p>

<?php
	display_setup_form();
	break;
	case 2:
	
		//include_once('../application/helpers/config_helper.php');
		
		$site_name = isset($_POST['site_name']) ? stripslashes($_POST['site_name']) : '';
		$ootp_league_name = isset($_POST['ootp_league_name']) ? stripslashes($_POST['ootp_league_name']) : '';
		$ootp_league_abbr = isset($_POST['ootp_league_abbr']) ? stripslashes($_POST['ootp_league_abbr']) : '';
		$ootp_league_id = isset($_POST['ootp_league_id']) ? $_POST['ootp_league_id'] : '';
		
		$ootp_html_report_path = isset($_POST['ootp_html_report_path']) ? stripslashes($_POST['ootp_html_report_path']) : '';
		$html_root = isset($_POST['html_root']) ? $_POST['html_root'] : '';
		$sql_file_path = isset($_POST['sql_file_path']) ? $_POST['sql_file_path'] : '';
		$fantasy_web_root = isset($_POST['fantasy_web_root']) ? stripslashes($_POST['fantasy_web_root']) : '';
		$ootp_html_report_root = isset($_POST['ootp_html_report_root']) ? $_POST['ootp_html_report_root'] : '';
		
		$admin_username = isset($_POST['admin_username']) ? $_POST['admin_username'] : '';
		$admin_email = isset($_POST['admin_email']) ? $_POST['admin_email'] : '';
		$admin_password = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
		
		
		$site_url = isset($_POST['site_url']) ? $_POST['site_url'] : '';
		$site_directory = isset($_POST['site_directory']) ? str_replace("/","",$_POST['site_directory']) : '';
		
		$errors = "";
		$error = false;
		
		if (empty($site_name)) {
			$errors .= '<li>you must provide a name for this web site.</li>';
			$error = true;
		} // END if
		
		if (empty($ootp_league_name)) {
			$errors .= '<li>you must provide the name of your OOTP league.</li>';
			$error = true;
		} // END if
		
		if (empty($ootp_league_id)) {
			$errors .= '<li>you must provide the ID number of your league, though it is usually 100.</li>';
			$error = true;
		} // END if
		
		if (empty($ootp_html_report_path)) {
			$errors .= '<li>you must provide a file path for the ootp html reports.</li>';
			$error = true;
		} // END if
		
		if (empty($html_root)) {
			$errors .= '<li>you must provide a server file path to the fantasy league files.</li>';
			$error = true;
		} // END if
		if (empty($sql_file_path)) {
			$errors .= '<li>you must provide a server file path to the sql data files.</li>';
			$error = true;
		} else {
			if (strpos($sql_file_path."//")) {
				$sql_file_path = stripslashes($sql_file_path);
			} // END if
		}
		if (empty($fantasy_web_root)) {
			$errors .= '<li>you must provide a web url for the fanbtasy site.</li>';
			$error = true;
		} // END if
		
		if (empty($ootp_html_report_root)) {
			$errors .= '<li>you must provide a server file path to the OOTP HTML Reports.</li>';
			$error = true;
		} // END if
		
		if (empty($admin_username)) {
			$errors .= '<li>you must provide a username for administrative access.</li>';
			$error = true;
		} else if (!empty($admin_username) && strtolower($admin_username) == "admin") {
			$errors .= '<li>we strongly recommend against using the username <b>admin</b> for security purposes.</li>';
			$error = true;
		} // END if
		
		if (empty($admin_email)) {
			$errors .= '<li>you must provide an email address for administrative access.</li>';
			$error = true;
		} else if (!empty($admin_email)) {
			if (strtolower($admin_email) == "admin") {
				$errors .= '<li>we strongly recommend against using the username <b>admin</b> for security purposes.</li>';
				$error = true;
			}
			include_once('../lib/codeignighter/1_7_3/helpers/email_helper.php');
			if (function_exists('valid_email')) {
				if (!valid_email($admin_email)) {
					$errors .= '<li>the e-mail address <b>'.$admin_email.'</b> is not valid. please check you entries and try again.</li>';
					$error = true;
				}
			}
		} // END if
		
		if (!$error) {
			// ------------------------------------
			// UPDATE - 1.0.3
			// TEST WRITE STATUS OF FILES BEFORE LOADING UP SQL AND WRITING CONFIGS
			// -------------------------------------
			$perm_head = "<h2>Error</h2><h3>Required Files could not be written</h3>";
			$perm_message = " Assure that all directories required to be writable to the installer have their permissions set correctly and then try submitting the form again.";
			$fh = fopen('../application/config/constants.php',"w") or install_die($perm_head."Could not open <code>application/config/constants.php</code> for writing.".$perm_message);
			fwrite($fh, " ") or install_die($perm_head."Could not write to <code>application/config/constants.php</code>.".$perm_message);
			unset($fh);
			$fh = fopen('../.htaccess',"w") or install_die("$perm_head.Could not open <code>/[root]/.htaccess</code> for writing.".$perm_message);
			fwrite($fh, " ") or install_die($perm_head."Could not write to htaccess file.".$perm_message);
			unset($fh);
			if (defined('DB_CONNECTION_FILE') && file_exists($sql_file_path)) {
				$fh = fopen($sql_file_path.'/'.DB_CONNECTION_FILE,"w") or install_die($perm_head."Could not open database permissions file in the <code>".$sql_file_path."</code> for writing.".$perm_message);
				fwrite($fh, " ") or install_die($perm_head."Could not write to database permissions file in the <code>".stripslashes($sql_file_path)."</code> directory.".$perm_message);
				unset($fh);
			} else {
				install_die($perm_head."Could not locate the specified sql upload directory <code>".$sql_file_path."</code> directory.".$perm_message);
			}
			// -------------------------------
			//	CONTINUE INSTALL PROCEDURE BY LOADING THE DATABASE
			// -------------------------------
			if (defined('DB_CONNECTION_FILE') && file_exists('./'.DB_CONNECTION_FILE)) {
				include_once('./'.DB_CONNECTION_FILE);
			} // END if
			$fr = fopen('./db_install.sql',"r");
			$errCnt=0;
			$db_errors = '';
			$queries = '';
			$prevQuery = '';
			while (!feof($fr)) {
				$query=fgets($fr);
				if ($query=="") {continue;}
				$query=str_replace(", , );",",1,1);",$query);
				//$query=preg_replace("/([\xC2\xC3])([\x80-\xBF])/e","chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)",$query);
				$query=str_replace(", ,",",'',",$query);
				$query=str_replace("#IND",0,$query);
				$result=mysql_query($query,$db);
				$err=mysql_error($db);
				if (($err!="") && ($query!="")) {
					$db_errors .= $err.",query=".$query.",prevQuery=".$prevQuery."<br />";
					$errCnt++;
				}
				$queries .= $query;
				$prevQuery = $query;
			}
			fclose($fr);
			if ($errCnt>0) $errors .= "<strong>Database errors occured</strong>.<br />".$db_errors;
			
			//echo("errCnt = ".$errCnt."<br />");
			if ($errCnt == 0) {
				//$options = get_config_defaults();
				$options = array('site_name'=>$site_name,
											'ootp_league_name'=>$ootp_league_name,
											'ootp_league_id'=>$ootp_league_id,
											'ootp_league_abbr'=>$ootp_league_abbr,
											'ootp_html_report_path'=>$ootp_html_report_path,
											'sql_file_path'=>$sql_file_path,
											'fantasy_web_root'=>$fantasy_web_root,
											'ootp_html_report_root'=>$ootp_html_report_root);
											
				$insert = "";					
				foreach ( $options as $option => $value ) {
					//echo($option." = ".$value."<br />");
					$option = $option;
					if ( is_array($value) )
						$value = serialize($value); // END if
					$value = mysql_real_escape_string($value);
					if (strpos($value,"\\\\")) {
						$value = stripslashes($value); // END if
					} // END if
					if ( !empty($insert) )
						$insert .= ', ';
					$insert .= "('$option', '$value')";
				} // END if
				
				//echo("<br /><strong>Config insert sql</strong> = ".$insert."<br />");
			
				if ( !empty($insert) ) {
					$sql = "INSERT INTO fantasy_config (cfg_key, cfg_value) VALUES " . $insert;
					mysql_query($sql);
					$err=mysql_error($db);
					if ($err) {
						$error = true;
						$errors .= "Error updating config table. Error: ".$err."<br />SQL = ".$sql."<br />";
					} // END if
				} // END if
				
				// ADD ADMIN ACCOUNT
				include_once("../application/config/auth.php");
				$pw = !empty($admin_password) ? $admin_password : substr(md5('admin'.time()),0,10);
				$sql = "INSERT INTO `users_core` VALUES(1, '".$admin_username."', '".__hash($pw,$config['password_crypt'])."', '".$admin_email."', 4, 5, 6, '', '0', 0, 0, '".date('Y-m-d h:m:s')."', '".date('Y-m-d h:m:s')."', 0, 1, 0, 0)";
				mysql_query($sql);
				$err=mysql_error($db);
				if ($err) {
					$error = true;
					$errors .= "Error adding admin user. Error: ".$err."<br />SQL = ".$sql."<br />";
				} // END if
				$sql = "INSERT INTO `users_meta` VALUES(1, 1, 'Site', 'Admin', '', '', '', '', '', 'Site Administrator', '', '0000-00-00', '', '', '')";
				mysql_query($sql);
				$err=mysql_error($db);
				if ($err) {
					$error = true;
					$errors .= "Error adding admin meta data. Error: ".$err."<br />SQL = ".$sql."<br />";
				} // END if
				if (!$error) {
						
				
					$folder = str_replace("install/install.php","",$_SERVER['REQUEST_URI']);
					$basepath = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					$url = str_replace("install/install.php","",$basepath);
					
					if (is_writable('../')) {
						$fht = file_get_contents("./htaccess_install.txt");
						$fht = str_replace("[SITE_FOLDER]",$site_directory,$fht);
						$fh = fopen('../.htaccess',"w");
						fwrite($fh, $fht);
						fclose($fh);
						unset($fht);
						chmod('../.htaccess', 0666);
						unset($fh);
						$htaccess_write = true;
					} else {
						$htaccess_write = false;
					}
					if (is_writable('../application/config/')) {
						$fcs = file_get_contents("./constants_install.php");
						$fcs = str_replace("[WEB_SITE_URL]",$site_url,$fcs);
						$fcs = str_replace("[SITE_DIRECTORY]","/".$site_directory."/",$fcs);
						$fcs = str_replace("[HTML_ROOT]",$html_root,$fcs);
						$fh = fopen('../application/config/constants.php',"w");
						fwrite($fh, $fcs);
						fclose($fh);
						unset($fcs);
						chmod('../application/config/constants.php', 0666);
						unset($fh);
						
						$fcf = file_get_contents("./config_install.php");
						$fcf = str_replace("[SITE_PATH]",$site_url,$fcf);
						$fh = fopen('../application/config/config.php',"w");
						fwrite($fh, $fcf);
						fclose($fh);
						unset($fcf);
						chmod('../application/config/config.php', 0666);
						unset($fh);
						
						$config_write = true;
					} else {
						$config_write = false;
					}
					if (is_writable('../js/')) {
						$fcs = file_get_contents("./nicEdit_install.js");
						$fcf = str_replace("[SITE_PATH]",$site_url,$fcs);
						$fh = fopen('../js/nicEdit.js',"w");
						fwrite($fh, $fcf);
						fclose($fh);
						unset($fcf);
						chmod('../application/config/config.php', 0666);
						unset($fh);
						
						$js_write = true;
					} else {
						$js_write = false;
					}
					
					$sql_write = false;
					if (file_exists($sql_file_path) && is_writable($sql_file_path)) {
						copy('./'.DB_CONNECTION_FILE, $sql_file_path.'/'.DB_CONNECTION_FILE) or ($sql_write = false);
						$sql_write = true;
					} // END if

					// SET UPLOAD FOLDERS UP FOR WRITING
					if (file_exists($html_root."images/avatars/users")) {
						chmod($html_root."images/avatars/users", 0775);
					} // END if
					if (file_exists($html_root."images/avatars/leagues")) {
						chmod($html_root."images/avatars/leagues", 0775);
					} // END if
					if (file_exists($html_root."images/avatars/teams")) {
						chmod($html_root."images/avatars/teams", 0775);
					} // END if
					if (file_exists($html_root."images/news")) {
						chmod($html_root."images/news", 0775);
					} // END if
					if (file_exists($html_root."media/uploads")) {
						chmod($html_root."media/uploads", 0775);
					} // END if
					
					unlink('../index.html');
					if (!$error) {
					?>
                        <h1>Installation Complete!</h1>
                        <p />
                        <?php if (!$htaccess_write || !$config_write  || !$sql_write) { ?>
                        <link href="shighlight/shCore.css" rel="stylesheet" type="text/css" />
                        <link href="shighlight/shThemeDefault.css" rel="stylesheet" type="text/css" />
                        <h2>Manually Completion Required</h2>
                        <p />
                        Some files could not be written to during the installation. You must manually update the following files to complete your installation:
                        <p />
                        <ul>
                        <?php
						if (!$htaccess_write) { ?>
                        	<li><code><?php echo($html_root);?>.htaccess</code> - This file is critical for the site to 
                            work. Copy and past the following onto line three of <code>install/htaccess_install.txt</code> and save it as <code>.htaccess</code> in your fantasy leagues root folder.
                            <p />
                            <code>RewriteRule ^(.*)$ /<?php echo($site_directory); ?>/index.php/$1 [L]</code>
                            </li>
                        <?php } // END if
						if (!$config_write) { ?>
                        	<li><code><?php echo($html_root);?>application/config/constants.php</code> - Copy and 
                            paste the following into line three of <code>/install/constants_install.php</code> and save 
                            it to <code>/application/config/constants.php</code> from your fantasy leagues root folder.
                            <p />
                            <pre class="brush: php">define("SITE_URL",",<?php echo($site_url); ?>");
define("DIR_APP_ROOT","/<?php echo($site_directory); ?>/");
define("DIR_APP_WRITE_ROOT","<?php echo($site_directory); ?>");
define("DIR_WRITE_PATH","<?php echo($html_root); ?>");</pre>
                            </li>
                            <li><code><?php echo($html_root);?>application/config/config.php</code> - Copy and 
                            paste the following into line three of <code>install/config_install.php</code> and save 
                            it to <code>application/config/config.php</code> from your fantasy leagues root folder.
                            <p />
                            <pre class="brush: php">$config['base_url']	= "<?php echo($site_url); ?>";</pre>
                            </li>
                        <?php 
						} // END if
						if (!$js_write) { ?>
                        	<li><code><?php echo($html_root);?>/js/nicEdit.js</code> - Copy and 
                            paste the following into line 29 of <code>/js/nicEdit.js</code> from your fantasy leagues root folder.
                            <p />
                            <pre class="brush: JScript">iconsPath : '<?php echo($site_url); ?>/images/nicEditorIcons.gif',</pre>
                            </li>
                        <?php } // END if
						
						if (!$sql_write) { ?>
                        	<li><code><?php echo($sql_file_path.DB_CONNECTION_FILE);?></code> - Copy and 
                            paste the following into <code><?php echo($sql_file_path."/".DB_CONNECTION_FILE);?></code> from your fantasy leagues root folder.
                            <p />
                            <pre class="brush: php"><?php echo(file_get_contents("./".DB_CONNECTION_FILE)); ?></pre>
                            </li>
                        <?php } // END if
						?>
                        <script type="text/javascript" src="shighlight/shCore.js"></script>
						<script type="text/javascript" src="shighlight/shBrushPhp.js"></script>
                        <script type="text/javascript" src="shighlight/shBrushJScript.js.js"></script>
                        <?php if (!$sql_write || !$js_write ||!$config_write || !$htaccess_write) { ?>
						<script type="text/javascript">SyntaxHighlighter.all();</script>
                        <?php } ?>
                        </ul>
                        <?php } // END if
						?>
                        Your administrator password is <strong><?php echo($pw);?></strong>. Write this password down as you will not be able to administrate the site without it.
                        <p />
                        It is strongly recommended that you delete all files in the <code>install</code> directory 
                        to avoid issues with your sites setup and configuration,
                        <p />
                        Head to the league <a href="../index.php">homepage now</a>.
                	<?php
					} // END if
				} // END if
			} else {
				$error = true;
			} // END if
		} // END if
		if ($error) { ?>
        	<h1>An error occured.</h1>
            <p />
            The following errors were encountered: 
            <ul>
            <?php echo($errors); ?>
            </ul>
        <?php 
			exit();
		} // END if
		
	break;
} // END switch
?>
</body>
</html>