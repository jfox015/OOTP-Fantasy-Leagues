<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo($siteName); ?> Registration Confirmation</title>
	</head>
	<body>
		<h1><?php print($siteName); ?> Registration Confirmation</h1>
		<p>Congratulations <?php print($username); ?>! Your membership to the <?php print($siteName); ?> Fantasy league web site is confirmed.</p>
        <p>Login using your password on the <?php echo anchor('/usder/login','Login page'); ?> to begin using the site.</p>
	</body>
</html>