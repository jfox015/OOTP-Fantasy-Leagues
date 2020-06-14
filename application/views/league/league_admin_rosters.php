	<script type="text/javascript">
	var $inputs = null;
	var fader = null;
	$(document).ready(function(){
		$('input[rel=min]').change(function () {
			updateTotals();
		});
		$('input[rel=max]').change(function () {
			updateTotals();
		});
		$inputs = $('#configRosters :input');
		updateTotals();
		$('#submitButton').click(function() {
			checkAndSubmit();
		});
	});
	function updateTotals() {
		var totalMin = 0, totalMax = 0;
		var values = {};
		$inputs.each(function() {
			if ($(this).attr('rel') == "min") {
				totalMin += parseInt($(this).val());
			} else if ($(this).attr('rel') == "max") {
				totalMax += parseInt($(this).val());
			}
		});
		$('div#active_min').text(parseInt(totalMin));
		$('div#active_max').text(parseInt(totalMax));
		$('div#active_min').css('color','#000');
		$('div#active_min').css('font-weight','normal');
		$('div#active_min').removeClass('error');
		$('div#active_max').css('color','#000');
		$('div#active_max').css('font-weight','normal');


	}
	function checkAndSubmit() {
		fadeStatus();
		$('div#activeStatus').removeClass('error');
		$('div#activeStatus').removeClass('success');
		var valid = true;

		$('input#total_reserve_min').css('color','#000');
		$('input#total_reserve_min').css('font-weight','normal');
		$('input#total_reserve_min').removeClass('error');
		$('input#total_reserve_max').css('color','#000');
		$('input#total_reserve_max').css('font-weight','normal');
		$('input#total_reserve_max').removeClass('error');

		$('input#total_injured_min').css('color','#000');
		$('input#total_injured_min').css('font-weight','normal');
		$('input#total_injured_min').removeClass('error');
		$('input#total_reserve_max').css('color','#000');
		$('input#total_reserve_max').css('font-weight','normal');
		$('input#total_reserve_max').removeClass('error');

		var activeMin = parseInt($('div#total_active_min').text());
		var activeMax = parseInt($('div#total_active_max').text());
		if (activeMin > activeMax) {
			$('div#active_min').css('color','#c00');
			$('div#active_min').css('font-weight','bold');
			$('div#active_min').addClass('error');
			showMessage('error','The minimum active player count is greater than the maximum player count. The minimum <strong>MUST</strong> be an equal or lower value. Assure the active min total is less than the active max and try submitting again.');
			valid = false;
		}

		var reserveMin = parseInt($('input#total_reserve_min').val());
		var reserveMax = parseInt($('input#total_reserve_max').val());
		if (reserveMin > reserveMax) {
			$('input#total_reserve_min').css('color','#c00');
			$('input#total_reserve_min').css('font-weight','bold');
			$('input#total_reserve_min').addClass('error');
			showMessage('error','The minimum <strong>reserve</strong> player count is greater than the maximum player count. The minimum <strong>MUST</strong> be an equal or lower value. Assure the reserve min total is less than the reserve max and try submitting again.');
			valid = false;
		}

		var injuredMin = parseInt($('input#total_injured_min').val());
		var injuredMax = parseInt($('input#total_injured_max').val());
		if (injuredMin > injuredMax) {
			$('input#total_injured_min').css('color','#c00');
			$('input#total_injured_min').css('font-weight','bold');
			$('input#total_injured_min').addClass('error');
			showMessage('error','The minimum <strong>injured</strong> player count is greater than the maximum player count. The minimum <strong>MUST</strong> be an equal or lower value. Assure the injured min total is less than the injured max and try submitting again.');
			valid = false;
		}

		if (valid) {
			$('input#total_active_min').val(parseInt($('div#active_min').text()));
			$('input#total_active_max').val(parseInt($('div#active_max').text()));
			$('#configRosters').submit();
		}
	}
	function showMessage(type,errMess) {
		$('div#activeStatus').addClass(type);
		$('div#activeStatus').html(errMess);
		$('div#activeStatusBox').fadeIn("slow");
		setTimeout('fadeStatus("active")',10000);
	}
	function fadeStatus(type) {
		$('div#activeStatusBox').fadeOut("normal",function() { clearTimeout(fader); $('div#activeStatusBox').hide(); });

	}
	</script>
    	<!-- BEGIN ROSTER LIMIT EDITOR FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
		Select up to ten positions and the active minimum and maximum player counts. Leave any unused positons
		blank to skip.
		<br />
        <div id="activeStatusBox"><div id="activeStatus"></div></div>
        <form action="<?php echo($config['fantasy_web_root']); ?>league/configRosters/<?php echo($thisItem['id']); ?>" name="configRosters" id="configRosters" method="post" autocomplete="false">
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" style="width:475px;">
        <tr class='title'>
            <td style='padding:3px' colspan="3">Enter settings information below</td>
        </tr>
		<tr class='headline'>
				    	<td width="35%">Active Positons</td>
						<td width="35%">Min</td>
						<td width="35%">Max</td>
					</tr>
        <tr>
            <td>
			<?php
			$rowCount = 0;
			$maxCount = 10;
			$posCount = 0;
			$posList = get_pos_for_rosters();
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
			if ($outMess) {
                echo $outMess;
            }
			//asort($rosters);
			if (isset($rosters) && sizeof($rosters) > 0) {
				foreach($rosters as $pos => $data) {
					if ($pos < 100) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
				    	<td width="35%"><select name="pos<?php echo($posCount); ?>"  id="pos<?php echo($posCount); ?>">
                        <option value=""></option>
							<?php if (isset($posList) && sizeof($posList) > 0) {
							foreach($posList as $posId => $position) {
								echo('<option value="'.$posId.'"');
								if ($posId == $pos) { echo(' selected="selected"'); } // END if
								echo('>'.$position.'</option>');
							}
							} ?>
						</select></td>
						<td width="35%" class="hsc2_r"><input type="text" rel="min" name="min<?php echo($posCount); ?>" id="min<?php echo($posCount); ?>" size="4" value="<?php echo($data['active_min']); ?>" class="styledTextBox" /></td>
						<td width="35%" class="hsc2_r"><input type="text" rel="max" name="max<?php echo($posCount); ?>" id="max<?php echo($posCount); ?>" size="4" value="<?php echo($data['active_max']); ?>" class="styledTextBox" /></td>
					</tr>
					<?php
						$posCount++;
						$rowCount++;
					}
				}
			}
			while ($posCount < 10) { ?>
					<tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
				    	<td width="35%"><select name="pos<?php echo($posCount); ?>"  id="pos<?php echo($posCount); ?>">
							<option value=""></option>
							<?php if (isset($posList) && sizeof($posList) > 0) {
								foreach($posList as $posId => $position) {
									echo('<option value="'.$posId.'">'.$position.'</option>');
								}
							} ?>
						</select></td>
						<td width="35%" class="hsc2_r"><input type="text" rel="min" name="min<?php echo($posCount); ?>" id="min<?php echo($posCount); ?>" size="4" value="0" class="styledTextBox" /></td>
						<td width="35%" class="hsc2_r"><input type="text" rel="max" name="max<?php echo($posCount); ?>" id="max<?php echo($posCount); ?>" size="4" value="0" class="styledTextBox" /></td>
					</tr>
				<?php
				$rowCount++;
				$posCount++;
			}
			?>
                    <tr class='headline'>
				    	<td width="35%">Totals</td>
						<td width="35%">Min</td>
						<td width="35%">Max</td>
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
						<td class="hsc2_l"><?php
						if ($pos == 100) {
							echo('<div id="active_min">'.$data['active_min'].'</div>');
							echo('<input type="hidden" name="total_active_min" id="total_active_min" value="'.$data['active_min'].'" />');
						 } else {
						 	echo('<input type="text" name="total_'.strtolower($label).'_min" id="total_'.strtolower($label).'_min" size="4" value="'.$data['active_min'].'" class="styledTextBox" />');
						 } ?></td>
						<td class="hsc2_l "><?php
						if ($pos == 100) {
							echo('<div id="active_max">'.$data['active_max'].'</div>'); 
							echo('<input type="hidden" name="total_active_max" id="total_active_max" value="'.$data['active_max'].'" />');
						 } else {
						 	echo('<input type="text" name="total_'.strtolower($label).'_max" id="total_'.strtolower($label).'_max" size="4" value="'.$data['active_max'].'" class="styledTextBox" />');
						 } ?></td>
					</tr>
						<?php
						$rowCount++;
						}
					}
					?>
					<tr>
						<td class="hsc2_c" colspan="3"><input type="button" id="submitButton" name="submitButton" value="Save Changes" class="button" /></td>
					</tr>
					</table>
            </td>
        </tr>
        </table>
        </div>
        <input type="hidden" name="league_id" value="<?php print($thisItem['id']); ?>" />
        </form>
    </div>
