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
			if ($security_enabled == 1 && $security_class >= 1) {
				 $form->fieldset('Verification');
				if ($security_type == SECURITY_RECAPTHCA) {
					$form->html('<div width="100%" id="focus_response_field" style="margin-left:125px;">');
					$form->html('<div id="captcha_resp" class="clearfix"></div>');
					$form->html('<div id="recaptcha_div" class="clearfix"></div>');
					$form->html('</div>');
				}
           	}
			$form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->html('<p class="step"><div id="waitDiv" style="display:none;"><img src="'.$config['fantasy_web_root'].'images/icons/ajax-loader.gif" width="28" height="28" border="0" align="absmiddle" />&nbsp;Operation in progress. Please wait...</div>');
    		$form->html('<div id="buttonDiv">');
            $form->button('Submit Form','btnSubmit','button',array('class'=>'button'));
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
    		testCaptcha(document.detailsForm.recaptcha_challenge_field.value,document.detailsForm.recaptcha_response_field.value,'detailsForm');
		});
		showRecaptcha('recaptcha_div');
		<?php  
    	}  else {// END if
    	?>
    	$("#btnSubmit").click(function() { $("#detailsForm").submit(); });
    	<?php  
        } // END if
        ?>
    });
	</script>
    <p /><br />