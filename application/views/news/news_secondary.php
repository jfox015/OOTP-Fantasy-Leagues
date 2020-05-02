<?php
$typeIdStr = "";
$varIdStr = "";
if (isset($type_id) && !empty($type_id) && $type_id != -1) {
    $typeIdStr = "/type_id/".$type_id;
}
if (isset($var_id) && !empty($var_id) && $var_id !== false) {
    $varIdStr = "/var_id/".$var_id;
}
?>
    <?php
    /*----------------------------------------------------
    /   EXTRA INFORMATION BOXES
    /   Displays infromation about a specific League, 
    /   Team or Player referenced by an article
    /---------------------------------------------------*/
    if ((isset($extra_data['leagueDetails']) && count($extra_data['leagueDetails']) > 0) ||
       (isset($extra_data['playerDetails']) && count($extra_data['playerDetails']) > 0) ||
       (isset($extra_data['teamDetails']) && count($extra_data['teamDetails']) > 0)) {  ?>

    <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0">
            <thead>	
                <tr class="title">
                    <td class='hsc2_l' id="details_title"><?php echo($extra_data['typeTitle']); ?> Details</td>
                </tr>
                <tr class="headline">
                    <td class='hsc2_l' id="details_headline">
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
                            <?php 
                            if (isset($article_id)) {
                                echo(anchor('/news/articles'.$typeIdStr.$varIdStr,' More '.$extra_data['leagueDetails']['league_name'].' News',['style'=>'font-weight:bold;']).'<br />');
                            }
                            echo(anchor('/league/home/'.$extra_data['leagueDetails']['id'], $extra_data['leagueDetails']['league_name'].' Homepage',['style'=>'font-weight:bold;'])); ?>
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
                            echo('<br /><br />');
                            if (isset($article_id)) {
                                echo(anchor('/news/articles'.$typeIdStr.$varIdStr,' More '.$extra_data['playerDetails']['first_name']." ".$extra_data['playerDetails']['last_name'].' News',['style'=>'font-weight:bold;']).'<br />');
                            }
                            echo(anchor('/players/info/'.$extra_data['playerDetails']['id'],'Fantasy Player Page',['style'=>'font-weight:bold'])); ?><br />
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
                                <?php 
                                if (isset($article_id)) {
                                    echo(anchor('/news/articles'.$typeIdStr.$varIdStr,' More '.$teamName.' News',['style'=>'font-weight:bold;']).'<br />');
                                }
                                echo(anchor('/team/info/'.$extra_data['teamDetails']['id'], $teamName.' Homepage',['style'=>'font-weight:bold;'])); ?>
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
    <div class="clear"></div>
    <?php
    } // END if details set

    /*-------------------------------------------------
    /   TOOLS BOX
    /------------------------------------------------*/
    if (($accessLevel >= ACCESS_WRITE && !isset($mode)) ||
    (isset($article_id) && ($accessLevel == ACCESS_ADMINISTRATE || ((isset($article['author_id']) && !empty($article['author_id'])) && $currUser == $article['author_id'])))) { ?>
    <div id="secondary">
        <section id="site-tools">
            <div class="widget widget_header">
                News Tools
            </div>
            <?php if ($accessLevel >= ACCESS_WRITE && !isset($mode)) { ?>
            <div class="widget widget_text">
                <img src="<?php echo(PATH_IMAGES.'icons/add.png'); ?>" width="48" height="48" alt="Add" title="Add" align="absmiddle" /> 
                <?php echo(anchor('/news/submit/mode/add'.$typeIdStr.$varIdStr,'Add a New Article',['style'=>'font-weight:bold;'])); ?>
            </div>
            <?php
            } // END if ($accessLevel >= ACCESS_WRITE && !isset($mode))
            if (isset($article_id) && ($accessLevel == ACCESS_ADMINISTRATE || ((isset($article['author_id']) && !empty($article['author_id'])) && $currUser == $article['author_id']))) { ?>
            <div class="widget widget_text">
                <img src="<?php echo(PATH_IMAGES.'icons/notes_edit.png'); ?>" width="48" height="48" alt="Add" title="Add" align="absmiddle" /> 
                <?php echo(anchor('/news/submit/mode/edit/id/'.$article_id,'Edit this Article',['style'=>'font-weight:bold;'])); ?><br />
            </div>
            <div class="widget widget_text">
                <img src="<?php echo(PATH_IMAGES.'icons/database_remove.png'); ?>" width="48" height="48" alt="Add" title="Add" align="absmiddle" /> 
                <?php echo(anchor('/news/submit/mode/delete/id/'.$article_id,'Delete this Article',['style'=>'font-weight:bold;'])); ?>
            </div>
            <?php 
            } // END if (isset($article_id) &&
            ?>
        </section>
    </div>
    <?php
    } // END if ($accessLevel >= ACCESS_WRITE && !isset($mode))
    // END TOOLBOX
        //------------------------------------------------
        // SOCIAL MEDIA BOX AND OPTION
        //------------------------------------------------			
        if (isset($article_id) && isset($config['sharing_enabled']) && $config['sharing_enabled'] == 1) { ?>
        <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0">
        <tr class='title'>
            <td style='padding:3px'>Share this story</td>
        </tr>
        <tr>
            <td style='padding:12px'>
            <?php 
            $buttonsDrawn = false;
            if (isset($config['share_addtoany']) && $config['share_addtoany'] == 1) { ?>
            <!-- AddToAny BEGIN -->
            <!-- AddToAny BEGIN -->
            <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
            <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
            <a class="a2a_button_facebook"></a>
            <a class="a2a_button_twitter"></a>
            <a class="a2a_button_email"></a>
            <a class="a2a_button_reddit"></a>
            </div>
            <script>
            var a2a_config = a2a_config || {};
            a2a_config.onclick = 1;
            </script>
            <script async src="https://static.addtoany.com/menu/page.js"></script>
            <!-- AddToAny END -->
            <!-- AddToAny END -->
            <?php } ?>
            </td>
        </tr></table>
        </div>
        <div class="clear"></div>
        <?php 
        } // END if (isset($article_id)
        // END SOCIAL MEDIA BOX
    
    /*----------------------------------------------------
    /   NEWS CATEGORIES
    /---------------------------------------------------*/
    if (isset($news_types) && count($news_types) > 0) { ?>
    <div id="secondary">
        <section id="recent-posts" class="widget widget_recent_entries"> 
            <h2 class="widget-title">Categories</h2>		
            <ul>
                <?php
                foreach($news_types as $news_type_id => $news_type_name) { 
                    if (isset($news_type_id) && !empty($news_type_id) && $news_type_id != -1) {
                ?>
                <li class="cat-item cat-item-6"><?php echo(anchor('/news/articles/type_id/'.$news_type_id, $news_type_name)); ?></li>
                <?php 
                    } // END if
                } // END foreach
                ?>
            </ul>
        </section>
       
    </div>
    <?php
    }   // END if (isset($news_types
    ?>