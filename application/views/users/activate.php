	<div id="single-column"> 
        <h1>Account Activation.</h1>
    </div>
    <div id="center-column">
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:525px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Please enter your activation code to continue.</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
            }
			if (isset($outMess) && !empty($outMess)) {
                print($outMess);
            }
            $form = new Form();
            $form->open('user/activate','activate');
            $form->fieldset();
            $form->text('code','Activation Code','required');
            $form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Activate');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
        <br class="clear" />
    	<p /><br />
        Didn't recieve an <b>activation code</b>? 
        <ul>
        	<li>Check your mailboxes <strong>spam</strong> folder. Sometimes they get caught there by accident.</li>
            <li><?php print anchor('/user/resend_activation','Request a new one'); ?></li>
            <li><?php print anchor('/about/contact','Contact the site administrator'); ?> for help.</li>
        </ul>
    </div>