<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo($siteName); ?> User Activation Required</title>
	</head>
	<body>
		<h1><?php print($siteName); ?> -  User Activation Required</h1>
		<p>A new user, <b><?php print($username); ?></b>, has signed up on the site and requires activation.</p>
        <p>View the list of pending <?php print(anchor('admin/userActivations','user activations')); ?> in the admin dashboard.</p>
	</body>
</html>