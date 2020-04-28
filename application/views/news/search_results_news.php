		<?php 
            if ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE) { ?>
            <img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" alt="Add" title="add" align="absmiddle" /> 
        <?php echo( anchor('/news/submit/add','Add new news article')); ?><br />
        <?php } ?>
        <div class="textbox">
        <table class="listing" cellpadding="5" cellspacing="2">
          <tr class="title">
            <td colspan="<?php 
			if ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE) { echo("5"); } else { echo "3"; } ?>">News Results</td>
          </tr>
          <tr class="headline">
            <td style="text-align:center;">Article Date</td>
           	<td width="250">Title</td>
            <td>Type</td>
            <?php 
			if ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE) { ?>
            <td style="text-align:center;">Author</td>
            <td class="last">Tools</td>
            <?php } ?>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">			
           	<td class="first" style="text-align:center;"><?php echo date('m/d/Y', strtotime($row['news_date'])); ?></td>
            <td style="text-align:left;"><?php echo(anchor('news/info/'.$row['id'],$row['news_subject'])); ?></td>
            <td style="text-align:left;"><?php echo($row['type_id']); ?></td>
            <?php 
			if ($loggedIn && $accessLevel == ACCESS_ADMINISTRATE) { ?>
            <td style="text-align:left;"><?php echo(resolveOwnerName($row['author_id'])); ?></td>
            <td class="last" nowrap="nowrap">
            <?php
			echo( anchor('/news/submit/mode/edit/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor('/news/submit/mode/delete/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
            <?php } ?>
          </tr>
			<?php $rowCount++;
        }
        if ($rowCount == 0) { ?>
            <tr class="empty">
                <td colspan="4" class="results">There were no results</td>   
            </tr>
        <?php } ?>
		</table>
      </div>
      <br clear="all" />
      