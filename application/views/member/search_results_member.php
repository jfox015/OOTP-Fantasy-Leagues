		<img src="<? echo ($config['fantasy_web_root']); ?>images/icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
        <?php echo( anchor('/member/submit/add','Add new member')); ?>
        <br />
        <p />&nbsp;<br />
        <div class="textbox">
        <table class="listing" cellpadding="5" cellspacing="0" width="650">
        <tr class="title">
            <th width="175">Username</th>
            <th>E-mail</th>
            <th>Register Date</th>
            <th>User Type</th>
            <th>Locked</th>
            <th>Status</th>
            <th class="last">Tools</th>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
		<tr class="<? echo(($rowCount % 2) == 0 ? "bg" : ""); ?>">				
            <td class="style1" style="text-align:left;"><?php echo(anchor('member/info/'.$row['id'],$row['username'])); ?></td>
            <td><a href="mailto:<? echo $row['email']; ?>"><? echo $row['email']; ?></a></td>
            <td><? echo date('m/d/Y', strtotime($row['dateCreated'])); ?></td>
            <td><?= $row['typeId']  ?></td>
            <td><? if ($row['locked'] == 1) { echo('<span style="color:#060">Available</span>'); } else { echo('<span style="color:#C00">Locked</span>'); } ?></td>
           	<td><? if ($row['active'] == 1) { echo('<span style="color:#060">Active</span>'); } else { echo('<span style="color:#C00">(inactive)</span>'); } ?></td>
           	<td class="last" nowrap="nowrap">
            <?php 
			echo( anchor('/member/submit/mode/edit/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor('/member/submit/mode/delete/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
          </tr>
			<? $rowCount++;
        }
        if ($rowCount == 0) { ?>
            <tr class="empty">
                <td colspan="6" class="results">There were no results</td>   
            </tr>
        <? } ?>
		</table>
        <br class="clear" />
      </div>