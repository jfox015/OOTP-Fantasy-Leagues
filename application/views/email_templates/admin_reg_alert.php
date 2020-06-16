<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo($siteName); ?> Registration Alert</title>
	</head>
	<body>
		<h1><?php print($siteName); ?> Registration Notice</h1>
        <p>To <?php print($siteName); ?> Site Admin,</p>
		<p>The user <?php echo($username." &lt;".$email."^gt;") ?> has completed their registration on the Fantasy league web site today at <?php date("F j, Y, g:i a", time()); ?>.</p>
	</body>
</html>