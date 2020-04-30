    <div id="secondary">
        <?php
        if ($accessLevel >= ACCESS_WRITE) { ?>
        <section id="site-tools">
            <div class="widget widget_header">
                News Tools
            </div>
            <div class="widget widget_text">
                <img src="<?php echo(PATH_IMAGES.'icons/add.png'); ?>" width="48" height="48" alt="Add" title="Add" align="absmiddle" /> 
                <?php echo(anchor('/news/submit/mode/add/type_id/'.$type_id,'Add a New Article',['style'=>'font-weight:bold;'])); ?>
            </div>
            <?php
            if (isset($article_id) && ($accessLevel == ACCESS_ADMINISTRATE || ((isset($article['author_id']) && !empty($article['author_id'])) && $currUser == $article['author_id']))) { ?>
            <div class="widget widget_text">
                <img src="<?php echo(PATH_IMAGES.'icons/notes_edit.png'); ?>" width="48" height="48" alt="Add" title="Add" align="absmiddle" /> 
                <?php echo(anchor('/news/submit/mode/edit/id/'.$article_id,'Edit this Article',['style'=>'font-weight:bold;'])); ?>
            </div>
            <?php } ?>
        </section>
        <?php
        }
        ?>

        <?php
        if (isset($news_types) && count($news_types) > 0) { ?>
        <section id="recent-posts" class="widget widget_recent_entries"> 
            <h2 class="widget-title">Categories</h2>		
            <ul>
                <?php
                foreach($news_types as $news_type_id => $news_type_name) { 
                    if (isset($news_type_id) && !empty($news_type_id) && $news_type_id != -1 && $type_id != $news_type_id) { ?>
                <li class="cat-item cat-item-6"><?php echo(anchor('/news/articles/type_id/'.$news_type_id, $news_type_name)); ?></li>
                <?php 
                    } // END if
                } // END foreach
                ?>
            </ul>
        </section>
        <?php
        }   // END if
        ?>
    </div>
    <?php
    if ((isset($extra_data['leagueDetails']) && count($extra_data['leagueDetails']) > 0) ||
       (isset($extra_data['playerDetails']) && count($extra_data['playerDetails']) > 0) ||
       (isset($extra_data['teamDetails']) && count($extra_data['teamDetails']) > 0)) {  ?>

    <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0">
            <thead>	
                <tr class="title">
                    <td class='hsc2_l'><?php echo($extra_data['typeTitle']); ?> Details</td>
                </tr>
                <tr class="headline">
                    <td class='hsc2_l'>
                    <?php
                    $boxTitle="";
                    switch($type_id) {
                        case 2:
                            $boxTitle=$extra_data['leagueDetails']['league_name'];
                            break;
                        case 3:
                            if ($extra_data['playerDetails']['position'] != 1) {
                                $boxTitle = get_pos($extra_data['playerDetails']['position']);
                            } else {
                                $boxTitle = get_pos($extra_data['playerDetails']['role']);
                            }
                            $boxTitle .= " ".$extra_data['playerDetails']['first_name']." ".$extra_data['playerDetails']['last_name'];
                            break;
                        case 4:
                            $boxTitle=$extra_data['teamDetails']['teamname']." ".$extra_data['teamDetails']['teamnick'];
                            break;
                    } // END SWITCH
                    echo($boxTitle);
                    ?>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 0 10px;">
                    <?php
                    if ($type_id == 2) { ?>
                        <div class="league_info">
                            <div class="league_logo" style="float:left;width:80px;">
                            <?php
                            if (isset($extra_data['leagueDetails']['avatar']) && !empty($extra_data['leagueDetails']['avatar'])) { 
                                $avatar = PATH_LEAGUES_AVATARS.$extra_data['leagueDetails']['avatar']; 
                            } else {
                                $avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
                            } 
                            ?>
                            <img src="<?php echo($avatar); ?>" width="80" height="80" alt="<?php echo($extra_data['leagueDetails']['league_name']); ?>" 
                            title="<?php echo($extra_data['leagueDetails']['league_name']); ?>" /> 
                            </div>
                            <div class="league" style="float:left; width: 65%;padding: 0 8px;">
                            <strong>Type:</strong> <?php echo($extra_data['leagueDetails']['leagueType']); ?> League<br />
                            <strong>Status:</strong> <?php 
                            $color = "#000";
                            switch($extra_data['leagueDetails']['league_status']) {
                                case 1:
                                    $color = "#080";
                                    break;
                                case -1:
                                    $color = "#A00";
                                    break;
                            }
                            echo('<span style="font-weight:bold; color:'.$color.'">'.$extra_data['leagueDetails']['leagueStatus'].'</span>'); ?><br />
                            <strong>Teams:</strong> <?php echo($extra_data['leagueDetails']['max_teams']); ?><br />
                            <strong>Commissioner:</strong> <?php if((isset($extra_data['leagueDetails']['commissioner_id']) && $extra_data['leagueDetails']['commissioner_id'] != -1) && isset($extra_data['leagueDetails']['commissioner'])) {echo(anchor('/user/profiles/'.$extra_data['leagueDetails']['commissioner_id'],$extra_data['leagueDetails']['commissioner'],['class' => 'teamLink-link'])); } 
                                else {
                                    echo('No Commissioner');
                                }?>
                            <br />
                            <?php echo(anchor('/league/home/'.$extra_data['leagueDetails']['id'], $extra_data['leagueDetails']['league_name'].' Homepage',['style'=>'font-weight:bold;'])); ?>
                            </div>
                        </div>
                    <?php
                    } else if ($type_id == 3) { ?>
                        <div class="player_info">
                            <div class="playerpic" style="float:left;width:80px;">
                            <?php
                            $htmlpath=$config['ootp_html_report_path'];
                            $filepath=$config['ootp_html_report_root'];
                            $imgpath=$filepath.URL_PATH_SEPERATOR."images".URL_PATH_SEPERATOR."person_pictures".URL_PATH_SEPERATOR."player_".$extra_data['playerDetails']['player_id'].".png";
                            ## Check for photo by player ID
                            if (file_exists($imgpath)) {echo "<img src='".$htmlpath."images/person_pictures/player_".$extra_data['playerDetails']['player_id'].".png' width='80'>";}
                            else {
                                echo "<img src='".$htmlpath."images/person_pictures/default_player_photo.png'>";   ## Show default
                            }
                            ?>
                            </div>
                            <div class="player" style="float:left; width: 65%;padding: 0 8px;">
                            <strong>Team:</strong> <a href="<?php echo($htmlpath); ?>teams/team_<?php echo($extra_data['playerDetails']['team_id']); ?>.html" target="_blank"><?php echo(" ".$extra_data['playerDetails']['team_name']." ".$extra_data['playerDetails']['teamNickname']); ?></a><br />
                            <?php if (isset($extra_data['playerDetails']['playerNickname']) && !empty($extra_data['playerDetails']['playerNickname'])) { ?>
                                <strong>Nickname:</strong> <?php echo($extra_data['playerDetails']['playerNickname'].'<br />'); } ?>
                            
                            <strong>Height/Weight:</strong> <?php echo(cm_to_ft_in($extra_data['playerDetails']['height'])); ?>/<?php echo($extra_data['playerDetails']['weight']); ?> lbs<br />
                            <strong>Bats/Throws:</strong> <?php echo(get_hand($extra_data['playerDetails']['bats'])); ?>/<?php echo(get_hand($extra_data['playerDetails']['throws'])); ?><br />
                            <strong>Age:</strong> <?php echo($extra_data['playerDetails']['age']); ?><br />
                            <strong>Birthdate:</strong> <?php echo(date("F j, Y",strtotime($extra_data['playerDetails']['date_of_birth']))); ?><br />
                            <strong>Birthplace:</strong> <?php echo($extra_data['playerDetails']['birthCity'].", ".$extra_data['playerDetails']['birthRegion']." ".$extra_data['playerDetails']['birthNation']); ?><br />
                            <strong> Drafted:</strong> <?php
                            if ($extra_data['playerDetails']['draft_pick'] != 0) {
                                echo ordinal_suffix($extra_data['playerDetails']['draft_pick'],1)." pick in the ".ordinal_suffix($extra_data['playerDetails']['draft_round'],1)." round of ";
                                if ($extra_data['playerDetails']['draft_year']==0) {echo "inaugural";} else {echo $extra_data['playerDetails']['draft_year'];}
                            }
                            ?>
                            <br /><br />
                            <?php echo(anchor('/players/info/'.$extra_data['playerDetails']['id'],'Fantasy Player Page',['style'=>'font-weight:bold'])); ?><br />
                            <b><a href="<?php echo($config['ootp_html_report_path']); ?>players/player_<?php echo($extra_data['playerDetails']['player_id']); ?>.html">OOTP Player Page</a></b>
                        </div>
                    <?php
                    } else if ($type_id == 4) { ?>
                        <div class="team_info">
                            <div class="team_logo" style="float:left;width:80px;">
                                <?php
                                if (isset($extra_data['teamDetails']['avatar']) && !empty($extra_data['teamDetails']['avatar'])) { 
                                    $avatar = PATH_TEAMS_AVATARS.$extra_data['teamDetails']['avatar']; 
                                } else {
                                    $avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
                                } 
                                $teamName = $extra_data['teamDetails']['teamname']." ".$extra_data['teamDetails']['teamnick'];
                                ?>
                                <img src="<?php echo($avatar); ?>" width="80" height="80" alt="<?php echo($teamName); ?>" 
                                title="<?php echo($teamName); ?>" />
                            </div>
                            <div class="team_block" style="float:left; width: 65%;padding: 0 8px;">
                                
                                <?php
                                if (isset($extra_data['teamDetails']['division_name']) && !empty($extra_data['teamDetails']['division_name'])) {
                                ?>
                                <strong>Division:</strong> <?php echo($extra_data['teamDetails']['division_name']); ?><br />
                                <?php
                                }
                                ?>

                                <?php if(isset($extra_data['teamDetails']['owner_id']) && $extra_data['teamDetails']['owner_id'] != -1) { ?>
                                    <strong>Team Owner:</strong> 
                                    <?php
                                    echo(anchor('/user/profiles/'.$extra_data['teamDetails']['owner_id'],$extra_data['teamDetails']['owner'],['class' => 'teamLink-link']));
                                    } else {
                                        echo('Team is unowned');
                                    }
                                ?>
                                <br />
                                <?php echo(anchor('/team/info/'.$extra_data['teamDetails']['id'], $teamName.' Homepage',['style'=>'font-weight:bold;'])); ?>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
    } // END if details set
    ?>