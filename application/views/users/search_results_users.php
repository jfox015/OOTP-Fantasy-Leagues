        <div class="textbox">
        <table class="listing" cellpadding="5" cellspacing="0">
        <tr class="title">
        	<td colspan="7">
            User List
            </td>
            </tr>
          <tr class="headline">
            <td class="first hsc2_c" width="55"></td>
            <td class="hsc2_c" width="50%">Name</td>
            <td class="hsc2_c" width="15%">Gender</td>
            <td class="hsc2_c" width="15%">Age</td>
            <td class="hsc2_c" width="15%">Location</td>
          </tr>
		<?php 
        $rowCount = 0;
        foreach ($searchResults as $row) { 
            ?>
		<tr class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">			
            <td class="style1 hsc2_c">
            <?php $img = (isset($row['avatar']) && !empty($row['avatar'])) ? $row['avatar'] : DEFAULT_AVATAR; ?>
			<img src="<?php echo(PATH_USERS_AVATARS.$img); ?>" width="35" height="35" />
            </td>
            <td class="hsc2_c"><?php 
			$name = $row['firstName']." ";
			if (isset($row['nickName']) && !empty($row['nickName'])) {
				$name .= '&quot;'.$row['nickName'].'&quot; ';
			}
			$name .= $row['lastName'];
			echo(anchor('/user/profiles/'.$row['userId'],$name)); ?></td>
            <td class="hsc2_c"><?php if (isset($row['gender']) && !empty($row['gender'])) { echo ($row['gender'] == 'm' ? 'Male' : 'Female');  } ?></td>
            <td class="hsc2_c"><?php 

			 if (isset($row['dateOfBirth']) && $row['dateOfBirth'] != EMPTY_DATE_STR) {
                $years = 60*60*24*365;
                $now = time();
                $diff = $now - strtotime($row['dateOfBirth']." 00:00:00");
                $years = $diff / $years;
			 	echo intval($years);
			 }?></td>
             
             <td class="hsc2_c"><?php 
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