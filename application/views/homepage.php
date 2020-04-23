<div id="single-column" class="homepage">
	<article class="hero-area">
		<?php if (isset($splashContent)) {
			echo($splashContent);	
		} ?>
		<h2>Welcome to the <?php echo($leagueAbbr); ?> Fantasy League!</h2>
	</article>
</div>
<div id="center-column" class="homepage">
	<div class="layout homepage">
		<div class="layout-column">
			<div class="headline">Public Leagues for the <?php echo($leagueName); ?></div>
			<section class="content">
			<?php 
			if (isset($leagues) && sizeof($leagues) > 0) { 
				foreach($leagues as $id => $details) { 
					?>
					<section class="teamLinks flex">
						<figure class="logo-lg">
							<?php
							if (isset($details['avatar']) && !empty($details['avatar'])) { 
								$avatar = PATH_LEAGUES_AVATARS.$details['avatar']; 
							} else {
								$avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
							} 
							?>
							<img align="absmiddle" src="<?php echo($avatar); ?>" 
							alt="<?php echo($details['league_name']); ?>" title="<?php echo($details['league_name']); ?>" />
						</figure>
						<div class="flexContent">
							<?php echo(anchor('/league/home/'.$id, $details['league_name'],['class' => 'teamLink'])); ?>
							<div class="TeamLinks-Links">
								<?php echo($details['league_type_lbl']." League"); ?>
							</div>
						</div>
					</section>
				<?php
				} // END foreach($divisionData['teams']
			} else {
				echo("No Public Leagues are available at this time.");
			}  // END if (isset($leagues)
			?>
			</section>
		</div>
	</div>
	<br clear="all" /><br />
    <div class="news_title home">
		<h3>Fantasy Leagues News</h3>
	</div>
	<div class="rule"></div>
	<?php 
	if (isset($news) && sizeof($news)) { 
		
		$dispNews = '';
		if (isset($news[0]['news_subject']) && !empty($news[0]['news_subject'])) { 
        	echo('<h3>'.$news[0]['news_subject'].'</h3>');
        }
		if (isset($news[0]['image']) && !empty($news[0]['image'])) {
		// GET IMAGE DIMENSIONS
		$size = getimagesize(FCPATH.'images\news\\'.$news[0]['image']);
		if (isset($size) && sizeof($size) > 0) {
			if ($size[0] > $size[1]) {
				$class = "wide";
			} else {
				$class = "tall";
			}
		}
		?>
        <img src="<?php echo(PATH_NEWS_IMAGES.$news[0]['image']); ?>" align="left" class="league_news_<?php echo($class); ?>" />
        <?php } ?>
        
        <?php if (isset($news[0]['news_date']) && !empty($news[0]['news_date'])) { 
        	echo('<span class="league_date">'.date('l, M d',strtotime($news[0]['news_date'])).'</span>&nbsp; --&nbsp;');
        } ?>
        <?php if (isset($news[0]['news_body']) && !empty($news[0]['news_body'])) { 
			$maxChars = 500;
			if (strlen($news[0]['news_body']) > $maxChars) {
				$dispNews = substr($news[0]['news_body'],0,$maxChars);
			} else {
				$dispNews = $news[0]['news_body'];
			}
			echo('<span class="news_body">'.$dispNews);
			if (strlen($news[0]['news_body']) > $maxChars) {
				echo('&nbsp;&nbsp;'.anchor('/news/info/'.$news[0]['id'],'Read more...').'</span>');
			}
        }
	}  else {
		echo("No news is available at this time.");
	} ?>
	<p>&nbsp;&nbsp;
	<br clear="all" />
	<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_search.gif" width="16" height="16" alt="Add" title="add" align="absmiddle" /> 
	<?php echo anchor('/search/news/', 'More News'); ?><br />
	<?php 
	if ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE) {
		echo('<img src="'.$config['fantasy_web_root'].'images/icons/icon_add.gif" width="16" height="16" alt="Add" title="add" align="absmiddle" /> '.anchor('/news/submit/mode/add/type_id/'.NEWS_FANTASY_GAME,'Add News Article'));
	}
    if (isset($message) && !empty($message)) { ?>
    <?php echo($message); ?>
	<br /><br />
	<?php } ?>
</div>

<div id="right-column">
	<!--  Fantasy League Details -->
    <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0" style="width:265px;">
        <tr class='title'>
            <td style='padding:3px'>Fantasy League Overview</td>
        </tr>
		<tr class='headline'>
            <td style='padding:3px'>Fantasy Site Status</td>
        </tr>
        <tr>
            <td style='padding:6px'>
			The game is currently in the:<br />
			<span style="margin-left: 12px;"><b><?php echo($fantasyStatus); ?></b></span><br />&nbsp;<br />
			<?php if ($fantasyStatusID == 1) { ?>
			The fantasy season begins:<br />
			<span style="margin-left: 12px;"><b><?php echo($fantasyStartDate); ?></b></span><br />&nbsp;<br />
			<?php } ?>
			The next Sim will be processed on:<br />
			<span style="margin-left: 12px;"><b><?php echo($nextSimDate); ?></b></span>
			</td>
		</tr>
		</table>
	</div>
	
	<!-- OOTP League Details Box -->
    <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0" style="width:265px;">
        <tr class='title'>
            <td style='padding:3px'>OOTP League Details</td>
        </tr>
		<tr class='headline'>
            <td style='padding:3px'>League Status</td>
        </tr>
        <tr>
            <td style='padding:6px' >
			<?php 
			if (!isset($league_info)) { ?>
			<span class="error" style="margin:0px; width:90%;"><strong>League Files not loaded</strong>
			<br /><br />
			The OOTP database files have not been uploaded yet for this league.</span>
			<?php } else { ?>
			The <?php echo($leagueName." is currently in the:"); ?><br />
			<span style="margin-left: 12px;"><b><?php echo($leagueStatus); ?></b></span><br />&nbsp;<br />
			The current league Date is:<br />
			<span style="margin-left: 12px;"><b><?php echo($current_date); ?></b></span>
			<?php } ?>
			</td>
		</tr>
        <tr class='headline'>
            <td style='padding:3px'>Upcoming OOTP Events</td>
        </tr>
        <tr>
            <td style='padding:6px' >
		<?php if (isset($events) && sizeof($events) > 0) { 
			foreach($events as $event) { 
		?>
        <b><?php echo(date('F d, Y',strtotime($event['start_date']))); ?></b><br />
        <span style="margin-left: 12px;"><?php echo($event['name']); ?></span><br />&nbsp;<br />
        <?php 	} // END for
		} else {
			?>
        <span class="error" style="margin:0px; width:90%;"><strong>League Files not loaded</strong>
        <br /><br />
		The OOTP database files have not been uploaded yet for this league. No events are available.</span>
		<?php
        } // END if 
		?>
		</td>
		</tr>
		</table>
    </div>
</div>

<br class="clear" />