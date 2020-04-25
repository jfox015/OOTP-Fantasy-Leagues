	<?php if ((isset($thisItem['type_id']) && $thisItem['type_id'] == NEWS_PLAYER) && 
			 (isset($thisItem['var_id']) && !empty($thisItem['var_id']) && $thisItem['var_id'] != -1)) { ?>
	<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
	<script type="text/javascript">
    var ajaxWait = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
	$(document).ready(function(){		   
		var obj = new Object();
		obj.id = <?php print($thisItem['var_id']); ?>;
		selectPlayer(obj);	
        
	});
	function cacheBuster() {
		var date = new Date();
		var hash = $.md5(Math.floor(Math.random())+date.toUTCString()).toString();
		return "/uid/"+hash.substr(0,16);
	}
	function selectPlayer(obj) {
		//alert (obj.id);
		var url = "<?php echo($config['fantasy_web_root']); ?>players/getInfo/player_id/"+obj.id+cacheBuster();
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
		var outHTML = '<table cellspacing=0 cellpadding=3 width="320px">';
		outHTML += '<tr align="left" valign="top">';
		outHTML += '<td width="35%">';
		var count = 0;
		var item = data.result.items[0];
		if (item.id != '' && item.player_name != '') {
			outHTML += '<img src="<?php echo($config['ootp_html_report_path']); ?>images/player_'+item.player_id+'.png" border="0" align="left" /></td>';
			outHTML += '<td width="65%"><b><a target="_blank" href="<?php echo($config['fantasy_web_root']); ?>players/info/player_id/'+item.id+'" style="font-weight:bold;font-size:larger;">'+item.player_name+'</a></b><br />';
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
	<?php } ?>
   	<div id="subPage">
       	<div class="top-bar"><h1><?php echo($thisItem['news_subject']); ?></h1></div>
       	<div id="content">
            <div id="metaColumn">
            	<?php 
				if ($loggedIn) { ?>
                <div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="325px">
                <tr class='title'>
                    <td style='padding:3px'>News Tools</td>
                </tr>
                <tr>
                    <td style='padding:12px'>
                    <div id="row">
                    <img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
					<?php echo( anchor('/news/submit/add','Add new news article')); ?>
                    </div>
					<?php if ($accessLevel == ACCESS_ADMINISTRATE) { ?>
                    <div id="row">
                    <img src="<?php echo PATH_IMAGES; ?>icons/edit-icon.gif" width="16" height="16" border="0" alt="Edit" title="Edit" align="absmiddle" /> 
					<b><?php echo(anchor('/news/submit/mode/edit/id/'.$thisItem['id'],'Edit this article')); ?></b>
                    </div>
                    <div id="row">
                    <img src="<?php echo PATH_IMAGES; ?>icons/hr.gif" width="16" height="16" border="0" alt="Delete" title="Delete" align="absmiddle" /> 
					<b><?php echo(anchor('/news/submit/mode/delete/id/'.$thisItem['id'],'Delete this article')); ?></b>
                    </div>
                    <?php } ?>
                    </td>
                </tr>
                </table>
                </div>
                <?php } 
				//------------------------------------------------
				// PLAYER INFO BOX
				//------------------------------------------------
				if (isset($thisItem['type_id']) && $thisItem['type_id'] == NEWS_PLAYER) { ?>
                <div class="textbox">
                <table cellspacing=0 cellpadding=3 width="320px">
                <tr class='title'><td colspan=3>Player Details</td></tr>
                <tr class='s1'>
                <td>
                <div id="playerDetails">
                <table cellspacing=0 cellpadding=3 width="320px">
                <tr align="left" valign="top">
                    <td width="35%">
                    <img src='<?php echo($config['ootp_html_report_path']); ?>images/default_player_photo.png' border="0" align="left" />
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
                <?php }
				//------------------------------------------------
				// UPDATE 1.0.2
				//------------------------------------------------
				// SOCIAL MEDAI BOX AND OPTION				
				if (isset($config['sharing_enabled']) && $config['sharing_enabled'] == 1) { ?>
			    <div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="325px">
                <tr class='title'>
                    <td style='padding:3px'>Share this story</td>
                </tr>
                <tr>
                	<td style='padding:12px'>
                    <?php 
                    $buttonsDrawn = false;
                    if (isset($config['share_addtoany']) && $config['share_addtoany'] == 1) { ?>
                    <!-- AddToAny BEGIN -->
                    <!-- AddToAny BEGIN -->
                    <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
                    <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                    <a class="a2a_button_facebook"></a>
                    <a class="a2a_button_twitter"></a>
                    <a class="a2a_button_email"></a>
                    <a class="a2a_button_reddit"></a>
                    </div>
                    <script>
                    var a2a_config = a2a_config || {};
                    a2a_config.onclick = 1;
                    </script>
                    <script async src="https://static.addtoany.com/menu/page.js"></script>
                    <!-- AddToAny END -->
                    <!-- AddToAny END -->
                    <?php } ?>
                    </td>
                </tr></table>
                </div>
                <?php } ?>
                
                <div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="325px">
                <tr class='title'>
                    <td style='padding:3px'>Related News</td>
                </tr>
                <tr>
                    <td style='padding:12px'>
                    <?php if (isset($thisItem['related']) && sizeof($thisItem['related']) > 0) {
						foreach($thisItem['related'] as $article) { ?>
                        <div id="row">
                       <?php echo(anchor('/news/info/'.$article['id'],$article['news_subject'])); ?>
                        </div>
						<?php	
						}
					} else { ?>
                    <div id="row">
                    No related news articles were found.
                    </div>
                    <?php } ?>
                    <br /><br />
                    <?php echo(anchor('search/doSearch/news/','All News',array('style'=>'font-weight:bold;'))); ?>
                    </td>
                </tr>
                </table>
                </div>
            </div>
           
            <div id="detailColumn">
            
                <b>Date:</b> &nbsp;<?php echo(date('m/d/Y',strtotime($thisItem['news_date']))); ?>
                <br /><br />
				<?php
				if (!empty($thisItem['author'])) { ?>
                <b>Author:</b>&nbsp;
                <?php echo anchor('/user/profile/'.$thisItem['author_id'], $thisItem['author']); ?>
                <br /><br />
                <?php 
                } // END if
				?>
                <?php if (isset($thisItem['image']) && !empty($thisItem['image'])) { 
                // GET IMAGE DIMENSIONS
                $size = getimagesize(DIR_WRITE_PATH.'images/news/'.$thisItem['image']);
                if (isset($size) && sizeof($size) > 0) {
                    if ($size[0] > $size[1]) {
                        $class = "wide";
                    } else {
                        $class = "tall";
                    } // END if
                } // END if
                ?>
                <img src="<?php echo(PATH_NEWS_IMAGES.$thisItem['image']); ?>" align="left" border="0" class="league_news_<?php echo($class); ?>" />
                <?php } ?>
                <?php 
                if (!empty($thisItem['news_body'])) { ?>
                <?php echo($thisItem['news_body']); ?>
                <br /><br />
                <?php 
                } // END if
				if ($thisItem['type_id'] == NEWS_PLAYER && !empty($thisItem['var_id'])) {
					echo( anchor('/players/info/'.$thisItem['var_id'],'View Players Page<br />'));
				}
                ?>
            </div>

        </div>
	</div>
