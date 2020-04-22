	<script src="<?php echo($config['fantasy_web_root']); ?>js/nicEdit.js" type="text/javascript"></script>
	<script type="text/javascript">
    bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('aboutHTML');
	});
    </script>
    	<!-- BEGIN LOGIN FORM -->
    <div id="subPage">
        <?php include_once('admin_breadcrumb.php'); ?>
        <div id="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        <br /><br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Edit About Page</td>
        </tr>
        <tr>
            <td>
                <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
				}
				$form = new Form();
        		$form->open($config['fantasy_web_root'].'admin/configAbout/','detailsForm|detailsForm');
        		
        		$form->fieldset('');

        		$form->html('<div class="richEditor">');
				$form->html('<div id="myNicPanel" class="nicEdit-panel-wide html_editor"></div>');
              	$form->textarea('aboutHTML','','trim',($input->post('aboutHTML') ? $input->post('aboutHTML') : $aboutHTML),array('style'=>'width:725px'));
                $form->html('</div>');
				$form->space();
				$form->fieldset('',array('class'=>'button_bar'));
				$form->submit('Update About Page');
				$form->hidden('mode','edit');
                echo($form->get());
                ?>
                </td>
        </tr>
        </table>
        </div>
            <br class="clear" clear="all" />
    </div>
    <p /><br />