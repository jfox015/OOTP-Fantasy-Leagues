		<img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
        <?php echo( anchor('/league/submit/add','Create a new League')); ?><br />
        <p />&nbsp;<br />
        <div class="table">
        <table class="listing" cellpadding="0" cellspacing="0">
          <tr>
            <th class="first" width="175">Name</th>
            <th>OOTP League</th>
            <th>Status</th>
            <th class="last">Tools</th>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "bg" : ""); ?>">			
            <td class="style1" style="text-align:left;"><?php echo(anchor('location/info/'.$row['id'],$row['name'])); ?></td>
            <td><?php echo $row['address']."<br />".$row['address2']; ?></td>
            <td><?php echo $row['city']; ?></td>
             <td><?php echo $row['state']; ?></td>
             <td class="last" nowrap="nowrap">
            <?php 
			echo( anchor('/location/submit/mode/edit/id/'.$row['id'],'<img src="'.PATH_IMAGES.'edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor('/location/submit/mode/delete/id/'.$row['id'],'<img src="'.PATH_IMAGES.'hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
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
      <br class="clear" />