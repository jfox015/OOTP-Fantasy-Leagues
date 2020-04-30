	<?php 
	$htmlpath=$config['ootp_html_report_path'];
	?>
	<script type="text/javascript" src="<?php print($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
	<script type="text/javascript" src="<?php print($config['fantasy_web_root']); ?>js/nicEdit.js"></script>
	<script type="text/javascript">
    var ajaxWait = '<img src="<?php print($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php print($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
	//var league_id = <?php print($league_id); ?>;
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
		$('div.player_info').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div.player_info').empty();
			$('#details_title').empty();
			$('#details_headline').empty();
			if (data.code.indexOf("200") != -1) {
				$('div.player_info').append(drawPlayerInfo(data));
				var item = data.result.items[0];
				var titleName = "Not Found";
				var headline = "";
				if (item.id != '' && item.player_name != '') {
					titleName = item.player_name + " Details";
					headline = item.pos + " " + item.player_name;
				}
				$('#details_title').append(titleName);
				$('#details_headline').append(headline);
			} else {
				var outHTML = '<div class="playerpic" style="float:left;width:50px;">';
				outHTML += '<img src="<?php echo($htmlpath); ?>images/person_pictures/default_player_photo.png">';
				outHTML += '</div>';
                outHTML += '<div class="player" style="float:left; width: 65%;padding: 0 8px;">';
                outHTML += 'The selected Player was not found.';
				outHTML += '</div>';
				$('div.player_info').append(outHTML);
			}
		});	
	}
	function drawPlayerInfo(data) {
		var outHTML = '';
		var item = data.result.items[0];
		if (item.id != '' && item.player_name != '') {
			outHTML += '<div class="playerpic" style="float:left;width:80px;">';
			outHTML += "<img src='<?php echo($htmlpath); ?>images/person_pictures/player_" + item.player_id + ".png' width='80'>";
			outHTML += '<div class="player" style="float:left; width: 65%;padding: 0 8px;">';
			outHTML += '<strong>Team:</strong> ' + item.team_name+'<br />';	    
			outHTML += '</div>';
		}
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
    <!-- RIGHT COLUMN -->
	<div id="right-column">
    <?php echo($secondary); ?>
	</div>