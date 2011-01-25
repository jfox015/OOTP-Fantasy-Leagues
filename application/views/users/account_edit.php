    <div id="column-center">
        <h1>Update Account Details</h1>
        <p /><br />
        <div class="content-form">
            <div id="dialog" class="dlg-changepass">
                <div class="dialog_head">
                Please enter your password information to continue.
                </div>
                <div class="dialog_body">
                <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
				}
				$form = new Form();
                $form->open('user/account','detailsForm');
                $form->fieldset('Required Fields');
                $form->label('Username');
				$form->span($account->username,array('style'=>'float:left;'));
				$form->space();
                $form->text('email','E-Mail Address','',($input->post('email') ? $input->post('email') : $account->email));
                $form->space();
                if ($accessLevel == ACCESS_ADMINISTRATE) {
                   $form->select('accessId',loadSimpleDataList('accessLevel'),'Access Level',($input->post('accessId') ? $input->post('accessId') : $account->accessId));
                   $form->space();
                   $form->select('levelId',loadSimpleDataList('userLevel'),'User Level',($input->post('levelId') ? $input->post('levelId') : $account->levelId));
                   $form->space();
                   $form->select('typeId',loadSimpleDataList('userType'),'User Type',($input->post('typeId') ? $input->post('typeId') : $account->typeId));
                   $form->space();
                }
              	$form->fieldset('',array('class'=>'button_bar'));
				$form->submit('Update Account Info');
				$form->hidden('mode','edit');
                echo($form->get());
                ?>
                </div>
                <div class="dialog_foot"></div>
                <br class="clear" clear="all" />
            </div>
            <br class="clear" clear="all" />
        </div>
    </div>
    <p /><br />