	<script type="text/javascript">
	$(document).ready(function(){
		$('#submitButton').click(function() {
			checkAndSubmit();			
		});
		$('#chooseScoring').change(function() {
			if ($('#chooseScoring').val() != -1) {
				document.location.href='<?php echo($config['fantasy_web_root']); ?>admin/configScoringRules/scoring_type/'+$('#chooseScoring').val();		
			}
		});
		
	});
	function checkAndSubmit() {
		$('#configScoring').submit();
	}
	</script>
	<!-- BEGIN ROSTER LIMIT EDITOR FORM -->
    <?php 
	$stats_batting = get_stats_for_scoring(1);
	$stats_pitching = get_stats_for_scoring(2);
	$instr = "All fields are required.";
	$disabled = ' disabled="disabled"';
	$cutoff = -1;
	$requiredCount = 0;
	switch($scoring_type) {
		case LEAGUE_SCORING_ROTO:
			$requiredCount = 3;
			$cutoff = 4;
			break;
		case LEAGUE_SCORING_ROTO_5X5:
			$requiredCount = 4;
			$cutoff = 5;
			break;
		case LEAGUE_SCORING_ROTO_PLUS:
			$requiredCount = 5;
			$cutoff = 6;
			break;
		case LEAGUE_SCORING_HEADTOHEAD:
			$requiredCount = 0;
			$cutoff = 12;
			$disabled = '';
			$instr = "Leave any unused statistics blank to skip.";
			break;
		default:
			break;
	}
	?>
	<div id="single-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
		Select up to <?php print($cutoff); ?> statistcs in both the batting and pitching categories. <?php
		print($instr); ?>
        <br /><br />
		<span style="color:#C00;font-weight:bold;">*</span> indicated a required field.
		<br /><br />
        <?php
        $errors = validation_errors();
		if ($errors) {
			echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
		}
		if ($outMess) {
			echo $outMess;
		}
		?>
    </div>
	<div id="center-column">
        <form action="<?php echo($config['fantasy_web_root']); ?>admin/configScoringRules" name="configScoring" id="configScoring" method="post" autocomplete="false">
     	<div class='textbox'>
	    <table cellpadding="0" cellspacing="0">
	    <tr class='title'>
	    	<td style='padding:6px' colspan="2">Point Scoring Values</td>
	    </tr>
	    <tr>
	    	<td>  
				<table cellpadding="0" cellspacing="0" border="0" style="width:560px;">
				<tr>
					<td width="50%">
					<table cellpadding="2" cellspacing="0" border="0" style="width:100%;">
				    <tr class='headline'>
				    	<td width="70%">Batting Category</td>
						<td width="35%">Points</td>
					</tr>
					<?php 
					/*****************************************
					/	BATTING STATS
					/****************************************/
					$statCount = 0;
					if (isset($scoring_batting) && sizeof($scoring_batting) > 0) {
						$rowCount = 0;
						foreach($scoring_batting as $cat => $val) { 
					?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
				    	<td class="hsc2_l"><select name="batting_type_<?php echo($statCount); ?>"  id="batting_type_<?php echo($statCount); ?>">
							<option value=""></option>
							<?php if (isset($stats_batting) && sizeof($stats_batting) > 0) { 
								foreach($stats_batting as $statId => $stat) { 
									echo('<option value="'.$statId.'"');
									if ($statId == $cat) { echo(' selected="selected"'); } // END if
									echo('>'.$stat.'</option>');
								}
							} ?>
						</select></td>
                        
						<td class="hsc2_r" align="center"><input type="text" name="batting_value_<?php echo($statCount); ?>" size="6"<?php print($disabled); ?> 
                        value="<?php $field_val = $input->post('batting_value_'.$statCount); if (isset($field_val) && !empty($field_val)) { echo($field_val); } else { echo($val); } ?>" class="styledTextBox" />
                        <?php if ($statCount <= $requiredCount) { echo('<span style="color:#C00;font-weight:bold;">*</span>'); } ?>
                        </td>
					</tr>
						<?php 
						$rowCount++;
						$statCount++;
						}
					}
					while ($statCount < $cutoff) { 
						$rowCount = 0;
						?>
						<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
	                    
					    	<td class="hsc2_l"><select name="batting_type_<?php echo($statCount); ?>" id="batting_type_<?php echo($statCount); ?>">
								<option value="-1" selected="selected" />
								<?php if (isset($stats_batting) && sizeof($stats_batting) > 0) { 
									foreach($stats_batting as $statId => $stat) { 
										echo('<option value="'.$statId.'">'.$stat.'</option>');
									}
								} ?>
							</select></td></td>
							<td class="hsc2_r" align="center"><input type="text" name="batting_value_<?php echo($statCount); ?>" size="6"<?php print($disabled); ?>  value="-1" class="styledTextBox" /></td>
						</tr>
						<?php 
						$rowCount++;
						$statCount++;
					} // END while 
					?>
					</table>
					</td>
					<td width="15" sty;e="wodth:15px;">&nbsp;</td>
					<td width="50%">
					<?php
					/*****************************************
					/	PITCING STATS
					/****************************************/
					$statCount = 0;
					?>
					<table cellpadding="2" cellspacing="0" border="0" style="width:100%;">
					<tr class='headline'>	
						<td>Pitching Category</td>
						<td>Points</td>
				    </tr>
					<?php if (isset($scoring_pitching) && sizeof($scoring_pitching) > 0) {
							$rowCount = 0;
						foreach($scoring_pitching as $cat => $val) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
                    
				    	<td class="hsc2_l"><select name="pitching_type_<?php echo($statCount); ?>"  id="pitching_type_<?php echo($statCount); ?>">
							<option value="" />
							<?php 
							if (isset($stats_pitching) && sizeof($stats_pitching) > 0) { 
								foreach($stats_pitching as $statId => $stat) { 
									echo('<option value="'.$statId.'"');
									if ($statId == $cat) { echo(' selected="selected"'); } // END if
									echo('>'.$stat.'</option>');
								}
							} 
							?>
						</select></td>
						<td class="hsc2_r" align="center"><input type="text" name="pitching_value_<?php echo($statCount); ?>" size="6"<?php print($disabled); ?>  
                        value="<?php $field_val = $input->post('pitching_value_'.$statCount); if (isset($field_val) && !empty($field_val)) { echo($field_val); } else { echo($val); } ?>" class="styledTextBox" />
                        <?php if ($statCount <= $requiredCount) { echo('<span style="color:#C00;font-weight:bold;">*</span>'); } ?>
                        </td>
					</tr>
						<?php 
						$rowCount++;
						$statCount++;
						}
					}
					while ($statCount < $cutoff) { ?>
						<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
	                    
					    	<td class="hsc2_l"><select name="pitching_type_<?php echo($statCount); ?>"  id="pitching_type_<?php echo($statCount); ?>">
								<option value="-1" selected="selected" />
								<?php if (isset($stats_pitching) && sizeof($stats_pitching) > 0) { 
									foreach($stats_pitching as $statId => $stat) { 
										echo('<option value="'.$statId.'">'.$stat.'</option>');
									}
								} ?>
							</select></td></td>
							<td class="hsc2_r" align="center"><input type="text" name="pitching_value<?php echo($statCount); ?>" size="6"<?php print($disabled); ?>  value="-1" class="styledTextBox" /></td>
						</tr>
						<?php 
						$rowCount++;
						$statCount++;
					} // END while
					?>
					</table>
				</td>
        </tr>
		<tr>
            <td align="center" colspan="3"><input type="button" id="submitButton" name="submitButton" value="Save Changes" class="button" /></td>
        </tr>
        </table>
            </td>
        </tr>
        </table>
        </div>
        <input type="hidden" name="scoring_type" value="<?php print($scoring_type); ?>" />
         </form>
        <br class="clearfix" />
	</div>
	<div id="right-column">
    	<div class='textbox'>
        <table cellpadding="2" cellspacing="0" border="0" style="width:225px;">
        <tr class='title'>
            <td width="100%">Scoring Type</td>
        </tr>
        <tr>
            <td width="100%">
    	<?php if (isset($scoring_types) && sizeof($scoring_types) > 0) { ?>
    	<label for="chooseScoring" style="min-width:0px;width:auto;margin:0px;">Scoring Type:</label>
        <select id="chooseScoring" name="chooseScoring">
            <?php
			foreach($scoring_types as $id => $type) {
				print('<option value="'.$id.'"');
				if (isset($scoring_type) && $scoring_type == $id) {
					print(' selected="selected"');
				}
				print('>'.$type.'</option>');
			}
			?>
        </select>
        <?php } ?>
        </td>
        </tr>
        </table>
        </div>
	</div> 
    
   
    