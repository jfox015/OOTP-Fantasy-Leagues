   
   		<div id="subPage">
            <div class="top-bar"><h1><?php echo($thisItem['league_name']); ?> Standings</h1></div>
            
            <?php
			if (isset($league_start) && strtotime($league_start) > strtotime(date('Y-m-d',time()))) { ?>
            <span class="notice"><?php print($start_str); ?></span>
			<?php
			}
           	?>
           	<?php if (isset($avail_periods) && sizeof($avail_periods) > 0) {  ?>
            <div style="width:48%;text-align:left;float:left;">
                <?php echo("<b>Period: </b>");
                foreach($avail_periods as $period) { 
                    if ($period != $curr_period) {
                        echo(anchor('/league/standings/id/'.$league_id."/period_id/".$period,$period));
                    } else {
                        echo($period);
                    }
                    echo("&nbsp;");
                } 
                ?>
            </div>
            <?php } ?>
            <div class='textbox'>
                <table style="margin:6px" class="sortable-table" cellpadding="8" cellspacing="2">
                <?php 
                if (isset($thisItem['teams']) && sizeof($thisItem['teams']) > 0 && 
					isset($thisItem['rules']) && sizeof($thisItem['rules']) > 0) { 
				
					$catCount = sizeof($thisItem['rules']['batting']);
					$colspan = ($catCount * 2) + 5;
				?>
                <tr class='title'>
                    <td colspan='<?php print($colspan); ?>' class='lhl'>Currrent Standings</td>
                </tr>
                
                <tr class='headline'>
                    <td class='hsc2_c'>Team</td>
                    <td width="1">&nbsp;</td>
                    <td class='hsc2_c' colspan="<?php print($catCount); ?>">Batting</td>
                    <td width="1">&nbsp;</td>
                    <td class='hsc2_c' colspan="<?php print($catCount); ?>">Pitching</td>
                    <td width="1">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr class='headline'>
                	<td>&nbsp;</td>
					<td>&nbsp;</td>
					<?php
					$types = array('batting','pitching');
					foreach($types as $type) {
						foreach ($thisItem['rules'][$type] as $cat => $val) {
							print('<td class="hsc2_r">'.get_ll_cat($cat).'</td>');
						} // END foreach
						print('<td>&nbsp;</td>');
					} // END foreach
					?>
                    <td class="hsc2_r">Total</td>
                </tr>
                
                <?php 
                $rowcount = 0;
                foreach($thisItem['teams'] as $id=>$teamData) { 
                    if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }  // END if
                    ?>
                <tr style="background-color:<?php echo($color); ?>">
					<?php
                    if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
                        $avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
                    } else {
                        $avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
                    }
                    ?>
                    <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24" /> &nbsp;
					<?php echo(anchor('/team/info/'.$id,$teamData['teamname']." ".$teamData['teamnick'],['class'=>'teamLargeLink'])); ?></td>
                    <td>&nbsp;</td>
                    <?php 
						$i = 0;
						foreach($types as $type) {
							foreach ($thisItem['rules'][$type] as $cat => $val) {
								print('<td class="hsc2_r" align="right">');
								if (isset($teamData['value_'.$i]) && $teamData['value_'.$i] != -1) {
									$outVal = 0;
									switch ($cat) {
										case 18:
										case 19:
										case 20:
										case 25:
										case 41:
											$outVal=sprintf("%.3f",$teamData['value_'.$i]);
											if ($outVal<1) {$outVal=strstr($outVal,".");}
											break;
										case 40:
										case 42:
											$outVal=sprintf("%.2f",$teamData['value_'.$i]);
											if (($outVal<1)&&($cat==42)) {$outVal=strstr($outVal,".");}
											break;
										default:
											$outVal = intval($teamData['value_'.$i]);
											break;
									}
									print($outVal);
								} else {
									print(' - - ');
								} // END if
								print ('</td>');
								$i++;
							} // END foreach
							if ($i < 6) {
								$i = 6;
							} // END if
							print('<td>&nbsp;</td>'); 
						} // END foreach
					?>
                    <td class="hsc2_r"><strong><?php 
					if (isset($teamData['total'])) {
						print($teamData['total']); 
					} else {
						print("0");
					} 
					?></strong></td>
                </tr>
                    <?php
                    $rowcount++;
                    } // END foreach
                } else { ?>
                <tr class='title'>
                    <td class='lhl'>Currrent Standings</td>
                </tr>
                <tr>
                    <td class="hsc2_l">No Teams were Found</td>
                </tr>
                <?php 
				} // END if
				?>
                </table>
            </div>  <!-- end batting stat div -->
        </div>
