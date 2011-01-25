    <script type="text/javascript">
    $(document).ready(function(){		   
		
	});
    </script>
    <div id="center-column" class="dashboard">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <br class="clear" />
		
        <div class='textbox'>
	    <table cellpadding="0" cellspacing="0" border="0" style="width:465px;">
	    <tr class='title'>
	    	<td style='padding:6px'>Admin Functions</td>
	    </tr>
	    <tr>
	    	<td class="hsc2_l" style='padding:6px'>
			<ul class="iconmenu">
				<li><?php echo anchor('/team/submit/mode/edit/id/'.$team_id,'<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit Team Details</li>
                
                <li><?php echo anchor('/team/avatar/'.$team_id,'<img src="'.$config['fantasy_web_root'].'images/icons/image_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit Team Avatar</li>
			</ul>
			</td>
		</tr>
		</table>
		</div>
        
        <p>&nbsp;</p>
    </div>
    <p /><br />