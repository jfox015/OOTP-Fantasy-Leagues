<div id="one-column">
	<h1><?php print($this->lang->line('user_login_title'); ?></h1>
	<?php echo validation_errors(); ?>
    <p />
	<?php echo form_open('user/login'); ?>
    <label for="username">Username</label>
    <?php echo form_input('username', set_value('username')); ?>
	<p /><br />
    <label for="password">Password</label>
    <?php echo form_password('password'); ?>
    <p />
    <?php echo form_submit('submit', 'Login'); ?>
	<?php echo form_close(''); ?>
</div>