<?php
require_once('base_editor.php');
/**
 *	Project.
 *	The primary controller for Project manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	1/3/11
 *	@lastModified	1/3/11
 *
 */
class project extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'project';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of project.
	 */
	public function project() {
		parent::BaseEditor();
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	function init() {
		parent::init();
		$this->modelName = 'project_model';
		
		$this->views['EDIT'] = 'project/project_editor';
		$this->views['VIEW'] = 'project/project_info';
		$this->views['FAIL'] = 'project/project_message';
		$this->views['SUCCESS'] = 'project/project_message';
		$this->views['ATTACHMENT_UPLOAD'] = 'project/project_attachment';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_DEVELOP;
		$this->debug = false;
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
    public function attachment() {
		if ($this->params['loggedIn']) {
			if ($this->params['accessLevel'] < ACCESS_DEVELOP) {
			    $this->data['heading'] = $this->lang->line('access_heading_not_authorized');
			    $this->data['message'] = '<span class="error">'.$this->lang->line('access_not_authorized').'</span>';	
			    $this->load->view('errors/error_403',$this->data,false);
			} else {
    		    $this->getURIData();
    			$this->loadData();
    			$this->data['attachment'] = $this->dataModel->attachment;
    			$this->data['id'] = $this->dataModel->id;
    			$this->data['subTitle'] = 'Attachment';
    			if (!($this->input->post('submitted')) || ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name']))) {
    				if ($this->input->post('submitted') && !isset($_FILES['attachmentFile']['name'])) {
    					$fv = & _get_validation_object();
    					$fv->setError('attachmentFile','The Attatchement File field is required.');
    				}
    				$this->params['content'] = $this->load->view($this->views['ATTACHMENT_UPLOAD'], $this->data, true);
    				$this->makeNav();
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
    			} else {
    				$change = $this->dataModel->applyData($this->input, $this->params['currUser']); 
    				if ($change) {
    					$this->dataModel->save();
    					$this->session->set_flashdata('message', '<p class="success">The attachement has been successfully uploaded.</p>');
    					redirect('project/info/'.$this->dataModel->id);
    				} else {
    					$message = '<p class="error">Attachement Change Failed.';
    					if ($this->auth->get_status_code() != 0) {
    						$message .= ' '.$this->auth->get_status_message().'</p>';
    					}
    					$message .= '</p >';
    					$this->session->set_flashdata('message', $message);
    					redirect('project/attachement');
    				}
    			}
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('home/login');
	    }
	}
	public function deleteAttachment() {
		if ($this->params['loggedIn']) {
		    if ($this->params['accessLevel'] < ACCESS_DEVELOP) {
			    $this->data['heading'] = $this->lang->line('access_heading_not_authorized');
			    $this->data['message'] = '<span class="error">'.$this->lang->line('access_not_authorized').'</span>';	
			    $this->load->view('errors/error_403',$this->data,false);
			} else {
		        $this->getURIData();
    			if ($this->loadData()) {
    				if ($this->dataModel->deleteAttachment()) {
    					$this->outMess = "The attachment was sucessfully deleted.";
    					$this->messageType = "success";
    				} else {
    					$this->outMess = "The operation failed. Error:".$this->dataModel->statusMess;
    					$this->messageType = "fail";
    				}
    				$this->mode = 'edit';
    				$this->makeForm();
    				$this->makeNav();
					$this->showForm();
    			} else {
    				$this->outMess = "The operation failed. Error:".$this->dataModel->statusMess;
    				$this->messageType = "fail";
    				$this->makeNav();
					$this->showError();
    			}
		    }
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('home/login');
	    }
	}
	/*--------------------------------
	/	PRIVATE FUNCTIONS
	/-------------------------------*/
	protected function makeForm() {
		$form = new Form();
		
		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');
		
		$form->fieldset('Project Details');
		
		$form->text('name','Project Name','required|trim',($this->input->post('name')) ? $this->input->post('name') : $this->dataModel->name,array("class"=>"longtext"));
		$form->br();
		$form->text('jobCode','Job Code','required|trim',($this->input->post('jobCode')) ? $this->input->post('jobCode') : $this->dataModel->jobCode);
		$form->br();
		$form->text('summary','Summary','required|trim',($this->input->post('summary')) ? $this->input->post('summary') : $this->dataModel->summary,array("class"=>"longtext"));
		$form->br();
		$form->label('Description');
		$form->html('<div class="richEditor">');
		$form->html('<div id="myNicPanel" class="nicEdit-panel"></div>');
		$form->textarea('description','','trim',($this->input->post('description')) ? $this->input->post('description') : $this->dataModel->description,array("cols"=>50,"rows"=>16));
		$form->html('</div>');
		$form->space();
		$startArr = ($this->dataModel->startDate != EMPTY_DATE_TIME_STR) ? explode("-",date('Y-m-d',strtotime($this->dataModel->startDate))) : array(-1,-1,-1);
		$form->fieldset('',array('class'=>'dateLists'));
		$form->label('Start Date', '', array('class'=>'required'));
		$form->select('startMonth|startMonth',getMonths(),'Month',($this->input->post('startMonth') ? $this->input->post('startMonth') : $startArr[1]),'integer');
		$form->nobr();
		$form->select('startDay|startDay',getDays(),'Day',($this->input->post('startDay') ? $this->input->post('startDay') : $startArr[2]),'integer');
		$form->nobr();	
		$form->select('startYear|startYear',getYears(),'Year',($this->input->post('startYear') ? $this->input->post('startYear') :$startArr[0]),'integer');
		$form->space();
		
		$dueArr = ($this->dataModel->dueDate != EMPTY_DATE_TIME_STR) ? explode("-",date('Y-m-d',strtotime($this->dataModel->dueDate))) : array(-1,-1,-1);
		$form->fieldset('',array('class'=>'dateLists'));
		$form->label('Due Date');
		$form->select('dueMonth|dueMonth',getMonths(),'Month',($this->input->post('dueMonth') ? $this->input->post('dueMonth') : $dueArr[1]),'integer');
		$form->nobr();
		$form->select('dueDay|dueDay',getDays(),'Day',($this->input->post('dueDay') ? $this->input->post('dueDay') : $dueArr[2]),'integer');
		$form->nobr();	
		$form->select('dueYear|dueYear',getYears(),'Year',($this->input->post('dueYear') ? $this->input->post('dueYear') :$dueArr[0]),'integer');
		$form->space();
		
		$closeArr = ($this->dataModel->closeDate != EMPTY_DATE_TIME_STR) ? explode("-",date('Y-m-d',strtotime($this->dataModel->closeDate))) : array(-1,-1,-1);
		$form->fieldset('',array('class'=>'dateLists'));
		$form->label('Close Date');
		$form->select('closeMonth|closeMonth',getMonths(),'Month',($this->input->post('closeMonth') ? $this->input->post('closeMonth') : $closeArr[1]),'integer');
		$form->nobr();
		$form->select('closeDay|closeDay',getDays(),'Day',($this->input->post('closeDay') ? $this->input->post('closeDay') : $closeArr[2]),'integer');
		$form->nobr();	
		$form->select('closeYear|closeYear',getYears(),'Year',($this->input->post('closeYear') ? $this->input->post('closeYear') :$closeArr[0]),'integer');
		$form->fieldset('',array('class'=>'radioGroup'));
		$responses[] = array('1','Yes');
		$responses[] = array('-1','No');
        $form->fieldset('',array('class'=>'radioGroup'));$form->radiogroup ('active',$responses,'Active:',($this->input->post('active') ? $this->input->post('active') : $this->dataModel->active));
		$form->space();
		
		$form->fieldset('',array('class'=>'button_bar'));
		$form->button('Delete','delete','button',array('class'=>'button'));	
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->button('Cancel','cancel','button',array('class'=>'button'));
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$form->hidden('submitted',1);
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
		$this->params['subTitle'] = "Project Details";
			
		$this->data['thisItem']['name'] = $this->dataModel->name;
		$this->data['thisItem']['jobCode'] = $this->dataModel->jobCode;
		$this->data['thisItem']['summary'] = $this->dataModel->summary;
		$this->data['thisItem']['description'] = $this->dataModel->description;
		$this->data['thisItem']['dateCreated'] = $this->dataModel->dateCreated;
		$this->data['thisItem']['startDate'] = $this->dataModel->startDate;
		$this->data['thisItem']['dueDate'] = $this->dataModel->dueDate;
		$this->data['thisItem']['closeDate'] = $this->dataModel->closeDate;
		$this->data['thisItem']['active'] = $this->dataModel->active;
		
		$modDate ='';
		if ($this->dataModel->lastModified != '0000-00-00 00:00:00') { 
			$modDate = date('m/j/Y',strtotime($this->dataModel->lastModified));
		}   
		$modName = '';
		$modName = resolveUsername($this->dataModel->lastModifiedBy);
		if (!empty($modName))
		    $modName = ' by '.$modName;
		$this->data['thisItem']['modifiedStr'] = $modDate.$modName;
		
		$this->data['thisItem']['projectBugs'] = loadProjectsBugs($this->dataModel->id,false);

		$this->makeNav();
		parent::showInfo();
	}
	protected function makeNav() {
		$admin = false;
		if (isset($this->params['currUser']) && $this->params['accessLevel'] >= ACCESS_DEVELOP){
			$admin = true;
		}
		array_push($this->params['subNavSection'],bugdb_nav($admin));
	}
}
/* End of file project.php */
/* Location: ./application/controllers/project.php */ 