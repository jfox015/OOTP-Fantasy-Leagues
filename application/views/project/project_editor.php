    <script src="<?php echo($config['fantasy_web_root']); ?>js/nicEdit.js" type="text/javascript"></script>
	<script type="text/javascript">
    $(document).ready(function(){		   
		$('#delete').click(function(){
			document.location.href = '/project/delete/<?php echo($thisItem['id']); ?>';
		});	
		$('#cancel').click(function(){
			<?php if (isset($thisItem['id']) && ($thisItem['id'] != "add" && $thisItem['id'] != -1)) { ?>
			document.location.href = '/project/info/<?php echo($thisItem['id']); ?>';
			<?php } else { ?>
			history.back(-1);
			<?php } ?>
		});
	});
    bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('description');
	});
    </script>
    <div id="column_center">
        <div id="top_nav"><h1><?php echo $subTitle; ?></h1></div>
        <br />
        <div class="content-form">
            <div id="editor">
                <div class="dialog_head">
                Enter the information for this project below.
                </div>
                <div class="dialog_body">
                <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
				}
				echo($form);
                ?>
                </div>
                <div class="dialog_foot"></div>
                <br class="clear" clear="all" />
            </div>
            <br class="clear" clear="all" />
        </div>
    </div>
    <p /><br />