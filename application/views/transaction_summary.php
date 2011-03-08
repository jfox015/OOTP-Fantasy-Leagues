				<table class="sortable" cellpadding="3" cellspacing="1" border="0" width="100%" style="border:1px solid #606060;">
				<?php if (!isset($showEffective) || (isset($showEffective) && $showEffective != -1)) { $cols = 4; } else { $cols = 3; } ?>
                <thead>
                <tr class='title'>
                    <td colspan='<?php echo($cols); ?>' class='lhl'>Transactions Summary</td>
                </tr>
                </thead>
                <tbody>
                <tr class='headline'>
                    <td>Date</td>
                    <td>Team</td>
                    <td>Details</td>
                    <?php if (!isset($showEffective) || (isset($showEffective) && $showEffective != -1)) { ?>
                    <td>Effective</td>
                    <?php } ?>
                </tr>
				<?php 
                if (isset($thisItem['transactions']) && sizeof($thisItem['transactions']) > 0) { 
					 $rowcount = 0;foreach($thisItem['transactions'] as $details) { ?>
						<?php 
						if ($details['trans_owner'] == TRANS_OWNER_OWNER || $details['trans_owner'] == TRANS_OWNER_OTHER) {
							if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }  // END if
						} else if ($details['trans_owner'] == TRANS_OWNER_COMMISH) { 
							$color = "#89A3BC"; 
						} else if ($details['trans_owner'] == TRANS_OWNER_ADMIN) { 
							$color = "#C4BB97"; 
						}
						?>
                        <tr style="background-color:<?php echo($color); ?>" align="left" valign="top">
                            <td class='hsc2_l'><?php echo(date('m/d/Y h:m:s A',strtotime($details['trans_date']))); ?></td>
                            <td class='hsc2_l'><?php 
                            if (isset($thisItem['teamList'][$details['team_id']])) {
                                $team = $thisItem['teamList'][$details['team_id']];
                                echo anchor('/team/info/'.$details['team_id'], $team['teamname']." ".$team['teamnick']); 
                            } else {
                                echo("Unknown Team");	
                            } // END if
                            // IF THIS IS A TRADE, DISPLAY THE OTHER TEAMS NAME AND INFO
                            if(isset($details['trade_team_id']) && $details['trade_team_id'] > 0 && !empty($details['trade_team_name'])) {
                            	print "<br /><br />".anchor('/team/info/'.$details['trade_team_id'], $details['trade_team_name']); 
                            }
                            ?></td>
                            <td class='hsc2_l'>
                            <table width="100%" cellpadding="0" cellspacing="2" border="0">
                            <?php 
                            $transTypes = array('added'=>'Added','dropped'=>'Dropped','claimed'=>'Claimed off waivers','tradedTo'=>'Sent','tradedFrom'=>'Received');
                           	
                            foreach ($transTypes as $field => $label) {
                                if (isset($details[$field]) && sizeof($details[$field]) > 0) { ?>
                                <tr align="left" valign="top">
                                    <td width="30%"><b><?php echo($label); ?>:</b></td>
                                    <td width="70%"><?php 
									$numDrawn = 0;
									foreach ($details[$field] as $playerInfo) {
										if ($numDrawn != 0 && $numDrawn != sizeof($details[$field])) { echo("<br />"); }
										echo($playerInfo);
										$numDrawn++;
									} // END foreach ?>
                                    </td>
                                </tr>
                                <?php
                                } // END if
                            } // END foreach
                            ?></table>
                            </td>
                            <?php if (!isset($showEffective) || (isset($showEffective) && $showEffective != -1)) { ?>
                            <td>Period <?php echo($details['effective']); ?></td>
                            <?php } ?>
                            </tr>
                            </td>
                        </tr>
                       <?php
                       $rowcount++;
						} // END foreach($divisions)
					} else { ?>
					<tr>
						<td class="sc2" colspan="<?php echo($cols); ?>">No transactions were found for this league.</td>
					</tr>
                <?php } // END if isset($divisions) 
                ?>
                </tbody>
                </table>
                <?php
                 if (isset($thisItem['transactions']) && sizeof($thisItem['transactions']) > 0 && $limit > -1) {
					$text= '';
					$text.='  <table width="100%" cellpadding="0" cellspacing="0" border="0">';
					$text.="  <tr><td width='200'></td><td>";
					
					if ($pageCount > 1 && $pageId > 1) {
						$text.='<a href="#" rel="first" id="1">&lt;&lt; First</a> &nbsp; ';
						$text.='<a href="#" rel="previous" id="'.($pageId - 1).'">&lt; Previous</a>';
					} else {
						$text.="&lt;&lt; First &nbsp; &lt; Previous";
					}
					$text .= "</td><td>";
					$text .= "Page ".$pageId." of ".$pageCount;
					$text .= "</td><td>";
					if ($pageCount > 1 && $pageId < $pageCount) {
						$text.='<a href="#" rel="next" id="'.($pageId + 1).'">Next &gt;</a> &nbsp; ';
						$text.='<a href="#" rel="last" id="'.($pageCount).'">Last &gt;</a>';
					} else {
						$text.="Next &gt; &nbsp; Last &gt;&gt;";
					}
					$text.="  </td>";
					 $text.=" <td width='200'></td></tr></table>";
					echo($text);
				}
				?>
                