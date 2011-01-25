		<!-- BEGIN LOGIN FORM -->
    <div id="single=column">
        <h1><?php echo($subTitle); ?></h1>
        <br />
		<?php echo($theContent); ?>
        <br /><br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:640px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Please enter bug details below.</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
            }
            $form = new Form();
            $form->open($config['fantasy_web_root'].'about/bug_report/','detailsForm|detailsForm');
            
            $form->fieldset('Bug Details');
            
            $form->label('Entered:');
            $entryDate = date('m/j/Y');
			$enteredBy = '';
			if (isset($currUser)) {
            	$enteredBy = resolveUsername($currUser);
			} 
			if (empty($enteredBy)) {
				$enteredBy = "Anonymous User";
			}
            $form->span($entryDate." by ".$enteredBy);
            $form->space();
            $form->text('summary','Summary','required|trim',$this->input->post('summary'),array('class'=>'longtext'));
            $form->span('Please enter a descriptive summary or title for this bug',array('class'=>'field_caption'));
            $form->space();
            $form->textarea('description','Description','required',$this->input->post('description'));
            $form->span('Please be as thorough and descriptive of the problems as possible. Please include all steps leading up to and after the bug occured to help in recreating the bug.',array('class'=>'field_caption'));
            $form->space();
            $form->fieldset('Optional Details');
            $form->text('url','URL','trim|max_length[1000]',$this->input->post('url'),array('class'=>'longtext'));
            $form->br();
            $form->select('os',loadSimpleDataList('os'),'Platform OS',$this->input->post('os'));
            $form->br();
            $form->select('browser',loadSimpleDataList('browser'),'Browser',$this->input->post('browser'));
            $form->br();
            $form->text('browVersion','Browser Version','trim|max_length[500]',$this->input->post('browVersion'));
            $form->br();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit();
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />