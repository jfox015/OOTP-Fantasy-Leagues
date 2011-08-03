		<script type="text/javascript" charset="UTF-8">
		var ajaxWait = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
		$(document).ready(function(){	
			$('a[rel=itemEdit]').click(function() {
				document.location.href="<?php echo($config['fantasy_web_root']); ?>league/scheduleEdit/league_id/<?php echo($league_id); ?>/period_id/"+this.id;
				return false;
			});
		});
		</script>
   		<div id="subPage">
            <div class="top-bar"><h1><?php echo($thisItem['league_name']); ?> Schedule</h1></div>
            
            <div class='textbox'>
                <table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="560px">
                <tr class='title'>
                    <td colspan='5' class='lhl'><?php echo($thisItem['league_name']); ?></td>
                </tr>
				<?php 
				//echo("schedleEdit = ".(($schedleEdit) ? "true" : "false")."<br />");
				//echo("playoffEdit = ".(($playoffEdit) ? "true" : "false")."<br />");
                $lastId = -1;
				if (isset($thisItem['schedule']) && sizeof($thisItem['schedule']) > 0) { 
                foreach($thisItem['schedule'] as $id=>$scheduleData) { ?>
                <tr class='headline'>
					<?php
					$divWidth = '100%';
					if (($isCommish || $isAdmin) && ($schedleEdit || $playoffEdit)) {
						$divWidth = '65%';
					}
					?>
                    <td class='hsc2_c' colspan="2">
					<div style="position:relative;">
					<div style="width:<?php echo($divWidth); ?>; float:left;">
					Week <?php echo($id); ?></div>
					<?php
					$lastId = $id;
					$divWidth = '100%';
					if (($isCommish || $isAdmin) && 
						 (($curr_period <= $max_reg_period && $id <= $max_reg_period && $schedleEdit) || 
						  ($curr_period > $max_reg_period && $id > $max_reg_period && $id == $curr_period && $playoffEdit)))  { ?>
						<div style="width:35%; float:left; text-align:right;margin:0px; padding:0px;">
						<a href="#" rel="itemEdit" id="<?php echo($id); ?>"><strong>Edit</strong></a>
						</div>
					<?php
					} // END if
					?>
					</div></td>
                </tr>
                <?php 
                $rowcount = 0;
                if (isset($scheduleData) && sizeof($scheduleData) > 0) { 
                    foreach($scheduleData as $details) { 
                    if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
                    ?>
                <tr style="background-color:<?php echo($color); ?>">
                    <td class='hsc2_l' width="50%"><?php echo($details['home_team']); if (isset($details['home_team_score'])) { echo(' <span style="color:#C00;">'.$details['home_team_score'].'</span>'); } ?></td>
                    <td class='hsc2_l' width="50%"><?php echo($details['away_team']); if (isset($details['away_team_score'])) { echo(' <span style="color:#00A;">'.$details['away_team_score'].'</span>'); } ?></td>
                </tr>
                    <?php
                    $rowcount++;
                    }
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="4">No games were Found</td>
                </tr>
                <?php } ?>

                
                <?php } // END foreach
				//echo("curr_period = ".$curr_period."<br />");
				//echo("max_reg_period = ".$max_reg_period."<br />");
				if (isset($curr_period) && $curr_period > $max_reg_period && $lastId < $curr_period) { ?>
				 <tr class='headline'>
					<?php
					$divWidth = '100%';
					if (($isCommish || $isAdmin) && ($schedleEdit || $playoffEdit)) {
						$divWidth = '65%';
					}
					?>
                    <td class='hsc2_c' colspan="2">
					<div style="position:relative;">
					<div style="width:65%; float:left;">Week <?php echo($curr_period); ?></div>
					<div style="width:35%; float:left; text-align:right;margin:0px; padding:0px;">
					<a href="#" rel="itemEdit" id="<?php echo($curr_period); ?>"><strong>Edit</strong></a>
					</div></td>
                </tr>
				<tr style="background-color:<?php echo($color); ?>">
                    <td class='hsc2_l' colspan="2">Click <strong>Edit</strong> to add games</td>
                </tr>
				<?php 
				} // END if
				
                } else { ?>
                <tr class='headline'>
                    <td class="sc2">No schedule was found for this league.</td>
                </tr>
                <?php } // END if isset($divisions) 
                ?>
                </table>
            </div>  <!-- end batting stat div -->
        </div>
