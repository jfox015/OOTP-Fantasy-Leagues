    <script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){		   

		$('select#teams').change(function(){
			var src = $('select#teams').val();
			
			if (src !== ' ' && src !== undefined)
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/stats/' + src;
		});
		var val = $('select#stat_source').val();
		console.log(val);
		if (val == "score_period") {
			$('select#sp_id').show();
		} else {
			$('select#sp_id').hide();
		}
		$('select#stat_source').change(function(){
			var src = $('select#stat_source').val();
			if (src == "score_period") {
				$('select#sp_id').show();
			} else {
				$('select#sp_id').hide();
			}
		});
	});
	</script>
    <div id="subPage">
        <div class="top-bar"> <div class="top-bar"><h1><?php
		if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
			$avatar = PATH_TEAMS_AVATARS.$thisItem['avatar'];
		} else {
			$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
		}
		?>
		<img src="<?php echo($avatar); ?>" width="48" height="48" border="0" align="absmiddle" />
		&nbsp;&nbsp;<?php echo($thisItem['teamname']." ".$thisItem['teamnick']); ?> Team Stats</h1></div>

		<div id="content">
			
            <?php
			if (isset($formatted_stats) && sizeof($formatted_stats) > 0) { ?>
				
			<div style="display:block;width:98%;position:relative; clear:right;">
				
				<?php
				##### Filters #####
				echo "<div class='textbox'>";
				//echo("Stat source =".$stat_source."<br />");
				echo ' <table cellspacing="0" cellpadding="2" border="0">';
				echo "  <tr class='title'><td colspan=11  height='17'>Filters</td></tr>";
				echo "  <form method='post' id='filterform' action='".$config['fantasy_web_root']."team/stats' class='inline'>";
				echo "   <tr>";
				
				// STAT SOURCE DROP DOWN
				echo '    <td class="formLabel">Stats Source:</td>';
				echo "     <td>";
				echo "      <select name='stat_source' id='stat_source'>";
				// FULL YEAR STATS
				echo '      <option value="full_year"';
				if ($stat_source =="full_year" || $stat_source == '' || $stat_source === false) { echo " selected='selected'"; }
				echo '      >Season Scoring</option>\n';
				echo '      <option value="team_stats"';
				if ($stat_source =="team_stats") { echo " selected='selected'"; }
				echo '      >Team Scoring</option>\n';
				if (isset($scoring_periods) && sizeof($scoring_periods) > 0) {
				echo '      <option value="score_period"';
				if ($stat_source =="score_period") { echo " selected='selected'"; }
				echo '      >Scoring Period</option>\n';
				} // END if
				
				echo '      <option value="3yr_avg"';
				if ($stat_source =="3yr_avg") { echo " selected='selected'"; }
				echo '      >Three Year Avg</option>\n';
				echo "      </select>&nbsp;&nbsp;";
				if (isset($scoring_periods) && sizeof($scoring_periods) > 0) {
					echo "      <select name='sp_id' id='sp_id' style='disply:none'>";
					foreach ($scoring_periods as $scoring_periods) {
						echo('<option value="sp_'.$scoring_periods['id'].'"');
						if ($sp_id=="sp_".$scoring_periods['id']) { echo " selected='selected'"; }
						echo ('>Period '.$scoring_periods['id'].'</option>');
					}
					echo "      </select>&nbsp;&nbsp;";
				 }
				echo "     </td>";
				
				## Close Form
				echo "    <td align='right'>\n";
				echo "     <input type='hidden' name='team_id' value='".$team_id."' />\n";
				echo "     <input type='submit' class='submitButton' value='Go' />\n";
				echo "    </td>\n";
				echo "   </tr>\n";
				echo "  </form>";
				echo " </table>";
				  
				echo "</div>"; 	
				?>
				
				<?php if (isset($thisItem['fantasy_teams']) && sizeof($thisItem['fantasy_teams']) > 0 ) {?>
				<div style="width:48%;text-align:right;float:left;">
				<label for="teams" style="min-width:225px;">Fantasy Teams:</label> 
				<select id="teams" style="clear:none;">
					<?php  
					foreach($thisItem['fantasy_teams'] as $id => $teamName) {
						echo('<option value="'.$id.'"');
						if ($id == $thisItem['team_id']) { echo(' selected="selected"'); }
						echo('>'.$teamName.'</option>');
					}
					?>
				</select>
				</div>
				<?php } ?>
			</div>
            <?php
			
				$types = array('batters','pitchers');
				foreach($types as $player_type) {
					if (isset($formatted_stats[$player_type]) && !empty($formatted_stats[$player_type])) { ?>
                        <div class="textbox" style="width:915px;">
                            <!-- HEADER -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="border:0px">
                        <tr class="title">
                            <td height="17" style="padding:6px;"><?php echo($title[$player_type]); ?> Stats</td>
                         </tr>
                         </table>
                        <?php
                        if (isset($formatted_stats[$player_type])){
                            echo($formatted_stats[$player_type]);						 
                        }
						?>
                        </div>
                        <?php
					}
				}
			} else if (isset($message) && !empty($message)) { ?>
				<div style="display:block;width:98%;position:relative; clear:right; float:left;">
				<?php
					$messageType = isset($messageType) ? $messageType : "";
					echo('<span class="'.$messageType.'">'.$message.'</span>');
				?>
				</div>
			<?php 
			}
			?>
		</div>
    </div>
    </div>
    <p /><br />