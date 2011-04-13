<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){	
		$('a[rel=back]').click(function(e) {
			e.preventDefault();
			history.back(-1);					
		});
   	});
</script>
    <div id="center-column">
   	<div class="textual_content">
            <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
            <p /><br />
            <?php echo $theContent; ?>
            <p /><br />&nbsp;<br />
        </div>
	</div>