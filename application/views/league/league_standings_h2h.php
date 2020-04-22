   
   		<div id="subPage">
            <div class="top-bar"><h1><?php echo($thisItem['league_name']); ?> Standings</h1></div>
           
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
                <table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0">
                <?php 
                if (isset($thisItem['divisions']) && sizeof($thisItem['divisions']) > 0) { 
                foreach($thisItem['divisions'] as $id=>$divisionData) { ?>
                <tr class='title'>
                    <td colspan='5' class='lhl'><?php echo($divisionData['division_name']); ?></td></tr>
                <tr class='headline'>
                    <td class='hsc2_c'>Team</td>
                    <td class='hsc2_c'>W</td>
                    <td class='hsc2_c'>L</td>
                    <td class='hsc2_c'>%</td>
                    <td class='hsc2_c'>GB</td>
                </tr>
                <?php 
                $rowcount = 0;
				$leadW = 0;
				$leadG = 0;
                if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
                    foreach($divisionData['teams'] as $teamId => $teamData) { 
                    if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
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
                    <?php echo(anchor('/team/info/'.$teamId,$teamData['teamname']." ".$teamData['teamnick'])); ?></td>
                    <td class='hsc2_l'><?php echo($teamData['w']); ?></td>
                    <td class='hsc2_l'><?php echo($teamData['l']); ?></td>
                    <td class='hsc2_l'><?php echo(sprintf("%.3f",$teamData['pct'])); ?></td>
                    <?php 
					if ($rowcount == 0) { 
						$leadG = $teamData['g']; $leadW = $teamData['w']; $gb = "--"; 
					} else {
						$gb = $leadW - $teamData['w'];
						if ((($leadG-$teamData['g'])%2) != 0) { $gb .= "<sup>1/2</sup>"; }
					}
					?>
                    <td class='hsc2_l'><?php echo($gb); ?></td>
                </tr>
                    <?php
                    $rowcount++;
                    }
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="4">No Teams were Found</td>
                </tr>
                <?php } ?>

                
                <?php } // END foreach($divisions)
                } else { ?>
                <tr class='title'>
                    <td class="lhl">No divisions were found for this league.</td>
                </tr>
                <?php } // END if isset($divisions) 
                ?>
                </table>
            </div>  <!-- end batting stat div -->
        </div>
