    <script src="<?php echo($config['fantasy_web_root']); ?>js/nicEdit.js" type="text/javascript"></script>
	<script type="text/javascript">
	bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('inviteMessage');
	});
    $(document).ready(function() {

		$('#email').change(function(){
			selectOne('email');
		});
		$('#user_id').change(function(){
			selectOne('user_id');
		});
	});
	function selectOne(type) {
		var user_id = parseInt($('#user_id').val()), 
		email = parseInt($('#email').val());
		if (type =='email') {
			if ((typeof email !== 'undefined' || email !== '') && typeof user_id !== 'undefined' && user_id !== -1) {
				$('#user_id').val(-1);
			}
		} else {
			if ((typeof email !== 'undefined' || email !== '') && typeof user_id !== 'undefined' && user_id !== -1) {
				$('#email').val('');
			}
		}
	}
    </script>
    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;">
            Use the following form to invite new owners into your league. Only one invitation is allowed per e-mail address per League.</p>
			<p style="text-align:left;">
			To invite non-site members, enter an e-mail address. To select an existing site member, choose their name from the list below.
			</p>
			<p style="text-align:left;">
			View a complete list of <?php echo anchor('league/leagueInvites/'.$this->dataModel->id,'pending invitiations'); ?>.
            </p>
			<?php 
			if ( ! function_exists('form_open')) {
				$this->load->helper('form');
			}
			$errors = validation_errors();
			if ($errors || !empty($customError)) {
				echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.$customError.'</b></span><p />';
			}
			$form = new Form();
			$form->open('league/inviteOwner','detailsForms');
			$form->fieldset();
			$form->text('email|email','E-Mail Address','email',($input->post('email')) ? $input->post('email') : '',array('class'=>'longtext'));
			$form->br();
			$form->br();
			$form->span("OR",array('style'=>'text-align:left;'));
			$form->br();
			$form->br();
			if (isset($availableUsers) && sizeof($availableUsers) > 0) {
				$form->select('user_id|user_id',$availableUsers,'Select Site Member:',($this->input->post('user_id') ? $this->input->post('user_id') : -1));
				$form->br();
				$form->space();
				$form->br();
			}
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
            <p><br />          
        </div>
    </div>
    <p><br />