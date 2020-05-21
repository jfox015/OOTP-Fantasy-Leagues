    <script src="<?php echo($config['fantasy_web_root']); ?>js/nicEdit.js" type="text/javascript"></script>
	<script type="text/javascript">
    bkLib.onDomLoaded(function() {
		var myNicEditor = new nicEditor();
        myNicEditor.setPanel('myNicPanel');
        myNicEditor.addInstance('bio');
	});
    </script>
    <div id="column-single">
        <h1>Update Profile Details</h1>
        <p />&nbsp;&nbsp;<br />
        <div class="table">
        <table class="listing form" cellpadding="0" cellspacing="0" width="800">
          <tr>
            <th class="full" colspan="2">Please enter your profile information to continue.</th>
          </tr>
          <tr>
            <td class="onecell" width="100%">
                <?php 
				$this->load->helper('display');
				$errors = validation_errors();
				if ($errors) {
					echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
				}
				$form = new Form();
                $form->open('user/profile','detailsForm');
                $form->text('firstName','First Name','required|trim|max_length[300]',($input->post('firstName') ? $input->post('firstName') : $profile->firstName));
                $form->br();
              	$form->text('lastName','Last Name','required|trim|max_length[300]',($input->post('lastName') ? $input->post('lastName') : $profile->lastName));
                $form->space();
              	$form->text('nickName','Nick Name','trim|max_length[100]',($input->post('nickName') ? $input->post('nickName') : $profile->nickName));
                $form->space();
              	$birthArr = explode("-",$profile->dateOfBirth);
				$form->fieldset('',array('class'=>'dateLists'));
				$form->select('birthMonth|birthMonth',getMonths(),'Month',($this->input->post('birthMonth') ? $this->input->post('birthMonth') : $birthArr[1]),'integer');
				$form->nobr();
				$form->select('birthDay|birthDay',getDays(),'Day',($this->input->post('birthDay') ? $this->input->post('birthDay') : $birthArr[2]),'integer');
				$form->nobr();	
				$form->select('birthYear|birthYear',getYears(),'Year',($this->input->post('birthYear') ? $this->input->post('birthYear') :$birthArr[0]),'integer');
				$form->br();
				$form->span('Your birthday is never publicly displayed on the site. it is displayed as your <b>Age</b> in Public Search Results and on your Profile Page.');
				$form->fieldset('');
				$form->text('city','City','trim|max_length[10000]',($input->post('city') ? $input->post('city') : $profile->city));
                $form->br();
              	$form->text('zipCode','Zip Code','trim|max_length[25]',($input->post('zipCode') ? $input->post('zipCode') : $profile->zipCode));
                $form->br();
              	$form->select('country|country',loadCountries(),'Country',($this->input->post('country') ? $this->input->post('country') : $profile->country));
				$form->space();
				$form->text('title','Title','trim|max_length[10000]',($input->post('title') ? $input->post('title') : $profile->title));
                $form->space();
				$form->label('Bio', '', array('class'=>'required'));
				$form->html('<div class="richEditor">');
				$form->html('<div id="myNicPanel" class="nicEdit-panel"></div>');
              	$form->textarea('bio','','trim|max_length[50000]',($input->post('bio') ? $input->post('bio') : $profile->bio));
                $form->html('</div>');
				$form->space();
				$form->fieldset('',array('class'=>'radioGroup'));
              	$gender[] = array('m','Male');
				$gender[] = array('f','Female');
				$form->radiogroup ('gender',$gender,'Gender',($input->post('gender') ? $input->post('gender') : $profile->gender));
				$form->space();
				$responses[] = array('1','Yes');
				$responses[] = array('-1','No');
				$form->fieldset('',array('class'=>'radioGroup'));
				$form->radiogroup ('showTeams',$responses,'Show Teams Publicly',($input->post('showTeams') ? $input->post('showTeams') : $profile->showTeams));
				$form->space();
				$form->fieldset('',array('class'=>'button_bar'));
				$form->submit('Update Profile');
				$form->hidden('mode','edit');
				$form->hidden('id',$profile->userId);
                echo($form->get());
                ?>
                </td>
              </tr>
            </table>
            <p>&nbsp;</p>
          </div>
    </div>
    <p />&nbsp;&nbsp;<br />