    <div id="single-column"> 
        <h1>Change Password</h1>
         <p /><br />
    </div>
    <div id="center-column">
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:525px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Please enter your password information to continue.</td>
        </tr>
        <tr>
            <td>
			<?php 
			$errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span>';
            }
            $form = new Form();
            $form->open('user/change_password','password');
            $form->fieldset();
            $form->password('old','Old Password','required');
            $form->space();
            $form->password('new','New Password','required');
            $form->space();
            $form->password('new_repeat','Repeat New Password','required');
            $form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Change Password');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />