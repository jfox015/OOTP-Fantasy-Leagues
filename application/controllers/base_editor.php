<?php
/**
 *	BASE EDITOR.
 *	Base class for all controllers that will load, display and 
 *	manipulate model based data.
 *	@author			Jeff Fox
 *	@dateCreated	06/24/09
 *  @dataModified	6/18/10
 *
 */
class BaseEditor extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'BaseEditor';
	/**
	 *	RECORD ID.
	 *	Temp Record ID.
	 *	@var $recordId:int
	 */
	var $recordId = -1;
	/**
	 *	RECORD DATA.
	 *	Temp Record Data Array.
	 *	@var $recordData:Array
	 */
	var $recordData = array();
	/**
	 *	ACTION.
	 *	The action type
	 *	@var $action:Text
	 */
	var $action = '';
	/**
	 *	MODE.
	 *	The mode of the page (add,edit, delete, info)
	 *	@var $action:Text
	 */
	var $mode = 'new';
	/**
	 *	REDIRECT SUCCESS.
	 *	URL to redirect to upon successful submission
	 *	@var $redirect_success:Text
	 */
	var $redirect_success = '';
	/**
	 *	RESRICTED FUNCTIONS.
	 *	Restrict functions to a particular functional group
	 *	@var $restrictedFunctions:Int
	 */
	var $restrictedFunctions = RESTRICT_NONE;
	/**
	 *	REDIRECT FAIL.
	 *	URL to redirect to upon failed submission
	 *	@var $redirect_fail:Text
	 */
	var $redirect_fail = '';
	/**
	 *	OUTPUT MESSAGE.
	 *	Message to output to view
	 *	@var $outMess:Text
	 */
	var $outMess = '';
	/**
	 *	MESSAGE TEXT.
	 *	Additional messaging variable
	 *	@var $messageType:Text
	 */
	var $messageType = '';
	/**
	 *	DEBUG.
	 *	Flasg to enable ot disable debugging in the class
	 *	@var $debug:Boolean
	 */
	var $debug = false;
	
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of BaseEditor.
	 */
	public function BaseEditor() {
		parent::MY_Controller();
		$this->load->model($this->modelName,'dataModel');
	}
	/*--------------------------------
	/	PUBLIC FUNCTIONS
	/-------------------------------*/
	/**
	 *	INIT.
	 *	Initalizing function of the class.
	 */
	function init() {
		parent::init();
		$this->params['customValidate'] = false;
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	public function index() {
		$this->submit();
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	/**
	 *	SUBMIT.
	 *	The handler for all thre standard "Built-in" functionsof the class 
	 *	including add. edit, delete and view operations.
	 *
	 */
	public function submit() {
		if ($this->params['loggedIn']) {
		    $proceed = true;
		    if ($this->restrictAccess && ($this->restrictedFunctions == RESCTRICT_ALL || $this->restrictedFunctions == RESTRICT_EDIT) && $this->params['accessLevel'] < $this->minAccessLevel) {
			    $this->data['heading'] = $this->lang->line('access_heading_not_authorized');
			    $this->data['message'] = '<span class="error">'.$this->lang->line('access_not_authorized').'</span>';	
			    $this->load->view('../errors/error_403',$this->data,false);
			    $proceed = false;
		    }
			if ($proceed) {
				$this->getURIData();
				$this->loadData(); 
				
				// HANDLE DELETE OPERATIONS
				if ($this->mode == 'delete') {
					
					if ($this->input->post('confirm')) {
						$this->beforeDelete();
						if ($this->dataModel->delete()) {
							$this->outMess = $this->lang->line('form_complete_success_delete');
							$this->messageType = "success";
							$this->load->model($this->modelName,'dataModel');
							$this->recordId = -1;
							$this->complete();
						} else {
							$this->outMess = str_replace('[ERROR_MESSAGE]',$this->dataModel->statusMess,$this->lang->line('form_complete_fail'));
							$this->messageType = MESSAGE_FAIL;
							if ($this->dataModel->id != -1) {
								$this->mode = 'edit';
								$this->makeForm();
								$this->showForm();
							} else {
								$this->complete();
							}
						}
					} else {
						$this->showConfirm();
					}
				} else {
					
					if ($this->mode == "add") {
						$this->beforeAdd();
					} else if ($this->mode == "edit") {
						$this->beforeEdit();
					}
					$this->makeForm();
					if ($this->input->post('submitted') || ($this->input->post('showPreview') && $this->input->post('showPreview') != -1)) {
						if ($this->processForm()) {
							if ($this->input->post('showPreview') && $this->input->post('showPreview') != -1) {
								$this->data['previewContent'] = $this->preview();
								$this->outMess = $this->lang->line('form_preview');
								$this->messageType = MESSAGE_NOTICE;
								$this->showPreview();
							} else {
								$this->outMess = $this->lang->line('form_complete_success');
								$this->messageType = MESSAGE_SUCCESS;
								$this->complete();
							}
						} else {
							//echo("Form Fail"."<br />");
							$_POST['showPreview'] = -1;
							$this->outMess = str_replace('[ERROR_MESSAGE]',$this->dataModel->statusMess,$this->lang->line('form_complete_fail'));
							$this->form->errors .= $this->form->error_string_open.$this->outMess.$this->form->error_string_close;
							$fv = & _get_validation_object();
							$fv->setError('name',$this->outMess);
							$this->showForm();
						}
					} else {
						if ($this->mode == 'edit') {
							if ($this->dataModel->id == -1) {
								$errStr = '';
								if ($this->dataModel->statusMess != '') {
									$errStr = $this->dataModel->statusMess;
								} else {
									$errStr = "A required id parameter was missing.";
								}
								$this->outMess = str_replace('[ERROR_MESSAGE]',$errStr,$this->lang->line('form_complete_fail'));
								$this->messageType = MESSAGE_FAIL;
								$this->showError();
							} else {
								$this->showForm();
							}
						} else {
							$this->showForm();
						}
					}
				}
		    } else {
				$this->outMess = '<span class="error">YOu do not have sufficient privlidges to access this page.</span>';	
				$this->showError();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	EDIT.
	 *	General Handler for record editing operations.
	 *
	 *	Example: http://urlofsite.com/controller/edit/[record id]
	 *
	 */
	public function edit() {
		$this->mode = 'edit';
		$this->submit();
	}
	/**
	 *	DELETE.
	 *	General Handler for record deletion operations.
	 *
	 *	Example: http://urlofsite.com/controller/delete/[record id]
	 *
	 */
	public function delete() {
		$this->mode = 'delete';
		$this->submit();
	}
	/**
	 *	INFO.
	 *	General Handler for record viewing operations.
	 *
	 *	Example: http://urlofsite.com/controller/info/[record id]
	 *
	 */
	public function info() {
	    $proceed = true;
	    if ($this->restrictAccess && ($this->restrictedFunctions == RESCTRICT_ALL || $this->restrictedFunctions == RESTRICT_INFO) && $this->params['accessLevel'] < $this->minAccessLevel) {
		    $this->data['heading'] = $this->lang->line('access_heading_not_authorized');
		    $this->data['message'] = '<span class="error">'.$this->lang->line('access_not_authorized').'</span>';	
		    $this->load->view('../errors/error_403',$this->data,false);
		    $proceed = false;
	    }
		if ($proceed) {
			$this->getURIData();
			if ($this->loadData()) {
				if ($this->dataModel->id != -1) {
					$this->showInfo();
				} else {
					$this->outMess = $this->dataModel->statusMess;
					$this->messageType = MESSAGE_FAIL;
					$this->showError();
				}
			} else {
				$this->outMess = $this->dataModel->statusMess;
				$this->messageType = MESSAGE_FAIL;
				$this->showError();
			}
		}
	}
	/*----------------------------------------------
	/	PROTECTED FORM/DATA PROCESSING FUNCTIONS
	/---------------------------------------------*/
	protected function customValidation() { return true; }
	
	protected function beforeAdd() { return true; }
	protected function beforeDelete() { return true; }
	protected function beforeEdit() { return true; }
	
	protected function afterAdd() { return true; }
	protected function afterDelete() { return true; }
	protected function afterEdit() { return true; }
	/**
	 * LOAD DATA.
	 * If an ID parameter is found, this method loads the corresponding 
	 * data model and returns it's status.
	 * 
	 * @return		Boolean	TRUE on success, FALSE on error
	 */
	protected function loadData() {
		if ($this->recordId == -1) {
			if (count($this->uriVars) > 0 && (isset($this->uriVars['id']) && !empty($this->uriVars['id']))) {
				$this->recordId = $this->uriVars['id'];
			} else if ($this->input->post('id')) {
				$this->recordId = $this->input->post('id');
			}
		}
		if ($this->recordId == -1) {
			return true;
		} else {
			return $this->dataModel->load($this->recordId);
		} 
	}
	/**
	 * MAKE FORM.
	 * Generic placeholder for form creation method. All child classes 
	 * should override this method using the /application/libraries/Form 
	 * library to generate the form code.
	 * 
	 * @return	void
	 */
	protected function makeForm() {
		$this->data['form'] =  '<form></form>';
	}
	/**
	 * PROCESS FORM.
	 * Handles form validation, application of form data to the model and 
	 * saving the record. If errors are encountered they are noted and 
	 * available via global class vars.
	 * 
	 * @return	Boolean		TRUE on succes, FALSE on ERROR
	 */
	protected function processForm() {
		if ($this->debug) {
			echo("PROCESS FORM<br/>");
			echo("this->form = ".(($this->form === true) ? 'true' : 'false')."<br/>");
			echo("this->input->post('validForm') = ".($this->input->post('validForm') ? 'true' : 'false')."<br/>");
		}
		$success = false;
		$valid = false;
		if ($this->input->post('validForm')) {
			$valid = true;
		} else if ($this->form) {
			$this->form->validate();
			if ($this->form->valid) {
				$valid = true;
				if (isset($this->params['customValidate'])) {
					$valid = $this->customValidation();
				}
			}
		}
		if ($valid) {
			if (!$this->input->post('showPreview') || $this->input->post('showPreview') == -1) {
				$this->dataModel->applyData($this->input,$this->params['currUser']);
				if ($this->dataModel->save()) {
					$this->outMess = "Your submission was completed successfully.";
					$this->messageType = 'success';
					$success = true;
				}
			} else {
				$success = true;
			}
		}
		
		$this->outMess = " No Errors were encountered.";
		if (!$success) {
			if (!empty($this->form->errors)) {
				$this->outMess = "Errors were encountered processing your request.";
				$this->outMess .= $this->form->errors;
			} else if ($this->dataModel->errorCode != -1) {
				$this->outMess .= $this->dataModel->statusMess;
			}
			$this->messageType = 'fail';
		}
		if ($this->debug) {
			echo("success = ".(($success === true) ? 'true' : 'false')."<br/>");
			if ($success) {$this->data['dump'] = $this->dataModel->dumpData(); }
			echo("Errors = ".$this->outMess."<br />");
		}
		return $success;
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if (isset($this->uriVars['mode']) && !empty($this->uriVars['mode'])) {
			$this->mode = $this->uriVars['mode'];
		} else if ($this->input->post('mode')) {
			$this->mode = $this->input->post('mode');
		}
		//echo("urivars id = ".$this->uriVars['id']."<br />");
	}
	/*----------------------------------------------------
	/	OUTPUT FUNCTIONS
	/----------------------------------------------------*/
	/**
	 *	COMPLETE.
	 *	Executed once form processing operations have completed. All 
	 *	"after" operations like <b>afterAdd></b> are executed by this 
	 *	function.
	 *
	 */
	protected function complete() {
		
		// AFTER [mode] function calls
		if ($this->mode == "add") {
			$this->afterAdd();
		} else if ($this->mode == "edit") {
			$this->afterEdit();
		} else if ($this->mode == "delete") {
			$this->afterDelete();
		}
		
		// Setup header Data
		$this->data['thisItem']['id'] = $this->recordId;
		$this->data['thisItem']['ownerId'] = (isset($this->dataModel->recordOwnerId) ? $this->dataModel->recordOwnerId : -1);
		$this->data['subTitle'] = "Submission Complete";
		$this->data['theContent'] = $this->outMess;
		$this->data['currUser'] = $this->params['currUser'];
		$this->params['content'] = $this->load->view($this->views['SUCCESS'],$this->data,true);
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 * 	SHOW FORM.
	 * 	This method checked the mode of the class and displays the view.
	 * 
	 * 	@return	void
	 */
	protected function showForm() {
		// Setup header Data
		$titleStr = '';
		switch ($this->mode) {
			case 'delete':
				$titleStr = 'Delete';
				break;
			case 'edit':
				$titleStr = 'Edit';
				break;
			case 'add':
			default:
				$titleStr = 'Add';
				break;
		}
		if (!isset($this->data['form']) || empty($this->data['form'])) {
			$this->data['form'] = $this->form->get();
		}
		$this->data['subTitle'] = $titleStr." ".$this->_NAME;
		$this->data['thisItem']['id'] = $this->recordId;
		$this->data['thisItem']['ownerId'] = (isset($this->dataModel->recordOwnerId) ? $this->dataModel->recordOwnerId : -1);
		$this->data['currUser'] = $this->params['currUser'];
		$this->params['content'] = $this->load->view($this->views['EDIT'],$this->data,true);
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 * 	SHOW PREVIEW.
	 * 	This method displays the preview page.
	 * 
	 * 	@return	void
	 *  @since 1.0.3 PROD
	 */
	protected function showPreview() {
		// Setup header Data
		$this->data['thisItem']['id'] = $this->recordId;
		$this->data['thisItem']['ownerId'] = (isset($this->dataModel->recordOwnerId)) ? $this->dataModel->recordOwnerId : -1;
		$this->data['subTitle'] = "News Preview";
		$this->data['previewMessage'] = $this->outMess;
		$this->data['previewMessageType'] = $this->messageType;
		$this->data['currUser'] = $this->params['currUser'];
		$this->params['content'] = $this->load->view($this->views['PREVIEW'],$this->data,true);
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 * 	SHOW ERROR.
	 * 	This method displays the error handling view.
	 * 
	 * 	@return	void
	 */
	protected function showError() {
		// Setup header Data
		$this->data['thisItem']['id'] = $this->recordId;
		$this->data['thisItem']['ownerId'] = (isset($this->dataModel->recordOwnerId)) ? $this->dataModel->recordOwnerId : -1;
		$this->data['subTitle'] = "An error occured";
		$this->data['theContent'] = $this->outMess;
		$this->data['currUser'] = $this->params['currUser'];
		$this->params['content'] = $this->load->view($this->views['FAIL'],$this->data,true);
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 * 	SHOW CONFIRM.
	 * 	This method displays a confirmation view.
	 * 
	 * 	@return	void
	 */
	protected function showConfirm() {
		// Setup header Data
		$this->data['thisItem']['id'] = $this->recordId;
		$this->data['thisItem']['ownerId'] = (isset($this->dataModel->recordOwnerId)) ? $this->dataModel->recordOwnerId : -1;
		$this->data['subTitle'] = "Confirm Deletion";
		$this->data['theContent'] = str_replace('[ITEM_TYPE]',$this->_NAME,$this->lang->line('form_confirm_delete'));
		$this->data['theContent'] = str_replace('[RECORD_ID]',$this->recordId,$this->data['theContent']);
		$this->data['currUser'] = $this->params['currUser'];
		$this->params['content'] = $this->load->view($this->views['FAIL'],$this->data,true);
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 * 	SHOW INFO.
	 * 	This method displays the data display view.
	 * 
	 * 	@return	void
	 */
	protected function showInfo($template = false) {
		// Setup header Data
		$this->data['thisItem']['id'] = $this->recordId;
		$this->data['thisItem']['ownerId'] = (isset($this->dataModel->recordOwnerId)) ? $this->dataModel->recordOwnerId : -1;
		$this->data['currUser'] = $this->params['currUser'];
		$this->params['content'] = $this->load->view($this->views['VIEW'],$this->data,true);
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
}
/* End of file BaseEditor.php */
/* Location: ./application/controllers/BaseEditor.php */ 