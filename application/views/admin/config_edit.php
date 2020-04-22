		<!-- BEGIN LOGIN FORM -->
	<div id="subPage">
        <?php include_once('admin_breadcrumb.php'); ?>
        <div id="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        <br />
		<?php echo($theContent); ?>
        <br /><br />
        <div class="content-form">
            <div id="editor">
                <div class="dialog_head">
                Edit config options below.
                </div>
                <div class="dialog_body">
                <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
				}
				$form = new Form();
        		$form->open($config['fantasy_web_root'].'admin/updateConfig/','detailsForm|detailsForm');
        		
        		$form->fieldset('Game Details');
        		
        		$form->text('ootp_league_id','OOTP League ID','required|trim',$this->input->post('ootp_league_id'));
        		$form->span('Usually 100. Minor leagues are not recommended.',array('class'=>'field_caption'));
        		$form->space();
        		$form->textarea('description','Description','required',$this->input->post('description'));
        		$form->span('Please be as thorough and descriptive of the problems as possible. Please include all steps leading up to and after the bug occured to help in recreating the bug.',array('class'=>'field_caption'));
        		$form->space();
        		$form->fieldset('Optional Details');
        		$form->text('url','URL','trim|max_length[1000]',$this->input->post('url'),array('class'=>'longtext'));
        		$form->br();
        		$form->select('os',loadSimpleDataList('os'),'Platform OS',$this->input->post('os'));
        		$form->br();
        		$form->select('browser',loadSimpleDataList('browser'),'Browser',$this->input->post('browser'));
        		$form->br();
        		$form->text('browVersion','Browser Version','trim|max_length[500]',$this->input->post('browVersion'));
        		$form->br();
        		$form->fieldset('',array('class'=>'button_bar'));
		        $form->submit();
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