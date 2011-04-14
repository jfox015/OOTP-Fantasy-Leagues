<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo($siteName); ?>: <?php print($leagueName); ?> Team Request</title>
	</head>
	<body>
		<h1><?php print($leagueName); ?> Team Request</h1>
		<p>A request has been submitted by <?php print($username); ?> for the <?php ($teamName); ?></p>
        <p>You can accept or reject this request on the League <?php echo anchor('/league/requestAdmin/','Request Admin page'); ?>.</p>
	</body>
</html>