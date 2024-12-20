<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
<script type="text/javascript" charset="UTF-8">
	var ajaxWait = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
	var fader = null;
	var team_id = <?php echo($team_id); ?>;
	var league_id = <?php echo($league_id); ?>;
	var curr_type = "pos";
	var curr_param = 2;
	var max_add_drop = 3;
	var addPlayers = new Array(max_add_drop);
	var dropPlayers = new Array(max_add_drop);
	var playerCache = new Array(25);
	var listType = <?php echo($list_type); ?>;
	$(document).ready(function(){	

		$('select#list_type').change(function(){
			if ($('select#list_type').val() != 'X') {							  
				var obj = new Object();
				obj.id = curr_type+'|'+curr_param;
				listType = $('select#list_type').val();
				loadList(obj);
			}
		});

		$('div#activeStatusBox').hide();
		
		$('#optChooseAlpha').click(function(){
			$('#optChooseAlpha').addClass('active');
			$('#optChooseAll').removeClass('active');
			$('#optChoosePos').removeClass('active');
			$('#list_type_div').css('display','none');
			$('#optionsAlpha').css('display','block');
			$('#optionsPos').css('display','none');
			listType = 1;
			loadList(null);
			return false;
			
		});
		$('#optChoosePos').click(function(){
			$('#optChooseAlpha').removeClass('active');
			$('#optChooseAll').removeClass('active');
			$('#optChoosePos').addClass('active');
			$('#list_type_div').css('display','block');
			$('#optionsAlpha').css('display','none');
			$('#optionsPos').css('display','block');
			loadList(this);
			return false;
		});
		$('#optChooseAll').click(function(){
			$('#optChooseAlpha').removeClass('active');
			$('#optChooseAll').addClass('active');
			$('#optChoosePos').removeClass('active');
			$('#list_type_div').css('display','none');
			$('#optionsAlpha').css('display','none');
			$('#optionsPos').css('display','none');
			listType = 1;
			loadList(this);
			return false;
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
			if (offList(addPlayers,this.id)) {
				updatePageLists();
			}
			return false;
		});	
		$('img[rel=dropListRemove]').live('click',function () {
			if (offList(dropPlayers,this.id)) {
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
		$('a[rel=listLoad]').live('click',function () { 
			loadList(this);
			highlightAlpha(this);
			return false;
		});
	});
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
					errorListName = "add";
					break;
				}
			}
		}
		if (dropPlayers.length > 0) {
			if (!found) {
				for (var i = 0; i < dropPlayers.length; i++) {
					if (dropPlayers[i] != null && params[0] == dropPlayers[i].id) {
						found = true;
						errorListName = "drop";
						break;
					}
				}
			}
		}
		if (!found) {
			if (params[3] == "add" && listLength(addPlayers) >= max_add_drop) {
				$('div#listStatus').addClass('error');
				$('div#listStatus').html("You can only add a maximum of "+max_add_drop+" players at a time.");
			} else if (params[3] == "remove" && listLength(dropPlayers) >= max_add_drop) {
				$('div#listStatus').addClass('error');
				$('div#listStatus').html("You can only drop a maximum of "+max_add_drop+" players at a time.");
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
								addToList(playerCache,player);
								if (params[3] == "add") {
									addToList(addPlayers,player);
								} else {
									addToList(dropPlayers,player);
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
						addToList(addPlayers,player);
					} else {
						addToList(dropPlayers,player);
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
	function clearTransaction() {
		clearList(addPlayers);
		clearList(dropPlayers);
		updatePageLists();
		$('div#listStatus').removeClass('error');
		$('div#listStatus').removeClass('success');
		$('div#listStatus').empty();		
	}
	
	function updatePageLists() {
		var totalCount = 0;
		var rowCount = 0;
		var addHTML = '<table>';
		var rowClass = '';
		for (var i = 0; i < addPlayers.length; i++) {
			if (addPlayers[i] != null && (addPlayers[i].id != '' && addPlayers[i].id != -1)) {
				rowClass = ((rowCount % 2) == 0) ? 'sl_1' : 'sl_2';
				addHTML += '<tr align=left class="'+rowClass+'">';
				addHTML += '<td><img alt="Remove" title="Remove" rel="addListRemove" id="'+addPlayers[i]['id']+'" src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="16" height="16" align="absmiddle" border="0" />';
				addHTML += ' &nbsp;<a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/'+league_id+'/player_id/'+addPlayers[i]['id']+'" title="Click to view bio" alt="Click to view bio">'+addPlayers[i]['player_name']+'</a>';
				if (addPlayers[i]['position'] == "P") {
					addHTML += ' '+addPlayers[i].role;
				} else {
					addHTML += ' '+addPlayers[i].position;
				}
				addHTML += '</td>';
				addHTML += '</tr>';
				rowCount++;
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
				dropHTML += '<td><img alt="Remove" title="Remove" rel="dropListRemove" id="'+dropPlayers[i]['id']+'" src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="16" height="16" align="absmiddle" border="0" />';
				dropHTML += ' &nbsp;<a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/'+league_id+'/player_id/'+dropPlayers[i]['id']+'" title="Click to view bio" alt="Click to view bio">'+dropPlayers[i]['player_name']+'</a>';
				if (dropPlayers[i]['position'] == "P") {
					dropHTML += ' '+dropPlayers[i].role;
				} else {
					dropHTML += ' '+dropPlayers[i].position;
				}
				dropHTML += '</td>';
				dropHTML += '</tr>';
				rowCount++;
			}
		} // END for
		if (rowCount == 0) {
			dropHTML+='<tr><td>No players added yet</td></tr>'; 
		}
		totalCount += rowCount;
		dropHTML+='</table>';
		$('div#playersToDrop').empty();
		$('div#playersToDrop').append(dropHTML);
		if (totalCount > 0) {
			btnDisplay = 'block';
		} else {
			btnDisplay = 'none';
		}
		$('button#btnSubmit').css('display',btnDisplay);
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
		// PREPARE URL
		var url = "<?php echo($config['fantasy_web_root']); ?>team/processTransaction/league_id/"+league_id+"/team_id/"+team_id+"/add/"+addList+"/drop/"+dropList+cacheBuster();
		$('div#listStatus').empty();
		$('div#listStatus').html(ajaxWait);
		$('div#listStatusBox').show();
		$.getJSON(url, function(data){
			if (data.code.indexOf("200") != -1) {
				$('div#activeList').empty();
				$('div#activeList').append(drawResults(data,'itemRemove','Remove'));
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#activeStatus').addClass(status[0].toLowerCase());
					$('div#activeStatus').html(status[1]);
					if (status[0].toLowerCase() == "notice") {
						clearTransaction();
						var obj = new Object();
						obj.id = curr_type+"|"+curr_param;
						loadList(obj);
					}
				} else {
					$('div#activeStatus').addClass('success');
					$('div#activeStatus').html('Transaction Completed Successfully');
					clearTransaction();
					var obj = new Object();
					obj.id = curr_type+"|"+curr_param;
					loadList(obj);
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
		
		// SUBMIT QUERY
		/*$('div#activeList').html(ajaxWait);
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
			
		});*/
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
				//sorttable.makeSortable(document.getElementById("stats_table"));
			} else {
				$('div#pickList').append('<div id="listColumn1" class="listcolumn"><ul> <li>No players were returned.</li> </ul> </div>');
			}
		});
	
	}
	function highlightAlpha(obj) {
		var objList = $(obj.id).siblings();
		if (objList) {
			$.each(objList, function(i,item) {
				$(item.id).removeClass('active');
			});
		}
		$(obj.id).addClass('active');
	}
	function fadeStatus(type) {
		///alert("Fade out");
		$('div#'+type+'StatusBox').fadeOut("normal",function() { clearTimeout(fader); $('div#'+type+'StatusBox').hide(); });
	}
	function drawResults(data,rel,alt) {
		var itemCount = data.result.items.length;
		var colLimit = (Math.round(itemCount / 3)) + 1; 
		var columnsDrawn = 1;
		var countDrawn = 0;
		var countPerColumn = 0;
		var outHTML = '';
		var type = "list";
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
						outHTML += '<th height="17" class="hsn2" style="text-align:left;">'+cols[i]+'</th>';
						break;
					case 'Team':
					case 'Draft':
						break;
					default:
						outHTML += '<th class="hsn2" style="text-align:center;">'+cols[i]+'</th>';
						break;
				}
			}
			outHTML += '</tr>';
			outHTML += '</thead>';
			outHTML += '<tbody>';
			
		}
		$.each(data.result.items, function(i,item){
			//if (rownum == 0) alert("type = " + type);
			if (type == "list") {
				if (countPerColumn == 0) {
					outHTML += '<div id="listColumn'+columnsDrawn+'" class="listcolumn"><ul>';
				}
				if (item.id != '' && item.name != '') {
					if (rel == 'itemPick') {
						outHTML += '<li>';
						outHTML += '<a alt="'+alt+'" title="'+alt+'" rel="'+rel+'" id="'+item.id+'|'+item.position+'|'+item.role+'" href="#"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/add.png" width="16" height="16" alt="Add" title="Add" /></a>&nbsp;';
						outHTML += '<a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/'+league_id+'/player_id/'+item.id+'" target="_blank">'+item.player_name+'</a>';
						if (item.positions != null && item.positions != '') { outHTML += '&nbsp;<span style="font-size:smaller">'+item.positions+'</span>'; } 
						outHTML += '</li>';
					} else
						outHTML += '<li><img alt="'+alt+'" title="'+alt+'" rel="'+rel+'" id="'+item.id+'" src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="16" height="16" align="absmiddle" border="0" /> <a href="<?php echo($config['fantasy_web_root']); ?>player/info/league_id/'+league_id+'/player_id/'+item.id+'" title="Click to view bio" alt="Click to view bio">'+item.player_name+'</a></li>';
					countDrawn++;
					countPerColumn++;
				}
				if (countPerColumn == colLimit || countDrawn == itemCount) {
					outHTML += '</ul></div>'
					countPerColumn = 0;
					columnsDrawn++;
				}
			} else {
                var bg = ((rownum % 2) == 0) ? '#E0E0E0' : '#fff';
				outHTML += '<tr bgcolor="'+bg+'" align="center" style="background:'+bg+'">';
				outHTML += '<td><a alt="'+alt+'" title="'+alt+'" rel="'+rel+'" id="'+item.id+'|'+item.position+'|'+item.role+'" href="#"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/add.png" width="16" height="16" alt="Add" title="Add" /></a></td>';
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
					var ratingColor = '';
					if (item.rating> 0) {
						ratingColor = "#080";
					} else if (item.rating < 0) {
						ratingColor = "#C00";
					} else {
						ratingColor = "#000";
					}
					outHTML += '<td><span style="color:'+ratingColor+'">'+item.rating+'</span></td>';
				}
				outHTML += '</tr>';
            } // END if
			rownum++;
		});
		if (type == "stats") {
			outHTML += '</tbody>';
			outHTML += '</table>';
		}
		if (outHTML == '') {
			if (type == "list") {
				outHTML = '<div id="listColumn1" class="emptyList"><ul><li>No players have been added to this team</li></ul></div>';
			}
		}
		return outHTML;
	}
    </script>
    
    <div id="single-column">
        <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        
        <h2><?php echo($team_name); ?></h2>
            <p><br />
            <b>Players on roster<?php if ($game_date != null) { echo(" for game date <i>".date('M j, Y',strtotime($game_date))."</i>"); } ?></b>
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
                    	<li><img alt="Remove" title="Remove" rel="itemRemove" id="<?php echo($player['id']); ?>" 
                        src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="16" 
                        height="16" align="absmiddle" /> 
                        <?php if ($player['player_position'] == 1) {
							$pos = $player['player_role'];
						} else {
							$pos = $player['player_position'];
						}
						echo(get_pos($pos)); 
						?>
                        <a href="<?php echo($config['fantasy_web_root']); ?>players/info/league_id/<?php echo($league_id); ?>/player_id/<?php echo($player['id']); ?>"><?php echo($player['player_name']); ?></a></li>
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
            <p>&nbsp;<br clear="all" /><br />
            <b>Add a Player</b>
            <br />
            <div id="pickStatusBox"><div id="pickStatus"></div></div>
            
            <div>
            	<div id="optionsBar">
                	<div id="options">
                    <ul>
                        <li><a id="optChoosePos" class="active" href="#">By Position</a></li>
                       	<li><a id="optChooseAlpha" href="#">Alphabetical</a></li>
                        <!--li><a id="optChoosePop" href="#">Most Popular</a></li-->
                        <li><a id="optChooseAll" rel="listLoad" id="all" href="#">All</a></li>
                    </ul>
                    </div>
                    
                    <div id="subBars">
                         <div style="width:155px; text-align:right; float:right;margin-top:5px;" id="list_type_div">
                           <label for="list_type" style="float:none;margin:1px;width:auto;min-width:none;">Display:</label> 
                           <select style="float:none;margin:0px;width:auto;min-width:none;" name="list_type" id="list_type">
                                <option value="X">Select View</option>
                                <option value="1"<?php if (isset($list_type) && $list_type ==1) { echo(" selected='selected'"); } ?>>List</option>
                                <option value="2"<?php if (isset($list_type) && $list_type ==2) { echo(" selected='selected'"); } ?>>Stats</option>
                            </select>
                        </div>
                        <div id="optionsPos" class="subOptionsBar optionsPos">
                        <ul>
                            <?php 
                            $positions = array(2,3,4,5,6,7,8,9,20,11,12,13);
                            foreach ($positions as $pos) {
                                echo('<li><a rel="listLoad" href="#" id="pos|'.$pos.'">'.get_pos($pos).'</a></li>');
                            } ?>
                        </ul>
                        
                       
                        
                        </div>
                        <div id="optionsAlpha" class="subOptionsBar">
                        <ul>
                            <?php 
                            for ($i = 65; $i < 91; $i++) {
                                echo('<li><a rel="listLoad" href="#" id="alpha|'.chr($i).'">'.chr($i).'</a></li>');
                            } ?>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div id="pickList" class="listPickerBox">
                <?php
				if (isset($playerList) && sizeof($playerList) > 0) {
					$itemCount = sizeof($playerList);
					$colLimit = (round($itemCount / 3)) + 1;
					$columnsDrawn = 1;
					$countDrawn = 0;
					$countPerColumn = 0;
					foreach ($playerList as $itemArr) {
							/*-----------------------------------
							/
							/	FREE AGENT LIST VIEW
							/
							/-----------------------------------*/
							if ($countPerColumn == 0) {
							?>
							<div id="listColumn<?php echo($columnsDrawn); ?>" class="listcolumn">
								<ul>
							<?php } // END if
							?>
									<li><a rel="itemPick" id="<?php echo($itemArr['id']); ?>|<?php echo($itemArr['position']); ?>|<?php echo($itemArr['role']); ?>" href="#"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/add.png" width="16" height="16" alt="Add" title="Add" /></a>&nbsp;<a href="<?php 
									$gmPos = '';
									if (isset($itemArr['positions']) || !empty($itemArr['positions'])) {
										$gmPos = makeEligibilityString($itemArr['positions']);
									}
									echo($config['fantasy_web_root']); ?>players/info/<?php echo($itemArr['id']); ?>" target="_blank"><?php echo($itemArr['player_name']); ?></a><?php if (!empty($gmPos)) { echo("&nbsp;<span style='font-size:smaller'>".$gmPos."</span>"); } ?></li>
							<?php 	$countDrawn++;
						
								$countPerColumn++;
								if ($countPerColumn == $colLimit || $countDrawn == $itemCount) { ?>
								</ul>
							</div>
							<?php 	
								$countPerColumn = 0;
								$columnsDrawn++;
							}  // END if
							if ($countDrawn == 0) { ?>
                            <div id="listColumn1" class="emptyList">
                                <ul>
                                    <li>No players found.</li>
                                </ul>
                            </div>
                            <?php }
						} // END foreach
				} else {
					/*------------------------------------------------
					/
					/	BEGIN STATS TABLE
					/
					/-----------------------------------------------*/
					?>
					<?php
					if (isset($formatted_stats)){
						echo($formatted_stats);						 
					}
					?>
				<?php
                } // END if
				?>
             </div>
		</div>
	</div>
            
            <div id="right-column">
            	<div id="listStatusBox"><div id="listStatus"></div></div>
            	<div class='textbox'>
                <table cellpadding="0" cellspacing="0" width="265px">
                <tr class='title'>
                    <td style='padding:3px'>Transaction Summary</td>
                </tr>
                <tr class='headline'>
                    <td style='padding:6px'>Players to Add</td>
                </tr>
                <tr>
                    <td style='padding:3px'>
                    <table cellpadding="2" cellspacing="1" style="width:100%;">
                    <tr>
                    	<td>
                        <div id="playersToAdd">
                        <table>
                        <tr class="s1_2">
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
                    <td style='padding:6px'>Players to Drop</td>
                </tr>
                <tr>
                    <td style='padding:3px'>
                    <table cellpadding="2" cellspacing="1" style="width:100%;">
                    <tr>
                    	<td>
                        <div id="playersToDrop">
                        <table>
                        <tr class="s1_2">
                            <td width="40%">No players added yet</td>
                        </tr>
                        </table>
                        </div>
                        </td>
                    </tr>
                    </table>
                    </td>
                </tr>
                <tr>
                    <td style='padding:6px'>
                    <div class="button_bar" style="text-align:right;">
                    	<button id="btnClear" class="sitebtn adddrop">Clear</button>
						<button id="btnSubmit" class="sitebtn adddrop">Submit</button>
                    </div></td>
                </tr>
                
                </table>
                </div>
                <?php 
				/*-------------------------------------------------------
				/	WAIVER CLAIMS
				/-----------------------------------------------------*/
				if (isset($waiver_claims) && sizeof($waiver_claims) > 0) { ?>
                <div class='textbox'>
                <table cellpadding="3" cellspacing="1" width="265px">
                <tr class='title'>
                    <td style='padding:3px' colspan="3">Pending Waivers Claims</td>
                </tr>
                <tr class='headline'>
					<td width="60%">Player</td>
                    <td width="30%">In Period</td>
                    <td width="10%">&nbsp;</td>
                </tr>
                <?php
				$rowNum = 0;
				if (sizeof($waiver_claims) > 0) {
					foreach ($waiver_claims as $claimData) { 
						$bg = (($rowNum % 2) == 0) ? '#fff' : '#E0E0E0'; ?>
                <tr style="background-color:<?php echo($bg); ?>">
					<td><?php 
					$pos = -1;
					if ($claimData['position'] == 1) {
						if ($claimData['role'] == 13) {
							$pos = 12;
						} else {
							$pos = $claimData['role'];
						}
					} else {
						if ($claimData['position'] == 7 || $claimData['position'] == 8 || $claimData['position'] == 9) {
							$pos = 20;
						} else {
							$pos = $claimData['position'];
						}
					}
					
					echo(get_pos($pos)." ".anchor('/players/info/league_id/'.$league_id.'/player_id/'.$claimData['player_id'],$claimData['player_name'])); ?></td>
                	<td class="hsc2_c"><?php echo($claimData['waiver_period']); ?></td>
                    <td class="hsc2_c">
					<?php 
                    echo( anchor('/team/removeClaim/team_id/'.$team_id.'/id/'.$claimData['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" />')); ?></td>
                </tr>
                <?php $rowNum++;
				} 
				} else { ?>
				<tr class="s1_1">
					<td colspan="2">No claims were found.</td>
                </tr>
				<?php } 	?>
                </table>
                </div>
            	<?php } ?>
                
                <?php 
				/*-------------------------------------------------------
				/	WAIVER ORDER
				/-----------------------------------------------------*/
				if (isset($waiver_order) && sizeof($waiver_order) > 0) { ?>
                <div class='textbox'>
                <table cellpadding="3" cellspacing="1" width="265px">
                <tr class='title'>
                    <td style='padding:3px' colspan="2">Waivers Order</td>
                </tr>
                <tr>
                    <td style='padding:6px' colspan="2">For Scoring Period <?php echo($scoring_period['id']); ?></td>
                </tr>
                <tr class='headline'>
					<td width="20%">Rank</td>
                    <td width="60%">Team</td>
                </tr>
                <?php
				$rowNum = 0;
				if (sizeof($waiver_order) > 0) {
					foreach ($waiver_order as $teamData) { 
						$bg = (($rowNum % 2) == 0) ? '#fff' : '#E0E0E0'; ?>
                <tr style="background-color:<?php echo($bg); ?>">
					<td class="hsc2_c"><?php echo($teamData['waiver_rank']); ?></td>
                    <td class="hsc2_l"><?php echo(anchor('/team/info/'.$teamData['id'],$teamData['teamname']." ".$teamData['teamnick'])); ?></td>
                </tr>
                <?php $rowNum++;
				} 
				} else { ?>
				<tr class="s1_1">
					<td colspan="2">No teams were found.</td>
                </tr>
				<?php } 	?>
                </table>
                </div>
            	<?php } ?>
            </div>
    <p><br />