<?php 
$f = ' [DB_USER]';
if (file_exists("../application/config/database.php")) {
	$f = file_get_contents("../application/config/database.php") or die ("Could not open datbase file");
}
if (strpos($f, "[DB_USER]")) {
	header('Location: config.php');
	exit();
} else {
	header('Location: install.php');
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="3; url=install.php" />
<title>OOTP Fantasy League Setup Utility </title>
</head>

<body>


</body>
</html>