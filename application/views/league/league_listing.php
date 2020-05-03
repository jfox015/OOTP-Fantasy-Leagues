<script type="text/javascript" charset="UTF-8">
$(document).ready(function(){	
    $('a[rel=delete]').live('click',function (e) {
        e.preventDefault();
        if (confirm("Are you sure you want to delete this league? It will delete the league entry and ALL supporting data including teams, transactions rosters, etc.\n\n ARE YOU 100% SUREYOU WANT TO DELETE THIS LEAGUE?")) {
            document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/submit/mode/delete/id/'+this.id;
        }
    });
    $('button[rel=create]').live('click',function (e) {
        e.preventDefault();
        document.location.href = '<?php echo($config['fantasy_web_root']); ?>/user/createLeague';
    });
});
</script>
<div id="single-column">
    <h1><?php echo($subTitle); ?></h1>
    <?php print($league_list_intro_str); ?>
    <br/>
	<div id="content">
        <?php if ($loggedIn) {
            if ($curr_period_id <= 1) { ?>
            <div class="top-bar leagues">
                <button class="sitebtn create_league" rel="create">Create a New League</button>
            </div>
            <?php 
            } else { ?>
                <br /><span class="message"><b>Interested in Starting a League?</b> The <?php echo($leagueName); ?> has begun it's <?php echo($current_year); ?> season. New Leagues can be added once this season has finished.</span>
            <?php
            }
        }
        /*-------------------------------------------------
        /
        // LEAGUE LISTING
        /
        /------------------------------------------------*/
        if (isset($league_list) && sizeof($league_list) > 0) {
            $rowcount = 0;
            foreach ($league_list as $leagueData) {
                /*foreach($leagueData as $key => $val) {
                    echo($key." = ".$val."<br />");
                }*/
                if ($rowcount > 0) {
                    echo('<div class="rule"></div>');
                }
            ?>
        <div class="layout leagues">
            <div class="layout-column">
                <section class="content">
                <?php
                    // COLUMN 1, drawn LEAGUE AVATAR, NAME and COMMISSIONER INFO
                    if (isset($leagueData['avatar']) && !empty($leagueData['avatar'])) { 
                        $avatar = PATH_LEAGUES_AVATARS.$leagueData['avatar']; 
                    } else {
                        $avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
                    } ?>
                    <section class="teamLinks flex">
                        <figure class="logo-exlg">
                            <img src="<?php echo($avatar); ?>" alt="<?php echo($leagueData['league_name']); ?>" title="<?php echo($leagueData['league_name']); ?>" />
                        </figure>
                        <div class="flexContent">
                            <?php 
                            if ($leagueData['access_type'] == 1) {
                                echo(anchor('/league/home/'.$leagueData['league_id'], $leagueData['league_name'],['class' => 'leagueLink'])); 
                            } else {
                                echo('<h2>'.$leagueData['league_name'].'</h2>');
                            } // END if
                            ?>
                            <div class="TeamLinks-Links">
                                <?php 
                                if(isset($leagueData['description']) && !empty($leagueData['description'])) {
                                    echo('<br />'.$leagueData['description']); ?><br />
                                <?php
                                } 
                                ?>
                            </div>
                        </div>
                    </section>
                </section>
            </div>
            <div class="layout-column">
                <section class="content">
                    <section class="teamLinks flex">
                        <div class="flexContent">
                            <strong>Type:</strong> <?php echo($leagueData['league_type_lbl']); ?> League<br />
                            <strong>Status:</strong> <?php 
                            switch($leagueData['league_status']) {
                                case 1:
                                    $color = "#080";
                                    break;
                                default:
                                    $color = "#A00";
                                    break;
                            }
                            echo('<span style="font-weight:bold; color:'.$color.'">'.$leagueData['league_status_lbl'].'</span>'); ?><br />
                            <strong>Teams:</strong> <?php echo($leagueData['max_teams']); ?><br />
                            <strong>Commissioner:</strong> <?php if((isset($leagueData['commissioner_id']) && $leagueData['commissioner_id'] != -1) && isset($leagueData['commissioner'])) {echo(anchor('/user/profiles/'.$leagueData['commissioner_id'],$leagueData['commissioner'],['class' => 'teamLink-link'])); } 
                                else {
                                    echo('No Commissioner');
                                }?>
                            <br />
                            <?php
                            if ($loggedIn) {
                                if ($leagueData['teamsOwned'] > 0) {
                                    echo('<span style="color:#080;">You own a team in this League!</span>');
                                } else {
                                    if (sizeof($leagueData['pendingRequests'][0]) > 0) {
                                        $requests = $leagueData['pendingRequests'][0];
                                        echo("You requested to own the <b>".$requests['team']."</b> on ".$requests['date_requested'].". Your request is Pending approval."); 
                                    } else {
                                        if ($leagueData['openCount'] > 0) {
                                            echo("This League currently has <b>".$leagueData['openCount']."</b> unowned teams!"); 
                                            if ($leagueData['accept_requests']) { ?>
                                                <div class="TeamLinks-Links">
                                                <?php
                                                echo(anchor('/league/requestTeam/'.$leagueData['league_id'],'<img src="'.PATH_IMAGES.'icons/note_edit.png" width="16" height="16" alt="Edit" title="Edit" /> Request a Team'));
                                                ?>
                                                </div>
                                            <?php
                                            } else {
                                                echo('<span style="color:#A00;">Not currently accepting team requests.</span>');
                                            } // END if
                                        } else {
                                            echo('<span style="color:#A00;">This Leagues does not have any teams available.</span>');
                                        } // END if
                                    }
                                } // END if
                            } // END if
                            ?>
                            <?php if ($accessLevel == ACCESS_ADMINISTRATE) { 
                            echo('<br/>');
                            echo( anchor('/league/submit/mode/edit/id/'.$leagueData['league_id'],'<img src="'.PATH_IMAGES.'icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" /> Edit League'));
                            echo('&nbsp;');
                            echo( anchor('#','<img src="'.PATH_IMAGES.'icons/hr.gif" width="16" height="16" alt="Delete" title="Delete" /> Delete League',array('id'=>$leagueData['league_id'],'rel'=>'delete'))); ?></td>
                            <?php 
                            }  // END if 
                            ?>
                        </div>
                    </section>
                </dsectioniv>
            </div>
        </div>
            <?php
                $rowcount++;
            } // END foreach
        } else { ?>
            <br />
            <div class='textbox right-column'>
            <table cellspacing="0" cellpadding="3">
            <tr class='title'><td>No Leagues have been created yet.</td></tr>
            <tr class='headline'>
                <td><span class="message" style="text-align:center;"><b>Interested in Starting a League?</b></span></td>
            </tr>
            <tr class='headline'>
                <td><span class="message" style="text-align:center;"><?php if ($loggedIn) {
                    echo(anchor('/league/submit/mode/add','Create a New League today!'));
                } else {
                    echo(anchor('/user/register','Signup')." or ".anchor('/user/login','Login')." to create a new League today.");
                 } ?>
                </span></td>
            </tr>
            </table>
            </div>
        <?php
        } // END if (isset($league_list) && sizeof($league_list) > 0)
        ?>
	</div>
</div>