    <div id="single-column"> 
        <h1><?php echo($subTitle); ?></h1>
        <br />
		<?php echo($theContent); ?>
        <p /><br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:525px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Please enter your email address to reset your password.</td>
        </tr>
        <tr>
            <td>
            <?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span>';
            }
            $form = new Form();
            $form->open('user/forgotten_password','forgotten_password');
            $form->fieldset();
            $form->text('email','E-Mail Address','required|valid_email|max_length[500]');
            $form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Reset Password');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />