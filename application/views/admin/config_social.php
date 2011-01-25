		<!-- BEGIN REGISTRATION FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Enter settings information below</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
			if ($outMess) {
                echo $outMess;
            }
            $form = new Form();
            $form->open('admin/configSocial','configSocial');
           	$responses[] = array('1','Enabled');
			$responses[] = array('-1','Disabled');       
			$form->fieldset('Global Settings');
			$form->html('Setting the following option to <strong>disabled</strong> turns off all social media options sitewide. You can fine tune which options to use below.');
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('sharing_enabled',$responses,'Social Media Sharing Options',($this->input->post('sharing_enabled') ? $this->input->post('sharing_enabled') : $config['sharing_enabled']),'required');
			$form->space();
			$form->fieldset('Social Sharing Tools');
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('share_facebook',$responses,'<img src="'.PATH_IMAGES.'icons/facebook.png" align="absmiddle" /> Facebook',($this->input->post('share_facebook') ? $this->input->post('share_facebook') : $config['share_facebook']),'required');
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('share_twitter',$responses,'<img src="'.PATH_IMAGES.'icons/twitter.png" align="absmiddle" /> Twitter',($this->input->post('share_twitter') ? $this->input->post('share_twitter') : $config['share_twitter']),'required');
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('share_digg',$responses,'<img src="'.PATH_IMAGES.'icons/digg.png" align="absmiddle" /> Digg',($this->input->post('share_digg') ? $this->input->post('share_digg') : $config['share_digg']),'required');
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('share_stumble',$responses,'<img src="'.PATH_IMAGES.'icons/stumbleupon.png" align="absmiddle" /> StumbleUpon',($this->input->post('share_stumble') ? $this->input->post('share_stumble') : $config['share_stumble']),'required');
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('share_addtoany',$responses,'<img src="'.PATH_IMAGES.'icons/addtoany.png" align="absmiddle" /> Add to Any',($this->input->post('share_addtoany') ? $this->input->post('share_addtoany') : $config['share_addtoany']),'required');
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