    <script type="text/javascript">
    $(document).ready(function(){		   
		$('#deleteConfirm').click(function(){
			$('#confirmForm').submit();
		});	
		$('#deleteCancel').click(function(){
			history.back(-1);
		});
	});
    </script>
    
    <div id="left-column">
    <?php include_once('nav_news.php'); ?>
    </div>
    <div id="center-column">
   	<div class="textual_content">
            <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
            <p><br />
            <?php echo $theContent; ?>
            <p><br />&nbsp;<br />
        </div>
	</div>