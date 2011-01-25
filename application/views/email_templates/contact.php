<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo($siteName); ?> Contact Request</title>
</head>
<body>
    <h3><?php echo($siteName); ?> Contact Request</h3>
    <p>
    The following contact request was sent by <?php echo($name); ?> from the <?php echo($siteName); ?> contact form 
    today at <?php echo (date('m/d/Y h:m:s A',time())); ?>.
    </p>
    <p><?php echo($details); ?></p>
    <p>You can respond to this message at <a href="mailto:<?php echo($email); ?>"><?php echo($email); ?></a></p>
    <p>
    NOTE: This is an auto generated message. DO NOT REPLY TO THIS MESSAGE.
    </p>
</body>
</html>