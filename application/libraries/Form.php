<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Form Generator Class
 * 
 * This class will help you to save
 * time in building forms with CodeIgniter
 * If you have code improvements please 
 * contact me and I will update the code!
 * 
 * @license		MIT License
 * @author		Frank Michel
 * @link		http://frankmichel.com
 * @docu		http://frankmichel.com/formgenlib
 * @email		info@frankmichel.com
 * 
 * @file		Form.php
 * @version		0.1.2
 * @date		05/28/2009
 * 
 * Copyright (c) 2009 Frank Michel
 */
class El {

	var $unique = array();
	
	var $nameasid = FALSE;
	
	var $error_inline = FALSE;
	var $error_inline_open = '<span>';
	var $error_inline_close = '</span>';
	var $error_flag;
	
	var $label_pos = 'before';
	var $label_req_class;
	var $label_req_flag;	

	// --------------------------------------------------------------------------	

	var $atts = array();

	var $_valid_atts = array(
		'label'	=> 'accesskey|onblur|onfocus|class|style|title|dir|lang', // 'for' not included, supplied with form helper function
		'form'		=> 'action|accept|accept-charset|enctype|method|name|onreset|onsubmit|target',
		'input'	=> 'accept|accesskey|align|alt|checked|disabled|ismap|maxlength|name|onblur|onchange|onfocus|onselect|readonly|size|src|tabindex|type|usemap|value',
		'select'	=> 'disabled|multiple|onblur|onchange|onfocus|size|tabindex', // 'name' not included, supplied with form helper function
		'textarea'	=> 'cols|rows|accesskey|disabled|name|onblur|onchange|onfocus|onselect|readonly|tabindex|value', // 'value' added, supplied with form helper function
		'button'	=> 'accesskey|disabled|name|onblur|onfocus|tabindex|type|value|content', // 'content' added, supplied with form helper function
		'*'		=> 'class|id|style|title|dir|lang|onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup'
	);

	var $error;	// full error with open/close	
	var $error_message; // error message only
	
	var $upload_path;
	var $allowed_types;
	var $overwrite = FALSE;
	var $max_size = 0;
	var $max_width = 0;
	var $max_height = 0;
	var $max_filename = 0;
	var $encrypt_name = FALSE;
	var $remove_spaces = TRUE;
	
	// --------------------------------------------------------------------------	
	
	function El($info, $config=array()) 
	{
		// set config vars
		foreach ($config as $key=>$value)
		{
			if ($value) $this->{$key} = $value;
		}

		// load globals
		$merged = $this->_make_info(@$this->globals);

		// merge defaults (by type)
		$merged = $this->_make_info(@$this->defaults[$info['type']], $merged);
		
		// merge defaults (by element)
		if (array_key_exists('name', $info)) $merged = $this->_make_info(@$this->defaults[$info['name']], $merged);
		
		// merge element info
		$merged = $this->_make_info($info, $merged);

		$this->_make_properties($merged);
	}

	/**
	 * Make Info
	 * 
	 * Merge (combine or replace) element info
	 */
	function _make_info($source=NULL, $base=array()) // $source = NAME or TYPE of element
	{
		if (is_array($source))
		{
			$replace = explode('|', $this->replace);
			if (count($replace) != 3) show_error(FGL_ERR.'Wrong parameter count for $replace in config file.');

			foreach ($source as $att=>$value)
			{
				switch ($att)
				{
					// classes
					case 'class':
						if ($replace[0] == 'TRUE') // replace
						{
							$base['class'] = $value;
						}
						else // combine
						{	
							$old = explode(' ', @$base['class']);
							$this->_trim_array($old);
	
							$new = explode(' ', $value);
							$this->_trim_array($new);
	
							$merged = array_unique(array_merge($old, $new));
							$base['class'] = implode(' ', $merged);
						}
					break;
					
					// styles
					case 'style':
						$old = array();
						$pair = array();
						$old_vals = explode(';', @$base[$att]);
						$this->_trim_array($old_vals);
						foreach ($old_vals as $k=>$v) $pair[$k] = explode(':', $v);
						if (isset($pair)) foreach ($pair as $line) $old[trim($line[0])] = trim($line[1]);

						$new = array();
						$pair = array();
						$new_vals = explode(';', $value);
						$this->_trim_array($new_vals);
						foreach ($new_vals as $k=>$v) $pair[$k] = explode(':', $v);
						if (isset($pair)) foreach ($pair as $line) $new[trim($line[0])] = trim($line[1]);

						if ($replace[1] == 'TRUE') // replace
						{
							foreach ($new as $key=>$value)
							{
								$old[$key] = $value;
							}
							foreach ($old as $key=>$value)
							{
								$style[] = $key.':'.$value;
							}
						}
						else // preserve
						{						
							$style = array();
							$merged = array_merge($new, $old); // preserve previously set values
							foreach ($merged as $key=>$value)
							{
								if ($key && $value) $style[] = $key.':'.$value;
							}
						}
						$base['style'] = implode(';', $style);						
					break;
					
					// javascript event handlers
					case (substr($att, 0, 2) == 'on'):
						if ($replace[2] == 'TRUE') // replace
						{
							$base[$att] = $value;
						}
						else // combine
						{					
							$old = explode(';', @$base[$att]);
							$this->_trim_array($old);
												
							$new = explode(';', $value);
							$this->_trim_array($new);
	
							$merged = array_unique(array_merge($old, $new));
							$base[$att] = implode(';', $merged);
						}
					break;
					
					default:
					// replace all other attributes by default
					$base[$att] = $source[$att];
				}
			}
		}

		return $base;
	}	

	/**
	 * Make Properties
	 * 
	 * Convert element info to properties and attribute array
	 */
	function _make_properties($info) 
	{
		// add maxlength attribute to element, if max_length[] rule was set
		if (@$info['rules'] && strstr($info['rules'], 'max_length'))
		{
			$rules = explode('max_length[', $info['rules']);
			$rules = explode(']', $rules[1]);
			$info['maxlength'] = $rules[0];
		}

		// add maxlength attribute to element, if exact_length[] rule was set
		if (@$info['rules'] && strstr($info['rules'], 'exact_length'))
		{
			$rules = explode('exact_length[', $info['rules']);
			$rules = explode(']', $rules[1]);
			$info['maxlength'] = $rules[0];
		}
		
		// set unique id for internal element access
		if (@$info['name'])
		{ 
			$info['name'] = preg_replace('/ /', '_', $info['name']); // convert spaces to underscores
	
			// checkboxes and radio buttons
			$types = array('checkbox', 'radio');			
			if (in_array($info['type'], $types))
			{
				if (@!$info['id'])
				{
					$unique = $this->_get_unique();
					$info['id'] = $unique;		// checkboxes and radio buttons always have labels, therefore need an id
					$info['unique'] = $unique;	// unique id also becomes internal element access id
				}
				else
				{
					$info['unique'] = $info['id']; // use given id for internal element access id, if provided
				}
			}
			// other elements
			else
			{
				if ($this->nameasid && @!$info['id']) $info['id'] = $info['name'];	// set 'id' as 'name'
				
				if (@$info['label'] && @!$info['id']) 
				{	
					$unique = $this->_get_unique();
					$info['id'] = $unique;		// id is required if a label was specified
					$info['unique'] = $unique;	// unique id also becomes internal element access id
				}
				elseif ($info['id'])
				{
					$info['unique'] = $info['id']; // use given id for internal element access id, if provided
				}
				else
				{
					$info['unique'] = $info['name']; // if no label or id was provided, use the elements name as the internal element id
				}
			}
			
			// set checkbox, radio button and select names as arrays
			$types = array('checkbox', 'radio', 'select');
			if (in_array($info['type'], $types)) 
			{
				//$info['name'] .= '[]';
			}
		}
		elseif ($info['type'] == 'image') 
		{
			if (!@$info['name']) $info['name'] = 'submitimg';
			$info['unique'] = $info['name'];
			if ($this->nameasid && @!$info['id']) $info['id'] = $info['name'];	// set 'id' as 'name'
		}
		elseif ($info['type'] == 'submit') 
		{
			if (!@$info['name']) $info['name'] = 'submit';
			$info['unique'] = $info['name'];
			if ($this->nameasid && @!$info['id']) $info['id'] = $info['name'];	// set 'id' as 'name'
		}
		elseif ($info['type'] == 'reset')
		{
			if (!@$info['name']) $info['name'] = 'reset';
			$info['unique'] = $info['name'];
			if ($this->nameasid && @!$info['id']) $info['id'] = $info['name'];	// set 'id' as 'name'
		}
		else
		{
			// elements without a name attribute
			$info['unique'] = $this->_get_unique(); // assign unique internal element access id
		}
		
		foreach ($info as $key=>$value) 
		{
			$this->atts[$key] = $value; // save attributes in atts[] array
			$this->$key = $value; // make property->value pair
		}
	}

	/**
	 * Get Unique
	 * 
	 * Returns a unique id for an element
	 */	
	function _get_unique($name='')
	{
		$unique = substr(md5(mt_rand(0,2147483647).mt_rand(0,2147483647)), 0, 5);
		while (@in_array($unique, $this->unique) || is_numeric(substr($unique, 0, 1)))
		{
			$unique = substr(md5(mt_rand(0,2147483647).mt_rand(0,2147483647)), 0, 5);
		}
		return $unique;
	}
	
	/**
	 * Trim Array
	 * 
	 * Trims all keys and values of an array
	 */		
	function _trim_array(&$array)
	{
		$arr = array();
		foreach ($array as $k=>$v)
		{
			if (trim($v)) $arr[trim($k)] = trim($v);
		}
		$array = $arr;
	}	

	/**
	 * Filter Attributes
	 * 
	 * Filters only valid attributes used with CodeIgniter form_helper function
	 */
	function _filter_atts($tag)
	{
		$atts = array();
		foreach ($this->atts as $key=>$value) 
		{
			if (strstr(@$this->_valid_atts[$tag], $key) || strstr(@$this->_valid_atts['*'], $key))
			{
				$atts[$key] = $value;
			}
		}
		if (count($atts)) return $atts;
	}

	/**
	 * Attributes String
	 * 
	 * Returns attributes as string
	 */
	function _att_string($atts=array())
	{
		$array = array();

		foreach ($atts as $att=>$val) 
		{
			$array[] = $att.'="'.$val.'"';
		}
		
		if (count($array)) 
		{
			$str = implode(' ', $array);
			return $str;
		}
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Add Margin
	 * 
	 * Adds 'margin' to the element
	 */	
	function add_margin($margin, $pos='')
	{
		$old_vals = @explode(';', $this->atts['style']);
		$this->_trim_array($old_vals);
		foreach ($old_vals as $k=>$v) $pair[$k] = explode(':', $v);
		
		if (isset($pair)) 
		{
			foreach ($pair as $line) $old[trim($line[0])] = trim($line[1]);
		}
		else
		{
			$old = array();
		}

		if (!$pos) $pos = 'left';
		$new['margin-'.$pos] = $margin.'px';

		$style = array();
		$merged = array_merge($old, $new);
		foreach ($merged as $key=>$value) 
		{
			if ($key && isset($value)) $style[] = $key.':'.$value;
		}
		$this->atts['style'] = implode(';', $style);
		
		return $this;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Make Label
	 * 
	 * Adds label to the label position: before|after|above the element
	 */
	function _make_label($pos='')
	{
		if ($pos) $pos = explode('|', $pos);
		if ($this->label && (!$pos || in_array($this->label_pos, $pos)))
		{
			$req = @strstr($this->rules, 'required');
			
			$for = (!@$this->for) ? @$this->id : $this->for;
			$type = $this->atts['type'];

			if ($type == 'label') 
			{
				$atts = $this->_filter_atts('label'); // only allow attributes if element is of 'label' type
			}
			else
			{
				$atts = array();
			}

			// load globals
			$merged = $this->_make_info(@$this->globals);
			
			// merge defaults for type 'label'
			$merged = $this->_make_info(@$this->defaults['label'], $merged);
			
			// merge defaults for type 'label' belonging to specific element type
			$merged = $this->_make_info(@$this->defaults['label|'.$type], $merged);
			
			// merge filtered attributes
			$atts = $this->_make_info($atts, $merged);

			if ($req && $type != 'checkbox' && $type != 'radio' && @$this->label_req_class) @$atts['class'] .= ' '.$this->label_req_class;
			if ($req && $type != 'checkbox' && $type != 'radio' &&  @$this->label_req_flag) $this->label .= $this->label_req_flag;
		
			$la = form_label($this->label, $for, $atts);
			if ($pos && $this->label_pos == 'above') $la .= '<br />';
			
			return $la;
		}
	}

	/**
	 * Make Error
	 * 
	 * Flags an error message
	 */		
	function _make_error() 
	{
		if ($this->error_inline && $this->error_message) 
		{
			if ($this->error_flag)
			{
				$flag = str_replace('{error}', $this->error_message, $this->error_flag);
				return $flag;
			}
			else
			{
				return $this->error_inline_open.$this->error_message.$this->error_inline_close;
			}
		}
	}	

	/**
	 * Elements
	 * 
	 * Handles the element creation
	 */	
	function fieldset()
	{
		$atts = $this->_filter_atts('fieldset');
		$el = form_fieldset($this->legend, $atts);
		return $el;
	}

	function fieldset_close() 
	{
		$el = form_fieldset_close();
		if (@$this->after) $el .= $this->after;
		return $el;
	}
	
	function hidden()
	{
		$el = form_hidden($this->name, $this->value);
		return $el;
	}

	function label() 
	{
		$el = $this->_make_label();
		if (@$this->after) $el .= $this->after;
		return $el;
	}

	function text() 
	{
		$atts = $this->_filter_atts('input');
		$el = $this->_make_label('above|before');
		$el .= form_input($atts);
		$el .= $this->_make_label('after');
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}
	
	function password() 
	{
		$atts = $this->_filter_atts('input');
		$el = $this->_make_label('above|before');
		$el .= form_password($atts);
		$el .= $this->_make_label('after');
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}	
	
	function textarea() 
	{
		$atts = $this->_filter_atts('textarea');
		$el = $this->_make_label('above|before');
		$el .= form_textarea($atts);
		$el .= $this->_make_label('after');
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}

	function upload() 
	{
		$atts = $this->_filter_atts('input');	
		$el = $this->_make_label('above|before');
		$el .= form_upload($atts);
		$el .= $this->_make_label('after');
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}
	
	function select() 
	{
		$atts = $this->_filter_atts('select');
		if (@$atts['multiple']) $atts['multiple'] = 'multiple';		
		$atts = $this->_att_string($atts);
		$el = $this->_make_label('above|before');	
		$el .= form_dropdown($this->name, $this->options, $this->selected, $atts);
		$el .= $this->_make_label('after');
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}

	function checkbox() 
	{
		$atts = $this->_filter_atts('input');
		$el = form_checkbox($atts);
		$el .= $this->_make_label();
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}	
		
	function radio() 
	{
		$atts = $this->_filter_atts('input');
		$el = form_radio($atts);
		$el .= $this->_make_label();
		$el .= $this->_make_error();
		if (@$this->after) $el .= $this->after;
		return $el;
	}

	function button() 
	{
		$atts = $this->_filter_atts('button');
		$el = form_button($atts);
		if (@$this->after) $el .= $this->after;
		return $el;
	}

	function image() 
	{
		$atts = $this->_filter_atts('input');
		if (@$this->after) $el .= $this->after;
		return form_input($atts);
	}

	function submit() 
	{
		$atts = $this->_filter_atts('input');
		if (@$this->after) $el .= $this->after;
		return form_submit($atts);
	}

	function reset() 
	{
		$atts = $this->_filter_atts('input');
		if (@$this->after) $el .= $this->after;
		return form_reset($atts);
	}

	function span() 
	{
		$atts = $this->_filter_atts('span');
		if ( ! empty($atts)) $atts = ' '.$this->_att_string($atts);
		$el = '<span'.$atts.'>'.$this->content."</span>";
		if (@$this->after) $el .= $this->after;
		return $el;
	}

	function html() 
	{
		return $this->content;
	}
	
	function br() 
	{
		$el = (@$this->clear) ? '<br style="clear:both" />' : '<br />';
		return $el;
	}
	
	function hr() 
	{
		$atts = $this->_filter_atts('*');
		$atts = $this->_att_string($atts);
		$el = (@$this->clear) ? '<hr style="clear:both" '.$atts.' />' : '<hr '.$atts.' />';
		return $el;
	}		

	function space() 
	{
		$el = (@$this->clear) ? '<br style="clear:both" /><br />' : '<br /><br />';
		return $el;
	}		

	/**
	 * Get
	 * 
	 * Gets the element
	 */		
	function get()
	{
		$func = $this->type;
		return $this->$func();
	}	
}

// --------------------------------------------------------------------------

class Form {

	var $CI;

	var $lang;
	var $fieldsets = 0;
	var $indented = 0;
	var $columns = 0;
	
	var $action;
	var $method = 'post';
	var $multipart = FALSE;
	var $atts = array();

	var $_elements = array();
	var $_labels = array();
	var $_aliases = array();
	var $_names = array();

	var $_last_accessed;
	var $_output;

	var $_submit_name = array();
	var $_posted;
		
	var $_files = array();
	var $_data = array();

	var $_onsuccess = array();
	var $_postprocess = array();
		
	var $error = array();
	var $errors;
	
	var $error_string; // for internal use only

	var $error_open = '<div>'; // must be provided, will be replaced
	var $error_close = '</div>';

	var $error_string_open;
	var $error_string_close;

	var $error_class;
	
	var $model;
	var $model_method;
	var $model_data;
	
	var $valid = FALSE;
	var $validated = FALSE;
	
	// --------------------------------------------------------------------------
	
	var $config = array();
	
	// --------------------------------------------------------------------------
	
	function Form() 
	{
		// set CI instance
		$this->CI =& get_instance();
		$this->CI->load->helper('form');
		$this->config();
	}
	
	/**
	 * Config
	 * 
	 * Writes configuration values into config array
	 */	
	function config($key=NULL)
	{
		if (!defined('FGL_ERR')) define('FGL_ERR', '<b>Form Generation Library</b><br />');
		
		if (is_file(APPPATH."config/form.php")) 
		{
			include(APPPATH."config/form.php");

			// settings used in EL class
			$this->config['globals'] = (array_key_exists(1, $globals)) ? $globals[$key] : $globals;
			$this->config['defaults'] = (array_key_exists(1, $defaults)) ? $defaults[$key] : $defaults;
			$this->config['replace'] = (is_array($replace)) ? $replace[$key] : $replace;
			
			$this->config['nameasid'] = (is_array($nameasid)) ? $nameasid[$key] : $nameasid;

			$this->config['label_pos'] = (is_array($label_pos)) ? $label_pos[$key] : $label_pos;
			$this->config['label_req_class'] = (is_array($label_req_class)) ? $label_req_class[$key] : $label_req_class;
			$this->config['label_req_flag'] = (is_array($label_req_flag)) ? $label_req_flag[$key] : $label_req_flag;			

			$this->config['error_inline'] = (is_array($error_inline)) ? $error_inline[$key] : $error_inline;
			$this->config['error_inline_open'] = (is_array($error_inline_open)) ? $error_inline_open[$key] : $error_inline_open;
			$this->config['error_inline_close'] = (is_array($error_inline_close)) ? $error_inline_close[$key] : $error_inline_close;
	
			$this->config['error_flag'] = (is_array($error_flag)) ? $error_flag[$key] : $error_flag;
			
			$this->config['novalidate'] = (is_array($novalidate)) ? $novalidate[$key] : $novalidate;
			// settings used in FORM class
			$error_open = (is_array($error_open)) ? $error_open[$key] : $error_open;
			if ($error_open) $this->error_open = $error_open;
			
			$error_close = (is_array($error_close)) ? $error_close[$key] : $error_close;
			if ($error_close) $this->error_close = $error_close;
				
			$this->error_string_open = (is_array($error_string_open)) ? $error_string_open[$key] : $error_string_open;
			$this->error_string_close = (is_array($error_string_close)) ? $error_string_close[$key] : $error_string_close;
	
			$this->error_class = (is_array($error_class)) ? $error_class[$key] : $error_class;

			$break_after = (is_array($break_after)) ? $break_after[$key] : $break_after;
			$this->break_after = explode('|', $break_after);
		}
		else
		{
			show_error(FGL_ERR.'Config file could not be loaded.');
		}

		return $this;		
	}	
	
	/**
	 * Make Array
	 * 
	 * Converts comma separated list to array
	 */
	function _make_array($list) 
	{
		if (!is_array($list)) 
		{
			$list = explode(',', $list);
			foreach ($list as $key=>$value)
			{
				$list[$key] = trim($value);
			}
		}
		return $list;
	}		

	/**
	 * Attributes
	 * 
	 * Converts attribute string or array into array
	 */	
	function _make_info($atts=array(), $divider1=',', $divider2='=') 
	{
		if (is_array($atts)) 
		{
			return $atts;
		} 
		else 
		{
			$array = array();
			if ($atts) 
			{
				$splits = explode($divider1, $atts);
				foreach ($splits as $val)
				{
					if (!strstr($val, $divider2)) show_error(FGL_ERR.'attribute not well formed: \''.$val.'\' (\'attribute=\' missing)');
					
					$split = explode($divider2, $val, 2);
					$array[trim($split[0])] = trim(str_replace('"', '', $split[1])); // strip double quotes
				}
			}
			return $array;
		}
	}

	/**
	 * Make Name/ID
	 * 
	 * Splits piped name|id values and adds them to $info
	 */		
	function _make_nameid($nameid, &$info)
	{
		if (strstr($nameid, '|'))
		{
			$nameid = explode('|', $nameid);
			$info['name'] = $nameid[0];
			$info['id'] = $nameid[1];
		}
		else
		{
			$info['name'] = $nameid;
		}
	}

	/**
	 * Make Value/ID
	 * 
	 * Splits piped value|id values and adds them to $info
	 */			
	function _make_valueid($valid, &$info)
	{
		if (strstr($valid, '|'))
		{
			$valid = explode('|', $valid);
			$info['value'] = $valid[0];
			$info['id'] = $valid[1];
		}
		else
		{
			$info['value'] = $valid;
		}
	}		

	/**
	 * Check Name
	 * 
	 * Checks if element name was already used
	 */		
	function _check_name($name, $type='') {
		if ($type && !$name) show_error(FGL_ERR.'No name specified for '.$type.' element.');
		if (in_array($name, $this->_names)) show_error(FGL_ERR.'Element name "'.$name.'" already exists.');
		if ($name) $this->_names[] = $name;
	}

	/**
	 * Add (Element)
	 * 
	 * Instantiates element object
	 */
	function add($info) 
	{
		if (is_array($this->break_after) && $info['type'] != 'fieldset' && in_array($info['type'], $this->break_after)) $info['after'] = '<br />';
			
		$el = new El($info, $this->config);
		$this->_add_element_to_form($el);	
		return $this;
	}

	/**
	 * Elements
	 * 
	 * Prepares elements
	 */	
	function open($action, $nameid='', $atts=array()) 
	{
		$this->action = $action;
		if ($nameid) $this->_make_nameid($nameid, $atts);
		$this->atts = $this->_make_info($atts);
		
		return $this;
	}
	
	function label($label, $for='', $atts=array())
	{
		$info = $this->_make_info($atts);
		$info['type'] = 'label';
		$info['label'] = $label;
		$info['for'] = $for;
		$this->add($info);
		return $this;
	}	
			
	function fieldset($legend='', $atts=array())
	{
		if ($this->fieldsets) 
		{
			$info['type'] = 'fieldset_close';
			$el = new El($info, $this->config);
			$this->_add_element_to_form($el);
		}
		$info = $this->_make_info($atts);
		$info['type'] = 'fieldset';
		$info['legend'] = $legend;
		$this->add($info);
		$this->fieldsets++;
		return $this;
	}	

	function hidden($nameid, $value='', $rules='')
	{
		$info = array();
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'hidden');
		$info['type'] = 'hidden';
		$info['rules'] = $rules;
		$info['value'] = $value;
		$this->add($info);
		return $this;
	}
	
	function text($nameid, $label='', $rules='', $value='', $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'text');
		$info['type'] = 'text';
		$info['label'] = $label;
		$info['rules'] = $rules;
		$info['value'] = $value;
		$this->add($info);
		return $this;
	}
	
	function password($nameid, $label='', $rules='', $value='', $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'password');
		$info['type'] = 'password';
		$info['label'] = $label;
		$info['rules'] = $rules;
		$info['value'] = $value;
		$this->add($info);
		return $this;
	}	
	
	function pass($nameid, $label='', $rules='', $value='', $atts=array()) 
	{
		$this->password($nameid, $label, $rules, $value, $atts);
		return $this;
	}
	
	function textarea($nameid, $label='', $rules='', $value='', $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'textarea');
		$info['type'] = 'textarea';
		$info['label'] = $label;
		$info['rules'] = $rules;
		$info['value'] = $value;
		if (@!$info['cols']) $info['cols'] = 40; // get this from config file
		if (@!$info['rows']) $info['rows'] = 10;		
		$this->add($info);
		return $this;
	}	
	
	function upload($nameid, $label='', $required=FALSE, $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'upload');
		$info['type'] = 'upload';
		$info['label'] = $label;
		$info['required'] = $required;
		$this->add($info);
		$this->_files[] = $this->_last_accessed;
		$this->multipart = TRUE;
		return $this;
	}
	
	function iupload($nameid, $label='', $required=FALSE, $atts=array()) 
	{
		$info = $this->_make_info($atts);
		if (!@$info['allowed_types']) $info['allowed_types'] = 'jpg|png|gif';
		$this->upload($nameid, $label, $required, $info);
		return $this;
	}	
	
	function select($nameid, $options=array(), $label='', $selected='', $rules='', $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'select');
		if (!count($options)) show_error(FGL_ERR.'You did not supply an array with the select options for element: '.$info['name']);				
		$info['type'] = 'select';
		$info['label'] = $label;
		$info['options'] = $options;
		$selected = $this->_make_array($selected);
		$info['selected'] = $selected;
		if (count($selected) > 1) $info['multiple'] = TRUE;
		$info['rules'] = $rules;
		$this->add($info);
		return $this;
	}	

	function checkbox($nameid, $value, $label='', $checked=FALSE, $rules='', $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'checkbox');
		$info['type'] = 'checkbox';
		$info['value'] = $value;
		$info['checked'] = $checked; // string or array
		$info['label'] = $label;
		$info['rules'] = $rules;
		$this->add($info);		
		return $this;
	}
	
	function bool($nameid, $label='', $checked=FALSE, $rules='', $atts=array()) 
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name'], 'bool');
		$info['type'] = 'checkbox';
		$info['value'] = 1;
		$info['checked'] = $checked; // string or array
		$info['label'] = $label;
		$info['rules'] = $rules;
		$this->add($info);		
		return $this;
	}	
	
	function checkgroup($name, $checks=array(), $label='', $checked=array(), $rules='', $atts=array()) 
	{
		$checked = $this->_make_array($checked);
		$atts = $this->_make_info($atts);
		$this->_check_name($name, 'checkgroup');
		if (!count($checks)) show_error(FGL_ERR.'You did not supply an array with the checkboxes for element: '.$name);		
		
		$info['rules'] = $rules;
		if ($label && strstr('above|before', $this->config['label_pos'])) $this->label($label, '', $info);
		if ($label && $this->config['label_pos'] == 'above') $this->br();		
		foreach ($checks as $check)
		{
			$info = $this->_make_info(@$check[2]);
			$info = array_merge($atts, $info); // replace global info with element specific info
			$this->_make_valueid($check[0], $info);			
			$info['type'] = 'checkbox';
			$info['name'] = $name;
			$info['checked'] = (in_array($info['value'], $checked)) ? TRUE : FALSE;
			$info['label'] = $check[1];
			$info['rules'] = $rules;
			$info['group_label'] = $label;			
			$this->add($info);
		}
		if ($label && $this->config['label_pos'] == 'after') $this->label($label);

		return $this;
	}
	
	function radiogroup($name, $radios=array(), $label='', $checked=array(), $rules='', $atts=array()) 
	{
		$checked = $this->_make_array($checked);
		$atts = $this->_make_info($atts);
		$this->_check_name($name, 'radiogroup');
		if (!count($radios)) show_error(FGL_ERR.'You did not supply an array with the radio buttons for element: '.$name);		

		$info['rules'] = $rules;
		if ($label && strstr('above|before', $this->config['label_pos'])) $this->label($label, '', $info);
		if ($label && $this->config['label_pos'] == 'above') $this->br();
		foreach ($radios as $radio)
		{
			$info = @$this->_make_info($radio[2]);
			$info = array_merge($atts, $info); // replace global info with element specific info
			$this->_make_valueid($radio[0], $info);	
			$info['type'] = 'radio';
			$info['name'] = $name;
			$info['checked'] = (in_array($info['value'], $checked)) ? TRUE : FALSE;
			$info['label'] = $radio[1];
			$info['rules'] = $rules;
			$info['group_label'] = $label;
			$this->add($info);
		}
		if ($label && $this->config['label_pos'] == 'after') $this->label($label);
				
		return $this;
	}	
	
	function button($content, $nameid='', $type='button', $atts=array())
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name']);	
		$info['type'] = $type;
		if ($info['type'] == 'button') 
		{
			$info['content'] = $content;
		}
		elseif ($info['type'] == 'image')
		{
			$info['src'] = $content;
		}
		else
		{
			$info['value'] = $content;
		}
		$this->add($info);
		if ($info['type'] == 'submit') $this->_submit_name[] = $this->_last_accessed;
		if ($info['type'] == 'image') $this->_submit_name[] = $this->_last_accessed.'_x';		
		return $this;
	}
	
	function image($src, $nameid='', $atts=array())
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name']);
		$info['type'] = 'image';
		$info['src'] = $src;
		$this->add($info);
		$this->_submit_name[] = $this->_last_accessed.'_x';
		return $this;
	}	
		
	function submit($value='Submit', $nameid='', $atts=array())
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name']);		
		$info['type'] = 'submit';
		$info['value'] = $value;
		$this->add($info);
		$this->_submit_name[] = $this->_last_accessed;
		return $this;
	}	
	
	function reset($value='Reset', $nameid='', $atts=array())
	{
		$info = $this->_make_info($atts);
		$this->_make_nameid($nameid, $info);
		$this->_check_name($info['name']);
		$info['type'] = 'reset';
		$info['value'] = $value;
		$this->add($info);
		return $this;
	}
	
	function span($content, $atts=array())
	{
		$info = $this->_make_info($atts);
		$info['type'] = 'span';
		$info['content'] = $content;
		$this->add($info);
		return $this;
	}
	
	function html($content)
	{
		$info = array();
		$info['type'] = 'html';
		$info['content'] = $content;
		$this->add($info);
		return $this;
	}

	function br($clear = FALSE)
	{
		$info = array();
		$info['type'] = 'br';
		$info['clear'] = $clear;
		$this->add($info);
		return $this;
	}

	function hr($clear = FALSE, $atts=array())
	{
		$info = $this->_make_info($atts);
		$info['type'] = 'hr';
		$info['clear'] = $clear;
		$this->add($info);
		return $this;
	}

	function space($clear = FALSE)
	{
		$info = array();
		$info['type'] = 'space';
		$info['clear'] = $clear;		
		$this->add($info);
		return $this;
	}

	/**
	 * Add Element To Form
	 * 
	 * Adds an element to the form
	 */
	function _add_element_to_form($el) 
	{
		$unique = $el->unique;
		$type = $el->type;
		$name = @$el->name;

		if (!@$el->label && @strstr($el->rules, 'required'))
		{
			if (array_key_exists($unique, $this->_labels)) {
				$label = $this->_labels[$unique];

				if ($type != 'checkbox' && $type != 'radio' && @$this->label_req_class) @$this->$label->atts['class'] .= ' '.$this->config['label_req_class'];
				if ($type != 'checkbox' && $type != 'radio' &&  @$this->label_req_flag) $this->$label->label .= $this->label_req_flag;				
			}
		}
		
		$this->config['unique'][] = $unique;
		$this->{$unique} = $el;		
		$this->_elements[] = array('unique'=>$unique, 'name'=>$name, 'type'=>$type);
		$this->_last_accessed = $unique;
		if ($type == 'label') $this->_labels[$el->for] = $unique;
		return $this;
	}

	/**
	 * Get
	 * 
	 * Returns the form as string
	 */
	function get() 
	{
		$this->_output='';
		$this->_close_tags();
		if (!$this->validated && !$this->config['novalidate']) $this->validate();
		$this->_upload_files();
		$this->_load_model();
		if ($this->error_string) $this->errors = $this->error_string_open.$this->error_string.$this->error_string_close;
		$this->_do_onsuccess();
		$this->_elements_to_string();
		$form = $this->_get_form_open();
		$form .= $this->_output;
		$form .= form_close();
		return $form;
	}

	/**
	 * Get Array
	 * 
	 * Returns the form as array
	 */
	function get_array() 
	{
		$this->_output='';
		$this->_close_tags();
		if (!$this->validated) $this->validate();
		$this->_upload_files();
		$this->_load_model();		
		$this->_do_onsuccess();
		$form = array();
		$form = $this->_elements_to_string(TRUE);
		foreach ($this->_aliases as $old=>$new) 
		{
			$form[$new] = $form[$old];
			$form[$new.'_error'] = $form[$old.'_error'];
			unset($form[$old]);
			unset($form[$old.'_error']);
		}
		$form['form_open'] = $this->_get_form_open();
		$form['form_close'] = form_close();
		$form['action'] = $this->action;
		$form['method'] = $this->method;
		return $form;
	}

	/**
	 * Get form_open()
	 * 
	 * Creates form opening tag
	 */
	function _get_form_open()
	{
		$this->atts['method'] = $this->method;
		if ($this->multipart) return form_open_multipart($this->action, $this->atts);
		return form_open($this->action, $this->atts);
	}

	/**
	 * Close Tags
	 * 
	 * Closes previously opened columns and fieldsets
	 */	
	function _close_tags() 
	{
		if ($this->indented)
		{
			$this->html('</div><div style="clear:both"></div>');
		}
		
		if ($this->columns)
		{
			$this->html('</div><div style="clear:both"></div>');
		}
			
		if ($this->fieldsets) 
		{
			$info['type'] = 'fieldset_close';
			if (is_array($this->break_after) && in_array('fieldset', $this->break_after)) $info['after'] = '<br />';
			$el = new El($info, $this->config);
			$this->_add_element_to_form($el);
		}
	}

	/**
	 * Check Post
	 * 
	 * Checks if form was submitted
	 */				
	function _check_post() 
	{
		if (count($this->_submit_name)) 
		{
			foreach ($this->_submit_name as $sn)
			{
				if ((isset($_POST[$sn]) || isset($_GET[$sn])))
				{
					$this->_posted = TRUE;
					break;
				}
			}
		}
	}

	/**
	 * Validate
	 * 
	 * Validates the form
	 */
	function validate() 
	{	
		$this->_check_post();
		if ($this->_posted) 
		{
			$this->CI->load->library('form_validation');
			foreach ($this->_elements as $el)
			{
				if ($el['name'])
				{
					$name = $el['name'];
					$element = $el['unique'];
					$type = $el['type'];
					
					if (@$this->$element->rules)
					{
						$label = (@$this->$element->label) ? $this->$element->label : ucfirst($element);
						if (@$this->$element->group_label) $label = $this->$element->group_label;
						if (@$this->$element->err_label) $label = $this->$element->err_label;
						$this->CI->form_validation->set_rules($name, $label, $this->$element->rules);					
					}
					else
					{
						$this->CI->form_validation->set_rules($name, @$this->$element->label);
					}
				}		
			}
	
			$errors = array();
			if ($this->CI->form_validation->run() == FALSE)
			{	
				foreach ($this->_elements as $el)
				{
					if ($el['name'])
					{
						$name = $el['name'];
						$element = $el['unique'];
						$type = $el['type'];
						
						switch ($type)
						{
							case 'select':
							$this->$element->selected = set_value($name);
							break;
							
							case 'checkbox':
							case 'radio':
							$checked = set_value($name);
							//echo("element = ".$element."<br />");
							//echo("Checked = ".$checked."<br />");
							$this->$element->atts['checked'] = ($checked && $this->$element->value == $checked) ? TRUE : FALSE;
							break;
							
							case 'submit':
							case 'reset':
							$this->$element->atts['value'] = $this->$element->value;
							break;
							
							case 'password':
							$this->$element->atts['value'] = '';
							break;
							
							case 'hidden':
							$this->$element->value = set_value($name);
							break;
							
							default:
							$this->$element->atts['value'] = set_value($name);
						}
	
						$error = form_error($name, $this->error_open, $this->error_close);
						if ($error) $errors[$name] = array($element, $error);
					}
				}
	
				foreach ($errors as $element => $error)
				{
					$element = $error[0];
					$error = $error[1];
					$type = $this->$element->type;
					
					// replace RULE specific error message with ELEMENT specific error message
					if (@$this->$element->err_message) $error = $this->error_open.$this->$element->err_message.$this->error_close;
	
					$this->$element->error = $error; // this adds the full error string (including error_open and error_close) to the element
					$message = str_replace($this->error_open, '', $error);
					$message = str_replace($this->error_close, '', $message);
					$this->$element->error_message = $message; // this adds the inline error to the element
					if ($type != 'checkbox' && $type != 'radio') $this->$element->atts['class'] = (isset($this->$element->atts['class'])) ? ' '.$this->error_class : $this->error_class;
					$this->error[] = $message;
					
					$this->error_string .= $error;
				}
			}

			if (!$this->error_string) $this->valid = TRUE;
			$this->validated = TRUE;
		}
		
		return $this;
	}

	/**
	 * Upload Files
	 * 
	 * Uploads files
	 */		
	function _upload_files() 
	{
		if ($this->_posted) 
		{
			foreach ($this->_files as $el) 
			{
				$config = array(
					'upload_path' => $this->$el->upload_path,
					'allowed_types' => $this->$el->allowed_types,
					'overwrite' => $this->$el->overwrite,
					'max_size' => $this->$el->max_size,
					'max_width' => $this->$el->max_width,
					'max_height' => $this->$el->max_height,
					'max_filename' => $this->$el->max_filename,
					'encrypt_name' => $this->$el->encrypt_name,
					'remove_spaces' => $this->$el->remove_spaces
				);

				$this->CI->load->library('upload');
				$this->CI->upload->initialize($config);
				$this->CI->lang->load('upload', $this->lang);
				
				if (!$this->CI->upload->do_upload($this->$el->name)) 
				{
					$err = $this->CI->upload->display_errors('','');

					if (!$this->$el->required && $err == $this->CI->lang->line('upload_no_file_selected')) 
					{
						// don't flag error
					} else {
						$this->add_error($el, $this->CI->upload->display_errors('',''));
					}
				} 
				else 
				{
					$this->_data[$this->$el->name] = $this->CI->upload->data();
				}
			}
			
			if ($this->error_string) 
			{
				// unlink uploaded file
				foreach ($this->_data as $el) 
				{
					unlink($el['full_path']);
				}
				
				// set values of other elements
				foreach ($this->_elements as $el)
				{
					if ($el['name'])
					{
						$name = $el['name'];
						$element = $el['unique'];
						$type = $el['type'];
						
						switch ($type)
						{
							case 'select':
							$this->$element->selected = set_value($name);
							break;
							
							case 'checkbox':
							case 'radio':
							$checked = array(set_value($name));
							//echo("Checked = ".$checked."<br />");
							$this->$element->atts['checked'] = ($checked && in_array($this->$element->value, $checked)) ? TRUE : FALSE;
							break;
							
							case 'submit':
							case 'reset':
							$this->$element->atts['value'] = $this->$element->value;
							break;
							
							case 'password':
							$this->$element->atts['value'] = '';
							break;
							
							default:
							$this->$element->atts['value'] = set_value($name);
						}
					}
				}				
			}
		}
	}

	/**
	 * Load Model
	 * 
	 * Loads the models
	 */
	function _load_model() {
		if ($this->_posted && !$this->error_string && $this->model) 
		{
			if ($this->_data) $this->model_data['uploads'] = $this->_data;

			$this->CI->load->model($this->model, 'mod');
			$this->CI->mod->{$this->model_method}($this, $this->model_data);
		}
	}
	
	/**
	 * Do on_success
	 * 
	 * Calls user function upon success
	 */	
	function _do_onsuccess() 
	{
		if ($this->_posted && !$this->error_string && $this->_onsuccess) 
		{
			foreach ($this->_onsuccess as $func)
			{
				if (is_array($func['values'])) 
				{
					foreach ($func['values'] as $vkey => $val) 
					{
						$this->_do_onsuccess_matches($val, $func['values'][$vkey], $func['rules']);
					}
				} 
				else 
				{
					// old: $this->_do_onsuccess_matches($val, $func['values'], $func['rules']);
					$this->_do_onsuccess_matches('', $func['values'], $func['rules']);
				}
				call_user_func_array($func['function'], $func['values']);
			}
		}
	}
	
	function _do_onsuccess_matches($string, &$fullstring, $rules) 
	{
		preg_match_all('/{([a-z_0-9]+)}/', $string, $matches);
		
		if ($matches[1]) {
			foreach ($matches[1] as $match) 
			{
				$str = '{'.$match.'}';
				$value = $this->$match->value;
				
				if (@$rules[$str]) 
				{
					foreach (explode(',', $rules[$str]) as $rule) 
					{
						$values = $this->_postprocess[$rule]['values'];
						$function = $this->_postprocess[$rule]['function'];
						if (!is_array($values)) $values = array($values);
						$key = array_search('$var', $values);
						$values[$key] = $this->$match->value;
						
						$value = call_user_func_array($function, $values);
						$fullstring = str_replace($str, $value, $fullstring);
					}
				} 
				else 
				{
					$fullstring = str_replace($str, $value, $fullstring);
				}
			}
		}
	}

	/**
	 * Elements To String
	 * 
	 * Utilized by get() and get_array() to generate output
	 */
	function _elements_to_string($strip=FALSE) 
	{
		$array = array();
		foreach ($this->_elements as $el)
		{
			$element = $el['unique'];
			$name = str_replace('[]', '', $el['name']);
						
			if ($strip && $name)
			{
				$this->$element->label='';
				$this->$element->error = '';
				
				// checkgroups and radiogroups share the same 'name' attribute
				if (isset($array[$name])) 
				{
					if (!is_array($array[$name]) )
					{
						$old = $array[$name];
						$old_error = $array[$name.'_error'];

						unset($array[$name]);
						unset($array[$name.'_error']);

						$array[$name][] = $old;
						$array[$name.'_error'][] = $old_error;
					}

					$array[$name][] = $this->$element->get();
					$array[$name.'_error'][] = $this->$element->error_message;				
				}
				else
				{
					$array[$name] = $this->$element->get();
					$array[$name.'_error'] = $this->$element->error_message;				
				}
			}

			$this->_output .= $this->$element->get();
		}
		
		return $array;
	}	
	
	// --------------------------------------------------------------------------
	
	/**
	 * Action
	 * 
	 * Sets the form's action
	 */		
	function action($action) 
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * Add Class
	 * 
	 * Adds a class to the element
	 */	
	function add_class($name, $class='') 
	{
		if ($this->_last_accessed && !$class) 
		{
			$class = $name;
			$el = $this->_last_accessed;
		}
		else
		{
			$el = $this->_el_get_unique($name);
		}
		
		if ($el)
		{
			$this->$el->atts['class'] = (isset($this->$el->atts['class'])) ? ' '.$class : $class;
			$this->_last_accessed = $el;
		}
		else
		{
			show_error(FGL_ERR.'add_error: Element name "'.$name.'" does not exist');
		}
		
		return $this;
	}	

	/**
	 * Add Error
	 * 
	 * Adds an error to the form
	 */	
	function add_error($name, $message='')
	{		
		if ($this->_last_accessed && !$message)
		{
			$message = $name;
			$el = $this->_last_accessed;
		}
		else 
		{
			$el = $this->_el_get_unique($name);
		}

		if ($el)
		{
			$this->$el->error = $this->error_open.$message.$this->error_close;
			$this->$el->error_message = $message;
			if ($this->$el->type != 'checkbox' && $this->$el->type != 'radio') $this->$el->atts['class'] = (isset($this->$el->atts['class'])) ? ' '.$this->error_class : $this->error_class;
			$this->error[] = $message;
			$this->error_string .= $this->error_open.$message.$this->error_close;		
		}
		else
		{
			show_error(FGL_ERR.'add_error: Element name "'.$name.'" does not exist');
		}
		
		return $this;
	}	

	/**
	 * Alias
	 * 
	 * Creates an alias for the variable name in get_array() output
	 */		
	function alias($name='', $alias='')
	{
		if (!$name && !$alias) show_error(FGL_ERR.'alias: No alias specified');
		
		$group = (!$alias && (is_array($name) || preg_match('/=/', $name))) ? TRUE : FALSE;
		 
		if ($group)
		{
			$groups = $this->_make_info($name);
			foreach ($groups as $name=>$alias)
			{
				$el = $this->_el_get_unique($name);
				if ($el)
				{
					$this->_build_alias($el, $alias);
				}
				else
				{
					show_error(FGL_ERR.'alias: Alias cannot be assigned. Element "'.$name.'" does not exist.');
				}			
			}
			
			return $this;
		}
		elseif ($this->_last_accessed && !$alias)
		{
			$alias = $name;
			$el = $this->_last_accessed;
		}		
		else 
		{
			$key = $this->_el_search($name);
			$el = ($key) ? $this->_el_unique($key) : $name;
		}
		
		if ($el)
		{
			$this->_build_alias($el, $alias);
		}
		else
		{
			show_error(FGL_ERR.'alias: Alias cannot be assigned. Element "'.$name.'" does not exist.');
		}
		
		return $this;
	}	
	
	function _build_alias($el, $alias) 
	{
		if (!array_key_exists($alias, $this->_aliases) && !$this->_el_name_exists($alias)) 
		{
			$this->_aliases[$this->$el->name] = $alias;
		}
		else
		{
			show_error(FGL_ERR.'alias: Alias cannot be assigned. An element with this name ("'.$alias.'") already exists.');
		}
	}

	/**
	 * Columns
	 * 
	 * Creates columns for display of fields
	 *
	 * ->col(150) creates a column with 150 pixel width
	 * ->col(0) resets columns
	 *
	 * if width is not set, it will be set to 'auto'
	 */
	function col($width=NULL, $float='left', $ta='')
	{
		if ($width || $width === NULL)
		{
			if ($this->columns) 
			{
				// close previous column
				$this->html('</div>');
				$this->columns--;
			}

			$width = ($width) ? $width.'px' : 'auto';
			$html = '<div style="float:'.$float.'; width:'.$width;
			if ($ta) $html .= '; text-align:'.$ta;
			$html .= '">';
			$this->html($html);
			$this->columns++;
		}
		elseif ($this->columns)
		{
			$this->html('</div><div style="clear:both"></div>');
			$this->columns--;
		}
		
		return $this;
	}

	/**
	 * Error Label
	 * 
	 * Sets an error label for an element (in case no element label was specified)
	 */			
	function error_label($name='', $label='')
	{
		if ($this->_last_accessed && !$label) 
		{
			if (!$name) show_error(FGL_ERR.'error_label: No label specified');
		
			$label = $name;
			$el = $this->_last_accessed;
		}
		else 
		{
			if (!$name) show_error(FGL_ERR.'error_label: No element name specified');
			if (!$label) show_error(FGL_ERR.'error_label: No label specified');
		
			$key = $this->_el_search($name);
			$el = ($key) ? $this->_el_unique($key) : $name;
		}

		if ($el)
		{
			$this->$el->err_label = $label;
		}
		else
		{
			show_error(FGL_ERR.'error_label: Element name "'.$name.'" does not exist');
		}
		
		return $this;
	}
	
	/**
	 * Error Message
	 * 
	 * Sets a custom error message for the element
	 *
	 * {element} can be used as placeholder for the element's label
	 */			
	function error_message($name='', $message='')
	{
		if ($this->_last_accessed && !$message) 
		{
			if (!$name) show_error(FGL_ERR.'error_message: No message specified');
			
			$message = $name;
			$el = $this->_last_accessed;
		}
		else 
		{
			if (!$name) show_error(FGL_ERR.'error_message: No element name specified');
			if (!$message) show_error(FGL_ERR.'error_message: No message specified');
						
			$el = $this->_el_get_unique($name);
		}

		if ($el)
		{
			$label = (@$this->$el->label) ? $this->$el->label : ucfirst($this->$el->name);
			$this->$el->err_message = str_replace('{element}', $this->$el->label, $message);
		}
		else
		{
			show_error(FGL_ERR.'error_message: Element "'.$name.'" could not be accessed');
		}
		
		return $this;
	}

	/**
	 * Indent
	 * 
	 * Indents the following form fields with a floating div
	 *
	 * ->indent(40) sets indentation
	 * ->indent(0) resets indentation
	 */	
	function indent($width)
	{
		if (!is_numeric($width)) show_error(FGL_ERR.'indent: Value must be numeric');
		
		if ($this->indented) 
		{
			$this->html('</div><div style="clear:both"></div>');
			$this->indented--;
		}
		
		if ($width)
		{
			$this->html('<div style="float:left;width:'.$width.'px">&nbsp;</div><div style="float:left">');
			$this->indented++;
		}
		
		return $this;
	}

	/**
	 * Lang
	 * 
	 * Sets the language for the form validation class
	 */	
	function lang($lang) 
	{
		$this->lang = $lang;
		return $this;
	}

	/**
	 * Margin
	 * 
	 * Sets 'margin' for an element
	 */		
	function margin($name='', $margin='', $pos='')
	{
		if ($this->_last_accessed && (!$margin || ($margin && !$pos)))
		{
			if (!$name) show_error(FGL_ERR.'margin: No margin value specified');
		
			$pos = $margin;
			$margin = $name;
			$el = $this->_last_accessed;
		}
		elseif (!is_numeric($name))
		{
			if (!$name) show_error(FGL_ERR.'margin: No element name specified');
			if (!$margin) show_error(FGL_ERR.' margin: No margin value specified');		
		
			$el = $this->_el_get_unique($name);
		}

		// if name attribute exists
		if ($el)
		{
			if (!is_numeric($margin)) show_error(FGL_ERR.'margin: Value must be numeric.');
			$this->$el->add_margin($margin, $pos);
		}
		else
		{
			show_error(FGL_ERR.'margin: Element "'.$name.'" could not be accessed');
		}
		
		return $this;
	}	
	
	/**
	 * Method
	 * 
	 * Sets the form's send method
	 */			
	function method($method='')
	{
		if ($method != 'post' && $method != 'get') show_error("method: Method must be either 'post' or 'get'");
		$this->method = $method;
		return $this;
	}

	/**
	 * Model
	 * 
	 * Passes the validated form data to a model
	 */		
	function model($model='', $method='index', $data=array())
	{
		if (!$model) show_error("model: No model specified");
		$data = $this->_make_array($data);
		$data = array_merge($data, $_POST); // post data validated at this time, combine data provided with POST data
		$this->model = $model;
		$this->model_method = $method;
		$this->model_data = $data;
		return $this;
	}

	/**
	 * No Break
	 * 
	 * Removes the previous break (useful if $break_after is set in config file)
	 */		
	function nobr($name='')
	{
		$group = (preg_match('/,/', $name)) ? TRUE : FALSE;
		 
		if ($group)
		{
			$groups = explode(',', $name);
			foreach ($groups as $name)
			{
				$el = $this->_el_get_unique(trim($name));
				if ($el)
				{
					unset($this->$el->after);
				}
				else
				{
					show_error(FGL_ERR.'nobr: Line break cannot be removed. Element "'.$name.'" does not exist.');
				}			
			}
			
			return $this;
		}
		elseif ($this->_last_accessed && !$name)
		{
			$el = $this->_last_accessed;
		}		
		else 
		{
			$key = $this->_el_search($name);
			$el = ($key) ? $this->_el_unique($key) : $name;
		}
		
		if ($el)
		{
			unset($this->$el->after);
		}
		else
		{
			show_error(FGL_ERR.'nobr: Line break cannot be removed. Element "'.$name.'" does not exist.');
		}
		
		return $this;		
	}
	function novalidate($val = true) {
		$this->config['novalidate'] = $val;	
	}
	
	function onsuccess($func, $vals, $rules=array()) 
	{
		$this->_onsuccess[] = array(
			'function'	=> $func,
			'values'	=> $vals,
			'rules'		=> $rules
		);
		return $this;
	}		
	
	function postprocess($array) 
	{
		$this->_postprocess = array_merge($this->_postprocess, $array);
		return $this;
	}

	/**
	 * Remove
	 * 
	 * Removes an element from the form
	 */		
	function remove($name='') 
	{
		if (!$name) show_error(FGL_ERR.'remove: No element name specified');

		$key = $this->_el_search($name);
		$el = $this->_el_unique($key);
		
		unset($this->$el);
		unset($this->_elements[$key]);
		
		$this->_last_accessed = NULL;
		
		return $this;
	}
	
	/**
	 * Remove Attribute
	 * 
	 * Removes an attribute from the element
	 */		
	function rem_att($name='', $att='')
	{
		if ($this->_last_accessed && !$att)
		{
			if (!$name) show_error(FGL_ERR.'rem_att: No attribute specified');

			$att = $name;
			$el = $this->_last_accessed;
		}
		else
		{
			if (!$name) show_error(FGL_ERR.'rem_att: No element name specified');
			if (!$att) show_error(FGL_ERR.'rem_att: No attriute specified');		
		
			$el = $this->_el_get_unique($name);
		}
		
		if ($el) 
		{
			if (isset($this->$el->atts[$att])) unset($this->$el->atts[$att]);
			if (isset($this->$el->$att)) unset($this->$el->$att);
		}
		else
		{
			show_error(FGL_ERR.'rem_att: Element "'.$name.'" could not be accessed');
		}
		
		return $this;
	}

	/**
	 * Remove Class
	 * 
	 * Removes a class from the element
	 */		
	function rem_class($name='', $class='')
	{
		if ($this->_last_accessed && !$class) 
		{
			if (!$name) show_error(FGL_ERR.'rem_class: No class specified');
		
			$class = $name;
			$el = $this->_last_accessed;
		}
		else
		{
			if (!$name) show_error(FGL_ERR.'rem_class: No element name specified');
			if (!$class) show_error(FGL_ERR.'rem_class: No class specified');		
		
			$el = $this->_el_get_unique($name);
		}
		
		if ($el)
		{
			$this->$el->atts['class'] = preg_replace('/ *'.$class.'/', '', $this->$el->atts['class']);
			if (empty($this->$el->atts['class'])) unset($this->$el->atts['class']);		
		}
		
		return $this;
	}

	/**
	 * Set Attribute
	 * 
	 * Sets an attribute for the element
	 */		
	function set_att($name='', $att='', $value='')
	{
		if ($this->_last_accessed && !$value) 
		{
			if (!$name) show_error(FGL_ERR.'set_att: No attribute specified');
			if (!$att) show_error(FGL_ERR.'set_att: No value specified');		
		
			$value = $att;
			$att = $name;
			$el = $this->_last_accessed;
		}
		else
		{
			if (!$name) show_error(FGL_ERR.'set_att: No element name specified');
			if (!$att) show_error(FGL_ERR.'set_att: No attribute specified');
			if (!$value) show_error(FGL_ERR.'set_att: No value specified');		

			$el = $this->_el_get_unique($name);
		}
		
		if ($el)
		{
			$this->$el->atts[$att] = $value;		
		}
		
		return $this;
	}

	/**
	 * Set Value
	 * 
	 * Sets a value for the element
	 */	
	function set_value($name='', $value='')
	{		
		if ($this->_last_accessed && !$value) 
		{
			if (!$name) show_error(FGL_ERR.'set_value: No value specified');
		
			$value = $name;
			$el = $this->_last_accessed;
		}
		else
		{
			if (!$name) show_error(FGL_ERR.'set_value: No element name specified');
			if (!$value) show_error(FGL_ERR.'set_value: No value specified');

			$el = $this->_el_get_unique($name);		
		}
		
		if ($el) 
		{
			$this->$el->atts['value'] = $value;
		}
		
		return $this;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Element Name Exists
	 * 
	 * Checks if the element has a 'name' attribute
	 */
	function _el_name_exists($unique) 
	{
		foreach ($this->_elements as $v) 
		{
			if ($v['unique'] == $unique && !empty($v['name'])) return TRUE;
		}
	}

	/**
	 * Element Search
	 * 
	 * Searches all elements by 'name' and returns the key of the last occurrence 
	 * of an element with this name (important for checkgroups etc.)
	 */
	function _el_search($name) 
	{
		$i = 0;
		foreach ($this->_elements as $el) 
		{
			if ($el['name'] == $name || $el['name'] == $name.'[]') $key = $i;
			$i++;
		}
		return @$key;
	}	

	/**
	 * Element Unique
	 * 
	 * Returns the 'unique' identifier by element key
	 */	
	function _el_unique($key) 
	{
		return $this->_elements[$key]['unique'];
	}

	/**
	 * Element Get Unique
	 * 
	 * Returns the 'unique' identifier by element 'name'
	 */	
	function _el_get_unique($name) 
	{
		$key = $this->_el_search($name);
		if ($key) return $this->_elements[$key]['unique'];
		return FALSE;
	}
	
}

/* End of file Form.php */
/* Location: ./application/libraries/Form.php */