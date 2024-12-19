    <link media="screen" rel="stylesheet" href="<?php echo($config['fantasy_web_root']); ?>css/colorbox.css" />
	<script src="<?php echo($config['fantasy_web_root']); ?>js/jquery.colorbox.js"></script>
	<script type="text/javascript" charset="UTF-8">
	var charLimit = 1000;
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
        $('a[rel=withdrawInvite]').click(function (e) {					   
            e.preventDefault();
            openDialog(e, 'retractDialog', this.id);
        });
		 
	});
	function init() {
		/* Character Counter for inputs and text areas 
		 */  
		$('.word_count').each(function(){  
		    // get current number of characters  
		    var length = $(this).val().length;  
		    // get current number of words  
		    //var length = $(this).val().split(/\b[\s,\.-:;]*/).length;  
		    // update characters  
		    $(this).parent().find('.counter').html('<span style="color:#'+getColor(length)+';">' + length + ' characters</span>');  
		    // bind on key up event  
		    $(this).keyup(function(){  
				 // get new length of characters  
		        var new_length = $(this).val().length;  
		        // get new length of words  
		        //var new_length = $(this).val().split(/\b[\s,\.-:;]*/).length;  
		        // update  
		        $(this).parent().find('.counter').html('<span style="color:#'+getColor(new_length)+';">' + new_length + ' characters</span>');  
		    });  
		}); 
		$('#btnCancel').click(function (e) {					   
			e.preventDefault();
			$.colorbox.close();
		});
		 $('#btnSendWithdrawl').click(function (e) {					   
			e.preventDefault();
			if ($('#reqMessage').val() != '') {
				$('#withdrawlForm').submit();
			 } else {
				alert("A message to the user is required to withdraw the request.");
				$('#reqMessage').focus();
			 }
		});
	}
	function getColor(new_length) {
		var color = "060";
	    var critLen = (charLimit - parseInt(charLimit * .05)),
        warnLen = (charLimit - parseInt(charLimit * .15));
        if (new_length >= warnLen && new_length < critLen) {
			color = "f60";
        } else if (new_length >= critLen) {
	        color = "c00";
        }
        return color;
	}
	function openDialog(e,id, params) {
        var paramList = params.split("|");
        $('#'+id + ' input#request_id').val(paramList[0]);
        $('#'+id + ' input#league_id').val(paramList[1]);
		
		$.colorbox({html:$('div#'+id).html()});
		init();
	}
</script>

<div id="retractDialog" class="dialog" style="position:absolute;visibility:hidden;top:-5000px;left:-5000px">
    <form method='post' action="<?php echo($config['fantasy_web_root']); ?>league/withdrawRequest" name='withdrawlForm' id="withdrawlForm">
    <input type='hidden' id="submitted" name='submitted' value='1'></input>
    <input type='hidden' id="request_id" name='request_id' value='-1'></input>
    <input type='hidden' id="type_id" name='type_id' value='2'></input>
    <input type='hidden' id="league_id" name='league_id' value='-1'></input>
    <div class='textbox'>
        <table cellpadding="2" cellspacing="0" cellborder="0">
        <tr class='title'><td>Message to Commissioner</td></tr>
        <tr>
        <tr class='highlight'>
        <td>Provide a message to the Commissioner why you're withdrawing your request.
        </td>
        </tr>
        <tr>
        <td>
        <?php 
        $data = array(
            'name'        => 'reqMessage',
            'id'          => 'reqMessage',
            'value'       => '',
            'maxlength'   => '1000',
            'class'		=> 'word_count',
            'rows'        => '5',
            'cols'		=> '45'
        );
        echo(form_textarea($data));
        ?><br clear="all" />
        <span class="counter"></span>, Limit 1000.
        </td>
        </tr>
        <tr>
        <td>
        <input type='button' id="btnCancel" class="button" value='Cancel' />
        <input type='button' class="button" id="btnSendWithdrawl" value='Withdraw Request' /></td>
        </tr>
        </table>
    </div>
    </form>
</div>

<?php
    if (isset($fantasyStatusID) && $fantasyStatusID == 3) { ?>
        <div id="single-column"><div class="seasonOver_banner show"><h2><?php echo($playoffs['league_year']." ".$playoffs['league_name']." ".$this->lang->line('season_over_title')); ?></h2></div></div>
    <?php
    }
?>

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
                                        echo("You requested to own the <b>".$requests['team']."</b> on ".$requests['date_requested'].". Your request is Pending approval.<br />");
                                        print(anchor('#','Withdraw Request',array('id'=>$requests['id']."|".$leagueData['league_id'],'rel'=>'withdrawInvite')));  
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