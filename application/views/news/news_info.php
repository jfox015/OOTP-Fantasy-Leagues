
   	<div id="subPage">
       	<div class="top-bar"><h1><?php echo($thisItem['news_subject']); ?></h1></div>
       	<div id="content">
            <div id="metaColumn">
            	<?php 
				if ($loggedIn) { ?>
                <div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="325px">
                <tr class='title'>
                    <td style='padding:3px'>News Tools</td>
                </tr>
                <tr>
                    <td style='padding:12px'>
                    <div id="row">
                    <img src="<?php echo PATH_IMAGES; ?>icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
					<?php echo( anchor('/news/submit/add','Add new news article')); ?>
                    </div>
					<?php if ($accessLevel == ACCESS_ADMINISTRATE) { ?>
                    <div id="row">
                    <img src="<?php echo PATH_IMAGES; ?>icons/edit-icon.gif" width="16" height="16" border="0" alt="Edit" title="Edit" align="absmiddle" /> 
					<b><?php echo(anchor('/news/submit/mode/edit/id/'.$thisItem['id'],'Edit this article')); ?></b>
                    </div>
                    <div id="row">
                    <img src="<?php echo PATH_IMAGES; ?>icons/hr.gif" width="16" height="16" border="0" alt="Delete" title="Delete" align="absmiddle" /> 
					<b><?php echo(anchor('/news/submit/mode/delete/id/'.$thisItem['id'],'Delete this article')); ?></b>
                    </div>
                    <?php } ?>
                    </td>
                </tr>
                </table>
                </div>
                <?php } 
				//------------------------------------------------
				// UPDATE 1.0.2
				//------------------------------------------------
				// SOCIAL MEDAI BOX AND OPTION				
				if (isset($config['sharing_enabled']) && $config['sharing_enabled'] == 1) { ?>
			    <div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="325px">
                <tr class='title'>
                    <td style='padding:3px'>Share this story</td>
                </tr>
                
                <tr>
                	<td style='padding:12px'>
                    <?php 
					$buttonsDrawn = false;
					if (isset($config['share_facebook']) && $config['share_facebook'] == 1) { ?>
					<!-- FACEBOOK BUTTON -->
                    <iframe src="http://www.facebook.com/plugins/like.php?href=%3C%3Fphp+echo%28%24_SERVER%5B%27PHP_SELF%27%5D%29%3B+%3F%3E&amp;layout=box_count&amp;show_faces=true&amp;width=50&amp;action=like&amp;font=verdana&amp;colorscheme=light&amp;height=60" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:58px; height:65px;" allowTransparency="true" style="margin: 10px 0 0 0"></iframe>
					<?php 
						if (!$buttonsDrawn) { $buttonsDrawn = true; }
					} ?>
                    <?php if (isset($config['share_twitter']) && $config['share_twitter'] == 1) { ?>
					<!-- TWITTER BUTTON -->
					<script type="text/javascript">
					tweetmeme_service = 'bit.ly';
					</script>
					<script type="text/javascript" src="http://tweetmeme.com/i/scripts/button.js"></script>
                    <?php 	if (!$buttonsDrawn) { $buttonsDrawn = true; }
					} ?>
                    <?php if (isset($config['share_digg']) && $config['share_digg'] == 1) { ?>
					<!-- DIGG BUTTON -->
					<script type="text/javascript">
					(function() {
					var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
					s.type = 'text/javascript';
					s.async = true;
					s.src = 'http://widgets.digg.com/buttons.js';
					s1.parentNode.insertBefore(s, s1);
					})();
					</script>
					<!-- Medium Button -->
					<a class="DiggThisButton DiggMedium"></a>
                    <?php if (!$buttonsDrawn) { $buttonsDrawn = true; }
					} ?>
                    <?php if (isset($config['share_stumble']) && $config['share_stumble'] == 1) { ?>
					<!-- STUMBLE BUTTON -->
                    <script src="http://www.stumbleupon.com/hostedbadge.php?s=5"></script>
                    <?php 	if (!$buttonsDrawn) { $buttonsDrawn = true; }
					} ?>
                    <?php if ($buttonsDrawn) { ?><br clear="all" /><br /><?php } ?>
					<?php if (isset($config['share_addtoany']) && $config['share_addtoany'] == 1) { ?>
                    <!-- AddToAny BEGIN -->
                    <div class="a2a_kit a2a_default_style">
                    <a class="a2a_dd" href="http://www.addtoany.com/share_save">Share</a>
                    <span class="a2a_divider"></span>
                    <a class="a2a_button_facebook"></a>
                    <a class="a2a_button_twitter"></a>
                    <a class="a2a_button_email"></a>
                    </div>
                    <script type="text/javascript">
                    var a2a_config = a2a_config || {};
                    a2a_config.onclick = 1;
                    </script>
                    <script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
                    <!-- AddToAny END -->
                    <?php } ?>
                    </td>
                </tr></table>
                </div>
                <?php } ?>
                
                <div class='textbox'>
                <table cellpadding="0" cellspacing="0" border="0" width="325px">
                <tr class='title'>
                    <td style='padding:3px'>Related News</td>
                </tr>
                <tr>
                    <td style='padding:12px'>
                    <?php if (isset($thisItem['related']) && sizeof($thisItem['related']) > 0) {
						foreach($thisItem['related'] as $article) { ?>
                        <div id="row">
                       <?php echo(anchor('/news/info/'.$article['id'],$article['news_subject'])); ?>
                        </div>
						<?	
						}
					} else { ?>
                    <div id="row">
                    No related news articles were found.
                    </div>
                    <?php } ?>
                    <br /><br />
                    <?php echo(anchor('search/doSearch/news/','All News',array('style'=>'font-weight:bold;'))); ?>
                    </td>
                </tr>
                </table>
                </div>
            </div>
           
            <div id="detailColumn">
            
                <b>Date:</b> &nbsp;<?php echo(date('m/d/Y',strtotime($thisItem['news_date']))); ?>
                <br /><br />
				<?php
				if (!empty($thisItem['author'])) { ?>
                <b>Author:</b>&nbsp;
                <?php echo anchor('/user/profile/'.$thisItem['author_id'], $thisItem['author']); ?>
                <br /><br />
                <?php 
                } // END if
				?>
                <?php if (isset($thisItem['image']) && !empty($thisItem['image'])) { 
                // GET IMAGE DIMENSIONS
                $size = getimagesize(DIR_WRITE_PATH.'images/news/'.$thisItem['image']);
                if (isset($size) && sizeof($size) > 0) {
                    if ($size[0] > $size[1]) {
                        $class = "wide";
                    } else {
                        $class = "tall";
                    } // END if
                } // END if
                ?>
                <img src="<?php echo(PATH_NEWS_IMAGES.$thisItem['image']); ?>" align="left" border="0" class="league_news_<?php echo($class); ?>" />
                <?php } ?>
                <?php 
                if (!empty($thisItem['news_body'])) { ?>
                <?php echo($thisItem['news_body']); ?>
                <br /><br />
                <?php 
                } // END if
				if ($thisItem['type_id'] == NEWS_PLAYER && !empty($thisItem['var_id'])) {
					echo( anchor('/players/info/'.$thisItem['var_id'],'View Players Page<br />'));
				}
                ?>
            </div>

        </div>
	</div>
