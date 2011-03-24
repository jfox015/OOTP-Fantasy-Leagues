	<script type="text/javascript" src="<?php print($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
	<script type="text/javascript" src="<?php print($config['fantasy_web_root']); ?>js/nicEdit.js"></script>
	<script type="text/javascript">
    var ajaxWait = '<img src="<?php print($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php print($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
	var league_id = <?php print($league_id); ?>;
	$(document).ready(function(){		   
		$('#delete').click(function() {
			document.location.href = '<?php print($config['fantasy_web_root']); ?>news/delete/<?php print($thisItem['id']); ?>';
		});	
		$('#cancel').click(function() {
			<?php if (isset($thisItem['id']) && ($thisItem['id'] != "add" && $thisItem['id'] != -1)) { ?>
			document.location.href = '<?php print($config['fantasy_web_root']); ?>news/info/<?php print($thisItem['id']); ?>';
			<?php } else { ?>
			history.back(-1);
			<?php } ?>
		});
		
		$('#var_id').change(function () {
			var obj = new Object();
			obj.id = $('#var_id').val();
			selectPlayer(obj);
		});
		<?php if ((isset($type_id) && $type_id == NEWS_PLAYER) && (isset($var_id) && !empty($var_id) && $var_id != -1)) { ?>
		var obj = new Object();
		obj.id = <?php print($var_id); ?>;
		selectPlayer(obj);	
        <?php } ?>
	});
	bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('news_body');
	});
	function cacheBuster() {
		var date = new Date();
		var hash = $.md5(Math.floor(Math.random())+date.toUTCString()).toString();
		return "/uid/"+hash.substr(0,16);
	}
	function selectPlayer(obj) {
		//alert (obj.id);
		var url = "<?php print($config['fantasy_web_root']); ?>players/getInfo/player_id/"+obj.id+cacheBuster();
		$('div#playerDetails').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#playerDetails').empty();
			if (data.code.indexOf("200") != -1) {
				$('div#playerDetails').append(drawPlayerInfo(data));
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#draftStatus').addClass(status[0].toLowerCase());
					$('div#draftStatus').html(status[1]);
				} else {
					$('div#draftStatus').addClass('success');
					$('div#draftStatus').html('Player Info Not Found');
				}
				$('div#draftStatusBox').fadeIn("slow",function() { setTimeout('fadeStatus("active")',15000); });
			} else {
				var outHTML = '<tr align="left" valign="top">';
				outHTML += '<td colspan="3">No player info found.</td>';
				outHTML += '</tr>';
				$('div#playerDetails').append(outHTML);
			}
		});	
	}
	function drawPlayerInfo(data) {
		var outHTML = '<table cellspacing=0 cellpadding=3 width="250px">';
		outHTML += '<tr align="left" valign="top">';
		outHTML += '<td width="35%">';
		var count = 0;
		var item = data.result.items[0];
		if (item.id != '' && item.player_name != '') {
			outHTML += '<img src="<?php print($config['ootp_html_report_path']); ?>images/player_'+item.player_id+'.png" border="0" align="left" /></td>';
			outHTML += '<td width="65%"><b><a target="_blank" href="<?php print($config['fantasy_web_root']); ?>players/info/player_id/'+item.id+'" style="font-weight:bold;font-size:larger;">'+item.player_name+'</a></b><br />';
			outHTML += item.team_name+'<br />';
			if (item.pos == 1) {
				outHTML += item.role+'<br />';
			} else {
				outHTML += item.position+'<br />';
			}
			outHTML += '</td>';
			outHTML += '</tr>';
			count++;
		}
		outHTML += '</tr>';
		outHTML += '</table>';
		return outHTML;
	}
    </script>
    <div id="single-column">
    	<div class="top-bar"> <h1><?php print $subTitle; ?></h1></div>
        <br class="clear" />
        
    </div>
    <div id="center-column">
        <?php if (isset($dump) && !empty($dump)) {
			print("<h3>DEBUG: Object Data Dump:</h3><br />".$dump."<br />");
		} ?>
        <?php if (isset($preview) && !empty($preview)) { ?>
        	<?php print($preview); ?>
		<?php } ?>
        <div class="textbox">
        <table cellpadding="0" cellspacing="0" style="width:625px;">
          <tr class="title">
            <td style="padding:0 0 4px 6px;height:25px;">Enter the information for this news article below.</td>
          </tr>
          <tr>
            <td width="100%">
            <?php 
				$errors = validation_errors();
				if ($errors) {
					print '<div class="error">The following errors were found with your submission:<br /><ul>'.$errors.'</ul></div>';
				}
				print($form);
                ?>
            
            </td>
          </tr>
        </table>
      </div>
    </div>
    <?php if (isset($type_id) && $type_id == NEWS_PLAYER) { ?>
    <div id="right-column">
        <div class="textbox" style="width:261px;">
        <table cellspacing=0 cellpadding=3 width="250px">
        <tr class='title'><td colspan=3>Player Details</td></tr>
        <tr class='s1'>
        <td>
        <div id="playerDetails">
        <table cellspacing=0 cellpadding=3 width="250px">
        <tr align="left" valign="top">
        	<td width="35%">
            <img src='<?php print($config['ootp_html_report_path']); ?>images/default_player_photo.jpg' border="0" align="left" />
            </td>
            <td width="65%">
            <b>No Player Selected.</b><br /><br />Select a player from the "Select Player" drop down to preview their information.
            <br />
        	</td>
        </tr>
        </table>
        </div>
        </td>
        </tr>
        </table>
		</div>
		&nbsp;
    	<br clear="all" /> 
    </div>
    <?php } ?>
    <p /><br />