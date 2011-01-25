    <script type="text/javascript">
    $(document).ready(function(){		   
		$('#deleteConfirm').click(function(){
			$('#confirmForm').submit();
		});	
		$('#deleteCancel').click(function(){
			history.back(-1);
		});
	});
    </script>
    
    <div id="center-column">
   	<div class="textual_content">
            <?php include_once('admin_breadcrumb.php'); ?>
    		<div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
            <p /><br />
            <FORM ACTION="<?php echo($config['fantasy_web_root']); ?>draft/draftOrder/" METHOD="post">
            <input type='hidden' name='action' value="order" />
            <input type='hidden' name='league_id' value="<?php echo($league_id); ?>" />
            <input type='hidden' name='teamCount' value="<?php echo(sizeof($teams)); ?>" />
            <input type='hidden' name='round' value="<?php echo(sizeof($curRound)); ?>" />
            <table cellpadding="2" cellspacing="0" border="0">
            <TR class='title'><TD colspan="5">Round</TD></TR>
            <TR>
            <TD colspan="2">Display Round:</TD>
			<?php 
			if ($curRound==1) { ?>
            	<TD width='60px'>&nbsp;</TD>
            <?php } else { ?>
            	<TD align='left' width='60px'><INPUT type='button' value='<' onClick="window.location.href='<?php echo($config['fantasy_web_root']); ?>draft/draftOrder/league_id/<?php echo($league_id); ?>/round/<?php echo($curRound-1); ?>';" /></TD>
            <?php } ?>
			<TD width='60px'><INPUT type="text" size="4" maxlength="2" name="round" 
            value="<?php echo($curRound); ?>" 
            onChange="window.location.href='<?php echo($config['fantasy_web_root']); ?>draft/draftOrder/league_id/<?php echo($league_id); ?>/round/'+this.value;"></TD>
            <?php
			if ($curRound==$nRounds) { ?>
            	<TD width='60px'>&nbsp;</TD>
            <?php
			} else { ?>
            <TD align='left' width='60px'><INPUT type="button" value=">" onClick="window.location.href='<?php echo($config['fantasy_web_root']); ?>draft/draftOrder/league_id/<?php echo($league_id); ?>/round/<?php echo($curRound+1); ?>';"></TD>
            <?php } ?>
            </TR>
			<TR class='headline'><TD class='hsc2_l' colspan=5>Draft Order</TD></TR>
            <?php
			for ($i=1;$i<=sizeof($picks);$i++) {
			echo "     <TR><TD width='60px'><label for='pick_$i'>Pick $i:</label></TD>\n";
			echo "         <TD colspan=4>\n";
			echo "          <select name='pick_$i'>\n";
			foreach ($teams as $tid => $val) {
				echo "           <option value='$tid'";
				if (isset($picks[$i]) && $tid==$picks[$i]) { echo " selected"; }
				echo ">".$val['teamname']." ".$val['teamnick']."</option>\n";
			}
			echo "       </select>\n";
			echo "      </TD>\n";
			echo "     </TR>\n";
			}?>
			<TR>
			 <TD><INPUT type='checkbox' value=1 name='applyToRem' style='align=left' /></TD>
			 <TD colspan=3><LABEL for'applyToRem'>Apply to Remaining Rounds</LABEL></TD>
			 <TD>&nbsp;</TD>
			</TR>
			<TR>
			 <TD><INPUT type='checkbox' value=1 name='applySerp' style='align=left' /></TD>
			 <TD colspan=3><LABEL for'applyToRem'>Apply in Serpentine Fashion</LABEL></TD>
			 <TD><INPUT type="submit" value="Save"></TD>
			</TR>
			</table>
            <input type="hidden" value="1" name="submitted" />
			</FORM>
			
            <p /><br />&nbsp;<br />
        </div>
	</div>