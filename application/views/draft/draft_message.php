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
    
    <div id="center-column">
    <?php if (isset($thisItem['id']) && ($thisItem['id'] != "add" && $thisItem['id'] != -1)) {
	include_once('admin_breadcrumb.php'); 
	}
	?>
    
   	<div class="textual_content">
            <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
            <p /><br />
            <?php echo $theContent; ?>
            <p /><br />&nbsp;<br />
        </div>
	</div>