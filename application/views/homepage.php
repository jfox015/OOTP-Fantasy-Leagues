<div id="single-column" class="homepage">

    <h2>Welcome to the <?php echo($leagueAbbr); ?> Fantasy League!</h2>
    <br />
</div>
<div id="center-column" class="homepage">
	<?php if (isset($splashContent)) {
		echo($splashContent);	
	} ?>
    <br /><br />
    
    <h2>Fantasy News</h2>
    <?php if (isset($news) && sizeof($news)) { 
		
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
        <img src="<?php echo(PATH_NEWS_IMAGES.$news[0]['image']); ?>" align="left" border="0" class="league_news_<?php echo($class); ?>" />
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
        <p />&nbsp;&nbsp;
        <br clear="all" />
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_search.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
		<?php echo anchor('/search/news/', 'More News'); ?><br />
		<?php 
		if ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE) {
			echo('<img src="'.$config['fantasy_web_root'].'images/icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> '.anchor('/news/submit/mode/add/type_id/'.NEWS_FANTASY_GAME,'Add News Article'));
		}
        ?>
    
    
    <div class='textbox'>
    <table cellpadding="0" cellspacing="0" border="0" style="width:646px;">
    <tr class='title'>
    	<td style='padding:6px'>Public Leagues for the <?php echo($leagueName); ?></td>
    </tr>
    <tr>
    	<td class="hsc2_l">
        <?php if (isset($leagues) && sizeof($leagues) > 0) { ?>
        <ul id="league_list">
        	<?php foreach($leagues as $id => $details) { ?>
            <li>
            <?php if (isset($details['avatar']) && !empty($details['avatar'])) { ?>
            <img align="absmiddle" src="<?php echo(PATH_LEAGUES_AVATARS.$details['avatar']); ?>" 
            border="0" width="24" height="24" alt="<?php echo($details['league_name']); ?>" 
            title="<?php echo($details['league_name']); ?>" />
			<?php } ?>
			<?php echo(anchor('/league/info/'.$id, $details['league_name'])); ?></li>
        	<?php } ?>
        </ul>
        <?php } else { ?>
        	No Public Leagues are available at this time.
        <?php } ?>
        </td>
    </tr>
    </table>
    </div>
	<br clear="all" /><br />
	<?php
    if (isset($message) && !empty($message)) { ?>
    <?php echo($message); ?>
    <br><br>
    <?php } ?>
</div>

<div id="right-column">
	<!--  Fantasy League Details -->
    <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:265px;">
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
    <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:265px;">
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