		<!-- BEGIN REGISTRATION FORM -->
    <style>
	label { margin-top:0px; } 
	</style>
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1>
		<?php 
		if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
			$avatar = PATH_LEAGUES_AVATARS.$thisItem['avatar']; 
		} else {
			$avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
		} ?>
		<img src="<?php echo($avatar); ?>" 
        border="0" width="24" height="24" alt="<?php echo($thisItem['league_name']); ?>" 
        title="<?php echo($thisItem['league_name']); ?>" /> 
		<?php echo($subTitle); ?></h1>
        <br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Current Settings (Read Only)</td>
        </tr>
        <tr>
            <td style="padding:10px;">
            
            <label>League Name:</label> <?php print($thisItem['league_name'].' ('.anchor('league/submit/mode/edit/id/'.$league_id,'Edit').')'); ?>
            <p /><br />
            <label>Description</label> <?php print($thisItem['description'].' ('.anchor('league/submit/mode/edit/id/'.$league_id,'Edit').')'); ?>
            <p /><br />
            <label>Team Count</label> <?php print($thisItem['max_teams']); ?>
            <p /><br />
           <label>Public/Private</label> <?php print($thisItem['access_type']); ?>
            <p /><br />
             <label>Scoring</label> <?php print($thisItem['league_type']); ?>
            <p /><br />
           	<label>Commissioner</label> <?php print(anchor('/user/profile/id/'.$thisItem['commissioner_id'],$thisItem['commissioner']).'('.anchor('/league/teamAdmin/'.$league_id,'Change').')'); ?>
            <p /><br />
            <label>Accept Team Requests</label> <?php print((($thisItem['accept_requests'] == 1) ? 'Yes' : 'no').' ('.anchor('league/submit/mode/edit/id/'.$league_id,'Change').')'); ?>
            <p /><br />
            
            <?php 
			if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { ?>
            <label>Playoff Begin in week</label> <?php print(($thisItem['regular_scoring_periods']+1)); ?>
            <p /><br />
            <label>Games Per Team</label> <?php print($thisItem['games_per_team']); ?>
            <p /><br />
            <label>Playoff Rounds</label> <?php print($thisItem['playoff_rounds']); ?>
            <p /><br />
            
            <?php } ?>
            <p /><br />
            
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />