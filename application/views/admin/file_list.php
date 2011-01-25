<div id="center-column">
    <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
    <br />
    <?php if (isset($missingTables) && sizeof($missingTables) > 0) { ?>
    <span class="error" style="margin:0px; width:98%;"><b>Required data files missing!</b>
        <br /><br />
        The following <b>required</b> OOTP MySQL data SQL files were not found in your 
        server SQL upload directory. Please assure all required files listed below 
        are loaded on the server before proceeding.
        <br /><br />
        <ul>
       <?php foreach ($missingTables as $tableName) {
        	echo("<li><b>".$tableName."</b></li><br />");
	   } ?>
       </ul>
       </span>
       <p /><br />
    <?php } ?>
    <?php
	/*-------------------------------------------------------------
	/	UPDATE 1.0.3
	/	IF THE DB CONNECTION FILE NEEDED TO BE UPDATED, A STATUS
	/ 	MESSAGE IS DIPLSYED HERE>
	/------------------------------------------------------------*/
	if (isset($db_file_update) && !empty($db_file_update)) { ?>
	<?php echo('<span class="warn">DB File Updated</span>'); ?>
	<br />
	<?php } 
	// END 1.0.3 MOD 
	?>
    
    <?php if (isset($fileList) && sizeof($fileList) > 0) { ?>
    <b style="color:#c00; font-weight:bold;">NOTE:</b> File names that are <span class="hilight">highlighted</span> are required by the fantasy league mod to work correctly.
    <form action='<?php echo($config['fantasy_web_root']); ?>admin/loadSQLFiles' method='post' name="fileList" id="fileList">    
   	<div id="activeStatusBox"><div id="activeStatus"></div></div>
    <div class='textbox'>
    <table cellpadding=2 cellspacing=0 border=0 style="width:825px;" class='sortable'>
     <thead>
     <tr class='title'>
     	<td width="25%" class='hsc_l'>Filename</td>
        <td width="25%" class='hsc'>Timestamp</td>
        <td width="25%" class='hsc'>Size</td>
        <td width="25%" class='hsc' colspan=2>Actions</td>
      </tr>
      </thead>
   <?php 
   $loadCnt = sizeof($fileList);
   $cnt=0;
   if ($loadCnt>0) {asort($fileList);}
   $isSplit=0;
   $splitParents = array();
   foreach ($fileList as $file){
      $ex = explode(".",$file);
      $fileTime=filemtime($config['sql_file_path']."/".$file);
      $tblName=$ex[0];
      if (($isSplit==0)&&(substr_count($file,".mysql_")>0)) {$isSplit=1;}
      $cls='s'.($cnt%2+1); ?>
      <tr class='<?php echo($cls); ?>'>
      <td class='<?php echo($cls); ?>_l'><?php 
	  $fileArr = explode(".",$file);
	 // echo("Table name from file = ".$fileArr[0]."<br />");
	  $hilite = -1;
	  if (isset($requiredTables) && sizeof($requiredTables) > 0) {
		  foreach ($requiredTables as $tableName) {
			  //echo("Table name from list = ".$tableName."<br />");
			  if ($tableName == $fileArr[0]) {
				  $hilite = 1;
				  break;
			  } // END if
		  } // END foreach
	  } // END if
	  if ($hilite == 1) { echo('<span class="hilight">'); } // END if
	  echo($file); 
	  if ($hilite == 1) { echo('</span>'); } // END if
      if (isset($filesLoaded[$config['sql_file_path']."/".$file])) { echo("- <b>LOADED</b>"); } // END if
      $fsize=filesize($config['sql_file_path']."/".$file);
	  ?>
      </td>
      <td sorttable_customkey='<?php echo($fileTime); ?>'><?php echo(date("D M j, Y H:i",$fileTime)); ?></td>
      <td sorttable_customkey='<?php echo($fsize); ?>'><?php echo(formatBytes($fsize)); ?></td>
      <td sorttable_customkey=1><?php echo(anchor('/admin/loadSQLFiles/returnPage/file_list/filename/'.$file,'Load')); ?>
      <?php 
	  /*--------------------------------------
	  /	UPDATE 1.0.3
	  / Identify files with splits and add them 
	  /	to an array so the larger parent file
	  / is skipped in favor of the splits.
	  /----------------------------------------*/
	  if (strpos($file,".mysql_")>0) { 
	  	echo('/<a href="#" id="'.$file.'" rel="delete">Delete Split</a>');
		if (!in_array($fileArr[0].".mysql.sql",$splitParents)) {
			array_push($splitParents,$fileArr[0].".mysql.sql");
		} // END if
	  } else { 
	  	echo('/<a href="#" id="'.$file.'" rel="split">Split</a>');
	  } // END if
	  ?>
      </td>
      <td sorttable_customkey=1><input type='checkbox' name='loadList[]' value='<?php echo($file); ?>' /></td>
      </tr>
      <?php 
      $cnt++;
    } // END if
   if ($isSplit==1)
    { ?>
        <tfoot><tr class='headline'><td class='hsc2' colspan=2>&nbsp;</td>
      <td class='hsc2' colspan=2><?php echo(anchor('/admin/splitSQLFile/delete/1/filename/DELSPLITS','Delete All Splits'));  ?></td>
      <td>&nbsp;</td>
      </tr></tfoot>
    <?php } ?>
     <tfoot><tr><td colspan="5" align="right">
     <input type="hidden" name="returnPage" value="file_list" />
     <a href="#" onclick="checkRequired(); uncheckSplitParents(); return false;">Select Only Required</a> | 
     <a href="#" onclick="setCheckBoxState(true); return false;">Select All</a> | <a 
     href="#" oncLick="setCheckBoxState(false); return false;">Select None</a><br />
     <span class="button_bar"><input type='submit' name='Load Checked' value='Load Checked' /></span></td></tr></tfoot>
    </table>
   </div>
   </form>
   <?php } // END if ?>
</div>

<br class="clear" />

<script type="text/javascript">
var ajaxWait = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
var responseError = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;';
var fader = null;
var refreshAfterUpdate = false;

$(document).ready(function(){
	$('a[rel=split]').click(function () {
		refreshAfterUpdate = true;
		runAjax("<?php echo($config['fantasy_web_root']); ?>admin/splitSQLFile/filename/"+this.id); return false;
	});
	$('a[rel=delete]').click(function () {
		refreshAfterUpdate = true;
		runAjax("<?php echo($config['fantasy_web_root']); ?>admin/splitSQLFile/delete/1/filename/"+this.id); return false;
	});
	checkRequired();
	uncheckSplitParents();
});
function runAjax (url) {
		//clearTimeout(fader);
		$('div#activeStatus').removeClass('error');
		$('div#activeStatus').removeClass('success');
		$('div#activeStatus').html(ajaxWait);
		$('div#activeStatusBox').fadeIn("slow");
		$.getJSON(url, function(data){
			error = false;
			if (data.status.indexOf(":") != -1) {
				var status = data.status.split(":");
				$('div#activeStatus').addClass(status[0].toLowerCase());
				var response = status[1];
				if (status[0].toLowerCase() == "error") {
					response = responseError + response;
					error = true;
				}
				$('div#activeStatus').html(response);
			} else {
				$('div#activeStatus').addClass('success');
				$('div#activeStatus').html('Operation Completed Successfully');
			}
			if (!error && refreshAfterUpdate) {
				setTimeout('refreshPage()',3000);
			}
			//setTimeout('fadeStatus("active")',15000);
		});
	}

	function fadeStatus(type) {
		$('div#'+type+'StatusBox').fadeOut("normal",function() { clearTimeout(fader); $('div#'+type+'StatusBox').hide(); });
	}
	function refreshPage() { 
		document.location.href = '<?php echo($_SERVER['PHP_SELF']); ?>';
	}
	
<?php if (isset($requiredTables) && sizeof($requiredTables) > 0) { ?>
var required = new Array(<?php echo(sizeof($requiredTables)); ?>);
<?php
  $count = 0;
  foreach ($requiredTables as $tableName) {
	echo("required[".$count."] = '".$tableName."';");
	$count++;
  }
}
if (isset($splitParents) && sizeof($splitParents) > 0) { ?>
var parentList = new Array(<?php echo(sizeof($splitParents)); ?>);
<?php
	$count = 0;
	foreach($splitParents as $parent) {
		echo('parentList['.$count.'] = "'.$parent.'";');
		$count++;
	}
}
?>
function uncheckSplitParents() {
	var form = document.fileList;
	if (parentList != null) {
		for (var i = 0; i < parentList.length; i++) {
			for (var j = 0; j < form.elements.length; j++) {
				if (form.elements[j].type == 'checkbox' && form.elements[j].value == parentList[i]) {
					form.elements[j].checked = false; // END if
					break;
				}
			}
		}
	}	
}
function checkRequired() {
	var form = document.fileList;
	if (required != null) {
		var startIndex = 0;
		for (var i = 0; i < required.length; i++) {
			for (var j = startIndex; j < form.elements.length; j++) {
				if (i == 0) {
					//alert(form.elements[j].value);
					//alert(required[i]);
				}
				if (form.elements[j].type == 'checkbox') {
					var table = form.elements[j].value.split(".");
					if (table[0] == required[i]) {
						form.elements[j].checked = true; // END if
						//startIndex = j;
					}
				}
			} // END for
		}
	}
}
function setCheckBoxState(state) {
	var form = document.fileList;
	for (var i = 0; i < form.elements.length; i++) {
		if (form.elements[i].type == 'checkbox')
			form.elements[i].checked = state; // END if
	} // END for
} // END function
</script>