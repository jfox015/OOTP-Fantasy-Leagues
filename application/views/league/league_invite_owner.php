    <script src="<?php echo($config['fantasy_web_root']); ?>js/nicEdit.js" type="text/javascript"></script>
	<script type="text/javascript">
	bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('inviteMessage');
	});
	</script>
    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            Use the following form to invite new owners into your league. Only one invitation is allowed per e-mail address. View a complete list of <?php echo anchor('league/leagueInvites/'.$this->dataModel->id,'pending invitiations'); ?>.
            <?php 
			if ( ! function_exists('form_open')) {
				$this->load->helper('form');
			}
			$errors = validation_errors();
			if ($errors) {
				echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
			}
			$form = new Form();
			$form->open('league/inviteOwner','detailsForms');
			$form->fieldset();
			$form->text('email','E-Mail Address','required',($input->post('email')) ? $input->post('email') : '',array('class'=>'longtext'));
			$form->space();
			$form->br();
			$form->label('Message', '', array('class'=>'required'));
			$form->html('<div class="richEditor">');
			$form->html('<div id="myNicPanel" class="nicEdit-panel"></div>');
			$form->br();
			$form->textarea('inviteMessage','','required',($input->post('inviteMessage')) ? $input->post('inviteMessage') : $defaultMessage,array('rows'=>10,'cols'=>70,'wrap'=>'soft'));
			$form->html('</div>');
			$form->br();
			$form->space();
			$form->fieldset('',array('class'=>'button_bar'));
			$form->submit('Send Invitation');
			$form->hidden('id',$league_id);
			$form->hidden('owner_id',$owner_id);
			$form->hidden('team_id',$team_id);
			$form->hidden('submitted',1);
			echo($form->get());
			?>
            <p /><br />          
        </div>
    </div>
    <p /><br />