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
	<!-- League Status -->
    <div id="contentBox">
        <div class="title">League Status</div>
        <br /><br />
        <?php 
		if (!isset($league_info)) { ?>
        <div id="row">
        <span class="error" style="margin:0px; width:90%;"><strong>League Files not loaded</strong>
        <br /><br />
		The OOTP database files have not been uploaded yet for this league.</span>
        </div>
        <?php } else { ?>
        <div id="row">
        The <?php echo($leagueName." is currently in the:"); ?>
        <span><b><?php echo($leagueStatus); ?></b></span><br />&nbsp;
        </div>
        <div id="row">
        The current league Date is:
        <span><b><?php echo($current_date); ?></b></span><br />&nbsp;
        </div>
        <div id="row">
        Next Sim:
        <span><b><?php echo($nextSimDate); ?></b></span><br />&nbsp;
        </div>
        <?php } ?>
    </div>
    <div style="margin:6px 0 6px 0;min-height:12px;"><br clear="all" class="clear" /></div>
                
	<!-- League Events -->
    <div id="contentBox">
        <div class="title">Upcoming OOTP Events</div>
        <br /><br />
		<?php if (isset($events) && sizeof($events) > 0) { 
			foreach($events as $event) { 
		?>
        <div id="row">
        <b><?php echo(date('F d, Y',strtotime($event['start_date']))); ?></b>
        <span><?php echo($event['name']); ?></span><br />&nbsp;
        </div>
        
        <?php 	} // END for
		} else {
			?>
		<div id="row">
        <span class="error" style="margin:0px; width:90%;"><strong>League Files not loaded</strong>
        <br /><br />
		The OOTP database files have not been uploaded yet for this league. No events are available.</span>
        </div>
		<?php
        } // END if 
		?>
    </div>
</div>

<br class="clear" />