    <div id="center-column" class="league-home">
        <div class="top-bar">
            <?php
            if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
                $avatar = PATH_LEAGUES_AVATARS.$thisItem['avatar']; 
            } else {
                $avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
            } 
            ?>
            <img src="<?php echo($avatar); ?>" width="100" height="100" alt="<?php echo($thisItem['league_name']); ?>" 
            title="<?php echo($thisItem['league_name']); ?>" /> 
            <h1><?php echo($thisItem['league_name']); ?></h1>
            <?php
            if (isset($thisItem['description']) && !empty($thisItem['description'])) { 
                echo('<br /><strong>'.$thisItem['description'].'</strong<br />');
            }
            ?>
        </div>

        <div class="news_title">
            <h3>Latest League News</h3>
        </div>
        <div class="rule"></div>

        <?php 
        if (isset($newsTitle) && !empty($newsTitle)) { 
            echo("<h2>".$newsTitle."</h2>");
        }
        if (isset($newsImage) && !empty($newsImage)) { 
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
        <?php 
        } // END if (isset($newsImage) && !empty($newsImage))

        if (isset($newsDate) && !empty($newsDate)) { 
        	echo('<span class="league_date">'.date('l, M d',strtotime($newsDate)).'</span>');
        } 
        if (isset($newsDate) && !empty($newsDate) && isset($author) && !empty($author)) { 
            echo(" | ");
        } else {
            echo("<br />");
        }
        if (isset($author) && !empty($author)) { 
        	echo('<span class="news_author">'.$author.'</span><br />');
        }
        if (isset($newsBody) && !empty($newsBody)) { 
			$maxChars = 500;
			if (strlen($newsBody) > $maxChars) {
				$dispNews = substr($newsBody,0,$maxChars);
			} else {
				$dispNews = $newsBody;
			}
			echo('<span class="news_body">'.$dispNews);
			if (strlen($newsBody) > $maxChars) {
				echo('&nbsp;&nbsp;'.anchor('/news/article/id/'.$newsId.'/type_id/'.NEWS_LEAGUE.'/var_id/'.$league_id,'Read more...').'</span>');
			}
        } else {
       		echo("No news is available at this time.");
        } ?>
        <p>
        <br clear="all" />
        <div class="button_bar" style="text-align:right;">
        <?php echo anchor('/news/articles/type_id/'.NEWS_LEAGUE.'/var_id/'.$league_id, '<button id="btnClear" class="sitebtn news">More News</button>'); ?>
        <?php 
        if ($loggedIn && ($accessLevel >= ACCESS_WRITE || $thisItem['commissionerId'] == $currUser|| $isLeagueMember === true)) {
            echo(anchor('/news/submit/mode/add/type_id/'.NEWS_LEAGUE.'/var_id/'.$league_id,'<button id="btnSubmit" class="sitebtn news">Add Article</button>'));
        }
        ?>
        </div>
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
            
        </div>
	</div>
    <div id="right-column">
    	<?php 
        /*------------------------------------------------
		/	CONTACT COMMISSIONER LINK
		/-----------------------------------------------*/
		print(anchor('/league/leagueContact/'.$league_id,'<img src="'.PATH_IMAGES.'/btn_contact_commish.png" width="220" height="57" border="0" alt="Contact the commissioner" title="Contact the commissioner" />'));
		print('<br /><br /> ');
        /*------------------------------------------------
		/	LEAGUE STATUS MODULE
        /-----------------------------------------------*/
        if (isset($thisItem['league_status']) && !empty($thisItem['league_status']) && $thisItem['league_status'] != 1) {
            if ($thisItem['league_status'] ==2) {
                $class="warn";
                $message = "This League is currently inactive. It is bypassed in all League Sims. Please contact the Site Administrator with questions.";
            } else {
                $class="error";
                $message = "This League has been suspended or removed. Any actions taken will have no effect on Sim Processing. Please contact the Site Administrator with questions.";
            }
        ?>
        <div class='textbox right-column'>
            <table cellpadding="0" cellspacing="0">
                <thead>	
                <tr class="title">
                    <td class='hsc2_l'>League Status</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="<?php echo($class); ?>"><?php echo($message); ?></span></td>
                </tr>
            </tbody>
            </table>
        </div>
        <?php
        }
		/*------------------------------------------------
		/	DRAFT MODULE
		/-----------------------------------------------*/
		if (isset($userDrafts) && isset($userDrafts[$league_id]) && sizeof($userDrafts[$league_id]) > 0) { 
			$draftInfo = $userDrafts[$league_id];
			if ($draftInfo['draftStatus'] > 0 && $draftInfo['draftStatus'] < 5) {
		?>
		<div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0">
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
        ?>
        <div class='textbox right-column'>
            <table cellpadding="5" cellspacing="0" border="0">
            <tr class='title'>
                <td colspan='5'>Standings</td>
            </tr>
            <?php
        if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			if (isset($thisItem['divisions'])) {  
			    if (sizeof($thisItem['divisions']) > 0) {
                    foreach($thisItem['divisions'] as $id=>$divisionData) { ?>
                    <tr class='headline'>
                        <td colspan='5'><?php echo($divisionData['division_name']); ?></td></tr>
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
                <?php 
                        } // END if (isset($divisionData['teams']
                    } // END foreach($thisItem['divisions']
                } else { ?>
            <tr class='headline'>
                <td class="lhl">No divisions were found for this league.</td>
            </tr>
            <?php 
                } // END if (isset($divisionData['teams']
			}  // END if (isset($thisItem['divisions'])
		} // END if ($scoring_type ==LEAGUE_SCORING_HEADTOHEAD

        if ($scoring_type != LEAGUE_SCORING_HEADTOHEAD) {
            if (isset($thisItem['teams']) && sizeof($thisItem['teams']) > 0) { 
			?>
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
                } // END foreach($thisItem['teams']
            } else { ?>
            <tr>
                <td class="hsc2_l" colspan="4">No teams were found</td>
            </tr> 
            <?php 
            } // END if (isset($thisItem['teams']
        } // END if ($scoring_type != LEAGUE_SCORING_HEADTOHEAD) {
        ?>
            
            </table>
        </div>  <!-- end batting stat div -->
        
        <?php
		/*------------------------------------------------
		/	LEAGUE GAME RESULTS MODULE
		/-----------------------------------------------*/
		if (isset($gameList) && sizeof($gameList) > 0) { ?>
        <div class='textbox right-column'>
        <table cellpadding="5" cellspacing="0" border="0">
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
                $homeTeamName = (strstr($data['home_team_name'], "Team ")) ? $homeTeamArr[2] : $homeTeamArr[1];
                $awayTeamArr = explode(" ",$data['away_team_name']);
                $awayTeamName = (strstr($data['away_team_name'], "Team ")) ? $awayTeamArr[2] : $awayTeamArr[1];
                ?>
                <td width="50%"><?php echo($homeTeamName); ?><br /><?php echo($awayTeamName); ?></td>
                <td width="50%" style="text-align:right"><?php echo($data['home_team_score']); ?><br /><?php echo($data['away_team_score']); ?></td>
                <!--td width="30%" align="center"><a href="#" rel="game_nav" id="<?php echo($game_id); ?>">game</a></td-->
            </tr>
            <?php 
			$rowCount++;
            } // END foreach
            ?>
            </table>
            </td>
        </tr>
        <tr style="background-color:#EAEAEA">
            <td style='padding:6px'><?php echo(anchor('/league/results/id/'.$league_id,"View All Results")); ?></td>
        </tr>
        </table>
        </div>
		<?php } 
		/*------------------------------------------------
		/	LEAGUE DETAILS MODULE
		/-----------------------------------------------*/
		?>
        <div class='textbox right-column'>
        <table cellpadding="5" cellspacing="0" border="0">
        <tr class='title'>
            <td style='padding:3px'>League Details</td>
        </tr>
        <tr>
            <td style='padding:12px; line-height:1.5;'>
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