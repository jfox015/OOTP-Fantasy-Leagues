    <div id="column-single">
   	<h1><?php echo($subTitle); ?></h1>
        <br />
        <div class="content-form">
            <p><br />
            <?php 
			if ( ! function_exists('form_open')) {
				$this->load>helper('form');
			}
			$errors = validation_errors();
			if ($errors) {
				echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
			}
			?>
            <label>Current Avatar</label>
            <?php 
			$avatar = (isset($avatar) && !empty($avatar)) ? $avatar : DEFAULT_AVATAR;
			echo('<img src="'.PATH_USERS_AVATARS.$avatar.'" border="0" width="50" height="50" align="left" />');
			?>
            <br clear="all" /><br /><br />
            <?php
			echo(form_open_multipart("/user/avatar",array("id"=>"detailsForm","name"=>"detailsForm")));
			echo(form_fieldset());
			echo(form_label("Avatar:","avatarFile"));
			echo(form_upload("avatarFile"));
			?>
            <span class="field_caption">Acceptable image formats include GIF, JPEG (JPG) and PNG. Max file size is 50Kb</span>
            <?php
			echo(form_fieldset_close());
			echo(form_fieldset('',array('class'=>"button_bar")));
			echo(form_submit('submit',"Submit"));
			echo(form_hidden('submitted',"1"));
			echo(form_fieldset_close());
			echo(form_close()); ?>
            <p><br />          
        </div>
    </div>
    <p><br />