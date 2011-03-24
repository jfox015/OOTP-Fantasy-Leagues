	<?php
	$defaultType = ($this->input->post('fileType')) ? $this->input->post('fileType') : 1;
	?>    
    	<!-- BEGIN REGISTRATION FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
        <?php
		$errors = validation_errors();
		if ($errors) {
			print '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
		}
		if ($outMess) {
			print $outMess;
		} else {
			print("Use this form to upload your MySQL SQL Data files.<br /><br />* - Uploaded files must be in ZIP format.<br />");
		}
		?>
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Select files to upload</td>
        </tr>
        <tr>
            <td>
			<?php 
            $form = new Form();
            $form->open('admin/configUploadFile','configUpload');
           	$form->fieldset('');
			$form->iupload ('dataFiles','SQL Zip File Archive',false,array("class"=>"longtext"));
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$responses[] = array('1','Yes');
			$responses[] = array('-1','No');       
			$form->radiogroup ('deflate',$responses,'Unzip files after upload?',($this->input->post('deflate') ? $this->input->post('deflate') : 1),'required');
			$form->space('');
			$form->span("If you select no, you will have to manually unzip these files after upload.",array('class'=>'field_caption info_txt'));
			$form->space();
           	$form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Submit');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />