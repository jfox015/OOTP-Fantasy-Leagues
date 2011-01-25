		<img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
        <?php echo anchor('/bug','Add new Bug'); 
        if ($loggedIn) { ?> | <?php echo anchor('/search/doSearch/id/bugs/filterAction/search/assignmentId/'.$currUser,'View My Bugs'); } ?>
        <br />
        <p />&nbsp;<br />
        <div class="textbox">
        <table class="listing" cellpadding="5" cellspacing="2" width="725">
        <tr class="title">
        	<td colspan="<?php if ($accessLevel >= ACCESS_DEVELOP) { echo("7"); } else { echo("6"); } ?>">
            Bugs
            </td>
            </tr>
          <tr class="headline">
            <th class="first" width="220">Summary</th>
            <th width="100">Project</th>
            <th>Severity</th>
            <th>Added</th>
            <th>Priority</th>
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
            <td><b><?php echo anchor('/bug/info/'.$row['id'],$sum); ?></b></td>
            <td><?php echo $row['projectId']; ?></td>
            <td align="center"><span class="severity_<?php echo $row['severityId']; ?>"><?php echo $row['severityId']; ?></span></td>
            <td><?php echo date('m/d/Y', strtotime($row['dateCreated'])); ?></td>
            <td align="center"><span class="priority_<?php echo $row['priorityId']; ?>"><?php echo $row['priorityId']; ?></span></td>
            <td align="center"><span class="status_<?php echo $row['bugStatusId']; ?>"><?php echo $row['bugStatusId']; ?></span></td>
              <?php if ($accessLevel >= ACCESS_DEVELOP) { ?>
              <td class="last" nowrap="nowrap">
            <?php 
			echo( anchor('/bug/submit/mode/edit/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor('/bug/submit/mode/delete/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
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