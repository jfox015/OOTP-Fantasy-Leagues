		<!-- BEGIN LOGIN FORM -->
    <div id="col_s">
        <h1>Account Activation.</h1>
		<p />
        Please enter your activation code from the registration email.<br />
        <div class="content-form">
            <div id="dialog">
                <div class="dialog_head">
                Activation Code
                </div>
                <div class="dialog_body">
                <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
				}
				$form = new Form();
                $form->open('home/activate','activate');
                $form->fieldset();
                $form->text('code','Activation Code','required');
                $form->space();
                $form->fieldset('',array('class'=>'button_bar'));
				$form->submit('Activate');
                echo($form->get());
                ?>
                </div>
                <div class="dialog_foot"></div>
                <br class="clear" clear="all" />
            </div>
            <br class="clear" clear="all" />
        </div>
        Didn't recieve an <b>activation code</b>? Request a new one or contact the site administrator for help.
    </div>
    <p /><br />