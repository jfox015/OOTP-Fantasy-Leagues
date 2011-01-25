	<script src="<?php echo($config['fantasy_web_root']); ?>js/nicEdit.js" type="text/javascript"></script>
	<script type="text/javascript">
    $(document).ready(function(){		   
		$('#delete').click(function() {
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>news/delete/<?php echo($thisItem['id']); ?>';
		});	
		$('#cancel').click(function() {
			<?php if (isset($thisItem['id']) && ($thisItem['id'] != "add" && $thisItem['id'] != -1)) { ?>
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>news/info/<?php echo($thisItem['id']); ?>';
			<?php } else { ?>
			history.back(-1);
			<?php } ?>
		});
	});
	bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('news_body');
	});
    </script>
    <div id="center-column">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <br class="clear" />
        <?php if (isset($dump) && !empty($dump)) {
			echo("<h3>DEBUG: Object Data Dump:</h3><br />".$dump."<br />");
		} ?>
        <?php if (isset($preview) && !empty($preview)) { ?>
        	<?php echo($preview); ?>
		<?php } ?>
        <div class="textbox">
        <table cellpadding="0" cellspacing="0" style="width:625px;">
          <tr class="title">
            <td style="padding:0 0 4px 6px;height:25px;">Enter the information for this news article below.</td>
          </tr>
          <tr>
            <td width="100%">
            <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<div class="error">The following errors were found with your submission:<br /><ul>'.$errors.'</ul></div>';
				}
				echo($form);
                ?>
            
            </td>
          </tr>
        </table>
      </div>
    </div>
    <p /><br />