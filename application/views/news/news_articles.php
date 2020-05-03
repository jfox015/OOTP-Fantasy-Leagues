<div id="single-column" class="newslist">
	<article class="hero-area">
        <img src="<?php echo(PATH_IMAGES); ?>news_header_<?php echo(strtolower($news_type_name)); ?>.jpg" class="splash" />
		<h2><?php echo($pageTitle); ?></h2>
	</article>
</div>

<?php
$typeIdStr = "";
$varIdStr = "";
if (isset($type_id) && !empty($type_id) && $type_id != -1) {
    $typeIdStr = "/type_id/".$type_id;
}
if (isset($var_id) && !empty($var_id) && $var_id !== false) {
    $varIdStr = "/var_id/".$var_id;
}
if (isset($articles) && count($articles) > 0) {
    
    //echo("Article Count = ".count($articles).'<br />');
    if (count($articles)> 1) {
        $articlesPerColumn = intval(count($articles) / 2);
        if (!(count($articles) % 2) == 0) { $articlesPerColumn++; }
    } else {
        $articlesPerColumn =  1;
    }
    $totalArticlesDrawn = 0;
    $articlesDrawn = 0;
    $columnDrawn = 0;
    foreach($articles as $article) {
        $aTypeIdStr = $typeIdStr;
        if (isset($article['type_id']) && !empty($article['type_id']) && $article['type_id'] !== false) {
            $aTypeIdStr = "/type_id/".$article['type_id'];
        }
        $aVarIdStr = $varIdStr;
        if (isset($article['var_id']) && !empty($article['var_id']) && $article['var_id'] !== false) {
            $aVarIdStr = "/var_id/".$article['var_id'];
        }   
        if ($articlesDrawn == 0 || $articlesDrawn == $articlesPerColumn) {
            if ($articlesDrawn == $articlesPerColumn) {
                $articlesDrawn = 0;
            } // END if ($articlesDrawn == $articlesPerColumn)
            ?>
<div id="left-column-article" class="content-area">

        <?php
        } // END if ($articlesDrawn == 0 || $articlesDrawn == $articlesPerColumn)
        ?>
        <article class="excerpt">
            <header class="entry-header">
                <h2 class="entry-title">
                    <?php echo(anchor('/news/article/id/'.$article['id'].$aTypeIdStr.$aVarIdStr, $article['news_subject'],['rel'=>'bookmark'])); ?>
                </h2>    
                <div class="entry-meta">
                    <?php
                    if (isset($article['var_id']) && !empty($article['var_id']) && $article['var_id'] !== false) { 
                        if (!function_exists('getLeagueName')) {
                            $this->load->helper('roster');
                        }
                    ?>
                    <span class="cat-links">
                        <?php
                        $boxTitle="";
                        switch($type_id) {
                            case 2:
                                $boxTitle=getLeagueName($article['var_id']);
                                break;
                            case 3:
                                $details = getPlayerBasics($article['var_id']);
                                if ($details['position'] != 1) {
                                    $boxTitle = get_pos($details['position']);
                                } else {
                                    $boxTitle = get_pos($details['role']);
                                }
                                $boxTitle .= " ".$details['first_name']." ".$details['last_name'];
                                break;
                            case 4:
                                $boxTitle=getTeamName($article['var_id']);
                                break;
                        } // END SWITCH
                        echo($boxTitle);
                        ?>
                    </span>
                    <?php } ?>
                    <div class="excerpt-thumb">
                        <!--div class="thumb-social">
                            <ul>
                                <li><a href="http://www.facebook.com/sharer.php?u=<?php echo(SITE_URL.'/news/article/'.$id); ?>"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                <li><a href="https://twitter.com/share?url=<?php echo(SITE_URL.'/news/article/'.$id); ?>"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                            </ul>
                        </div-->
                        <?php 
                        if (isset($article['image']) && !empty($article['image']) && file_exists(PATH_NEWS_IMAGES_WRITE.$article['image'])) {
                            echo(anchor('/news/article/id/'.$article['id'].$aTypeIdStr.$aVarIdStr,'<img src="'.PATH_NEWS_IMAGES.$article['image'].'" class="attachment-post-thumb size-post-thumb wp-post-image">',['class'=>"excerpt-thumb"]));
                        }
                        ?>
                    </div>
                    <div class="date"><?php echo(date('F j, Y',strtotime($article['news_date']))); ?></div><br />
                    <span class="cat-links">
                        <?php echo(anchor('/user/profiles/'.$article['author_id'], $article['author_name'],['class'=>'cat_link'])); ?> <span class="separator"></span>
                        <?php echo(anchor('/news/articles'.$typeIdStr.$varIdStr,$article['newsType'],['class'=>'cat_link'])); ?>
                    </span>    
                </div><!-- .entry-meta -->
            </header><!-- .entry-header -->

            <div class="entry-content">
                <p>
                <?php
                $dispNews = '';
                if (strlen($article['news_body']) > $excerptMaxChars) {
                    $dispNews = substr($article['news_body'],0,$excerptMaxChars);
                } else {
                    $dispNews = $article['news_body'];
                } // END if
                echo('<span class="news_body">'.$dispNews);
                if (strlen($article['news_body']) > $excerptMaxChars) {
                    echo('...');
                } // END if
                echo('</span>');
                ?>
                </p>
            </div><!-- .entry-content -->

            <footer class="entry-footer">
                <?php echo(anchor('/news/article/id/'.$article['id'].$aTypeIdStr.$aVarIdStr,'<button class="sitebtn readmore">Read More</button>')); ?>
                <div class="clear"></div>
            </footer><!-- .entry-footer -->
            <div class="clear"></div>
        </article>
        <?php
            $articlesDrawn++;
            $totalArticlesDrawn++;
            if ($articlesDrawn == $articlesPerColumn || $totalArticlesDrawn == count($articles)) { ?>
</div>
            <?php
                $columnDrawn++;
                if ($columnDrawn == 2 && $totalArticlesDrawn < count($articles)) {
                    break;
                }
                $articlesDrawn = 0;
                if ($articlesPerColumn == 1 &&  count($articles) == 1 && $totalArticlesDrawn == 1) { ?>
<div id="left-column-article" class="content-area"><article class="excerpt"><div class="entry-content">&nbsp;</div></article></div>
                <?php
                } // END if
            } // END if
        } // END foreach($articles as $id => $article) 
    // } // END if ($articlesPerColumn > 1)
} else {
    ?>
<div id="left-column-article-dbl" class="content-area">
    <article class="excerpt">
        <header class="entry-header">
            <h2 class="entry-title">No Articles Found</h2>
        </header>
        <div class="entry-content">
            <p>No news articles are available at this time. Try choosing a different category of news.</p>
        </div>
        <?php
        if ($accessLevel >= ACCESS_WRITE) { 
            echo('<footer class="entry-footer">');
            echo(anchor('/news/submit/mode/add'.$typeIdStr.$varIdStr,'<button class="sitebtn readmore">Create a News Article</button>')); 
            echo('</footer>');
        } else {
            echo('<div class="entry-content">');
            echo('<p><br /><b>'.anchor('/user/register','Register',['style'=>'font-weight:bold;']).'</b> for the site or '.anchor('/user/login', 'Login',['style'=>'font-weight:bold;']).' to create a news article for the site.');
            echo('</div>');
        }
        ?>
        <div class="clear"></div>
    </article>
</div>
<?php
}// END if (isset($articles) && count($articles) > 0)
?>
    <!-- RIGHT COLUMN -->
<div id="right-column-mid">
    <?php echo($secondary); ?>
</div>