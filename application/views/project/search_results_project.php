		<img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
        <?php echo anchor('/project','Add new Project'); ?>
        <br />
        <p />&nbsp;<br />
        <div class="textbox">
        <table class="listing" cellpadding="5" cellspacing="2" width="725">
        <tr class="title">
        	<td colspan="<?php if ($accessLevel >= ACCESS_DEVELOP) { echo("7"); } else { echo("6"); } ?>">
            Projects
            </td>
            </tr>
          <tr class="headline">
          	<th class="first">Name</th>
            <th class="first">Job Code</th>
            <th class="first" width="250">Summary</th>
            <th>Status</th>
            <?php if ($accessLevel >= ACCESS_DEVELOP) { ?>
            <th class="last">Tools</th>
            <?php } ?>
          </tr>
        <?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            $sum = (strlen($row['summary']) > 100) ? substr($row['summary'],0,100)."...": $row['summary'];
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">			
            <td><b><?php echo anchor('/project/info/'.$row['id'],$row['name']); ?></b></td>
            <td><?php echo $row['jobCode']; ?></td>
            <td><?php echo $sum; ?></td>
            <td><?php if ($row['active'] == 1) { echo('<span style="color:#060">Active</span>'); } else { echo('<span style="color:#C00">(inactive)</span>'); } ?></td>
           	<?php if ($accessLevel >= ACCESS_DEVELOP) { ?>
              <td class="last" nowrap="nowrap">
            <?php 
			echo( anchor('/project/submit/mode/edit/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor('/project/submit/mode/delete/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
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