<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Forgotten Password Request</title>
	</head>
	<body>
		<h1>Forgotten Password Request</h1>
		<p>You have requested a new password.</p>
		<p>Please click the following link and enter this code</p>
		<p><b><?php echo $forgotten_password_code; ?></b></p>
		<p><?php echo anchor('welcome/forgotten_password/', 'New Password'); ?></p>
	</body>
</html>