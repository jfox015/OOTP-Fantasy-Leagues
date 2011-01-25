    <div id="left-column">
    <?php include_once('nav_projects.php'); ?>
    </div>
    <div id="center-column">
    	<div id="subPage">
        	<div id="head">
            <h1>Project Details</h1>
            </div>
        	<div id="content">
            	<!-- BEGIN RIGHT COLUMN -->
            	<div id="metaColumn">
                    	<!-- INFO BOX -->
                    <div id="contentBox">
                        <div class="title">General Details</div>
                        <div id="row">
                        	 <label>Job Code:</label>
                            <span><?php echo($thisItem['jobCode']); ?></span><br />
                            <label>Start Date:</label>
                            <span><?php if ($thisItem['startDate'] != EMPTY_DATE_TIME_STR) { echo(date('m/d/Y',strtotime($thisItem['startDate']))); } else { echo("Not set"); }?></span><br />
                            <label>Due Date:</label>
                            <span><?php if ($thisItem['dueDate'] != EMPTY_DATE_TIME_STR) { echo(date('m/d/Y',strtotime($thisItem['dueDate']))); } else { echo("Not set"); }?></span><br />
                            <label>Close Date:</label>
                            <span><?php if ($thisItem['closeDate'] != EMPTY_DATE_TIME_STR) { echo(date('m/d/Y',strtotime($thisItem['closeDate']))); } else { echo("Not set"); }?></span><br />
                            <label>Status:</label>
                    		<span <?php if ($thisItem['active'] == 1) { echo('style="color:#060"><img src="'.PATH_IMAGES.'icons/img_icon_ok.gif" />&nbsp;&nbsp;Active'); } else { echo('style="color:#C00"><img src="'.PATH_IMAGES.'icons/img_icon_error.gif" />&nbsp;&nbsp;(inactive)'); } ?></span><br />
                        </div>
                        <br clear="all" class="clear" />
                    </div>
                    
            	</div>
                	<!-- BEGIN MAIN COLUMN -->
                <div id="detailColumn">
                    <h2><?php echo($thisItem['summary']); ?></h2>
                    
                    <p /><br />
                    <label>Project Name:</label><br />
                    <?php echo((!empty($thisItem['name']) ? $thisItem['name'] : 'Not Provided')); ?>
                    <p /><br />
                    <label>Summary:</label><br />
                    <?php echo((!empty($thisItem['summary']) ? $thisItem['summary'] : 'Not Provided')); ?>
                   <p /><br />
                    <label>Description:</label><br />
                    <?php echo((!empty($thisItem['description']) ? $thisItem['description'] : 'Not Provided')); ?>
                    
                    <br clear="all" class="clear" /><br />
                    <h2>Bugs (<?php echo(sizeof($thisItem['projectBugs'])); ?>)</h2>
                    <div class="textbox" style="width:100%; min-height:100px; max-height:350px; overflow:scroll;">
                    <table class="listing" cellpadding="5" cellspacing="2" style="width:100%;">
                    <tr class="headline">
                        <th class="first" width="250">Summary</th>
                        <th>Status</th>
                        <th>Severity</th>
                        <th>Priority</th>
                      </tr>
                    <?php 
                    $rowCount = 0;
                    if (isset($thisItem['projectBugs']) && sizeof($thisItem['projectBugs']) > 0) {
                    foreach ($thisItem['projectBugs'] as $id => $data) { 
                        $sum = (strlen($data['summary']) > 100) ? substr($data['summary'],0,100)."...": $data['summary'];
                        ?>
                    <tr class="<?php echo(($rowCount % 2) == 0 ? "s1_l" : "s2_l"); ?>">			
                        <td><b><?php echo anchor('/bug/info/'.$id,$sum); ?></b></td>
                        <td><?php echo($data['bugStatus']); ?></td>
                        <td><?php echo($data['severity']); ?></td>
                        <td><?php echo($data['priority']); ?></td>
                      </tr>
                        <?php $rowCount++;
                    }
					} 
                    if ($rowCount == 0) { ?>
                        <tr class="empty">
                            <td colspan="5" class="results">There were no results</td>   
                        </tr>
                    <?php } 
					?>
                    </table>
                  </div>
               </div>
            </div>
        	<div id="foot">
            <label>Record Last Updated:</label> <?php echo($thisItem['modifiedStr']); ?>
            </div>
        </div>
	</div>
