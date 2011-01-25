		<!-- BEGIN LOGIN FORM -->
    <div id="center-single"> 
    <h1>Login</h1>
        <p /><br />
    </div>
    <div id="center-column">
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:640px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Please enter your login information to continue</td>
        </tr>
        <tr>
            <td>
            <?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br /><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
            $form = new Form();
            $form->open('user/login','login');
            $form->fieldset();
            $form->text('username','Username','required|trim',($input->post('username') ? $input->post('username') : ''));
            $form->space();
            $form->password('password','Password','required');
            $form->br();
            $form->span(anchor('/user/forgotten_password','Forogt your password?'),array('class'=>'field_caption'));
            $form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Login');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
        <?php 
		$this->ci =& get_instance();
		if ($this->ci->config->item('email_activation')) { ?>
        Did you recieve an <b>activation code</b>? If so, <?php echo(anchor('/user/activate','enter your activation code')); ?> to begin using the site.
        <?php } ?>
    </div>
	<div id="right-column">
    
	    <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0">
        <tr class='title'>
            <td style='padding:3px'>Not a member yet?</td>
        </tr>
        <tr>
            <td style='padding:12px'>
            What are you waiting for? 
            <br /><br />
            Jump in and test your skills at running an Out of the Park 
            baseball Fantasy League Team.
            <br /><br />
            <p style="width:100%; text-align:center;">
            <b><?php echo(anchor('/user/register','REGISTER NOW!',array('style'=>'font-size:14px;font-weight:bold;color:#c00;'))); ?></b>
     		</p></td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />