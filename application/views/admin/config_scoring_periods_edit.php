<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.datepicker.js"></script>
<script type="text/javascript">
$(function() {
	var today = new Date();
	$("#date_start").datepicker();
	$("#date_end").datepicker();
});
</script>

<div id="center-column">
	<?php include_once('admin_breadcrumb.php'); ?>
    <span id="top-bar"><h1><?php echo($subTitle); ?></h1></span>

    <br clear="all" />
	<?php
    if ( ! function_exists('form_open')) {
		$this->load>helper('form');
	}
	$errors = validation_errors();
	if ($errors) {
		echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
	}
	if ($outMess) {
		echo $outMess;
	}
	echo(form_open($config['fantasy_web_root']."admin/configScoringPeriodsEdit",array("id"=>"detailsForm","name"=>"detailsForm")));
    echo(form_fieldset());
	echo(form_label('Scoring Period ID:','period_id'));
    echo(form_input(array('id'=>'period_id','name'=>'period_id','style'=>'width:125px;border:1px solid black;','value'=>$period_id)));
	echo('<br clear="all"/><br />');
	echo(form_label('Start/End Dates','date_start'));
    echo(form_input(array('id'=>'date_start','name'=>'date_start','style'=>'width:125px;border:1px solid black;','value'=>$date_start)));
	echo(form_input(array('id'=>'date_end','name'=>'date_end','style'=>'width:125px;border:1px solid black;','value'=>$date_end)));
	echo('<br clear="all"/><br />');
	echo("<br /><br />");

    echo(form_fieldset_close());
    echo(form_fieldset('',array('class'=>"button_bar")));
    echo(form_submit('submit',"Submit"));
    echo(form_hidden('submitted',"1"));
    echo(form_fieldset_close());
    echo(form_close()); ?>
    
</div>

<br class="clear" />