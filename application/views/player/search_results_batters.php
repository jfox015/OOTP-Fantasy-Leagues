		<div class='textbox' width=100%>

      <table width=100% cellpadding=0 cellspacing=0 border=0><tr class='h1_l'><td>2010 Batting Stats</td></tr></table>
      <table cellpadding=5 cellspacing=2 border=0 class='sortable' style='width:935px;'><thead>    <tr class='hsn2' class='sortable'>
         <td class='hsn2_l'>Name</td>     <td class='hsn2_l'>POS</td>	<td class='hsn2'>G</td><td class='hsn2'>AB</td><td class='hsn2'>R</td><td class='hsn2'>H</td><td class='hsn2'>2B</td><td class='hsn2'>3B</td><td class='hsn2'>HR</td><td class='hsn2'>RBI</td><td class='hsn2'>BB</td><td class='hsn2'>K</td><td class='hsn2'>SB</td><td class='hsn2'>CS</td><td class='hsn2'>AVG</td><td class='hsn2'>OBP</td><td class='hsn2'>SLG</td><td class='hsn2'>OPS</td>
    
        <td class='hsn2'>XBH</td><td class='hsn2'>WALK%</td><td class='hsn2'>WIFF%</td>
        <td class='hsn2'>FPTS</td></tr></thead>
        <tbody>

		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
        <tr bgcolor='#fff'>
        	<td class="style1" style="text-align:left;"><?php echo(anchor('player/info/'.$row['id'],$row['name'])); ?></td>
            <td><?php echo(get_pos($row['position'])); ?></td>
            <td>8</td>
            <td>38</td>
            <td>7</td>
            <td>10</td>
            <td>3</td>
            <td>0</td>
            <td>2</td>
            <td>5</td>
            <td>1</td>
            <td>11</td>
            <td>0</td>
            <td>0</td>
            <td>.263</td>
            <td>.282</td>
            <td>.500</td>
            <td>.782</td>
            <td>5</td>
            <td>2%</td>
            <td>28%</td>
            <td style='font-weight:bold;'>21</td>
        </tr>


		<tr class="<?php echo(($rowCount % 2) == 0 ? "bg" : ""); ?>">			
            
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
		</tbody>
        </table>
        </div>  <!-- end batting splits div -->

        <br class="clear" />
      </div>