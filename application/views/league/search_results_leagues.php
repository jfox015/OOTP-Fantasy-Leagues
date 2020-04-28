	<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){	
		$('a[rel=delete]').live('click',function (e) {
			e.preventDefault();
			if (confirm("Are you sure you want to delete this league? It will delete the league entry and ALL supporting data including teams, transactions rosters, etc.\n\n ARE YOU 100% SUREYOU WANT TO DELETE THIS LEAGUE?")) {
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/submit/mode/delete/id/'+this.id;
			}
		});
	});
	</script>
		<?php if ($loggedIn) { ?>
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
        <?php echo( anchor('/user/createLeague','Create a new League')); ?><br />
        <?php } ?>
        <div class="textbox searchBox">
        <table class="listing" cellpadding="5" cellspacing="0">
        <tr class="title">
        	<td colspan="<?php if ($accessLevel == ACCESS_ADMINISTRATE) { echo("7"); } else { echo("6"); } ?>">
            Leagues
            </td>
            </tr>
          <tr class="headline">
            <th class="first" width="35"></th>
            <th width="150">Name</th>
            <th class="center-align">Scoring</th>
            <th class="center-align">Teams</th>
            <th class="center-align">Type</th>
            <th class="center-align">Status</th>
            <th class="center-align">Commissioner</th>
            <?php if ($accessLevel == ACCESS_ADMINISTRATE) { ?>
            <th class="last">Tools</th>
            <?php } ?>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">			
            <td class="style1" style="text-align:left;">
            <?php 
			if (isset($row['avatar']) && !empty($row['avatar'])) { 
				$avatar = PATH_LEAGUES_AVATARS.$row['avatar']; 
			} else {
				$avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
			} ?>
            <img src="<?php echo($avatar); ?>" 
            width="24" height="24" alt="<?php echo($row['league_name']); ?>" 
            title="<?php echo($row['league_name']); ?>" />
            </td>
            <td><?php
				echo(anchor('/league/home/'.$row['id'], $row['league_name']));
			?></td>
            
            <td class="center-align"><?php echo $row['league_type']; ?></td>
            <td class="center-align"><?php echo $row['max_teams']; ?></td>
             
             <td class="center-align"><?php echo ($row['access_type']); ?></td>
             <td class="center-align"><?php echo ($row['league_status']); ?></td>
             <td class="center-align"><?php if ($row['commissioner_id'] != -1) {
				 echo anchor('/user/profile/mode/view/id/'.$row['commissioner_id'],resolveUsername($row['commissioner_id'])); 
			 } ?></td>
             
              <?php if ($accessLevel == ACCESS_ADMINISTRATE) { ?>
              <td class="last" nowrap="nowrap" class="center-align">
            <?php 
			echo( anchor('/league/submit/mode/edit/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />',array('id'=>$row['id'],'rel'=>'delete'))); ?></td>
            <?php } ?>
          </tr>
			<?php $rowCount++;
        }
        if ($rowCount == 0) { ?>
            <tr class="empty">
                <td colspan="5" class="results">There were no results</td>   
            </tr>
        <?php } ?>
		</table>
      </div>