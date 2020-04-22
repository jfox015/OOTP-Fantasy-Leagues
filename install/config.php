<?php
/**
 * 	OOTP Fantasy League Install Script
 *	This script handles verifying the minimum server requirements are met, capturing 
 *	database information and creating the database connection files.
 *	
 */
define('OFL_INSTALLING', true);
include_once('./common.php');
include_once('./constants_install.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="../images/favicon.png" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OOTP Fantasy League Setup Utility Step 1</title>
<link rel="stylesheet" href="install.css" type="text/css" />

</head>
<body>
<h1><img src="../images/ootp_fantasy_150.png" width="110" height="110" align="absmiddle" />
OOTP Fantasy League Setup Utility</h1>
<?php 
$step = (isset($_GET['step'])) ? $_GET['step'] : 0;

switch ($step) {
	case 0: ?>

<p>Welcome to OOTP Fantasy Leagues setup utility. Before starting your league, some information is needed regarding your database. You will need to know the following items before proceeding.</p>
<ol>
	<li>Database name</li>
	<li>Database username</li>
	<li>Database password</li>
	<li>Database host</li>
</ol>

<p>This information is supplied to you by your Web Host. If you do not have this 
information, then you will need to contact them before you can continue. If 
you&#8217;re all ready, press <strong>Let's Go</strong> below.</p>

<p>Now let's check your system compatibilty with this mod:</p>

<ul>
	<li>Root Directory Writable: <?php echo(($root_writable) ? '<span style="color:#060;">Writable!</span>' : '<span style="color:#c00;">Not Writable</span><br />This means that you will need to either make the root directory writable via <code>chmod</code> or manually edit the the files that must go there once the setup operations have been completed.'); ?>
	<li>Install Directory Writable: <?php echo(($install_writable) ? '<span style="color:#060;">Writable!</span>' : '<span style="color:#c00;">Not Writable</span><br />This means that you will need to either make the install directory writable via <code>chmod</code> or manually edit the the files that must go there during the setup process.'); ?>
	<li>Config Directory Writable: <?php echo(($config_writable) ? '<span style="color:#060;">Writable!</span>' : '<span style="color:#c00;">Not Writable</span><br />This means that you will need to either make the "application/config" directory writable via <code>chmod</code> or manually edit the information in these files once the setup operations have been completed.'); ?>
	<li>PHP Version is compatible: <?php echo(($php_compatible) ? '<span style="color:#060;">Yes!</span>, Version '.phpversion().'' : '<span style="color:#c00;">Not Compatible</span><br />Your server is running PHP version '.phpversion().' but OOTP Fantasy Leagues requires at least '.$phpMinRequired.'.'); ?>
	<li>PHP Curl Extention is available: <?php echo(($has_curl) ? '<span style="color:#060;">Yes!</span>' : '<span style="color:#c00;">Curl Not Found</span><br />The PHP <strong>Curl</strong> extension is required for numerous administrative purposes. Please check with your web host if Curl can be activated on your hosting environment.'); ?>
	<li>MySQL is loaded: <?php echo(($mysql_available) ? '<span style="color:#060;">Yes!</span>' : '<span style="color:#c00;">Not Loaded</span><br />Your PHP installation appears to be missing the MySQL extension which is required by the OOTP Fantasy Leagues.'); ?>
	<li>MySQL Version is compatible: <?php echo(($mysql_version) ? '<span style="color:#060;">Yes!</span>, Version '.$mysql_version.'' : '<span style="color:#c00;">Not Compatible</span><br />Your server is running MySQL version '.$mysql_version.' but at least '.$mySqlMinRequired.' is required.'); ?>
</ul>

<?php if (!$config_writable || !$root_writable || !$install_writable) { ?>
<p><strong>If for any reason this automatic file creation doesn't work, don't worry. All this does is fill in the database information to a configuration file. You can open the files in a text editor, fill in the required information provided in the next step, and save them manually.</strong></p>
<?php } 
if ($php_compatible && $mysql_compat && $mysql_available) { ?>
<p>All looks good let's get going!</p>
<p class="step"><a href="config.php?step=1" class="button">Let&#8217;s go!</a></p>

<?php
}
	break;
	case 1: ?>
	<form method="post" action="config.php?step=2">
	<p>Enter your database details below. If you're not sure about these, 
    contact your web hosting provider.</p>
	<table class="form-table">

		<tr>
			<th scope="row"><label for="dbname">Database Name</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="ootpdb" /></td>
			<td>The name of the database you want to run your leagues on. </td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">User Name</label></th>

			<td><input name="uname" id="uname" type="text" size="25" value="db username" /></td>
			<td>Your MySQL username</td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">Password</label></th>
			<td><input name="pwd" id="pwd" type="password" size="25" value="db password" /></td>
			<td>...and MySQL password.</td>

		</tr>
		<tr>
			<th scope="row"><label for="dbhost">Database Host</label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td>99% chance you won't need to change this value.</td>
		</tr>
	</table>
	<p class="step"><input name="submit" type="submit" value="Submit" class="button" /></p>
</form>
<?php
	break;
	case 2:
		$dbname  = trim($_POST['dbname']);
		$uname   = trim($_POST['uname']);
		$passwrd = trim($_POST['pwd']);
		$dbhost  = trim($_POST['dbhost']);
	
		// Test the db connection.
		/**#@+
		 * @ignore
		 */
		define('DB_NAME', $dbname);
		define('DB_USER', $uname);
		define('DB_PASSWORD', $passwrd);
		define('DB_HOST', $dbhost);
		
		$f = file_get_contents("./database_install.php");
		$f = str_replace("[DB_NAME]",DB_NAME,$f);
		$f = str_replace("[DB_USER]",DB_USER,$f);
		$f = str_replace("[DB_PASSWORD]",DB_PASSWORD,$f);
		$f = str_replace("[DB_HOST]",DB_HOST,$f);
		
		// UPDATE PROD 1.0.3
		// DB connection updated to MYSQLi
		$conn = new mysqli($dbhost, $uname, $passwrd, $dbname);
		if ($conn->connect_error) {
			$html = "<h1>Error establishing a database connection</h1>
			<p>Error Message: ".$conn->connect_error."</p>			
			<p>This either means that the username and password information is incorrect or we can't 
			contact the database server at <code>".$dbhost."</code>. This could mean your host's 
			database server is down.</p>
			<ul>
				<li>Are you sure you have the correct username and password? You entered <code>".$uname."</code> and <code>".$passwrd."</code></li>
				<li>Are you sure that you have typed the correct hostname?</li>
				<li>Are you sure that the database server is running?</li>
			
			</ul>
			<p>If you're unsure what these terms mean you should probably contact your host. If you still need help you can always visit the <a href='http://www.ootpdevelopments.com/board/ootp-mods/198634-out-park-baseball-online-fantasy-leagues.html'>OOTP Fantasy League Forum Page</a>.</p>
			</p>";
			install_die ($html);		
		} else {
			// UPDATE - VERSION 1.0.3
			// RUN DB TEST SCRIPT
			//mysql_select_db(DB_NAME) or install_die("Could not connect to datababase named: ".DB_NAME.". Check the database name and try again.");
			$fr = fopen('./db_test.sql',"r");
			$db = '';
			$errCnt=0;
			$db_errors = '';
			$queries = '';
			$prevQuery = '';
			while (!feof($fr)) {
				$err = "";
				$query=fgets($fr);
				if ($query=="") {continue;}
				$query=str_replace(", , );",",1,1);",$query);
				//$query=preg_replace("/([\xC2\xC3])([\x80-\xBF])/e","chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)",$query);
				$query=str_replace(", ,",",'',",$query);
				$query=str_replace("#IND",0,$query);
				//echo("SQL Test query = ".$query."<br/>");
				if ($conn->query($query) !== TRUE) {
					$err=$conn->error;
				}
				if (($err!="") && ($query!="")) {
					$db_errors .= $err.",query=".$query.",prevQuery=".$prevQuery."<br />";
					$errCnt++;
				}
				$queries .= $query;
				$prevQuery = $query;
			} // END while
			fclose($fr);
			$conn->close();
			if ($errCnt>0) {
	        	$html = "<h3>The following errors were encountered:</h3> 
	            <ul>
	            <strong>Database errors occured</strong>.<br />".$db_errors."
	            </ul>";
				install_die ($html);
			} // END if
		} // END if
		
		if ( !$config_writable || !$install_writable) :
		?>
        <h2>Manual Update Required</h2>
		<link href="shighlight/shCore.css" rel="stylesheet" type="text/css" />
        <link href="shighlight/shThemeDefault.css" rel="stylesheet" type="text/css" />
 		<?php
		if ( !$config_writable) : ?>
    <p>
    Sorry, but I can't write to the <code>/application/config/database.php</code> file.</p>
    <p>Please open the file <code>/install/database_install.php</code>, replace the default fields in the file with the information below, 
    save it as <code>database.php</code> and upload it to <code>/application/config/database.php</code>.</p>
    <pre class="brush: php"><?php
		echo("\$db['default']['hostname'] = \"".DB_HOST."\";\n");
		echo("\$db['default']['username'] = \"".DB_USER."\";\n");
		echo("\$db['default']['password'] = \"".DB_PASSWORD."\";\n");
		echo("\$db['default']['database'] = \"".DB_NAME."\";\n");
?></pre>
	<?php endif;
	if ( !$install_writable) : ?>
    <p>
    Sorry, but I can't write to the <code>/install/DB_CONNECTION_FILE</code> file.</p>
    <p>Please create a new file, copy and paste the information below into it, 
    save it as <code>DB_CONNECTION_FILE</code> and upload it to the <code>/install/</code> directory. 
    This file also needs to be copied to the MySQL upload directory as well, which is defined in 
    the next step.</p>
    <pre class="brush: php"><?php
		echo("&lt;?php\n");
		echo("\$conn = new mysqli('".DB_HOST."','".DB_USER."','".DB_PASSWORD."','".DB_NAME."');\n");
		echo("if (\$conn->connect_error) { echo 'Failed to connect to MySQL: (' . \$conn->connect_errno . ') ' . \$conn->connect_error; }\n");
		echo("?&gt;");
		?>
		</pre>
        <?php
		endif;
		?>
        <script type="text/javascript" src="shighlight/shCore.js"></script>
		<script type="text/javascript" src="shighlight/shBrushPhp.js"></script>
        <script type="text/javascript">SyntaxHighlighter.all();</script>
<p>After you've done that, click "Run the install."</p>
<p class="step"><a href="install.php" class="button">Run the install</a></p>
<?php
	else :
		$fh = fopen('../application/config/database.php',"w") or install_die("Could not open datbase config file for writing.");
		fwrite($fh, $f);
		fclose($fh);
		chmod('../application/config/database.php', 0666);
		
		$fdb = "<?php
		\$conn = new mysqli('".DB_HOST."','".DB_USER."','".DB_PASSWORD."','".DB_NAME."');\n
		if (\$conn->connect_error) { echo 'Failed to connect to MySQL: (' . \$conn->connect_errno . ') ' . \$conn->connect_error; }\n
		?>";
		
		$fh = fopen('./'.DB_CONNECTION_FILE,"w") or install_die("Could not open database config file for writing.");
		fwrite($fh, $fdb);
		fclose($fh);
		chmod('./'.DB_CONNECTION_FILE, 0666);
?>
<p>You've made it through this part of the installation. We can now communicate with your database. If you are ready, time now to&hellip;</p>

<p class="step"><a href="install.php" class="button">Run the install</a></p>
<?php
	endif;
	break;
}
?>

</body>
</html>
