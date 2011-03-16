<?php
/**
 *	GENERIC MODEL CLASS.
 *	
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
*/
class generic_data_model extends base_model {

	var $_NAME = 'generic_data_model';
	
	function generic_data_model() {
		parent::__construct();
		
		parent::_init();
	}
}