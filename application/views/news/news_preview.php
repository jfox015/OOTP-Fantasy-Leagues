            <h3>Preview</h3>
            <div id="previewDiv_news" style="margin-left:65px; border:1px solid #D0D0D0; background-color:#FFC; display:block; float:left; clear:both; width:725px;">
                <div class="top-bar"><h1><?php echo($thisItem['news_subject']); ?></h1></div>
                
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
                $size = getimagesize(PATH_NEWS_IMAGES_PREV_WRITE.$thisItem['image']);
                if (isset($size) && sizeof($size) > 0) {
                    if ($size[0] > $size[1]) {
                        $class = "wide";
                    } else {
                        $class = "tall";
                    } // END if
                } // END if
                ?>
                <img src="<?php echo(PATH_NEWS_IMAGES_PREV.$thisItem['image']); ?>" align="left" border="0" class="league_news_<?php echo($class); ?>" />
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
            <br clear="all" /><br />