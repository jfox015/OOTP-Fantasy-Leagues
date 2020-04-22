		<!-- BEGIN REGISTRATION FORM -->
	<div id="subPage">
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
            $form->open('admin/configGame','configGame');
            $form->br();
           	$form->fieldset('General Details');
            $form->text('site_name','Site name','required|trim',($input->post('site_name') ? $input->post('site_name') : $config['site_name']),array("class"=>"longtext"));
            $form->space();
            $form->text('ootp_league_name','OOTP League Name','required|trim',($input->post('ootp_league_name') ? $input->post('ootp_league_name') : $config['ootp_league_name']),array("class"=>"longtext"));
            $form->space();
            $form->text('ootp_league_abbr','OOTP League Abbreviation','required',($input->post('ootp_league_abbr') ? $input->post('ootp_league_abbr') : $config['ootp_league_abbr']),array("class"=>"longtext"));
            $form->br();
           	$form->text('fantasy_web_root','Fantasy League Root URL','required|trim',($input->post('fantasy_web_root') ? $input->post('fantasy_web_root') : $config['fantasy_web_root']),array("class"=>"longtext"));
            $form->space();
            $form->text('ootp_league_id','OOTP League ID','required',($input->post('ootp_league_id') ? $input->post('ootp_league_id') : $config['ootp_league_id']));
            $form->br();
			$form->select('ootp_version|ootp_version',loadOOTPVersions(),'OOTP Version',($this->input->post('ootp_version') ? $this->input->post('ootp_version') : $config['ootp_version']));
			$form->space();
			$form->select('timezone|timezone',loadTimezones(),'Timezone',($this->input->post('timezone') ? $this->input->post('timezone') : $config['timezone']));
			$form->space();
			$responses[] = array('1','Enabled');
			$responses[] = array('-1','Disabled');       
			$form->fieldset('User Management');
			$form->select('user_activation_method|user_activation_method',loadSimpleDataList('activationType'),'Activation Method',($this->input->post('user_activation_method')) ? $this->input->post('user_activation_method') : $config['user_activation_method'],'required');
			$form->space();
            $form->select('primary_contact|primary_contact',$adminList,'Primary Contact',($this->input->post('primary_contact')) ? $this->input->post('primary_contact') : $config['primary_contact'],'required');
			$form->space();
			$form->fieldset('OOTP Links and Tools');
			$form->text('ootp_html_report_path','OOTP HTML Reports URL','required|trim',($input->post('ootp_html_report_path') ? $input->post('ootp_html_report_path') : $config['ootp_html_report_path']),array("class"=>"longtext"));
            $form->nobr();
			$form->span("Web URL to your OOTP HTML reports folder.",array('class'=>'field_caption'));
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('ootp_html_report_links',$responses,'OOTP HTML Reports Links',($this->input->post('ootp_html_report_links') ? $this->input->post('ootp_html_report_links') : $config['ootp_html_report_links']),'required');
			$form->fieldset();
			$form->span('When enabled, displays links to OOTP HTML Reports in the global OOTP nav bar at the top of the site.',array('class'=>'field_caption'));
           	$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('stats_lab_compatible',$responses,'StatsLab Compatibility Mode',($this->input->post('stats_lab_compatible') ? $this->input->post('stats_lab_compatible') : $config['stats_lab_compatible']),'required');
			$form->fieldset();
            $form->span('Enable this option if you are running <b>StatsLab</b> using the same <em>MySQL File Load Path</em> as this fantasy league mod.',array('class'=>'field_caption'));
           	$form->space();
			 $form->br();
			$form->text('stats_lab_url','StatasLab URL','trim',($input->post('stats_lab_url') ? $input->post('stats_lab_url') : $config['stats_lab_url']),array("class"=>"longtext"));
            $form->nobr();
			$form->span("Web URL to a StatsLab implementation for your league site. URL will display in OOT HTML nav bar (if enabled)",array('class'=>'field_caption'));
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('google_analytics_enable',$responses,'Google Analytics',($this->input->post('google_analytics_enable') ? $this->input->post('google_analytics_enable') : $config['google_analytics_enable']),'required');
			$form->fieldset();
            $form->text('google_analytics_tracking_id','Google Analytics Tracking ID','trim',($input->post('google_analytics_tracking_id') ? $input->post('google_analytics_tracking_id') : $config['google_analytics_tracking_id']));
			$form->space();
            $form->fieldset('File Settings');
			$form->space();
			$form->span('<b style="color:#c00">WARNING</b>: Do not change these paths unless you have moved your files for some reason.');
           	$form->space();
            $form->text('sql_file_path','MySQL File Load Path','required|trim',($input->post('sql_file_path') ? $input->post('sql_file_path') : $config['sql_file_path']),array("class"=>"longtext"));
            $form->nobr();
			$form->span("Server path to MySQL Data Upload Dir. NO TRAILING SLASH.",array('class'=>'field_caption'));
			$form->space();
           	$form->text('ootp_html_report_root','HTML Report File Path','required|trim',($input->post('ootp_html_report_root') ? $input->post('ootp_html_report_root') : $config['ootp_html_report_root']),array("class"=>"longtext"));
            $form->nobr();
			$form->span("Server path to OOTP HTML Reports Dir. NO TRAILING SLASH.",array('class'=>'field_caption'));
			$form->space();
           	$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('limit_load_all_sql',$responses,'Limit <b>Load All Files</b> to only required?',($this->input->post('limit_load_all_sql') ? $this->input->post('limit_load_all_sql') : $config['limit_load_all_sql']),'required');
			$form->fieldset();
			$form->text('max_sql_file_size','Max SQL File Size','required|trim|number',($input->post('max_sql_file_size') ? $input->post('max_sql_file_size') : $config['max_sql_file_size']));
            $form->space();
           	$form->span('Specify Max File Size in Megabytes',array('class'=>'field_caption'));
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
    <p><br />