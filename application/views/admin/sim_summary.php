	<script type="text/javascript">
    var ajaxWait = '<img src="<?php echo(PATH_IMAGES); ?>icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Submitting Request';
	var responseError = '<img src="<?php echo(PATH_IMAGES); ?>icons/stock_mail-priority-high.png" width="24" height="24" border="0" align="absmiddle" />&nbsp;<span class="error">';
	var fader = null;
	$(document).ready(function(){		   
		$('div#activeStatusBox').hide();		
		
		$('a[rel=itemPick]').live('click',function () {
			loadInfo(this);	
		});
		
		<?php if (isset($summaryId) && !empty($summaryId)) { ?>
		var obj = new Object();
		obj.id = <?php echo($summaryId); ?>;
		loadAttendeeInfo(obj);
		<?php } ?>
	});
	function loadInfo(obj) {
		var url = "<?php print($config['fantasy_web_root']); ?>admin/loadSummary/summary_id/"+obj.id;
		$('div#activeList').html(ajaxWait);
		$.getJSON(url, function(data){
			$('div#activeList').empty();
			if (data.code.indexOf("200") != -1) {
				$('div#activeList').append(drawInfoHTML(data));
				if (data.status.indexOf(":") != -1) {
					var status = data.status.split(":");
					$('div#activeStatus').addClass(status[0].toLowerCase());
					$('div#activeStatus').html(status[1]);
				} else {
					$('div#activeStatus').addClass('success');
					$('div#activeStatus').html('Summary loaded successfully');
				}
				$('div#activeStatusBox').fadeIn("slow",function() { setTimeout('fadeStatus("active")',5000); });
			} else {
				$('div#activeList').append('Information for the selected summary could not be found.');
			}
			
		});
		return false;	
	}
	function drawInfoHTML(data) {
		var html = '';
		$.each(data.result.items, function(i,item){
			html += '<h2>Sim for Period ' + item.scoring_period_id + '</h2>';
			html += '<br />';
			var simDate = item.sim_date.split(" ");
			var sim_dateDetails = simDate[0].split("-");
			var sim_timeDetails = simDate[1].split("-");
			html += '<b>Date:</b> ' + sim_dateDetails[1] + "/"+ sim_dateDetails[2] + "/" + sim_dateDetails[0] + " ";
			html += sim_timeDetails[0] + ":"+ sim_timeDetails[1] + ":" + sim_timeDetails[2] + " " + sim_timeDetails[3];
			html += '<br />';
			html += '<b>Processing Time:</b> ' + item.process_time + ' seconds';
			html += '<br />';
			html += '<b>Result:</b> ';
			if (item.sim_result == 1) {
				html += '<span class="success_txt">Success!</span>';
			} else {
				html += '<span class="error_txt">Failed!</span>';
			}
			html += '<br /><br />';
			html += '<b>Sim Details:</b><br />' + unescape(item.sim_summary).split("+").join(" ");
			html += '<br /><br />';
			html += '<b>Comments:</b> ' + unescape(item.comments).split("+").join(" ");
			html += '<br /><br />';
		});
		return html;
	}
	function fadeStatus(type) {
		///alert("Fade out");
		$('div#'+type+'StatusBox').fadeOut("normal",function() { clearTimeout(fader); $('div#'+type+'StatusBox').hide(); });
	}
	</script>
	<!-- BEGIN REGISTRATION FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
        <div class="listPicker">
            <div id="activeStatusBox"><div id="activeStatus"></div></div>
            <div class="listPickerBox">
                <?php
                $itemCount = sizeof($summaries);
                $colLimit = (round($itemCount / 2)) + 1;
                $columnsDrawn = 1;
                $countDrawn = 0;
                $countPerColumn = 0;
                foreach ($summaries as $summary) {
                if ($countPerColumn == 0) {
                ?>
                <div id="listColumn<?php echo($columnsDrawn); ?>" class="listcolumn">
                    <ul>
                <?php } ?>
                        <li><a href="#" rel="itemPick" id="<?php echo($summary['id']); ?>">Period <?php echo($summary['scoring_period_id']." - ".date('m/y/d h:i A',strtotime($summary['sim_date']))); ?></a></li>
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
                        <li>No aummaries were found.</li>
                    </ul>
                </div>
                <?php 
                }
            ?>
            </div>
        </div>
        
        <br class="clear" /> <br /> 
        <div class="listPicker"> 
           <div class="listPickerBox"><div id="activeList">
               Choose a summary above to continue. 
           </div>
           </div>
		</div>
    </div>
    <p /><br />