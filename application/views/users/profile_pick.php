    <div id="col_l">
    <?php include_once('nav_users.php'); ?>
    </div>
    <div id="col_r">
   	<h1><?php echo($subTitle); ?></h1>
        <br />
        <div class="content-form">
            <h4>Choose a user to view their profile:</h4>
            <p /><br />
            <?php 
			$errors = validation_errors();
			if ($errors) {
				echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
			}
			$form = new Form();
			$form->open('user/profiles','detailsForm');
			$form->select('id|id',$users,'User Id','','required');
			$form->space();
			$form->fieldset('',array('class'=>'button_bar'));
			$form->submit('Submit');
			echo($form->get());
			?>
            
        </div>
    </div>
    <p /><br />