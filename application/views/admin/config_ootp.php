		<!-- BEGIN REGISTRATION FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        
        <h1><?php echo($subTitle); ?></h1>
        <br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Enter settings information below</td>
        </tr>
        <tr>
            <td>
			<?php 
            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
			if ($outMess) {
                echo $outMess;
            }
            $form = new Form();
            $form->open('admin/configOOTP','configOOTP');
            $form->br();
           	$form->fieldset('General');
            $form->text('start_date','League Start Date:','required',$start_date);
            $form->space();
           	$form->text('current_date','League Current Date:','required',$current_date);
            $form->space();
            $form->text('current_period','Current Fantasy Scoring Period:','required',$config['current_period']);
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
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.datepicker.js"></script>
<script type="text/javascript">
$(function() {
	var today = new Date();
	$("#start_date").datepicker();
	$("#current_date").datepicker();
});
</script>