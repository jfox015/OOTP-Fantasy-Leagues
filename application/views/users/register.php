		<!-- BEGIN LOGIN FORM -->
    <div id="center-column">
        <h1><?php echo($subTitle); ?></h1>
        <br />
		<?php echo($theContent); ?>
        <?php 
		if (!empty($activation)) { ?>
        <p /><br />
        <?php echo($activation); ?>
		<?php } ?>
        <br /><br />
	</div>
    <div id="center-column">
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:525px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Enter the information below to register for the site.</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
            $form = new Form();
            $form->open('user/register','register');
            $form->fieldset('Required Fields');
            $form->text('email','E-Mail Address','required|trim|valid_email',($input->post('email') ? $input->post('email') : ''));
            $form->space();
            $form->text('username','Username','required|trim',($input->post('username') ? $input->post('username') : ''));
            $form->space();
            $form->password('password','Password','required|min_length[8]|match[passwordConfirm]');
            $form->br();
            $form->password('passwordConfirm','Confirm Password','required|min_length[8]');
            $form->space();
            $form->label('Terms Agreement');
            $form->nobr();
            //$form->checkbox('termsAgree',1,'Terms Agreement',$input->post('termsAgree'),'required');
            //$form->nobr();
            // $form->span('I have read and accept the <a href="/about/terms">Terms and Conditions</a> and <a href="/about/privacy">Privacy Policy</a> of this Web site.',array('class'=>'field_caption'));
            $form->space();
            $form->fieldset('Optional Fields');
            $form->text('firstName','First Name','trim',($input->post('firstName') ? $input->post('firstName') : ''));
            $form->space();
            $form->text('lastName','Last Name','trim',($input->post('lastName') ? $input->post('lastName') : ''));
			$form->space();
			$form->select('country|country',loadCountries(),'Country',($this->input->post('country') ? $this->input->post('country') : 231));
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$gender[] = array('m','Male');
			$gender[] = array('f','Female');
			$form->radiogroup ('gender',$gender,'Gender',($input->post('gender') ? $input->post('gender') : ''));
			$form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Register');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />