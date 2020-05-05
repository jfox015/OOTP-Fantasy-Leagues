    <script type="text/javascript">
        $(document).ready(function(){		   
            $('#edit').click(function() {
                $('#confirmForm').submit();
            });
            $('#acceptBtn').click(function() {
                console.log("Accept button clocked");
                $('#confirmForm').append('<input type="hidden" id="submitted" name="submitted" value="1" />');
                $('#confirmForm').append('<input type="hidden" id="showPreview" name="showPreview" value="-1" />');
                $('#confirmForm').append('<input type="hidden" id="validForm" name="validForm" value="1" />');
                $('#confirmForm').submit();
            });
        });
    </script>
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
    <div id="left-column-article-dbl" class="content-area">
        <div id="primary" class="content-area single-column right" 
        style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
            <!-- #main -->
            <main id="main" class="site-main" role="main">
                <article id="post-20" 
                class="single post-20 post type-post status-publish format-standard has-post-thumbnail hentry category-featured category-travel">
                    <header class="entry-header">
                        <h2 class="entry-title"><?php echo($article['news_subject']); ?></h2>    
                        <div class="entry-meta">
                            <div class="date"><?php echo(date('F j, Y',strtotime($article['news_date']))); ?></div>
                            <span class="cat-links">
                                <?php echo(anchor('/user/profiles/'.$article['author_id'], $article['authorName'],['class'=>'cat_link'])); ?></span>
                            </span> 
                        </div>
                    </header><!-- .entry-header -->

                <div class="clear"></div>

                <div class="entry-content">
                    <?php 
                    $artImage = "";
                     if (isset($article['uploadedImage']) && !empty($article['uploadedImage']) && file_exists(PATH_NEWS_IMAGES_PREV_WRITE.$article['uploadedImage'])) {
                        $artImage = PATH_NEWS_IMAGES_PREV.$article['uploadedImage'];
                    } else if (isset($article['prevImage']) && !empty($article['prevImage']) && file_exists(PATH_NEWS_IMAGES_WRITE.$article['prevImage'])) {
                        $artImage = PATH_NEWS_IMAGES.$article['prevImage'];
                    }

                    if (!empty($artImage)) echo('<p><img src="'.$artImage.'" class="alignnone wp-image-21"></p>');
                    ?>
                    <p><?php echo($article['news_body']); ?></p>
                    <?php
                    if (isset($article['fantasy_analysis']) && !empty($article['fantasy_analysis'])) {
                        echo('<h3>Fantasy Analysis</h3>');
                        echo('<p>'.$article['fantasy_analysis'].'</p>');
                    }
                    ?>
                </div><!-- .entry-content -->

                <footer class="entry-footer">
                    <div class="clear"></div>
                    <span class="cat-links">
                        <?php echo(anchor('/news/articles'.$typeIdStr.$varIdStr,$news_type_name,['class'=>'cat_link'])); ?>
                    </span>
                </footer><!-- .entry-footer -->
                <div class="clear"></div>
                </article>
            </main>
        </div>
        <br />
        <?php
            $mode = 'add';
            if (isset($article['article_id']) && $article['article_id'] != -1) 
                $mode = 'edit';
            ?>
            <form id="confirmForm" name="confirmForm" action="<?php echo(DIR_APP_ROOT.'news/submit'); ?>" method="post">
                <input type="hidden" name="author_id" value="<?php echo($article['author_id']); ?>" />
                <input type="hidden" name="news_body" value="<?php echo($article['news_body']); ?>" />
                <input type="hidden" name="news_subject" value="<?php echo($article['news_subject']); ?>" />
                <input type="hidden" name="storyDateM" value="<?php echo($article['storyDateM']); ?>" />
                <input type="hidden" name="storyDateD" value="<?php echo($article['storyDateD']); ?>" />
                <input type="hidden" name="storyDateY" value="<?php echo($article['storyDateY']); ?>" />
                <input type="hidden" name="type_id" value="<?php echo($article['type_id']); ?>" />
                <input type="hidden" name="var_id" value="<?php echo($article['var_id']); ?>" />
                <?php
                if (isset($article['fantasy_analysis']) && !empty($article['fantasy_analysis'])) { ?>
                    <input type="hidden" name="fantasy_analysis" value="<?php echo($article['fantasy_analysis']); ?>" />
                <?php
                }
                if (isset($article['uploadedImage']) && !empty($article['uploadedImage']) && file_exists(PATH_NEWS_IMAGES_PREV_WRITE.$article['uploadedImage'])) { ?>
                    <input type="hidden" name="uploadedImage" value="<?php echo($article['uploadedImage']); ?>" />
                <?php
                } else if (isset($article['prevImage']) && !empty($article['prevImage']) && file_exists(PATH_NEWS_IMAGES_PREV_WRITE.$article['prevImage'])) { ?>
                    <input type="hidden" name="prevImage" value="<?php echo($article['prevImage']); ?>" />
                <?php
                }
                ?>
                <!-- META FIELDS -->
                <input type="hidden" name="mode" value="<?php echo($mode); ?>" />
                <?php
                if (isset($article['article_id']) && $article['article_id'] != -1) { ?>
                <input type="hidden" name="id" value="<?php echo($article['article_id']); ?>" />
                <?php } ?>
                <input type="hidden" name="validForm" value="1" />
                <input type="hidden" name="preview" value="0" />
                
            </form>
            <fieldset class="button_bar align-left">
                <button class="sitebtn " id="edit">Revise Article</button>
                <span style="margin-right:2px;display:inline;">&nbsp;</span>
                <button class="sitebtn " id="acceptBtn">Accept &amp; Submit</button>
            </fieldset>
            <br clear="all" /><br />
    </div>
        <!-- RIGHT COLUMN -->
    <div id="right-column-mid">
        <?php echo($secondary); ?>
    </div>
            