    <div id="left-column">
    <?php include_once('nav_bugs.php'); ?>
    </div>
    <div id="center-column">
    	<div id="subPage">
        	<div id="head">
            <img src="<?php echo(PATH_IMAGES); ?>/bug.gif" width="65" height="65" border="0" align="left" />
            <h1>Bug Details</h1>
            </div>
        	<div id="content" style="background:#FFD;">
            	<!-- BEGIN RIGHT COLUMN -->
            	<div id="metaColumn">                    
                    	<!-- INFO BOX -->
                    <div id="contentBox">
                        <div class="title">Status Details</div>
                        <div id="row">
                        <label>Severity:</label>
                        <span class="severity_<?php echo $thisItem['severityStr']; ?>"><?php echo($thisItem['severityStr']); ?></span><br />
                        <label>Priority:</label>
                        <span class="priority_<?php echo $thisItem['priorityStr']; ?>"><?php echo($thisItem['priorityStr']); ?></span><br />
                        <label>Status:</label>
                        <span class="status_<?php echo $thisItem['statusStr']; ?>"><?php echo($thisItem['statusStr']); ?></span><br />
                        </div>
                    </div>
                    	<!-- INFO BOX -->
                    <div id="contentBox">
                        <div class="title">General Details</div>
                        <div id="row">
                        	<label>ID:</label>
                            <span><?php echo($thisItem['id']); ?></span><br />
                            <label>Created:</label>
                            <span><?php echo($thisItem['entryInfo']); ?></span><br />
                            <label>Assigned To:</label>
                            <span><?php echo($thisItem['assignedTo']); ?></span><br />
                            <span class="empty">&nbsp;</span>
                            <label>Category:</label>
                            <span><?php echo($thisItem['categoryStr']); ?></span><br />
                            <?php if (isset($thisItem['subcategoryStr']) && !empty($thisItem['subcategoryStr'])) { ?>
                            <label>Sub Category:</label>
                            <span><?php echo($thisItem['subcategoryStr']); ?></span><br />
                            <?php } ?>
                            <span class="empty">&nbsp;</span>
                            <label>OS:</label>
                            <span><?php echo($thisItem['platformStr']); ?></span><br />
                            <label>Browser:</label>
                            <span><?php echo($thisItem['browserStr']); ?></span><br />
                            <label>Browser Version:</label>
                            <span><?php echo($thisItem['browserVersion']); ?></span><br />
                        </div>
                        <br clear="all" class="clear" />
                    </div>
                    
                    	<!-- ATTACHEMNTS BOX -->
                    <div id="contentBox">
                        <div class="title">Attachment</div>
                        <div id="row">
                        <?php if (!empty($thisItem['attachement'])) { ?>
                        <a href="<?php echo(PATH_BUGS_ATTACHEMENTS.$thisItem['attachement']); ?>" 
                        target="_blank"><img src="<?php echo(PATH_IMAGES); ?>icons/icon_attachment.gif" width="32" 
                        height="32" border="0" alt="Click to Open Attachement" title="Click to Open Attachement" align="absmiddle" /></a><br />
                        <div class="tools">
                        <a href="#" id="attachRemove">Remove Attachment</a></div>
                        </div>
                        <?php } else { ?>
                        <span class="empty">
                        No Attachements found</span>
                        <?php } ?>
                        </div>
                    </div>
                    
            	</div>
                	<!-- BEGIN MAIN COLUMN -->
                <div id="detailColumn">
                    <h2><?php echo($thisItem['summary']); ?></h2>
                    
                    <p /><br />
                    <label>Project:</label><br />
                    <?php echo((!empty($thisItem['projectStr']) ? anchor('/project/info/'.$thisItem['projectId'],$thisItem['projectStr']) : 'Not Provided')); ?>
                    <p /><br />
                    <label>Component:</label><br />
                    <?php echo((!empty($thisItem['component']) ? $thisItem['component'] : 'Not Provided')); ?>
                   <p /><br />
                    <label>Description:</label><br />
                    <?php echo((!empty($thisItem['description']) ? $thisItem['description'] : 'Not Provided')); ?>
                   <p /><br />
                    <label>URL:</label><br />
                    <?php echo((!empty($thisItem['url']) ? '<a href="'.$thisItem['url'].'" target="_blank">'.$thisItem['url'].'</a>' : '')); ?>
                     <p /><br />
                    <label>Comments:</label><br />
                    <div class="comments_box"><?php echo((!empty($thisItem['comments']) ? $thisItem['comments'] : 'None')); ?></div>
                    
                </div>
            </div>
        	<div id="foot">
            <label>Record Last Updated:</label> <?php echo($thisItem['modifiedStr']); ?>
            </div>
        </div>
	</div>
