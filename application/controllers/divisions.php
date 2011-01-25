<?php
require_once('base_editor.php');
/**
 *	Divisions.
 *	The primary controller for Divisions manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	5/7/10
 *	@lastModified	5/7/10
 *
 */
class divisions extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'divisions';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of divisions.
	 */
	public function divisions() {
		parent::BaseEditor();
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	public function index() { 
		redirect('divisions/showList');
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	function init() {
		parent::init();
		$this->modelName = 'division_model';
		
		$this->views['ADD'] = 'division/division_add';
		$this->views['EDIT'] = 'division/division_editor';
		$this->views['VIEW'] = 'division/division_info';
		$this->views['FAIL'] = 'division/division_message';
		$this->views['SUCCESS'] = 'division/division_message';
		$this->views['LIST'] = 'division/search_results_division.php';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_WRITE;
		
		$this->debug = false;
		$this->params['customValidate'] = true;
	}
	/*--------------------------------
	/	PRIVATE FUNCTIONS
	/-------------------------------*/
	protected function customValidation() {
		$league_id = $this->uriVars['league_id'];
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
			$this->league_model->load($league_id);
		}
		$max_teams = $this->league_model->max_teams;
		$count = $this->dataModel->getDivisionCount($league_id);
		if ($this->mode == "new" && $count > ($max_teams / 2)) {
			$errMsg = "You can only have half the number of divisions as the number of teams in your league. You league is set for ".$max_teams." teams so you can only have ".($max_teams/2)." divisions.";
			$this->form->errors .= $this->form->error_string_open.$errMsg.$this->form->error_string_close;
			$fv = & _get_validation_object();
			$fv->setError('division_name',$errMsg);
			return false;
		}
		return true;
	}
	public function showList() {
		if ($this->params['loggedIn']) {
			$this->init();
			$this->getURIData();
			$this->loadData();
			if (!isset($this->league_model)) {
				$this->load->model('league_model');
			}
			$this->league_model->load($this->uriVars['league_id']);
			$this->data['league_name'] = $this->league_model->league_name;
			$this->data['league_id'] = $this->uriVars['league_id'];
			$this->data['divisions'] = $this->dataModel->getDivisionList($this->uriVars['league_id']);
			$this->data['subTitle'] = 'Divisions for '.$this->league_model->league_name;
			$this->makeNav();
			$this->params['content'] = $this->load->view($this->views['LIST'], $this->data, true);
			$this->params['pageType'] = PAGE_SEARCH;
			$this->params['subTitle'] = 'League Divisions';
			
			$this->displayView();	
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function submit() {
		parent::submit();
	}
	public function addDivision() {    
		if ($this->params['loggedIn']) {
			$this->init();
			$this->getURIData();
			$this->loadData();
			$this->form_validation->set_rules('division_name', 'Division Name', 'required|trim');
			$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
			
			if ($this->form_validation->run() == false) {
				$this->params['pageType'] = PAGE_FORM;
				$this->params['subTitle'] = $this->data['subTitle'] = 'Add Division';
				$this->data['league_id'] = $this->uriVars['league_id'];
				$this->data['input'] = $this->input;
				$this->data['config'] = $this->params['config'];
				$this->params['content'] = $this->load->view($this->views['ADD'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			} else {
				$valid = true;
				$added = false;
				if (isset($this->params['customValidate'])) {
					$valid = $this->customValidation();
				}
				if ($valid) {
					$session_auth = $this->session->userdata($this->config->item('session_auth'));
					$this->dataModel->applyData($this->input,$session_auth);
					$added = $this->dataModel->save();
					if ($added) {
						$this->session->set_flashdata('message', '<p class="success">The Division was added successfully.</p>');
						redirect('divisions/showList/league_id/'.$this->uriVars['league_id']);
					}
				}
				if (!$valid || !$added) {
					$message = '<p class="error">Division Addition Failed.';
					if ($this->auth->get_status_code() != 0) {
						$message .= ' '.$this->auth->get_status_message().'</p>';
					}
					$message .= '</p >';
					$this->session->set_flashdata('message', $message);
					redirect('divisions/addDivision/league_id'.$this->uriVars['league_id']);
				}
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	
	protected function makeForm() {
		$form = new Form();
		
		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');
		
		$form->fieldset('Details');
		
		$form->text('division_name','Name','required|trim',($this->input->post('division_name')) ? $this->input->post('division_name') : $this->dataModel->division_name);
		$form->br();

		$form->fieldset('',array('class'=>'button_bar'));
		if ($this->recordId != -1) {
			$form->button('Delete','delete','button',array('class'=>'button'));	
			$form->nobr();
		}
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->button('Cancel','cancel','button',array('class'=>'button'));
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$form->hidden('submitted',1);
		$form->hidden('league_id',$this->dataModel->league_id);
		if ($this->recordId != -1) {
			$form->hidden('mode','edit');
			$form->hidden('id',$this->recordId);
		} else {
			$form->hidden('mode','add');
		}
		$this->form = $form;
		$this->data['form'] = $form->get();
		$this->makeNav();
	}
	
	
	protected function showInfo() {
		// Setup header Data		
		$this->data['thisItem']['division_name'] = $this->dataModel->division_name;
		$this->data['thisItem']['league_id'] = $this->dataModel->league_id;
		
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
		}
		$this->league_model->load($this->dataModel->league_id);
		$this->data['thisItem']['league_name'] = $this->league_model->league_name;
		$this->params['subTitle'] = "Division Information";
		$this->data['subTitle'] = $this->dataModel->division_name;
		
		$this->makeNav();
		parent::showInfo();
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('league_id')) {
			$this->uriVars['league_id'] = $this->input->post('league_id');
		} // END if
	}
	protected function makeNav() {
		$admin = false;
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
			$this->league_model->load($this->uriVars['league_id']);
		}
		if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->league_model->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)){
			$admin = true;
		}
		array_push($this->params['subNavSection'],league_nav($this->league_model->id, $this->league_model->league_name,$admin));
	}
}
/* End of file divisions.php */
/* Location: ./application/controllers/divisions.php */ 