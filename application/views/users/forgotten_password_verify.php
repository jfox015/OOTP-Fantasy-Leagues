    <div id="center-single"> 
        <h1>Password Reset Verification</h1>
        <p /><br />
    </div>
    <div id="center-column">
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:525px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Enter your confirmation code to reset your password.</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span>';
            }
            $form = new Form();
            $form->open('user/forgotten_password_verify','forgotten_password_verify');
            $form->fieldset();
            $form->text('code','Confirmation Code','required|trim');
            $form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Complete Password Reset');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />