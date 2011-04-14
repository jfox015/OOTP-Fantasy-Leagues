    <div id="column-single">
   	<h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            <?php print($league_finder_intro_str); ?>
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" style="width:825px;">
            <tr class='title'><td colspan="5">League with current team openings</td></tr>
            <tr class='headline'>
                <td width="40%">League</td>
                <td width="30%">Type</td>
                <td align="center" width="20%">Teams</td>
                <td align="center" width="10%">Openings</td>
                <td align="center" width="10%">Options</td>
            </tr>
            <?php 
            if (isset($league_list) && sizeof($league_list) > 0) {
               $rowCount = 0;
			   foreach ($league_list as $row) {
				$cls="s".($rowCount%2);
				?>
 				<tr class="<?php echo($cls); ?>" style="text-align:left;">
					<td><?php echo(anchor('/league/info/'.$row['id'],$row['league_name'])); ?></td>
                    <td><?php echo($row['leagueType']); ?></td>
                    <td align="center"><?php echo($row['max_teams']); ?></td>
                    <td align="center"><?php echo($row['openings']); ?></td>
                    <td align="center" class="last" nowrap="nowrap">
                    <?php echo(anchor('/league/requestTeam/'.$row['id'],'Request a Team')); ?>
                    </td>
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="4">No leagues currently have opeings or are accepting new members.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>      
        </div>
        <p /><br />
    </div>
    <p /><br />