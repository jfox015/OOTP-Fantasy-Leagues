<?php
/**
 *	GENERIC MODEL CLASS.
 *	
 *	@author			Jeff Fox (Github ID: jfox015)
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