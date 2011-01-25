	<script type="text/javascript">
	$(document).ready(function(){
		$('#submitButton').click(function() {
			checkAndSubmit();			
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
	?>
	<div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
		Select up to twelve statitcs in both the batting and pitching categories. Leave any unused statistics
		blank to skip.
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
        <form action="<?php echo($config['fantasy_web_root']); ?>admin/configScoringRules" name="configScoring" id="configScoring" method="post" autocomplete="false">
        <div class='textbox'>
		
	    <table cellpadding="0" cellspacing="0" border="0" style="width:255px;">
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
						</select></td></td>
						<td class="hsc2_r" align="center"><input type="text" name="batting_value_<?php echo($statCount); ?>" size="6" 
                        value="<?php $field_val = $input->post('batting_value_'.$statCount); if (isset($field_val) && !empty($field_val)) { echo($field_val); } else { echo($val); } ?>" class="styledTextBox" />
                        <?php if ($statCount == 0) { echo('<span style="color:#C00;font-weight:bold;">*</span>'); } ?>
                        </td>
					</tr>
						<?php 
						$rowCount++;
						$statCount++;
						}
					}
					while ($statCount < 12) { ?>
						<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
	                    
					    	<td class="hsc2_l"><select name="batting_type_<?php echo($statCount); ?>" id="batting_type_<?php echo($statCount); ?>">
								<option value="-1" selected="selected" />
								<?php if (isset($stats_batting) && sizeof($stats_batting) > 0) { 
								foreach($stats_batting as $statId => $stat) { 
									echo('<option value="'.$statId.'">'.$stat.'</option>');
								}
								} ?>
							</select></td></td>
							<td class="hsc2_r" align="center"><input type="text" name="batting_value_<?php echo($statCount); ?>" size="6" value="-1" class="styledTextBox" /></td>
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
							<?php if (isset($stats_pitching) && sizeof($stats_pitching) > 0) { 
							foreach($stats_pitching as $statId => $stat) { 
								echo('<option value="'.$statId.'"');
								if ($statId == $cat) { echo(' selected="selected"'); } // END if
								echo('>'.$stat.'</option>');
							}
							} ?>
						</select></td></td>
						<td class="hsc2_r" align="center"><input type="text" name="pitching_value_<?php echo($statCount); ?>" size="6" 
                        value="<?php $field_val = $input->post('pitching_value_'.$statCount); if (isset($field_val) && !empty($field_val)) { echo($field_val); } else { echo($val); } ?>" class="styledTextBox" />
                        <?php if ($statCount == 0) { echo('<span style="color:#C00;font-weight:bold;">*</span>'); } ?>
                        </td>
					</tr>
						<?php 
						$rowCount++;
						$statCount++;
						}
					}
					while ($statCount < 12) { ?>
						<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
	                    
					    	<td class="hsc2_l"><select name="pitching_type_<?php echo($statCount); ?>"  id="pitching_type_<?php echo($statCount); ?>">
								<option value="-1" selected="selected" />
								<?php if (isset($stats_pitching) && sizeof($stats_pitching) > 0) { 
								foreach($stats_pitching as $statId => $stat) { 
									echo('<option value="'.$statId.'">'.$stat.'</option>');
								}
								} ?>
							</select></td></td>
							<td class="hsc2_r" align="center"><input type="text" name="pitching_value<?php echo($statCount); ?>" size="6" value="-1" class="styledTextBox" /></td>
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
        </form>
    </div>