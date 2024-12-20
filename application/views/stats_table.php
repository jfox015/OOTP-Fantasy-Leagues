			<?php
			if (isset($colnames)) { 
			?>

                <!-- STATS BODY -->
            <table cellpadding="4" cellspacing="1" class='sortable-table' style="width:100%;">
            <thead>    
            <tr class="headline">
                <?php
                $cols = explode("|",$colnames);
                foreach ($cols as $colname) { 
                    switch ($colname) {
                        case 'Player':
                        case 'POS':
							echo('<th height="17" class="hsn2" style="text-align:left;">'.$colname.'</th>');
							break;
                        case 'Team':
                            if ((isset($league_id) && !empty($league_id) && $league_id != -1) && (!isset($showTeam) || (isset($showTeam) && $showTeam != -1))) {
								echo('<th height="17" class="hsn2" style="text-align:left;">'.$colname.'</th>');
							}
                            break;
						case 'Add':
                            if (isset($showTrans) && $showTrans != -1) {
								echo('<th class="hsn2" style="text-align:center;">'.$colname.'</th>');
							}
                            break;
						case 'Draft':
                            if (isset($showDraft) && $showDraft != -1) {
								echo('<th class="hsn2" style="text-align:center;">'.$colname.'</th>');
							}
                            break;
                        default:
                            echo('<th class="hsn2 numeric-sort" style="text-align:center;">'.$colname.'</th>');
                            break;
                    }
                }
                
			}
			?>
            </tr>
            </thead>
            <tbody>
            <?php
               
            if (isset($player_stats) && sizeof($player_stats) > 0 && isset($fields) && sizeof($fields) > 0) {
                $rownum=0;
                foreach($player_stats as $row) {
                    $id = $row['id'];
                    $class = "";
                    if (isset($league_id) && !empty($league_id) && $league_id != -1 && (isset($player_teams[$id]) && isset($userTeamId) && $player_teams[$id] == $userTeamId[0])) {
                        $class = "sl_5";
                    } else {
                        $class = (($rownum % 2) == 0) ? 'sl_1' : 'sl_2';
                    }
                    echo("<tr class='".$class."'>");
                    foreach($fields as $col) {
						$showCol = true;
						switch($col){
							case 'player_name':
								$align="left";
								break;
							case 'teamname':
								if ((isset($league_id) && !empty($league_id) && $league_id != -1) && (!isset($showTeam) || (isset($showTeam) && $showTeam != -1))) {
									$align="left";
								} else {
									$showCol = false;
								}
								break;
							case 'role':
							case 'positions':
								$showCol = false;
								break;
							case 'rating':	
								if ($row[$col] > 0) {
									$color = "#080";
								} else if ($row[$col] < 0) {
									$color = "#C00";
								} else {
									$color = "#000";
								}
								$row[$col] = '<span style="color:'.$color.';">'.$row[$col].'</span>';
								break;
							default:
								$align='center';
								break;
						}
						if ($showCol) echo('<td align="'.$align.'">'.((isset($row[$col])) ? $row[$col] : '').'</td>');
					}
                    echo("</tr>");
                    $rownum++;
                } // END foreach
            } // END if
            echo("</tbody>");
            echo("</table>"); 
            
            if ($limit > -1) {
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