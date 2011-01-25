 <?php
require_once('base_editor.php');
/**
 *	Bug.
 *	The primary controller for BugTracker Bug manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	06/24/09
 *	@lastModified	12/21/10
 *
 */
class bug extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'bug';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of bug.
	 */
	public function bug() {
		parent::BaseEditor();
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	function init() {
		parent::init();
		$this->modelName = 'bug_model';
		
		$this->views['EDIT'] = 'bug/bug_editor';
		$this->views['VIEW'] = 'bug/bug_info';
		$this->views['FAIL'] = 'bug/bug_message';
		$this->views['SUCCESS'] = 'bug/bug_message';
		$this->views['ATTACHMENT_UPLOAD'] = 'bug/bug_attachment';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_DEVELOP;
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
    					redirect('bug/info/'.$this->dataModel->id);
    				} else {
    					$message = '<p class="error">Attachement Change Failed.';
    					if ($this->auth->get_status_code() != 0) {
    						$message .= ' '.$this->auth->get_status_message().'</p>';
    					}
    					$message .= '</p >';
    					$this->session->set_flashdata('message', $message);
    					redirect('bug/attachement');
    				}
    			}
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('home/login');
	    }
	}public function deleteAttachment() {
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
		
		$form->fieldset('bug Details');
		
		$form->label('Entered:');
		if ($this->dataModel->createdById != -1) { 
			$entryDate = date('m/j/Y',strtotime($this->dataModel->dateCreated));
			$enteredBy = $this->dataModel->createdById;
		} else { 
			$entryDate = date('m/j/Y');
			$enteredBy = $this->params['currUser'];
		}
		$form->span($entryDate." by ".resolveUsername($enteredBy));
		$form->space();
		
		$form->select('projectId|projectId',$this->dataModel->getProjectList(),'Project',($this->input->post('projectId')) ? $this->input->post('projectId') : $this->dataModel->selectedProjectId,'required');
		$form->br();
		$form->text('summary','Summary','required|trim',($this->input->post('summary')) ? $this->input->post('summary') : $this->dataModel->summary,array("class"=>"longtext"));
		$form->span('Please make this as unique as possible',array('class'=>'field_caption'));
		$form->space();
		$form->label('Description', '', array('class'=>'required'));
		$form->html('<div class="richEditor">');
		$form->html('<div id="myNicPanel" class="nicEdit-panel"></div>');
		$form->textarea('description','','required|trim',($this->input->post('description')) ? $this->input->post('description') : $this->dataModel->description,array("cols"=>50,"rows"=>16));
		$form->html('</div>');
		$form->space();
		$form->select('category|category',loadSimpleDataList('bugCategory'),'Category',($this->input->post('category')) ? $this->input->post('category') : $this->dataModel->category,'required');
		$form->br();
		$form->select('subCategory|subCategory',loadSimpleDataList('bugCategory'),'Sub Category',($this->input->post('subCategory')) ? $this->input->post('subCategory') : $this->dataModel->subCategory);
		$form->br();
		$form->text('component','Component','required|trim',($this->input->post('component')) ? $this->input->post('component') : $this->dataModel->component,array("class"=>"longtext"));
		$form->space();
		$form->text('url','URL','trim|max_length[1000]',($this->input->post('url')) ? $this->input->post('url') : $this->dataModel->url,array("class"=>"longtext"));
		$form->br();
		if ($this->recordId != -1) {
			$form->space();
			$form->textarea('newComments','Comments');
			$form->space();
			$form->span('Previous Comments (Read Only)',array('class'=>'field_caption'));
			$form->br();
			$form->html('<div class="textblock">'.$this->dataModel->comments.'</div>');
		}
		$statusList = array();
		if ($this->recordId == -1) {	
			$statusList = loadLimitedBugStatusList(3);
		} else {
			$statusList = loadSimpleDataList('bugStatus');
		}
		$form->fieldset('Meta Information');
		$form->select('severityId',loadSimpleDataList('severity','id','DESC'),'Severity',($this->input->post('severityId') ? $this->input->post('severityId') : $this->dataModel->severityId),'required');
		$form->br();
		$form->select('priorityId|priorityId',loadSimpleDataList('priority'),'Priority',($this->input->post('priorityId') ? $this->input->post('priorityId') : $this->dataModel->priorityId),'required');
		$form->br();
		$form->select('bugStatusId|bugStatusId',$statusList,'Status',($this->input->post('bugStatusId') ? $this->input->post('bugStatusId') : $this->dataModel->bugStatusId),'required');
		$form->br();
		$form->select('assignmentId|assignmentId',loadSimpleDataList('username'),'Assignment',($this->input->post('assignmentId') ? $this->input->post('assignmentId') : $this->dataModel->assignmentId));
		$form->br();
		$form->select('os',loadSimpleDataList('os'),'Platform OS',($this->input->post('os') ? $this->input->post('os') : $this->dataModel->os));
		$form->br();
		$form->select('browser',loadSimpleDataList('browser'),'Browser',($this->input->post('browser') ? $this->input->post('browser') : $this->dataModel->browser));
		$form->br();
		$form->text('browVersion','Version','trim|max_length[500]',($this->input->post('browVersion') ? $this->input->post('browVersion') : $this->dataModel->browVersion));
		$form->br();
		$form->fieldset('',array('class'=>'button_bar'));
		if ($this->dataModel->id != -1 && ($this->dataModel->createdById == $this->params['currUser'] || $this->params['accessLevel'] >= ACCESS_MANAGER)) {
			$form->button('Delete','delete','button',array('class'=>'button'));	
			$form->nobr();
			$form->span(' ','style="margin-right:8px;display:inline;"');
		}
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
		$this->params['subTitle'] = "Bug Details";
		if ($this->dataModel->dateCreated != '00-00-0000 00:00:00') { 
			$entryDate = date('m/j/Y',strtotime($this->dataModel->dateCreated));
		}   
		$enteredName = '';
		$enteredName = resolveUsername($this->dataModel->createdById);
		if (!empty($enteredName))
		    $enteredName = ' by '.$enteredName;
		$this->data['thisItem']['entryInfo'] = $entryDate.$enteredName;
		$this->data['thisItem']['createdById'] = $this->dataModel->createdById;
		
		$projectStr = '';
		if ($this->dataModel->projectId != -1) {
			$projList = $this->dataModel->loadProjectsWithBugs();
			foreach($projList as $key => $value) {
				if ($this->dataModel->projectId == $key) {
					$projectStr = $value ;
					break;
				}
			}
		}
		$this->data['thisItem']['projectId'] = $this->dataModel->projectId;
		$this->data['thisItem']['projectStr'] = $projectStr;
		$this->data['thisItem']['summary'] = $this->dataModel->summary;
		$this->data['thisItem']['description'] = $this->dataModel->description;
		$this->data['thisItem']['comments'] = $this->dataModel->comments;
		$this->data['thisItem']['component'] = $this->dataModel->component;
		
		$categoryStr = '';
		$catList = loadSimpleDataList('bugCategory');
		foreach($catList as $key => $value) {
			if ($this->dataModel->category == $key) {
				$categoryStr = $value;
				break;
			}
		}
		$this->data['thisItem']['categoryStr'] = $categoryStr;	
		
		$subCatStr = '';
		if ($this->dataModel->subCategory != -1 && $this->dataModel->subCategory != 0) {
			foreach($catList as $key => $value) {
				if ($this->dataModel->category == $key) {
					$subCatStr = $value;
					break;
				}
			}
		}
		$this->data['thisItem']['subcategoryStr'] = $subCatStr;	
		
		$severityStr = '';
		if ($this->dataModel->severityId != -1 && $this->dataModel->severityId != 0) {
			$severityList = loadSimpleDataList('severity');
			foreach($severityList as $key => $value) {
				if ($this->dataModel->severityId == $key) {
					$severityStr = $value;
					break;
				}
			}
		}
		$this->data['thisItem']['severityStr'] = $severityStr;
		
		$priorityStr = '';
		if ($this->dataModel->priorityId != -1 && $this->dataModel->priorityId != 0) {
			$priorityList = loadSimpleDataList('priority');
			foreach($priorityList as $key => $value) {
				if ($this->dataModel->priorityId == $key) {
					$priorityStr = $value;
					break;
				}
			}
		}
		$this->data['thisItem']['priorityStr'] = $priorityStr;
		
		$statusStr = '';
		if ($this->dataModel->bugStatusId != -1 && $this->dataModel->bugStatusId != 0) {
			$statusList = loadSimpleDataList('bugStatus');
			foreach($statusList as $key => $value) {
				if ($this->dataModel->bugStatusId == $key) {
					$statusStr = $value;
					break;
				}
			}
		}
		$this->data['thisItem']['statusStr'] = $statusStr;
		
		$assignedStr = '';
		$assignedStr = resolveUsername($this->dataModel->assignmentId);
		$this->data['thisItem']['assignedTo'] = $assignedStr;
		
		$platformStr = '';
		if ($this->dataModel->os != -1 && $this->dataModel->os != 0) {
			$osList = loadSimpleDataList('os');
			foreach($osList as $key => $value) {
				if ($this->dataModel->os == $key) {
					$platformStr = $value;
					break;
				}
			}
		}
		$this->data['thisItem']['platformStr'] = $platformStr;
		
		$browserStr = '';
		if ($this->dataModel->browser != -1 && $this->dataModel->browser != 0) {
			$browserList = loadSimpleDataList('browser');
			foreach($browserList as $key => $value) {
				if ($this->dataModel->browser == $key) {
					$browserStr = $value;
					break;
				}
			}
		}
		$this->data['thisItem']['browserStr'] = $browserStr;
		$this->data['thisItem']['browserVersion'] = $this->dataModel->browVersion;
		
		$modDate ='';
		if ($this->dataModel->dateModified != '0000-00-00 00:00:00') { 
			$modDate = date('m/j/Y',strtotime($this->dataModel->dateModified));
		}   
		$modName = '';
		$modName = resolveUsername($this->dataModel->lastModifiedBy);
		if (!empty($modName))
		    $modName = ' by '.$modName;
		$this->data['thisItem']['modifiedStr'] = $modDate.$modName;
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
/* End of file bug.php */
/* Location: ./application/controllers/bug.php */ 