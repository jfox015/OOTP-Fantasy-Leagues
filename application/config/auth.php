<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Misc.
 **/
$config['login_redirect'] = '/user/login';
$config['login_attempt_max'] = 3;
$config['session_auth'] = 'id';
$config['password_crypt'] = '$1$l135c355$';
/**
 * Tables.
 **/
$config['tables']['user_access'] = 'list_access_levels';
$config['tables']['user_levels'] = 'list_user_levels';
$config['tables']['user_types'] = 'list_user_types';
$config['tables']['users_core'] = 'users_core';
$config['tables']['users_meta'] = 'users_meta';


/**
 * Default group, use name
 */
$config['default_level'] = '1';
 
/**
 * Meta table column you want to join WITH.
 * Joins from users.id
 **/
$config['join'] = 'userId';

/**
 * Columns in your meta table,
 * id not required.
 **/
$config['columns'] = array('firstName', 'lastName', 'gender','avatar');

/**
 * A database column which is used to
 * login with.
 **/
$config['unique_field'] = 'username';

/**
 * Email Activation for registration
 **/
$config['email_activation'] = false;

/**
 * Folder where email templates are stored.
 * Default : redux_auth/
 **/
$config['email_templates'] = 'email_templates/';

/**
 * Salt Length
 **/
$config['salt'] = '$1Ac98H5dp$';
$config['salt_length'] = 10;

/**
 * Enable auth loggins
 * Default : redux_auth/
 **/
$config['log_auth'] = true;
$config['tables']['access_log'] = 'log_access_users';
	
?>