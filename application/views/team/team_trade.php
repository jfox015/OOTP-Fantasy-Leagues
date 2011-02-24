<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>

<div id="single-column">
        <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        
        <h2><?php echo($team_name); ?></h2>
            <p /><br />
            <b>Players  on roster:</b>
            <br />
    </div>
    
	<div id="center-column">
        <div class="listPicker">
            
            <div id="activeStatusBox"><div id="activeStatus"></div></div>
            <div id="activeList" class="listPickerBox">
            	<?php
				$itemCount = sizeof($players);
				$colLimit = (round($itemCount / 3)) + 1;
				$columnsDrawn = 1;
				$countDrawn = 0;
				$countPerColumn = 0;
				foreach ($players as $player) {
				if ($countPerColumn == 0) {
				?>
            	<div id="listColumn<?php echo($columnsDrawn); ?>" class="listcolumn">
                	<ul>
                <?php } ?>
                    	<li><img alt="Send in Trade" title="Send in Trade" rel="itemRemove" id="<?php echo($player['id']); ?>" 
                        src="<?php echo($config['fantasy_web_root']); ?>images/icons/arrow_right.png" width="16" 
                        height="16" align="absmiddle" border="0" /> 
                        <?php if ($player['player_position'] == 1) {
							$pos = $player['player_role'];
						} else {
							$pos = $player['player_position'];
						}
						echo(get_pos($pos)); 
						?>
                        <a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/<?php echo($player['id']); ?>"><?php echo($player['player_name']); ?></a></li>
                <?php 	$countDrawn++;
					$countPerColumn++;
					if ($countPerColumn == $colLimit || $countDrawn == $itemCount) { ?>
                    </ul>
                </div>
                <?php 	
					$countPerColumn = 0;
					$columnsDrawn++;
					} 
				}
				if ($countDrawn == 0) { ?>
                <div id="listColumn1" class="emptyList">
                	<ul>
                    	<li>No player were found for this team</li>
                    </ul>
                </div>
                <?php 
				}
			?>
            </div>
            <p />&nbsp;<br clear="all" /><br />
            <b>Add a Player</b>
            <br />
            <div id="pickStatusBox"><div id="pickStatus"></div></div>
            
            <div>
            	<div id="optionsBar">
                	<div id="options">
                    <?php if (isset($fantasy_teams) && sizeof($fantasy_teams) > 0 ) {?>
                    <div style="width:95%;text-align:right;float:left;">
                    <label for="teams" style="margin:none;min-width:auto;width:auto;">Fantasy Teams:</label> 
                    <select id="teams" style="margin:none;min-width:auto;clear:none;width:auto;">
                        <?php  
                        foreach($fantasy_teams as $id => $teamName) {
                           	if ($id != $team_id) {
								echo('<option value="'.$id.'"');
								if ($id == $team_id2) { echo(' selected="selected"'); }
								echo('>'.$teamName.'</option>');
							}
                        }
                        ?>
                    </select>
                    </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
            <div id="pickList" class="listPickerBox">
                <?php
				if (isset($formatted_stats) && sizeof($formatted_stats)){
					echo($formatted_stats);						 
				}
				?>
             </div>
		</div>
	</div>
    
            
            <div id="right-column">
            	<div id="listStatusBox"><div id="listStatus"></div></div>
            	<div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="265px">
                <tr class='title'>
                    <td style='padding:3px'>Trade Summary</td>
                </tr>
                <tr class='headline'>
                    <td style='padding:6px'>Players to Recieve</td>
                </tr>
                <tr>
                    <td style='padding:3px'>
                    <table cellpadding="2" cellspacing="1" border="0" style="width:100%;">
                    <tr>
                    	<td>
                        <div id="playersToAdd">
                        <table>
                        <tr align=left class="s1_2">
                            <td width="40%">No players added yet</td>
                        </tr>
                        </table>
                        </div>
                        </td>
                    </tr>
                    </table>
                    </td>
                </tr>
                <tr class='headline'>
                    <td style='padding:6px'>Players to Send</td>
                </tr>
                <tr>
                    <td style='padding:3px'>
                    <table cellpadding="2" cellspacing="1" border="0" style="width:100%;">
                    <tr>
                    	<td>
                        <div id="playersToDrop">
                        <table>
                        <tr align=left class="s1_2">
                            <td width="40%">No players added yet</td>
                        </tr>
                        </table>
                        </div>
                        </td>
                    </tr>
                    </table>
                    </td>
                </tr>
                <tr>
                    <td style='padding:6px'>
                    <div class="button_bar" style="text-align:right;">
                    	<input type='button' id="btnClear" class="button" value='Clear' style="display:none;float:left;margin-right:8px;" />
						<input type='button' id="btnReview" class="button" value='Review Trade' style="display:none;float:left;" />
                        <input type='button' id="btnSubmit" class="button" value='Offer Trade' style="display:none;float:left;" />
                    </div></td>
                </tr>
                
                </table>
                </div>
                
                <?php 
				/*-------------------------------------------------------
				/	PENDING TRADES
				/-----------------------------------------------------*/
				if (isset($trades_pending) && sizeof($trades_pending) > 0) { ?>
                <div class='textbox'>
                <table cellpadding="3" cellspacing="1" border="0" width="265px">
                <tr class='title'>
                    <td style='padding:3px' colspan="3">Pending Tradess</td>
                </tr>
                <tr class='headline'>
					<td width="60%">Player</td>
                    <td width="30%">In Period</td>
                    <td width="10%">&nbsp;</td>
                </tr>
                <?php
				$rowNum = 0;
				if (sizeof($trades_pending) > 0) {
					foreach ($trades_pending as $tradeData) { 
						$bg = (($rowNum % 2) == 0) ? '#fff' : '#E0E0E0'; ?>
                <tr bgcolor="<?php echo($bg); ?>">
					<td align="left"><?php 
					$pos = -1;
					if ($tradeData['position'] == 1) {
						if ($tradeData['role'] == 13) {
							$pos = 12;
						} else {
							$pos = $tradeData['role'];
						}
					} else {
						if ($tradeData['position'] == 7 || $tradeData['position'] == 8 || $tradeData['position'] == 9) {
							$pos = 20;
						} else {
							$pos = $tradeData['position'];
						}
					}
					
					echo(get_pos($pos)." ".anchor('/players/info/league_id/'.$league_id.'/player_id/'.$tradeData['player_id'],$tradeData['player_name'])); ?></td>
                	<td align="center"><?php echo($tradeData['waiver_period']); ?></td>
                    <td align="center">
					<?php 
                    echo( anchor('/team/removeClaim/team_id/'.$team_id.'/id/'.$tradeData['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
                </tr>
                <?php $rowNum++;
				} 
				} else { ?>
				<tr class="s1_1">
					<td colspan="2">No claims were found.</td>
                </tr>
				<?php } 	?>
                </table>
                </div>
            	<?php } ?>
            </div>
    <p /><br />