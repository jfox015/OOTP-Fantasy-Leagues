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
    <?php include_once('nav_members.php'); ?>
    </div>
    <div id="center-column">
   	<div class="textual_content">
            <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
            <p /><br />
            <div class="content_column">
            <?php echo anchor('/search/doSearch/members/','Registered Member List'); ?>
            <br /><br />
            <?php echo anchor('/member/submit/add','Add a new member manually'); ?>
            </div>
            <div class="content_column"></div>
            <p /><br />&nbsp;<br />
        </div>
	</div>