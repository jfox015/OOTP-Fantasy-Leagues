    <div id="left-column">
   	<?php include_once('nav_divisions.php'); ?>
    </div>
    <div id="center-column">
   	<div id="subPage">
       	<?php include_once('admin_breadcrumb.php'); ?>
    	<div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
       	<div id="content">
            <b>League:</b> <?php echo(anchor('/league/info/'.$thisItem['league_id'],$thisItem['league_name'])); ?><br />
            </div>
        </div>
	</div>
