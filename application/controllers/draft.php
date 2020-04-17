<?php
require_once('base_editor.php');
/**
 *	DRAFT.
 *	The primary controller for draft capabilities and functionlaity.
 *
 *	NOTE: This class contains draft functionlaity originally developed by Frank Esslink and 
 *	Published as part of the StasLab Mod for Out of the Park Baseball. Thanks to Frank for permission 
 *	to adpat this code into the Fantasy Draft Class.
 *	(http://www.ootpdevelopments.com/board/ootp-mods-database-tools/185408-statslab-sql-utilities-ootpx.html)
 *
 *	@author			Jeff Fox (Github ID: jfox015)
 *	@author			Frank Esslink (OOTP ID: fhommes)
 *	@dateCreated	03/23/10
 *	@lastModified	04/16/20
 *
 */
class draft extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'draft';
	/**
	 *	LEAGUE ID.
	 *	@var $league_id:Int
	 */
	var $league_id = -1;
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of draft.
	 */
	public function draft() {
		parent::BaseEditor();
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 */
	public function index() { 
		$this->getURIData(); 
		redirect('draft/info/'.$this->uriVars['id']);
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	/**
	 *	INIT.
	 *	Creates and instaitates the basic properties required
	 *	by this controller.
	 */
	function init() {
		parent::init();
		$this->modelName = 'draft_model';
		
		$this->enqueStyle('content.css');
		
		$this->getURIData();
		if (!$this->isAjax() && isset($this->uriVars['league_id'])) {
			$this->load->model($this->modelName,'dataModel');
			$this->loadModel();
			$this->load->model('league_model');
			$this->league_model->load($this->uriVars['league_id']);
			
			//print("ID = ".$this->uriVars['id']."<br />");
			if ($this->league_model->id != -1 && $this->league_model->access_type == -1) {
				$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true : false;
				$isCommish = $this->league_model->userIsCommish($this->params['currUser']) ? true: false;
				if (!$isAdmin && !$isCommish) {
					if (!$this->league_model->userHasAccess($this->params['currUser'])) {
						redirect('/league/privateLeague/'.$this->uriVars['league_id']);
					}
				}
			}
		}
		$this->views['EDIT'] = 'draft/draft_editor';
		$this->views['VIEW'] = 'draft/draft_info';
		$this->views['FAIL'] = 'draft/draft_message';
		$this->views['SUCCESS'] = 'draft/draft_message';
		$this->views['SELECT'] = 'draft/draft_selection';
		$this->views['HISTORY'] = 'draft/draft_history';
		$this->views['ORDER'] = 'draft/draft_order';
		$this->views['TEAM_SETTINGS'] = 'draft/draft_team_settings';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_WRITE;
		
		$this->debug = false;
	}
	/*--------------------------------
	/	PRIVATE FUNCTIONS
	/-------------------------------*/
	/**
	 *	DRAFT ADMIN.
	 *	Creates and instaitates the basic properties required
	 *	by this controller.
	 */
	public function admin() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();
			$this->params['subTitle'] = "Draft";
			if ($this->dataModel->id != -1) {
				redirect('/draft/submit/mode/edit/id/'.$this->dataModel->id);
			} else if ($this->league_id != -1) {
				redirect('/draft/submit/add/league_id/'.$this->league_id);
			} else {
				$this->data['theContent'] = '<span class="error">A required league identifier could not be found.</span>';
				$this->data['subTitle'] = 'An error occured.';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	LOAD.
	 *	Loads a league's draft results screen is a valid league id is passed. If not, the error result screen is shown.
	 */
	public function load() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();		
			$this->params['subTitle'] = "Draft";
			$error = false;
			if ($this->dataModel->id != -1) {
				redirect('draft/info/'.$this->dataModel->id);
			} else {
				$this->data['theContent'] = "The draft for this league has not been initalized. Please check back again soon.";
				$this->data['subTitle'] = "Draft Results";
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	DRAFT ORDER.
	 *	Allows the League Commissioenr to manually manipulate the draft order.
	 *	This can be done per round or using two addition checkbox options on 
	 *	the view.
	 */
	public function draftOrder() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();		
			$this->params['subTitle'] = "Draft";
			$error = false;
			if ($this->dataModel->id != -1) {
				$this->league_model->load($this->dataModel->league_id);
				if ($this->input->post('submitted')) {
					$year = date('Y');
					if ($this->input->post('action') == 'settings') {
						if ($debug==1) {echo "In draft settings scheduler<br />\n";}
						$this->dataModel->createDraftOrder($this->league_model->getTeamDetails(), $this->league_model->id);
						//$this->dataModel->sheduleDraft($this->league_model->getTeamDetails());
					} else {
						$this->dataModel->savedDraftSettings($this->input,false, false, $this->debug);
					}
					$this->session->set_flashdata('message','<span class="success">Draft order update has been saved.</span>');
					redirect('league/admin/id/'.$this->dataModel->league_id);
				} else {
					// TEST FOR DRAFT SETTINGS IN DB
					$max = $this->dataModel->getDraftMax();
					if ($max == 0) {
						// No setting to change, so run them for the first time
						$this->rescheduleDraft();
					}
					$this->data['teams'] = $this->league_model->getTeamDetails();
					$this->data['curRound'] = (isset($this->uriVars['round'])) ? $this->uriVars['round'] : 1;
					$this->data['picks'] = $this->dataModel->getTeamPicks($this->data['curRound']);
					$this->data['nRounds'] = $this->dataModel->nRounds;
					$this->data['subTitle'] = "Edit Draft Order";
					$this->data['league_id'] = $this->league_model->id;
					$this->params['content'] = $this->load->view($this->views['ORDER'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->makeNav();
					$this->displayView();
				}
			} else {
				$error = true;
				$this->params['outMess'] = '<span class="error">A required league identifier could not be found. Your selections could not be processed at this time.</span>';
			}
			if ($error) {
				$this->data['subTitle'] = 'Draft: An error occured.';
				$this->data['theContent'] = $this->params['outMess'];
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	TEAM SETTINGS.
	 *	Allows the League Commissioner to set or override teams settings in regards to auto draft.
	 */
	public function teamSettings() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();		
			$this->params['subTitle'] = "Draft";
			$error = false;
			if ($this->dataModel->id != -1) {
				$this->league_model->load($this->dataModel->league_id);
				$this->data['thisItem']['divisions'] = $this->league_model->getFullLeageDetails();
				$this->data['subTitle'] = "Team Draft Settings";
				$this->data['league_id'] = $this->league_model->id;
				$this->params['content'] = $this->load->view($this->views['TEAM_SETTINGS'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				$error = true;
				$this->params['outMess'] = '<span class="error">A required league identifier could not be found. Your selections could not be processed at this time.</span>';
			}
			if ($error) {
				$this->data['subTitle'] = 'Draft: An error occured.';
				$this->data['theContent'] = $this->params['outMess'];
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	
	
	/**
	 *	COMPLETE DRAFT.
	 *	Finalizes the draft by copying the draft selections to the individual team
	 *	rosters. If waivers areenabled, the team waiver order is also set atthe end of 
	 * 	this operation.
	 */
	public function completeDraft() {
		// COPY DRAFT PICKS TO TEAM ROSTERS
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();		
			$this->params['subTitle'] = "Draft";
			$this->data['subTitle'] = "Complete Draft";
			$error = false;
			$useWaivers = (isset($this->params['config']['useWaivers']) && $this->params['config']['useWaivers'] == 1) ? true : false;
			$waiverOrder = array();	
			if ($this->dataModel->id != -1) {
				$this->league_model->load($this->dataModel->league_id);
				$this->db->query('DELETE FROM fantasy_rosters WHERE league_id = '.$this->dataModel->league_id); 
				$this->load->model('player_model');
				$results = $this->dataModel->getDraftResults();
				//echo("Draft results = ".sizeof($results)."<br />");
				$playersAdded = 0;
				$rules = $this->league_model->getRosterRules();
				
				if (sizeof($results) > 0) {
					$quota = array(); 
					foreach($results as $row) {
						
						$tid=$row['team_id'];
						if (!isset($quota[$tid])) { $quota[$tid] = array(); }
						$pid=$row['player_id'];
						// LOOK UP PLAYER INFO 
						$player = $this->player_model->getPlayerDetails($pid);
						$pos = 0;
						$role = -1;
						if ($player['position'] != 1) {
							if ($player['position'] == 7 || $player['position'] == 8 || $player['position'] == 9) {
								$pos = 20;
							} else {
								if ($player['position'] != NULL) {
									$pos = $player['position'];
								}
							}
							if (isset($quota[$tid][$pos])) {
								if (($quota[$tid][$pos]+1) > $rules[$pos]['active_max']) {
									$pos = 25;
									$quota[$tid][$pos] = 1;
								} else {
									$quota[$tid][$pos] += 1;
								}
							} else {
								$quota[$tid][$pos] = 1;
							}
						} else {
							$pos = 1;
							if ($player['role'] == 13) {
								$role = 12;
							} else {
								$role = $player['role'];
							}
						}
						$data = array('player_id'=>$pid,'league_id'=>$this->dataModel->league_id,'team_id'=>$tid,'scoring_period_id'=>1,'player_position'=>$pos,
									  'player_role'=>$role,'player_status'=>1);
						$this->db->insert('fantasy_rosters',$data);
						if ($this->db->affected_rows() == 1) {
							$playersAdded++;
						}
						
						if ($useWaivers && $row['round'] == 1) {
							array_push($waiverOrder,$row['team_id']);
						}
					}
					//echo("Players added to rosters = ".$playersAdded."<br />");
					if ($playersAdded != sizeof($results)) {
						$error = true;
						$this->data['theContent'] = '<span class="error">Your leagues draft results were not be saved and applied to each teams rosters.</span>';
					} else {
						// TODO: SET TEAM WAIVER ORDER
						if ($useWaivers) {
							$this->dataModel->setWaiverOrder($waiverOrder);
						}
						$this->dataModel->completed = 1;
						$this->dataModel->save();
						$this->data['theContent'] = '<span class="success">Your leagues draft results have been saved and applied to each teams rosters.</span>';
					}
				} else {
					$error = true;
					$this->data['theContent'] = '<span class="error">No draft results were found. Assure that your league draft was initalized and completed successfully.</span>';
				}
			} else {
				$error = true;
				$this->data['theContent'] = '<span class="error">A required league identifier could not be found. Your selections could not be processed at this time.</span>';
			}
			if ($error) {
				$this->data['subTitle'] = 'Draft: An error occured.';
			}
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
			$this->makeNav();
			$this->displayView();
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	
	/**
	 *	PROCESS DRAFT.
	 *	The heart of the draft engine. This function runs the draft.
	 *
	 *	Adapted from the processDraft.php script written by fhommes for StatsLab X.
	 */
	public function processDraft() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();		
			$this->params['subTitle'] = "Draft";
			if ($this->dataModel->id != -1) {
				//if ($this->dataModel->getDraftStatus() < 3) {
					$this->league_model->load($this->dataModel->league_id);
					$continue = true;
					if (isset($this->uriVars['action']) && $this->uriVars['action'] != 'selection') {
						if( $this->params['accessLevel'] != ACCESS_ADMINISTRATE && $this->params['currUser'] != $this->league_model->commissioner_id) {
							$continue = false;
						}
					}
					if ($continue) {
						$this->load->model('team_model');
						if (isset($this->uriVars['team_id'])) {
							$this->team_model->load($this->uriVars['team_id']);
							$dteam = intval($this->uriVars['team_id']);
						} else {
							$dteam = -1;
						}
						if (isset($this->uriVars['action'])) {
							switch ($this->uriVars['action']) {
							   case 'reset_draft':
								  $this->dataModel->draftReset();
								  redirect('/draft/admin/league_id/'.$this->uriVars['league_id']);
							   case 'clear':
								  $this->dataModel->resetPick($this->uriVars['pick_id']);
								  $this->rescheduleDraft();
								  $this->session->set_flashdata('message','<span class="success">Pick '.$this->uriVars['pick_id'].' has been reset.</span>');
								  redirect('/draft/load/'.$this->uriVars['league_id']);
							   case 'skip':
								  $this->dataModel->skipPick($this->uriVars['pick_id']);
								  $this->rescheduleDraft();
								  $this->session->set_flashdata('message','<span class="success">Pick '.$this->uriVars['pick_id'].' has been skipped.</span>');
								  redirect('/draft/load/'.$this->uriVars['league_id']);
							   case 'edit':
								  $this->dataModel->resetPick($this->uriVars['pick_id'],$this->uriVars['league_id'],false,$this->uriVars['pick']);
								  $this->rescheduleDraft();
								  $this->session->set_flashdata('message','<span class="success">Pick '.$this->uriVars['pick_id'].' has been sucessfully edited.</span>');
								  redirect('/draft/load/'.$this->uriVars['league_id']);
							   case 'rollback':
								  $this->dataModel->rollbackPick($this->uriVars['pick_id']);  
								  $this->rescheduleDraft();
								  $this->session->set_flashdata('message','<span class="success">Draft rolled back to pick '.$this->uriVars['pick_id'].'.</span>');
								  redirect('/draft/load/'.$this->uriVars['league_id']);
							   case 'auto_off':
								  $this->team_model->setAutoDraft(false);
								  $this->session->set_flashdata('message','<span class="success">Auto draft has been disbaled for team ID # '.$dteam.'.</span>');
								  redirect('draft/teamSettings/league_id/'.$this->dataModel->league_id);
							   case 'auto_on_all':
								  $this->team_model->setLeagueAutoDraft($this->uriVars['league_id'],true);
								  $this->session->set_flashdata('message','<span class="success">Auto draft has been enabled for this league.</span>');
								  redirect('/draft/teamSettings/league_id/'.$this->uriVars['league_id']);
							   case 'auto_off_all':
								  $this->team_model->setLeagueAutoDraft($this->uriVars['league_id'],false);
								  $this->session->set_flashdata('message','<span class="success">Auto draft has been disabled for this league.</span>');
								  redirect('/draft/teamSettings/league_id/'.$this->uriVars['league_id']);
							   case 'auto_list_off':
								  $this->team_model->setAutoList(false);
								  $this->session->set_flashdata('message','<span class="success">Auto list usage has been enabled for this league.</span>');
								  redirect('/draft/teamSettings/league_id/'.$this->uriVars['league_id']);
							   case 'auto_list_off_all':
								  $this->team_model->setLeagueAutoList($this->uriVars['league_id'],false);
								  $this->session->set_flashdata('message','<span class="success">Auto list usage has been disabled for this league.</span>');
								  redirect('/draft/teamSettings/league_id/'.$this->uriVars['league_id']);
							   case 'selection':
							   case 'manualpick':
								  $pickingTeam=$this->uriVars['team_id'];
								  break;
							 }
							 if (isset($this->uriVars['pick_id'])) {
								$pick_id = intval($this->uriVars['pick_id']);
							 } else {
								 $pick_id = "";
							 } // END if
							 if (isset($this->uriVars['pick'])) {
								$dpick = intval($this->uriVars['pick']);
							 } else {
								 $dpick = "";
							 } // END if
							 // GET DRAFT ELIDGIBLE PLAYERS
							 $values = $this->dataModel->getPlayerValues();

							 // DRAFT SETTINGS FROM CONFIG
							 $draftEnable=$this->dataModel->draftEnable;
							 $pauseAuto=$this->dataModel->pauseAuto;
							 $setToAuto=$this->dataModel->setAuto;
							 $autoOpen=$this->dataModel->autoOpen;
							 $flexTimer=$this->dataModel->flexTimer;
							 $enforceTimer=$this->dataModel->enforceTimer;
							 $pauseTimer=-1;
							 
							 // DRAFT VARS
							 $drafted = array();
							 $lastPick=0;
							 $pickRound = 0;
							 $firstPick=999999;
							 
							 ##### Get draft order and drafted players #####
							 $picks = array();
							 $results = $this->dataModel->getDraftResults();
							 if (sizeof($results) > 0) {
								foreach($results as $row) {
									$tid=$row['team_id'];
									$pid=$row['player_id'];
									$round=$row['round'];
									$rndPick=$row['pick_round'];
									$pick=$row['pick_overall'];
									
									$drafted[$pid]=$pick;
									$picks[$pick]['player']=$pid;
									$picks[$pick]['team']=$tid;
									$picks[$pick]['round']=$round;
									$picks[$pick]['pick']=$rndPick;
									$picks[$pick]['due']=$row['due_date']." ".$row['due_time'].":00";
									
									if ($pick>$lastPick) {$lastPick=$pick;} // END if
									if (($pick<$firstPick) && ($pid=='')) {$firstPick=$pick;} // END if
								} // END foreach
							} // END if
							
							$teamList = array();
							if ($this->league_model->hasTeams()) {
								$teamList = $this->league_model->getTeamIdList();
							} // END if
							if ($this->debug) {
								echo("-----------DRAFT INIT--------------<br />");
								echo("Draft Action = ".$this->uriVars['action']."<br />");
								echo("Size of values array = ".sizeof($values)."<br />");
								if (isset($this->uriVars['pick'])) {
									echo("uri vars dpick = ".$this->uriVars['pick']."<br />");	
								}
								echo("first pick = ".$firstPick."<br />");
								echo("last pick = ".$lastPick."<br />");
								echo("picking team id = ".$dteam."<br />");
								echo("Size of picks array = ".sizeof($picks)."<br />");
								echo("Size of teamList array = ".sizeof($teamList)."<br />");
							} // END if
							if (!isset($this->team_model)) {
								$this->load->model('team_model');
							} // END if
							$teams = array();
							$teamQuotas = array();
							if (sizeof($teamList) > 0) {
								foreach ($teamList as $id) {
									if ($this->team_model->load($id)) {
										if ($this->uriVars['action']=='auto' && $this->dataModel->setAuto==1 && $this->team_model->auto_draft!=1) {
											$this->team_model->auto_draft = 1;
											$this->team_model->auto_round_x=$pickRound-1;
											$this->team_model->save();
										} // END if
										$teams[$id]['auto']=$this->team_model->auto_draft;
										$teams[$id]['autoList']=$this->team_model->auto_list;
										$teams[$id]['autoRound']=$this->team_model->auto_round_x;
										$teams[$id]['owner_id']=$this->team_model->owner_id;
										$teams[$id]['teamname']=$this->team_model->teamname;
										$teams[$id]['teamnick']=$this->team_model->teamnick;
										$teamQuotas[$id] = array();
									} // END if
								} // END foreach
							} // END if
							$rules = $this->league_model->getRosterRules();
							$nextPick = 0;
							if ($this->debug) {
								echo("Size of teams array = ".sizeof($teams)."<br />");
								echo("Size of drafted array = ".sizeof($drafted)."<br />");
								if ($this->uriVars['action']=='auto') {
									echo("auto option = ".$this->uriVars['auto_option']."<br />");
								}
							} // END if
							if (!isset($this->player_model)) {
								$this->load->model('player_model');
							} // END if
							
							$breakBeforePick = false;
							$breakAfterPick = false;
							$auto_pick_count = 0;
							$limitToRound = false;
							$picksMade = 0;
							/*----------------------------------------------
							/	AUTO PICK HANDLING
							/---------------------------------------------*/
							if ($this->uriVars['action']=='auto') {
								
								$auto_option = (isset($this->uriVars['auto_option']) && !empty($this->uriVars['auto_option'])) ? $this->uriVars['auto_option'] : 'current';
								// DETERMINE EXTENT OF AUTO PICK
								switch($auto_option) {
									case "all":
										$pauseAuto=-1;
										$setToAuto=1;
										$autoOpen=1;
										break;
									case "round":
										$limitToRound = true;
										break;
									case "x_picks":
										$auto_pick_count = (isset($this->uriVars['auto_pick_count']) && !empty($this->uriVars['auto_pick_count'])) ? $this->uriVars['auto_pick_count'] : 1;
										break;
									case "current":
										$breakAfterPick = true;
										break;
								}
								// GET TEAMS AUTO LIST
								$pickList = array();
								if ($teams[$dteam]['autoList']==1) {
									$pickList = $this->dataModel->getUserPicks($teams[$dteam]['owner_id']);
									foreach ($pickList as $pid => $val) {
										if (!isset($drafted[$val['player_id']])) { $dpick=intval($val['player_id']);break; } // END if
									}
								} // END if
								
								// NO PICK SPECIFIED BY THE AUTO LIST, SO MAKE THE PICK AUTOMATICALLY
								if ($dpick=="") {
									$aPick = $this->makeAutoPick($values, $teamQuotas, $dteam, $rules, $drafted);
									$dpick = $aPick[0];
									$teamQuotas = $aPick[1]; 
								} // END if
								
								
							} // END if
							/*----------------------------------------------
							/	END AUTO PICK HANDLING
							/---------------------------------------------*/
							if ($this->debug) {
								print("-----AUTO DRAFT REVIEW---------<br />");
								print("this->uriVars['action']=='auto' = ".(($this->uriVars['action']=='auto') ? "true" : "false")."<br />");
								print("auto_option = ".$auto_option."<br />");
								print("auto_pick_count = ".$auto_pick_count."<br />");
								print("limitToRound = ".(($limitToRound) ? "true" : "false")."<br />");
							}
							$error = false;
							$nextPick = 1;
							$pickRound = 0;
							$pickNum = 0;
							$pickOvrall = 0;
							$nTeams=sizeof($teams);
							$mailTo = '';
							$leagueName = $this->league_model->getLeagueName($this->dataModel->league_id);
							
							if ($this->debug) {
								echo("---------------------------------------<br />");
								echo("---------------------------------------<br />");
								echo("-         STARTING DRAFT LOOP         -<br />");
								echo("---------------------------------------<br />");
								echo("---------------------------------------<br />");
								echo("firstPick = '".$firstPick."'<br />");
								echo("Current player pick id = '".$dpick."'<br />");
								echo("breakBeforePick = '".(($breakBeforePick) ? "true" : "false")."'<br />");
								echo("breakAfterPick = '".(($breakAfterPick) ? "true" : "false")."'<br />");
							} // END if
							$this->lang->load('draft');
							
							/*---------------------------------------------------------
							/	PROCESS SELECTION
							/
							/	Loop from first open slot to final pick of the draft
							/
							/--------------------------------------------------------*/
								
							for ($i=$firstPick;$i<=$lastPick;$i++) {
								
								// ASSURE CURRENT PICK IS AN OPEN (non-drafted) slot. IF NOT, LOOP BACK
								// 	TO ADVANCE TO NEXT PICK
								if (isset($picks[$i]['player'])) {
									continue;
								} // END if
								
								//-----------------------------------------------
								// ROUND SUMMARY EMAIL
								//-----------------------------------------------
								$round = $picks[$i]['round'];
								//print('round pick = '.$picks[$i]['pick']."<br />");
								if ($i > 1 && ($picks[$i]['pick'] == 1 || $i == $lastPick)) {
									if ($this->dataModel->emailDraftSummary == 1) {
										
										$message = '';
										$msg = '';
										$teamInfo = $this->league_model->getTeamDetails($this->dataModel->league_id);
										
										$prevRound = $picks[$i-1]['round'];
										//print('prevRound = '.$prevRound."<br />");
										if (!isset($this->player_model)) {
											$this->load->model('player_model');
										} // END if
										$startPick = ($i - $nTeams);
										//print('i = '.$i.", prevRound = ".$prevRound.", nTeams*prevRound = ".$nTeams*$prevRound."<br />");
										//print('startPick = '.$startPick."<br />");
										$roundSummary = "";
										$stopAt= ($startPick+$nTeams);
										for ($t=$startPick;$t<$stopAt;$t++) {
											$tmpTeam=$picks[$t]['team'];
											$roundSummary.=$picks[$t]['round'].".".$picks[$t]['pick']." - ".anchor('/team/info/'.$tmpTeam,$teams[$tmpTeam]['teamname']." ".$teams[$tmpTeam]['teamnick']);
											$playerInfo = array();
											if (isset($picks[$t]['player']) && !empty($picks[$t]['player']) && $picks[$t]['player'] != -999) {
												$playerInfo = $this->player_model->getPlayerDetails($picks[$t]['player']);
												if (sizeof($playerInfo) > 0) {
													$roundSummary.= " picked  ".get_pos($playerInfo['position'])." ".anchor('/players/info/'.$picks[$t]['player'],$playerInfo['first_name']." ".$playerInfo['last_name']);
												} else {
													$roundSummary.= "Pick Details unavailable";
												} // END if
											} else if ($picks[$t]['player'] == -999) {
												$roundSummary.= "Pick Skipped";
											} else {
												$roundSummary.= "Pick information not available";
											} // END if 
											$roundSummary.= "<br />\n";
										} // END for
										$nextSummary = "";
										$stopAt = ($i+$nTeams);
										$nextSummary.="";
										for ($z=$i;$z<$stopAt;$z++) {
											$tmpTeam=$picks[$z]['team'];
											$nextSummary.=$picks[$z]['round'].".".$picks[$z]['pick']." - ".anchor('/team/info/'.$tmpTeam,$teams[$tmpTeam]['teamname']." ".$teams[$tmpTeam]['teamnick']);
											if ($this->dataModel->timerEnable == 1) {
												$nextSummary.=" due at ".$picks[$z]['due'];
											} // END if
											$nextSummary.= "<br />\n";
										} // END for
										## Generate Subject Line
										$subject=str_replace('[ROUND_NUM]',$prevRound,$this->lang->line('draft_aummary_subject'));
										$subject=str_replace('[LEAGUE_NAME]',$this->league_model->league_name,$subject);
										## Generate Message Text
										$msg = str_replace('[ROUND_NUM]',$prevRound,$this->lang->line('draft_aummary_message'));
										$msg = str_replace('[ROUND_NEXT]', $picks[$i]['round'],$msg);
										$msg = str_replace('[LEAGUE_NAME]', $this->league_model->league_name,$msg);
										$msg = str_replace('[ROUND_NUM_SUMMARY]', $roundSummary,$msg);
										$msg = str_replace('[ROUND_NEXT_SUMMARY]', $nextSummary,$msg);
										
										$data['leagueName'] = $this->league_model->league_name;
										$data['messageBody'] = $msg;
										$data['title'] = ' Draft Summary';
										$message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
										// LOAD OWNER ID AND EMAILS OF ALL OWNERS OF LEAGUE
										if ($this->league_model->id == -1) {
											$this->league_model->load($this->dataModel->league_id);
										} // END if
										//print ("league id = ".$this->dataModel->league_id."<br />");
										$ownerIds = $this->league_model->getOwnerIds($this->dataModel->league_id);
										//print ("owner id size = ".sizeof($ownerIds)."<br />");
										if (isset($ownerIds) && sizeof($ownerIds) > 0) {
											foreach($ownerIds as $owner) {
												$email = $this->user_auth_model->getEmail($owner);
												$ownerUsername = $this->user_auth_model->getUsername($owner);
												if (!empty($email)) {
													$sent = sendEmail($email,$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
													$this->params['config']['site_name']." Administrator",$subject,$message,$ownerUsername,'email_draft_summary_');
												} // END if
											} // END foreach
										}  // END if
										//	EDIT 1.0.6 - CLEAR EMAIL VARS AFTER SEND
										unset($roundSummary);
										unset($nextSummary);
										unset($message);
										unset($msg);
										unset($data['messageBody']);
									}  // END if
									
									//	EDIT 1.0.5 - AUTO PICK OPTION EXPANSION
									// LIMIT TO ROUND CHECK
									if ($limitToRound && $picksMade > 0) {
										$breakBeforePick = true;
										if ($this->debug) {
											print("Round Limit Reached, breaking draft<BR />");
										}
									} // END if
									
								} // END if ($rndPick == $nTeams)
								// ----------------------------------
								//	END ROUND SUMMARY EMAIL
								// ----------------------------------
								
								/*-----------------------------------------------
								/	EDIT 1.0.5 - AUTO PICK OPTION EXPANSION
								/--------------------------------------------- */
								// CHECK IF WE'VE REACHED A PICK LIMIT
								if ($auto_pick_count > 0 && $picksMade> 0 && $picksMade == $auto_pick_count) {
									if ($this->debug) {
										echo("Max auto pick count of ".$auto_pick_count." reached, ".$picksMade." picks made.<br />");
									}
									$breakBeforePick = true;
								} // END if
								// IF WE SHOULD BREAK OUT OF THE LOOP, DO SO
								if ($this->debug) {
									print("breakBeforePick? ".(($breakBeforePick) ? "true" : "false")."<BR />");
								}
								
								if ($breakBeforePick === true) break;
								// END 1.0.5 EDITS
								
								if ($this->debug) {
									echo("---------------------------------------<br />");
									echo("------------BEGIN NEW PICK---------------<br />");
									echo("---------------------------------------<br />");
									echo("Current Pick = ".$i."<br />");
									echo("picks made already = '".$picksMade."'<br />");
									if (isset($picks[$i])) {
										echo("Current picking team = ".$dteam."<br />");
										echo("Team for pick = ".$picks[$i]['team']."<br />");
									}
								}
								/*------------------------------------------------
								/	DRAFT PICK VALIDATION
								/-----------------------------------------------*/
								##### Check that team matches active pick #####
								if (isset($picks[$i]) && $dteam!=$picks[$i]['team']) {
									$error = true;
									$this->params['outMess'] =  "Incorrect team for pick $i. ".$picks[$i]['team']." should be picking.";
								} ## Team trying to draft out of turn
								
								##### Check that player is not yet drafted #####
								if (isset($drafted[$dpick])) {
									$error = true;
									$this->params['outMess'] =  "Player $dpick has already been drafted.";
								} ## Team trying to draft player already taken
								
								##### Check that player is not yet drafted #####
								if (!isset($values[$dpick])) {
									$error = true;
									$this->params['outMess'] =  "Player $dpick is not draft eligible.";
								}
								if ($this->debug) {
									echo("ERROR? = ".($error ? 'true' : 'false')."<br />");
								}
								// IF WE PASSED VALIDATION, MAKE A PICK
								// OTHERWISE< SHOW THE ERROR
								if (!$error) {
									##### Make Pick #####
									$this->dataModel->draftPlayer(date("Y-m-d",time()), date("H:i",time()),$dpick,$dteam,$i);
									$drafted[$dpick]=$i;
									$picks[$i]['player']=$dpick;
									$picks[$i]['team']=$dteam;
									$picksMade++;
								} else {
									$this->data['theContent'] = '<span class="error">'.$this->params['outMess'].'</span>';
									//continue;
								}
								
								// STOP LOOPING IF WE HAVE HIT AN ERROR OR THE LAST PICK
								// EDIT 1.0.5
								// SOMETIME WE WANT TO BREAK AFTER THE PICK IS MADE (AVOID LOOPING)
								if ($error || $i>$lastPick || $breakAfterPick === true) {
									break;
								}
								if ($this->debug) {
									echo("Get Next team in draft order<br />");
								}
								$dpick = '';
								##### Get next team in draft order #####
								for ($j=$i+1;$j<=$lastPick;$j++) {
									if (isset($picks[$j]['round'])) {
										if (!isset($picks[$j]['player'])) {
											$dteam=$picks[$j]['team'];
											$i=$j-1;
											$nextPick = $j;
											break;
										} // END if
									} else {
										break;
									} // END if
								} // END for
                                // EDIT 1.0.6 - FAIL SAFE IN CASE WE GET HERE AND THE NEXT PICK IS OUTSIDE THE
						        // DRAFT SIZE RANGE
								if ($j > $lastPick) { break; }
								if ($this->debug) {
									echo("Next pick ID = ".$j."<br />");
									echo("Next pick = ".$nextPick."<br />");
									echo("Pick Round =  ".$pickRound."<br />");
									echo("Next team to pick = ".$dteam."<br />");
									echo($dteam." has auto list? ".($teams[$dteam]['autoList']==1?'true':'false')."<br />");
								} // END if
								##### Check team for auto draft list #####
								if ($teams[$dteam]['autoList']==1) {	   
									$dpick='';
									$pid = 0;
									$pickList = $this->dataModel->getUserPicks($teams[$dteam]['owner_id']);
									foreach($pickList as $rank =>$data) {
										$pid = intval($data['player_id']);
										break;
									}
								}
								if (($pid!="") && ($pid!=0) && !isset($drafted[$pid])) {
									$dpick=$pid;
								}
								## - Loop back
								if ($this->debug) {
									echo("Team ".$dteam." Auto List pick = '".$dpick."'<br />");
								}
								if ($dpick!='') {continue;}
								
								if ($this->debug) {
									echo("-----NO AUTO LIST PICK---<br />");
									echo("-----FORCE AUTO SELECT, AUTO OPEN NO OWNER TEAM, TEAM AUTO PICK OPTION---<br />");
								}
								##### Check if team is not human controlled #####
                                if ($this->debug) {
                                   print("J = ".$j."<br />");
                                }

								$pickRound=$picks[$j]['round'];
								$pickNum = $picks[$j]['pick'];
								if ($this->debug) {
									echo("pauseAuto =  ".$pauseAuto."<br />");
									echo("autoOpen =  ".$autoOpen."<br />");
									echo("teams[".$dteam."]['autoRound'] =  ".$teams[$dteam]['autoRound']."<br />");
									echo("teams[".$dteam."]['auto'] =  ".$teams[$dteam]['auto']."<br />");
								}
								if ($this->uriVars['action']=='auto' && ($auto_option == 'all' || //BRUTE FORCE AUTO PICK
								($auto_option =='x_picks' && $picksMade < $auto_pick_count) || //BRUTE FORCE X NUMBER OF AUTO PICK
								$limitToRound) || //BRUTE FORCE PICK TO END OF ROUND
								($pauseAuto==-1 && $this->uriVars['action']!='manualpick') && // NO PAUSING ON AUTO PICK AND NOT A MANUAL PICK
								((!isset($teams[$dteam]['owner_id']) && $autoOpen==1) // NO OWNER AND WE SHOULD AUTO PICK FOR THEM
								|| ($teams[$dteam]['auto']==1 && $pickRound>=$teams[$dteam]['autoRound']))) { // TEAM AUTO DRAFT OVER THE ROUND LIMIT
									
									## Set human team to auto - if here, already know team is not using list or on auto already
									if (($this->uriVars['action']!='manualpick' && $this->uriVars['action']!='auto') && 
									($setToAuto==1) && (isset($teams[$dteam]['owner_id'])) && ($teams[$dteam]['auto']!=1)) {
										$this->team_model->load($dteam);
										$this->team_model->setAutoDraft(true);
										$teams[$dteam]['auto']=1;
										$teams[$dteam]['autoRound']=$pickRound-1;
									}
									
									
									## - Determine Auto Pick
									$aPick = $this->makeAutoPick($values, $teamQuotas, $dteam, $rules, $drafted);
									$dpick = $aPick[0];
									$teamQuotas = $aPick[1];
									if ($dpick != '') {
										if ($this->debug) {
											echo("No human owner team pick = '".$dpick."'<br />");
										}
										continue;
									}
								}
								##### Check if next team is past due #####
								$curTime=time();
								$pickInst=strtotime($picks[$j]['due']);
								if (($this->dataModel->timerEnable==1) && ($pickInst<=$curTime) && ($this->uriVars['action']!='manualpick') && ($enforceTimer==1)) {
									if ($this->debug) {
										echo("****pICK IS PAST DUE<br />");
										echo("****ENFORCING TIMER<br />");
									} 
									## Determine Auto Pick
									$aPick = $this->makeAutoPick($values, $teamQuotas, $dteam, $rules, $drafted);
									$dpick = $aPick[0];
									$teamQuotas = $aPick[1];
									if ($dpick != '') {
										continue;
									}
								}
								if ($this->debug) {
									echo("****Last resort auto pick<br />");
								} 
								##### Check if team is not human controlled #####
								if ($dpick == '' && ($this->uriVars['action']!='manualpick'&&$pauseAuto==-1 && $autoOpen==1)) {
									if ($this->debug) {
										echo("In the last resort auto pick for team ".$dteam."<br />");
									} 
									## - Determine Auto Pick
									$aPick = $this->makeAutoPick($values, $teamQuotas, $dteam, $rules, $drafted);
									$dpick = $aPick[0];
									$teamQuotas = $aPick[1];
									if ($dpick != '') {
										continue;
									} // END if
								} // END if
							
								if ($this->debug) {
									echo("Player to be drafted = '".$dpick."'<br />");
								} // END if
								##### If we make it here, time for another manual pick #####
								break;
							} // END for (draft pick loop)
							
							$pickingTeam = $dteam;
							if (isset($picks[$nextPick]['team'])) {
								$nextTeam = $picks[$nextPick]['team'];
							}
							if (!$error) {
								if (isset($j) && $j>=$lastPick) {
									redirect('draft/load/'.$this->uriVars['league_id']);
								}						
								/*------------------------------------------
								/
								/	EMAIL COMMUNICATIONS
								/
								/-----------------------------------------*/
								// EDIT 7/7/10
								// CHANGE FROM MASS MAIL FOR EVERY PICK TO SINGLE USER EMAIL
								
								//-----------------------------------------------
								// INDIVIDUAL EMAIL
								//-----------------------------------------------
								// GET NEXT PICKING OWNER INFO
								$nextOwnerEmail = '';
								if (isset($teams[$dteam]['owner_id']) && !empty($teams[$dteam]['owner_id']) && $teams[$dteam]['owner_id'] != -1 &&
								$this->dataModel->emailOwnersForPick == 1) {
									$nextOwnerDetails = $this->user_auth_model->accountDetails($teams[$dteam]['owner_id']);
									if (isset($nextOwnerDetails)) {
										$nextOwnerEmail = $nextOwnerDetails->email;
									}
									$this->user_meta_model->load($teams[$dteam]['owner_id'],'userId');
									$nextOwnerName = '';
									
									if ($this->user_meta_model->id != -1) {
										$nextOwnerName = $this->user_meta_model->firstName." " .$this->user_meta_model->lastName;
									} // END if
									
									$msg = $this->lang->line('draft_user_message_pick_next');
									$msg .= $this->lang->line('email_footer');
									$msg = str_replace('[USER_NAME]',$nextOwnerName,$msg);
									$msg = str_replace('[LEAGUE_NAME]', $this->league_model->league_name,$msg);
									$msg = str_replace('[DRAFT_URL]', anchor('draft/selection/league_id/'.$this->league_model->id,'Draft Selection Site'),$msg);
									$data['messageBody'] = $msg;
									//print("email template path = ".$this->config->item('email_templates')."<br />");
									$data['leagueName'] = $this->league_model->league_name;
									$data['title'] = $this->lang->line('draft_email_title_pick_due');
									$message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
									// Generate Subject Line
									$subject= str_replace('[LEAGUE_NAME]',$leagueName,$this->lang->line('draft_user_subject_pick_next'));
									$mailTo = $nextOwnerName.' <'.$nextOwnerEmail.'>';
									// SEND TO OWNER
									if ((!empty($mailTo)) && (!empty($message)) && ($this->uriVars['action'] !='manualpick')) {
										$emailSend = sendEmail($mailTo,$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
										$this->params['config']['site_name']." Administrator",$subject,$message,'','email_draft_next_pick_');
									}
									unset($data['title']);
									unset($data['messageBody']);
									unset($subject);
									unset($message);
								}
								/*-----------------------------------------
								/
								/	END MESSAGING BLOCK
								/
								/-----------------------------------------*/
								
								/*-------------------------------------------
								/	WRAP UP
								/------------------------------------------*/
								if ($pickingTeam==$nextTeam && $nextTeam!="" && $this->uriVars['action']!='manualpick') {
									redirect('draft/selection/league_id/'.$this->uriVars['league_id']);
								} else {
									redirect('draft/load/'.$this->uriVars['league_id']);
								} // END if ($pickingTeam==$nextTeam)
								
							} // END if (!$error)
						} else {
							$error = true;
							$this->data['theContent'] = '<span class="error">No draft action was defined.</span>';
						} // END if
					} else {
						$error = true;
						$this->data['theContent'] = '<span class="error">You do not have sufficient privlidges to perform the requested action.</span>';
					} // END if
				//} else {
				//	$error = true;
				//	$this->data['theContent'] = '<span class="error">This leagues draft has already been completed.</span>';
				//}
			} else {
				$error = true;
				$this->data['theContent'] = '<span class="error">A required league identifier could not be found. Your selections could not be processed at this time.</span>';
			} // END if
			if ($error) {
				$this->data['subTitle'] = 'Draft: An error occured.';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			} // END if
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    } // END if
	}
	protected function makeEmailHeader() {
		// CREATE GENERIC EMAIL HEADER
		$header="From: ".$this->dataModel->replyList."\r\n";
		$header.="MIME-Version: 1.0\r\n";
		$header.="Content-type: text/html; charset=iso-8859-1\r\n";
	}
	protected function makeEmailFooter($leagueName) {
		return '<br><br>
			Regards,<br>
			The Commisionner<br>
			'.$leagueName.'<br>'.SITE_URL;
	}
	/**
	 *	MAKE AUTO PICK
	 *
	 *	Enforces the league roster rules when detemrining the next auto pick when a team draft list
	 * 	is either empty or not in use.
	 *	@param	$values			List of players that can be drafted
	 *	@param	$teamQuotas		Array of values of what roster spots have been filled
	 *	@param	$dteam			Curent picking team
	 *	@param	$rules			Array of roster rules
	 *	@return					Array [0] = current player pick, [1] Updated team Quotas array
	 */
	protected function makeAutoPick($values, $teamQuotas, $dteam, $rules, $drafted) {
		$dpick = '';
        $posUtil = get_pos_num('U');
        $hasUtil = false;
        foreach($rules as $ruleKey => $ruleVal) {
           if ($ruleKey == $posUtil) {
               $hasUtil = true;
               break;
           }
        }
        // $posIF = get_pos_num('IF');
        // $posMI = get_pos_num('MI');
        // $posCI = get_pos_num('CI');
        if ($this->debug) {
            echo("-----------------------------------------------<br/>");
            echo("----------------MAKE AUTO PICK- ---------------<br/>");
            echo("----------------\$draft->makeAutoPick ---------<br/>");
			echo("-----------------------------------------------<br/>");
			echo("League uses U position, hasUtil =  ".$hasUtil."<br/>");
			if (isset($teamQuotas[$dteam][$posUtil])) {
                echo("teamQuotas[dteam][posUtil] =  ".$teamQuotas[$dteam][$posUtil]."<br/>");
            }
        }
        foreach ($values as $pid => $val) {
			if (!isset($drafted[$pid])) {
				if (!isset($this->player_model)) {
					$this->load->model('player_model');
				}
				// ENFORCE ROSTER LIMITS ON DRAFTED PLAYERS
				$details = $this->player_model->getPlayerDetails($pid);
				$pos = 0;
				if ($details['position'] == 1) {
					if ($details['role'] == 13) {
						$pos = 12;
					} else {
						$pos = $details['role'];
					}
				} else {
					if ($hasUtil && !isset($teamQuotas[$dteam][$posUtil])) {
                        $pos = $posUtil;
                    } else {
                        if ($details['position']== 7 || $details['position'] == 8 || $details['position'] == 9) {
                            $pos = 20;
                        } else {
                            $pos = $details['position'];
                        }
                    }
				}
				// TEST THIS PLAYERS POSITION AGAINST TEAM QUOTA
				if ($this->debug) {
					echo("CHECKING POSITION<br />");
					echo("Draft Team ID, dteam= ".$dteam."<br />");
                    echo("Player $pid OOTP position = ".get_pos($pos)."<br />");
				}
				$draft = true;
                if (isset($teamQuotas[$dteam][$pos])) {
                    if ($this->debug) {
						echo("CHECKING QUOTAS<br />");
						echo("teamQuotas[".$dteam."][".$pos."] = ".$teamQuotas[$dteam][$pos]."<br />");
                        echo("rules[".$pos."]['active_max'] = ".$rules[$pos]['active_max']."<br />");
                        echo("teamQuotas[".$dteam."][".$pos."] >= rules[".$pos."]['active_max'] = ");
                        echo((($teamQuotas[$dteam][$pos] >= $rules[$pos]['active_max']) ? "true" : "false")."<br />");
                    }
                    if ($teamQuotas[$dteam][$pos] >= $rules[$pos]['active_max']) {
                        $draft = false;
                    } else {
                        $teamQuotas[$dteam][$pos] += 1;
                    }
                } else {
                    $teamQuotas[$dteam][$pos] = 1;
                }
				if ($draft) { $dpick=$pid; break; }
			}
		}
		return array($dpick,$teamQuotas);
	}
	/**
	 *	SELECTION.
	 *
	 *	Draws the draftselection (warroom in StatsLab) sreen.
	 */
	public function selection() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadModel();
			$this->params['subTitle'] = "Draft";
			
			if ($this->dataModel->id != -1) {
				// GET DRAFT STATUS
				$status = $this->dataModel->getDraftStatus();
				//-----------------------------------------------------------------------
				// UPDATE 1.0.3
				// TEST TO ASSURE PLAYERS HAVE BEEN IMPORTED BEFORE DISPLAYING THE PAGE
				//-----------------------------------------------------------------------
				if (!isset($this->player_model)) {
					$this->load->model('player_model');
				}
				if ($this->player_model->getPlayerCount() < 1) {
					$this->data['subTitle'] = 'Draft: An error occured.';
					$this->data['theContent'] = '<span class="warning">This leagues inital setup has not been completed. There are no players eligible to be drafted.</span>';
					$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				} else if ($status >= 4) {
					$this->data['subTitle'] = 'Draft: An error occured.';
					$this->data['theContent'] = '<span class="warning">Your leagues draft has been completed. Please see the draft history page to review the draft results.</span>';
					$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				} else {
					$pick_id = 0;
					$pick_team_id = -1;
					
					// GET CURRENT DRAFT PICK
					$pick = $this->dataModel->getCurrentPick();
					if ($pick) {
						$pick_id = $pick['pick_overall'];
						$pick_team_id = $pick['team_id'];
					}
					$this->data['pick_id'] = $pick_id;
					$this->data['team_override'] = false;
					$this->data['pick_team_id'] = $pick_team_id;
					$userTeams = $this->user_meta_model->getUserTeamIds($this->dataModel->league_id,$this->params['currUser']);
					$this->data['user_team_id'] = $userTeams[0];
					$this->data['team_owner_id'] = $this->params['currUser'];
					$this->data['isCommish'] = $this->league_model->userIsCommish($this->params['currUser'],$this->dataModel->league_id); 
					$this->data['isAdmin'] = $this->params['accessLevel'] == ACCESS_ADMINISTRATE; 
					
					if ($this->debug) {
						echo("Curr user Id = ".$this->params['currUser']."<br />");
						echo("Current pick = ".$pick_id."<br />");
						echo("Current team pikcing = ".$pick_team_id."<br />");
					}
					// GET OWNER INFO FOR "ACT AS..." MENU
					if ($this->data['isCommish'] || $this->data['isAdmin']) {
						$this->data['ownerList'] = $this->league_model->getOwnerInfo($this->dataModel->league_id,true);
						if (isset($this->uriVars['act_as_id']) && !empty($this->uriVars['act_as_id']) && $this->uriVars['act_as_id'] != $this->params['currUser']) {
							$this->data['user_team_id'] = $this->uriVars['act_as_id'];
							$this->data['team_owner_id'] = $this->resolveTeamOwner($this->data['user_team_id']);
							$this->data['team_override'] = true;
						}
					}
					if (!isset($this->uriVars['player_id']) || empty($this->uriVars['player_id'])) {
						$this->uriVars['player_id'] = -1;
					}
					$this->data['player_type'] = $player_type = (isset($this->uriVars['player_type'])) ? $this->uriVars['player_type'] : 1;
					$this->data['position_type'] = $position_type = (isset($this->uriVars['position_type'])) ? $this->uriVars['position_type'] : -1;
					$this->data['role_type'] = $role_type = (isset($this->uriVars['role_type'])) ? $this->uriVars['role_type'] : -1;
					
					if (!isset($this->uriVars['min_plate']) || empty($this->uriVars['min_plate'])) {
						$this->uriVars['min_plate'] = 100;
					}
					if (!isset($this->uriVars['min_inning']) || empty($this->uriVars['min_inning'])) {
						$this->uriVars['min_inning'] = 20;
					}
					if ($player_type == 1) {
						$positionVar = 	$position_type;
						$minVar = $this->uriVars['min_plate'];
					} else {
						$positionVar = 	$role_type;
						$minVar = $this->uriVars['min_inning'];
					}
					if (!isset($this->uriVars['stats_range']) || empty($this->uriVars['stats_range'])) {
						$this->uriVars['stats_range'] = 1;
					}
					if (!function_exists('getNonFreeAgentsByLeague')) {
						$this->load->helper('roster');
					}
					$this->data['limit'] = $limit = (isset($this->uriVars['limit'])) ? $this->uriVars['limit'] : DEFAULT_RESULTS_COUNT;
					$this->data['pageId'] = $pageId = (isset($this->uriVars['pageId'])) ? $this->uriVars['pageId'] : 1;	
					
					$startIdx = 0;
					if ($limit != -1) {
						$startIdx = ($limit * ($pageId - 1))-1;
					}
					if ($startIdx < 0) { $startIdx = 0; } // END if
					$this->data['startIdx'] = $startIdx;
					
					$league_id = -1;
					if(isset($this->uriVars['league_id'])) {
						$league_id = $this->uriVars['league_id'];
					} else {
						$league_id = $this->session->userdata('league_id');
						if (!isset($league_id)) {
							$league_id = -1;
						} // END if
					} // END if
					$this->data['team_list'] = array();
					$this->data['scoring_rules'] = $rules = $this->league_model->getScoringRules(0);
					if ($league_id != -1) {
						$this->data['team_list'] = $this->league_model->getTeamDetails($league_id);
						//if ($this->params['loggedIn']) {
						//	$this->data['userTeamId'] = $this->user_meta_model->getUserTeamIds($league_id,$this->data['team_owner_id']);
						//}
						$rules = $this->league_model->getScoringRules($league_id);
						if (sizeof($rules) == 0) {
							$rules = $this->league_model->getScoringRules(0);
						} // END if
					} // END if
					$this->data['league_id'] = $league_id;
					
					$this->data['league_date'] = EMPTY_DATE_STR;
					$currDate = strtotime($this->ootp_league_model->current_date);
					$startDate = strtotime($this->ootp_league_model->start_date);
					if ($currDate <= $startDate) {
						$this->data['league_date'] = date('Y-m-d',$currDate - (60*60*24*365));
					} else {
						$this->data['league_date'] = date('Y-m-d',$currDate);
					} // END if
					// RAW Record Count total with no limit
					$this->data['recCount'] = sizeof($this->dataModel->getPlayerPool(true,$this->params['config']['ootp_league_id'], $player_type, $position_type,  $role_type, $this->uriVars['stats_range'], $minVar, -1, 0, $league_id,$this->data['league_date'],$rules));
					// Actual results with limit applied
					$player_stats = $this->dataModel->getPlayerPool(false,$this->params['config']['ootp_league_id'], $player_type, $position_type,  $role_type, $this->uriVars['stats_range'], $minVar, $limit, $startIdx, $league_id,$this->data['league_date'],$rules);
					//echo($this->db->last_query()."<br />");
					$this->data['pageCount'] = 1;
					if ($limit != -1) {
						$this->data['pageCount'] = intval($this->data['recCount'] / $limit);
					} // END if
					if ($this->data['pageCount'] < 1) { $this->data['pageCount'] = 1; }
					if ($player_type == 1) {
						$this->data['title'] = "Batting";
					} else {
						$this->data['title'] = "Pitching";
					} // END if
					$this->data['colnames']=player_stat_column_headers($player_type, QUERY_STANDARD, false, false, true, true, true);
					$this->data['fields']=player_stat_fields_list($player_type, QUERY_STANDARD, false, false, true, true, true);
					$this->data['showTeam'] = -1;
					$this->data['showTrans'] = 1;
					$this->data['showDraft'] = 1;
					$this->data['player_stats'] = formatStatsForDisplay($player_stats, $this->data['fields'], $this->params['config'],$league_id,false,false, false, true, true, $this->data['pick_team_id'],$this->data['user_team_id'],$status, $this->params['accessLevel'], $this->data['isCommish'],$this->dataModel->draftDate);
					$this->data['formatted_stats'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
					
					//$this->data['playerList'] = $this->dataModel->getPlayerPool($this->dataModel->league_id,$this->params['config']['ootp_league_id'], $this->uriVars['player_type'], $positionVar, $this->uriVars['stats_range'], $minVar);
					
					//echo($this->db->last_query()."<br />");
					$this->data['draftStatus'] = $status;
					$this->data['subTitle'] = "Draft Selections";
					//$this->data['player_type'] = $player_type'];
					//$this->data['position_type'] = $this->uriVars['position_type'];
					$this->data['min_plate'] = $this->uriVars['min_plate'];
					//$this->data['role_type'] = $this->uriVars['role_type'];
					$this->data['min_inning'] = $this->uriVars['min_inning'];
					$this->data['league_id'] = $this->dataModel->league_id;
					$this->data['player_id'] = $this->uriVars['player_id'];
					$this->data['stats_range'] = $this->uriVars['stats_range'];
					$this->params['content'] = $this->load->view($this->views['SELECT'], $this->data, true);
				}
				$this->params['pageType'] = PAGE_FORM;
			} else {
				$this->data['subTitle'] = 'Draft: An error occured.';
				$this->data['theContent'] = '<span class="error">A required league identifier could not be found. Your selections could not be processed at this time.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
			} // END if
			$this->makeNav();
			$this->displayView();
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    } // END if
	}
	public function rescheduleDraft() {
		$this->dataModel->draftSchedule($this->league_model->getTeamDetails(), $this->league_model->id);
		$this->dataModel->save();
		return true;
	}
	/*-------------------------------------------------
	/	AJAX/JSON FUNCTIONS
	/------------------------------------------------*/
	/**
	 * 	SAVE DRAFT LIST
	 * 	AJAX/JSON function that accepts a string of player ids and saved them to the DB.
	 * 	@param 		$this->uriVars['player_id_list']	{String} 	List of player ids
	 *  @param 		$this->uriVars['user_id']			(Integer) 	Optional Fantasy user ID
	 *  @return											{JSON}		JSON Object response
	 *  @since		1.0.5
	 *  @access		public
	 */
	public function saveDraftList() {
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code =200;
		$result = "";
		$user_id = $this->params['currUser'];
		if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id'])) {
			$user_id = $this->uriVars['user_id'];
		} // END if
		if ($this->dataModel->id != -1) {
			if((isset($this->uriVars['player_id_list']) && !empty($this->uriVars['player_id_list']))) {
				$player_ids = array();
				// PARSE THE LIST OF PLAYER IDs
				// THERE are three possible combinations:
				//  - Multiple players seperate by an underscoe "_"
				//	- Single player id, no underscores
				//	- 'empty' command which will clear the list
				if (strpos($this->uriVars['player_id_list'],"_") != false) {
					$player_ids = explode("_",$this->uriVars['player_id_list']);
				} else {
					if ($this->uriVars['player_id_list'] != 'empty') {
						array_push($player_ids,$this->uriVars['player_id_list']);
					}
				} // END if
				if (!$this->dataModel->saveDraftList($player_ids,$user_id)) { 
					$status .= "error:Your list was not saved.";
					if (!empty($this->dataModel->statusMess)) {
						$status .=  " ".$this->dataModel->statusMess;
					} // END if
				} else {
					$status .= "Draft list Successfully Saved.";
				} // END if
				$result = "Success";
			} else {
				$status .= "error: Player ID list missing.";
			} // END if
		} else {
			$status .= "error:League Identifier Missing";
		} // END if
		$result = '{"result": "'.$result.'","code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 * 	GET DRAFT PICKS
	 * 	AJAX/JSON function that returns an array object of draft list picks.
	 * 	@param 		$this->uriVars['user_id']			(Integer) 	Optional Fantasy user ID
	 *  @return											{JSON}		JSON Object response
	 *  @since		1.0
	 *  @access		public
	 */
	public function getPicks() {
		$this->init();
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code = 0;
		$result = "";
		$user_id = $this->params['currUser'];
		if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id'])) {
			$user_id = $this->uriVars['user_id'];
		} // END if
		if ($this->dataModel->id != -1) {
			$result = $this->loadPickList($this->dataModel->league_id, $user_id, true);
			$status .= "OK";
			$code = 200;
			$result =  '{ "items": ['.$result.']}';
		} else {
			$status .= "error:League Identifier Missing";
			$code = 203;
			$result = '""';
		}
		$result = '{"result": '.$result.',"code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 * 	GET DRAFT RESULTS.
	 *	Returns a JSON encoded string of the specified users draft results. Defaults to current 
	 *	uaser if none specified.
	 * 	@param 		$this->uriVars['user_id']			(Integer) 	Optional Fantasy user ID
	 *  @return											{JSON}		JSON Object response
	 *  @since		1.0
	 *  @access		public
	 */
	public function getResults() {
		$this->init();
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code = 0;
		$result = "";
		$user_id = $this->params['currUser'];
		if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id'])) {
			$user_id = $this->uriVars['user_id'];
		} // END if
		if ($this->dataModel->id != -1) {
			$result = $this->loadUserResults($this->dataModel->league_id, $user_id, true);
			$status .= "OK";
			$code = 200;
			$result =  '{ "items": ['.$result.']}';
		} else {
			$status .= "error:League Identifier Missing";
			$code = 203;
			$result = '""';
		}
		$result = '{"result": '.$result.',"code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 *	LOAD USER RESULTS.
	 *	Loads draft results for a spefici user id ans returns an array of picks (uif any have been made).
	 *
	 *	@access	protected
	 *	@param	$league_id		(Integer)	Fantasy League ID
	 *	@param	$user_id		(integer)	User ID (Defaults to currUser if not specified)
	 *	@param	$return			(Boolean)	(Not cuurrently used)
	 *	@return					(Array)		Array of pick information		
	 *	@since 	1.0
	 */
	protected function loadUserResults($league_id = false, $user_id = false, $return = false) {
		
		if ($user_id === false) { $user_id = $this->params['currUser']; }
		$status = "";
		$code = 0;
		$result = '';
		if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id'])) {
			$user_id = $this->uriVars['user_id'];
		} // END if
		$list = $this->dataModel->getUserResults($user_id);
		if (sizeof($list) > 0) {
			foreach ($list as $round => $data) {
				if ($result != '') { $result .= ','; }
				$result .= '{"id":"'.$data['player_id'].'","player_name":"'.$data['player_name'].'","position":"'.$data['position'].'","draft_round":"'.$round.'","draft_pick":"'.$data['pick'].'"}';
			}
			$status .= "OK";
			$code = 200;
		}
		if (strlen($result) == 0) {
			$status .= "notice:No players found";
			$code = 201;
		}
		return $result;
	}
	/**
	 *	LOAD DRAFT PICK LIST.
	 *	Loads draft list pick for a spefici user id ans returns an array of picks (if any have been made).
	 *
	 *	@access	protected
	 *	@param	$league_id		(Integer)	Fantasy League ID
	 *	@param	$user_id		(integer)	User ID (Defaults to currUser if not specified)
	 *	@param	$return			(Boolean)	(Not cuurrently used)
	 *	@return					(Array)		Array of pick information	
	 *	@since 	1.0
	 */
	protected function loadPickList($league_id = false, $user_id = false, $return = false) {
		
		if ($user_id === false) { $user_id = $this->params['currUser']; }
		$status = "";
		$code = 0;
		if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id'])) {
			$user_id = $this->uriVars['user_id'];
		} // END if
		$list = $this->dataModel->getUserPicks($user_id);
		$result = '';
		if (isset($this->uriVars['act_as_id']) && !empty($this->uriVars['act_as_id'])) {
			$user_id = $this->resolveTeamOwner($this->uriVars['act_as_id']);
		}
		if (sizeof($list) > 0) {
			foreach ($list as $rank => $data) {
				if ($result != '') { $result .= ','; }
				$result .= '{"id":"'.$data['player_id'].'","player_name":"'.$data['player_name'].'","position":"'.$data['position'].'","rank":"'.$rank.'"}';
			}
			$status .= "OK";
			$code = 200;
		}
		if (strlen($result) == 0) {
			$status .= "notice:No players found";
			$code = 201;
		}
		return $result;
	}
	/**
	 *	RESOLVE TEAM OWNER.
	 *	Internal utility function that returns the owner ID for a given team.
	 *
	 *	@access	protected
	 *	@param	$team_id		(Integer)	Fantasy Team ID
	 *	@return					(Integer)	Fantasy Owner (user) ID	
	 *	@since 	1.0.5
	 */
	protected function resolveTeamOwner($team_id) {
		if (!isset($this->team_model)) {
			$this->load->model('team_model');
		}
		return $this->team_model->getTeamOwnerId($team_id);
	}
	/**
	 *	LOAD MODEL.
	 *	Internal utility function that loads the draft_model object using approriate uriVars data.
	 *
	 *	@access	protected
	 *	@return					{Void}
	 *	@since 	1.0
	 */
	protected function loadModel() {
		
		if (isset($this->uriVars['league_id']) && $this->uriVars['league_id'] != -1) {
			$league_id = $this->uriVars['league_id'];
		} else if (isset($this->uriVars['id']) && !isset($this->uriVars['league_id']) && $this->uriVars['id'] != -1) {
			$league_id = $this->uriVars['id'];	
		} else {
			$league_id = $this->session->userdata('league_id');
		}
		$this->dataModel->load($league_id,'league_id',true);	
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 *	@access	protected
	 *	@return					{Void}
	 *	@since 	1.0
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('draft_id')) {
			$this->uriVars['draft_id'] = $this->input->post('draft_id');
		} // END if
		if ($this->input->post('league_id')) {
			$this->uriVars['league_id'] = $this->input->post('league_id');
		} // END if
		if ($this->input->post('player_type')) {
			$this->uriVars['player_type'] = $this->input->post('player_type');
		} // END if
		if ($this->input->post('position_type')) {
			$this->uriVars['position_type'] = $this->input->post('position_type');
		} // END if
		if ($this->input->post('min_plate')) {
			$this->uriVars['min_plate'] = $this->input->post('min_plate');
		} // END 
		if ($this->input->post('role_type')) {
			$this->uriVars['role_type'] = $this->input->post('role_type');
		} // END if
		if ($this->input->post('min_inning')) {
			$this->uriVars['min_inning'] = $this->input->post('min_inning');
		} // END if
		if ($this->input->post('stats_range')) {
			$this->uriVars['stats_range'] = $this->input->post('stats_range');
		} // END if
		if ($this->input->post('player_id')) {
			$this->uriVars['player_id'] = $this->input->post('player_id');
		} // END if
		if ($this->input->post('team_id')) {
			$this->uriVars['team_id'] = $this->input->post('team_id');
		} // END if
		if ($this->input->post('action')) {
			$this->uriVars['action'] = $this->input->post('action');
		} // END if
		if ($this->input->post('pick')) {
			$this->uriVars['pick'] = $this->input->post('pick');
		} // END if
		if ($this->input->post('pick_id')) {
			$this->uriVars['pick_id'] = $this->input->post('pick_id');
		} // END if
		if ($this->input->post('round')) {
			$this->uriVars['round'] = $this->input->post('round');
		} // END if
		if ($this->input->post('limit')) {
			$this->uriVars['limit'] = $this->input->post('limit');
		} // END if
		if ($this->input->post('startIdx')) {
			$this->uriVars['startIdx'] = $this->input->post('startIdx');
		} // END if
		if ($this->input->post('pageId')) {
			$this->uriVars['pageId'] = $this->input->post('pageId');
		} // END if
		if ($this->input->post('uid')) {
			$this->uriVars['uid'] = $this->input->post('uid');
		} // END if
		// EDIT 1.0.5 
		// NEW QUERY VARS
		if ($this->input->post('autoTime')) { // USED BY DRAFT TIME MODULE
			$this->uriVars['autoTime'] = $this->input->post('autoTime');
		}
		if ($this->input->post('auto_option')) { // USED FOR ADMIN/COMMISH AUTO PICK OPTION
			$this->uriVars['auto_option'] = $this->input->post('auto_option');
		} // END if
		if ($this->input->post('auto_pick_count')) { // USED FOR ADMIN/COMMISH AUTO PICK OPTION
			$this->uriVars['auto_pick_count'] = $this->input->post('auto_pick_count');
		} // END if
		if ($this->input->post('act_as_id')) { // USED FOR ADMIN/COMMISH AUTO PICK OPTION
			$this->uriVars['act_as_id'] = $this->input->post('act_as_id');
		} // END if
		if ($this->input->post('player_id_list')) { // USED FOR ADMIN/COMMISH AUTO PICK OPTION
			$this->uriVars['player_id_list'] = $this->input->post('player_id_list');
		} // END if
	}
	/**
	 *	MAKE EDITOR FORM.
	 *	Overrides the default makeForm function with custom form object code.
	 *
	 *	@access	protected
	 *	@return					{Void}
	 *	@since 	1.0
	 */
	protected function makeForm() {
		$this->enqueStyle('jquery.ui.css');
		$form = new Form();
		
		$form->html('<div id="draftForm">');
		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');
		
		$form->fieldset('Draft Admin');
		
		$checked = false;
		if ($this->input->post('draftEnable') && $this->input->post('draftEnable') == 1) {
			$checked = true;
		} else if ($this->dataModel->draftEnable == 1) {
			$checked = true;
		}
		$this->form_validation->set_message('valid_short_date', 'Please enter a date in mm/dd/yyyy format'); 
		//$form->label('Enable Draft','draftEnable');
		//$form->checkbox('draftEnable',1,'',$checked,'',array('class','first'));
		//$form->space();
		$form->text('whenDraft','Draft Date','required|valid_short_date',($this->input->post('whenDraft')) ? $this->input->post('whenDraft') : ($this->dataModel->draftDate != EMPTY_DATE_TIME_STR ? date('m/d/Y',strtotime($this->dataModel->draftDate)): ''), array('id'=>'dateField'));
		$form->span('Click this field to view the draft dates recommended by the site owner that take place BEFORE the fantasy season begins. To select a draft date outside this range, you may manually enter it in MM/DD/YYYY format.',array('class'=>'field_caption'));
		$form->space();        
		$form->br();
		$form->fieldset('',array('class'=>'dateLists'));
		$form->label('Start Time**','',array('class'=>'required','style'=>'width:225px;text-align:right;'));
		$draftDate = ($this->dataModel->draftDate != EMPTY_DATE_TIME_STR) ? $this->dataModel->draftDate : date('Y-m-d');
		
		$dTime = strtotime($draftDate);
		$timeSHour = date('h',$dTime);
		$timeSMins = date('i',$dTime);
		$timeStartA = date('A',$dTime);
		
		$form->select('startTimeH|startTimeH',getHours(),'&nbsp;',($this->input->post('startTimeH')) ? $this->input->post('startTimeH') : (string)$timeSHour);
		$form->nobr();
		$form->select('startTimeM|startTimeM',getMinutes(true),'&nbsp;',($this->input->post('startTimeM')) ? $this->input->post('startTimeM') : (string)$timeSMins);
		$form->nobr();
		$form->select('startTimeA|startTimeA',getAMPM(),'&nbsp;',($this->input->post('startTimeAM')) ? $this->input->post('startTimeAM') : $timeStartA);
		$form->space();
		$form->fieldset('');
		$form->text('nRounds','Number of Rounds','required|number',($this->input->post('nRounds')) ? $this->input->post('nRounds') : $this->dataModel->nRounds);
		$form->br();
		//$form->text('dispLimit','Number of Players to Display','required|number',($this->input->post('dispLimit')) ? $this->input->post('dispLimit') : $this->dataModel->dispLimit);
		//$form->space();
		$responses[] = array('1','Yes');
		$responses[] = array('-1','No');
		$form->fieldset('Auto Draft Settings');
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('pauseAuto',$responses,'Pause auto draft for manual picks',($this->input->post('pauseAuto') ? $this->input->post('pauseAuto') : $this->dataModel->pauseAuto));
        $form->span('This option suspends all auto draft options when making a single manual pick. Disabling this option allows auto draft to continue uninterupted once begun.',array('class'=>'field_caption'));
		$form->space();        
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('autoOpen',$responses,'Auto Draft for Teams without owners',($this->input->post('autoOpen') ? $this->input->post('autoOpen') : $this->dataModel->autoOpen));
		$form->span('If disabled, commissioner will need to manually initate picks for all non-owned teams..',array('class'=>'field_caption'));
		$form->space();
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('setAuto',$responses,'Force auto pick for all teams',($this->input->post('setAuto') ? $this->input->post('setAuto') : $this->dataModel->setAuto));
       	$form->span('<b style="color:#c00;">WARNING:</b> This will set ALL the leagues team\'s auto draft settings to <strong>true</strong> after the first team with auto pick enabled is encountered. Use this options with caution.',array('class'=>'field_caption'));
		$form->space();        
		
		$form->fieldset('Email Settings');
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('emailOwnersForPick',$responses,'Send owners pick alerts:',($this->input->post('emailOwnersForPick') ? $this->input->post('emailOwnersForPick') : $this->dataModel->emailOwnersForPick));
		$form->space();
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('emailDraftSummary',$responses,'Send round summary emails:',($this->input->post('emailDraftSummary') ? $this->input->post('emailDraftSummary') : $this->dataModel->emailDraftSummary));
		$form->space();
		$form->br();
		$form->fieldset('Draft Schedule Settings');
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('timerEnable',$responses,'Created Draft Schedule',($this->input->post('timerEnable') ? $this->input->post('timerEnable') : $this->dataModel->timerEnable));
		$form->space();
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('flexTimer',$responses,'Update Schedule After Each Pick',($this->input->post('flexTimer') ? $this->input->post('flexTimer') : $this->dataModel->flexTimer));
		$form->space();
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('enforceTimer',$responses,'Auto Pick for teams past time limit',($this->input->post('enforceTimer') ? $this->input->post('enforceTimer') : $this->dataModel->enforceTimer));
		$form->space();
		$form->fieldset();
		//$form->label('Current server time',"time");
		//$form->html('<span>'.date("Y-m-d H:i T",time()).'</span>');
		//$form->space();
		//$form->text('dStartDt','Start Date','trim',($this->input->post('dStartDt')) ? $this->input->post('dStartDt') : $this->dataModel->dStartDt);
		//$form->br();
		//$form->text('timePerPick','Time Per Pick (in seconds)','trim',($this->input->post('timePerPick')) ? $this->input->post('timePerPick') : $this->dataModel->timePerPick);
		//$form->br();
		$form->text('timePick1','Time Per Pick (in seconds)','trim',($this->input->post('timePick1')) ? $this->input->post('timePick1') : $this->dataModel->timePick1);
		$form->br();
		//$form->text('timePick1','Time Per Pick (min)','trim',($this->input->post('timePick1')) ? $this->input->post('timePick1') : $this->dataModel->timePick1);
		//$form->br();
		//$form->text('rndSwitch','- Through Round','trim',($this->input->post('rndSwitch')) ? $this->input->post('rndSwitch') : $this->dataModel->rndSwitch);
		//$form->br();
		//$form->text('timePick2','Time Per Pick After (min)','trim',($this->input->post('timePick2')) ? $this->input->post('timePick2') : $this->dataModel->timePick2);
		//$form->br();
		//$form->text('timeStart','Time Start','trim',($this->input->post('timeStart')) ? $this->input->post('timeStart') : $this->dataModel->timeStart);
		//$form->br();
		//$form->text('timeStop','Stop Timer At (Time)','trim',($this->input->post('timeStop')) ? $this->input->post('timeStop') : $this->dataModel->timeStop);
		//$form->br();
		//$form->br();
		/*$form->fieldset('',array('class'=>'dateLists'));
		$form->label('Stop Timer At (Time)','',array('class'=>'required','style'=>'width:225px;text-align:right;'));
		$timeStop = ($this->dataModel->timeStop != EMPTY_DATE_TIME_STR) ? $this->dataModel->timeStop : time('H:m:s A');
		$sTime = strtotime($timeStop);
		$timeStopHour = date('h',$sTime);
		$timeStopMins = date('i',$sTime);
		$timeStopA = date('A',$sTime);
		$form->select('stopTimeH|startTimeH',getHours(),'&nbsp;',($this->input->post('stopTimeH')) ? $this->input->post('stopTimeH') : (string)$timeStopHour);
		$form->nobr();
		$form->select('stopTimeM|stopTimeM',getMinutes(true),'&nbsp;',($this->input->post('stopTimeM')) ? $this->input->post('stopTimeM') : (string)$timeStopMins);
		$form->nobr();
		$form->select('stopTimeA|stopTimeA',getAMPM(),'&nbsp;',($this->input->post('stopTimeAM')) ? $this->input->post('stopTimeAM') : $timeStopA);
		$form->space();
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('pauseWkEnd',$responses,'Pause Timer on Weekends',($this->input->post('pauseWkEnd') ? $this->input->post('pauseWkEnd') : $this->dataModel->pauseWkEnd));
		$form->space();*/
		$form->fieldset('',array('class'=>'button_bar'));
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->button('Cancel','cancel','button',array('class'=>'button'));	
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$form->hidden('submitted',1);
		$form->hidden('league_id',(isset($this->uriVars['league_id']) ? $this->uriVars['league_id'] : $this->dataModel->league_id) );
		if ($this->recordId != -1) {
			$form->hidden('mode','edit');
			$form->hidden('id',$this->recordId);
		} else {
			$form->hidden('mode','add');
		}
		$form->html('</div>');
		$this->form = $form;
		$this->data['form'] = $form->get();
		$this->data['thisItem']['league_id'] = $this->dataModel->league_id;
		$draftPeriod = explode(":",$this->params['config']['draft_period']);
		$this->data['draftStart'] = $draftPeriod[0];
		$this->data['draftEnd'] = $draftPeriod[1];
		
		$this->makeNav();
	}
	/**
	 *	MAKE NAV.
	 *	Creates a sub nav menu for the given content category.
	 *
	 *	@access	protected
	 *	@return					{Void}
	 *	@since 	1.0
	 */
	protected function makeNav() {
		$admin = false;
		if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->league_model->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)){
			$admin = true;
		}
		$league_id = -1;
		$session_league_id = $this->session->userdata('league_id');
		if ($this->dataModel->league_id != -1) {
			$league_id = $this->dataModel->league_id;
		} else if ($this->league_model->id != -1) {
			$league_id = $this->league_model->id;
		} else if (isset($this->uriVars['league_id']) && $this->uriVars['league_id'] != -1) {
			$league_id = $this->uriVars['league_id'];
		} else if (isset($this->uriVars['id']) && !isset($this->uriVars['league_id']) && $this->uriVars['id'] != -1) {
			$league_id = $this->uriVars['id'];	
		} else if (isset($session_league_id) && !empty($session_league_id)) {
			$league_id = $session_league_id;
		}
		if ($league_id != -1 && $this->league_model->id == -1) {
			$this->league_model->load($league_id);
		}
		array_push($this->params['subNavSection'],league_nav($league_id, $this->league_model->league_name,$admin,true, $this->league_model->getScoringType()));
		array_push($this->params['subNavSection'],draft_nav($league_id));
	}
	/**
	 *	SHOW INFO.
	 *	Creates and passes view data to the view object. Calls the default baseEditor showInfo() to finish the
	 *	display output.
	 *
	 *	@access	protected
	 *	@return					{Void}
	 *	@since 	1.0
	 */
	protected function showInfo() {
		// Setup header Data
		$this->enqueStyle('list_picker.css');
		if ((isset($this->uriVars['id']) && !empty($this->uriVars['id'])) && !isset($this->uriVars['league_id'])) {
			$this->uriVars['league_id'] =  $this->uriVars['id'];
		}
		$teams = array();
		$isCommish = false;
		$isAdmin = false;
		//echo("draft id = ".$this->dataModel->id."<br />");
		if ($this->dataModel->id == -1 && isset($this->uriVars['draft_id']) && !empty($this->uriVars['draft_id'])) {
			$this->dataModel->load($this->uriVars['draft_id']);
		}
		if ($this->dataModel->id != -1) {			
			$this->league_model->load($this->dataModel->league_id);
			$teams = $this->league_model->getTeamDetails();
			$isCommish = $this->league_model->commissioner_id == $this->params['currUser'];
			$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
		}
		$this->data['thisItem']['isAdmin'] = $isAdmin;
		$this->data['thisItem']['isCommish'] = $isCommish;
		$this->data['thisItem']['draftDate'] = $this->dataModel->draftDate;
		$this->data['thisItem']['draftStatus'] = $this->dataModel->getDraftStatus();
		$this->data['thisItem']['timerEnable'] = ($this->dataModel->getDraftStatus() < 4 && $this->dataModel->timerEnable == 1) ? 1 : 0;
		$this->data['thisItem']['league_id'] = $this->dataModel->league_id;
		$this->data['thisItem']['draft_id'] = $this->dataModel->id;
		$this->data['thisItem']['teamList'] = $teams;
		$draftedPlayers = $this->dataModel->getTakenPicks();
		//echo($this->db->last_query()."<br />");
		if (!isset($this->player_model)) {
			$this->load->model('player_model');
		}
		
		$this->data['thisItem']['playersInfo'] = $this->player_model->getPlayersDetails($draftedPlayers);
		$this->data['thisItem']['draftResults'] = $this->dataModel->getDraftResults();
		
		// EDITS 1.0.5
		// IF ADMIN OR COMMISH, LOAD AVAILABLE PLAYERS FOR MANUAL/EDIT PICK DIALOG
		if ($isAdmin || $isCommish) {
			$this->data['playerList'] = $this->dataModel->getPlayerPool(false,$this->params['config']['ootp_league_id'], NULL, NULL,  NULL, NULL, 0, -1, 0, $this->dataModel->league_id,$this->ootp_league_model->current_date,NULL,'all',-1,true);
		}
		if (isset($this->uriVars['autoTime']) && $this->uriVars['autoTime'] != -1) {
			$this->data['thisItem']['autoTime'] = $this->uriVars['autoTime'];
		}
		// END 1.0.5 EDITS
		
		//echo($this->db->last_query()."<Br />");
		$this->params['subTitle'] = "League Draft";
		$this->makeNav();
		parent::showInfo();
	}
	/*-----------------------------------
	/	DEPRECATED FUNCTIONS
	/	SALTED FOR REMOVAL, BUT STILL 
	/	INCLUDED FOR REFERENCE, TESTING
	/------------------------------------*/
	/**
	 * @deprecated
	 */
	public function addPlayer() {
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code =200;
		$result = "";
		$user_id = false;
		if (isset($this->uriVars['act_as_id']) && !empty($this->uriVars['act_as_id'])) {
			$user_id = $this->resolveTeamOwner($this->uriVars['act_as_id']);
		}
		if ($this->dataModel->id != -1) {
			if (!$this->dataModel->addUserPick($this->uriVars['player_id'],$this->params['currUser'],$this->dataModel->league_id)) {
				$status .= "error:Your pick was not saved.";
				if (!empty($this->dataModel->statusMess)) {
					$status .=  " ".$this->dataModel->statusMess;
				}
			} else {
				$status .= "Player Added Successfully.";
			}
			$result = $this->loadPickList($this->dataModel->league_id, $user_id, true);
		} else {
			$status .= "error:League Identifier Missing";
		}
		$result = '{ "result": { "items": ['.$result.']},"code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 * @deprecated
	 */
	public function movePlayer() {
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code =200;
		$result = "";
		$user_id = false;
		if (isset($this->uriVars['act_as_id']) && !empty($this->uriVars['act_as_id'])) {
			$user_id = $this->resolveTeamOwner($this->uriVars['act_as_id']);
		}
		if ($this->dataModel->id != -1) {
			if (!$this->dataModel->movePick($this->uriVars['direction'],$this->uriVars['player_id'],$this->params['currUser'],$this->dataModel->league_id)) {
				$status .= "error:Your pick was not saved.";
				if (!empty($this->dataModel->statusMess)) {
					$status .=  " ".$this->dataModel->statusMess;
				}
			} else {
				$status .= "Player Moved Successfully.";
			}
			$result = $this->loadPickList($this->dataModel->league_id, $user_id, true);
		} else {
			$status .= "error:League Identifier Missing";
		}
		$result = '{ "result": { "items": ['.$result.']},"code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 * @deprecated
	 */
	public function removePlayer() {
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code =200;
		$result = "";
		$user_id = false;
		if (isset($this->uriVars['act_as_id']) && !empty($this->uriVars['act_as_id'])) {
			$user_id = $this->resolveTeamOwner($this->uriVars['act_as_id']);
		}
		if ($this->dataModel->id != -1) {
			if (!$this->dataModel->removePick($this->uriVars['player_id'],$this->params['currUser'],$this->dataModel->league_id)) {
				$status .= "error:Your pick was not saved.";
				if (!empty($this->dataModel->statusMess)) {
					$status .=  " ".$this->dataModel->statusMess;
				}
			} else {
				$status .= "Player Removed Successfully.";
			}
			$result = $this->loadPickList($this->dataModel->league_id, $user_id, true);
		} else {
			$status .= "error:League Identifier Missing";
		}
		$result = '{ "result": { "items": ['.$result.']},"code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 * @deprecated
	 */
	public function clearDraftList() {
		$this->getURIData();
		$this->loadModel();
		$status = "";
		$code =200;
		$result = "";
		$user_id = false;
		if (isset($this->uriVars['act_as_id']) && !empty($this->uriVars['act_as_id'])) {
			$user_id = $this->resolveTeamOwner($this->uriVars['act_as_id']);
		}
		if ($this->dataModel->id != -1) {
			if (!$this->dataModel->clearDraftList($this->params['currUser'])) {
				$status .= "error:Your pick was not saved.";
				if (!empty($this->dataModel->statusMess)) {
					$status .=  " ".$this->dataModel->statusMess;
				}
			} else {
				$status .= "Draft list Successfully Cleared.";
			}
			$result = $this->loadPickList($this->dataModel->league_id, $user_id, true);
		} else {
			$status .= "error:League Identifier Missing";
		}
		$result = '{ "result": { "items": ['.$result.']},"code":"'.$code.'","status": "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
}
/* End of file draft.php */
/* Location: ./application/controllers/draft.php */