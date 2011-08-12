	<script type="text/javascript" charset="UTF-8">
	var fader = null;
	$(document).ready(function(){
		$('button#btnSubmit').click(function() {
			$('div#activeStatus').removeClass('error');
			$('div#activeStatus').removeClass('success');
			$('div#activeStatus').removeClass('warn');
			$('div#activeStatus').empty();
			$('div#statusBox').hide();
			var mess = '';
			var val = $('input:radio[name=security_enabled]:checked').val();;
			if (val != -1 && $('#security_type').val() == -1) {
				mess = "You must select a security type before saving";
			} else if (val != -1 && $('#security_type').val() == 1) {
				if ($('#recaptcha_key_public').val() == '' || $('#recaptcha_key_private').val() == '') {
					mess = "reCAPTCHA public and private keys are required to use this service. Turn off security or select another service to save this form without entering keys.";
					if ($('#recaptcha_key_public').val() == '') $('#recaptcha_key_public').addClass('error');
					if ($('#recaptcha_key_private').val() == '') $('#recaptcha_key_private').addClass('error');
				}
			}
			if (mess != '') {
				$('div#activeStatus').html(mess);
				$('div#activeStatus').addClass('error');
				$('div#statusBox').fadeIn("fast",function() { fader = setTimeout('fadeStatus("statusBox")',5000); });
			} else {
				$('form#configSecurity').submit();
			}
        });
		$('#security_type').change(function() {
			testSecurityType();
		});
		$('#recaptcha_key_public').change(function() { $('#recaptcha_key_public').removeClass('error'); });
		$('#recaptcha_key_private').change(function() { $('#recaptcha_key_private').removeClass('error'); });
        testSecurityType();
	});
	function fadeStatus(box) {
		$('div#'+box).focus();
		$('div#'+box).fadeOut("normal",function() { clearTimeout(fader); $('div#'+box).hide(); });
	}
	function testSecurityType() {
		var type = parseInt($('#security_type').val());
		var recaptchaStyle = 'none';
		switch(type) {
			case <?php print(SECURITY_RECAPTHCA); ?>:
				recaptchaStyle = 'block';
				break;
			default:
				break;
		}
		$('fieldset#recpatcha').css('display',recaptchaStyle);
	}
	</script>
	  
    	<!-- BEGIN REGISTRATION FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        
        <h1><?php echo($subTitle); ?></h1>
        <br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Edit security settings information below</td>
        </tr>
        <tr>
            <td>
            <div id="statusBox"><div id="activeStatus"></div></div>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
			if ($outMess) {
                echo $outMess;
            }
            $form = new Form();
            $form->open('admin/configSecurity','configSecurity|configSecurity');
          	$form->fieldset('Security Settings');
           	$responses[] = array('1','Enabled');
			$responses[] = array('-1','Disabled');       
			$form->fieldset('',array('class'=>'radioGroup'));
            $form->radiogroup ('security_enabled',$responses,'Spam Bot Countermeasures',($this->input->post('security_enabled') ? $this->input->post('security_enabled') : $config['security_enabled']),'required');
            $form->fieldset('');
            $form->select('security_type|security_type',array('-1'=>'Select a service',SECURITY_RECAPTHCA=>'reCAPTCHA'),'Countermeasure Type',($this->input->post('security_type')) ? $this->input->post('security_type') : $config['security_type']);
            $form->br();
			$form->fieldset('Choose Scope of Protection');
			$form->html('Select the groups of page to apply spam protection to.');
			$form->br();
			$form->select('security_class|security_class',loadSecurityClasses(),'Page Classes',($this->input->post('security_class')) ? $this->input->post('security_class') : $config['security_class']);
			$form->space();
			$form->br();
			$form->fieldset('reCAPTCHA Settings',array('id'=>"recpatcha"));
			$form->span('reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.');
			$form->space();
			$form->br();
			$form->html('<b>reCAPTCHA Authentication</b>');
			$form->space();
            $form->html('These keys are required before you are able to do anything else. You can get the keys <a href="http://www.google.com/recaptcha/" target="_blank" title="Get your reCAPTCHA API Keys">here</a>.');
			$form->space();
            $form->span('Be sure not to mix them up! The public and private keys are not interchangeable!');
            $form->space();
            $form->br();
			$form->text('recaptcha_key_public','Public Key','trim',($input->post('recaptcha_key_public') ? $input->post('recaptcha_key_public') : $config['recaptcha_key_public']),array('class'=>'longtext'));
            $form->br();
			$form->text('recaptcha_key_private','Private Key','trim',($input->post('recaptcha_key_private') ? $input->post('recaptcha_key_private') : $config['recaptcha_key_private']),array('class'=>'longtext'));
            $form->space();
            $form->br();
			$form->html('<b>reCAPTCHA Options</b>');
			$form->space();
            $form->br();
			$form->select('recaptcha_theme|recaptcha_theme',loadRecaptchaThemes(),'Theme',($this->input->post('recaptcha_theme')) ? $this->input->post('recaptcha_theme') : $config['recaptcha_theme']);
			$form->br();
			$form->select('recaptcha_lang|recaptcha_lang',loadRecaptchaLangs(),'Language',($this->input->post('recaptcha_lang')) ? $this->input->post('recaptcha_lang') : $config['recaptcha_lang']);
			$form->br();
			$form->select('recaptcha_compliant|recaptcha_compliant',array('-1'=>'None','1'=>'XHTML 1.0 Strict Compliant Code'),'Standards Compliance Mode',($this->input->post('recaptcha_compliant')) ? $this->input->post('recaptcha_compliant') : $config['recaptcha_compliant']);
			$form->space();
            
            $form->fieldset('',array('class'=>'button_bar'));
            $form->button('Save Settings','btnSubmit','button',array('class'=>'button'));
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />