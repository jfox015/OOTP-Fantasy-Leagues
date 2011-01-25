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
    <?php include('nav_bugs.php'); ?>
    </div>
    <div id="center-column">
    	<div class="textual_content">
            <h1><?php echo $subTitle; ?></h1>
            <p /><br />
            <?php echo $theContent; ?>
            <p /><br />&nbsp;<br />
        </div>
	</div>