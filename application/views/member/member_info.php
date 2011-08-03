    <div id="left-column">
   	<? include_once('nav_members.php'); ?>
    </div>
    <div id="center-column">
   	<div id="subPage">
       	<div class="top-bar"><h1><? echo($thisItem['username']); ?></h1></div>
       	<div id="content">
           	<!-- BEGIN RIGHT COLUMN -->
           	<div id="metaColumn">
               <div id="contentBox">
                    <div class="title">Registration Details</div>
                    <div id="row">
                   	<label>Access Level:</label>
                    <span><?php echo($thisItem['accessStr']); ?></span><br />
                    <label>Registration Date:</label>
                    <span><?php echo($thisItem['dateCreated']);; ?></span><br />
                    <label>Attendance Status:</label>
                    <span><?php echo($thisItem['locked']); ?></span><br />
                    <label>Activation Status:</label>
                    <span><?php echo($thisItem['active']); ?></span><br />
                    </div>
                </div>
           	</div>
               	<!-- BEGIN MAIN COLUMN -->
            <div id="detailColumn">
 				<b>E-Mail Address:</b>
                <span><?php echo($thisItem['email']); ?></span>
                <br /><br />


                <br clear="all" class="clear" />
                <?php if (isset($memberTeams)) { ?>
                    <div id="issueListBox">
                        <h2>Event Registrations (<?php echo(sizeof($thisItem['eventRegs'])); ?>)</h2>
                        <br />
                       <div id="characterList">
                        </div>

                        <br clear="all" class="clear" />
                    </div>
                    <?php } ?>
                </div>
            </div>
       		<div id="foot">

            </div>
        </div>
	</div>
