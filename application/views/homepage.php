<div id="single-column" class="homepage">
	<article class="hero-area">
		<?php if (isset($splashContent)) {
			echo($splashContent);	
		} ?>
		<h2>Welcome to the <?php echo($leagueAbbr); ?> Fantasy League!</h2>
	</article>
</div>
<div id="center-column" class="homepage">
		<h3>Public Leagues<?php if (isset($leagueName) && !empty($leagueName)) { ?> for the <?php echo($leagueName);  } ?></h3>
		<div class="layout homepage">
		<?php 
		if (isset($leagues) && sizeof($leagues) > 0) {
			if (count($leagues)> 1) {
				$leaguesPerColumn = intval(count($leagues) / 2);
				if (($leaguesPerColumn % 2) != 0) { $leaguesPerColumn++; }
			} else {
				$leaguesPerColumn =  1;
			}
			$totalleaguesDrawn = 0;
			$leaguesDrawn = 0;

			foreach($leagues as $id => $details) { 
				if ($leaguesDrawn == 0 || $leaguesDrawn == $leaguesPerColumn) {
					if ($leaguesDrawn == $leaguesPerColumn) {
						$leaguesDrawn = 0;
					} // END if ($leaguesDrawn == $leaguesPerColumn)
				?>
		<div class="layout-column">
			<section class="content">
				<?php
				} // END if ($articlesDrawn == 0 || $articlesDrawn == $articlesPerColumn)
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
			$leaguesDrawn++;
			$totalleaguesDrawn++;
			if ($leaguesDrawn == $leaguesPerColumn || $totalleaguesDrawn == count($leagues)) { ?>
			</section>
		</div>
				<?php
                $leaguesDrawn = 0;
				} // END if
			} // END foreach($leagues as $id => $article) 
		} else {
			echo('<div class="layout-column"><section class="content"><h4>No Public Leagues are available at this time.</h4></section></div>');
		}  // END if (isset($leagues)
		?>
	</div>
	<?php 
	if (!$loggedIn) {
		echo(anchor('/user/login','<button id="btnSubmit" class="sitebtn login" style="display:inline-block;">Login/Signup to Create League</button>'));
	} else {
		if (!$amCommish) {
			echo(anchor('/league/submit/mode/add','<button id="btnSubmit" class="sitebtn login" style="display:inline-block;">Create a League</button>'));
		}
	}
	?>
	<br clear="all" /><br />
    <div class="news_title home">
		<h3>Fantasy Leagues News</h3>
	</div>
	<div class="rule"></div>
	<div class="home_news">
		<?php 
		if (isset($news) && sizeof($news)) { 
			
			$dispNews = '';
			
			if (isset($news[0]['image']) && !empty($news[0]['image'])) {
			// GET IMAGE DIMENSIONS
			$size = getimagesize(FCPATH.'images'.URL_PATH_SEPERATOR.'news'.URL_PATH_SEPERATOR.$news[0]['image']);
			if (isset($size) && sizeof($size) > 0) {
				if ($size[0] > $size[1]) {
					$class = "wide";
				} else {
					$class = "tall";
				}
			}
			?>
			<div class="home_news_img">
				<img src="<?php echo(PATH_NEWS_IMAGES.$news[0]['image']); ?>" class="league_news_<?php echo($class); ?>" />
			</div>
			<?php } ?>
			<div class="home_news_body">
				<?php
				if (isset($news[0]['news_subject']) && !empty($news[0]['news_subject'])) { 
					echo('<h3>'.$news[0]['news_subject'].'</h3><br />');
				}
				if (isset($news[0]['news_date']) && !empty($news[0]['news_date'])) { 
					echo('<span class="league_date">'.date('l, M d',strtotime($news[0]['news_date'])).'</span>&nbsp; --&nbsp;');
				} 
				if (isset($news[0]['author_id']) && !empty($news[0]['author_id'])) { 
					$authorName = resolveOwnerName($news[0]['author_id']);
					if (empty($authorName)) {
						$authorName = resolveUsername($news[0]['author_id']);
					}
				}
				echo('<span class="news_author">'.$authorName.'</span>');
				echo('<br />');
				?>
				<?php if (isset($news[0]['news_body']) && !empty($news[0]['news_body'])) { 
					//$maxChars = 500;
					if (strlen($news[0]['news_body']) > $excerptMaxChars) {
						$dispNews = substr($news[0]['news_body'],0,$excerptMaxChars);
						$lastSpace = strrpos($dispNews, ' ', -2);
						$dispNews = substr($dispNews,0,$lastSpace);
					} else {
						$dispNews = $news[0]['news_body'];
					}
					echo('<span class="news_body">'.$dispNews);

					$typeIdStr = "";
					$varIdStr = "";
					if (isset($news[0]['type_id']) && !empty($news[0]['type_id']) && $news[0]['type_id'] != -1) {
						$typeIdStr = "/type_id/".$news[0]['type_id'];
					}
					if (isset($news[0]['var_id']) && !empty($news[0]['var_id']) && $news[0]['var_id'] != -1) {
						$varIdStr = "/var_id/".$var_id;
					}
					if (strlen($news[0]['news_body']) > $excerptMaxChars) {
						echo('&nbsp;&nbsp;'.anchor('/news/article/id/'.$news[0]['id'].$typeIdStr.$varIdStr,'Read more...').'</span>');
					} else {
						echo('<br>'.anchor('/news/article/id/'.$news[0]['id'].$typeIdStr.$varIdStr,'Read Article').'</span>');
					}
				}
				?>
			</div>
		<?php
		}  else {
			echo("No news is available at this time.");
		} ?>
	</div>
	<p>&nbsp;&nbsp;
	<br clear="all" />
	<div class="button_bar" style="text-align:right;">
	<?php echo anchor('/news/articles/'.NEWS_FANTASY_GAME, '<button id="btnClear" class="sitebtn adddrop" style="display:inline-block;">More News</button>'); ?>
	<?php 
	if ($loggedIn && $accessLevel >= ACCESS_WRITE) {
		echo(anchor('/news/submit/mode/add/type_id/'.NEWS_FANTASY_GAME,'<button id="btnSubmit" class="sitebtn adddrop" style="display:inline-block;">Add Article</button>'));
	}
	?>
	</div>
	<?php
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
	
	<?php if (isset($league_info) || ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE)) { ?>
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
	<?php } ?>
</div>

<br class="clear" />