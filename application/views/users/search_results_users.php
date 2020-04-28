        <div class="textbox">
        <table class="listing" cellpadding="5" cellspacing="0" width="650">
        <tr class="title">
        	<td colspan="7">
            User List
            </td>
            </tr>
          <tr class="headline">
            <th class="first" width="35"></th>
            <th width="150">Name</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Location</th>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">			
            <td class="style1" style="text-align:left;">
            <?php $img = (isset($row['thumb']) && !empty($row['thumb'])) ? $row['thumb'] : DEFAULT_AVATAR; ?>
			<img src="<?php echo(PATH_USERS_AVATARS.$img); ?>" border="0" width="35" height="35" />
            </td>
            <td align="center"><?php 
			$name = $row['firstName']." ";
			if (isset($row['nickName']) && !empty($row['nickName'])) {
				$name .= '&quot;'.$row['nickName'].'&quot; ';
			}
			$name .= $row['lastName'];
			echo(anchor('/user/profiles/'.$row['userId'],$name)); ?></td>
            <td align="center"><?php if (isset($row['gender']) && !empty($row['gender'])) { echo ($row['gender'] == 'm' ? 'Male' : 'Female');  } ?></td>
             <td align="center"><?php 
			 /*if (!function_exists('timespan')) {
					$this->load->helper('date');
				}	
			 $age = timespan(strtotime($row['dateOfBirth']), time());
			 $ageYears = explode(",",$age);
			 
			 echo  $ageYears[0];*/
			 if (isset($row['dateOfBirth']) && $row['dateOfBirth'] != EMPTY_DATE_STR) {
			 	echo date('m/d/Y',strtotime($row['dateOfBirth']));
			 }?></td>
             
             <td align="center"><?php 
			 if (isset($row['country']) && $row['country'] != "Choose Country") {
				 echo $row['country']; 
			 } ?></td>

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