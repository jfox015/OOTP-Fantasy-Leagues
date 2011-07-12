<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
<style>
#listRow div { display: inline; padding-right:3px; } 
.noShow { display:none; }
</style>
<script type="text/javascript" charset="UTF-8">
	var ajaxWait = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
	var fader = null;
	var team_id = <?php echo($user_team_id); ?>;
	var league_id = <?php echo($league_id); ?>;
	var fader = null;
	var curr_type = "pos";
	var curr_param = 2;
	var totalRecords = <?php echo($recCount); ?>;
	var players_max = 200;
	var draftList = new Array(players_max);
	var playerCache = new Array(players_max);
	var pageChanged = false;
	
	$(document).ready(function(){		   

		// PAGINATION NAVIGATION
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
		$('select#owners').change(function(){
		 	document.location.href = '<?php echo($config['fantasy_web_root']); ?>draft/selection/league_id/<?php echo($league_id); ?>/act_as_id/' + $('select#owners').val();
		});
		// DRAFT PLAYER BUTTONS
		// PICK PLAYER FOR CONFIRMATION
		$('a[rel=draft]').live('click',function (e) {
			e.preventDefault();
			var obj = new Object();
			obj.id = this.id;
			selectPlayer(obj);
		});
		// SUBMIT OFFICIAL DRAFT CHOICE
		// Not currently used
		/*$('#draft').click(function(e){
			e.preventDefault();
			addPick(this.id);
			getPicks();
			getPlayers();
		});*/
		$('div#activeStatusBox').hide();

		/*----------------------------------------------
		/	DRAFT LISTS
		/---------------------------------------------*/

		// EVENT HANDLERS
		
		// ADD TO LIST
		$('a[rel=itemPick]').live('click',function (e) {
			updatePlayerLists(this.id);
			draftListButtonClick(e, false);
		});	
		// REMOVE FROM LIST
		$('a[rel=remove]').live('click',function (e) {
			offList(draftList,this.id);
			draftListButtonClick(e, true);
		});
		// CLEAR LIST
		$('a[rel=clearAll]').live('click',function (e) {
			clearList(draftList);
			draftListButtonClick(e, true);	
		});
		// CHANGE LIST ORDER
		$('a[rel=movePick]').live('click',function (e) {
			var params = this.id.split("|");
			moveItemInList(draftList,params[0],params[1]);
			draftListButtonClick(e, true);				
		});
		// SAVE LIST
		$('a[rel=saveList]').live('click',function (e) {
			e.preventDefault();
			var draftListStr = '';
			for (var i = 0; i < draftList.length; i++) {
				if (draftList[i] != null && draftList[i].id != -1) {
					if (draftListStr != '') { draftListStr += "_"; }
					draftListStr += draftList[i].id;
				}
			}
			if (draftListStr == '') {
				draftListStr = 'empty';
			}
			var url = "<?php echo($config['fantasy_web_root']); ?>draft/saveDraftList/league_id/"+league_id+"/player_id_list/"+draftListStr;
			<?php if ($team_override) { ?> url += '/user_id/<?php print($team_owner_id); ?>';<? } ?>
			url += cacheBuster();
			$('div#activeList').html(ajaxWait);
			$.getJSON(url, function(data){
				if (data.code.indexOf("200") != -1) {
					var statusOut = '';
					var cssOut = '';
					if (data.status.indexOf(":") != -1) {
						var status = data.status.split(":");
						cssOut = status[0].toLowerCase();
						statusOut = status[1];
					} else {
						cssOut = 'success';
						statusOut = 'Draft List Saved Successfully.';
						if (pageChanged) pageChanged = false;
						buttonDisplay('optSave',false);
						$('div#activeList').empty();
						$('div#activeList').append(drawPicks(draftList));
					}
					updateStatus('activeStatus','activeStatusBox',statusOut, cssOut);
				} else {
					updateStatus('activeStatus','activeStatusBox','Save Error: Draft List was not saved.', 'error');
				}
			});
			//} else {
			//	updateStatus('activeStatus','activeStatusBox','Save Error: Draft List is empty.', 'error');
			//}				
		}); // END a[rel=saveList]').live('click')
		
		// RELOAD USER DRAFT PICK LIST
		$('a[rel=reloadDraftList]').live('click',function (e) {
			e.preventDefault();
			loadUserPicks();
		});
		
		// ALERT USER ON PAGE UNLOAD THAT DRAFT LIST MAY NOT BE SAVED
		$(window).bind('beforeunload', function(){ 
			if (pageChanged) {
				return "WARNING: There are unsaved draft list changes on this page. If you exit without saving, your changes will be lost. \n\nPress 'OK' to continue leaving this page or 'Cancel' to stop and save your changes first.";
			} else {
				return "There may be unsaved changes on this page. Are you sure you want to exit? Click 'Cancel' to stop unload and save your changes first.";
			}
		}); 
		/*---------------------------
		// DEAFULT PAGE ACTIONS
		/--------------------------*/
		loadList();
		loadUserPicks();
		<?php if (isset($player_id) && $player_id != -1) { ?>
		var obj = new Object();
		obj.id = <?php echo($player_id); ?>;
		selectPlayer(obj);
		<?php } ?>
	});
	/*-----------------------------------------------
	/	PUBLIC JS FUNCTIONS
	/----------------------------------------------*/
	function draftListButtonClick(e, updateList) {
		e.preventDefault();
		if (!pageChanged) pageChanged = true;
		buttonDisplay('optSave',true);
		if (updateList) {
			$('div#activeList').empty();
			$('div#activeList').append(drawPicks(draftList));
		}
	}
	function updateStatus(contentDiv, boxDiv, status, cssClass) {
		removeStatusCSSClasses(contentDiv);
		$('div#'+contentDiv).addClass(cssClass);
		$('div#'+contentDiv).html(status);
		$('div#'+boxDiv).fadeIn("slow",function() { setTimeout('fadeStatus("'+boxDiv+'")',15000); });
	}
	function removeStatusCSSClasses(divId) {
		var classes = ['success','notice','warn','error','info'];
		for (var i = 0; i < classes.length; i++) {
			$('div#'+divId).removeClass(classes[i]);
		}
		return true;
	}
	function fadeStatus(type) {
		///alert("Fade out");
		$('div#'+type).fadeOut("normal",function() { clearTimeout(fader); $('div#'+type).hide(); });
	}
	function Player() {
		this.id = -1;
		this.player_name = '';
		this.position = '';
		this.role = '';
	}
	function copyToPlayer(object) {
		var player = new Player();
		player.id = object.id;
		player.player_name = object.player_name;
		if (object.position) player.position = object.position;
		if (object.role) player.role = object.role;
		return player;
	}
	/**
	 *	CACHE BUSTER.
	 *	This function appends a unqiue string to the end of ajax requests to prevent caching in IE and FireFox
	 *	@since	1.0.2
	 */
	function cacheBuster() {
		var date = new Date();
		var hash = $.md5(Math.floor(Math.random())+date.toUTCString()).toString();
		return "/uid/"+hash.substr(0,16);
	}
	/*------------------------------------------------
	/	PLAYER LIST FUNCTIONS
	/-----------------------------------------------*/
	function addToList(list,player) {
		var added = false;
		for (var i = 0; i < list.length; i++) {
			if (list[i] == null ||(list[i] != null && list[i].id == -1)) {
				list[i] = player;
				added = true;
				break;
			}
		}
		return added;
	}
	function offList(list,id) {
		var removed = false;
		for (var i = 0; i < list.length; i++) {
			if (list[i] != null && list[i].id != -1) {
				if (list[i].id  == id){
					list[i] = null;
					removed = true;
					break;
				}
			}
		}
		return removed;
	}
	function clearList(list) {
		for (var i = 0; i < list.length; i++) {
			list[i] = null;
		}
		return true;
	}
	function listLength(list) {
		count = 0;
		for (var i = 0; i < list.length; i++) {
			if (list[i] != null && list[i].id != -1) {
				count++;
			}
		}
		return count;
	}
	function moveItemInList(list, id, direction) {
		// First, lots of error handling
		if ((list != null && listLength(list) > 0) && (id != null && id != '') && (direction != null && direction != '')) {
			var tmpItem = null;
			for (var i = 0; i < list.length; i++) {
				if (list[i] != null && list[i].id != null && list[i].id != -1) {
					if (list[i].id == id) {
						tmpItem = copyToPlayer(list[i]);
						switch(parseInt(direction)) {
							case 1: // UP
								if (list[i-1] != null) {
									var tmp2 = copyToPlayer(list[i-1]);
									list[i] = tmp2;
									list[i-1] = tmpItem;
								} // END if
								break;
							case 2:
								if (list[i+1] != null) {
									var tmp2 = copyToPlayer(list[i+1]);
									list[i] = tmp2;
									list[i+1] = tmpItem;
								} // END if
								break;
							default:
								break;
						} // End switch
					} // END if
				} // END if
			} // END for
			return true;
		} else {
			return false;
		} // END if
	} // END function
	
	function buttonDisplay(divId,status) {
		var displayType = "block";
		if (!status) displayType = "none";
		$('div#'+divId).css('display',displayType);
	}
	function updatePlayerLists(player_id) {
		var found = false;
		var errorListName = "";
		if (draftList.length > 0) {
			for (var i = 0; i < draftList.length; i++) {
				if (draftList[i] != null && draftList[i].id != -1 && player_id == draftList[i].id) {
					found = true;
					errorListName = "draft";
					break;
				}
			}
		}
		if (!found) {
			if (listLength(draftList) >= players_max) {
				updateStatus('activeStatus','activeStatusBox',"You can only add a maximum of "+players_max+" players at a time.", 'error');
			} else {
				// SEE if player info is cached (added and removed form a list already)
				var player = new Player();
				for (var i = 0; i < playerCache.length; i++) {
					if (playerCache[i] != null && playerCache[i].id != -1) {
						if (player_id == playerCache[i].id) {
							player = copyToPlayer(playerCache[i]);
							break;
						}
					}
				}
				if (player.id == -1) {
					var url = "<?php echo($config['fantasy_web_root']); ?>players/getInfo/player_id/"+player_id+cacheBuster();
					$('div#activeStatus').removeClass('error');
					$('div#activeStatus').removeClass('success');
					$('div#activeStatus').html(ajaxWait);
					$('div#activeStatusBox').fadeIn("fast");
					$.getJSON(url, function(data){
						if (data.code.indexOf("200") != -1) {
							if (data.status.indexOf(":") != -1) {
								var status = data.status.split(":");
								$('div#activeStatus').addClass(status[0].toLowerCase());
								$('div#activeStatus').html(status[1]);
							} else {
								player = copyToPlayer(data.result.items[0]);
								addToList(playerCache,player);
								addToList(draftList,player);
								updateStatus('activeStatus','activeStatusBox',player.player_name + " added to list.", 'success');
							}
						} else {
							$('div#activeStatus').addClass('error');
							$('div#activeStatus').append('No information was returned for the selected player.');
						}
						$('div#activeList').empty();
						$('div#activeList').append(drawPicks(draftList));
					});
				} else {
					addToList(draftList,player);
					$('div#activeList').empty();
					$('div#activeList').append(drawPicks(draftList));
					updateStatus('activeStatus','activeStatusBox',player.player_name + " added to list.", 'success');
				}
			}
		} else {
			updateStatus('activeStatus','activeStatusBox',"This player already appears on your "+errorListName+" list.", 'error');
		}
	}
	function selectPlayer(obj) {
		var url = "<?php echo($config['fantasy_web_root']); ?>players/getInfo/player_id/"+obj.id+cacheBuster();
		$('div#playerSelected').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#playerSelected').empty();
			if (data.code.indexOf("200") != -1) {
				$('div#playerSelected').append(drawPlayerInfo(data));
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
				$('div#playerSelected').append(outHTML);
			}
		});	
	}
	function loadList(obj) {
		var url = "<?php echo($config['fantasy_web_root']); ?>draft/getPicks/league_id/"+league_id;
		<?php if ($team_override) { ?> url += '/user_id/<?php print($team_owner_id); ?>';<? } ?>
		url += cacheBuster();
		$('div#activeList').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#activeList').empty();
			if (data.code.indexOf("200") != -1) {
				$.each(data.result.items, function(i,item){
					var player = copyToPlayer(item);
					addToList(playerCache,player);
					addToList(draftList,player);
				});
				$('div#activeList').append(drawPicks(draftList));
			} else {
				var outHTML = '<tr align="left" valign="top">';
				outHTML += '<td colspan="3">No players have been added yet.</td>';
				outHTML += '</tr>';
				$('div#activeList').append(outHTML);
			}
		});
	}
	function loadUserPicks(obj) {
		var url = "<?php echo($config['fantasy_web_root']); ?>draft/getResults/league_id/"+league_id;
		<?php if ($team_override) { ?> url += '/user_id/<?php print($team_owner_id); ?>';<? } ?>
		url += cacheBuster();
		$('div#activeResults').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#activeResults').empty();
			if (data.code.indexOf("200") != -1) {
				$('div#activeResults').append(drawResults(data));
			} else {
				var outHTML = '<tr align="left" valign="top">';
				outHTML += '<td colspan="3">No players have been picked yet.</td>';
				outHTML += '</tr>';
				$('div#activeResults').append(outHTML);
			}
		});
	}
	function drawPlayerInfo(data) {
		var outHTML = '<table cellspacing=0 cellpadding=3 width="375px">';
		outHTML += '<tr align="left" valign="top">';
		outHTML += '<td width="35%">';
		var count = 0;
		var item = data.result.items[0];
		if (item.id != '' && item.player_name != '') {
			outHTML += '<img src="<?php echo($config['ootp_html_report_path']); ?>images/player_'+item.player_id+'.png" border="0" align="left" /></td>';
			outHTML += '<td width="65%"><b><a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/'+item.id+'" style="font-weight:bold;font-size:larger;">'+item.player_name+'</a></b><br />';
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
		if (count == 0) {
			outHTML += '<tr align="left" valign="top">';
            outHTML += '<td colspan="3">No players have been picked yet</td>';
            outHTML += '</tr>';
		} else {
			$('input#pick').val(item.id);
			$('input#btnSubmit').css('display','block');
		}
		outHTML += '</tr>';
		outHTML += '</table>';
		return outHTML;
	}
	
	function drawResults(data) {
		var outHTML = '<table style="width:250px;" cellpadding="2" cellspacing="0" border="0">';
		var count = 0;
		var itemCount = 0;
		$.each(data.result.items, function(i,item){	
			itemCount++;
		});
		$.each(data.result.items, function(i,item){	
			outHTML += '<tr align="left" valign="top" class="s'+((count%2)+1)+'_l">';
			if (item.id != '' && item.player_name != '') {
				outHTML += '<td width="20%">'+item.draft_round+'</td>';
				outHTML += '<td width="20%">'+item.draft_pick+'</td>';
                outHTML += '<td width="60%"><a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/'+item.id+'">'+item.player_name+'</a>, '+item.position+'</td>';
                outHTML += '</tr>';
				count++;
			}
		});
		if (count == 0) {
			outHTML += '<tr align="left" valign="top">';
            outHTML += '<td colspan="3">No players have been picked yet</td>';
            outHTML += '</tr>';
		}
		outHTML += '</table>';
		return outHTML;
	}
	function drawPicks(data) {
		var outHTML = '<table style="width:245px;" cellpadding="2" cellspacing="0" border="0">';
		var count = 0;
		var itemCount = 0;
		var itemArr = data;
		if (data) {
			// JSON DATA OBJECT
			if (data.result && data.result != null && data.result.items && data.result.items != null) {
				for (k in data.result.items) if (data.result.items.hasOwnProperty(k)) itemCount++;
				itemArr = data.result.items;
				$.each(itemArr, function(i, item) {
					outHTML += '<tr align="left" valign="top" class="s'+((count%2)+1)+'_l">';
					if (item.id != '' && item.player_name != '') {
						outHTML += '<td>'+item.rank+'</td>';
						outHTML += '<td><a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/'+item.id+'">'+item.player_name+'</a>, '+item.position+'</td>';
						outHTML += '<td align="right">';
						if ((count == 0 && itemCount > 1) || (count > 0 && count != (itemCount-1))) {
							outHTML += '<a href="#" rel="movePick" id="'+this.id+'|2"><img src="<?php echo($config['fantasy_web_root']); ?>images/down.png" width="15" height="15" /></a>&nbsp;';
						}
						if (count > 0) {
							outHTML += '<a href="#" rel="movePick" id="'+this.id+'|1"><img src="<?php echo($config['fantasy_web_root']); ?>images/up.png" width="15" height="15" /></a>&nbsp;';
						}	
						outHTML += '<a href="#" rel="remove" id="'+this.id+'"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="15" height="15" /></a>&nbsp;';
						<?php if ($pick_team_id == $user_team_id && ($draftStatus >= 2 && $draftStatus < 4)) { ?>
						outHTML += '<a href="#" rel="draft" id="'+this.id+'"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/next.png" width="15" height="15" alt="Draft Player" title="Draft Player" /></a>';
						<?php } ?>
						outHTML += '</td></tr>';
						count++;
					}
				});
			} else {
			// PLAYER OBJECT ARRAY
				itemCount = listLength(data);
				for (var i= 0; i < itemCount; i++) {	
					if (data[i] && data[i] != null && data[i].id != null) {
						var player = data[i];
						outHTML += '<tr align="left" valign="top" class="s'+((count%2)+1)+'_l">';
						if (player.id != '' && player.player_name != '') {
							outHTML += '<td>'+(count+1)+'</td>';
							outHTML += '<td><a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/'+player.id+'">'+player.player_name+'</a>, '+player.position+'</td>';
							outHTML += '<td align="right">';
							if ((count == 0 && itemCount > 1) || (count > 0 && count != (itemCount-1))) {
								outHTML += '<a href="#" rel="movePick" id="'+player.id+'|2"><img src="<?php echo($config['fantasy_web_root']); ?>images/down.png" width="15" height="15" /></a>&nbsp;';
							}
							if (count > 0) {
								outHTML += '<a href="#" rel="movePick" id="'+player.id+'|1"><img src="<?php echo($config['fantasy_web_root']); ?>images/up.png" width="15" height="15" /></a>&nbsp;';
							}	
							outHTML += '<a href="#" rel="remove" id="'+player.id+'"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="15" height="15" /></a>&nbsp;';
							<?php if ($pick_team_id == $user_team_id && ($draftStatus >= 2 && $draftStatus < 4)) { ?>
							outHTML += '<a href="#" rel="draft" id="'+player.id+'"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/next.png" width="15" height="15" alt="Draft Player" title="Draft Player" /></a>';
							<?php } ?>
							outHTML += '</td></tr>';
							count++;
						}
					}
				}
			}
		}
		if (count == 0) {
			outHTML += '<tr align="left" valign="top">';
            outHTML += '<td colspan="3">No players have been added yet</td>';
            outHTML += '</tr>';
		}
		buttonDisplay('optSave',pageChanged);
		outHTML += '</table>';
		return outHTML;
	}
    </script>
     <div id="single-column">
    	<div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        <?php 
		/*------------------------------------------------------------------------
		/	ACT AS OWNER
		/	DRAWN ONLY IF THE OWNER LIST ARRAY IS PASSED WITH A LENGTH > 0
		/-----------------------------------------------------------------------*/
		if (isset($ownerList) && sizeof($ownerList) > 0) { ?>
        <div style="width:98%;text-align:right;float:left;">
		<?php if (isset($team_override) && $team_override === true) { ?>
        <div class="notice">
        Currently Viewing Page as: <?php print($ownerList[$user_team_id]." - ".$team_list[$user_team_id]['teamname']." ".$team_list[$user_team_id]['teamnick']); ?>
        </div><br />
        <?php 
        } // END if (isset($team_override)
        ?>
        <label for="owners" style="min-width:610px;">Act As Owner:</label> 
        <select id="owners" style="clear:none;">
        	<option value="X">Select owner</option>
            <?php
			foreach($ownerList as $id => $ownerName) {
				echo('<option value="'.$id.'"');
				if ($id == $user_team_id) { echo(' selected="selected"'); }
				echo('>'.$ownerName.'</option>');
			}
			?>
        </select>
       	</div>
        <br />&nbsp;<br />
        <?php } ?>
    </div>
    <div id="left-column-wide">
        <div class="textbox" style="width:245px;">
        <div id="activeStatusBox"><div id="activeStatus"></div></div>
            <table style="width:245px;" cellpadding="2" cellspacing="0" border="0">
            <?php $countDrawn = 0; ?>
            <tr class="title">
                <td colspan="3">My Draft List</td>
            </tr>
            <tr class="headline">
                <td width="5%">Pick</td>
                <td width="65%">Player</td>
                <td width="30%">Options</td>
            </tr>
            </table>
            <div id="activeList" class="listPickerBox" style="width:225px;">
            <table style="width:245px;" cellpadding="2" cellspacing="0" border="0">
            <tr align="left" valign="top">
                <td colspan="3">No player have been added yet</td>
            </tr>
            </table>
            </div>
    	
        <div style="margin:8px 0 0 0; padding:2px; width:245px; text-align:right;" id="listRow">
            <div id="optSave"><b>Save List:</b> <a href="#" rel="saveList"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/database_up.png" 
			align="absmiddle" width="15" height="15" border="0" /></a></div>
			<div id="optClear"><b>Clear List:</b> <a href="#" rel="clearAll"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" 
			align="absmiddle" width="15" height="15" border="0" /></a></div>
        </div>
        </div>
    </div>
    
    <div id="center-column-short">
        <?php 
		if (($pick_team_id == $user_team_id && ($draftStatus >= 2 && $draftStatus < 4)) || ($accessLevel == ACCESS_ADMINISTRATE || $isCommish)) { ?>
        <div class="textbox" style="width:381px;">
        <table cellspacing=0 cellpadding=3 width="375px">
        <tr class='title'><td colspan=3>My Draft Selection</td></tr>
        <tr class='s1'>
        <td>
        <form method='post' action='<?php echo($config['fantasy_web_root']); ?>draft/processDraft/'>
        <div id="playerSelected">
        <table cellspacing=0 cellpadding=3 width="375px">
        <tr align="left" valign="top">
        	<td width="35%">
            <img src='<?php echo($config['ootp_html_report_path']); ?>images/default_player_photo.jpg' border="0" align="left" />
            </td>
            <td width="65%">
            <b>No Player Selected.</b><br /><br />Click "draft" next to a player below to select to draft that player.
            <br />
        	</td>
        </tr>
        </table>
        </div>
        <input type='hidden' name='action' value='selection'></input>
        <input type='hidden' name='pick' id='pick'></input>
		<?php if (isset($team_override) && $team_override === true) {
        	$draft_team_id = $user_team_id;
		} else {
			$draft_team_id = $pick_team_id;
		}
		?>
        <input type='hidden' name='team_id' value="<?php echo($draft_team_id); ?>"></input>
        <input type='hidden' name='league_id' value="<?php echo($league_id); ?>"></input>
        <input type='hidden' name='pick_id' value="<?php echo($pick_id); ?>"></input>
        <div class="button_bar">
        <input type='submit' id="btnSubmit" value='Draft Player' style="display:none;"></input>
        </div>
        </form></td>
        </tr>
        </table>
		 </div>
		 <?php } else { ?>
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_question.gif" align="absmiddle" width="24" height="24" border="0" /><strong>Did you know?</strong><br />
        You can manage your draft settings including auto draft and draft list 
        picking from your <?php echo anchor('/team/submit/mode/edit/id/'.$user_team_id,'Team Admin page',array('style'=>'font-weight:bold;')); ?>?
		<?php } ?>
        &nbsp;
    	<br clear="all" />
       
        
    </div>
    
    <div id="right-column">
            <div class="textbox" style="width:265px;">
        	<table cellspacing='0' cellpadding='3' width="265px">
            <tr class='title'><td colspan='3'>My Drafted Players</td></tr>
            <tr class='headline' align='left'>
                <td class='hsc2_l' width="20%" align='left'>Rnd</td>
                <td class='hsc2_l' width="20%" align='left'>Pick</td>
                <td class='hsc2_l' width="60%" align='left'>Player</td>
            </tr>
           	<tr align="left" valign="top">
                <td colspan="3">
                <div id="activeResults" class="listPickerBox">
           		No player have been added yet	
           		</div>
                </td>
            </tr>
            </table>
            <br />
            <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_search.gif" align="absmiddle" width="15" height="15" border="0" /> <?php echo anchor('/draft/load/league_id/'.$league_id,'View complete draft results'); ?>
            <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/repeat.png" align="absmiddle" width="15" height="15" border="0" /> <?php echo anchor('#','Refresh List',array('rel'=>'reloadDraftList')); ?>
            </div>
			<br clear="all" />
    </div>
    
    <div id="single-column">
		<form method='post' name="filterform" id="filterform" action='<?php echo($config['fantasy_web_root']); ?>draft/selection/league_id/<?php echo($league_id); ?>' style="display:inline;">
		<div style="float:left; width:900px; margin-top:12px;border:1px solid black; ">
         <!--div class='tablebox' style="width:915px;"-->
		 <table cellspacing="0" cellpadding="2" border="0" width="900px" 
         style="padding:0px; margin:0px;" class="draft_settings">
		 <tr class='title'>
         	<td colspan='11' height='17'>Filters</td>
         </tr>
		  <tr>
		    <td class="formLabel">Player Type:</td>
		    <td>
		      <select name='player_type' id='player_type'>
				<?php $types = array(1=>"Batters",2=>"Pitchers");
                foreach ($types as $key => $val) {
                    echo("<option value='$key'");
                    if ($key==$player_type) { echo(" selected");}
                    echo(">$val</option>");
                } ?>
		      </select>
		     </td>
		<script type="text/javascript">
		function changePosOptions(val){
			posData = "";
			minData = "";
			posLabel = "";
			minLabel = "";
			if (val == 1) {
				posData = $('#formBatters').html();
				minData = $('#formMinBatting').html();
				posLabel = "Position:";
				minLabel = "AB:";
			} else {
				posData = $('#formPitchers').html();
				minData = $('#formMinPitchers').html();
				posLabel = "Role:";
				minLabel = "IP:";
			}
			$('td#posForm').html(posData);
			$('td#minField').html(minData);
			$('td#posLbl').html(posLabel);
			$('td#minLbl').html("Min "+minLabel);
		}
		$('#player_type').click(function() {
			changePosOptions($('#player_type').val());
		});
		$(document).ready(function(){	
			changePosOptions(<?php echo($player_type); ?>);	   
		});
		</script>
        <!-- ----------------------EDIT 1.0.5 --------------------- -->
        <!-- Begin Hidden data fields for borh pitchers and batters -->
        <div id="formBatters" class="noShow">
        <select name='position_type' id='position_type'>
			<?php
			$pos = array(-1,2,3,4,5,6,7,8,9,10,20);
			foreach ($pos as $pos_id) {
				echo("<option value='$pos_id'");
				if ($pos_id==$position_type) {echo(" selected");}
				echo(">".get_pos($pos_id)."</option>");
			}
			?>
			</select>
        </div>
        <div id="formMinBatting" class="noShow">
        <select name='min_plate' id='min_plate'>
			<?php
			$max = 0;
			while ($max < 650) {
				echo("<option value='$max'");
				if ($max==$min_plate) {echo(" selected");}
				echo(">".$max."</option>");
				$max += 50;
			}
			?>
			</select>
        </div>
        
        <div id="formPitchers" class="noShow">
			<select name='role_type' id='role_type'>
			<?php 
			$roles = array(-1,11,12,13);
			foreach ($roles as $role) {
				echo("<option value='$role'");
				if ($role==$role_type) { echo(" selected");}
				echo(">".get_pos($role)."</option>");
			}
			?>
		</select>
        </div>
        <div id="formMinPitchers" class="noShow">
			<select name='min_inning' id='min_inning'>
			<?php
			$max = 0;
			while ($max < 201) {
				echo("<option value='$max'");
				if ($max==$min_inning) {echo(" selected");}
				echo(">".$max."</option>");
				$max += 25;
			}
			?>
			</select>
        </div>
	
        <td class="formLabel" id="posLbl">Position:</td>
        <td id="posForm">&nbsp;</td>
        <td class="formLabel" id="minLbl">Min. AB</td>
        <td id="minField">&nbsp;</td>
			      

        	<td class="formLabel">Stats Range:</td>
		     <td>
		      <select name='stats_range' id='stats_range'>
				<?php $types = array(1=>"Last Year", 2=>"Two Years Ago", 3=>"Three Years Ago",4=>"3 Year Average");
                foreach ($types as $key => $val) {
                    echo("<option value='$key'");
                    if ($key==$stats_range) { echo(" selected");}
                    echo(">$val</option>");
                } ?>
		      </select>
		     </td>
			 <?php
			## Num to display
            echo '    <td class="formLabel">Records per page:</td>';
            echo "     <td>";
            echo "      <select name='limit' id='limit'>";
            echo '      <option value="-1">All</option>';
            for ($i = 25; $i < 201; $i += 25) {
                echo("<option value='$i'");
                if ($i == $limit) {echo " selected";}
                echo ">$i</option>";
            }
            echo "      </select>";
            echo "     </td>";
			echo '<input type="hidden" name="startIdx" id="startIdx" value="'.$startIdx.'" />';
            echo '<input type="hidden" name="pageId" id="pageId" value="'.$pageId.'" />';
			
			?>
		    <td align='right'>
		    <input type='submit' class='submitButton' value='Go' />
		    </td>
		   </tr>
		  
		 </table>
		  
		</div></form>
        <?php
		/*------------------------------------------------
		/
		/	BEGIN STATS TABLE
		/
		/-----------------------------------------------*/
		?>
		<div class="textbox" width="95%">
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
		if (isset($formatted_stats) && sizeof($formatted_stats)){
			echo($formatted_stats);						 
		}
		?>
		</div>
        <br clear="all" />
    </div>
    <p /><br />