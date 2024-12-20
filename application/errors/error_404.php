<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" href="images/favicon.ico" />
        <title>404 Page Not Found Error</title>
        <link rel="stylesheet" type="text/css" href="css/all.css" />
    </head>
    <body class="body_en">
	<!-- begin wrap -->
		<div id="main">
            <div id="header">
                <div class="logo"></div>
                <div class="site_name"><?php echo(SITE_NAME); ?></div>

                <ul id="top-navigation" class="clearfix">
                    <li><span><span><a href="/admin/home/">Home</a></span></span></li>
                </ul> 
			</div>
                <!-- begin content_wrap -->
   		<div id="middle">
           	<div id="one-column">
					<h1><?php echo $heading; ?></h1>
					<?php echo $message; ?>
                    <p>&nbsp;<br />
                    <div class="details"><?php echo(SITE_NAME); ?> v.<?php echo(SITE_VERSION); ?> | <a href='/admin/login/' style="color:#fff;">Admin Access</a><br />
  					<?php echo(date('l, F jS, Y')); ?>
                    </p></div>
                </div>
            </div>
           <div id="footer"></div>
				<!-- end footer -->
        </div>
    </body>
</html>
