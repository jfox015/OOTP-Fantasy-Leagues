	<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){		   
		$('a[rel=previous]').click(function(){
			$('input#pageId').val(this.id);
			$('input#startIdx').val(($('input#limit').val()*(this.id-1)));
			$('#filterform').submit();
			return false;
		});
		$('a[rel=first]').click(function(){
			$('input#pageId').val(1);
			$('input#startIdx').val(0);
			$('#filterform').submit();
			return false;
		});
		$('a[rel=next]').click(function(){
			$('input#pageId').val(this.id);
			$('input#startIdx').val(($('input#limit').val()*(this.id+1)));
			$('#filterform').submit();
			return false;
		});
		$('a[rel=last]').click(function(){
			$('input#pageId').val(this.id);
			$('input#startIdx').val(($('input#limit').val()*(this.id+1)));
			$('#filterform').submit();
			return false;
		});
	});
	var totalRecords = <?php echo($recCount); ?>;
	</script>
    <div id="subPage">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <div id="content">
			<?php
            /*----------------------------------------------
            /
            /	BEGIN FILTER BAR
            /
            /---------------------------------------------*/
            echo "<div class='textbox'>";
            echo ' <table style="width:100%" cellspacing="0" cellpadding="2" border="0">';
            echo "  <tr class='title'><td colspan=11  height='17'>Filters</td></tr>";
            echo "  <form method='post' id='filterform' action='".$config['fantasy_web_root']."players/stats' class='inline'>";
            echo "   <tr>";
            
            // YEAR DROP DOWN
            echo '    <td class="formLabel">Year:</td>';
            echo "     <td>";
            echo "      <select name='year' id='year'>";
            if (isset($years))
             {
               foreach ($years as $year) {
                  echo("<option value='$year'");
                  if ($year==$lgyear) {echo " selected";}
                  echo ">$year</option>";
                }
             }
            echo "      </select>";
            echo "     </td>";
            
            ## Show free agent status filter
            // ONLY AVAILABLE WHEN A LEAGUE ID IS PASSED
            if (isset($league_id) && !empty($league_id) && $league_id != -1) {
                
                echo '    <td class="formLabel">Roster Status:</td>';
                echo "     <td>";
                echo "      <select name='roster_status' id='roster_status'>";
                $types = array(-1=>"Free Agents",1=>"All Players",3=>"Waiver Wire");
                foreach ($types as $key => $val) {
                    echo("<option value='$key'");
                    if ($key==$roster_status) {echo " selected";}
                    echo ">$val</option>";
                }
                echo "      </select>";
                echo '      <input type="hidden" name="league_id" value="'.$league_id.'" />';
                echo "     </td>";
            
            } // END if
            
            ## Show type Filter
            echo '    <td class="formLabel">Player Type:</td>';
            echo "     <td>";
            echo "      <select name='player_type' id='player_type'>";
            $types = array(1=>"Batters",2=>"Pitchers");
            foreach ($types as $key => $val) {
                echo("<option value='$key'");
                if ($key==$player_type) {echo " selected";}
                echo ">$val</option>";
            }
            echo "      </select>";
            echo "     </td>";
            
            ## Show position Filter
            if ($player_type == 1) {
                $pos = array(-1,2,3,4,5,6,7,8,9,10,20);
                echo '    <td class="formLabel">Position:</td>';
                echo "     <td>";
                echo "      <select name='position_type' id='position_type'>";
                
                foreach ($pos as $pos_id) {
                    echo("<option value='$pos_id'");
                    if ($pos_id==$position_type) {echo " selected";}
                    echo ">".get_pos($pos_id)."</option>";
                }
                echo "      </select>";
                echo "     </td>";
            
            } else {
                $roles = array(-1,11,12,13);	
                echo '    <td class="formLabel">Role:</td>';
                echo "     <td>";
                echo "      <select name='role_type' id='role_type'>";
                foreach ($roles as $role) {
                    echo("<option value='$role'");
                    if ($role==$role_type) {echo " selected";}
                    echo ">".get_pos($role)."</option>";
                }
                echo "      </select>";
                echo "     </td>";
            }
            ## Num to display
            echo '    <td class="formLabel">Records per page:</td>';
            echo "     <td>";
            echo "      <select name='limit' id='limit'>";
            echo '      <option value="-1">All</option>';
            for ($i = 20; $i < 201; $i += 20) {
                echo("<option value='$i'");
                if ($i == $limit) {echo " selected";}
                echo ">$i</option>";
            }
            echo "      </select>";
            echo "     </td>";
            
            ## Close Form
            echo "    <td align='right'>\n";
            echo "     <input type='submit' class='submitButton' value='Go' />\n";
            echo "    </td>\n";
            echo "   </tr>\n";
            echo '   <input type="hidden" name="startIdx" id="startIdx" value="'.$startIdx.'" />';
            echo '   <input type="hidden" name="pageId" id="pageId" value="'.$pageId.'" />';
            echo "  </form>";
            echo " </table>";
              
            echo "</div>";
			/*------------------------------------------------
            /
            /	BEGIN STATS TABLE
            /
            /-----------------------------------------------*/
            ?>
            <div class="textbox" width="100%">
                <!-- HEADER -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr class="title">
                <td height="17" style="padding:6px;"><?php echo($title); ?> Stats
                <?php
                if ($limit != -1) {
                     echo(" Showing ".$limit." of ".$recCount." records)");
                 }
                 ?>
                 </td>
             </tr>
             </table>
             
            <?php
            if (isset($formatted_stats)){
				echo($formatted_stats);						 
			}
            ?>
            </div>
		</div>
    </div>
    <p /><br />