<?php if (isset($league_info)) {
   $logo = $config['ootp_html_report_path']."images/league_logos/".$league_info->logo_file_name;
   $theDate = date('m/d/Y',strtotime($league_info->current_date));
   $league_abbr = strtoupper($league_info->abbr);
   $header_bg = $league_info->background_color_id;
   $header_txt = $league_info->text_color_id;
   $favicon = $logo;
} else {
   $logo = $config['fantasy_web_root']."images/ootp_fantasy_150.png";
   $theDate = date('m/d/Y');
   $league_abbr = strtoupper('ootp');
   $header_bg = "#000000";
   $header_txt = "#FFFFFF";
   $favicon = $config['fantasy_web_root']."images/favicon.png";
}
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html>
<head>
    <?php $uri = $this->uri->uri_string(); ?>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="<?php echo($favicon); ?>" />
    <title><?php echo(!empty($subTitle) ? $subTitle." - ".$league_abbr." Fantasy Leagues" : 'OOTP Fantasy Leagues'); ?></title>
    <?php echo link_tag('css/styles.css'); ?>
	<?php echo link_tag('css/ootpsqlstyles.css'); ?>
    <?php echo link_tag('css/all.css'); ?>
	<?php if (isset($styles) && sizeof($styles)) {
		foreach($styles as $style) {
			echo link_tag('css/'.$style)."\n";
		}
	} ?>
    <?php if ($pageType == PAGE_SEARCH) { echo link_tag('css/search.css')."\n"; } ?>
    <?php if ($pageType == PAGE_FORM) { echo link_tag('css/forms.css')."\n"; } ?>
    <?php if ($pageType == PAGE_FORM) { echo link_tag('css/sortable-tables.min.css')."\n"; } ?>
    <?php if (strstr($uri,'info') || strstr($uri,'profile') ||  strstr($uri,'rules')) { echo link_tag('css/content.css')."\n"; } ?>
    <?php if ($pageType == PAGE_FORM || $pageType == PAGE_SEARCH) { ?>
<!--script type="text/javascript" src='<?php echo($config['fantasy_web_root']); ?>js/sorttable.js'></script-->
<script type="text/javascript" src='<?php echo($config['fantasy_web_root']); ?>js/sortable-tables.min.js'></script>

<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/<?php print(JS_JQUERY); ?>"></script>
<?php  } ?>
<?php if (isset($scripts) && sizeof($scripts)) {
	foreach($scripts as $script) {
		echo('<script type="text/javascript" src="'.$config['fantasy_web_root'].'js/'.$script.'"></script>');
	}
} ?>
</head>
<body class="body_en">
<!-- begin wrap -->
<div id='pagebody'>
    <div id='topbar'>
        <div style='clear:both;float:left;width:959px;padding:0;margin:0;border:0;'>
            <div style='clear:both;float:left;width:110px;height:110px;padding:10px 12px 10px 8px;margin:0;'>

                <a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_home.html'><img
                src='<?php echo($logo); ?>' width="110" height="110" border=0></a></div>
                <div style='clear:right;float:left;padding:0;margin:0;width:829px;height:130px;'>
                <div style='clear:both;float:left;padding:0;margin:0;width:829px;height:36px;'>
                <div style='clear:both;float:left;width:8px;height:36px;'></div>
                <div style='float:left;width:410px;height:36px;padding:8px 0 0 0;'><span
                style='color:#FFFFFF; font-size:14px; font-weight:bold;'><?php echo($theDate); ?></span></div>
                <div style='float:left;width:411px;height:36px;padding:0;margin:0;'></div>
            </div>
            <div id='mainmenu' style='float:left;width:821px;height:21px;margin:0;padding:7px 8px 0 0;'>
                <?php if ($config['ootp_html_report_links'] == 1) { ?>
				<ul style='float:right;'>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>index.html' class='menu first' id='first'>BNN Home</a></li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_home.html' class='menu'><?php echo($config['ootp_league_abbr']); ?></a></li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_scores.html' class='menu'>Scores</a></li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_standings.html' class='menu'>Standings</a></li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_stats.html' class='menu'>Stats</a></li>

                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_teams.html' class='menu'>Teams</a></li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_players.html' class='menu'>Players</a><li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>leagues/league_<?php echo($config['ootp_league_id']); ?>_transactions_0_0.html' class='menu'>Transactions</a></li>
                <li><a href='<?php echo($config['ootp_html_report_path']); ?>history/league_<?php echo($config['ootp_league_id']); ?>_index.html' class='menu'>History</a></li>
				<?php if (isset($config['stats_lab_url']) && !empty($config['stats_lab_url'])) { ?>
				<li><a href='<?php echo($config['stats_lab_url']); ?>' class='menu'>StatsLab</a></li>
				<?php } ?>
                </ul>
				<?php } ?>
            </div>   <!-- END mainmenu DIV -->

            <div style='float:left;width:821px;height:59px;background-color:<?php echo($header_bg); ?>;margin:0;padding:7px 0 0 8px;'>
                <span style='color:<?php echo($header_txt); ?>; font-size:24px; font-weight:bold;'><?php echo($league_abbr); ?> Fantasy Leagues</span><br>
                <span style='color:<?php echo($header_txt); ?>; font-size:18px; font-weight:bold;'><?php echo $subTitle; ?></span>
            </div>
        </div>
    </div>
    <?php
	if (isset($subNavSection) && sizeof($subNavSection) > 0) {
	foreach ($subNavSection as $id => $subNav) {
	?>
    <div id='subnav'>
        <ul>
            <?php
			$itemCount = 0;
			if (isset($subNav) && sizeof($subNav) > 0) {
				foreach($subNav as $menuItem) {
					$xtraClass = '';
					if ($itemCount == 0) {
						$xtraClass = ' first';
					} else if ($itemCount == (sizeof($subNav) - 1)) {
						$xtraClass = ' last';
					} // END if
					$attr = array('class'=>'menu'.$xtraClass);

					if (isset($menuItem['url']) && !empty($menuItem['url'])) {
						if (isset($menuItem['rel'])) {
							$attr = $attr + array('rel'=>$menuItem['rel']);
						}
						if (isset($menuItem['id'])) {
							$attr = $attr + array('id'=>$menuItem['id']);
						}
						if (isset($menuItem['target'])) {
							$attr = $attr + array('target'=>$menuItem['target']);
						}
						echo '<li>'.anchor($menuItem['url'],$menuItem['label'],$attr);
					} else {
						echo('<li class="label'.$xtraClass.'"'.$id.'>'.$menuItem['label']);
					}
					if (isset($menuItem['menu'])) {
						echo($menuItem['menu']);
					}
					echo('</li>');

					$itemCount++;
				} // END foreach
			} // END if
			?>
        </ul>
    </div>   <!-- END subnav DIV -->
    <?php } // END foreach
	} // END if
	// EDIT 1.0.6, IF the site version contains the "beta" tag, show the bug report toolbar
	if (strpos(SITE_VERSION,"Alpha") !== false || strpos(SITE_VERSION,"Beta") !== false) {
	?>
	<div id="bug_bar">
		<ul>
			<li><?php  print(anchor(BUG_URL,'<img src="'.PATH_IMAGES.'/bug.gif" width="32" height="32" alt="Help Squash Me!" title="Help Squash Me!" align="absmiddle" /> Found a bug? Report it here!')); ?></li>
		</ul>
	</div><?php } ?>
 	</div>   <!-- END topbar DIV -->
 <div id='contentpane'>
	<?php echo "<p>".$this->session->flashdata('message')."<p />"; ?>
    <?php
	$admin_message = '';
	if (isset($installWarning) || isset($dbConnectError) || isset($dataUpdate) || isset($configUpdate)) {
		if (isset($installWarning)) {
			$admin_message .= str_replace("[SITE_URL]",site_url(),$install_message);
		} // END if
		if (isset($dbConnectError)) {
			if (!empty($admin_message)) { $admin_message .= "<br />"; }
			$admin_message .= str_replace("[OOTP_FANTASY_URL]",MOD_SITE_URL,$dbConnect_message);
		} // END if
		if (isset($dataUpdate) || isset($configUpdate) && strpos(current_url(),"dashboard") === false) {
			if (!empty($admin_message)) { $admin_message .= "<br />"; }
			$admin_message .= str_replace("[ADMIN_URL]",$config['fantasy_web_root'].'/admin/dashboard',$update_message);
		} // END if
		if (!empty($admin_message)) {
		?>
		<span class="error"><?php echo($admin_message); ?></span>
		<?php
		} // END if
	} // END if
	?>
    <?php echo $content."\n" ?>
    <p>&nbsp;<br class="clear" clear="all" />
 </div>   <!-- END contentpane DIV -->
 <div id='bottombar'>
  <a href='http://www.ootpdevelopments.com'><img src='<?php echo($config['ootp_html_report_path']); ?>images/bnn_logo_bottom.jpg' /></a>
  <div id='credits'>
   <?php echo($league_abbr); ?> Fantasy Leagues powered by <?php echo('<a href="'.MOD_SITE_URL.'" target="_blank">'.SITE_NAME.'</a>'); ?> mod.<br />
   <?php echo(date('l, F jS, Y')); ?>
  </div>   <!-- END credits DIV -->
 </div>   <!-- END bottombar DIV -->

</div>   <!-- END pagebody DIV -->

<?php if (isset($config['google_analytics_enable']) && isset($config['google_analytics_tracking_id']) &&
		$config['google_analytics_enable'] != -1 && !empty($config['google_analytics_tracking_id'])) { ?>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo($config['google_analytics_tracking_id']); ?>']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<?php } ?>
</body>
</html>
