<?php
/**
 * Name as ID
 * 
 * If set to TRUE an 'id' attribute with the value of the 'name' will be
 * generated automatically in all elements (where applicable)
 */
$nameasid = TRUE;

/**
 * Replace
 * 
 * These parameters set the replace vs. combine behaviour for
 * multi-value attributes defined in $globals and $defaults
 *
 * 1st parameter sets CLASSES replacement behaviour
 * 2nd parameter sets STYLES replacement behaviour
 * 3rd parameter sets SCRIPTS ('on...') replacement behaviour
 */
$replace = 'FALSE|TRUE|TRUE';

/**
 * Break After
 * 
 * Inserts a <br /> tag after these elements (separated by pipe)
 */
$break_after = 'text|password|textarea|upload|select|button';

/**
 * Label Placement
 * 
 * Labels can be placed 'above', 'before' or 'after' the element tag(s)
 * If changed please remember to change your style sheet accordingly
 */
$label_pos = 'before';

/**
 * Label Required Class
 * 
 * This class will be added to the element label if it is required
 */
$label_req_class = 'required';

/**
 * Label Required Flag
 * 
 * This string (or image) will be appended to the label if it is required (e.g. '*')
 */
$label_req_flag = '*';

/**
 * Error Tags
 * 
 * Opening tag of a single error, e.g. '<span class="error">- '
 * Closing tag of a signle error, e.g. '</span>'
 *
 * This allows you to wrap all single errors within a styled element
 */
$error_open = '<div style="color: #c00">- ';
$error_close = '</div>';

/**
 * Error String Tags
 * 
 * Opening tag of the error string, e.g. '<div id="errors">'
 * Closing tag of the error string, e.g. '</div>'
 *
 * This allows you to wrap all single errors within a styled element
 */
$error_string_open = '<div id="errors">';
$error_string_close = '</div>';

/**
 * Error Class
 * 
 * This class will be added to the element and its label if validation rules do not pass
 */
$error_class = 'error';

/**
 * Inline Error
 * 
 * If set to 'TRUE' the error message will be shown directly after the element
 * You can also set the inline error message opening and closing tag.
 */
$error_inline = FALSE;
$error_inline_open = '';
$error_inline_close = '';

/**
 * Error Flag
 * 
 * This class will be added to the element and its label if validation rules do not pass
 * To include the error message with an image title attribute you can use the placeholder {error}
 */
$error_flag = '';

/**
 * No validate
 * 
 * Allows you to force validation off
 */
$novalidate = false;


/**
 * Global Attributes
 * 
 * Define global standard attributes
 */
$globals = array();

/**
 * Default Attributes
 * 
 * Define standard attributes to element types or specific elements (by name)
 */
$defaults = array(
	'label' => array(	// this applies to all labels
		//'class' => 'left'
	),
	'label|checkbox' => array(	// this only applies to labels of checkboxes
		'class' => 'check'
	),
	'label|radio' => array(	// this only applies to labels of radios
		'class' => 'check'
	),
	'text' => array(
		'onkeypress' => "this.style.borderColor='#666'"
	),
	'password' => array(
		'onkeypress' => "this.style.borderColor='#666'"
	),	
	'upload' => array(
		'upload_path' => '/images/',
		'allowed_types' => 'xml|xls|pdf|doc|jpg|gif|png'
	),
	'checkbox' => array(
		'class' => 'check'
	),
	'radio' => array(
		'class' => 'check'
	),
	'submit' => array(
		'class' => 'button'
	),
	'reset' => array(
		'class' => 'button'
	),
	// always define defaults for specific elements (below) after defaults by element type (above)
	'multiple' => array(
		'style' => 'height: 120px'
	)
);

/* End of file form.php */
/* Location: ./application/config/form.php */