		<!-- BEGIN LOGIN FORM -->
    <div id="single=column">
        <h1><?php echo($subTitle); ?></h1>
        <br />
		<?php echo($theContent); ?>
        <br /><br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:640px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Please enter information below.</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br /><b>'.$errors.'</b></span>';
            }
            $form = new Form();
            $form->open($config['fantasy_web_root'].'about/contact/','detailsForm|detailsForm');
            $form->fieldset('');
            $form->text('name','Name','required|trim',$this->input->post('name'),array('class'=>'longtext'));
            $form->space();
			$form->text('email','E-mail Address','required|trim',$this->input->post('email'),array('class'=>'longtext'));
            $form->space();
			$form->text('subject','Subject','required|trim',$this->input->post('subject'),array('class'=>'longtext'));
            $form->space();
			$form->textarea('details','Contact Details','required',$this->input->post('details'));
            $form->space();
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