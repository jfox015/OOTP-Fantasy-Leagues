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
    <? include_once('nav_members.php'); ?>
    </div>
    <div id="center-column">
   	<div class="textual_content">
            <div class="top-bar"><h1><? echo($subTitle); ?></h1></div>
            <p /><br />
            <? echo $theContent; ?>
            <p /><br />&nbsp;<br />
        </div>
	</div>