    <div id="single-column">
        <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
    </div>
    <div id="center-column">
        <?php if (isset($newsImage) && !empty($newsImage)) { 
		// GET IMAGE DIMENSIONS
		$size = getimagesize(FCPATH.'images\news\\'.$newsImage);
		if (isset($size) && sizeof($size) > 0) {
			if ($size[0] > $size[1]) {
				$class = "wide";
			} else {
				$class = "tall";
			}
		}
		?>
        <img src="<?php echo(PATH_NEWS_IMAGES.$newsImage); ?>" align="left" border="0" class="league_news_<?php echo($class); ?>" />
        <?php } ?>
        
        <?php if (isset($newsDate) && !empty($newsDate)) { 
        	echo('<span class="league_date">'.date('l, M d',strtotime($newsDate)).'</span><br />');
        } ?>
        <?php if (isset($newsBody) && !empty($newsBody)) { 
			$maxChars = 500;
			if (strlen($newsBody) > $maxChars) {
				$dispNews = substr($newsBody,0,$maxChars);
			} else {
				$dispNews = $newsBody;
			}
			echo('<span class="news_body">'.$dispNews);
			if (strlen($newsBody) > $maxChars) {
				echo('&nbsp;&nbsp;'.anchor('/news/info/'.$newsId,'Read more...').'</span>');
			}
        } else {
       		echo("No news is available at this time.");
        } ?>
        <p />
        <br clear="all" />
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_search.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
		<?php echo anchor('/search/news/', 'More League News'); ?><br />
		<?php 
		if ($loggedIn && $isOwner) {
			echo('<img src="'.$config['fantasy_web_root'].'images/icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> '.anchor('/news/submit/mode/add/type_id/'.NEWS_LEAGUE.'/var_id/'.$league_id,'Add News Article'));
		}
        ?>
        <?php
		/*------------------------------------------------
		/	LEAGUE TRANSACTIONS MODULE
		/-----------------------------------------------*/
		?>
		<div class='textbox'>
            <table style="margin:6px" class="sortable" cellpadding="0" cellspacing="0" border="0" width="615px">
            <tr>
            <td>
            <?php if (isset($transaction_summary)) { 
                echo($transaction_summary); 
            } ?>
            </td>
            </tr>
            </table>
            <div style="width:98%; text-align:right;">
            <?php echo anchor('/league/transactions/'.$league_id,'See all transactions'); ?>
            </div>
            
        </div>  <!-- end batting stat div -->
	</div>
    <div id="right-column">
    	<?php 
		/*------------------------------------------------
		/	DRAFT MODULE
		/-----------------------------------------------*/
		if (isset($userDrafts) && isset($userDrafts[$league_id]) && sizeof($userDrafts[$league_id]) > 0) { 
			$draftInfo = $userDrafts[$league_id];
			if ($draftInfo['draftStatus'] > 0 && $draftInfo['draftStatus'] < 5) {
		?>
		<div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" width="225px">
        <tr class='title'>
            <td style='padding:3px'>League Draft</td>
        </tr>
        <tr>
			<td class='hsc2_l' style='padding:6px'><span style="color:#c00;font-weight:bold;">
            <?php 
			if ($draftInfo['draftStatus'] < 2) {
				if (isset($draftDate) && $draftDate != EMPTY_DATE_TIME_STR) {
					$statusMess = 'Your league draft begins '.date('m/d/Y',strtotime($draftDate))." at ".date('h:i A T',strtotime($draftDate));
				} else {
					$statusMess = 'Your league draft is coming up soon.';
				}
				$statusMess .= '<br /><br /> '.anchor('/team/info/'.$draftInfo['team_id'],$draftInfo['teamname']." ".$draftInfo['teamnick']).' is the first team to pick.';
			} else if ($draftInfo['draftStatus'] >= 2 && $draftInfo['draftStatus'] < 5) {
				$statusMess = 'Your league is currently drafting.</span><br /><br /> '.anchor('/team/info/'.$draftInfo['team_id'],$draftInfo['teamname']." ".$draftInfo['teamnick']).' is the next team to pick.';
			} // END if ($draftInfo['draftStatus'] > 0
			echo($statusMess); ?></span>
			<br /><br />
			<?php
			$outText = "Set up your draft list or review the player pool.";
			if ($draftInfo['draftStatus'] >= 2 && $draftInfo['draftStatus'] < 5 && $draftInfo['team_id'] == $user_team_id) { 
				$outText = "Pick now!";
			} else {
				echo "<strong>In the meantime:</strong><br /><br />";
			}
			echo '<img src="'.$config['fantasy_web_root'].'images/icons/application_edit.png" width="32" height="32" border="0" alt="" style="padding-right:6px; padding-bottom:6px;" title="" align="left" /> '.anchor('/draft/selection/league_id/'.$league_id.'/team_id/'.$user_team_id,$outText).'<br clear="all" /><br />';          
        	echo '<img src="'.$config['fantasy_web_root'].'images/icons/search.png" width="32" height="32" border="0" alt="" style="padding-right:6px; padding-bottom:6px;" title="" align="left" /> <a href="'.$config['ootp_html_report_path'].'leagues/league_'.$config['ootp_league_id'].'_players.html">Scout Players</a>';
        	?>
            </td>
        </tr>
        </table>
        </div>
		<?php } // END if ($draftInfo['draftStatus'] > 0 
		} // if (isset($userDrafts) 
					   
		// STANDINGS MODULE
        if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			if (isset($thisItem['divisions'])) {  ?>
        <div class='textbox'>
            <table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="213px">
            <?php 
			if (sizeof($thisItem['divisions']) > 0) {
            foreach($thisItem['divisions'] as $id=>$divisionData) { ?>
            <tr class='title'>
                <td colspan='5' class='lhl'><?php echo($divisionData['division_name']); ?></td></tr>
            <tr class='headline'>
                <td class='hsc2_c'>Team</td>
                <td class='hsc2_c'>W</td>
                <td class='hsc2_c'>L</td>
                <td class='hsc2_c'>GB</td>
            </tr>
            <?php 
            $rowcount = 0;
            $leadW = 0;
            $leadG = 0;
            if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
                foreach($divisionData['teams'] as $teamId => $teamData) { 
                if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }  // END if
                ?>
            <tr style="background-color:<?php echo($color); ?>">
                <td class='hsc2_l'><?php echo(anchor('/team/info/'.$teamId,$teamData['teamnick'])); ?></td>
                <td class='hsc2_l'><?php echo($teamData['w']); ?></td>
                <td class='hsc2_l'><?php echo($teamData['l']); ?></td>
                <?php 
                if ($rowcount == 0) { 
                    $leadG = $teamData['g']; $leadW = $teamData['w']; $gb = "--"; 
                } else {
                    $gb = $leadW - $teamData['w'];
                    if ((($leadG-$teamData['g'])%2) != 0) { $gb .= "<sup>1/2</sup>"; } // END if
                } // END if ($rowcount == 0) {)
                ?>
                <td class='hsc2_l'><?php echo($gb); ?></td>
            </tr>
                <?php
                $rowcount++;
                } // END foreach($divisionData['teams'] 
            } else { ?>
            <tr>
                <td class="hsc2_l" colspan="4">No teams were found</td>
            </tr>
            <?php } // END if (isset($divisionData['teams']
				} // END foreach($thisItem['divisions']
            } else { ?>
            <tr class='title'>
                <td class="lhl">No divisions were found for this league.</td>
            </tr>
            <?php } // END if (isset($divisionData['teams']
				}  // END if (isset($thisItem['divisions'])
			} // END if ($scoring_type ==LEAGUE_SCORING_HEADTOHEAD

			if ($scoring_type != LEAGUE_SCORING_HEADTOHEAD) {
				if (isset($thisItem['teams']) && sizeof($thisItem['teams']) > 0) { 
			?>
            <div class='textbox'>
            <table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="213px">
            <tr class='title'>
                <td colspan='5' class='lhl'>Current Standings</td>
            </tr>
            <tr class='headline'>
                <td class='hsc2_c'>Team</td>
                <td class='hsc2_c'>Total</td>
            </tr>
            <?php 
            $rowcount = 0;            
                foreach($thisItem['teams'] as $teamId => $teamData) { 
                if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
                ?>
            <tr style="background-color:<?php echo($color); ?>">
                <td class='hsc2_l'><?php echo(anchor('/team/info/'.$teamId,$teamData['teamnick'])); ?></td>
                <td class='hsc2_l'><?php echo($teamData['total']); ?></td>
            </tr>
                <?php
                $rowcount++;
                }
            } else { ?>
            <tr>
                <td class="hsc2_l" colspan="4">No teams were found</td>
            </tr> 
            <?php } 
			} ?>
            
            
            </table>
        </div>  <!-- end batting stat div -->
        
        <?php

		/*------------------------------------------------
		/	LEAGUE GAME RESULTS MODULE
		/-----------------------------------------------*/
		if (isset($gameList) && sizeof($gameList) > 0) { ?>
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" width="225px">
        <tr class='title'>
            <td style='padding:3px'>Game Scores</td>
        </tr>
        <tr>
        <tr class='headline'>
            <td style='padding:6px'>Period <?php echo($curr_period_id); ?></td>
        </tr>
        <tr>
            <td style='padding:3px'>
            <table cellpadding="2" cellspacing="1" border="0" style="width:100%;">
            <?php
			$rowCount = 0;
            foreach($gameList as $game_id => $data) { ?>
            <tr align=left class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">
                <?php 
                $homeTeamArr = explode(" ",$data['home_team_name']);
                $awayTeamArr = explode(" ",$data['away_team_name']);
                ?>
                <td width="40%"><?php echo($homeTeamArr[1]); ?><br /><?php echo($awayTeamArr[1]); ?></td>
                <td width="30%" align="right"><?php echo($data['home_team_score']); ?><br /><?php echo($data['away_team_score']); ?></td>
                <td width="30%" align="center"><a href="#" rel="game_nav" id="<?php echo($game_id); ?>">game</a></td>
            </tr>
            <?php 
			$rowCount++;
            } // END foreach
            ?>
            </table>
            </td>
        </tr>
        </table>
        </div>
		<?php } ?>
        <?php
		/*------------------------------------------------
		/	LEAGUE DETAILS MODULE
		/-----------------------------------------------*/
		?>
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" width="225px">
        <tr class='title'>
            <td style='padding:3px'>League Details</td>
        </tr>
        <tr>
            <td style='padding:6px'>
            <div id="row">
            <b>Status:</b>
            <span <?php if ($thisItem['statusType'] == 'Active') { echo('style="color:#060"'); } else { echo('style="color:#C00"'); } ?>>
            <?php echo($thisItem['statusType']); ?></span><br />
            <b>Commisioner:</b>
            <span><?php if ($thisItem['commissionerId'] != -1) { echo(anchor('/user/profile/mode/view/id/'.$thisItem['commissionerId'],$thisItem['commissionerName'])); } else { echo("No Commissioner"); } ?></span><br />
            <b>Members:</b>
            <span><?php echo($thisItem['memberCount']); ?></span><br />
            <b>Scoring System:</b>
            <span><?php echo($thisItem['leagueType']); ?></span><br />
            <b>Type:</b>
            <span <?php if ($thisItem['accessType'] == 'Public') { echo('style="color:#060"'); } else { echo('style="color:#C00"'); } ?>>
            <?php echo($thisItem['accessType']); ?></span></td>
        </tr>
        </table>
        </div>
        
    </div>
    <script type="text/javascript">
    $(document).ready(function(){		   
		$('a[rel=game_nav]').click(function() {
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/results/id/<?php echo($league_id); ?>/period_id/<?php echo($curr_period_id); ?>/game_id/'+this.id;
			return false;
		});
	});
    </script>