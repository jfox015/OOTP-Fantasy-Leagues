<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo($siteName); ?> User Activation Code</title>
	</head>
	<body>
		<h1><?php print($siteName); ?> User Activation Code</h1>
		<p>Your activation code for <?php print($username); ?> is : <b><?php print($activation); ?></b></p>
         <p>Activate your membership on the site using the <?php print(anchor('user/activate','email activiation form')); ?>.</p>
	</body>
</html>