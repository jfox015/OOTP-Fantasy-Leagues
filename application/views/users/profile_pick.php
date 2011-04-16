    <div id="single-column"> 
   	<h1><?php echo($subTitle); ?></h1>
        <br />
        <?php 
		$errors = validation_errors();
        if ($errors) {
            echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
        }
		?>
       	<div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:525px;">
        <tr class='title'>
            <td style='padding:6px'>Choose a user to view their profile</td>
        </tr>
        <tr>
            <td>
            <?php 
			$form = new Form();
			$form->open('user/profiles','detailsForm');
			$form->select('id|id',$users,'User Id','','required');
			$form->space();
            $form->fieldset('',array('class'=>'button_bar'));
			$form->submit('Submit');
			echo($form->get());
			?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />