    <div id="column_center">
    	<h1><?php echo($subTitle); ?></h1>
        <br />
        <div class="content-form">
            <p /><br />
            <?php 
			if ( ! function_exists('form_open')) {
				$this->load>helper('form');
			}
			$errors = validation_errors();
			if ($errors) {
				echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
			}
			if (isset($attachement) && !empty($attachement)) {
            echo('<label>Current Attachement: </label><br />');
			echo('<a href="'.PATH_BUGS_ATTACHEMENTS.$attachement.'" target="_blank"><img src="'.PATH_IMAGES.'icons/'.ATTACHEMENT_ICON.'" border="0" width="50" height="50" align="left" /></a>');
			} 
			?>
            <br clear="all" /><br /><br />
            <?php
			echo(form_open_multipart("/bug/attachment",array("id"=>"detailsForm","name"=>"detailsForm")));
			echo(form_fieldset());
			echo(form_label("Attachement:","attachementFile"));
			echo(form_upload("attachementFile"));
			?>
            <span class="field_caption">Acceptable file formats include GIF, JPG (JPEG), PNG, ZIP, PDF, DOC, PPT. Max file size is 150Kb</span>
            <?php
			echo(form_fieldset_close());
			echo(form_fieldset('',array('class'=>"button_bar")));
			echo(form_hidden('id',$id));
			echo(form_submit('submit',"Submit"));
			echo(form_hidden('submitted',"1"));
			echo(form_fieldset_close());
			echo(form_close()); ?>
            <p /><br />          
        </div>
    </div>
    <p /><br />