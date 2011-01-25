    <script type="text/javascript">
    $(document).ready(function(){		   
		$('#delete').click(function(){
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>divisions/delete/<?php echo($thisItem['id']); ?>';
		});	
		$('#cancel').click(function(){
			<?php if (isset($thisItem['id']) && $thisItem['id'] != -1) { ?>
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>divisions/info/<?php echo($thisItem['id']); ?>';
			<?php } else { ?>
			history.back(-1);
			<?php } ?>
		});
	});
    </script>
    <div id="left-column">
   	<?php include_once('nav_divisions.php'); ?>
    </div>
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
    	<div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <br class="clear" />
        <?php if (isset($dump) && !empty($dump)) {
			echo("Object Data Dump:<br />".$dump."<br />");
		} ?>
        <div class="table">
        <table class="listing form" cellpadding="0" cellspacing="0">
          <tr>
            <th class="full" colspan="2">Enter the information for this division below.</th>
          </tr>
          <tr>
            <td class="onecell" width="100%">
            <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<div class="error">The following errors were found with your submission:<br /><ul>'.$errors.'</ul></div>';
				}
				echo validation_errors().'<p />';
                $form = new Form();
                $form->open($config['fantasy_web_root'].'divisions/addDivision','division');
                $form->fieldset();
                $form->text('division_name','Division Name','required|trim',($input->post('division_name')) ? $input->post('division_name') : '');
                $form->space();
                $form->hidden('league_id',$league_id);
                $form->fieldset('',array('class'=>'button_bar'));
				$form->submit('Add Division');
                echo($form->get());
                ?>
            </td>
          </tr>
        </table>
        <p>&nbsp;</p>
      </div>
    </div>
    <p /><br />