<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo($siteName); ?> Forgotten Password Request</title>
	</head>
	<body>
		<h1><?php echo($siteName); ?> Forgotten Password Request</h1>
		<p>A requeste for a new password has been created on the <?php echo($siteName); ?>  site for this account.</p>
		<p>Please click the following link and enter the verification code below to create your new password:</p>
		<p>Verification Code: <b><?php echo $forgotten_password_code; ?></b></p>
		<p><?php print($verify_url); ?></p>
	</body>
</html>