<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.datepicker.js"></script>
<script type="text/javascript">
$(function() {
	var today = new Date();
	$("#season_start").datepicker({ minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()) });
	$("#draft_start").datepicker();
	$("#draft_end").datepicker();
});
</script>

<div id="center-column">
	<?php include_once('admin_breadcrumb.php'); ?>
    <span id="top-bar"><h1>Set League Dates</h1></span>

    <br clear="all" />
	<?php
    if ( ! function_exists('form_open')) {
		$this->load>helper('form');
	}
	$errors = validation_errors();
	
	echo(form_open($config['fantasy_web_root']."admin/setleagueDates",array("id"=>"detailsForm","name"=>"detailsForm")));
    echo(form_fieldset());
	echo(form_label('Season Start Date:','season_start'));
    echo(form_input(array('id'=>'season_start','name'=>'season_start','style'=>'width:125px;border:1px solid black;','value'=>$season_start)));
	echo('<span class="field_caption">This is the date in real life when you will begin running sims for this season. Be sure to choose a date that is far enough out to allow people to set up and populate their leagues as well as scout available players.</span>');
	echo('<br clear="all"/><br />');
	echo(form_label('Draft Period:','season_start'));
    echo(form_input(array('id'=>'draft_start','name'=>'draft_start','style'=>'width:125px;border:1px solid black;','value'=>$draft_start)));
	echo(form_input(array('id'=>'draft_end','name'=>'draft_end','style'=>'width:125px;border:1px solid black;','value'=>$draft_end)));
	echo('<span class="field_caption">These are the dates in which commisioners can schedule their leagues draft. By the draft start date, all pre-season operations should be complete and the legaue ready to begin simming once all drafts are complete.</span>');
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