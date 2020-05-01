    <div id="single-column">
        <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        <?php if (isset($previewMessage) && !empty($previewMessage)) { ?>
            <span class="<?php echo($previewMessageType); ?>"><?php echo $previewMessage; ?></span>
        <?php } //END if ?>
    </div>
    <div class="rule"></div>
    <br />
    <?php if (isset($previewContent) && !empty($previewContent)) { 
        echo $previewContent;
    }
    ?>