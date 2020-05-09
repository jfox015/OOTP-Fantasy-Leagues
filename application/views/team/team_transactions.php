   		<script type="text/javascript" charset="UTF-8">
		$(document).ready(function(){		   
			$('select#teams').change(function(){
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/transactions/team_id/' + $('select#teams').val();
			});
			$('a[rel=previous]').click(function(){
				$('input#pageId').val(this.id);
				$('input#startIdx').val(($('input#limit').val()*(this.id-1)));
				$('#filterform').submit();
			});
			$('a[rel=first]').click(function(){
				$('input#pageId').val(1);
				$('input#startIdx').val(0);
				$('#filterform').submit();
			});
			$('a[rel=next]').click(function(){
				$('input#pageId').val(this.id);
				$('input#startIdx').val(($('input#limit').val()*(this.id+1)));
				$('#filterform').submit();
			});
			$('a[rel=last]').click(function(){
				$('input#pageId').val(this.id);
				$('input#startIdx').val(($('input#limit').val()*(this.id+1)));
				$('#filterform').submit();
			});
		});
		var totalRecords = <?php echo($recCount); ?>;
		</script>
   		<div id="subPage">
            <div class="top-bar"><h1><?php echo($thisItem['subTitle']); ?></h1></div>
           
           <?php
            /*----------------------------------------------
            /
            /	BEGIN FILTER BAR
            /
            /---------------------------------------------*/
            echo "<div class='textbox'>";
            echo ' <table cellspacing="0" cellpadding="2" border="0">';
            echo "  <tr class='title'><td colspan=11  height='17'>Filters</td></tr>";
            echo "  <form method='post' id='filterform' action='".$config['fantasy_web_root']."team/transactions/' class='inline'>";
            echo "   <tr>";
            ## Num to display
            echo "    <td><label for='view'>Records per page:</label></td>";
            echo "     <td>";
            echo "      <select name='limit' id='limit'>";
            echo '      <option value="-1">All</option>';
            for ($i = 10; $i < 101; $i += 10) {
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
            echo '   <input type="hidden" name="startIndex" id="startIndex" value="'.$startIndex.'" />';
            echo '   <input type="hidden" name="pageId" id="pageId" value="'.$pageId.'" />';
			echo '   <input type="hidden" name="team_id" id="team_id" value="'.$team_id.'" />';
			
            echo "  </form>";
            echo " </table>";
              
            echo "</div>"; 
			?>
            
            <?php if (isset($thisItem['fantasy_teams']) && sizeof($thisItem['fantasy_teams']) > 0 ) {?>
				<div style="width:48%;text-align:right;float:left;">
				<label for="teams" style="min-width:225px;">Fantasy Teams:</label> 
				<select id="teams" style="clear:none;">
					<?php  
					foreach($thisItem['fantasy_teams'] as $id => $teamName) {
						echo('<option value="'.$id.'"');
						if ($id == $team_id	) { echo(' selected="selected"'); }
						echo('>'.$teamName.'</option>');
					}
					?>
				</select>
				</div>
				<?php } ?>
            <div class='textbox'>
                <table style="margin:6px" class="sortable-table" cellpadding="0" cellspacing="0" border="0" width="915px">
                <tr>
				<td><?php if (isset($transaction_summary)) { 
					echo($transaction_summary); 
				} ?>
                </td>
                </tr>
                </table>
            </div>  <!-- end batting stat div -->
        </div>
