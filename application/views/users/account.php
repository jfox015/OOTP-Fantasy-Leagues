    <div id="column-center">
   	<div id="subPage">
       	<div id="head">
            <h1>Account Details</h1>
            </div>
       	<div id="content">
           	<!-- BEGIN RIGHT COLUMN -->
           	<div id="metaColumn">
           		<?php if ($loggedIn) { ?>
                   	<!-- Tool Box -->
                    <div id="contentBox" class="dashboard">
                        <div class="title">My Toolbox</div>
                        <div id="row">
                        <ul class="iconmenu">
                        <li><?php echo anchor('/user/account/edit','<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" border="0" />'); ?><br />
                        Account Settings</li>
                        <li><?php echo anchor('/user/change_password','<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" border="0" />'); ?><br />
                        Change Password</li>
                        </ul>
                        </div>
                    </div>
                    
                    <?php } ?>
                   	<!-- INFO BOX -->
                    <div id="contentBox">
                        <div class="title">General Information</div>
                        <div id="row">
                            <label>E-Mail:</label>
                            <span><?php echo($account->email); ?></span><br />
                            <label>User Type:</label>
                            <span><?php echo($account->userType); ?></span><br />
                            <?php if ($accessLevel == ACCESS_ADMINISTRATE) { ?>
                            <label>User Level:</label>
                            <span><?php echo($account->userLevel); ?></span><br />  
                            <label>Access Level:</label>
                            <span><?php echo($account->accessLevel); ?></span><br />  
                            <?php } ?>
                        </div>
                        <br clear="all" class="clear" />
                    </div>
                    
                   	<!-- Link BOX FUTURE-->
                    <div id="contentBox">
                        <div class="title">My Links</div>
                        Placeholder for future links support
                    </div>
           	</div>
               	<!-- BEGIN MAIN COLUMN -->
                <div id="detailColumn">
					
                    <p /><br clear="all" class="clear" />
				</div>
            </div>
       	<div id="foot">
            <label>Record Last Updated:</label> 
            </div>
        </div>
	</div>