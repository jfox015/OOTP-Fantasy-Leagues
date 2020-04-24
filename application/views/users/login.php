		<!-- BEGIN LOGIN FORM -->
    <div id="center-single"> 
    	<h1><?php print($this->lang->line('user_login_title')); ?></h1>
        <p /><br />
    </div>
    <div id="center-column">
        
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:640px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2"><?php print($this->lang->line('user_login_inst')); ?></td>
        </tr>
        <tr>
            <td>
            <?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">'.$this->lang->line('user_login_errors').'<br /><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
            $form = new Form();
            $form->open('user/login','login');
            $form->fieldset();
            $form->text('username','Username','required|trim',($input->post('username') ? $input->post('username') : ''));
            $form->space();
            $form->password('password','Password','required');
            $form->br();
            $form->span(anchor('/user/forgotten_password','Forgot your password?'),array('class'=>'field_caption'));
            $form->space();
            $form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Login');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
        
    </div>
	<div id="right-column">
    	
	    <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0" border="0">
        <tr class='title'>
            <td style='padding:3px;color:#ff0;'><?php print($this->lang->line('user_login_register_title')); ?></td>
        </tr>
        <tr>
            <td style='padding:12px'>
            <?php print($this->lang->line('user_login_register_body')); ?>
            <br /><br />
            <p style="width:100%; text-align:center;">
            <b><?php echo(anchor('/user/register','REGISTER NOW!',array('class'=>'register'))); ?></b>
     		</p></td>
        </tr>
        </table>
        </div>
		
		<?php 
		if (isset($authenticationType) && $authenticationType == 1) { ?>
        <div class='textbox right-column'>
        <table cellpadding="0" cellspacing="0" border="0">
        <tr class='title'>
            <td style='padding:3px'><?php print($this->lang->line('user_login_activate_title')); ?></td>
        </tr>
        <tr>
            <td style='padding:12px'>
            <?php 
			if (isset($activate_str) && !empty($activate_str)) { 
				print($activate_str."<br /><br />");
			}  // END if
			?>
            </td>
        </tr>
        </table>
        </div>
        <?php 
		} // END if
		?>
    </div>
    <p /><br />