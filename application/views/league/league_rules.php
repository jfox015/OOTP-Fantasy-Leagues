	<script type="text/javascript">
    $(document).ready(function(){		   
		
	});
    </script>
    <div id="subPage">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <div id="content">
                <!-- BEGIN RIGHT COLUMN -->
            <div id="metaColumn"> 
				<?php if (isset($rosters) && sizeof($rosters) > 0) { ?>
				<div class='textbox'>
			    <table cellpadding="0" cellspacing="0" border="0" style="width:255px;">
			    <tr class='title'>
			    	<td style='padding:6px' colspan="2">Roster Restrictions</td>
			    </tr>
			    <tr>
			    	<td>
					<table cellpadding="2" cellspacing="0" border="0" style="width:100%;">
				    <tr class='headline'>
				    	<td width="35%">Positon</td>
						<td width="35%" align="center">Min</td>
						<td width="35%" align="center">Max</td>
					</tr>
					<?php 
					$rowCount = 0;
					foreach($rosters as $pos => $data) { 
						
						if ($pos < 100) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
				    	<td class="hsc2_l"><?php echo(get_pos($pos)); ?></td>
						<td class="hsc2_c" align="center"><?php echo($data['active_min']); ?></td>
						<td class="hsc2_c" align="center"><?php echo($data['active_max']); ?></td>
					</tr>
						<?php
						$rowCount++;
						} 
					} 
					?>
                    <tr class='headline'>
				    	<td width="35%">Positon</td>
						<td width="35%" align="center">Min</td>
						<td width="35%" align="center">Max</td>
					</tr>
                    <?php
					$rosterTotals = $rosters;
					asort($rosterTotals);
					$rowCount = 0;
					foreach($rosterTotals as $pos => $data) { 
						if ($pos >= 100) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
				    	<td class="hsc2_l"><?php 
						$label= '';
						switch($pos) {
							case 100: 
								$label = "Active";
								break;
							case 101: 
								$label = "Reserve";
								break;
							case 102: 
								$label = "Injured";
								break;
						}
						echo($label); ?></td>
						<td class="hsc2_r" align="center"><?php echo($data['active_min']); ?></td>
						<td class="hsc2_r" align="center"><?php echo($data['active_max']); ?></td>
					</tr>
						<?php 
						$rowCount++;
						} 
					} 
					?>
					
					</table>
					</td>
				</tr>
				</table>
				</div>
				<div style="margin:6px 0 6px 0;min-height:12px;"><br clear="all" class="clear" /></div>
              	<?php } ?>
				
				<div class='textbox'>
			    <table cellpadding="0" cellspacing="0" border="0" style="width:255px;">
			    <tr class='title'>
			    	<td style='padding:6px' colspan="2">Point Scoring Values</td>
			    </tr>
			    <tr>
			    	<td>
					<table cellpadding="2" cellspacing="0" border="0" style="width:100%;">
				    <tr class='headline'>
				    	<td width="70%">Batting Category</td>
						<td width="35%">Points</td>
					</tr>
					<?php if (isset($scoring_batting) && sizeof($scoring_batting) > 0) {
						$rowCount = 0;
						foreach($scoring_batting as $cat => $val) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
                    
				    	<td class="hsc2_l"><?php echo(get_ll_cat($cat)); ?></td>
						<td class="hsc2_r" align="center"><?php echo($val); ?></td>
					</tr>
						<?php 
						$rowCount++;
						}
					}
					?>
					<tr class='headline'>	
						<td>Pitching Category</td>
						<td>Points</td>
				    </tr>
					<?php if (isset($scoring_pitching) && sizeof($scoring_pitching) > 0) {
							$rowCount = 0;
						foreach($scoring_pitching as $cat => $val) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
						
				    	<td class="hsc2_l"><?php echo(get_ll_cat($cat)); ?></td>
						<td class="hsc2_r" align="center"><?php echo($val); ?></td>
					</tr>
						<?php 
						$rowCount++;
						}
					}
					?>
					</table>
					</td>
				</tr>
				</table>
				</div>
				<div style="margin:6px 0 6px 0;min-height:12px;"><br clear="all" class="clear" /></div>
               </div>
                   <!-- BEGIN MAIN COLUMN -->
               <div id="detailColumn">
			
		        <br class="clear" />
				<p>
				<table cellpadding=5 cellspacing=0 border=0 width=100%><tr valign=top><td><b>Draft</b>:</td>
                <td>&nbsp;</td>
                <td><table border=0 cellpadding=1 cellspacing=0><tr valign=top><td>&#149;</td>
                <td> Draft, <?php if (isset($draftDate) && $draftDate != -1 && $draftDate != EMPTY_DATE_TIME_STR) { echo(date('m/d/Y', strtotime($draftDate))." at ".date('h:i A T',strtotime($draftDate))); } else { echo("A draft date has not been set"); } ?></td>
                </tr><tr valign=top><td>&#149;</td>
                <td><?php echo($draftRounds); ?> Rounds, <?php echo(($draftTimer != -1) ? 'Timed picks' :'no time limit'); ?></td>
                </tr></table></td>
                </tr><tr valign=top><td><b>Player Pool</b>:</td>
                <td>&nbsp;</td>
                <td>All Players</td>
                <?php 
				if (isset($rosters) && sizeof($rosters) > 0) { 
					$posStr = "";
                	foreach($rosters as $pos => $data) {
						if ($pos < 100) { 
							if (!empty($posStr)) { $posStr .= ", "; }
							$posStr .= get_pos($pos);
						}
					}
					?>
                </tr><tr valign=top><td><b>Positions</b>:</td>
                <td>&nbsp;</td>
                <td><?php echo($posStr); ?></td>
                </tr>
				<?php } ?>
				<tr valign=top><td><b>Eligibility</b>:</td>
                <td>&nbsp;</td>
                <td>Players are eligible at their primary position, plus positions they've 
                played <?php echo($config['min_game_last']); ?> games last year or <?php echo($config['min_game_current']); ?> games this year.</td>
                </tr><tr valign=top><td><b>Transactions</b>:</td>
                <td>&nbsp;</td>
                <td><table border=0 cellpadding=1 cellspacing=0><tr valign=top><td>&#149;</td>
                <td>Lineups are set once for the start of each period.<br>Deadline is anytime before the game admin uploads and processes the current sim.</td>
                </tr><tr valign=top><td>&#149;</td>
                <td>Owners may set lineups and change players' positions from a list of their eligible positions.</td>
                </tr>
                <?php if ($config['useWaivers'] != -1) { ?>
                <tr valign=top><td>&#149;</td>
                <td>Add/drops are handled by a waivers process.</td>
                </tr>
                <?php } ?>
                </table></td>
                </tr>
                <?php if ($config['useWaivers'] != -1) { ?>
                <tr valign=top>
                	<td><b>Waivers</b>:</td>
                    <td>&nbsp;</td>
                    <td><table border=0 cellpadding=1 cellspacing=0>
                    <tr valign=top>
                        <td>&#149;</td>
                        <td>Inital waivers order is the reversed order of the draft.</td>
                    </tr>
                    <tr valign=top>
                        <td>&#149;</td>
                        <td>Each time a team makes a waivers pick, it is moved to the bottom of the waivers list.</td>
                    </tr>
                    <tr valign=top>
                        <td>&#149;</td>
                        <td>The order of execution of waiver picks is random generated for each period.</td>
                    </tr>
                    </table>
                </tr>
                <?php } 
				if ($config['useTrades'] != -1) { ?>
                <tr valign="top">
                	<td><b>Trading</b>:</td>
                    <td>&nbsp;</td>
                    <td><table border=0 cellpadding=1 cellspacing=0>
                    <tr valign="top">
                        <td>&#149;</td>
                        <td>Teams are allowed to initatate and react to trades.</td>
                    </tr>
                    <?php  if ($config['approvalType'] != -1) { 
					 if ($config['approvalType'] == 1) { ?>
                    <tr valign="top">
                        <td>&#149;</td>
                        <td>The league commissioer must approve all trades before they become final.</td>
                    </tr>
                    <?php } else if ($config['approvalType'] == 2) { ?>
                     <tr valign="top">
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr valign="top">
                        <td>&#149;</td>
                        <td>Trades may be reviewed by other team owners.</td>
                    </tr>
                    <?php  if ($config['minProtests'] != 0) { ?>
                   	<tr valign="top">
                        <td>&nbsp</td>
                        <td>
                        <table border=0 cellpadding=1 cellspacing=0>
                        <tr valign="top">
                        <td width"=25">&nbsp;</td>
                        <td> &#149;</td>
                        <td>A trade is voided if it recieves <?php print($config['minProtests']); ?> protests from the league.</td>
                        </tr>
                        </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php  if ($config['protestPeriodDays'] != -1) { ?>
                    <tr valign="top">
                        <td>&nbsp</td>
                        <td>
                        <table border=0 cellpadding=1 cellspacing=0>
                        <tr valign="top">
                        <td width"=25">&nbsp;</td>
                        <td> &#149;</td>
                        <td>Owners have <?php print($config['protestPeriodDays']); ?> days after a trade is accepted to log a protest.</td>
                        </tr>
                        </table>
                        </td>
                    </tr>

					<?php  } }
					}  else { ?>
                    <tr valign="top">
                        <td>&#149;</td>
                        <td>Trades do not require approvals and are not subject to league review.</td>
                    </tr>
                    <?php }
					if ($config['tradesExpire'] != -1) { ?>
                    <tr valign="top">
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr valign="top">
                        <td>&#149;</td>
                        <td>Owners may set a trade expiration date on all offers.</td>
                    </tr>
                    <?php  if ($config['defaultExpiration'] != -1) { ?>
                   	<tr valign="top">
                        <td>&nbsp</td>
                        <td>
                        <table border=0 cellpadding=1 cellspacing=0>
                        <tr valign="top">
                        <td width"=25">&nbsp;</td>
                        <td> &#149;</td>
                        <td>The default expiration time for trades is <?php print((($config['defaultExpiration']==100)?" the following sim period":$config['defaultExpiration']." Days")); ?>.</td>
                       	</tr>
                        </table>
                        </td>
                    </tr>
                    <?php } 
					} ?>
                    </table>
                </tr>
                <?php } ?>
                </table></td>
                </tr> 
                <tr valign=top><td><b>Schedule</b>:</td>
                <td>&nbsp;</td>
                <td>Weekly scoring periods, starting on Sundays.<br><br>
                Playoffs start in Period <?php echo(($scorePeriods + 1)); ?> and last for <?php echo($playoffRounds); ?> Periods.</td>
                </tr></table>
		        <p>&nbsp;</p>
			</div>
		</div>
    </div>
    <p /><br />