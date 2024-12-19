<script type="text/javascript" charset="UTF-8">
	var ajaxWait = '<img src="<?php echo(PATH_IMAGES); ?>icons/ajax-loader.gif" width="28" height="28" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo(PATH_IMAGES); ?>icons/icon_fail.png" width="24" height="24" align="absmiddle" />&nbsp;';
	var fader = null;
	//var team_id = <?php //$team_id ?>;
	var league_id = <?php echo($league_id); ?>;
	var fader = null;
	$(document).ready(function(){
		$('input[rel=changeTeam]').live('click',function () {
			var params = this.id.split("|");
			var currTeam = params[0];
			var playerId = params[1];
			var position = params[2];
			var role = typeof(params[3] !== 'undefined') ? params[3] : -1;
			var teamId = $('select#rosterTeamList').val();
			if (teamId == -1) {
				$('div#activeStatus').addClass('error');
				$('div#activeStatus').html("You must select a new team assignment from the list top continue.");
				$('div#activeStatusBox').fadeIn("slow",function() { setTimeout('fadeStatus("active")',15000); });
			} else if (teamId != currTeam) {
				var url = "<?php echo($config['fantasy_web_root']); ?>team/administrativeAdd/league_id/"+league_id+"/player_id/"+playerId+"/team_id/"+teamId+"/curr_team/"+currTeam+"/pos/"+position+'/role/'+role;
				$('div#activeStatus').removeClass('error');
				$('div#activeStatus').removeClass('success');
				$('div#activeStatus').empty();
				$('div#activeStatus').html(ajaxWait);
				$('div#activeStatusBox').show();
				$.getJSON(url, function(data){
					if (data.code.indexOf("200") != -1) {
						if (data.status.indexOf(":") != -1) {
							var status = data.status.split(":");
							$('div#activeStatus').addClass(status[0].toLowerCase());
							$('div#activeStatus').html(status[1]);
						} else {
							$('div#activeStatus').addClass('success');
							$('div#activeStatus').html('The players team was successfully changed. This transaction has been logged in the league transactions log.');
						}
						$('div#activeStatusBox').fadeIn("slow");
						setTimeout(document.location.href = '<?php echo($_SERVER['REQUEST_URI']); ?>',1500000);
					} else {
						var outHTML = '<span class="error">An error occured. The operation was not completed.</td>';
						$('div#activeStatus').append(outHTML);
					}
				});
			}
			return false;
		});

		$('div#activeStatusBox').hide();

		$('input[rel=itemPick]').live('click',function () {
			var params = this.id;
			var url = "<?php echo($config['fantasy_web_root']); ?>draft/addPlayer/league_id/"+league_id+"/player_id/"+params;
			$('div#activeStatus').removeClass('error');
			$('div#activeStatus').removeClass('success');
			$('div#activeStatus').empty();
			$('div#activeStatus').html(ajaxWait);
			$('div#activeStatusBox').show();
			$.getJSON(url, function(data){
				if (data.code.indexOf("200") != -1) {
					if (data.status.indexOf(":") != -1) {
						var status = data.status.split(":");
						$('div#activeStatus').addClass(status[0].toLowerCase());
						$('div#activeStatus').html(status[1]);
					} else {
						$('div#activeStatus').addClass('success');
						$('div#activeStatus').html('Player Added Successfully');
						$('input[rel=itemPick]').remove();
					}
					$('div#activeStatusBox').fadeIn("slow",function() { setTimeout('fadeStatus("active")',15000); });
				} else {
					var outHTML = '<span class="error">An error occured. The operation was not completed.</td>';
					$('div#activeStatus').append(outHTML);
				}
			});
			return false;
		});
		$('input[rel=draft]').live('click',function () {
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>draft/selection/league_id/'+league_id+'/player_id/'+this.id;
		});
		$('input[rel=addPlayer]').live('click',function () {
			var params = this.id.split("|");
			var url = "<?php echo($config['fantasy_web_root']); ?>team/addAndDisplay/league_id/"+params[0]+"/team_id/"+params[1]+"/player_id/"+params[2]+"/position/<?php echo($thisItem['position']); ?>/role/<?php echo($thisItem['role']); ?>";
			$('div#roster_status').html(ajaxWait);
			$.getJSON(url, function(data){
				$('div#roster_status').empty();
				if (data.code.indexOf("200") != -1) {
					$('div#activeStatus').removeClass('error');
					$('div#activeStatus').removeClass('success');
					if (data.status.indexOf(":") != -1) {
						var status = data.status.split(":");
						$('div#activeStatus').addClass(status[0].toLowerCase());
						$('div#activeStatus').html(status[1]);
					} else {
						$('div#activeStatus').addClass('success');
						$('div#activeStatus').html('Player Added Successfully');
					}
					document.location.href = '<?php echo($_SERVER['REQUEST_URI']); ?>';
				} else {
					$('div#roster_status').append('<div id="listColumn1" class="listcolumn"><ul> <li>No status was returned.</li> </ul> </div>');
				}
			});
			return false;
		});
		$('input[rel=dropPlayer]').live('click',function () {
			if (confirm("Are you sure you want to drop this player?")) {
				var params = this.id.split("|");
				var url = "<?php echo($config['fantasy_web_root']); ?>team/removeAndDisplay/league_id/"+params[0]+"/team_id/"+params[1]+"/player_id/"+params[2];
				$('div#roster_status').html(ajaxWait);
				$.getJSON(url, function(data){
					$('div#roster_status').empty();
					if (data.code.indexOf("200") != -1) {
						if (data.status.indexOf(":") != -1) {
							var status = data.status.split(":");
							$('div#activeStatus').addClass(status[0].toLowerCase());
							$('div#activeStatus').html(status);
						} else {
							$('div#activeStatus').addClass('success');
							$('div#activeStatus').html('Player Removed Successfully');
						}
						document.location.href = '<?php echo($_SERVER['REQUEST_URI']); ?>';
					} else {
						$('div#roster_status').append('<div id="listColumn1" class="listcolumn"><ul> <li>No status was returned.</li> </ul> </div>');
					}
				});
			}
			return false;
		});
		$('input[rel=tradePlayer]').live('click',function () {
			var params = this.id.split("|");
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/trade/league_id/'+league_id+'/id/'+params[0]+'/tradeTo/'+params[1];
		});
		$('input[rel=tradeForPlayer]').live('click',function () {
			var params = this.id.split("|");
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/trade/league_id/'+league_id+'/id/'+params[0]+'/team_id2/'+params[1]+'/tradeFrom/'+params[2];
		});
	});
</script>
<div id="content">
    <div id="subPage">
        <div id="content">
        		<?php $name = $thisItem['first_name']." ".$thisItem['last_name']; ?>
    		<table width="925" cellpadding="0" cellspacing="0" class="teamheader">
            <tr>
            <td width="120" height="140" style="text-align: center;">

            <div class="playerpic" style="width:90px;">
            <?php
        $htmlpath=$config['ootp_html_report_path'];
		    $filepath=$config['ootp_html_report_root'];
        $imgpath=$filepath.URL_PATH_SEPERATOR."images".URL_PATH_SEPERATOR."person_pictures".URL_PATH_SEPERATOR."player_".$thisItem['player_id'].".png";
		    ## Check for photo by player ID
        if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/player_".$thisItem['player_id'].".png'>";}
         else
         {
           $imgpath=$htmlpath."images/person_pictures/".str_replace(" ","_",$name).".png";   ## Check for capitalized name PNG
           if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/".str_replace(" ","_",$name).".png'>";}
            else
            {
              $imgpath=$htmlpath."images/person_pictures/".str_replace(" ","_",strtolower($name)).".png";   ## Check for lowercase name PNG
              if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/".str_replace(" ","_",strtolower($name)).".png'>";}
               else
               {
                 $imgpath=$htmlpath."images/person_pictures/".str_replace(" ","_",$name).".jpg";   ## Check for capitalized name JPG
                 if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/".str_replace(" ","_",$name).".jpg'>";}
                  else
                  {
                    $imgpath=$htmlpath."images/person_pictures/".str_replace(" ","_",strtolower($name)).".jpg";   ## Check for lowercase name JPG
                if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/".str_replace(" ","_",strtolower($name)).".jpg'>";}
                     else
                     {
                   $imgpath=$htmlpath."images/person_pictures/".str_replace(" ","_",$name).".bmp";   ## Check for capitalized name BMP
                   if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/".str_replace(" ","_",$name).".bmp'>";}
                    else
                        {
                          $imgpath=$htmlpath."images/person_pictures/".str_replace(" ","_",strtolower($name)).".bmp";   ## Check for lowercase name bmp
                  if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/".str_replace(" ","_",strtolower($name)).".bmp'>";}
                   else
                       {
                     echo "<img src='".$htmlpath."images/person_pictures/default_player_photo.png'>";   ## Show default
                        }
                }
                 }
                  }
               }
            }
         } ?>

            </div>

            </td>
            <td width="705" height="150" style="vertical-align:top">

                <div class="player"><?php echo($name." ");
				if ($thisItem['position'] != 1) {
					echo(get_pos($thisItem['position']));
				} else {
					echo(get_pos($thisItem['role']));
				}
         ?></div>
                <div class="playerbio" height="105">
                    <strong>Team:</strong> <?php if (!empty($thisItem['team_id'])) { ?> 
                      <a href="<?php echo($htmlpath); ?>teams/team_<?php echo($thisItem['team_id']); ?>.html" target="_blank"><?php echo(" ".$thisItem['team_name']." ".$thisItem['teamNickname']); ?></a>
                    <?php 
                    } else {
                      echo("No Team");
                    }
                    ?>
                    | <strong>Nickname:</strong> <?php echo($thisItem['playerNickname']); ?>
                    <!--| <strong>MLB Experience:</strong>
                    | <strong>Salary:</strong> -->
                    <br />
                    <strong>Height/Weight:</strong> <?php echo(cm_to_ft_in($thisItem['height'])); ?>/<?php echo($thisItem['weight']); ?> lbs
                    | <strong>Bats/Throws:</strong> <?php echo(get_hand($thisItem['bats'])); ?>/<?php echo(get_hand($thisItem['throws'])); ?>
					<br /><strong>Age:</strong> <?php echo($thisItem['age']); ?> | <strong>Birthdate:</strong> <?php echo(date("F j, Y",strtotime($thisItem['date_of_birth']))); ?>
                    | <strong>Birthplace: </strong> <?php echo($thisItem['birthCity'].", ".$thisItem['birthRegion']." ".$thisItem['birthNation']); ?>
                    <br />
                    <strong> Drafted:</strong> <?php
					if ($thisItem['draft_team_id'] != 0) {
						echo ordinal_suffix($thisItem['draft_pick'],1)." pick in the ".ordinal_suffix($thisItem['draft_round'],1)." round of the ";
						if ($thisItem['draft_year']==0) {echo "inaugural";} else {echo $thisItem['draft_year'];}
						echo " draft by the ";
						if (!isset($teamList[$thisItem['draft_team_id']]['name'])) {
							$draftTeam = "Non or deleted Team";
						} else {
							$draftTeam = $teamList[$thisItem['draft_team_id']]['name'];
						}
						if ($thisItem['draft_year']==0) {echo $teamList[$thisItem['draft_team_id']]['name'];} else {echo $draftTeam;}
					}
					?>
                    <?php
					if (isset($awards) && sizeof($awards) > 0) { ?>
                    <br />
                    <strong>Awards:</strong>
                    <?php
					$awardsByYear = $awards['byYear'];
					$awdDrawn = false;
					$count = 0;
					foreach ($awardsByYear as $awid => $val) {
						$awCnt=explode(",",$val);
						$awCnt=count($awCnt);
						switch ($awid) {
							case 4:
							case 5:
							case 6:
							case 7:
							case 9: echo $awardName[$awid]." ($awCnt): $val"; break;
							default: break;
						} // END switch
						$count++;
						if ($count < (sizeof($awardsByYear))) { echo("; "); }
					}}
					?>
                    <br />
                    <a href="<?php echo($config['ootp_html_report_path']); ?>players/player_<?php echo($thisItem['player_id']); ?>.html">OOTP Player Page</a>
                        </div>
					</td>
          <td width="160" height="150" style="text-align: center; vertical-align:center;">
          <?php if (!empty($thisItem['team_id'])) { 
             echo anchor('/team/info/'.$thisItem['team_id'],'<img src="'.$htmlpath.'images/team_logos/'.$thisItem['logo_file_name'].'">'); 
          } 
          ?>
					</td>
					</tr>
					</table>

                <!-- BEGIN RIGHT COLUMN -->
            <div id="metaColumn">
            <?php if (isset($league_id) && !empty($league_id) && $league_id != -1) { ?>
            <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="325">
                <tr>
                  <td>
                  <b>OOTP Roster Status:</b><br />
                  </td>
                </tr>
                <tr class='title'>
                    <td>
                      <?php 
                      $icons = array();
                      $icons['warn'] = '<img src="'.PATH_IMAGES.'icons/icon_alert.png" width="20" height="20" align="absmiddle" />';
                      $icons['error'] = '<img src="'.PATH_IMAGES.'icons/icon_fail.png" width="20" height="20" align="absmiddle" />';
                      $icons['success'] = '<img src="'.PATH_IMAGES.'icons/icon_pass.png" width="20" height="20" align="absmiddle" />';
                      $icons['info'] = '<img src="'.PATH_IMAGES.'icons/icon_info.gif" width="20" height="20" align="absmiddle" />';
                      $class = "success";        
                      //echo("Player current status = ".$thisItem['player_status']."<br />");
                      switch($thisItem['player_status']) {
                          case 4:
                            $class = "info";
					                  $message = "Retired";
                            break;
                          case 3:
                            $class = "error";
					                  $message = "Currently on the DL";
                            break;
                          case 2:
                            $class = "warn";
					                  $message = "Player is in the MINORS";
                            break;
                          case 5:
                            $class = "warn";
                            $message = "Player is INACTIVE";
                            break;
                          case 1:
                          default:
                            $message = "Player is ACTIVE";
                            break;
                      }
                      echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
                      ?>
                    </td>
                </tr>
                <tr>
                	<td>
                    <div id="activeStatusBox"><div id="activeStatus"></div></div>
                    <div id="roster_status">
                    <tr class='headline'>
                        <td><?php echo($league_name); ?></td>
                    </tr>
                    <tr>
                        <td style="line-height:2.0;">
                        <b>Current Team:</b><br />
                        <?php if (!empty($current_team['avatar'])) { echo('<img src="'.PATH_TEAMS_AVATARS.$current_team['avatar'].'"width="32" align="absmiddle" height="32" /> &nbsp;'); }
						if ($current_team['id'] != -1) {
							echo(anchor('/team/info/'.$current_team['id'],$current_team['teamname']));
						} else {
							echo($current_team['teamname']);
							if (isset($useWaivers) && $useWaivers == 1 && isset($waiverStatus) && $waiverStatus != -1) {
								echo('<br /><span class="notice">Currently on waivers until period '.$waiverStatus.'</span>');
							}
						}
						if (($isCommish || $isAdmin) && isset($team_list) && sizeof($team_list) > 0) {
							echo('<div style="position:relative;width:100%;">');
							echo('<form name="updatePlayerRoster" id="updatePlayerRoster">');
							echo('<b style="float:left;">Edit Team:</b> <select name="rosterTeamList" id="rosterTeamList">');
							echo('<option value="-1">Choose Team</option>');
							foreach($team_list as $tmpid => $data) {
								echo('<option value="'.$tmpid.'"');
								if (isset($current_team) && $current_team['id'] == $tmpid) {
									echo(' selected="selected"');
								}
								echo('>'.$data['teamname'].' '.$data['teamnick'].'</option>');
							}
							echo('</select>');
							echo('<input type="button" class="button" id="'.((isset($current_team['id']))?$current_team['id']:"-1").'|'.$thisItem['id'].'|'.$thisItem['position'].'|'.$thisItem['role'].'" rel="changeTeam" name="rosterButton" value="Change" />');
							echo('</form>');
							echo('</div><br />');
						}
						if ($draftEnabled != -1 && $draftCompleted == -1 && ($draftStatus >= 1 && $draftStatus < 4)) { ?>
                        <br />
                        <b>Draft Eligibility: </b><br />
                        <span style="color:#<?php echo((($draftEligible == 1) ? '070;">Eligible' : 'c00;">Not Eligible').'</span>'); ?>
						<?php } ?>
                        </td>
                    </tr>
                    <?php if ($loggedIn) { ?>
                    <tr class='headline'>
                        <td>Actions</td>
                    </tr>
                    <tr>
                        <td align="left" style="line-height:2.0;">
                        <?php
                        $action = '';
						if ($draftEnabled != -1 && $draftCompleted == -1) {
                        	if (($draftEligible == 1 && $listEligible == 1) && ($draftStatus >= 1 && $draftStatus < 4)) {
								echo('<input type="button" class="button" id="'.$thisItem['id'].'" rel="itemPick" name="addToList" value="Add To Draft List" />');
								$action = 'addToList';
							}
							if (isset($draftEligible) && isset($user_team_id) && isset($pick_team_id) && ($draftStatus >= 1 && $draftStatus < 4)) {
								if ($draftEligible == 1 && ($pick_team_id == $user_team_id && ($draftStatus >= 2 && $draftStatus < 4)) || ($accessLevel == ACCESS_ADMINISTRATE || $isCommish)) {
									echo('<input type="button" class="button" id="'.$thisItem['id'].'" rel="draft" name="draftPlayer" value="Draft This Player" />');
									$action = 'draft';
								} // END if
							} // END if
						} else {
							if ($current_team['id'] == -1) {
								$showButton = true;
								if (isset($useWaivers) &&  $useWaivers == 1 && (isset($waiverClaims) && sizeof($waiverClaims) > 0 && in_array($userTeamId[0],$waiverClaims))) {
									$showButton = false;
									echo('<span class="notice">You have a waiver claim pending for this player.</span>');
									$action = 'claim';
								} if (sizeof($userTeamId[0]) == 0) {
                  $showButton = false;
                }
								if ($showButton) {
									echo('<input type="button" class="button" id="'.$league_id.'|'.$userTeamId[0].'|'.$thisItem['id'].'" rel="addPlayer" name="addPlayer" value="Pick up this Player" />');
									$action = 'add';
								}
							} // END if
							if ($current_team['id'] != -1 && $current_team['owner_id'] == $currUser) {
								echo('<input type="button" class="button" id="'.$league_id.'|'.$current_team['id'].'|'.$thisItem['id'].'" rel="dropPlayer" name="dropPlayer" value="Drop this Player" />');
								$action = 'drop';
								echo('<input type="button" class="button" id="'.$current_team['id'].'|'.$thisItem['id'].'_'.$thisItem['position'].'_'.$thisItem['role'].'" rel="tradePlayer" name="tradePlayer" value="Trade Player" />');
								$action = 'drop';
							} else if ($current_team['id'] != -1 && $current_team['owner_id'] != $currUser) {
								echo('<input type="button" class="button" id="'.$user_team_id.'|'.$current_team['id'].'|'.$thisItem['id'].'_'.$thisItem['position'].'_'.$thisItem['role'].'" rel="tradeForPlayer" name="tradeForPlayer" value="Trade for Player" />');
								$action = 'drop';
							} // END if
						}
                        if (empty($action)) {
                            echo("No actions are currently available at this time.");
                        } // END if
                        ?></td>
                    </tr>
                    <?php } ?>
                    </div>
                    </td>
                </tr>
				</table>
                </div>
                <div style="margin:6px 0 6px 0;min-height:12px;"><br clear="all" class="clear" /></div>

			<?php
            } ?>



            <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="325">
                <tr class='title'>
                    <td>Latest News</td>
                </tr>
                <tr class='headline'>
                    <td>News/Analysis</td>
                </tr>
                <?php if (isset($playerNews) && sizeof($playerNews) > 0) {
						$news = $playerNews[0]; ?>
                <tr>
                    <td>
                    <b><?php if (isset($news['news_subject'])) { echo($news['news_subject']); } ?></b><br />
					<?php if (isset($news['news_body'])) { echo($news['news_body']); } ?><br />
                    (Updated <?php if (isset($news['news_date'])) { echo(date('m/d/Y',strtotime($news['news_date']))); } ?>).
                    </td>
                </tr>
                <?php if (isset($news['fantasy_analysis'])) { ?>
                <tr class='headline'>
                    <td>Fantasy Impact</td>
                </tr>
                <tr>
                    <td><?php echo($news['fantasy_analysis']); ?>
                	</td>
                </tr>
                <?php 	}
				} else { ?>
                <tr>
                <td>No news was found for this player.</td></tr>
                <tr class='headline'>
                    <td>Injury Report</td>
                </tr>
                 <tr>
                    <td>
                    <?php
					if ($thisItem['injury_is_injured']) {
						$injStatus = "";
						if (isset($thisItem['injury_dtd_injury']) && $thisItem['injury_dtd_injury'] == 1) {
							$injStatus .= "Questionable - ";
						} else if (isset($thisItem['injury_career_ending']) && $thisItem['injury_career_ending'] == 1) {
							$injStatus .= 'Career Ending Injury! ';
						} else {
							$injStatus .= "Injured - ";
						}
						// GET injury name
						$injury_name = "Unknown Injury";
						if (isset($thisItem['injury_id'])) {
							$injury_name = getInjuryName($thisItem['injury_id']);
						}
						$injStatus .= $injury_name;
						if ((isset($thisItem['injury_dl_left']) && $thisItem['injury_dl_left'] > 0)) {
							$injStatus .= ", on DL - ".$thisItem['injury_dl_left']." Days Left";
						}
						if (isset($thisItem['injury_left']) && $thisItem['injury_left'] > 0 && $thisItem['injury_left'] > $thisItem['injury_dl_left']) {
							if (intval($thisItem['injury_left']) < 1000)
                $injStatus .= ", ".$thisItem['injury_left']." Total Days Left";
              else
                $injStatus .= ", Unknown Length Left";
						}
						?>
						<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/red_cross.gif" width="7" height="7" align="absmiddle"
						alt="<?php echo($injStatus); ?>" title="<?php echo($injStatus); ?>t" />&nbsp;
						<?php echo($injStatus);
					} else { ?>
                    No information available at this time (<?php echo(date('m/d/Y')); ?>).
                    <?php } ?></td>
                </tr>
                <?php if (isset($playerNews['fantasy_analysis'])) { ?>
                <tr class='headline'>
                    <td>Fantasy Analysis</td>
                </tr>
                 <tr>
                    <td><?php echo($playerNews['fantasy_analysis']); ?><br />
                    (Updated <?php if (isset($playerNews['news_subject'])) { echo(date('m/d/Y',strtotime($playerNews['news_date']))); } ?>). </td>
                </tr>
                <?php } 
                }
				        ?>
                <tr>
                    <td>
                    <div class="button_bar" style="text-align:right;">
                    <?php 
                    echo anchor('/news/articles/type_id/'.NEWS_PLAYER.'/var_id/'.$thisItem['id'], '<button class="sitebtn news">More News</button>');
                    if ($accessLevel == ACCESS_ADMINISTRATE) {
                      if (isset($playerNews) && sizeof($playerNews) > 0) { 
                        echo anchor('/news/submit/mode/edit/id/'.$news['id'], '<button class="sitebtn edit">Edit</button>');
                        echo anchor('/news/submit/mode/delete/id/'.$news['id'], '<button class="sitebtn edit">Delete</button>');
                      }
                      echo(anchor('/news/submit/mode/add/type_id/'.NEWS_PLAYER.'/var_id/'.$thisItem['id'],'<button class="sitebtn news">Add News</button>'));
                    }
                    ?>
                    </div>
                    </td>
                </tr>
                </table>
                </div>
                <div style="margin:6px 0 6px 0;min-height:12px;"><br clear="all" class="clear" /></div>

					<!-- LAST 7 GAMES -->
                <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="325">
                <tr class='title'>
                    <td>Last Seven Games</td>
                </tr>
				<tr class='headline'>
                    <table width=100% cellpadding=2 cellspacing=1>
                	<?php  if ($thisItem['position'] != 1) { ?>
					<tr class="bg4">
                        <td class="hsc2_c"><strong></strong></td>
                        <td class="hsc2_c"><strong>OPP</strong></td>
                        <td class="hsc2_c"><strong>AB</strong></td>
                        <td class="hsc2_c"><strong>R</strong></td>
                        <td class="hsc2_c"><strong>H</strong></td>
                        <td class="hsc2_c"><strong>HR</strong></td>
                        <td class="hsc2_c"><strong>RBI</strong></td>
                        <td class="hsc2_c"><strong>BB</strong></td>
                        <td class="hsc2_c"><strong>SB</strong></td>
                    </tr>
                    <?php
						if (isset($recentGames) && sizeof($recentGames) > 0) {
							$rowCount = 0;
							foreach($recentGames as $game) { ?>
                       <tr height="17" class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">

                        <td class="hsc2_c"><?php echo(date('m/d',strtotime($game['date']))); ?></td>
                        <td class="hsc2_c"><?php echo(strtoupper($game['opp'])); ?></td>
                        <td class="hsc2_c"><?php echo($game['ab']); ?></td>
                        <td class="hsc2_c"><?php echo($game['r']); ?></td>
                        <td class="hsc2_c"><?php echo($game['h']); ?></td>
                        <td class="hsc2_c"><?php echo($game['hr']); ?></td>
                        <td class="hsc2_c"><?php echo($game['rbi']); ?></td>
                        <td class="hsc2_c"><?php echo($game['bb']); ?></td>
                        <td class="hsc2_c"><?php echo($game['sb']); ?></td>

                    </tr>
					<?php 		$rowCount++;
							}
						} ?>
                    <?php
					} else {
					?>
                    <tr class="bg4">
                        <td class="hsc2_c"><strong></strong></td>
                        <td class="hsc2_c"><strong>OPP</strong></td>
                        <td class="hsc2_c"><strong>W</strong></td>
                        <td class="hsc2_c"><strong>L</strong></td>
                        <td class="hsc2_c"><strong>S</strong></td>
                        <td class="hsc2_c"><strong>IP</strong></td>
                        <td class="hsc2_c"><strong>H</strong></td>
                        <td class="hsc2_c"><strong>ERA</strong></td>
                        <td class="hsc2_c"><strong>BB</strong></td>
                        <td class="hsc2_c"><strong>K</strong></td>
                    </tr>
                    <?php
						if (isset($recentGames) && sizeof($recentGames) > 0) {
							$rowCount = 0;
							foreach($recentGames as $game) { ?>
                     <tr  height=17  class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>" style="text-align: center;" valign="middle">
                        <?php
							$ip = $game['ip'];
							$er = $game['er'];
							if ($ip==0) {
								$era=0;
						  	} else {
								$era=$er*9/$ip;
						   }
						   $era=sprintf("%.2f",$era);
						   $ip=sprintf("%.1f",$ip);
						?>
                        <td><?php echo(date('m/d',strtotime($game['date']))); ?></td>
                        <td><?php echo(strtoupper($game['opp'])); ?></td>
                        <td><?php echo($game['w']); ?></td>
                        <td><?php echo($game['l']); ?></td>
                        <td><?php echo($game['s']); ?></td>
                        <td><?php echo($ip); ?></td>
                        <td><?php echo($game['ha']); ?></td>
                        <td><?php echo($era); ?></td>
                        <td><?php echo($game['bb']); ?></td>
                        <td><?php echo($game['k']); ?></td>
                    </tr>
                        <?php $rowCount++;
							}
						}
					}?>
                    </table>
                	</td>
                </tr>
                </table>
                </div>

                <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="325">
                <tr class='title'>
                    <td>Upcoming Schedule</td>
                </tr>
                <tr class='headline'>
                    <table width="100%" cellpadding="2" cellspacing="1">
					          <tr class="bg4">
                        <td class="hsc2_c"><strong>Date</strong></td>
                        <td class="hsc2_c"><strong>Time</strong></td>
                        <td class="hsc2_c"><strong>OPP</strong></td>
                    </tr>
                    <?php
					$games = $upcomingGames['players_active'][$thisItem['player_id']];
          if (isset($games) && sizeof($games) > 0) {
						$drawn = 0;
						//$limit = $config['sim_length'];
						//$lastDate = "";
						$rowCount = 0;
						$dateStr = '';
            $startStr = '';
            $gameDate = EMPTY_DATE_STR;
            $iconStr = $config['ootp_html_report_path'].'images/dot1.gif';
            foreach ($games as $game_id => $game_data) {
                if ($gameDate != strtotime($game_data['game_date']." 00:00:00")) {
                  if ($gameDate != EMPTY_DATE_STR) {
                    echo($dateStr.$startStr."</td>\n</tr>\n");
                    $drawn++;
                    $rowCount++;
                  } // END if
                  $dateStr = '<tr height="17" class="'.(($rowCount % 2) == 0 ? "s1_l" : "s2_l").'"><td class="hsc2_c">';
                  $startStr = '';
                  $gameDate = strtotime($game_data['game_date']." 00:00:00");
                  $dateStr .= date('m/d',$gameDate).'</td>';
                  $dateStr .= '<td class="hsc2_c">';
                  if (isset($game_data['game_time'])) { 
                    $dateStr .= date('h:m A',strtotime($game_data['game_time'])); 
                  } else { 
                    $dateStr .= ' - - '; 
                  } // END if
                  $dateStr .= '</td><td class="hsc2_l">';
                  if ($game_id > 0) {
                    if ($thisItem['team_id'] == $game_data['home_team']) {
                      if (isset($teamList[$game_data['away_team']])) {
                        $dateStr .= strtoupper($teamList[$game_data['away_team']]['abbr']);
                      } // END if 
                    } else {
                      if (isset($teamList[$game_data['home_team']])) {
                        $dateStr .= "@".strtoupper($teamList[$game_data['home_team']]['abbr']);
                      } // END if 
                    } // END if ($playerData['team_id'] == $game_data['home_team']) 
                    if ($game_data['start'] != -1)
                      $startStr = '&nbsp;<img src="'.$iconStr.'" />';  // END if
                  } // END if ($game_id > 0)
                } else {
                  $dateStr .= "(2)";
                }  // END if ($gameDate != strtotime($game_data['game_date']." 00:00:00"))
            } // END foreach ($games as $game_id => $game_data)
            echo($dateStr.$startStr."</td>\n</tr>\n");
					} else {
						for ($i = 0; $i < $config['sim_length']; $i++) {
              echo('<tr height="17" class="bg2"><td class="hsc2_c"></td></tr>\n');
            }
          }  // END if (isset($games) && sizeof($games) > 0)
          ?>
                </table>
                	</td>
                </tr>
                </table>
                </div>
            </div>

                <!-- BEGIN MAIN COLUMN -->
            <div id="detailColumn">

                	<!-- OWN/START BOX -->
                <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="575">
                <tr class='title'>
                    <td>Owned/Started</td>
                </tr>
				<tr class='headline'>
					<td>
                    <table width=100% cellpadding=2 cellspacing=1>
                    <tr class="headline">
                        <td><strong>Start This Week</strong></td>
                        <td><strong>Change</strong></td>
                        <td><strong>Own This Week</strong></td>
                        <td><strong>Change</strong></td>
                    </tr>
                    <tr  height=17 class="bg2" style="font-size:12px; font-weight:bold; text-align: center" valign="middle">
                        <td><?php echo($thisItem['start']); ?></td>
                        <td><span style="color:#<?php
						$change = 0;
						$color = "000";
						$mark = "";
						if ($thisItem['start'] > $thisItem['start_last']) {
							$change = ((100-$thisItem['start_last'])-(100-$thisItem['start']));
						} else if ($thisItem['start'] < $thisItem['start_last']) {
							$change = ((100-$thisItem['start'])-(100-$thisItem['start_last']));
						}
						if ($change >=1) { $color = "060"; $mark = "+";  }
						else if ($change < 0) { $color = "C00";  }
						echo($color.'">'.$mark.intval($change)); ?></span></td>
                        <td><?php echo($thisItem['own']); ?></td>
                        <td><span style="color:#<?php
						$change = 0;
						$color = "000";
						$mark = "";
						if ($thisItem['own'] > $thisItem['own_last']) {
							$change = ((100-$thisItem['own_last'])-(100-$thisItem['own']));
						} else if ($thisItem['own'] < $thisItem['own_last']) {
							$change = ((100-$thisItem['own'])-(100-$thisItem['own_last']));
						}
						if ($change >= 1) { $color = "060"; $mark = "+";  }
						else if ($change < 0) { $color = "C00";  }
						echo($color.'">'.$mark.intval($change)); ?></span></td>
                    </tr>
                    </table>
                	</td>
                </tr>
                </table>
                </div>

                <?php
				if (!isset($scoring_type) || (isset($scoring_type) && $scoring_type == LEAGUE_SCORING_HEADTOHEAD)) {
				?>
                <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="575">
                <tr class='title'>
                    <td>Fantasy Trend</td>
                </tr>
                <tr class='headline'>
                    <td>Fantasy Points</td>
                </tr>
                <tr>
					<td>
                    <?php if (isset($scoringPeriods) && $scoringPeriods != 0 &&
						isset($playerPoints) && sizeof($playerPoints) > 0) {
						?>

                    	<div id="playerbars" height="100%" class="bg2" style="position:relative; width:100%; border-left:0px; border-right:0px; border-bottom:0px;">
                		<div class='fc' style='padding: 0px; border-top: 0px'>
                        <table width=96% cellpadding=0 cellspacing=0 class="player_points">
                        <tr>
                        <?php
						for($i = 1; $i < $scoringPeriods; $i++) {
							$height = 0;
							if ($playerPoints[$i] != 0 && $pointsMax != 0 && $playerPoints[$i] < $pointsMax) {
								$height = intval(($playerPoints[$i] / $pointsMax)*100);
							} else if ($playerPoints[$i] != 0 && $playerPoints[$i] >= $pointsMax) {
								$height = 100;
							} ?>
                            <td style="text-align:center; vertical-align:bottom;"><?php echo($playerPoints[$i]); ?><br /><img src="<?php echo($config['fantasy_web_root']); ?>images/dot_red.gif" width="12" height="<?php echo($height); ?>"></td>
                            <td style="text-align:center; vertical-align:bottom;"><img src="<?php echo($config['fantasy_web_root']); ?>images/dot_clear.gif" width="2" height="1"></td>
                        <?php } ?>
                        </tr>
                        <tr >
                        <?php
						for($i = 1; $i < $scoringPeriods; $i++) {
							$height = ($playerPoints[$i] < $pointsMax) ? intval($playerPoints[$i] / $pointsMax) : 100; ?>
                        	  <td style="color:black; text-align:center;"><?php echo($i); ?></td>
                            <td></td>
                        <?php } ?>
                        </tr>
                        </table>
                        <table width=96% cellspacing=1 cellpadding=2>
                        <tr>
                        <td colspan="52" style="background-color:#E8ECEE, text-align:center;">Fantasy Points by Week. &nbsp;
                        (<img src="<?php echo($config['fantasy_web_root']); ?>images/dot_red.gif" width="10" height="10"> Actual)
                        &nbsp;&nbsp;&nbsp;Note: Points are rounded down.<br></td>
                        </tr>
                        </table>
                        </div>
                    <?php } ?>
                    </td>
                </tr>
                </table>
                </div>
                <?php } ?>

                	<!-- CURRETN SEASON STATS -->
                <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="575">
                <tr class='title'>
                    <td><?php if (isset($statYear)) { echo($statYear); } else { echo(data('Y',time())); } ?> Stats</td>
                </tr>
				<tr class='headline'>
                <?php
				$rowsDrawn = 0; ?>
					<td>
                    <table width="100%" cellpadding=2 cellspacing=1>
                    <tr class="bg4">
                    <?php  if ($thisItem['position'] != 1) {
						$ab = isset($playerStats['ab']) ? $playerStats['ab'] : 0;
						$h = isset($playerStats['h']) ? $playerStats['h'] : 0;
						$bb = isset($playerStats['bb']) ? $playerStats['bb'] : 0;
						$k = isset($playerStats['k']) ? $playerStats['k'] : 0;
						$d = isset($playerStats['d']) ? $playerStats['d'] : 0;
						$t = isset($playerStats['t']) ? $playerStats['t'] : 0;
						$hr = isset($playerStats['hr']) ? $playerStats['hr'] : 0;
						$r = isset($playerStats['r']) ? $playerStats['r'] : 0;
						$rbi = isset($playerStats['rbi']) ? $playerStats['rbi'] : 0;
						$sb = isset($playerStats['sb']) ? $playerStats['sb'] : 0;
						if ($ab==0) {
							$avg=0;
							$wiff = 0;
						} else {
						  $avg=$h/$ab;
						  $wiff = intval(($k/$ab)*100);
						}
						if ($bb == 0 ) {
							$walk = 0;
						} else {
							$walk = intval(($bb/($ab+$bb))*100);
						}
					   if ($avg<1) {$avg=strstr(sprintf("%.3f",$avg),".");}
						else {$avg=sprintf("%.3f",$avg);}


						$xbh = ($d+$t+$hr);
						if ($walk > 20) {
						   $walk = '<span style="color:#060">'.$walk.'</span>';
					   } else if ($walk >= 10 && $walk <= 20) {
						   $walk = '<span style="color:#F60">'.$walk.'</span>';
					   } else {
						   $walk = '<span style="color:#C00">'.$walk.'</span>';
					   }
					   if ($wiff < 10) {
						   $wiff = '<span style="color:#060">'.$wiff.'</span>';
					   } else if ($wiff >= 10 && $wiff <= 25) {
						   $wiff = '<span style="color:#f30">'.$wiff.'</span>';
					   } else {
						   $wiff = '<span style="color:#C00">'.$wiff.'</span>';
					   }
					   if (!$rowsDrawn) $rowsDrawn = true;
						?>
                        <td class="hsc2_c" width="9%"><b>BA</b></td>
                        <td class="hsc2_c" width="9%"><b>AB</b></td>
                        <td class="hsc2_c" width="9%"><b>R</b></td>
                        <td class="hsc2_c" width="9%"><b>HR</b></td>
                        <td class="hsc2_c" width="9%"><b>RBI</b></td>
                        <td class="hsc2_c" width="9%"><b>BB</b></td>
                        <td class="hsc2_c" width="9%"><b>KO</b></td>
                        <td class="hsc2_c" width="9%"><b>SB</b></td>
                        <td class="hsc2_c" width="9%"><b>WIFF%</b></td>
                        <td class="hsc2_c" width="9%"><b>WALK%</b></td>
                        <td class="hsc2_c" width="9%"><b>XBH</b></td>
                    </tr>
                    <tr  height="17" class="bg2">
                    	  <td class="hsc2_c" style="vertical-align:middle;"><?php echo($avg); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($ab); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($r); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($hr); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($rbi); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($bb); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($k); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($sb); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($wiff); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($walk); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($xbh); ?></td>
                    </tr>
                    <?php } else {
							$w = isset($playerStats['w']) ? $playerStats['w'] : 0;
							$l = isset($playerStats['l']) ? $playerStats['l'] : 0;
							$s = isset($playerStats['s']) ? $playerStats['s'] : 0;
							$ip = isset($playerStats['ip']) ? $playerStats['ip'] : 0;
							$er = isset($playerStats['er']) ? $playerStats['er'] : 0;
							$bb = isset($playerStats['bb']) ? $playerStats['bb'] : 0;
							$ha = isset($playerStats['ha']) ? $playerStats['ha'] : 0;
							$k =  isset($playerStats['k']) ? $playerStats['k'] : 0;
							$hra = isset($playerStats['hra']) ? $playerStats['hra'] : 0;
							if ($ip==0) {
							$era=0;$whip=0;$k9 = 0;$bb9 = 0;$hr9 = 0;
						  } else {
							  $era=$er*9/$ip;
							  $whip=($ha+$bb)/$ip;
							  $k9 = ($k*9)/$ip;
							  $bb9 = ($bb*9)/$ip;
							  $hr9 = ($hra*9)/$ip;
						   }
						   $era=sprintf("%.2f",$era);
						   $ip=sprintf("%.1f",$ip);
						   if ($ip<1) {$ip=strstr($ip,".");}
						   $whip=sprintf("%.2f",$whip);


						   $k9=sprintf("%.2f",$k9);
						   if ($k9 > 6) {
							   $k9 = '<span style="color:#060">'.$k9.'</span>';
						   } else if ($k9 >= 4 && $k9 <= 6) {
							   $k9 = '<span style="color:#F60">'.$k9.'</span>';
						   } else {
							   $k9 = '<span style="color:#C00">'.$k9.'</span>';
						   }

						   $bb9=sprintf("%.2f",$bb9);
						   if ($bb9 < 3) {
							   $bb9 = '<span style="color:#060">'.$bb9.'</span>';
						   } else if ($bb9 > 3 && $bb9 <= 5) {
							   $bb9 = '<span style="color:#f30">'.$bb9.'</span>';
						   } else {
							   $bb9 = '<span style="color:#C00">'.$bb9.'</span>';
						   }

						   $hr9=sprintf("%.2f",$hr9);
						   if ($hr9 <= 1) {
							   $hr9 = '<span style="color:#060">'.$hr9.'</span>';
						   } else if ($hr9 > 1 && $hr9 <= 2) {
							   $hr9 = '<span style="color:#F60">'.$hr9.'</span>';
						   } else {
							   $hr9 = '<span style="color:#C00">'.$hr9.'</span>';
						   }
						   if (!$rowsDrawn) $rowsDrawn = true;
						?>
                        <td class="hsc2_c" width="9%"><b>W</b></td>
                        <td class="hsc2_c" width="9%"><b>L</b></td>
                        <td class="hsc2_c" width="9%"><b>ERA</b></td>
                        <td class="hsc2_c" width="9%"><b>S</b></td>
                        <td class="hsc2_c" width="9%"><b>INN</b></td>
                        <td class="hsc2_c" width="9%"><b>K</b></td>
                        <td class="hsc2_c" width="9%"><b>BB</b></td>
                        <td class="hsc2_c" width="9%"><b>WHIP</b></td>
                        <td class="hsc2_c" width="9%"><b>K/9</b></td>
                        <td class="hsc2_c" width="9%"><b>BB/9</b></td>
                        <td class="hsc2_c" width="9%"><b>HR/9</b></td>
                    </tr>
                    <tr  height=17  class="bg2">
                    	<td class="hsc2_c" style="vertical-align:middle;"><?php echo($w); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($l); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($era); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($s); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($ip); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($k); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($bb); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($whip); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($k9); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($bb9); ?></td>
                        <td class="hsc2_c" style="vertical-align:middle;"><?php echo($hr9); ?></td>
                    </tr>
                    <?php } ?>
                    </tr>
                   <?php
					if (!$rowsDrawn) { ?>
                    <tr  height=17  class="bg2" valign="middle">
                    	<td colspan="8" class="hsc2_c">No Stats Were Found</td>
                    </tr>
                     <?php } ?>
                     </table>
                	</td>
                </tr>
                </table>
                </div>

                	<!-- CURRETN ELIDIBILITY -->
                <div class='textbox'>
                <table cellpadding="2" cellspacing="0" width="575">
                <tr class='title'>
                    <td>Eligibility</td>
                </tr>
				<tr class='headline'>
					<td>
                    <?php $pos = unserialize($thisItem['positions']); ?>
					<table width=100% cellpadding=2 cellspacing=1>
                	<!--tr  height=17  class="bg2" align="center" valign="middle">
                        <td colspan="10">
                        <?php
						$pos_list = "";
						if (is_array($pos) && sizeof($pos) > 0) {
							foreach($pos as $position) {
								if ($pos_list != "") { $pos_list.= ", "; }
								$pos_list .= get_pos($position);
							}
						} echo ($pos_list);?> </td>
                    </tr-->
                    <tr class="headline"><td><strong>Pos.</strong></td>
                        <td class="hsc2_c"><strong>C</strong></td>
                        <td class="hsc2_c"><strong>1B</strong></td>
                        <td class="hsc2_c"><strong>2B</strong></td>
                        <td class="hsc2_c"><strong>3B</strong></td>
                        <td class="hsc2_c"><strong>SS</strong></td>
                        <td class="hsc2_c"><strong>OF</strong></td>
                        <td class="hsc2_c"><strong>U</strong></td>
                        <td class="hsc2_c"><strong>SP</strong></td>
                        <td class="hsc2_c"><strong>RP</strong></td>
                    </tr>
                    <tr  height=17  class="bg2" valign="middle">
                        <td>Eligible At:</td>
                        <?php
						$posToTest = array(2,3,4,5,6,20,25,11,12);
						foreach($posToTest as $testPos) { ?>
                        	<td>
							<?php if (in_array($testPos,$pos)) { ?><b>X</b><?php } ?>
                            </td>
                        <?php } ?>
                    </tr>

                    </table>
                	</td>
                </tr>
                </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div class='textbox'>
<table cellpadding="2" cellspacing="0">
<tr class='title'><td>Career Stats</td></tr>
<?php
$poy = array();
$boy = array();
$roy = array();
$gg = array();
$as = array();
if (isset($awards) && sizeof($awards) > 0) {
	$poy = $awards['poy'];
	$boy = $awards['boy'];
	$roy = $awards['roy'];
	$gg = $awards['gg'];
	$as = $awards['as'];
}
if (isset($careerStats) && sizeof($careerStats) > 0) {
	$expTable="";
	$noWS = 1;
	$noPSM = 1;

	if ($thisItem['position'] != 1) {
	$Tg=0;
	$Tab=0;
	$Th=0;
	$Td=0;
	$Tt = 0;
	$Thr=0;
	$Trbi=0;
	$Tsb=0;
	$Tsh=0;
	$Tcs=0;
	$Thp=0;
	$Tpa=0;
	$TpiPA=0;
	$Tibb=0;
	$Tgdp=0;
	$Tr=0;
	$Tbb=0;
	$Tsf=0;
	$Tk=0;
	$Twar=0;
	$Tpi=0;
   $playedFor = array();
   $teamStats = array();
   $yearStats = array();
   echo "  <tr><td>\n";
   echo "   <div class='tablebox'>\n";
   echo "    <table cellpadding=0 cellspacing=0><tr class='title'><td colspan=22>Batting Stats</td></tr><tr><td>\n";
   echo "    <table cellpadding=2 cellspacing=0 class='sortable' width='910px'>\n";
   echo "     <thead><tr class='headline'>";
   echo "<td class='hsc2_l'>Year/Team</td>";
   echo "<td class='hsc2'>Age</td>";
   echo "<td class='hsc2'>G</td>";
   echo "<td class='hsc2'>AB</td>";
   echo "<td class='hsc2'>H</td>";
   echo "<td class='hsc2'>2B</td>";
   echo "<td class='hsc2'>3B</td>";
   echo "<td class='hsc2'>HR</td>";
   echo "<td class='hsc2'>RBI</td>";
   echo "<td class='hsc2'>R</td>";
   echo "<td class='hsc2'>BB</td>";
   echo "<td class='hsc2'>HP</td>";
   echo "<td class='hsc2'>SH</td>";
   echo "<td class='hsc2'>SF</td>";
   echo "<td class='hsc2'>K</td>";
   echo "<td class='hsc2'>SB</td>";
   echo "<td class='hsc2'>CS</td>";
   echo "<td class='hsc2'>AVG</td>";
   echo "<td class='hsc2'>OBP</td>";
   echo "<td class='hsc2'>SLG</td>";
   echo "<td class='hsc2'>OPS</td>";
   echo "<td class='hsc2'>WAR</td>";
   echo "<td class='hsc2'>wOBA</td>";
  echo "</tr></thead>\n";
   $prevYr=-1;
   $cnt=0;
   foreach($careerStats as $row) {
      $yr=$row['year'];
      $tid=$row['team_id'];
      $tabbr = ((isset($teams[$tid][$yr])) ? $teams[$tid][$yr] : "");
      if ($tabbr=="" && isset($teams[$tid][$year])) {$tabbr=$teams[$tid][$year];}
      $g=$row['g'];
      $ab=$row['ab'];
      $h=$row['h'];
      $d=$row['d'];
      $t=$row['t'];
      $hr=$row['hr'];
      $rbi=$row['rbi'];
      $r=$row['r'];
      $bb=$row['bb'];
      $hp=$row['hp'];
      $sh=$row['sh'];
      $sf=$row['sf'];
      $k=$row['k'];
      $sb=$row['sb'];
      $cs=$row['cs'];
      $pa=$row['pa'];
      $war=$row['war'];
      #Expanded
      $pi=$row['pitches_seen'];
      $ibb=$row['ibb'];
      $gdp=$row['gdp'];

	  if (!isset($teamStats[$tid])) $teamStats[$tid] = array();

      $playedFor[$tid]=1;
      $teamStats[$tid]['g']= (isset($teamStats[$tid]['g'])) ? $teamStats[$tid]['g']+$g : $g;
      $teamStats[$tid]['ab']=(isset($teamStats[$tid]['ab'])) ? $teamStats[$tid]['ab']+$ab : $ab;
      $teamStats[$tid]['h']=(isset($teamStats[$tid]['h'])) ? $teamStats[$tid]['h']+$h : $h;
      $teamStats[$tid]['d']=(isset($teamStats[$tid]['d'])) ? $teamStats[$tid]['d']+$d : $d;
      $teamStats[$tid]['t']=(isset($teamStats[$tid]['t'])) ? $teamStats[$tid]['t']+$t : $t;
      $teamStats[$tid]['hr']=(isset($teamStats[$tid]['hr'])) ? $teamStats[$tid]['hr']+$hr : $hr;
      $teamStats[$tid]['rbi']=(isset($teamStats[$tid]['rbi'])) ? $teamStats[$tid]['rbi']+$rbi : $rbi;
      $teamStats[$tid]['r']=(isset($teamStats[$tid]['r'])) ? $teamStats[$tid]['r']+$r : $r;
      $teamStats[$tid]['bb']=(isset($teamStats[$tid]['bb'])) ? $teamStats[$tid]['bb']+$bb : $bb;
      $teamStats[$tid]['hp']=(isset($teamStats[$tid]['hp'])) ? $teamStats[$tid]['hp']+$hp : $hp;
      $teamStats[$tid]['sh']=(isset($teamStats[$tid]['sh'])) ? $teamStats[$tid]['sh']+$sh : $sh;
      $teamStats[$tid]['sf']=(isset($teamStats[$tid]['sf'])) ? $teamStats[$tid]['sf']+$sf : $sf;
      $teamStats[$tid]['k']=(isset($teamStats[$tid]['k'])) ? $teamStats[$tid]['k']+$k : $k;
      $teamStats[$tid]['sb']=(isset($teamStats[$tid]['sb'])) ? $teamStats[$tid]['sb']+$sb : $sb;
      $teamStats[$tid]['cs']=(isset($teamStats[$tid]['cs'])) ? $teamStats[$tid]['cs']+$cs : $cs;
      $teamStats[$tid]['pa']=(isset($teamStats[$tid]['pa'])) ? $teamStats[$tid]['pa']+$pa : $pa;
      $teamStats[$tid]['war']=(isset($teamStats[$tid]['war'])) ? $teamStats[$tid]['war']+$war : $war;
      #Expanded
      $teamStats[$tid]['pi']=(isset($teamStats[$tid]['pi'])) ? $teamStats[$tid]['pi']+$pi : $pi;
      $teamStats[$tid]['ibb']=(isset($teamStats[$tid]['ibb'])) ? $teamStats[$tid]['ibb']+$ibb : $ibb;
      $teamStats[$tid]['gdp']=(isset($teamStats[$tid]['gdp'])) ? $teamStats[$tid]['gdp']+$gdp : $gdp;

      $yrDate=strtotime($yr."-7-1");
      $age=floor(($yrDate-strtotime($thisItem['date_of_birth']))/31536000);

      $yearStats[$yr]['cnt']= (isset($yearStats[$tid]['cnt'])) ? $yearStats[$yr]['cnt']+1 : 1;
      $yearStats[$yr]['age']=$age;
      $yearStats[$yr]['g']= (isset($yearStats[$tid]['g'])) ? $yearStats[$yr]['g']+$g : $g;
      $yearStats[$yr]['ab']= (isset($yearStats[$tid]['ab'])) ? $yearStats[$yr]['ab']+$ab : $ab;
      $yearStats[$yr]['h']= (isset($yearStats[$tid]['h'])) ? $yearStats[$yr]['h']+$h : $h;
      $yearStats[$yr]['d']= (isset($yearStats[$tid]['d'])) ? $yearStats[$yr]['d']+$d : $d;
      $yearStats[$yr]['t']= (isset($yearStats[$tid]['t'])) ? $yearStats[$yr]['t']+$t : $t;
      $yearStats[$yr]['hr']= (isset($yearStats[$tid]['hr'])) ? $yearStats[$yr]['hr']+$hr : $hr;
      $yearStats[$yr]['rbi']= (isset($yearStats[$tid]['rbi'])) ? $yearStats[$yr]['rbi']+$rbi : $rbi;
      $yearStats[$yr]['r']= (isset($yearStats[$tid]['r'])) ? $yearStats[$yr]['r']+$r : $r;
      $yearStats[$yr]['bb']= (isset($yearStats[$tid]['bb'])) ? $yearStats[$yr]['bb']+$bb : $bb;
      $yearStats[$yr]['hp']= (isset($yearStats[$tid]['hp'])) ? $yearStats[$yr]['hp']+$hp : $hp;
      $yearStats[$yr]['sh']= (isset($yearStats[$tid]['sh'])) ? $yearStats[$yr]['sh']+$sh : $sh;
      $yearStats[$yr]['sf']= (isset($yearStats[$tid]['sf'])) ? $yearStats[$yr]['sf']+$sf : $sf;
      $yearStats[$yr]['k']= (isset($yearStats[$tid]['k'])) ? $yearStats[$yr]['k']+$k : $k;
      $yearStats[$yr]['sb']= (isset($yearStats[$tid]['sb'])) ? $yearStats[$yr]['sb']+$sb : $sb;
      $yearStats[$yr]['cs']= (isset($yearStats[$tid]['cs'])) ? $yearStats[$yr]['cs']+$cs : $cs;
      $yearStats[$yr]['pa']= (isset($yearStats[$tid]['pa'])) ? $yearStats[$yr]['pa']+$pa : $pa;
      $yearStats[$yr]['war']= (isset($yearStats[$tid]['war'])) ? $yearStats[$yr]['war']+war : $war;
      #Expanded
      $yearStats[$yr]['pi']= (isset($yearStats[$tid]['pi'])) ? $yearStats[$yr]['pi']+$pi : $pi;
      $yearStats[$yr]['ibb']= (isset($yearStats[$tid]['ibb'])) ? $yearStats[$yr]['ibb']+$ibb : $ibb;
      $yearStats[$yr]['gdp']= (isset($yearStats[$tid]['gdp'])) ? $yearStats[$yr]['gdp']+$gdp : $gdp;

      $Tg+=$g;
      $Tab+=$ab;
      $Th+=$h;
      $Td+=$d;
      $Tt+=$t;
      $Thr+=$hr;
      $Trbi+=$rbi;
      $Tr+=$r;
      $Tbb+=$bb;
      $Thp+=$hp;
      $Tsh+=$sh;
      $Tsf+=$sf;
      $Tk+=$k;
      $Tsb+=$sb;
      $Tcs+=$cs;
      $Tpa+=$pa;
      $Twar+=$war;
      #Expanded
      $Tpi+=$pi;
      if ($pi>0) {$TpiPA+=$pa;}
      $Tibb+=$ibb;
      $Tgdp+=$gdp;

      if ($ab==0) {
		  $avg=0;$slg=0;
	  } else {
         $avg=$h/$ab;
         $slg=($h+$d+2*$t+3*$hr)/$ab;
      }
      if (($ab+$bb+$hp+$sf)==0) {$obp=0;}
      else {$obp=($h+$bb+$hp)/($ab+$bb+$hp+$sf);}

	  if ($pa==0) {$wOBA=0;} else {$wOBA=(0.72*$bb+0.75*$hp+0.9*($h-$d-$t-$hr)+0.92*0+1.24*$d+1.56*$t+1.95*$hr)/$pa;}

      $ops=$obp+$slg;
      $avg=sprintf("%.3f",$avg);
      $obp=sprintf("%.3f",$obp);
      $slg=sprintf("%.3f",$slg);
      $ops=sprintf("%.3f",$ops);
      $wOBA=sprintf("%.3f",$wOBA);
      if ($avg<1) {$avg=strstr($avg,".");}
      if ($obp<1) {$obp=strstr($obp,".");}
      if ($slg<1) {$slg=strstr($slg,".");}
      if ($ops<1) {$ops=strstr($ops,".");}
      if ($wOBA<1) {$wOBA=strstr($wOBA,".");}
      $war=sprintf("%.1f",$war);
      #Expanded
      if ($pa==0) {$piPerPA=0;} else {$piPerPA=$pi/$pa;}
      if ($hr==0) {$paPerHR=0;} else {$paPerHR=$pa/$hr;}
      $outs=$ab-$h+$cs+$sh+$sf;
      $tb=$h+$d+2*$t+3*$hr;
      $ebh=$d+$t+$hr;
      if ($sb+$cs==0) {$sbPct=0;} else {$sbPct=$sb/($sb+$cs);}
      if (($ab-$k-$hr+$sf)==0) {$babip=0;} else {$babip=($h-$hr)/($ab-$k-$hr+$sf);}
      if ($ab==0) {$iso=0;} else {$iso=($tb-$h)/$ab;}
      if ($hr+$sb==0) {$pwrSpd=0;} else {$pwrSpd=(2*$hr*$sb)/($hr+$sb);}
      if (($ab+$bb+$hp+$sh+$sf)==0) {$rc=0;$rc27=0;}
       else
       {
         $rc=(($h+$bb-$cs+$hp-$gdp)*($tb+(.26*($bb-$ibb+$hp))+(.52*($sh+$sf+$sb))))/($ab+$bb+$hp+$sh+$sf);
         if ($outs != 0) $rc27=27*$rc/$outs; else $rc27 = 0;
       }
      if ($k==0) {$bbPerK=0;} else {$bbPerK=$bb/$k;}
      $piPerPA=sprintf("%.2f",$piPerPA);
      $paPerHR=sprintf("%.1f",$paPerHR);
      $sbPct=sprintf("%.1f",100*$sbPct);
      $babip=sprintf("%.3f",$babip);
      $iso=sprintf("%.3f",$iso);
      $pwrSpd=sprintf("%.1f",$pwrSpd);
      $rc=sprintf("%.0f",$rc);
      $rc27=sprintf("%.1f",$rc27);
      $bbPerK=sprintf("%.2f",$bbPerK);
      if ($babip<1) {$babip=strstr($babip,".");}
      if ($iso<1) {$iso=strstr($iso,".");}

      if (($prevYr!=$yr)&&(isset($yearStats[$prevYr]) && $yearStats[$prevYr]['cnt']>1))
       {
         if ((isset($roy[$prevYr])) || (isset($boy[$prevYr]))) {$cls='b'.($cnt%2+1);}
          else {$cls='s'.($cnt%2+1);}

         $Yab=$yearStats[$prevYr]['ab'];
 	 $Yh=$yearStats[$prevYr]['h'];
	 $Yd=$yearStats[$prevYr]['d'];
 	 $Yt=$yearStats[$prevYr]['t'];
	 $Yhr=$yearStats[$prevYr]['hr'];
 	 $Ypa=$yearStats[$prevYr]['pa'];
	 $Ybb=$yearStats[$prevYr]['bb'];
 	 $Yhp=$yearStats[$prevYr]['hp'];
	 $Ysh=$yearStats[$prevYr]['sh'];
	 $Ysf=$yearStats[$prevYr]['sf'];
	 $Ysb=$yearStats[$prevYr]['sb'];
	 $Ycs=$yearStats[$prevYr]['cs'];
	 $Yk=$yearStats[$prevYr]['k'];
	 #Expanded
	 $Ypi=$yearStats[$prevYr]['pi'];
	 $Yibb=$yearStats[$prevYr]['ibb'];
	 $Ygdp=$yearStats[$prevYr]['gdp'];

 	 if ($Yab==0) {$Yavg=0;$Yslg=0;}
          else
          {
            $Yavg=$Yh/$Yab;
            $Yslg=($Yh+$Yd+2*$Yt+3*$Yhr)/$Yab;
          }
         if (($Yab+$Ybb+$Yhp+$Ysf)==0) {$Yobp=0;}
          else {$Yobp=($Yh+$Ybb+$Yhp)/($Yab+$Ybb+$Yhp+$Ysf);}
         if ($Ypa==0) {$YwOBA=0;} else {$YwOBA=(0.72*$Ybb+0.75*$Yhp+0.9*($Yh-$Yd-$Yt-$Yhr)+0.92*0+1.24*$Yd+1.56*$Yt+1.95*$Yhr)/$Ypa;}
         #Expanded
         if ($Ypa==0) {$YpiPerPA=0;} else {$YpiPerPA=$Ypi/$Ypa;}
         if ($Yhr==0) {$YpaPerHR=0;} else {$YpaPerHR=$Ypa/$Yhr;}
         $Youts=$Yab-$Yh+$Ycs+$Ysh+$Ysf;
         $Ytb=$Yh+$Yd+2*$Yt+3*$Yhr;
         $Yebh=$Yd+$Yt+$Yhr;
         if ($Ysb+$Ycs==0) {$YsbPct=0;} else {$YsbPct=$Ysb/($Ysb+$Ycs);}
         if (($Yab-$Yk-$Yhr+$Ysf)==0) {$Ybabip=0;} else {$Ybabip=($Yh-$Yhr)/($Yab-$Yk-$Yhr+$Ysf);}
         if ($Yab==0) {$Yiso=0;} else {$Yiso=($Ytb-$Yh)/$Yab;}
         if ($Yhr+$Ysb==0) {$YpwrSpd=0;} else {$YpwrSpd=(2*$Yhr*$Ysb)/($Yhr+$Ysb);}
         if (($Yab+$Ybb+$Yhp+$Ysh+$Ysf)==0) {$Yrc=0;$Yrc27=0;}
          else
          {
            $Yrc=(($Yh+$Ybb-$Ycs+$Yhp-$Ygdp)*($Ytb+(.26*($Ybb-$Yibb+$Yhp))+(.52*($Ysh+$Ysf+$Ysb))))/($Yab+$Ybb+$Yhp+$Ysh+$Ysf);
	    $Yrc27=27*$Yrc/$Youts;
   	  }
	 if ($Yk==0) {$YbbPerK=0;} else {$YbbPerK=$Ybb/$Yk;}
	 $YpiPerPA=sprintf("%.2f",$YpiPerPA);
   	 $YpaPerHR=sprintf("%.1f",$YpaPerHR);
	 $YsbPct=sprintf("%.1f",100*$YsbPct);
	 $Ybabip=sprintf("%.3f",$Ybabip);
    	 $Yiso=sprintf("%.3f",$Yiso);
	 $YpwrSpd=sprintf("%.1f",$YpwrSpd);
   	 $Yrc=sprintf("%.0f",$Yrc);
	 $Yrc27=sprintf("%.1f",$Yrc27);
   	 $YbbPerK=sprintf("%.2f",$YbbPerK);
	 if ($Ybabip<1) {$Ybabip=strstr($Ybabip,".");}
   	 if ($Yiso<1) {$Yiso=strstr($Yiso,".");}

         $Yops=$Yobp+$Yslg;
         $Yavg=sprintf("%.3f",$Yavg);
	 $Yobp=sprintf("%.3f",$Yobp);
	 $Yslg=sprintf("%.3f",$Yslg);
	 $Yops=sprintf("%.3f",$Yops);
	 $YwOBA=sprintf("%.3f",$YwOBA);
	 if ($Yavg<1) {$Yavg=strstr($Yavg,".");}
	 if ($Yobp<1) {$Yobp=strstr($Yobp,".");}
	 if ($Yslg<1) {$Yslg=strstr($Yslg,".");}
	 if ($Yops<1) {$Yops=strstr($Yops,".");}
	 if ($YwOBA<1) {$YwOBA=strstr($YwOBA,".");}

         echo "     <tr class='$cls'>";
	 echo "<td class='".$cls."_l'>$prevYr - Tot";
	 if (isset($as[$yr])) {echo " (AS)";}
         echo "</td>";
         echo "<td>".$yearStats[$prevYr]['age']."</td>";
         echo "<td>".$yearStats[$prevYr]['g']."</td>";
         echo "<td>".$yearStats[$prevYr]['ab']."</td>";
         echo "<td>".$yearStats[$prevYr]['h']."</td>";
         echo "<td>".$yearStats[$prevYr]['d']."</td>";
         echo "<td>".$yearStats[$prevYr]['t']."</td>";
         echo "<td>".$yearStats[$prevYr]['hr']."</td>";
         echo "<td>".$yearStats[$prevYr]['rbi']."</td>";
         echo "<td>".$yearStats[$prevYr]['r']."</td>";
         echo "<td>".$yearStats[$prevYr]['bb']."</td>";
         echo "<td>".$yearStats[$prevYr]['hp']."</td>";
         echo "<td>".$yearStats[$prevYr]['sh']."</td>";
         echo "<td>".$yearStats[$prevYr]['sf']."</td>";
         echo "<td>".$yearStats[$prevYr]['k']."</td>";
         echo "<td>".$yearStats[$prevYr]['sb']."</td>";
	 echo "<td>".$yearStats[$prevYr]['cs']."</td>";
	 echo "<td>$Yavg</td>";
	 echo "<td>$Yobp</td>";
	 echo "<td>$Yslg</td>";
	 echo "<td>$Yops</td>";
	 echo "<td>".sprintf("%.1f",$yearStats[$prevYr]['war'])."</td>";
	 echo "<td>$YwOBA</td>";
   	 if ($noWS!=1)
          {
            $ows=$yearStats[$prevYr]['ows'];
            if ($ows!="") {$ows=sprintf("%.1f",$ows);}
            echo "<td>$ows</td>";
          }
         if (($noPSM!=1)&&($playerPos!=1))
          {
            $opsP=$yearStats[$prevYr]['opsPlus'];
            $EqAP=$yearStats[$prevYr]['EqA'];
	    $sabrPA=$yearStats[$prevYr]['sabrPA'];
            if ($sabrPA==0) {$opsP="";$EqAP="";}
             else
	     {
	       $opsP=round($opsP/$sabrPA,0);
	       $EqAP=$EqAP/$sabrPA;
	       $EqAP=sprintf("%.3f",$EqAP);
	       if ($EqAP<1) {$EqAP=strstr($EqAP,".");}
             }
            echo "<td>$opsP</td><td>$EqAP</td>";
          }
	 echo "</tr>\n";

         #Expanded
	 $expTable.="     <tr class='$cls'>";
	 $expTable.="<td class='".$cls."_l'>$prevYr - Tot";
	 if (isset($as[$yr])) {$expTable.=" (AS)";}
         $expTable.="</td>";
         $expTable.="<td>".$yearStats[$prevYr]['age']."</td>";
         $expTable.="<td>$Ypa</td>";
         $expTable.="<td>$Youts</td>";
         $expTable.="<td>$Yebh</td>";
         $expTable.="<td>$Ytb</td>";
         $expTable.="<td>$Yibb</td>";
         $expTable.="<td>$Ypi</td>";
         $expTable.="<td>$YpiPerPA</td>";
         $expTable.="<td>$YpaPerHR</td>";
         $expTable.="<td>$YbbPerK</td>";
         $expTable.="<td>$Ygdp</td>";
         $expTable.="<td>$YpwrSpd</td>";
         $expTable.="<td>$YsbPct</td>";
         $expTable.="<td>$Yrc</td>";
         $expTable.="<td>$Yrc27</td>";
         $expTable.="<td>$Ybabip</td>";
         $expTable.="<td>$Yiso</td>";
	 $expTable.="</tr>\n";

	 $cnt++;
       }
		// ADD AWARDS TO RESULTS
	  $awrdStr = "";
      if ((isset($roy[$yr])) || (isset($boy[$yr]))) {$cls='b'.($cnt%2+1);}
       else {$cls='s'.($cnt%2+1);}

	  if (isset($roy[$yr])) {
		  if (empty($awrdStr)) $awrdStr.="(";
		  $awrdStr.="ROY";
	  }
      if (isset($boy[$yr])) {
		  $teamStats[$tid]['boy']= (isset($teamStats[$tid]['boy'])) ? $teamStats[$tid]['boy']+1 : 1;
		  if (empty($awrdStr)) $awrdStr.="("; else $awrdStr.=",";
		  $awrdStr.="BOY";
       }

      echo "     <tr class='$cls'>";
      echo "<td class='".$cls."_l'>";
      // IF TEAM HTML HISTORY PAGE IS AVIALABLE FOR THIS TEAM AND YEAR, RENDER A LINK To IT
	  // FIXES BUG ID 212
	  $team_hist_path = "history/team_year_".$tid."_".$yr.".html";
	  $hasTeamHTML = file_exists($filepath."/".$team_hist_path);
	  if($hasTeamHTML) { echo('<a href="'.$htmlpath.$team_hist_path.'">'); }
	  $tabbr = ((isset($teams[$tid][$yr])) ? $teams[$tid][$yr] : "");
      if ($tabbr=="" && isset($teams[$tid][$year])) {$tabbr=$teams[$tid][$year];}
	  echo ($yr." - ".$tabbr);
	  if($hasTeamHTML) { echo('</a>'); }
	  // END HTML LINK EDIT
      if (isset($as[$yr]))
       {
        if (empty($awrdStr)) $awrdStr.="("; else $awrdStr.=",";
		 $awrdStr.="AS";
	 	$teamStats[$tid]['as']= (isset($teamStats[$tid]['as'])) ? $teamStats[$tid]['as']+1 : 1;
       }
	   if (!empty($awrdStr)) { echo("&nbsp;".$awrdStr.")"); }
      echo "</td>";
      echo "<td>$age</td>";
      echo "<td>$g</td>";
      echo "<td>$ab</td>";
      echo "<td>$h</td>";
      echo "<td>$d</td>";
      echo "<td>$t</td>";
      echo "<td>$hr</td>";
      echo "<td>$rbi</td>";
      echo "<td>$r</td>";
      echo "<td>$bb</td>";
      echo "<td>$hp</td>";
      echo "<td>$sh</td>";
      echo "<td>$sf</td>";
      echo "<td>$k</td>";
      echo "<td>$sb</td>";
      echo "<td>$cs</td>";
      echo "<td>$avg</td>";
      echo "<td>$obp</td>";
      echo "<td>$slg</td>";
      echo "<td>$ops</td>";
      echo "<td>$war</td>";
      echo "<td>$wOBA</td>";
      if ($noWS!=1)
       {
         $ows=$row['ows'];
	 $Tows+=$ows;
	 if ($ows!="")
	  {
            $teamStats[$tid]['ows']=$teamStats[$tid]['ows']+$ows;
	    $yearStats[$yr]['ows']=$yearStats[$yr]['ows']+$ows;
	    $ows=sprintf("%.1f",$ows);
	  }
         echo "<td>$ows</td>";
       }
      if (($noPSM!=1)&&($playerPos!=1))
       {
         $opsP=$opsPlus[$yr][$tid];
         $EqAP=$EqA[$yr][$tid];
	 $teamStats[$tid]['opsPlus']=$teamStats[$tid]['opsPlus']+$opsP*$pa;
	 $yearStats[$yr]['opsPlus']=$yearStats[$yr]['opsPlus']+$opsP*$pa;
	 $teamStats[$tid]['EqA']=$teamStats[$tid]['EqA']+$EqAP*$pa;
	 $yearStats[$yr]['EqA']=$yearStats[$yr]['EqA']+$EqAP*$pa;
	 $TopsP+=($opsP*$pa);
	 $TEqAP+=($EqAP*$pa);
         $EqAP=sprintf("%.3f",$EqAP);
         if ($EqAP<1) {$EqAP=strstr($EqAP,".");}
	 if ($opsP==0) {$opsP="";$EqAP="";}
	  else
	  {
            $teamStats[$tid]['sabrPA']=$teamStats[$tid]['sabrPA']+$pa;
	    $yearStats[$yr]['sabrPA']=$yearStats[$yr]['sabrPA']+$pa;
	    $TsabrPA+=$pa;
	  }
         echo "<td>$opsP</td><td>$EqAP</td>";
       }
      echo "</tr>\n";

      #Expanded
      $expTable.="     <tr class='$cls'>";
      $expTable.="<td class='".$cls."_l'>";
	  // IF TEAM HTML HISTORY PAGE IS AVIALABLE FOR THIS TEAM AND YEAR, RENDER A LINK To IT
	  // FIXES BUG ID 212
	  $team_hist_path = "history/team_year_".$tid."_".$yr.".html";
	  $hasTeamHTML = file_exists($filepath."/".$team_hist_path);
	  if($hasTeamHTML) { $expTable.='<a href="'.$htmlpath.$team_hist_path.'">'; }
	  $tabbr = ((isset($teams[$tid][$yr])) ? $teams[$tid][$yr] : "");
      if ($tabbr=="" && isset($teams[$tid][$year])) {$tabbr=$teams[$tid][$year];}
	  $expTable.= $yr.' - '.$tabbr;
	  if($hasTeamHTML) { $expTable.='</a>'; }
	  //$expTable.="</td>";
	  // END HTML LINK EDIT
      if (isset($as[$yr])) {$expTable.=" (AS)";}
      
      $expTable.="</td>";
      $expTable.="<td>$age</td>";
      $expTable.="<td>$pa</td>";
      $expTable.="<td>$outs</td>";
      $expTable.="<td>$ebh</td>";
      $expTable.="<td>$tb</td>";
      $expTable.="<td>$ibb</td>";
      $expTable.="<td>$pi</td>";
      $expTable.="<td>$piPerPA</td>";
      $expTable.="<td>$paPerHR</td>";
      $expTable.="<td>$bbPerK</td>";
      $expTable.="<td>$gdp</td>";
      $expTable.="<td>$pwrSpd</td>";
      $expTable.="<td>$sbPct</td>";
      $expTable.="<td>$rc</td>";
      $expTable.="<td>$rc27</td>";
      $expTable.="<td>$babip</td>";
      $expTable.="<td>$iso</td>";
      $expTable.="</tr>\n";

      $prevYr=$yr;
      $cnt++;
    }

   ## Show final total year
   if ($yearStats[$prevYr]['cnt']>1)
    {
      if ((isset($roy[$prevYr])) || (isset($boy[$prevYr]))) {$cls='b'.($cnt%2+1);}
       else {$cls='s'.($cnt%2+1);}

      $Yab=$yearStats[$prevYr]['ab'];
      $Yh=$yearStats[$prevYr]['h'];
      $Yd=$yearStats[$prevYr]['d'];
      $Yt=$yearStats[$prevYr]['t'];
      $Yhr=$yearStats[$prevYr]['hr'];
      $Ypa=$yearStats[$prevYr]['pa'];
      $Ybb=$yearStats[$prevYr]['bb'];
      $Yhp=$yearStats[$prevYr]['hp'];
      $Ysh=$yearStats[$prevYr]['sh'];
      $Ysf=$yearStats[$prevYr]['sf'];
      $Ysb=$yearStats[$prevYr]['sb'];
      $Ycs=$yearStats[$prevYr]['cs'];
      $Yk=$yearStats[$prevYr]['k'];
      #Expanded
      $Ypi=$yearStats[$prevYr]['pi'];
      $Yibb=$yearStats[$prevYr]['ibb'];
      $Ygdp=$yearStats[$prevYr]['gdp'];

      if ($Yab==0) {$Yavg=0;$Yslg=0;}
       else
       {
         $Yavg=$Yh/$Yab;
         $Yslg=($Yh+$Yd+2*$Yt+3*$Yhr)/$Yab;
       }
      if (($Yab+$Ybb+$Yhp+$Ysf)==0) {$Yobp=0;}
       else {$Yobp=($Yh+$Ybb+$Yhp)/($Yab+$Ybb+$Yhp+$Ysf);}
      if ($Ypa==0) {$YwOBA=0;} else {$YwOBA=(0.72*$Ybb+0.75*$Yhp+0.9*($Yh-$Yd-$Yt-$Yhr)+0.92*0+1.24*$Yd+1.56*$Yt+1.95*$Yhr)/$Ypa;}

      $Yops=$Yobp+$Yslg;
      $Yavg=sprintf("%.3f",$Yavg);
      $Yobp=sprintf("%.3f",$Yobp);
      $Yslg=sprintf("%.3f",$Yslg);
      $Yops=sprintf("%.3f",$Yops);
      $YwOBA=sprintf("%.3f",$YwOBA);
      if ($Yavg<1) {$Yavg=strstr($Yavg,".");}
      if ($Yobp<1) {$Yobp=strstr($Yobp,".");}
      if ($Yslg<1) {$Yslg=strstr($Yslg,".");}
      if ($Yops<1) {$Yops=strstr($Yops,".");}
      if ($YwOBA<1) {$YwOBA=strstr($YwOBA,".");}
      #Expanded
      if ($Ypa==0) {$YpiPerPA=0;} else {$YpiPerPA=$Ypi/$Ypa;}
      if ($Yhr==0) {$YpaPerHR=0;} else {$YpaPerHR=$Ypa/$Yhr;}
      $Youts=$Yab-$Yh+$Ycs+$Ysh+$Ysf;
      $Ytb=$Yh+$Yd+2*$Yt+3*$Yhr;
      $Yebh=$Yd+$Yt+$Yhr;
      if ($Ysb+$Ycs==0) {$YsbPct=0;} else {$YsbPct=$Ysb/($Ysb+$Ycs);}
      if (($Yab-$Yk-$Yhr+$Ysf)==0) {$Ybabip=0;} else {$Ybabip=($Yh-$Yhr)/($Yab-$Yk-$Yhr+$Ysf);}
      if ($Yab==0) {$Yiso=0;} else {$Yiso=($Ytb-$Yh)/$Yab;}
      if ($Yhr+$Ysb==0) {$YpwrSpd=0;} else {$YpwrSpd=(2*$Yhr*$Ysb)/($Yhr+$Ysb);}
      if (($Yab+$Ybb+$Yhp+$Ysh+$Ysf)==0) {$Yrc=0;$Yrc27=0;}
       else
       {
         $Yrc=(($Yh+$Ybb-$Ycs+$Yhp-$Ygdp)*($Ytb+(.26*($Ybb-$Yibb+$Yhp))+(.52*($Ysh+$Ysf+$Ysb))))/($Yab+$Ybb+$Yhp+$Ysh+$Ysf);
	 $Yrc27=27*$Yrc/$Youts;
       }
      if ($Yk==0) {$YbbPerK=0;} else {$YbbPerK=$Ybb/$Yk;}
      $YpiPerPA=sprintf("%.2f",$YpiPerPA);
      $YpaPerHR=sprintf("%.1f",$YpaPerHR);
      $YsbPct=sprintf("%.1f",100*$YsbPct);
      $Ybabip=sprintf("%.3f",$Ybabip);
      $Yiso=sprintf("%.3f",$Yiso);
      $YpwrSpd=sprintf("%.1f",$YpwrSpd);
      $Yrc=sprintf("%.0f",$Yrc);
      $Yrc27=sprintf("%.1f",$Yrc27);
      $YbbPerK=sprintf("%.2f",$YbbPerK);
      if ($Ybabip<1) {$Ybabip=strstr($Ybabip,".");}
      if ($Yiso<1) {$Yiso=strstr($Yiso,".");}

      echo "     <tr class='$cls'>";
      echo "<td class='".$cls."_l'>$prevYr - Tot";
      if (isset($as[$yr])) {echo " (AS)";}
      echo "</td>";
      echo "<td>".$yearStats[$prevYr]['age']."</td>";
      echo "<td>".$yearStats[$prevYr]['g']."</td>";
      echo "<td>".$yearStats[$prevYr]['ab']."</td>";
      echo "<td>".$yearStats[$prevYr]['h']."</td>";
      echo "<td>".$yearStats[$prevYr]['d']."</td>";
      echo "<td>".$yearStats[$prevYr]['t']."</td>";
      echo "<td>".$yearStats[$prevYr]['hr']."</td>";
      echo "<td>".$yearStats[$prevYr]['rbi']."</td>";
      echo "<td>".$yearStats[$prevYr]['r']."</td>";
      echo "<td>".$yearStats[$prevYr]['bb']."</td>";
      echo "<td>".$yearStats[$prevYr]['hp']."</td>";
      echo "<td>".$yearStats[$prevYr]['sh']."</td>";
      echo "<td>".$yearStats[$prevYr]['sf']."</td>";
      echo "<td>".$yearStats[$prevYr]['k']."</td>";
      echo "<td>".$yearStats[$prevYr]['sb']."</td>";
      echo "<td>".$yearStats[$prevYr]['cs']."</td>";
      echo "<td>$Yavg</td>";
      echo "<td>$Yobp</td>";
      echo "<td>$Yslg</td>";
      echo "<td>$Yops</td>";
      echo "<td>".sprintf("%.1f",$yearStats[$prevYr]['war'])."</td>";
      echo "<td>$YwOBA</td>";
      if ($noWS!=1)
       {
         $ows=$yearStats[$prevYr]['ows'];
         if ($ows!="") {$ows=sprintf("%.1f",$ows);}
         echo "<td>$ows</td>";
       }
      if (($noPSM!=1)&&($playerPos!=1))
       {
         $opsP=$yearStats[$prevYr]['opsPlus'];
	 $EqAP=$yearStats[$prevYr]['EqA'];
	 $sabrPA=$yearStats[$prevYr]['sabrPA'];
	 if ($sabrPA==0) {$opsP="";$EqAP="";}
          else
          {
            $opsP=round($opsP/$sabrPA,0);
            $EqAP=$EqAP/$sabrPA;
	    $EqAP=sprintf("%.3f",$EqAP);
	    if ($EqAP<1) {$EqAP=strstr($EqAP,".");}
	  }
	 echo "<td>$opsP</td><td>$EqAP</td>";
       }
      echo "</tr>\n";

      #Expanded
      $expTable.="     <tr class='$cls'>";
      $expTable.="<td class='".$cls."_l'>$prevYr - Tot";
      if (isset($as[$yr])) {$expTable.=" (AS)";}
      $expTable.="</td>";
      $expTable.="<td>".$yearStats[$prevYr]['age']."</td>";
      $expTable.="<td>$Ypa</td>";
      $expTable.="<td>$Youts</td>";
      $expTable.="<td>$Yebh</td>";
      $expTable.="<td>$Ytb</td>";
      $expTable.="<td>$Yibb</td>";
      $expTable.="<td>$Ypi</td>";
      $expTable.="<td>$YpiPerPA</td>";
      $expTable.="<td>$YpaPerHR</td>";
      $expTable.="<td>$YbbPerK</td>";
      $expTable.="<td>$Ygdp</td>";
      $expTable.="<td>$YpwrSpd</td>";
      $expTable.="<td>$YsbPct</td>";
      $expTable.="<td>$Yrc</td>";
      $expTable.="<td>$Yrc27</td>";
      $expTable.="<td>$Ybabip</td>";
      $expTable.="<td>$Yiso</td>";
      $expTable.="</tr>\n";
    }

   ## Display Career Totals
   echo "     <tfoot>\n";
   $expTable.="     <tfoot>\n";
   #per team
   foreach ($playedFor as $tid => $val)
    {
      $g=$teamStats[$tid]['g'];
      $ab=$teamStats[$tid]['ab'];
      $h=$teamStats[$tid]['h'];
      $d=$teamStats[$tid]['d'];
      $t=$teamStats[$tid]['t'];
      $hr=$teamStats[$tid]['hr'];
      $rbi=$teamStats[$tid]['rbi'];
      $r=$teamStats[$tid]['r'];
      $bb=$teamStats[$tid]['bb'];
      $hp=$teamStats[$tid]['hp'];
      $sh=$teamStats[$tid]['sh'];
      $sf=$teamStats[$tid]['sf'];
      $k=$teamStats[$tid]['k'];
      $sb=$teamStats[$tid]['sb'];
      $cs=$teamStats[$tid]['cs'];
      $pa=$teamStats[$tid]['pa'];
      $war=$teamStats[$tid]['war'];
      #Expanded
      $pi=$teamStats[$tid]['pi'];
      $ibb=$teamStats[$tid]['ibb'];
      $gdp=$teamStats[$tid]['gdp'];

      if ($ab==0) {$avg=0;$slg=0;}
       else
       {
         $avg=$h/$ab;
         $slg=($h+$d+2*$t+3*$hr)/$ab;
       }
      if (($ab+$bb+$hp+$sf)==0) {$obp=0;}
       else {$obp=($h+$bb+$hp)/($ab+$bb+$hp+$sf);}
      if ($pa==0) {$wOBA=0;} else {$wOBA=(0.72*$bb+0.75*$hp+0.9*($h-$d-$t-$hr)+0.92*0+1.24*$d+1.56*$t+1.95*$hr)/$pa;}

      $ops=$obp+$slg;
      $avg=sprintf("%.3f",$avg);
      $obp=sprintf("%.3f",$obp);
      $slg=sprintf("%.3f",$slg);
      $ops=sprintf("%.3f",$ops);
      $wOBA=sprintf("%.3f",$wOBA);
      if ($avg<1) {$avg=strstr($avg,".");}
      if ($obp<1) {$obp=strstr($obp,".");}
      if ($slg<1) {$slg=strstr($slg,".");}
      if ($ops<1) {$ops=strstr($ops,".");}
      if ($wOBA<1) {$wOBA=strstr($wOBA,".");}
      $war=sprintf("%.1f",$war);
      #Expanded
      if ($pa==0) {$piPerPA=0;} else {$piPerPA=$pi/$pa;}
      if ($hr==0) {$paPerHR=0;} else {$paPerHR=$pa/$hr;}
      $outs=$ab-$h+$cs+$sh+$sf;
      $tb=$h+$d+2*$t+3*$hr;
      $ebh=$d+$t+$hr;
      if ($sb+$cs==0) {$sbPct=0;} else {$sbPct=$sb/($sb+$cs);}
      if (($ab-$k-$hr+$sf)==0) {$babip=0;} else {$babip=($h-$hr)/($ab-$k-$hr+$sf);}
      if ($ab==0) {$iso=0;} else {$iso=($tb-$h)/$ab;}
      if ($hr+$sb==0) {$pwrSpd=0;} else {$pwrSpd=(2*$hr*$sb)/($hr+$sb);}
      if (($ab+$bb+$hp+$sh+$sf)==0) {$rc=0;$rc27=0;}
       else
       {
         $rc=(($h+$bb-$cs+$hp-$gdp)*($tb+(.26*($bb-$ibb+$hp))+(.52*($sh+$sf+$sb))))/($ab+$bb+$hp+$sh+$sf);
         $rc27=27*$rc/$outs;
       }
      if ($k==0) {$bbPerK=0;} else {$bbPerK=$bb/$k;}
      $piPerPA=sprintf("%.2f",$piPerPA);
      $paPerHR=sprintf("%.1f",$paPerHR);
      $sbPct=sprintf("%.1f",100*$sbPct);
      $babip=sprintf("%.3f",$babip);
      $iso=sprintf("%.3f",$iso);
      $pwrSpd=sprintf("%.1f",$pwrSpd);
      $rc=sprintf("%.0f",$rc);
      $rc27=sprintf("%.1f",$rc27);
      $bbPerK=sprintf("%.2f",$bbPerK);
      if ($babip<1) {$babip=strstr($babip,".");}
      if ($iso<1) {$iso=strstr($iso,".");}

      $boys=(isset($teamStats[$tid]['boy'])) ? $teamStats[$tid]['boy'] : 0;
      $asX=(isset($teamStats[$tid]['boy'])) ? $teamStats[$tid]['as'] : 0;
      $awTxt="";
      if ($boys>1) {$awTxt="$boys BOY's";}
      if ($boys==1) {$awTxt="$boys BOY";}
      if (($asX>0) && ($awTxt!="")) {$awTxt.=", ";}
      if ($asX>0) {$awTxt.=$asX."xAS";}
      if ($awTxt!="") {$awTxt=" (".$awTxt.")";}

      echo "     <tr class='headline'>";
      echo "<td class='hsc2_l' colspan=2>w/ ".$teams[$tid][$year].$awTxt."</td>";
      echo "<td class='hsc2'>$g</td>";
      echo "<td class='hsc2'>$ab</td>";
      echo "<td class='hsc2'>$h</td>";
      echo "<td class='hsc2'>$d</td>";
      echo "<td class='hsc2'>$t</td>";
      echo "<td class='hsc2'>$hr</td>";
      echo "<td class='hsc2'>$rbi</td>";
      echo "<td class='hsc2'>$r</td>";
      echo "<td class='hsc2'>$bb</td>";
      echo "<td class='hsc2'>$hp</td>";
      echo "<td class='hsc2'>$sh</td>";
      echo "<td class='hsc2'>$sf</td>";
      echo "<td class='hsc2'>$k</td>";
      echo "<td class='hsc2'>$sb</td>";
      echo "<td class='hsc2'>$cs</td>";
      echo "<td class='hsc2'>$avg</td>";
      echo "<td class='hsc2'>$obp</td>";
      echo "<td class='hsc2'>$slg</td>";
      echo "<td class='hsc2'>$ops</td>";
      echo "<td class='hsc2'>$war</td>";
      echo "<td class='hsc2'>$wOBA</td>";
      if ($noWS!=1)
       {
         $ows=$teamStats[$tid]['ows'];
         if ($ows!="") {$ows=sprintf("%.1f",$ows);}
         echo "<td class='hsc2'>$ows</td>";
       }
      if (($noPSM!=1)&&($playerPos!=1))
       {
	 $opsP=$teamStats[$tid]['opsPlus'];
         $EqAP=$teamStats[$tid]['EqA'];
	 $pa=$teamStats[$tid]['sabrPA'];
         if ($pa==0) {$opsP="";$EqAP="";}
	  else
	  {
	    $opsP=round($opsP/$pa,0);
	    $EqAP=$EqAP/$pa;
            $EqAP=sprintf("%.3f",$EqAP);
            if ($EqAP<1) {$EqAP=strstr($EqAP,".");}
	  }
         echo "<td class='hsc2'>$opsP</td><td class='hsc2'>$EqAP</td>";
       }
      echo "</tr>\n";

      #Expanded
      $expTable.="     <tr class='headline'>";
      $expTable.="<td class='hsc2_l' colspan=2>w/ ".$teams[$tid][$year].$awTxt."</td>";
      $expTable.="<td class='hsc2'>$pa</td>";
      $expTable.="<td class='hsc2'>$outs</td>";
      $expTable.="<td class='hsc2'>$ebh</td>";
      $expTable.="<td class='hsc2'>$tb</td>";
      $expTable.="<td class='hsc2'>$ibb</td>";
      $expTable.="<td class='hsc2'>$pi</td>";
      $expTable.="<td class='hsc2'>$piPerPA</td>";
      $expTable.="<td class='hsc2'>$paPerHR</td>";
      $expTable.="<td class='hsc2'>$bbPerK</td>";
      $expTable.="<td class='hsc2'>$gdp</td>";
      $expTable.="<td class='hsc2'>$pwrSpd</td>";
      $expTable.="<td class='hsc2'>$sbPct</td>";
      $expTable.="<td class='hsc2'>$rc</td>";
      $expTable.="<td class='hsc2'>$rc27</td>";
      $expTable.="<td class='hsc2'>$babip</td>";
      $expTable.="<td class='hsc2'>$iso</td>";
      echo "</tr>\n";
    }
   #for career
   $Twar=sprintf("%.1f",$Twar);
   if ($Tab==0) {$avg=0;$slg=0;}
    else
    {
      $avg=$Th/$Tab;
      $slg=($Th+$Td+2*$Tt+3*$Thr)/$Tab;
    }
   if (($Tab+$Tbb+$Thp+$Tsf)==0) {$obp=0;}
    else {$obp=($Th+$Tbb+$Thp)/($Tab+$Tbb+$Thp+$Tsf);}
   if ($Tpa==0) {$TwOBA=0;} else {$TwOBA=(0.72*$Tbb+0.75*$Thp+0.9*($Th-$Td-$Tt-$Thr)+0.92*0+1.24*$Td+1.56*$Tt+1.95*$Thr)/$Tpa;}
   $ops=$obp+$slg;
   $avg=sprintf("%.3f",$avg);
   $obp=sprintf("%.3f",$obp);
   $slg=sprintf("%.3f",$slg);
   $ops=sprintf("%.3f",$ops);
   $TwOBA=sprintf("%.3f",$TwOBA);
   if ($avg<1) {$avg=strstr($avg,".");}
   if ($obp<1) {$obp=strstr($obp,".");}
   if ($slg<1) {$slg=strstr($slg,".");}
   if ($ops<1) {$ops=strstr($ops,".");}
   if ($TwOBA<1) {$TwOBA=strstr($TwOBA,".");}
   #Expanded
   if ($Tpa==0) {$piPerPA=0;} else {$piPerPA=$Tpi/$TpiPA;}
   if ($Thr==0) {$paPerHR=0;} else {$paPerHR=$Tpa/$Thr;}
   $outs=$Tab-$Th+$Tcs+$Tsh+$Tsf;
   $tb=$Th+$Td+2*$Tt+3*$Thr;
   $ebh=$Td+$Tt+$Thr;
   if ($Tsb+$Tcs==0) {$sbPct=0;} else {$sbPct=$Tsb/($Tsb+$Tcs);}
   if (($Tab-$Tk-$Thr+$Tsf)==0) {$babip=0;} else {$babip=($Th-$Thr)/($Tab-$Tk-$Thr+$Tsf);}
   if ($Tab==0) {$iso=0;} else {$iso=($tb-$Th)/$Tab;}
   if ($Thr+$Tsb==0) {$pwrSpd=0;} else {$pwrSpd=(2*$Thr*$Tsb)/($Thr+$Tsb);}
   if (($Tab+$Tbb+$Thp+$Tsh+$Tsf)==0) {$rc=0;$rc27=0;}
    else
    {
      $rc=(($Th+$Tbb-$Tcs+$Thp-$Tgdp)*($tb+(.26*($Tbb-$Tibb+$Thp))+(.52*($Tsh+$Tsf+$Tsb))))/($Tab+$Tbb+$Thp+$Tsh+$Tsf);
      $rc27=27*$rc/$outs;
    }
   if ($Tk==0) {$bbPerK=0;} else {$bbPerK=$Tbb/$Tk;}
   $piPerPA=sprintf("%.2f",$piPerPA);
   $paPerHR=sprintf("%.1f",$paPerHR);
   $sbPct=sprintf("%.1f",100*$sbPct);
   $babip=sprintf("%.3f",$babip);
   $iso=sprintf("%.3f",$iso);
   $pwrSpd=sprintf("%.1f",$pwrSpd);
   $rc=sprintf("%.0f",$rc);
   $rc27=sprintf("%.1f",$rc27);
   $bbPerK=sprintf("%.2f",$bbPerK);
   if ($babip<1) {$babip=strstr($babip,".");}
   if ($iso<1) {$iso=strstr($iso,".");}

   echo "     <tr class='headline'>";
   echo "<td class='hsc2_l'>Totals</td>";
   echo "<td class='hsc2'>&nbsp;</td>";
   echo "<td class='hsc2'>$Tg</td>";
   echo "<td class='hsc2'>$Tab</td>";
   echo "<td class='hsc2'>$Th</td>";
   echo "<td class='hsc2'>$Td</td>";
   echo "<td class='hsc2'>$Tt</td>";
   echo "<td class='hsc2'>$Thr</td>";
   echo "<td class='hsc2'>$Trbi</td>";
   echo "<td class='hsc2'>$Tr</td>";
   echo "<td class='hsc2'>$Tbb</td>";
   echo "<td class='hsc2'>$Thp</td>";
   echo "<td class='hsc2'>$Tsh</td>";
   echo "<td class='hsc2'>$Tsf</td>";
   echo "<td class='hsc2'>$Tk</td>";
   echo "<td class='hsc2'>$Tsb</td>";
   echo "<td class='hsc2'>$Tcs</td>";
   echo "<td class='hsc2'>$avg</td>";
   echo "<td class='hsc2'>$obp</td>";
   echo "<td class='hsc2'>$slg</td>";
   echo "<td class='hsc2'>$ops</td>";
   echo "<td class='hsc2'>$Twar</td>";
   echo "<td class='hsc2'>$TwOBA</td>";
   if ($noWS!=1)
    {
      $Tows=sprintf("%.1f",$Tows);
      echo "<td class='hsc2'>$Tows</td>";
    }
   if (($noPSM!=1)&&($playerPos!=1))
    {
      $opsP=$TopsP;
      $EqAP=$TEqAP;
      $Tpa=$TsabrPA;
      if ($Tpa==0) {$opsP="";$EqAP="";}
       else
       {
         $opsP=round($opsP/$Tpa,0);
         $EqAP=$EqAP/$Tpa;
         $EqAP=sprintf("%.3f",$EqAP);
         if ($EqAP<1) {$EqAP=strstr($EqAP,".");}
       }
      echo "<td class='hsc2'>$opsP</td><td class='hsc2'>$EqAP</td>";
    }
   echo "</tr>";
   echo "     </tfoot>\n";

   #Expanded
   $expTable.="     <tr class='headline'>";
   $expTable.="<td class='hsc2_l' colspan=2>Totals</td>";
   $expTable.="<td class='hsc2'>$Tpa</td>";
   $expTable.="<td class='hsc2'>$outs</td>";
   $expTable.="<td class='hsc2'>$ebh</td>";
   $expTable.="<td class='hsc2'>$tb</td>";
   $expTable.="<td class='hsc2'>$Tibb</td>";
   $expTable.="<td class='hsc2'>$Tpi</td>";
   $expTable.="<td class='hsc2'>$piPerPA</td>";
   $expTable.="<td class='hsc2'>$paPerHR</td>";
   $expTable.="<td class='hsc2'>$bbPerK</td>";
   $expTable.="<td class='hsc2'>$Tgdp</td>";
   $expTable.="<td class='hsc2'>$pwrSpd</td>";
   $expTable.="<td class='hsc2'>$sbPct</td>";
   $expTable.="<td class='hsc2'>$rc</td>";
   $expTable.="<td class='hsc2'>$rc27</td>";
   $expTable.="<td class='hsc2'>$babip</td>";
   $expTable.="<td class='hsc2'>$iso</td>";
   $expTable.="</tr>";
   $expTable.="     </tfoot>\n";

   ## Close out batting stats
   echo "    </table>\n";
   echo "    </td></tr></table>\n";
   echo "   </div>\n";
   echo "  </tr></td>\n";

   ##### Display Expanded Batting Stats
   echo "  <tr><td>\n";
   echo "   <div class='tablebox'>\n";
   echo "    <table cellpadding=0 cellspacing=0><tr class='title'><td colspan=22>Expanded Batting Stats</td></tr><tr><td>\n";
   echo "    <table cellpadding=2 cellspacing=0 class='sortable' width='910px'>\n";
   echo "     <thead><tr class='headline'>";
   echo "<td class='hsc2_l'>Year/Team</td>";
   echo "<td class='hsc2'>Age</td>";
   echo "<td class='hsc2'>PA</td>";
   echo "<td class='hsc2'>Outs</td>";
   echo "<td class='hsc2'>EBH</td>";
   echo "<td class='hsc2'>TB</td>";
   echo "<td class='hsc2'>IBB</td>";
   echo "<td class='hsc2'>PS</td>";
   echo "<td class='hsc2'>PS/PA</td>";
   echo "<td class='hsc2'>PA/HR</td>";
   echo "<td class='hsc2'>BB/K</td>";
   echo "<td class='hsc2'>GDP</td>";
   echo "<td class='hsc2'>PwrSpd</td>";
   echo "<td class='hsc2'>SB%</td>";
   echo "<td class='hsc2'>RC</td>";
   echo "<td class='hsc2'>RC/27</td>";
   echo "<td class='hsc2'>BABIP</td>";
   echo "<td class='hsc2'>ISO</td>";
   echo "</tr></thead>\n";
   echo $expTable;
   echo "    </table>\n";
   echo "    </td></tr></table>\n";
   echo "   </div>\n";
   echo "  </tr></td>\n";
 } else {
	 /*------------------------------------------------------------------------
	 /
	 /	PITCHING STATS DISPLAY
	 /
	 /-----------------------------------------------------------------------*/
   echo "  <tr><td>\n";
   echo "   <div class='tablebox'>\n";
   echo "    <table cellpadding=0 cellspacing=0><tr class='title'><td colspan=22>Pitching Stats</td></tr><tr><td>\n";
   echo "    <table cellpadding=2 cellspacing=0 class='sortable' width='910px'>\n";
   echo "     <thead><tr class='headline'>";
   echo "<td class='hsc2_l'>Year/Team</td>";
   echo "<td class='hsc2'>Age</td>";
   echo "<td class='hsc2'>G</td>";
   echo "<td class='hsc2'>GS</td>";
   echo "<td class='hsc2'>W</td>";
   echo "<td class='hsc2'>L</td>";
   echo "<td class='hsc2'>SV</td>";
   echo "<td class='hsc2'>ERA</td>";
   echo "<td class='hsc2'>IP</td>";
   echo "<td class='hsc2'>HA</td>";
   echo "<td class='hsc2'>R</td>";
   echo "<td class='hsc2'>ER</td>";
   echo "<td class='hsc2'>HR</td>";
   echo "<td class='hsc2'>BB</td>";
   echo "<td class='hsc2'>K</td>";
   echo "<td class='hsc2'>HLD</td>";
   echo "<td class='hsc2'>CG</td>";
   echo "<td class='hsc2'>SHO</td>";
   echo "<td class='hsc2'>WHIP</td>";
   echo "<td class='hsc2'>BABIP</td>";
   echo "<td class='hsc2'>WAR</td>";
   if ($noWS!=1) {echo "<td class='hsc2'>WS</td>";}
   if (($noPSM!=1)&&($playerPos==1)) {echo "<td class='hsc2'>ERA+</td><td class='hsc2'>ERC</td>";}
   echo "</tr></thead>\n";
   $cnt=0;
   $prevYr=-1;
   	$Tg=0;
	$Tgs=0;
	$Tw=0;
	$Tl=0;
	$Ts=0;
	$Tip=0;
	$Tha=0;
	$Tr=0;
	$Ter=0;
	$Thra=0;
	$Tbb=0;
	$Tk=0;
	$Thld=0;
	$Tcg=0;
	$Tsho=0;
	$Tab=0;
	$Tsf=0;
	$Twar=0;

	$Tbf=0;
	$Tpi=0;
	$Tqs=0;
	$Tgb=0;
	$Tgf=0;
	$Tfb=0;
	$Twp=0;
	$Tbk=0;
	$Tsvo=0;
	$Tbs=0;
	foreach($careerStats as $row) {
      $yr=$row['year'];
      $tid=$row['team_id'];
      $tabbr = ((isset($teams[$tid][$yr])) ? $teams[$tid][$yr] : "");
      if ($tabbr=="" && isset($teams[$tid][$year])) {$tabbr=$teams[$tid][$year];}
      $g=$row['g'];
      $gs=$row['gs'];
      $w=$row['w'];
      $l=$row['l'];
      $s=$row['s'];
      $ip=$row['ip'];
      $ha=$row['ha'];
      $r=$row['r'];
      $er=$row['er'];
      $hra=$row['hra'];
      $bb=$row['bb'];
      $k=$row['k'];
      $hld=$row['hld'];
      $cg=$row['cg'];
      $sho=$row['sho'];
      $ab=$row['ab'];
      $sf=$row['sf'];
      $war=$row['war'];
      #Expanded
      $bf=$row['bf'];
      $pi=$row['pi'];
      $qs=$row['qs'];
      $gf=$row['gf'];
      $gb=$row['gb'];
      $fb=$row['fb'];
      $wp=$row['wp'];
      $bk=$row['bk'];
      $svo=$row['svo'];
      $bs=$row['bs'];



      $playedFor[$tid]=1;
      $teamStats[$tid]['g']=(isset($teamStats[$tid]['g'])) ? $teamStats[$tid]['g']+$g : $g;
	  $teamStats[$tid]['gs']=(isset($teamStats[$tid]['gs'])) ? $teamStats[$tid]['gs']+$gs : $gs;
	  $teamStats[$tid]['w']=(isset($teamStats[$tid]['w'])) ? $teamStats[$tid]['w']+$w : $w;
	  $teamStats[$tid]['l']=(isset($teamStats[$tid]['l'])) ? $teamStats[$tid]['l']+$l : $l;
	  $teamStats[$tid]['s']=(isset($teamStats[$tid]['s'])) ? $teamStats[$tid]['s']+$s : $s;
	  $teamStats[$tid]['ip']=(isset($teamStats[$tid]['ip'])) ? $teamStats[$tid]['ip']+$ip : $ip;
	  $teamStats[$tid]['ha']=(isset($teamStats[$tid]['ha'])) ? $teamStats[$tid]['ha']+$ha : $ha;
	  $teamStats[$tid]['r']=(isset($teamStats[$tid]['r'])) ? $teamStats[$tid]['r']+$r : $r;
	  $teamStats[$tid]['er']=(isset($teamStats[$tid]['er'])) ? $teamStats[$tid]['er']+$er : $er;
	  $teamStats[$tid]['hra']=(isset($teamStats[$tid]['hra'])) ? $teamStats[$tid]['hra']+$hra : $hra;
	  $teamStats[$tid]['bb']=(isset($teamStats[$tid]['bb'])) ? $teamStats[$tid]['bb']+$bb : $bb;
	  $teamStats[$tid]['k']=(isset($teamStats[$tid]['k'])) ? $teamStats[$tid]['k']+$k : $k;
	  $teamStats[$tid]['hld']=(isset($teamStats[$tid]['hld'])) ? $teamStats[$tid]['hld']+$hld : $hld;
	  $teamStats[$tid]['cg']=(isset($teamStats[$tid]['cg'])) ? $teamStats[$tid]['cg']+$cg : $cg;
	  $teamStats[$tid]['sho']=(isset($teamStats[$tid]['sho'])) ? $teamStats[$tid]['sho']+$sho : $sho;
	  $teamStats[$tid]['ab']=(isset($teamStats[$tid]['ab'])) ? $teamStats[$tid]['ab']+$ab : $ab;
	  $teamStats[$tid]['sf']=(isset($teamStats[$tid]['sf'])) ? $teamStats[$tid]['sf']+$sf : $sf;
	  $teamStats[$tid]['war']=(isset($teamStats[$tid]['war'])) ? $teamStats[$tid]['war']+$war : $war;
	  #Expanded
	  $teamStats[$tid]['bf']=(isset($teamStats[$tid]['bf'])) ? $teamStats[$tid]['bf']+$bf : $bf;
	  $teamStats[$tid]['pi']=(isset($teamStats[$tid]['pi'])) ? $teamStats[$tid]['pi']+$pi : $pi;
	  $teamStats[$tid]['qs']=(isset($teamStats[$tid]['qs'])) ? $teamStats[$tid]['qs']+$qs : $qs;
	  $teamStats[$tid]['gf']=(isset($teamStats[$tid]['gf'])) ? $teamStats[$tid]['gf']+$gf : $gf;
	  $teamStats[$tid]['gb']=(isset($teamStats[$tid]['gb'])) ? $teamStats[$tid]['gb']+$gb : $gb;
	  $teamStats[$tid]['fb']=(isset($teamStats[$tid]['fb'])) ? $teamStats[$tid]['fb']+$fb : $fb;
	  $teamStats[$tid]['wp']=(isset($teamStats[$tid]['wp'])) ? $teamStats[$tid]['wp']+$wp : $wp;
	  $teamStats[$tid]['bk']=(isset($teamStats[$tid]['bk'])) ? $teamStats[$tid]['bk']+$bk : $bk;
	  $teamStats[$tid]['svo']=(isset($teamStats[$tid]['svo'])) ? $teamStats[$tid]['svo']+$svo : $svo;
	  $teamStats[$tid]['bs']=(isset($teamStats[$tid]['bs'])) ? $teamStats[$tid]['bs']+$bs : $bs;


      $yrDate=strtotime($yr."-7-1");
      $age=floor(($yrDate-strtotime($thisItem['date_of_birth']))/31536000);

	  $yearStats[$tid]['cnt']=(isset($yearStats[$tid]['cnt'])) ? $yearStats[$tid]['cnt']+1 : 1;
	  $yearStats[$yr]['age']=$age;
	  $yearStats[$tid]['g']=(isset($yearStats[$tid]['g'])) ? $yearStats[$tid]['g']+$g : $g;
	  $yearStats[$tid]['gs']=(isset($yearStats[$tid]['gs'])) ? $yearStats[$tid]['gs']+$gs : $gs;
	  $yearStats[$tid]['w']=(isset($yearStats[$tid]['w'])) ? $yearStats[$tid]['w']+$w : $w;
	  $yearStats[$tid]['l']=(isset($yearStats[$tid]['l'])) ? $yearStats[$tid]['l']+$l : $l;
	  $yearStats[$tid]['s']=(isset($yearStats[$tid]['s'])) ? $yearStats[$tid]['s']+$s : $s;
	  $yearStats[$tid]['ip']=(isset($yearStats[$tid]['ip'])) ? $yearStats[$tid]['ip']+$ip : $ip;
	  $yearStats[$tid]['ha']=(isset($yearStats[$tid]['ha'])) ? $yearStats[$tid]['ha']+$ha : $ha;
	  $yearStats[$tid]['r']=(isset($yearStats[$tid]['r'])) ? $yearStats[$tid]['r']+$r : $r;
	  $yearStats[$tid]['er']=(isset($yearStats[$tid]['er'])) ? $yearStats[$tid]['er']+$er : $er;
	  $yearStats[$tid]['hra']=(isset($yearStats[$tid]['hra'])) ? $yearStats[$tid]['hra']+$hra : $hra;
	  $yearStats[$tid]['bb']=(isset($yearStats[$tid]['bb'])) ? $yearStats[$tid]['bb']+$bb : $bb;
	  $yearStats[$tid]['k']=(isset($yearStats[$tid]['k'])) ? $yearStats[$tid]['k']+$k : $k;
	  $yearStats[$tid]['hld']=(isset($yearStats[$tid]['hld'])) ? $yearStats[$tid]['hld']+$hld : $hld;
	  $yearStats[$tid]['cg']=(isset($yearStats[$tid]['cg'])) ? $yearStats[$tid]['cg']+$cg : $cg;
	  $yearStats[$tid]['sho']=(isset($yearStats[$tid]['sho'])) ? $yearStats[$tid]['sho']+$sho : $sho;
	  $yearStats[$tid]['ab']=(isset($yearStats[$tid]['ab'])) ? $yearStats[$tid]['ab']+$ab : $ab;
	  $yearStats[$tid]['sf']=(isset($yearStats[$tid]['sf'])) ? $yearStats[$tid]['sf']+$sf : $sf;
	  $yearStats[$tid]['war']=(isset($yearStats[$tid]['war'])) ? $yearStats[$tid]['war']+$war : $war;
	  #Expanded
	  $yearStats[$tid]['bf']=(isset($yearStats[$tid]['bf'])) ? $yearStats[$tid]['bf']+$bf : $bf;
	  $yearStats[$tid]['pi']=(isset($yearStats[$tid]['pi'])) ? $yearStats[$tid]['pi']+$pi : $pi;
	  $yearStats[$tid]['qs']=(isset($yearStats[$tid]['qs'])) ? $yearStats[$tid]['qs']+$qs : $qs;
	  $yearStats[$tid]['gf']=(isset($yearStats[$tid]['gf'])) ? $yearStats[$tid]['gf']+$gf : $gf;
	  $yearStats[$tid]['gb']=(isset($yearStats[$tid]['gb'])) ? $yearStats[$tid]['gb']+$gb : $gb;
	  $yearStats[$tid]['fb']=(isset($yearStats[$tid]['fb'])) ? $yearStats[$tid]['fb']+$fb : $fb;
	  $yearStats[$tid]['wp']=(isset($yearStats[$tid]['wp'])) ? $yearStats[$tid]['wp']+$wp : $wp;
	  $yearStats[$tid]['bk']=(isset($yearStats[$tid]['bk'])) ? $yearStats[$tid]['bk']+$bk : $bk;
	  $yearStats[$tid]['svo']=(isset($yearStats[$tid]['svo'])) ? $yearStats[$tid]['svo']+$svo : $svo;
	  $yearStats[$tid]['bs']=(isset($yearStats[$tid]['bs'])) ? $yearStats[$tid]['bs']+$bs : $bs;

      $Tg+=$g;
      $Tgs+=$gs;
      $Tw+=$w;
      $Tl+=$l;
      $Ts+=$s;
      $Tip+=$ip;
      $Tha+=$ha;
      $Tr+=$r;
      $Ter+=$er;
      $Thra+=$hra;
      $Tbb+=$bb;
      $Tk+=$k;
      $Thld+=$hld;
      $Tcg+=$cg;
      $Tsho+=$sho;
      $Tab+=$ab;
      $Tsf+=$sf;
      $Twar+=$war;
      #Expanded
      $Tbf+=$bf;
      $Tpi+=$pi;
      $Tqs+=$qs;
      $Tgf+=$gf;
      $Tgb+=$gb;
      $Tfb+=$fb;
      $Twp+=$wp;
      $Tbk+=$bk;
      $Tsvo+=$svo;
      $Tbs+=$bs;

      if ($ip==0) {$era=0;$whip=0;}
       else
       {
         $era=9*$er/$ip;
         $whip=($ha+$bb)/$ip;
       }
      $bip=$ab-$k-$hra+$sf;
      if ($bip==0) {$babip=0;}
       else {$babip=($ha-$hra)/$bip;}

      $era=sprintf("%.2f",$era);
      $whip=sprintf("%.2f",$whip);
      $babip=sprintf("%.3f",$babip);
      if ($whip<1) {$whip=strstr($whip,".");}
      if ($babip<1) {$babip=strstr($babip,".");}
      $war=sprintf("%.1f",$war);
      #Expanded
      if ($ip==0) {$rPer9=0;$hPer9=0;$kPer9=0;$bbPer9=0;$hrPer9=0;}
       {
          $rPer9=9*$r/$ip;
	  $hPer9=9*$ha/$ip;
    	  $kPer9=9*$k/$ip;
	  $bbPer9=9*$bb/$ip;
	  $hrPer9=9*$hra/$ip;
       }
      if ($bb==0) {$kPerBB=0;} else {$kPerBB=$k/$bb;}
      if ($gs==0) {$qsPct=0;$cgPct=0;} else {$qsPct=$qs/$gs;$cgPct=$cg/$gs;}
      if ($gb+$fb==0) {$gbPct=0;} else {$gbPct=$gb/($gb+$fb);}
      if (($s+$bs)==0) {$svPct=0;} else {$svPct=$s/($s+$bs);}
      $rPer9=sprintf("%.2f",$rPer9);
      $hPer9=sprintf("%.2f",$hPer9);
      $kPer9=sprintf("%.2f",$kPer9);
      $bbPer9=sprintf("%.2f",$bbPer9);
      $hrPer9=sprintf("%.2f",$hrPer9);
      $kPerBB=sprintf("%.2f",$kPerBB);
      $qsPct=sprintf("%.1f",100*$qsPct);
      $cgPct=sprintf("%.1f",100*$cgPct);
      $gbPct=sprintf("%.1f",100*$gbPct);
      $svPct=sprintf("%.1f",100*$svPct);
      $ip=sprintf("%.1f",$ip);

      if (($prevYr!=$yr)&&((isset($yearStats[$prevYr]['cnt']) && $yearStats[$prevYr]['cnt']>1)))
       {
         if ((isset($roy[$prevYr])) || (isset($poy[$prevYr]))) {$cls='b'.($cnt%2+1);}
          else {$cls='s'.($cnt%2+1);}

         $Yip=$yearStats[$prevYr]['ip'];
         $Yer=$yearStats[$prevYr]['er'];
         $Yr=$yearStats[$prevYr]['r'];
         $Yha=$yearStats[$prevYr]['ha'];
         $Ybb=$yearStats[$prevYr]['bb'];
         $Yab=$yearStats[$prevYr]['ab'];
         $Yk=$yearStats[$prevYr]['k'];
         $Yhra=$yearStats[$prevYr]['hra'];
         $Ysf=$yearStats[$prevYr]['sf'];

	 $Yqs=$yearStats[$prevYr]['qs'];
         $Ygs=$yearStats[$prevYr]['gs'];
         $Ycg=$yearStats[$prevYr]['cg'];
         $Ygb=$yearStats[$prevYr]['gb'];
         $Yfb=$yearStats[$prevYr]['fb'];
         $Ys=$yearStats[$prevYr]['s'];
         $Ysvo=$yearStats[$prevYr]['svo'];

	 if ($Yip==0) {$Yera=0;$Ywhip=0;}
	  else
          {
            $Yera=9*$Yer/$Yip;
            $Ywhip=($Yha+$Ybb)/$Yip;
          }
         $Ybip=$Yab-$Yk-$Yhra+$Ysf;
         if ($Ybip==0) {$Ybabip=0;}
          else {$Ybabip=($Yha-$Yhra)/$Ybip;}

         $Yera=sprintf("%.2f",$Yera);
         $Ywhip=sprintf("%.2f",$Ywhip);
	 $Ybabip=sprintf("%.3f",$Ybabip);
	 if ($Ywhip<1) {$Ywhip=strstr($Ywhip,".");}
	 if ($Ybabip<1) {$Ybabip=strstr($Ybabip,".");}
         #Expanded
         if ($Yip==0) {$YrPer9=0;$YhPer9=0;$YkPer9=0;$YbbPer9=0;$YhrPer9=0;}
          {
             $YrPer9=9*$Yr/$Yip;
	     $YhPer9=9*$Yha/$Yip;
   	     $YkPer9=9*$Yk/$Yip;
	     $YbbPer9=9*$Ybb/$Yip;
   	     $YhrPer9=9*$Yhra/$Yip;
	  }
   	 if ($Ybb==0) {$YkPerBB=0;} else {$YkPerBB=$Yk/$Ybb;}
	 if ($Ygs==0) {$YqsPct=0;$YcgPct=0;} else {$YqsPct=$Yqs/$Ygs;$YcgPct=$Ycg/$Ygs;}
   	 if ($Ygb+$Yfb==0) {$YgbPct=0;} else {$YgbPct=$Ygb/($Ygb+$Yfb);}
         if (($Ys+$Ybs)==0) {$YsvPct=0;} else {$YsvPct=$Ys/($Ys+$Ybs);}
	 $YrPer9=sprintf("%.2f",$YrPer9);
   	 $YhPer9=sprintf("%.2f",$YhPer9);
	 $YkPer9=sprintf("%.2f",$YkPer9);
   	 $YbbPer9=sprintf("%.2f",$YbbPer9);
	 $YhrPer9=sprintf("%.2f",$YhrPer9);
   	 $YkPerBB=sprintf("%.2f",$YkPerBB);
	 $YqsPct=sprintf("%.1f",100*$YqsPct);
   	 $YcgPct=sprintf("%.1f",100*$YcgPct);
	 $YgbPct=sprintf("%.1f",100*$YgbPct);
	 $YsvPct=sprintf("%.1f",100*$YsvPct);

         echo "     <tr class='$cls'>";
         echo "<td class='".$cls."_l'>$prevYr - Tot";
	 if (isset($as[$prevYr])) {echo " (AS)";}
	 echo "</td>";
	 echo "<td>".$yearStats[$prevYr]['age']."</td>";
	 echo "<td>".$yearStats[$prevYr]['g']."</td>";
	 echo "<td>".$yearStats[$prevYr]['gs']."</td>";
	 echo "<td>".$yearStats[$prevYr]['w']."</td>";
	 echo "<td>".$yearStats[$prevYr]['l']."</td>";
	 echo "<td>".$yearStats[$prevYr]['s']."</td>";
	 echo "<td>".$Yera."</td>";
	 echo "<td>".sprintf("%.1f",$Yip)."</td>";
	 echo "<td>".$yearStats[$prevYr]['ha']."</td>";
	 echo "<td>".$yearStats[$prevYr]['r']."</td>";
	 echo "<td>".$yearStats[$prevYr]['er']."</td>";
	 echo "<td>".$yearStats[$prevYr]['hra']."</td>";
	 echo "<td>".$yearStats[$prevYr]['bb']."</td>";
	 echo "<td>".$yearStats[$prevYr]['k']."</td>";
	 echo "<td>".$yearStats[$prevYr]['hld']."</td>";
	 echo "<td>".$yearStats[$prevYr]['cg']."</td>";
	 echo "<td>".$yearStats[$prevYr]['sho']."</td>";
	 echo "<td>".$Ywhip."</td>";
	 echo "<td>".$Ybabip."</td>";
	 echo "<td>".sprintf("%.1f",$yearStats[$prevYr]['war'])."</td>";
	 if ($noWS!=1)
	  {
	    $pws=$yearStats[$prevYr]['pws'];
	    if ($pws!="") {$pws=sprintf("%.1f",$pws);}
   	    echo "<td>$pws</td>";
  	  }
         if (($noPSM!=1)&&($playerPos==1))
          {
            $eraP=$yearStats[$prevYr]['eraPlus'];
	    $ceraP=$yearStats[$prevYr]['cERA'];
	    $sabrIP=$yearStats[$prevYr]['sabrIP'];
            if (($eraP==0)||($sabrIP==0)) {$eraP="";}
             else
	     {
	       $eraP=$eraP/$sabrIP;
               $eraP=round($eraP,0);
	     }
            if (($ceraP==0)||($sabrIP==0)) {$ceraP="";}
             else
	     {
	       $ceraP=$ceraP/$sabrIP;
               $ceraP=sprintf("%.2f",$ceraP);
	     }
	    echo "<td>$eraP</td><td>$ceraP</td>";
	  }
	 echo "</tr>\n";

         #Expanded
	 $expTable.="     <tr class='$cls'>";
         $expTable.="<td class='".$cls."_l'>$prevYr - Tot</td>";
	 $expTable.="<td>".$yearStats[$prevYr]['age']."</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['bf']."</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['pi']."</td>";
 	 $expTable.="<td>$YrPer9</td>";
 	 $expTable.="<td>$YhPer9</td>";
 	 $expTable.="<td>$YkPer9</td>";
 	 $expTable.="<td>$YbbPer9</td>";
 	 $expTable.="<td>$YhrPer9</td>";
 	 $expTable.="<td>$YkPerBB</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['qs']."</td>";
 	 $expTable.="<td>$YqsPct</td>";
 	 $expTable.="<td>$YcgPct</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['gf']."</td>";
 	 $expTable.="<td>$YgbPct</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['wp']."</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['bk']."</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['bs']."</td>";
 	 $expTable.="<td>$YsvPct</td>";
 	 $expTable.="<td>".$yearStats[$prevYr]['svo']."</td>";
	 $expTable.="</tr>\n";

         $cnt++;
       }
	   // ADD AWARDS TO RESULTS
	  $awrdStr = "";
      if ((isset($roy[$yr])) || (isset($poy[$yr]))) {$cls='b'.($cnt%2+1);}
       else {$cls='s'.($cnt%2+1);}

	  if (isset($roy[$yr])) {
		  if (empty($awrdStr)) $awrdStr.="(";
		  $awrdStr.="ROY";
	  }
      if (isset($poy[$yr])) {
		  $teamStats[$tid]['poy']= (isset($teamStats[$tid]['poy'])) ? $teamStats[$tid]['poy']+1 : 1;
		  if (empty($awrdStr)) $awrdStr.="("; else $awrdStr.=",";
		  $awrdStr.="POY";
       }

      echo "     <tr class='$cls'>";
      echo "<td class='".$cls."_l'>";
	  // IF TEAM HTML HISTORY PAGE IS AVIALABLE FOR THIS TEAM AND YEAR, RENDER A LINK To IT
	  // FIXES BUG ID 212
	  $team_hist_path = "history/team_year_".$tid."_".$yr.".html";
	  $hasTeamHTML = file_exists($filepath."/".$team_hist_path);
	  if($hasTeamHTML) { echo('<a href="'.$htmlpath.$team_hist_path.'">'); }
	  $tabbr = ((isset($teams[$tid][$yr])) ? $teams[$tid][$yr] : "");
      if ($tabbr=="" && isset($teams[$tid][$year])) {$tabbr=$teams[$tid][$year];}
	  echo ($yr." - ".$tabbr);
	  if($hasTeamHTML) { echo('</a>'); }
	  // END HTML LINK EDIT
      if (isset($as[$yr]))
       {
        if (empty($awrdStr)) $awrdStr.="("; else $awrdStr.=",";
		 $awrdStr.="AS";
	 	$teamStats[$tid]['as']= (isset($teamStats[$tid]['as'])) ? $teamStats[$tid]['as']+1 : 1;
       }
	   if (!empty($awrdStr)) { echo("&nbsp;".$awrdStr.")"); }
      echo "</td>";
      echo "<td>$age</td>";
      echo "<td>$g</td>";
      echo "<td>$gs</td>";
      echo "<td>$w</td>";
      echo "<td>$l</td>";
      echo "<td>$s</td>";
      echo "<td>$era</td>";
      echo "<td>$ip</td>";
      echo "<td>$ha</td>";
      echo "<td>$r</td>";
      echo "<td>$er</td>";
      echo "<td>$hra</td>";
      echo "<td>$bb</td>";
      echo "<td>$k</td>";
      echo "<td>$hld</td>";
      echo "<td>$cg</td>";
      echo "<td>$sho</td>";
      echo "<td>$whip</td>";
      echo "<td>$babip</td>";
      echo "<td>$war</td>";
      if ($noWS!=1)
       {
         $pws=$row['pws'];
         $Tpws+=$pws;
	 if ($pws!="")
	  {
            $teamStats[$tid]['pws']=$teamStats[$tid]['pws']+$pws;
	    $yearStats[$yr]['pws']=$yearStats[$yr]['pws']+$pws;
	    $pws=sprintf("%.1f",$pws);
	  }
         echo "<td>$pws</td>";
       }
      if (($noPSM!=1)&&($playerPos==1))
       {
         $eraP=$eraPlus[$yr][$tid];
         $teamStats[$tid]['eraPlus']=$teamStats[$tid]['eraPlus']+$eraP*$ip;
	 $yearStats[$yr]['eraPlus']=$yearStats[$yr]['eraPlus']+$eraP*$ip;
	 $TeraP+=($eraP*$ip);
         $ceraP=$cERA[$yr][$tid];
         $teamStats[$tid]['cERA']=$teamStats[$tid]['cERA']+$ceraP*$ip;
	 $yearStats[$yr]['cERA']=$yearStats[$yr]['cERA']+$ceraP*$ip;
	 $TceraP+=($ceraP*$ip);
	 if (($eraP!=0)||($ceraP!=0))
	  {
	    $teamStats[$tid]['sabrIP']=$teamStats[$tid]['sabrIP']+$ip;
	    $yearStats[$yr]['sabrIP']=$yearStats[$yr]['sabrIP']+$ip;
	    $TsabrIP+=$ip;
	  }
         if ($eraP==0) {$eraP="";}
	 if ($ceraP==0) {$ceraP="";}
 	  else {$ceraP=sprintf("%.2f",$ceraP);}
	 echo "<td>$eraP</td><td>$ceraP</td>";
       }
      echo "</tr>\n";

      #Expanded
      $expTable.="     <tr class='$cls'>";
	  $expTable.="<td class='".$cls."_l'>";
	  // IF TEAM HTML HISTORY PAGE IS AVIALABLE FOR THIS TEAM AND YEAR, RENDER A LINK To IT
	  // FIXES BUG ID 212
	  $team_hist_path = "history/team_year_".$tid."_".$yr.".html";
	  $hasTeamHTML = file_exists($filepath."/".$team_hist_path);
	  if($hasTeamHTML) { $expTable.='<a href="'.$htmlpath.$team_hist_path.'">'; }
	  $tabbr = ((isset($teams[$tid][$yr])) ? $teams[$tid][$yr] : "");
      if ($tabbr=="" && isset($teams[$tid][$year])) {$tabbr=$teams[$tid][$year];}
	  $expTable.= $yr.' - '.$tabbr;
	  if($hasTeamHTML) { $expTable.='</a>'; }
	  //$expTable.="</td>";
	  // END HTML LINK EDIT

      if (isset($as[$yr])) {$expTable.=" (AS)";}
      $expTable.="</td>";
      $expTable.="<td>$age</td>";
      $expTable.="<td>$bf</td>";
      $expTable.="<td>$pi</td>";
      $expTable.="<td>$rPer9</td>";
      $expTable.="<td>$hPer9</td>";
      $expTable.="<td>$kPer9</td>";
      $expTable.="<td>$bbPer9</td>";
      $expTable.="<td>$hrPer9</td>";
      $expTable.="<td>$kPerBB</td>";
      $expTable.="<td>$qs</td>";
      $expTable.="<td>$qsPct</td>";
      $expTable.="<td>$cgPct</td>";
      $expTable.="<td>$gf</td>";
      $expTable.="<td>$gbPct</td>";
      $expTable.="<td>$wp</td>";
      $expTable.="<td>$bk</td>";
      $expTable.="<td>$bs</td>";
      $expTable.="<td>$svPct</td>";
      $expTable.="<td>$svo</td>";
      $expTable.="</tr>\n";

      $prevYr=$yr;
      $cnt++;
    }

   ## Show Total for Last Year
   if (isset($yearStats[$prevYr]['cnt']) && $yearStats[$prevYr]['cnt']>1)
    {
      if ((isset($roy[$prevYr])) || (isset($poy[$prevYr]))) {$cls='b'.($cnt%2+1);}
       else {$cls='s'.($cnt%2+1);}
      $Yip=$yearStats[$prevYr]['ip'];
      $Yer=$yearStats[$prevYr]['er'];
      $Yha=$yearStats[$prevYr]['ha'];
      $Ybb=$yearStats[$prevYr]['bb'];
      $Yab=$yearStats[$prevYr]['ab'];
      $Yk=$yearStats[$prevYr]['k'];
      $Yhra=$yearStats[$prevYr]['hra'];
      $Ysf=$yearStats[$prevYr]['sf'];

      $Yqs=$yearStats[$prevYr]['qs'];
      $Ygs=$yearStats[$prevYr]['gs'];
      $Ycg=$yearStats[$prevYr]['cg'];
      $Ygb=$yearStats[$prevYr]['gb'];
      $Yfb=$yearStats[$prevYr]['fb'];
      $Ys=$yearStats[$prevYr]['s'];
      $Ysvo=$yearStats[$prevYr]['svo'];

      if ($Yip==0) {$Yera=0;$Ywhip=0;}
       else
       {
          $Yera=9*$Yer/$Yip;
          $Ywhip=($Yha+$Ybb)/$Yip;
       }
      $Ybip=$Yab-$Yk-$Yhra+$Ysf;
      if ($Ybip==0) {$Ybabip=0;}
       else {$Ybabip=($Yha-$Yhra)/$Ybip;}

      $Yera=sprintf("%.2f",$Yera);
      $Ywhip=sprintf("%.2f",$Ywhip);
      $Ybabip=sprintf("%.3f",$Ybabip);
      if ($Ywhip<1) {$Ywhip=strstr($Ywhip,".");}
      if ($Ybabip<1) {$Ybabip=strstr($Ybabip,".");}
      #Expanded
      if ($Yip==0) {$YrPer9=0;$YhPer9=0;$YkPer9=0;$YbbPer9=0;$YhrPer9=0;}
       {
         $YrPer9=9*$Yr/$Yip;
	 $YhPer9=9*$Yha/$Yip;
	 $YkPer9=9*$Yk/$Yip;
	 $YbbPer9=9*$Ybb/$Yip;
	 $YhrPer9=9*$Yhra/$Yip;
       }
      if ($Ybb==0) {$YkPerBB=0;} else {$YkPerBB=$Yk/$Ybb;}
      if ($Ygs==0) {$YqsPct=0;$YcgPct=0;} else {$YqsPct=$Yqs/$Ygs;$YcgPct=$Ycg/$Ygs;}
      if ($Ygb+$Yfb==0) {$YgbPct=0;} else {$YgbPct=$Ygb/($Ygb+$Yfb);}
      if (($Ys+$Ybs)==0) {$YsvPct=0;} else {$YsvPct=$Ys/($Ys+$Ybs);}
      $YrPer9=sprintf("%.2f",$YrPer9);
      $YhPer9=sprintf("%.2f",$YhPer9);
      $YkPer9=sprintf("%.2f",$YkPer9);
      $YbbPer9=sprintf("%.2f",$YbbPer9);
      $YhrPer9=sprintf("%.2f",$YhrPer9);
      $YkPerBB=sprintf("%.2f",$YkPerBB);
      $YqsPct=sprintf("%.1f",100*$YqsPct);
      $YcgPct=sprintf("%.1f",100*$YcgPct);
      $YgbPct=sprintf("%.1f",100*$YgbPct);
      $YsvPct=sprintf("%.1f",100*$YsvPct);

      echo "     <tr class='$cls'>";
      echo "<td class='".$cls."_l'>$prevYr - Tot";
      if (isset($as[$prevYr])) {echo " (AS)";}
      echo "</td>";
      echo "<td>".$yearStats[$prevYr]['age']."</td>";
      echo "<td>".$yearStats[$prevYr]['g']."</td>";
      echo "<td>".$yearStats[$prevYr]['gs']."</td>";
      echo "<td>".$yearStats[$prevYr]['w']."</td>";
      echo "<td>".$yearStats[$prevYr]['l']."</td>";
      echo "<td>".$yearStats[$prevYr]['s']."</td>";
      echo "<td>".$Yera."</td>";
      echo "<td>".sprintf("%.1f",$Yip)."</td>";
      echo "<td>".$yearStats[$prevYr]['ha']."</td>";
      echo "<td>".$yearStats[$prevYr]['r']."</td>";
      echo "<td>".$yearStats[$prevYr]['er']."</td>";
      echo "<td>".$yearStats[$prevYr]['hra']."</td>";
      echo "<td>".$yearStats[$prevYr]['bb']."</td>";
      echo "<td>".$yearStats[$prevYr]['k']."</td>";
      echo "<td>".$yearStats[$prevYr]['hld']."</td>";
      echo "<td>".$yearStats[$prevYr]['cg']."</td>";
      echo "<td>".$yearStats[$prevYr]['sho']."</td>";
      echo "<td>".$Ywhip."</td>";
      echo "<td>".$Ybabip."</td>";
      echo "<td>".sprintf("%.1f",$yearStats[$prevYr]['war'])."</td>";
      if ($noWS!=1)
       {
         $pws=$yearStats[$prevYr]['pws'];
         if ($pws!="") {$pws=sprintf("%.1f",$pws);}
         echo "<td>$pws</td>";
       }
      if (($noPSM!=1)&&($playerPos==1))
       {
         $eraP=$yearStats[$prevYr]['eraPlus'];
         $ceraP=$yearStats[$prevYr]['cERA'];
         $sabrIP=$yearStats[$prevYr]['sabrIP'];
         if (($eraP==0)||($sabrIP==0)) {$eraP="";}
          else
          {
            $eraP=$eraP/$sabrIP;
            $eraP=round($eraP,0);
          }
         if (($ceraP==0)||($sabrIP==0)) {$ceraP="";}
          else
          {
            $ceraP=$ceraP/$sabrIP;
            $ceraP=sprintf("%.2f",$ceraP);
          }
         echo "<td>$eraP</td><td>$ceraP</td>";
       }
      echo "</tr>\n";

      #Expanded
      $expTable.="     <tr class='$cls'>";
      $expTable.="<td class='".$cls."_l'>$prevYr - Tot</td>";
      $expTable.="<td>".$yearStats[$prevYr]['age']."</td>";
      $expTable.="<td>".$yearStats[$prevYr]['bf']."</td>";
      $expTable.="<td>".$yearStats[$prevYr]['pi']."</td>";
      $expTable.="<td>$YrPer9</td>";
      $expTable.="<td>$YhPer9</td>";
      $expTable.="<td>$YkPer9</td>";
      $expTable.="<td>$YbbPer9</td>";
      $expTable.="<td>$YhrPer9</td>";
      $expTable.="<td>$YkPerBB</td>";
      $expTable.="<td>".$yearStats[$prevYr]['qs']."</td>";
      $expTable.="<td>$YqsPct</td>";
      $expTable.="<td>$YcgPct</td>";
      $expTable.="<td>".$yearStats[$prevYr]['gf']."</td>";
      $expTable.="<td>$YgbPct</td>";
      $expTable.="<td>".$yearStats[$prevYr]['wp']."</td>";
      $expTable.="<td>".$yearStats[$prevYr]['bk']."</td>";
      $expTable.="<td>".$yearStats[$prevYr]['bs']."</td>";
      $expTable.="<td>$YsvPct</td>";
      $expTable.="<td>".$yearStats[$prevYr]['svo']."</td>";
      $expTable.="</tr>\n";
    }

   ## Display Career Totals
   echo "     <tfoot>";
   $expTable.="     <tfoot>";
   #per team
   foreach ($playedFor as $tid => $val)
    {
      $g=$teamStats[$tid]['g'];
      $gs=$teamStats[$tid]['gs'];
      $w=$teamStats[$tid]['w'];
      $l=$teamStats[$tid]['l'];
      $s=$teamStats[$tid]['s'];
      $ip=$teamStats[$tid]['ip'];
      $ha=$teamStats[$tid]['ha'];
      $r=$teamStats[$tid]['r'];
      $er=$teamStats[$tid]['er'];
      $hra=$teamStats[$tid]['hra'];
      $bb=$teamStats[$tid]['bb'];
      $k=$teamStats[$tid]['k'];
      $hld=$teamStats[$tid]['hld'];
      $cg=$teamStats[$tid]['cg'];
      $sho=$teamStats[$tid]['sho'];
      $ab=$teamStats[$tid]['ab'];
      $sf=$teamStats[$tid]['sf'];
      $war=$teamStats[$tid]['war'];
      #Expanded
      $bf=$teamStats[$tid]['bf'];
      $pi=$teamStats[$tid]['pi'];
      $qs=$teamStats[$tid]['qs'];
      $gf=$teamStats[$tid]['gf'];
      $gb=$teamStats[$tid]['gb'];
      $fb=$teamStats[$tid]['fb'];
      $wp=$teamStats[$tid]['wp'];
      $bk=$teamStats[$tid]['bk'];
      $svo=$teamStats[$tid]['svo'];
      $bs=$teamStats[$tid]['bs'];

      if ($ip==0) {$era=0;$whip=0;}
       else
       {
         $era=9*$er/$ip;
         $whip=($ha+$bb)/$ip;
       }
      $bip=$ab-$k-$hra+$sf;
      if ($bip==0) {$babip=0;}
       else {$babip=($ha-$hra)/$bip;}

      $era=sprintf("%.2f",$era);
      $whip=sprintf("%.2f",$whip);
      $babip=sprintf("%.3f",$babip);
      if ($whip<1) {$whip=strstr($whip,".");}
      if ($babip<1) {$babip=strstr($babip,".");}
      $war=sprintf("%.1f",$war);
      #Expanded
      if ($ip==0) {$rPer9=0;$hPer9=0;$kPer9=0;$bbPer9=0;$hrPer9=0;}
       {
          $rPer9=9*$r/$ip;
	  $hPer9=9*$ha/$ip;
    	  $kPer9=9*$k/$ip;
	  $bbPer9=9*$bb/$ip;
	  $hrPer9=9*$hra/$ip;
       }
      if ($bb==0) {$kPerBB=0;} else {$kPerBB=$k/$bb;}
      if ($gs==0) {$qsPct=0;$cgPct=0;} else {$qsPct=$qs/$gs;$cgPct=$cg/$gs;}
      if ($gb+$fb==0) {$gbPct=0;} else {$gbPct=$gb/($gb+$fb);}
      if (($s+$bs)==0) {$svPct=0;} else {$svPct=$s/($s+$bs);}
      $rPer9=sprintf("%.2f",$rPer9);
      $hPer9=sprintf("%.2f",$hPer9);
      $kPer9=sprintf("%.2f",$kPer9);
      $bbPer9=sprintf("%.2f",$bbPer9);
      $hrPer9=sprintf("%.2f",$hrPer9);
      $kPerBB=sprintf("%.2f",$kPerBB);
      $qsPct=sprintf("%.1f",100*$qsPct);
      $cgPct=sprintf("%.1f",100*$cgPct);
      $gbPct=sprintf("%.1f",100*$gbPct);
      $svPct=sprintf("%.1f",100*$svPct);
      $ip=sprintf("%.1f",$ip);

      $poys=(isset($teamStats[$tid]['poy']))? $teamStats[$tid]['poy'] : 0;
      $asX=(isset($teamStats[$tid]['as']))? $teamStats[$tid]['as'] : 0;
      $awTxt="";
      if ($poys>1) {$awTxt="$poys POY's";}
      if ($poys==1) {$awTxt="$poys POY";}
      if (($asX>0) && ($awTxt!="")) {$awTxt.=", ";}
      if ($asX>0) {$awTxt.=$asX."xAS";}
      if ($awTxt!="") {$awTxt=" (".$awTxt.")";}

      echo "     <tr class='headline'>";
      echo "<td class='hsc2_l' colspan=2>w/ ".$teams[$tid][$year].$awTxt."</td>";
      echo "<td class='hsc2'>$g</td>";
      echo "<td class='hsc2'>$gs</td>";
      echo "<td class='hsc2'>$w</td>";
      echo "<td class='hsc2'>$l</td>";
      echo "<td class='hsc2'>$s</td>";
      echo "<td class='hsc2'>$era</td>";
      echo "<td class='hsc2'>$ip</td>";
      echo "<td class='hsc2'>$ha</td>";
      echo "<td class='hsc2'>$r</td>";
      echo "<td class='hsc2'>$er</td>";
      echo "<td class='hsc2'>$hra</td>";
      echo "<td class='hsc2'>$bb</td>";
      echo "<td class='hsc2'>$k</td>";
      echo "<td class='hsc2'>$hld</td>";
      echo "<td class='hsc2'>$cg</td>";
      echo "<td class='hsc2'>$sho</td>";
      echo "<td class='hsc2'>$whip</td>";
      echo "<td class='hsc2'>$babip</td>";
      echo "<td class='hsc2'>$war</td>";
      if ($noWS!=1)
       {
         $pws=$teamStats[$tid]['pws'];
         if ($pws!="") {$pws=sprintf("%.1f",$pws);}
         echo "<td class='hsc2'>$pws</td>";
       }
      if (($noPSM!=1)&&($playerPos==1))
       {
	 $eraP=$teamStats[$tid]['eraPlus'];
	 $ceraP=$teamStats[$tid]['cERA'];
	 $ip=$teamStats[$tid]['sabrIP'];
         if (($eraP==0)||($ip==0)) {$eraP="";}
	  else
	  {
	    $eraP=$eraP/$ip;
            $eraP=round($eraP,0);
	  }
         if (($ceraP==0)||($ip==0)) {$ceraP="";}
	  else
	  {
	    $ceraP=$ceraP/$ip;
            $ceraP=sprintf("%.2f",$ceraP);
	  }
         echo "<td class='hsc2'>$eraP</td><td class='hsc2'>$ceraP</td>";
       }
      echo "</tr>\n";

      #Expanded
      $expTable.="     <tr class='headline'>";
      $expTable.="<td class='hsc2_l' colspan=2>w/ ".$teams[$tid][$year].$awTxt."</td>";
      $expTable.="<td class='hsc2'>$bf</td>";
      $expTable.="<td class='hsc2'>$pi</td>";
      $expTable.="<td class='hsc2'>$rPer9</td>";
      $expTable.="<td class='hsc2'>$hPer9</td>";
      $expTable.="<td class='hsc2'>$kPer9</td>";
      $expTable.="<td class='hsc2'>$bbPer9</td>";
      $expTable.="<td class='hsc2'>$hrPer9</td>";
      $expTable.="<td class='hsc2'>$kPerBB</td>";
      $expTable.="<td class='hsc2'>$qs</td>";
      $expTable.="<td class='hsc2'>$qsPct</td>";
      $expTable.="<td class='hsc2'>$cgPct</td>";
      $expTable.="<td class='hsc2'>$gf</td>";
      $expTable.="<td class='hsc2'>$gbPct</td>";
      $expTable.="<td class='hsc2'>$wp</td>";
      $expTable.="<td class='hsc2'>$bk</td>";
      $expTable.="<td class='hsc2'>$bs</td>";
      $expTable.="<td class='hsc2'>$svPct</td>";
      $expTable.="<td class='hsc2'>$svo</td>";
      $expTable.="</tr>\n";

    }
   #for career
   $Twar=sprintf("%.1f",$Twar);
   if ($Tip==0) {$era=0;$whip=0;}
    else
    {
      $era=9*$Ter/$Tip;
      $whip=($Tha+$Tbb)/$Tip;
    }
   $bip=$Tab-$Tk-$Thra+$Tsf;
   if ($bip==0) {$babip=0;}
    else {$babip=($Tha-$Thra)/$bip;}

   $era=sprintf("%.2f",$era);
   $whip=sprintf("%.2f",$whip);
   $babip=sprintf("%.3f",$babip);
   if ($whip<1) {$whip=strstr($whip,".");}
   if ($babip<1) {$babip=strstr($babip,".");}
   $war=sprintf("%.1f",$war);
   #Expanded
   if ($Tip==0) {$rPer9=0;$hPer9=0;$kPer9=0;$bbPer9=0;$hrPer9=0;}
    {
      $rPer9=9*$Tr/$Tip;
      $hPer9=9*$Tha/$Tip;
      $kPer9=9*$Tk/$Tip;
      $bbPer9=9*$Tbb/$Tip;
      $hrPer9=9*$Thra/$Tip;
    }
   if ($Tbb==0) {$kPerBB=0;} else {$kPerBB=$Tk/$Tbb;}
   if ($Tgs==0) {$qsPct=0;$cgPct=0;} else {$qsPct=$Tqs/$Tgs;$cgPct=$Tcg/$Tgs;}
   if ($Tgb+$Tfb==0) {$gbPct=0;} else {$gbPct=$Tgb/($Tgb+$Tfb);}
   if (($Ts+$Tbs)==0) {$svPct=0;} else {$svPct=$Ts/($Ts+$Tbs);}
   $rPer9=sprintf("%.2f",$rPer9);
   $hPer9=sprintf("%.2f",$hPer9);
   $kPer9=sprintf("%.2f",$kPer9);
   $bbPer9=sprintf("%.2f",$bbPer9);
   $hrPer9=sprintf("%.2f",$hrPer9);
   $kPerBB=sprintf("%.2f",$kPerBB);
   $qsPct=sprintf("%.1f",100*$qsPct);
   $cgPct=sprintf("%.1f",100*$cgPct);
   $gbPct=sprintf("%.1f",100*$gbPct);
   $svPct=sprintf("%.1f",100*$svPct);
   $Tip=sprintf("%.1f",$Tip);

   echo "     <tr class='headline'>";
   echo "<td class='hsc2_l'>Totals</td>";
   echo "<td class='hsc2'>&nbsp;</td>";
   echo "<td class='hsc2'>$Tg</td>";
   echo "<td class='hsc2'>$Tgs</td>";
   echo "<td class='hsc2'>$Tw</td>";
   echo "<td class='hsc2'>$Tl</td>";
   echo "<td class='hsc2'>$Ts</td>";
   echo "<td class='hsc2'>$era</td>";
   echo "<td class='hsc2'>$Tip</td>";
   echo "<td class='hsc2'>$Tha</td>";
   echo "<td class='hsc2'>$Tr</td>";
   echo "<td class='hsc2'>$Ter</td>";
   echo "<td class='hsc2'>$Thra</td>";
   echo "<td class='hsc2'>$Tbb</td>";
   echo "<td class='hsc2'>$Tk</td>";
   echo "<td class='hsc2'>$Thld</td>";
   echo "<td class='hsc2'>$Tcg</td>";
   echo "<td class='hsc2'>$Tsho</td>";
   echo "<td class='hsc2'>$whip</td>";
   echo "<td class='hsc2'>$babip</td>";
   echo "<td class='hsc2'>$Twar</td>";

   if ($noWS!=1)
    {
      $Tpws=sprintf("%.1f",$Tpws);
      echo "<td class='hsc2'>$Tpws</td>";
    }
   if (($noPSM!=1)&&($playerPos==1))
    {
      $eraP=$TeraP;
      $ceraP=$TceraP;
      $ip=$TsabrIP;
      if (($eraP==0)||($ip==0)) {$eraP="";}
       else
       {
         $eraP=$eraP/$ip;
         $eraP=round($eraP,0);
       }
      if (($ceraP==0)||($ip==0)) {$ceraP="";}
       else
       {
         $ceraP=$ceraP/$ip;
         $ceraP=sprintf("%.2f",$ceraP);
       }
      echo "<td class='hsc2'>$eraP</td><td class='hsc2'>$ceraP</td>";
    }
   echo "</tr>";
   echo "     </tfoot>\n";

   #Expanded
   $expTable.="     <tr class='headline'>";
   $expTable.="<td class='hsc2_l' colspan=2>Totals</td>";
   $expTable.="<td class='hsc2'>$Tbf</td>";
   $expTable.="<td class='hsc2'>$Tpi</td>";
   $expTable.="<td class='hsc2'>$rPer9</td>";
   $expTable.="<td class='hsc2'>$hPer9</td>";
   $expTable.="<td class='hsc2'>$kPer9</td>";
   $expTable.="<td class='hsc2'>$bbPer9</td>";
   $expTable.="<td class='hsc2'>$hrPer9</td>";
   $expTable.="<td class='hsc2'>$kPerBB</td>";
   $expTable.="<td class='hsc2'>$Tqs</td>";
   $expTable.="<td class='hsc2'>$qsPct</td>";
   $expTable.="<td class='hsc2'>$cgPct</td>";
   $expTable.="<td class='hsc2'>$Tgf</td>";
   $expTable.="<td class='hsc2'>$gbPct</td>";
   $expTable.="<td class='hsc2'>$Twp</td>";
   $expTable.="<td class='hsc2'>$Tbk</td>";
   $expTable.="<td class='hsc2'>$Tbs</td>";
   $expTable.="<td class='hsc2'>$svPct</td>";
   $expTable.="<td class='hsc2'>$Tsvo</td>";
   $expTable.="</tr>\n";
   $expTable.="     </tfoot>\n";

   ## Close off pitching stats
   echo "    </table>\n";
   echo "    </td></tr></table>\n";
   echo "   </div>\n";
   echo "  </tr></td>\n";

   ##### Display Expanded Pitching Stats
   echo "  <tr><td>\n";
   echo "   <div class='tablebox'>\n";
   echo "    <table cellpadding=0 cellspacing=0><tr class='title'><td colspan=22>Expanded Pitching Stats</td></tr><tr><td>\n";
   echo "    <table cellpadding=2 cellspacing=0 class='sortable' width='910px'>\n";
   echo "     <thead><tr class='headline'>";
   echo "<td class='hsc2_l'>Year/Team</td>";
   echo "<td class='hsc2'>Age</td>";
   echo "<td class='hsc2'>BF</td>";
   echo "<td class='hsc2'>PI</td>";
   echo "<td class='hsc2'>R/9</td>";
   echo "<td class='hsc2'>H/9</td>";
   echo "<td class='hsc2'>K/9</td>";
   echo "<td class='hsc2'>BB/9</td>";
   echo "<td class='hsc2'>HR/9</td>";
   echo "<td class='hsc2'>K/BB</td>";
   echo "<td class='hsc2'>QS</td>";
   echo "<td class='hsc2'>QS%</td>";
   echo "<td class='hsc2'>CG%</td>";
   echo "<td class='hsc2'>GF</td>";
   echo "<td class='hsc2'>GB%</td>";
   echo "<td class='hsc2'>WP</td>";
   echo "<td class='hsc2'>BK</td>";
   echo "<td class='hsc2'>BS</td>";
   echo "<td class='hsc2'>SV%</td>";
   echo "<td class='hsc2'>SVO</td>";
   echo "</tr></thead>\n";
   echo $expTable;
   echo "    </table>\n";
   echo "    </td></tr></table>\n";
   echo "   </div>\n";
   echo "  </tr></td>\n";
 }
 ##### End Stats Tables #####
echo " </table>\n";
echo "</div>\n";
}
?>


<br class="clear" />
