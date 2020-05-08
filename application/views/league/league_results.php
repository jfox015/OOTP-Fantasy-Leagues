	<script type="text/javascript">
    $(document).ready(function(){		   
		$('a[rel=game_nav]').click(function() {
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/results/id/<?php echo($league_id); ?>/period_id/<?php echo($curr_period['id']); ?>/game_id/'+this.id;
			return false;
		});
	});
    </script>
    <div id="subPage">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
		
		<?php if (isset($avail_periods) && sizeof($avail_periods) > 0) {  ?>
            <?php echo("<b>Period: </b>");
            foreach($avail_periods as $period) { 
                if ($period != $curr_period['id']) {
                    echo(anchor('/league/results/id/'.$league_id."/period_id/".$period,$period));
                } else {
                    echo($period);
                }
                echo("&nbsp;");
            } 
        }
        ?>

        <?php 
        $winners = array();
        if (isset($games) && sizeof($games) > 0) { ?>
        <div align="center" style="width:800px;margin:auto 0;">
            <div class='textbox'>
            <table cellpadding="0" cellspacing="0" border="0" style="width:800px;">
            <tr class='title'>
                <td style='padding:6px' colspan="8">Games</td>
            </tr>
            <tr>
                <?php 
                $maxCols = 6;
                $rowCount = 1;
                if (sizeof($games) > $maxCols) {
                    $rowCount = (sizeof($games) / $maxCols);
                }
                $colsDrawn = 0;
                $rowsDrawn = 0;
                foreach($games as $game_id => $data) { ?>
                <td width="16%">
                <a href="#" rel="game_nav" id="<?php echo($game_id); ?>"> 
                <table cellpadding="6" cellspacing="0" border="0" style="width:100%; border:1px solid white; background:#000000;">
                <?php 
                $homeColor = "#fff";
                $awayColor = "#fff";
                if ($data['home_team_score'] > $data['away_team_score']) {
                    array_push($winners, $data['home_team_id']);
                    $homeColor = "#E6EFC2";
                    $awayColor = "#FBE3E4";
                } else if ($data['home_team_score'] < $data['away_team_score']) {
                    array_push($winners, $data['away_team_id']);
                    $homeColor = "#FBE3E4";
                    $awayColor = "#E6EFC2";
                }
                if ($data['home_team_id'] == $owned_team) {
                    $homeColor = "#FCB97C";
                } else if ($data['away_team_id'] == $owned_team) {
                   $awayColor = "#FCB97C";
                }
                ?>
                <tr class='slg_1'<?php if ($data['home_team_score'] > $data['away_team_score']) { echo(' style="font-weight:bold;"'); } ?>>
                    <td height="42" class="hsc2_l" style="color:<?php echo($homeColor); ?>"><?php echo($data['home_team_name']); ?></td>
                    <td class="hsc2_r" style="color:<?php echo($homeColor); ?>;"><?php echo($data['home_team_score']); ?></td>
                </tr>
                <tr class='slg_1'<?php if ($data['away_team_score'] > $data['home_team_score']) { echo(' style="font-weight:bold;"'); } ?>>
                    <td height="42" class="hsc2_l" style="color:<?php echo($awayColor); ?>;"><?php echo($data['away_team_name']); ?></td>
                    <td class="hsc2_r" style="color:<?php echo($awayColor); ?>;"><?php echo($data['away_team_score']); ?></td>
                </tr>
                </table>
                </a>
                </td>
                <?php 
                    $colsDrawn++;
                    if ($colsDrawn >= $maxCols) {
                         $colsDrawn = 0;
						 $rowsDrawn++;
						 echo('</tr><tr>');
                    }
                } 
				if ($colsDrawn < $maxCols) {
					for ($i = $colsDrawn; $i < $maxCols; $i++) {
						echo('<td></td>');
					}
				}
				?>
            </tr>
            </table>
            </div>
        </div>
        <?php } ?>
        <br clear= "all"/>
        <?php if (isset($game_data) && sizeof($game_data) > 0) { ?>
        <div style="width:925px; margin-top:12px;">
            <?php 
			$gameCount= 0;
			foreach($game_data as $type => $team_data) {
                $left = 0;
				if ($gameCount == 1) { $left = 455; }
			?>
            <div class="textbox" style="width:420px; clear:none;margin-right:12px;">
                <table cellpadding="5" cellspacing="0" width="100%">
                <?php 
                $linkColor ="#000";            
                foreach($winners as $winner_Id) {
                    if ($winner_Id == $team_data['id']) {
                        $linkColor = "#009b77";
                        break;
                    }
                }
				if ($team_data['id'] == $owned_team) {
                    $linkColor = "#fc9b7e";
                }
                ?>
                <tr class='title'>
                    <td colspan='4' class='lhl'><?php echo(anchor('/team/info/'.$team_data['id'], $team_data['team_name'],['style'=>'font-weight:bold; color:'.$linkColor.';','class' => 'teamLink-link'])); ?></td>
                </tr> 
                <tr class='headline'>
                    <td class='hsc2_c' width="5%">POS</td>
                    <td class='hsc2_c' width="35%">Player</td>
                    <td class='hsc2_c' width="55%">Stats</td>
                    <td class='hsc2_c' width="5%">Total</td>
                </tr>
                <tr class='headline'>
                    <td colspan='4'>Active Players</td>
                </tr>
                <?php 
				
				$active_score = 0;
				if (isset($team_data['players_active']) && sizeof($team_data['players_active']) > 0) {
					$rowcount = 0;
					foreach($team_data['players_active'] as $player_id => $playerData) { 
					if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }
                    ?>
                <tr style="background-color:<?php echo($color); ?>">
                    <td class='hsc2_l'><?php echo(get_pos($playerData['position'])); ?></td>
                    <td class='hsc2_l'><?php 
					echo anchor('/players/info/league_id/'.$league_id.'/player_id/'.$player_id,$playerData['name']); 
					// INJURY STATUS
					$injStatus = "";
					if ($playerData['injury_is_injured'] == 1) {
						$injStatus = makeInjuryStatusString($playerData);
					}
					if (!empty($injStatus)) { ?>
					<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/red_cross.gif" width="7" height="7" align="absmiddle" 
					alt="<?php echo($injStatus); ?> " title="<?php echo($injStatus); ?>" />
					<?php } ?>
                    </td>
                    <td class='hsc2_l'><?php echo($playerData['stats']); ?></td>
                    <td class='hsc2_l'><?php echo($playerData['total']); $active_score += intval($playerData['total']);  ?></td>
                </tr>
                
                    <?php
                    $rowcount++;
                    } // END foreach
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="4">No Players were Found</td>
                </tr>
                <?php 
                } // END if
				?>
				<tr>
                	<td class="hsc2_l" colspan="2"><strong>Active Total</strong></td>
                    <td class="hsc2_l"><?php 
					if (isset($team_data['stats_active'])) { 
					print($team_data['stats_active']); 
					} ?>
					</td>
					<td class="hsc2_l"><?php echo($active_score); ?></td>
                </tr>                
                <tr class='headline'>
                    <td colspan='4'>Reserve Players</td>
                </tr>
                <?php 
				$reserve_score = 0;
				if (isset($team_data['players_reserve']) && sizeof($team_data['players_reserve']) > 0) {
					$rowcount = 0;
					foreach($team_data['players_reserve'] as $player_id => $playerData) { 
					if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }
                    ?>
                <tr style="background-color:<?php echo($color); ?>">
                    <td class='hsc2_l'><?php echo(get_pos($playerData['position'])); ?></td>
                    <td class='hsc2_l'><?php
                    echo anchor('/players/info/'.$player_id,$playerData['name']); 
					if (isset($playerData['is_injured']) && $playerData['is_injured']) {?>
                    <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/red_cross.gif" width="7" height="7" align="absmiddle" 
                    alt="<?php echo($playerData['dl_left']); ?> Days Left" title="<?php echo($playerData['dl_left']); ?> Days Left" />
                    <?php } ?> <td class='hsc2_l'><?php echo($playerData['stats']); ?></td>
                    <td class='hsc2_l'><?php echo($playerData['total']); $reserve_score += intval($playerData['total']); ?></td>
                </tr>
                    <?php
                    $rowcount++;
                    } // END foreach
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="2">No Players were Found</td>
                </tr>
                <?php 
                } // END if
				?>
                <tr>
                	<td class="hsc2_l" colspan="2"><strong>Reserve Total</strong>
					<td class="hsc2_l"><?php 
					if (isset($team_data['stats_reserve'])) { 
					print($team_data['stats_reserve']); 
					} ?>
					</td>
					<td class="hsc2_l"><?php echo($reserve_score); ?></td>
                </tr>
				
                </table>
                <br clear="all" />
            </div>  <!-- end batting stat div -->
            <?php $gameCount++;
			}?>
             <br clear="all" />
        </div>
		<?php } ?>	
         <br clear="all" />
    </div>
    <p /><br />