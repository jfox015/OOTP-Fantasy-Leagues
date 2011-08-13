
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
            $form->open('user/register','registerForm|registerForm');
            $form->fieldset('Required Fields');
            $form->text('email','E-Mail Address','required|trim|valid_email',($input->post('email') ? $input->post('email') : ''));
            $form->space();
            $form->text('username','Username','required|trim',($input->post('username') ? $input->post('username') : ''));
            $form->space();
            $form->password('password','Password','required|min_length[8]|match[passwordConfirm]');
            $form->br();
            $form->password('passwordConfirm','Confirm Password','required|min_length[8]');
            $form->space();
            // $form->label('Terms Agreement');
            //$form->nobr();
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
			$form->space();
			if (isset($security_enabled) && $security_enabled == 1) {
				 $form->fieldset('Verification');
				if ($security_type == SECURITY_RECAPTHCA) {
					$form->html('<div width="100%" id="focus_response_field" style="margin-left:125px;">');
					$form->html('<div id="captcha_resp" class="clearfix"></div>');
					$form->html('<div id="recaptcha_div" class="clearfix"></div>');
					$form->html('</div>');
				}
           	}
			$form->fieldset('',array('class'=>'button_bar'));
			$form->html('<p class="step"><div id="waitDiv" style="display:none;"><img src="'.$config['fantasy_web_root'].'images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...</div>');
    		$form->html('<div id="buttonDiv">');
            $form->button('Register','btnSubmit','button',array('class'=>'button'));
    		$form->html('</div>');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <?php 
    // IF SECURITY IS ENABLED, DRAW THE SUPPORTING JAVASCRIPT TO THE PAGE
    if (isset($securityJS) && !empty($securityJS)) { print($securityJS); } ?>
    <script type="text/javascript">
    $(document).ready(function(){
    	 <?php 
    	 if ($security_enabled == 1 && $security_class >= 1) { ?>
    	 $("#btnSubmit").click(function() {
    		testCaptcha(document.registerForm.recaptcha_challenge_field.value,document.registerForm.recaptcha_response_field.value,'registerForm');
		});
		showRecaptcha('recaptcha_div');
		<?php  
    	} else {// END if
    	?>
    	$("#btnSubmit").click(function() { $("#registerForm").submit(); });
    	<?php  
        } // END if
        ?>
    });
	</script>
    <p /><br />