<?php
/*-------------------------------------------------------------------------------------
/   Filename: CompiledStats.php
/   Date Created: 	08/29/11
/   Last modified: 	08/29/11
/   
/   Change Log:
/-----------------------------------------------------------------------------------*/
/**
 * 	CompiledStats object.
 *	A PHP Object for storing and referencing rotisserie scoring values.
 *
 * 	@author      Jeff Fox <jfox@NOSPAMaeoliandigital.com>
 * 	@version     $Revision: 1.0 $
 * 	@since       1.0
 * 	@copyright   (c)2009-11 Jeff Fox/Aeolian Digital Studios
 * 
 */
class CompiledStats {
	
	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:String
	 */
	var $_NAME = 'CompiledStats';
	/**
	 *	TYPE.
	 *	@var $type:String
	 */
	var $type = '';
	/**
	 *	VALUES.
	 *	@var $values:Array
	 */
	var $values = array();
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of CompiledStats
	/
	/---------------------------------------------*/
	function CompiledStats($type = false,$values = false) {
		if ($type !== false && !empty($type) && $type != '') { $this->type = $type; }
		$this->updateStats($values);
	}	
	/**
	 *	UPDATE STATS.
	 *	Updates the internal <code>values</code> array with the passed values.
	 *
	 *	@param		$values	{Array}	Array of stats in key = value pairs
	 *	@return				{Boolean}	TRUE on success, FALSE on failure
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function updateStats($values) {
		if ($values !== false && sizeof($values) > 0) { 
			$tmpVals = array();
			foreach($values as $id -> $value) {
				if(isset($this->values[$id])) {
					$tmpVals[$id] = $this->values[$id] + $value;
				} else {
					$tmpVals[$id] = $value;
				}
			}
			$this->values = $tmpVals;
			return true;
		} else {
			return false;
		}
	}
	/**
	 *	GET COMPILED STATS.
	 *	Returns a compiled stat based on the internal stored values.
	 *
	 *	@param		$type	{String}	Batting or Pitching
	 *	@param		$cat	{Int}		Stats Category index
	 *	@return				{Int}		Compiled stat value
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function getCompiledStats($type = false, $cat = false) {
		if ($type === false || $cat === false) return false;
		
		$value = 0;
		switch ($type) {
			case 'batting':
				switch($cat) {
					// AVG
					case 18:
						$value =  $this->values['h'] / $this->values['ab'];
						break;
					// OBP
					case 19:
						$value = ($this->values['h']+$this->values['bb']+$this->values['hp']) / ($this->values['ab']+$this->values['bb']+$this->values['ab']+$this->values['hp']+$this->values['sf']);
						break;
					// SLG
					case 20:
						$value = ($this->values['h']+($this->values['d']*2)+($this->values['t']*3)+$this->values['hr']) / $this->values['ab'];
						break;
					// OPS (OBP + SLG)
					case 25:
						$value = (($this->values['h']+$this->values['bb']+$this->values['hp']) / ($this->values['ab']+$this->values['bb']+$this->values['ab']+$this->values['hp']+$this->values['sf'])) + (($this->values['h']+($this->values['d']*2)+($this->values['t']*3)+$this->values['hr']) / $this->values['ab']);
						break;
					// SKIP ISO, TAVG and VORP
					case 23:
					case 24;
					case 26;
						break;
					// ALL OTHERS
					default:
						$stat = strtolower(get_ll_cat($cat, true));
						if (isset($this->values[$stat])) {
							$value = $this->values[$stat];
						}
						break;
				} // END switch
				break;
			case 'pitching':
				switch($cat) {
					// ERA
					case 40:
						$value =  (9*$this->values['er']) / ($this->values['ip']+($this->values['ipf']/3));
						break;
					// WHIP
					case 42:
						$value = ($this->values['ha']+$this->values['bb']) / ($this->values['ip']+($this->values['ipf']/3));
						break;
					// BABIP
					case 41:
						$value = ($this->values['ha']-$this->values['hra']) / ($this->values['ab']-$this->values['k']-$this->values['hra']+$this->values['sf']);
						break;
					// ALL OTHERS
					default:
						$stat = strtolower(get_ll_cat($cat, true));
						if (isset($this->values[$stat])) {
							$value = $this->values[$stat];
						}
						break;
				} // END switch
				break;
		} // END switch
		return $value;
	}
	/**
	 *	SERIALZE.
	 *	Serializes the current internal $values array for serialization
	 *	@return		{String}	Serialzed <code>$values</code> data string
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function serialize() {
		return serialize($this->values);
	}
	/**
	 *	UNSERIALZE.
	 *	Unserializes and serialized object and applies ito the the internal <code>$values</code> array.
	 *	@param		$serialized	{Array}		An array of serilazed stats data in key = value pairs.
	 *	@return					{Boolean}	TRUE on sucess, FALSE on error
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function unserialize($serialized) {
		$success = true;
		try {
			$raw = unserialize($serialized);
			if ($raw) {
				foreach ($raw as $key => $value) {
					$this->values[$key] = $value;
				} // END foreach
			} // END if
		} catch (Exception $e) {
			$success = false;
			$this->errorCode = 1;
			$this->statusMess = 'Error occured unserializing data. Error: '.$e;
		} // END try/catch
		return $success;
	}
}