<?php
$typeIdStr = "";
$varIdStr = "";
if (isset($type_id) && !empty($type_id) && $type_id != -1) {
    $typeIdStr = "/type_id/".$type_id;
}
if (isset($var_id) && !empty($var_id) && $var_id != -1) {
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
                            <?php echo(anchor('/user/profiles/'.$author['author_id'], $author['authorName'],['class'=>'cat_link'])); ?></span>
                        </span> 
                    </div>
                </header><!-- .entry-header -->

            <div class="clear"></div>

            <div class="entry-content">
                <?php 
                $img = PATH_NEWS_IMAGES."news_preview_default.jpg";
                if (isset($article['image']) && !empty($article['image']) && file_exists(PATH_NEWS_IMAGES_WRITE.$article['image'])) {
                    $img = PATH_NEWS_IMAGES.$article['image'];
                }
                echo('<p><img src="'.$img.'" class="alignnone wp-image-21"></p>');
                ?>
                <p><?php echo($article['news_body']); ?></p>
                <?php
                if (isset($article['fantasy_analysis']) && !empty($article['fantasy_analysis'])) {
                    echo('<h3>Fantasy Analysis</h3>');
                    echo('<p>'.$article['news_body'].'</p>');
                }
                ?>
            </div><!-- .entry-content -->

            <footer class="entry-footer">
                <div class="clear"></div>
                <span class="cat-links">
                    <?php echo(anchor('/news/articles'.$typeIdStr.$varIdStr,$articleType['newsType'],['class'=>'cat_link'])); ?>
                </span>
            </footer><!-- .entry-footer -->
            <div class="clear"></div>
            </article>
        </main>
    </div>
</div>
    <!-- RIGHT COLUMN -->
<div id="right-column-mid">
    <?php echo($secondary); ?>
</div>