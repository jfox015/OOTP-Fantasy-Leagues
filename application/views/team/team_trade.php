<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
<script type="text/javascript" charset="UTF-8">
	var ajaxWait = '<img src="<?php echo(PATH_IMAGES); ?>icons/ajax-loader.gif" width="28" height="28" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" align="absmiddle" />&nbsp;';
	var fader = null;
	var team_id = <?php echo($team_id); ?>;
	var team_id2 = <?php echo($team_id2); ?>;
	var league_id = <?php echo($league_id); ?>;
	var curr_type = "1";
	var max_add_drop = 3;
	var addPlayers = new Array(max_add_drop);
	var dropPlayers = new Array(max_add_drop);
	var playerCache = new Array(25);
	$(document).ready(function(){	
		$('#btnRefresh').click(function(){
			var proceed = true;
			if (countItems(1) > 0 && countItems(2) > 0) {
				proceed = confirm("Are you sure you want to load a new team? This will clear your current trade data and cannot be undone. Do you want to proceed?");
			}
			if (proceed) {// GATHER VALUE FOR SUBMISSION
				var teamId2 = $('select#teams').val();	
				var typeId = $('select#type').val();	
				var stats_range = $('select#stats_range').val();	
				var stats_source = $('select#stat_source').val();	
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/trade/id/'+team_id+'/team_id2/'+teamId2+'/stats_range/'+stats_range+'/stats_source/'+stats_source;
			}
		});
		$('div#activeStatusBox').hide();
		$('select#stats_range').change(function(){
			var displayType ='';
			if ($('select#stats_range').val() != 0) {
				displayType = "none";
			} else {
				displayType = "block";
			}
			$('select#stat_source').css('display',displayType);
			$('label#lblStatsSource').css('display',displayType);
		});
		$('a[rel=itemPick]').live('click',function () {
			var params = this.id.split("|");
			params[3] = "add";
			updatePlayerLists(params);
			return false;
		});	
		$('img[rel=itemRemove]').live('click',function () {
			var params = new Array(this.id,"","","remove");
			updatePlayerLists(params);
			return false;							
		});
		$('img[rel=addListRemove]').live('click',function () {
			if (offAddList(this.id)) {
				updatePageLists();
			}
			return false;
		});	
		$('img[rel=dropListRemove]').live('click',function () {
			if (offDropList(this.id)) {
				updatePageLists();
			}
			return false;
		});	
		$('button#btnClear').live('click',function () {
			if (confirm("Are you sure you want to clear your transaction?")) {
				clearTransaction();
			}
			return false;
		});	
		$('button#btnSubmit').live('click',function () {
			if (confirm("Process this transaction?")) {
				processTransaction();
			}
			return false;
		});
		$('input[rel=protestbtn]').live('click',function () {
			if (confirm("Are you sure you want to log a protest to this trade? This action cannot be undone.")) {
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/tradeResponse/team_id/'+team_id+'/trade_id/'+this.id+cacheBuster();
			}
			return false;	   
		});
		$('input[rel=responsebtn]').live('click',function () {
			var params = this.id.split("|");
			var  proceed = true;
			if (params[0] == <?php print(TRADE_REJECTED_OWNER); ?> || params[0] == <?php print(TRADE_REJECTED_COUNTER); ?>) {
				proceed = confirm("Do you wish to reject this trade WITHOUT adding a response to the proposing team's owner? Click 'OK' to submit the rejection, click 'Cancel' to provide a response,");
				if (!proceed) {
					if (params[0] == <?php print(TRADE_REJECTED_OWNER); ?>) {
						document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/tradeReview/league_id/'+league_id+'/team_id/'+team_id+'/trans_type/2/trade_id/'+params[1];
					} else {
						document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/tradeResponse/league_id/'+league_id+'/team_id/'+team_id+'/type/'+params[0]+'/trade_id/'+params[1];
					}
				}
			} else if (params[0] == <?php print(TRADE_PROTEST); ?>) {
				proceed = confirm("Are you sure you want to log a protest to this trade? This action cannot be undone.");
            }
			if (proceed) {
				var url = "<?php echo($config['fantasy_web_root']); ?>team/tradeResponse/id/"+team_id+"/type/"+params[0]+"/trade_id/"+params[1]+cacheBuster();
				$('div#tradeStatus').removeClass('error');
				$('div#tradeStatus').removeClass('success');
				$('div#tradeStatus').html(ajaxWait);
				$('div#tradeStatusBox').fadeIn("fast");
				$.getJSON(url, function(data){
					$('div#tradeStatus').empty();
					if (data.code.indexOf("200") != -1) {
						if (data.status.indexOf(":") != -1) {
							var status = data.status.split(":");
							$('div#tradeStatus').addClass(status[0].toLowerCase());
							$('div#tradeStatus').html(status[1]);
						} else {
							$('div#tradeStatus').addClass('success');
							$('div#tradeStatus').html("Your response has been completed.");
							setTimeout('refreshPage()',3000);
						}
					} else {
						$('div#tradeStatus').addClass('error');
						$('div#tradeStatus').append('No information was returned for the selected transaction.');
					}
				});
			}
			return false;
		});
		$('input[rel=reviewbtn]').live('click',function () {
			var params = this.id.split("|");
			document.location.href = "<?php echo($config['fantasy_web_root']); ?>team/tradeReview/team_id/"+team_id+"/league_id/"+league_id+"/trans_type/"+params[1]+"/trade_id/"+params[2]+cacheBuster();
		});
		$('button#btnReview').live('click',function () {
			// PREPARE PLAYER ID LISTS
			var teamId2 = $('select#teams').val();	
			var addList = '';
			for (var i = 0; i < addPlayers.length; i++) {
				if (addPlayers[i] != null && addPlayers[i].id != -1) {
					if (addList != '') { addList += "&"; }
					addList += addPlayers[i].id+"_"+addPlayers[i].position+"_"+addPlayers[i].role;
				}
			}
			if (addList == '') { addList = "-1_NA_NA"; }
			var dropList = '';
			for (var i = 0; i < dropPlayers.length; i++) {
				if (dropPlayers[i] != null && dropPlayers[i].id != -1) {
					if (dropList != '') { dropList += "&"; }
					dropList += dropPlayers[i].id+"_"+dropPlayers[i].position+"_"+dropPlayers[i].role;
				}
			}
			if (dropList == '') { dropList = "-1_NA_NA"; }
			// PREPARE URL
			document.location.href = "<?php echo($config['fantasy_web_root']); ?>team/tradeReview/league_id/"+league_id+"/team_id/"+team_id+"/trans_type/1/team_id1/"+team_id+"/tradeFrom/"+addList+"/team_id2/"+teamId2+"/tradeTo/"+dropList+cacheBuster();
			return false;
		});
		$('a[rel=listLoad]').live('click',function () { 
			loadList(this);
			highlightAlpha(this);
			return false;
		});
		var tradeFrom = false;
		var tradeTo = false;
		<?php
		if(isset($sendList) && sizeof($sendList) > 0) { ?>
			var sendPlayers = new Array(<?php sizeof($sendList); ?>);
			<?php 
			$count = 0;
			foreach($sendList as $playerData) { ?>
				sendPlayers[<?php print($count); ?>] = new Player();
				sendPlayers[<?php print($count); ?>].id = <?php print($playerData['id']); ?>;
				sendPlayers[<?php print($count); ?>].player_name = '<?php print($playerData['first_name']." ".$playerData['last_name']); ?>';
				sendPlayers[<?php print($count); ?>].position = '<?php print(get_pos($playerData['position'])); ?>';
				sendPlayers[<?php print($count); ?>].role = '<?php print(get_pos($playerData['role'])); ?>';
			<?php 
				$count++;
			}
			?>
			for (var i = 0; i < sendPlayers.length; i++) {
				toDropList(sendPlayers[i]);
			}
			tradeTo = true;
			<?php
		}
		if(isset($receiveList) && sizeof($receiveList) > 0) { ?>
			var receivePlayers = new Array(<?php sizeof($receiveList); ?>);
			<?php 
			$count = 0;
			foreach($receiveList as $playerData) { ?>
				receivePlayers[<?php print($count); ?>] = new Player();
				receivePlayers[<?php print($count); ?>].id = <?php print($playerData['id']); ?>;
				receivePlayers[<?php print($count); ?>].player_name = '<?php print($playerData['first_name']." ".$playerData['last_name']); ?>';
				receivePlayers[<?php print($count); ?>].position = '<?php print(get_pos($playerData['position'])); ?>';
				receivePlayers[<?php print($count); ?>].role = '<?php print(get_pos($playerData['role'])); ?>';
			<?php 
				$count++;
			}
			?>
			for (var i = 0; i < receivePlayers.length; i++) {
				toAddList(receivePlayers[i]);
			}
			tradeFrom = true;
			<?php
		}
		?>
		if (tradeTo || tradeFrom) {
			updatePageLists();
		}
	});
	function countItems(listId) {
		var itemList = null;
		switch(listId) {
			case 1:
				itemList = addPlayers;
				break;
			case 2:
				itemList = dropPlayers;	
				break;
		}
		var count = 0;
		for (var i = 0; i < itemList.length; i++) {
			if (itemList[i] != null && (itemList[i].id != '' && itemList[i].id != -1)) {
				count++;
			}
		} // END for
		return count;
	}
	function Player() {
		this.id = -1;
		this.player_name = '';
		this.position = '';
		this.role = '';
	}
	function updatePlayerLists(params) {
		var found = false;
		var errorListName = "";
		if (addPlayers.length > 0) {
			for (var i = 0; i < addPlayers.length; i++) {
				if (addPlayers[i] != null && params[0] == addPlayers[i].id) {
					found = true;
					errorListName = "recieve";
					break;
				}
			}
		}
		if (dropPlayers.length > 0) {
			if (!found) {
				for (var i = 0; i < dropPlayers.length; i++) {
					if (dropPlayers[i] != null && params[0] == dropPlayers[i].id) {
						found = true;
						errorListName = "send";
						break;
					}
				}
			}
		}
		if (!found) {
			if (params[3] == "add" && addListLength() >= max_add_drop) {
				$('div#listStatus').addClass('error');
				$('div#listStatus').html("You can only recieve a maximum of "+max_add_drop+" players at a time.");
			} else if (params[3] == "remove" && dropListLength() >= max_add_drop) {
				$('div#listStatus').addClass('error');
				$('div#listStatus').html("You can only send a maximum of "+max_add_drop+" players at a time.");
			} else {
				// SEE if player info is cached (added and removed form a list already)
				var player = new Player();
				for (var i = 0; i < playerCache.length; i++) {
					if (playerCache[i] != null && playerCache[i].id != -1) {
						if (params[0] ==  playerCache[i].id) {
							player = playerCache[i];
							break;
						}
					}
				}
				if (player.id == -1) {
					var url = "<?php echo($config['fantasy_web_root']); ?>players/getInfo/player_id/"+params[0]+cacheBuster();
					$('div#listStatus').removeClass('error');
					$('div#listStatus').removeClass('success');
					$('div#listStatus').html(ajaxWait);
					$('div#listStatusBox').fadeIn("fast");
					$.getJSON(url, function(data){
						if (data.code.indexOf("200") != -1) {
							if (data.status.indexOf(":") != -1) {
								var status = data.status.split(":");
								$('div#listStatus').addClass(status[0].toLowerCase());
								$('div#listStatus').html(status[1]);
							} else {
								var item = data.result.items[0];
								player.id = item.id;
								player.player_name = item.player_name;
								player.position = item.position;
								player.role = item.role;
								$('div#listStatus').html("");
								$('div#listStatusBox').fadeOut("fast");
								cachePlayer(player);
								if (params[3] == "add") {
									toAddList(player);
								} else {
									toDropList(player);
								}
								updatePageLists();
							}
						} else {
							$('div#listStatus').addClass('error');
							$('div#listStatus').append('No information was returned for the selected player.');
						}
					});
				} else {
					if (params[3] == "add") {
						toAddList(player);
					} else {
						toDropList(player);
					}
					updatePageLists();
				}
			}
		} else {
			$('div#listStatus').addClass('error');
			$('div#listStatus').html("This player already appears on your "+errorListName+" list.");
		}
	}
	function cacheBuster() {
		var date = new Date();
		var hash = $.md5(Math.floor(Math.random())+date.toUTCString()).toString();
		return "/uid/"+hash.substr(0,16);
	}
	function cachePlayer(player) {
		var added = false;
		for (var i = 0; i < playerCache.length; i++) {
			if (playerCache[i] == null) {
				playerCache[i] = player;
				added = true;
				break;
			}
		}
		return added;
	}
	function toAddList(player) {
		var added = false;
		for (var i = 0; i < addPlayers.length; i++) {
			if (addPlayers[i] == null ||(addPlayers[i] != null && addPlayers[i].id == -1)) {
				addPlayers[i] = player;
				added = true;
				break;
			}
		}
		return added;
	}
	function offAddList(id) {
		var removed = false;
		for (var i = 0; i < addPlayers.length; i++) {
			if (addPlayers[i] != null && addPlayers[i].id != -1) {
				if (addPlayers[i].id  == id){
					addPlayers[i] = null;
					removed = true;
					break;
				}
			}
		}
		return removed;
	}
	function clearAddList() {
		for (var i = 0; i < addPlayers.length; i++) {
			addPlayers[i] = null;
		}
		return true;
	}
	function addListLength() {
		count = 0;
		for (var i = 0; i < addPlayers.length; i++) {
			if (addPlayers[i] != null && addPlayers[i].id != -1) {
				count++;
			}
		}
		return count;
	}
	function toDropList(player) {
		var added = false;
		for (var i = 0; i < dropPlayers.length; i++) {
			if (dropPlayers[i] == null ||(dropPlayers[i] != null && dropPlayers[i].id == -1)) {
				dropPlayers[i] = player;
				added = true;
				break;
			}
		}
		return added;
	}
	function offDropList(id) {
		var removed = false;
		for (var i = 0; i < dropPlayers.length; i++) {
			if (dropPlayers[i] != null && dropPlayers[i].id != -1) {
				if (dropPlayers[i].id == id){
					dropPlayers[i] = null;
					removed = true;
					break;
				}
			}
		}
		return removed;
	}
	function clearDropList() {
		for (var i = 0; i < dropPlayers.length; i++) {
			dropPlayers[i] = null;
		}
		return true;
	}
	function dropListLength() {
		count = 0;
		for (var i = 0; i < dropPlayers.length; i++) {
			if (dropPlayers[i] != null && dropPlayers[i].id != -1) {
				count++;
			}
		}
		return count;
	}
	function clearTransaction() {
		clearAddList();
		clearDropList();
		updatePageLists();
		$('div#listStatus').removeClass('error');
		$('div#listStatus').removeClass('success');
		$('div#listStatus').empty();		
	}
	function updatePageLists() {
		var totalCount = 0;
		var addCount = 0;
		var dropCount = 0;
		var rowCount = 0;
		var addHTML = '<table>';
		var rowClass = '';
		for (var i = 0; i < addPlayers.length; i++) {
			if (addPlayers[i] != null && (addPlayers[i].id != '' && addPlayers[i].id != -1)) {
				rowClass = ((rowCount % 2) == 0) ? 'sl_1' : 'sl_2';
				addHTML += '<tr align=left class="'+rowClass+'">';
				addHTML += '<td><img alt="Remove" title="Remove" rel="addListRemove" id="'+addPlayers[i]['id']+'" src="<?php echo(PATH_IMAGES); ?>icons/icon_fail.png" width="16" height="16" align="absmiddle" />';
				addHTML += ' &nbsp;<a target="_blank" href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/'+league_id+'/player_id/'+addPlayers[i]['id']+'" title="Click to view bio" alt="Click to view bio">'+addPlayers[i]['player_name']+'</a>';
				if (addPlayers[i]['position'] == "P") {
					addHTML += ' '+addPlayers[i].role;
				} else {
					addHTML += ' '+addPlayers[i].position;
				}
				addHTML += '</td>';
				addHTML += '</tr>';
				rowCount++;
				addCount++;
			}
		} // END for
		if (rowCount == 0) {
			addHTML+='<tr><td>No players added yet</td></tr>'; 
		}
		totalCount += rowCount;
		addHTML+='</table>';
		$('div#playersToAdd').empty();
		$('div#playersToAdd').append(addHTML);
		
		var rowCount = 0;
		var dropHTML = '<table>';
		for (var i = 0; i < dropPlayers.length; i++) {
			if (dropPlayers[i] != null && (dropPlayers[i].id != '' && dropPlayers[i].id != -1)) {
				rowClass = ((rowCount % 2) == 0) ? 'sl_1' : 'sl_2';
				dropHTML += '<tr align=left class="'+rowClass+'">';
				dropHTML += '<td><img alt="Remove" title="Remove" rel="dropListRemove" id="'+dropPlayers[i]['id']+'" src="<?php echo(PATH_IMAGES); ?>/icons/icon_fail.png" width="16" height="16" align="absmiddle" />';
				dropHTML += ' &nbsp;<a target="_blank" href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/'+league_id+'/player_id/'+dropPlayers[i]['id']+'" title="Click to view bio" alt="Click to view bio">'+dropPlayers[i]['player_name']+'</a>';
				if (dropPlayers[i]['position'] == "P") {
					dropHTML += ' '+dropPlayers[i].role;
				} else {
					dropHTML += ' '+dropPlayers[i].position;
				}
				dropHTML += '</td>';
				dropHTML += '</tr>';
				rowCount++;
				dropCount++;
			}
		} // END for
		if (rowCount == 0) {
			dropHTML+='<tr><td>No players added yet</td></tr>'; 
		}
		totalCount += rowCount;
		dropHTML+='</table>';
		$('div#playersToDrop').empty();
		$('div#playersToDrop').append(dropHTML);
		if (addCount > 0 && dropCount > 0) {
			btnDisplay = 'block';
		} else {
			btnDisplay = 'none';
		}
		$('button#btnSubmit').css('display',btnDisplay);
		$('button#btnReview').css('display',btnDisplay);
		$('button#btnClear').css('display',btnDisplay);
	}
	function processTransaction() {
		// PREPARE PLAYER ID LISTS
		var addList = '';
		for (var i = 0; i < addPlayers.length; i++) {
			if (addPlayers[i] != null && addPlayers[i].id != -1) {
				if (addList != '') { addList += "&"; }
				addList += addPlayers[i].id+"_"+addPlayers[i].position+"_"+addPlayers[i].role;
			}
		}
		if (addList == '') { addList = "-1_NA_NA"; }
		var dropList = '';
		for (var i = 0; i < dropPlayers.length; i++) {
			if (dropPlayers[i] != null && dropPlayers[i].id != -1) {
				if (dropList != '') { dropList += "&"; }
				dropList += dropPlayers[i].id+"_"+dropPlayers[i].position+"_"+dropPlayers[i].role;
			}
		}
		if (dropList == '') { dropList = "-1_NA_NA"; }
		var teamId2 = $('select#teams').val();	
		// PREPARE URL
		var url = "<?php echo($config['fantasy_web_root']); ?>team/tradeOffer/league_id/"+league_id+"/team_id/"+team_id+"/tradeFrom/"+addList+"/tradeTo/"+dropList+"/team_id2/"+teamId2+cacheBuster();
		$('div#listStatus').empty();
		$('div#listStatus').html(ajaxWait);
		$('div#listStatusBox').show();
		
		$.getJSON(url, function(data){
			if (data.code.indexOf("200") != -1) {
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#activeStatus').addClass(status[0].toLowerCase());
					$('div#activeStatus').html(status[1]);
				} else {
					$('div#activeStatus').addClass('success');
					$('div#activeStatus').html('Transaction Completed Successfully');
					clearTransaction();
					refreshPendingTrades();
				}
				$('div#activeStatusBox').fadeIn("slow",function() { setTimeout('fadeStatus("active")',5000); });
			} else {
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#listStatus').addClass(status[0].toLowerCase());
					$('div#listStatus').html(status[1]);
				}
			}
		});
	}
	function refreshPendingTrades() {
		//TODO: CODE THIS
		
	}
	function addPlayers(params) {										
		var url = "<?php echo($config['fantasy_web_root']); ?>team/addAndDisplay/league_id/"+league_id+"/team_id/"+team_id+"/player_id/"+params[0]+"/position/"+params[1]+"/role/"+params[2]+cacheBuster();
		$('div#activeList').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#activeList').empty();
			if (data.code.indexOf("200") != -1) {
				$('div#activeList').append(drawResults(data,'itemRemove','Remove'));
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#activeStatus').addClass(status[0].toLowerCase());
					$('div#activeStatus').html(status[1]);
				} else {
					$('div#activeStatus').addClass('success');
					$('div#activeStatus').html('Player Added Successfully');
					var obj = new Object();
					obj.id = curr_type+"|"+curr_param;
					loadList(obj);
				}
				$('div#activeStatusBox').fadeIn("slow",function() { setTimeout('fadeStatus("active")',5000); });
			} else {
				$('div#activeList').append('<div id="listColumn1" class="listcolumn"><ul> <li>No items were returned.</li> </ul> </div>');
			}
		});						
	}
	function removePlayers(params) {	
		var url = "<?php echo($config['fantasy_web_root']); ?>team/removeAndDisplay/league_id/"+league_id+"/team_id/"+team_id+"/player_id/"+params+cacheBuster();
		$('div#activeList').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#activeList').empty();
			if (data.code.indexOf("200") != -1) {
				$('div#activeList').append(drawResults(data,'itemRemove','Remove'));
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#activeStatus').addClass(status[0].toLowerCase());
					$('div#activeStatus').html(status);	
				} else {
					$('div#activeStatus').addClass('success');
					$('div#activeStatus').html('Player Removed Successfully');
					var obj = new Object();
					obj.id = curr_type+"|"+curr_param;
					loadList(obj);
				}
				$('div#activeStatusBox').fadeIn("slow",function() { fader = setTimeout('fadeStatus("active")',5000); });
			} else {
				$('div#activeList').append('<div id="listColumn1" class="listcolumn"><ul> <li>No items were returned.</li> </ul> </div>');
			}
		});
	}
	function loadList(obj) {
		var params = null;
		if (!obj) {
			params = new Array('pos',2);
		} else if (obj.id.indexOf("|") != -1) {
			params = obj.id.split("|");
		} else {
			params = new Array(obj.id);
		}
		var url = "<?php echo($config['fantasy_web_root']); ?>team/pullList/league_id/"+league_id+cacheBuster();
		if (params[0]) { url += "/type/"+params[0]; curr_type = params[0]; }
		if (params[1]) { url += "/param/"+params[1]; curr_param = params[1]; }
		if (params[0] != null && params[0] != "pos") {
			listType = 1;
		}
		url += "/list_type/"+listType;
		$('div#pickList').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#pickList').empty();
			if (data.code.indexOf("200") != -1 || data.code.indexOf("300") != -1) {
				$('div#pickList').append(drawResults(data,'itemPick',"Click to Add"));
				$('select#list_type').val(listType);
				sorttable.makeSortable(document.getElementById("stats_table"));
			} else {
				$('div#pickList').append('<div id="listColumn1" class="listcolumn"><ul> <li>No players were returned.</li> </ul> </div>');
			}
		});
	}
	function fadeStatus(type) {
		$('div#'+type+'StatusBox').fadeOut("normal",function() { clearTimeout(fader); $('div#'+type+'StatusBox').hide(); });
	}
	function refreshPage() { 
		document.location.href = '<?php echo($_SERVER['PHP_SELF']); ?>';
	}
	function drawResults(data,rel,alt) {
		var itemCount = data.result.items.length;
		var colLimit = (Math.round(itemCount / 3)) + 1; 
		var columnsDrawn = 1;
		var countDrawn = 0;
		var countPerColumn = 0;
		var outHTML = '';
		var type = "stats";
		var colnames = "";
		var rownum = 0;
		if (data.status.indexOf(":")!= -1) {
			var status = data.status.split(":");
			type = status[0];
			colnames = status[1];
			if (type == "notice") { type = "list"; }
		}
		if (type == "stats") {
			outHTML += '<table cellpadding="4" cellspacing="1" class="sortable-table" id="stats_table" style="width:100%;">';
			outHTML += '<thead>';
			outHTML += '<tr class="headline">';
			var cols = colnames.split("|");
			for (var i = 0; i < cols.length; i++) { 
				switch (cols[i]) {
					case 'Player':
					case 'POS':
						outHTML += '<td height="17" class="hsn2" style="text-align:left;">'+cols[i]+'</td>';
						break;
					case 'Team':
					case 'Draft':
						break;
					default:
						outHTML += '<td class="hsn2" style="text-align:center;">'+cols[i]+'</td>';
						break;
				}
			}
			outHTML += '</tr>';
			outHTML += '</thead>';
			outHTML += '<tbody>';
		}
		$.each(data.result.items, function(i,item){
			var bg = ((rownum % 2) == 0) ? '#E0E0E0' : '#fff';
			outHTML += '<tr bgcolor="'+bg+'" align="center" style="background:'+bg+'">';
			outHTML += '<td><a alt="'+alt+'" title="'+alt+'" rel="'+rel+'" id="'+item.id+'|'+item.position+'|'+item.role+'" href="#"><img src="<?php echo(PATH_IMAGES); ?>/icons/add.png" width="16" height="16" alt="Add" title="Add" /></a></td>';
			outHTML += '<td align="left"><a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/'+league_id+'/player_id/'+item.id+'" target="_blank">'+item.player_name+'</a>';
			if (item.positions != null && item.positions != '') { outHTML += '&nbsp;<span style="font-size:smaller">'+item.positions+'</span> '; } 
			if (item.injStatus != null && item.injStatus != '') { outHTML += '&nbsp;<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/red_cross.gif" width="7" height="7" align="absmiddle" alt="'+item.injStatus+'" title="'+item.injStatus+'" /> ';}
			if (item.on_waivers != null && item.on_waivers == 1) { outHTML += '&nbsp;<b style="color:#ff6600;">W</b>&nbsp; '; }
			outHTML += '</td>';
			
			outHTML += '<td>'+item.pos+'</td>';
			if (item.pos != "SP" && item.pos != "MR" && item.pos != "CL") {
				outHTML += '<td>'+item.avg+'</td>';
				outHTML += '<td>'+item.hr+'</td>';
				outHTML += '<td>'+item.rbi+'</td>';
				outHTML += '<td>'+item.bb+'</td>';
				outHTML += '<td>'+item.k+'</td>';
				outHTML += '<td>'+item.sb+'</td>';
				outHTML += '<td>'+item.ops+'</td>';
			} else {
				outHTML += '<td>'+item.w+'</td>';
				outHTML += '<td>'+item.l+'</td>';
				outHTML += '<td>'+item.era+'</td>';
				outHTML += '<td>'+item.ip+'</td>';
				outHTML += '<td>'+item.pbb+'</td>';
				outHTML += '<td>'+item.pk+'</td>';
				outHTML += '<td>'+item.s+'</td>';
				outHTML += '<td>'+item.whip+'</td>';
			}
			if (item.fpts != null) {
				outHTML += '<td>'+item.fpts+'</td>';
			} else if (item.rating != null) {
				outHTML += '<td>'+item.rating+'</td>';
			}
			outHTML += '</tr>';
			rownum++;
		});
		if (type == "stats") {
			outHTML += '</tbody>';
			outHTML += '</table>';
		}
		if (rownum == 0) {
			outHTML += '<table cellpadding="4" cellspacing="1" class="sortable-table" id="stats_table" style="width:100%;">';
			outHTML += '<tr>';
			outHTML += '<td colspan="8">No Players were found</td>';
			outHTML += '</tr>';
			outHTML += '</table>';
		}
		return outHTML;
	}
    </script>
<div id="single-column" class="trades">
        <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        
        <h2><?php echo($team_name); ?></h2>
            <p /><br />
            <b>Players on roster:</b>
            <br />
    </div>
    
	<div id="center-column">
        <div class="listPicker">
            
            <div id="activeStatusBox"><div id="activeStatus"></div></div>
            <div id="activeList" class="listPickerBox">
            	<?php
				$itemCount = sizeof($players);
				$colLimit = (round($itemCount / 3)) + 1;
				$columnsDrawn = 1;
				$countDrawn = 0;
				$countPerColumn = 0;
				foreach ($players as $player) {
				if ($countPerColumn == 0) {
				?>
            	<div id="listColumn<?php echo($columnsDrawn); ?>" class="listcolumn">
                	<ul>
                <?php } ?>
                    	<li><img alt="Send in Trade" title="Send in Trade" rel="itemRemove" id="<?php echo($player['id']); ?>" 
                        src="<?php echo(PATH_IMAGES); ?>icons/arrow_right.png" width="16" 
                        height="16" align="absmiddle" /> 
                        <?php if ($player['player_position'] == 1) {
							$pos = $player['player_role'];
						} else {
							$pos = $player['player_position'];
						}
						echo(get_pos($pos)); 
						?>
                        <a target="_blank" href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/<?php echo($player['id']); ?>"><?php echo($player['player_name']); ?></a></li>
                <?php 	$countDrawn++;
					$countPerColumn++;
					if ($countPerColumn == $colLimit || $countDrawn == $itemCount) { ?>
                    </ul>
                </div>
                <?php 	
					$countPerColumn = 0;
					$columnsDrawn++;
					} 
				}
				if ($countDrawn == 0) { ?>
                <div id="listColumn1" class="emptyList">
                	<ul>
                    	<li>No player were found for this team</li>
                    </ul>
                </div>
                <?php 
				}
			?>
            </div>
            <p />&nbsp;<br clear="all" /><br />
            <b>Add a Player</b>
            <br />
            <div id="pickStatusBox"><div id="pickStatus"></div></div>
            
            <div>
            	<div id="optionsBar">
                	<div id="trade_options">
                    <?php if (isset($fantasy_teams) && sizeof($fantasy_teams) > 0 ) {?>
                    <label for="teams">Team:</label> 
                    <select id="teams">
                        <?php  
                        foreach($fantasy_teams as $id => $teamName) {
                           	if ($id != $team_id) {
								echo('<option value="'.$id.'"');
								if ($id == $team_id2) { echo(' selected="selected"'); }
								echo('>'.$teamName.'</option>');
							}
                        }
                        ?>
                    </select>
                    
                    <label for="stats_range">Range:</label>
                      <select id='stats_range'>
                        <?php $types = array(-1=>"This Year", 1=>"Last Year",2=>"Two Years Ago", 4=>"3 Year Average");
                        foreach ($types as $key => $val) {
                            echo("<option value='$key'");
                            if ($key == $stats_range) { echo(" selected");}
                            echo(">$val</option>");
                        } ?>
                      </select>
                    <label for="stat_source" id="lblStatsSource">Source:</label>
                    <select id="stat_source">
						<option value="sp_all"<?php 
						if ($stat_source=="sp_all") { echo " selected"; } ?>>All Periods</option>
						<?php
						if (isset($scoring_periods) && sizeof($scoring_periods) > 0) {
							foreach ($scoring_periods as $scoring_periods) {
								echo('<option value="sp_'.$scoring_periods['id'].'"');
								if ($stat_source=="sp_".$scoring_periods['id']) { echo " selected"; }
								echo ('>Period '.$scoring_periods['id'].'</option>');
							}
						}
						?>
                    </select>
                    <input type='submit' id="btnRefresh" class='submitButton' value='Go' />
					<?php } ?>
                    </div>
                    <br clear="all" class="clearfix clear" />
                </div>
            </div>
            <div id="pickList" class="listPickerBox">
                <?php
				if (isset($formatted_stats) && sizeof($formatted_stats)){
					print($formatted_stats['batters']);	
					print($formatted_stats['pitchers']);	
				}
				?>
             </div>
		</div>
	</div>
    
            
            <div id="right-column">
            	<div id="listStatusBox"><div id="listStatus"></div></div>
            	<div class='textbox'>
                <table cellpadding="0" cellspacing="0" width="265px">
                <tr class='title'>
                    <td style='padding:3px'>Trade Summary</td>
                </tr>
                <tr class='headline'>
                    <td style='padding:6px'>Players to Recieve</td>
                </tr>
                <tr>
                    <td style='padding:3px'>
                    <table cellpadding="2" cellspacing="1" style="width:100%;">
                    <tr>
                    	<td>
                        <div id="playersToAdd">
                        <table>
                        <tr align=left class="s1_2">
                            <td width="40%">No players added yet</td>
                        </tr>
                        </table>
                        </div>
                        </td>
                    </tr>
                    </table>
                    </td>
                </tr>
                <tr class='headline'>
                    <td style='padding:6px'>Players to Send</td>
                </tr>
                <tr>
                    <td style='padding:3px'>
                    <table cellpadding="2" cellspacing="1" style="width:100%;">
                    <tr>
                    	<td>
                        <div id="playersToDrop">
                        <table>
                        <tr align=left class="s1_2">
                            <td width="40%">No players added yet</td>
                        </tr>
                        </table>
                        </div>
                        </td>
                    </tr>
                    </table>
                    </td>
                </tr>
                <?php if ($config['tradesExpire'] == 1) { 
					$expireStr = "";
					if ($config['defaultExpiration'] == -1) {
						$expireStr .= "This trade has <b>no expiration</b> date.";
					} else {
						$expireStr = "Trade expiration: ";
						if ($config['defaultExpiration']  == 500) {
							$expireStr .= "<b>Next Sim Period</b>";
						} else {
							$expireStr .= "<b>".$config['defaultExpiration']." days</b>";
						}
					}
				?>
				<tr class='headline'>
                    <td style='padding:6px'>Expiration</td>
                </tr>
				<tr>
                    <td style='padding:6px'><?php print($expireStr.'<br /><br /><span class="small">To change expiration options, click <b>Review</b></span>'); ?></td>
                </tr>
				<?php 
				} // END if config['tradesExpire']
				?>
				<tr>
                    <td style='padding:6px'>
                    <div class="button_bar" style="text-align:right;">
						<button id="btnClear" class="sitebtn adddrop">Clear</button>
						<button id="btnReview" class="sitebtn adddrop">Review</button>
						<button id="btnSubmit" class="sitebtn adddrop">Make Offer</button>
                    </div></td>
                </tr>
                </table>
                </div>
                
                <?php 
				/*-------------------------------------------------------
				/	PENDING TRADES
				/-----------------------------------------------------*/
				if (isset($teamTrades) && sizeof($teamTrades) > 0) { ?>
                <div id="tradeStatusBox"><div id="tradeStatus"></div></div>
            	<?php
                    $types = array('incoming','offered','approvals','protests','completed','other');
                    foreach($types as $type) {
                       if (isset($teamTrades[$type]) && sizeof($teamTrades[$type]) > 0) {
                           $tradeList = $teamTrades[$type];
                    ?>
                
                <br class="clear" clear="all" /><br />
                        <?php
                        if ( ! function_exists('humanize')) {
                            $this->load->helper('inflector');
                        }
                        print('<b style="color:#700;">'.humanize($type).'</b>');
                        ?>
                <br class="clear" clear="all" />
        	<div class='textbox right-column tradebox'>
                <table cellpadding="3" cellspacing="1" width="265px">
                <tr class='title'>
                    <td style='padding:3px' colspan="3"><?php print(humanize($type));?> Trade Details</td>
                </tr>
                <?php
                //echo("Team id = ".$team_id."<br />");
                
				foreach ($tradeList as $tradeData) { 
					$rowNum = 0;
					//echo("Team_2_id = ".$tradeData['team_2_id']."<br />");
				?>
                <?php 
				$transType = 3;
				if ($tradeData['team_2_id'] == $team_id) { 
					$transType = 2;
				} else {
					if ($team_id != $tradeData['team_1_id'] && $team_id != $tradeData['team_2_id']) {
						$transType = 4;
					}
				} 
				?>
                <form action="" method="post" id="trade_form_<?php print($tradeData['trade_id']); ?>">
                <tr class='headline'>
				 	<td width="38%:"><b>From:</b></td>
                	<td width="60%:"><a target="_blank" href="<?php echo($config['fantasy_web_root']); ?>team/info/<?php print($tradeData['team_1_id']); ?>"><?php print($tradeData['team_1_name']); ?></a></td>
				</tr>
				
               <tr class="sl_1">
                	<td><b>Offer Date:</b></td>
					<td><?php print(date('m/d/Y',strtotime($tradeData['offer_date']))); ?></td>
				</tr>
                <tr class="sl_2">
                	<td><b>Offered To:</b></td>
                	<td><a  target="_blank" href="<?php echo($config['fantasy_web_root']); ?>team/info/<?php print($tradeData['team_2_id']); ?>"><?php print($tradeData['team_2_name']); ?></a></td>
				</tr>
                <tr class="sl_1">
					<td><img src="<?php echo(PATH_IMAGES); ?>icons/arrow_right.png" width="16" 
                        height="16" align="absmiddle" /> <b>Send:</b></td>
					<td>
					<?php  
						foreach($tradeData['send_players'] as $playerStr) {
							print($playerStr."<br />"); 
						}
					?>
					</td>
                </tr>
                <tr class="sl_2">
					<td><img src="<?php echo(PATH_IMAGES); ?>icons/arrow_left.png" width="16" 
                        height="16" align="absmiddle" /> <b>Recieve:</b></td>
					<td><?php  
						foreach($tradeData['receive_players'] as $playerStr) {
							print($playerStr."<br />"); 
						} 
					?>
					</td>
                </tr>
                <tr class="sl_1">
                	<td><b>Effective:</b></td>
                	<td>Period <?php print($tradeData['in_period']); ?></td>
				</tr>
                <tr class="sl_2">
                	<td><b>Status:</b></td>
					<?php
					switch ($tradeData['tradeStatus']) {
						case 'Offered':
						case 'Accepted':
						case 'Completed':
							$class = 'positive';
							break;
						case 'Rejected by Owner':
						case 'Rejected by League':
						case 'Rejected by Commissioner':
						case 'Rejected by Admin':
						case 'Rejected with Counter':
						case 'Invalid Trade':
							$class = 'negative';
							break;
						case 'Removed':
						case 'Retracted':
							$class = 'warning';
							break;
						case 'Pending League Approval':
						case 'Pending Commissioner Approval':
							$class = 'alert';
							break;
						default:
							$class = 'message';
					}
					?>
					<td class="<?php echo($class); ?>"> <?php print($tradeData['tradeStatus']); ?></td>
				</tr>
				<?php
				// EXPIRATION DATE
				if ($config['tradesExpire'] == 1 && !empty($tradeData['expiration_days'])) { ?>
                <tr class="<?php echo((($rowNum % 2) == 0) ? 'sl_1' : 'sl_2'); ?>">
					<td><b>Expires:</b></td>
					<td>
					<?php
                        $expireStr = "";
                        switch(intval($tradeData['expiration_days'])) {
                            case -1:
                                $expireStr = "No expiration";
                                break;
                            case 500:
                                $expireStr = "Next Sim";
                                break;
                            default:
                                $expireStr = date('m/d/Y h:m A', (strtotime($tradeData['offer_date']) + ((60*60*24) * $tradeData['expiration_days'])));
                                break;
                        }
                        print($expireStr); ?>
				</td>
                </tr>
                <?php $rowNum++;
                }
				// COMMENTS
				if (!empty($tradeData['comments']) && ($team_id == $tradeData['team_2_id'] || $team_id == $tradeData['team_1_id'])) { ?>
                <tr class="<?php echo((($rowNum % 2) == 0) ? 'sl_1' : 'sl_2'); ?>">
					<td><b>Comments:</b></td>
					<td><?php print($tradeData['comments']); ?></td>
                </tr>
				<?php $rowNum++;
                }
				// RESPONSE
				if (!empty($tradeData['response']) && ($team_id == $tradeData['team_2_id'] || $team_id == $tradeData['team_1_id'])) { ?>
					<tr class="<?php echo((($rowNum % 2) == 0) ? 'sl_1' : 'sl_2'); ?>">
						<td><b>Reponse:</b></td>
						<td><?php print($tradeData['response']); ?></td>
					</tr>
				<?php 
				}
				$rowNum++;
				// TRADE PROTESTS
				if ($allowProtests && (isset($tradeData['protest_count']) && $tradeData['protest_count'] > 0)) { ?>
                <tr class="<?php echo((($rowNum % 2) == 0) ? 'sl_1' : 'sl_2'); ?>">
					<td><b>Protests:</b></td>
					<td><?php print($tradeData['protest_count']); ?> <!--span class="small">(<a href="#">Who?</a>)</span--></td>
                </tr>
				<?php 
				$rowNum++;
				} 
				
				if ($transType <= 3) {
					$outMess = "";
					if ($tradeData['status'] == TRADE_OFFERED) {
						if ($transType == 2) {
							$outMess = "This trade requires a response from you.";
						} else if ($transType == 3) {
							$outMess = "You are waiting for a response to this offer.";
						}// END if
					} // END if
					// COMMISSIONER APPROVAL
					if (($tradeData['status'] == TRADE_PENDING_LEAGUE_APPROVAL || $tradeData['status'] == TRADE_PENDING_COMMISH_APPROVAL) && $config['approvalType'] != -1) { 
						$approvalType = loadSimpleDataList('tradeApprovalType'); 
						if (!empty($outMess)) { $outMess.="<br />"; }
						$outMess .= "This trade is pending ".$approvalType[$config['approvalType']]." approval";
					}// END if
				
					if (!empty($outMess)) {
					?>
						<tr class="<?php echo((($rowNum % 2) == 0) ? 'sl_1' : 'sl_2'); ?>">
							<td colspan="2"><span class="notice inline"><?php print($outMess); ?></span></td>
						</tr>
					<?php 
					} // END if (!empty($outMess))
				} // END if ($transType <= 3)
				?>
                <tr class="<?php echo((($rowNum % 2) == 0) ? 'sl_1' : 'sl_2'); ?>">
                	<td colspan="2">
                	<input type='button' rel="reviewbtn" id="<?php print("0|".$transType."|".$tradeData['trade_id']); ?>" class="button" value='Review Trade' style="float:left;margin-right:8px;" />
	                <?php if ($transType == 3) { 
						if ($tradeData['status'] == TRADE_OFFERED) { ?>
                		<input type='button' rel="responsebtn" id="<?php print(TRADE_RETRACTED."|".$tradeData['trade_id']); ?>" class="button" value='Retract' style="float:left;margin-right:8px;" />
                	<?php }
					} else if ($transType == 2) { 
                    	if ($tradeData['status'] == TRADE_OFFERED) { ?>
	                	<input type='button' rel="responsebtn" id="<?php print(TRADE_REJECTED_OWNER."|".$tradeData['trade_id']); ?>" class="button" value='Reject' style="float:left;margin-right:8px;" />
	                	<input type='button' rel="responsebtn" id="<?php print(TRADE_REJECTED_COUNTER."|".$tradeData['trade_id']); ?>" class="button" value='Counter' style="float:left;margin-right:8px;" />
	                	<input type='button' rel="responsebtn" id="<?php print(TRADE_ACCEPTED."|".$tradeData['trade_id']); ?>" class="button" value='Accept' style="float:left;margin-right:8px;" />
                	<?php }
					} else if ($transType == 4) { 
						$showProtestBtn = true;
						if (isset($protests) && sizeof($protests) > 0) {
							foreach ($protests as $tmpProtest) {
								if ($tmpProtest['trade_id'] == $tradeData['trade_id'] && $tmpProtest['team_id'] == $team_id) {
									$showProtestBtn = false;
									break;
								}
							}
						}
						if ($showProtestBtn) {
					?>
                        <input type='button' rel="responsebtn" id="<?php print(TRADE_PROTEST."|".$tradeData['trade_id']); ?>" class="button" value='Protest Trade' style="float:left;margin-right:8px;" />

	                <?php }
					} ?>
                	</td>
                </tr>
                <input type="hidden" name="trade_id" value="<?php print($tradeData['trade_id']); ?>" />
                </form>
                <?php  } ?>
                </table>
                </div>
                        <?php
                                            }
                    }
                }
?>
            </div>
    <p /><br />