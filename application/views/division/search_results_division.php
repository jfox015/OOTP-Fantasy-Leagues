		<!-- BEGIN RESULTS OUTPUT -->
        <div id="center-column">
        <div class="results_browse">
       	<p />&nbsp;<br />
        <?php include_once('admin_breadcrumb.php'); ?>
    	<div id="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        <div class="table">
        <table class="listing" cellpadding="5" cellspacing="2" style="width:400px;">
          <tr>
            <th class="first" width="80%" style="text-align:left;">Name</th>
            <th width="20%" style="text-align:center;">Tools</th>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($divisions as $row) { 
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "bg" : ""); ?>">			
            <td class="first" ><?php echo(anchor('divisions/info/'.$row['id'],$row['division_name'])); ?></td>
            <td class="last" nowrap="nowrap">
            <?php 
			echo( anchor($config['fantasy_web_root'].'divisions/submit/mode/edit/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/edit-icon.gif" border="0" width="16" height="16" alt="Edit" title="Edit" />'));
			echo('&nbsp;');
            echo( anchor($config['fantasy_web_root'].'divisions/submit/mode/delete/id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" border="0" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
          </tr>
			<?php $rowCount++;
        }
        if ($rowCount == 0) { ?>
            <tr class="empty">
                <td class="results">There were no results</td>   
            </tr>
        <?php } ?>
		</table>
        <br class="clear" />
         <img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
        <?php echo( anchor($config['fantasy_web_root'].'divisions/addDivision/league_id/'.$league_id,'Add new division')); ?><br />
        
      </div>
      </div>
      </div>