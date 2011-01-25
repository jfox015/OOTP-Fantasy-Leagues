<?php
require_once('base_editor.php');
/**
 *	MT_Portal.
 *	The primary controller for the MT Portal Web site.
 *	@author			Jeff Fox
 *	@dateCreated	06/24/09
 *
 */
class bug_resolve extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'bug_resolve';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of bug_resolve.
	 */
	public function bug_resolve() {
		parent::BaseEditor();
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	function init() {
		parent::init();
		$this->modelName = 'bug_model';
		$this->views['EDIT'] = 'bug/bug_editor';
		$this->views['FAIL'] = 'bug/bug_message';
		$this->views['SUCCESS'] = 'bug/bug_message';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_DEVELOP;
	}
	public function index() {
	    redirect('bug_resolve/submit');
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	public function submit() {
	    if ($this->params['loggedIn']) {
			if ($this->params['accessLevel'] < ACCESS_DEVELOP) {
			    $this->data['heading'] = $this->lang->line('access_heading_not_authorized');
			    $this->data['message'] = '<span class="error">'.$this->lang->line('access_not_authorized').'</span>';	
			    $this->load->view('../errors/error_403',$this->data,false);
			} else {
    			$this->getURIData();
        		$this->loadData();
    			if ($this->dataModel->id != -1) {
    				$this->makeForm();
    				if ($this->input->post('formPosted')) {
    					if ($this->processForm()) {
    						$this->outMess = $this->lang->line('form_complete_success');
    						$this->messageType = "success";
    						$this->complete();
    					} else {
    						$this->outMess = str_replace('[ERROR_MESSAGE]',$this->dataModel->statusMess,$this->lang->line('form_complete_fail'));
    						$this->messageType = "fail";
    						$this->showForm();
    					}
    				} else {
    					$this->showForm();
    				}
    			} else {
    				$this->makePickForm();
    			}
			}
		} 
	}
	/*--------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/-------------------------------*/
	protected function makeForm() {
		$form = new Form();
		
		if (!$this->input->post('formPosted')) {
			$form->novalidate();
		}
		$form->open('/'.$this->_NAME.'/submit/','resolveForm|resolveForm');
		
		$form->fieldset('Resolution Details');
		$form->label('ID:');
		$form->span($this->dataModel->id);
		$form->space();
		$form->label('Summary:');
		$form->span($this->dataModel->summary);
		$form->space();
		$form->select('bugStatusId',loadSimpleDataList('bugStatus'),'Status',($this->input->post('bugStatusId')) ? $this->input->post('bugStatusId') : $this->dataModel->bugStatusId,'required');
		$form->br();$form->br();$form->select('assignmentId',loadSimpleDataList('username'),'Assignment',($this->input->post('assignmentId')) ? $this->input->post('assignmentId') : $this->dataModel->assignmentId,'required');
		$form->space();
		$form->textarea('newComments','Comments','required',$this->input->post('newComments'));
		$form->space();
		$form->span('Previous Comments (Read Only)',array('class'=>'field_caption'));
		$form->br();
		$form->html('<div class="textblock">'.$this->dataModel->comments.'</div>');
		
		$form->fieldset('',array('class'=>'button_bar'));
		$form->button('Cancel','cancel','button',array('class'=>'button'));
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$form->hidden('formPosted','1');
		$form->hidden('id',$this->dataModel->id);
		$this->form = $form;
		$this->data['form'] = $form->get();
	}
	protected function makePickForm() {
		$form = new Form();
		$form->open('/'.$this->_NAME.'/submit/','pickForm|pickForm');
		
		$form->fieldset('Bug Details');
		$bugList = array(''=>'Select a bug');
		$bugSelected = '';
		$sql = "SELECT id, summary FROM admin_bugs WHERE bugStatusId = 1 OR bugStatusId = 2 ORDER BY id, summary ASC";
		$query = $this->db->query($sql,array('1','2'));
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$sum = $row->summary;
				if (strlen($sum) > 100) { $sum = substr($sum,0,100).'...'; }
				$bugList = $bugList + array($row->id =>$row->id.' - '.$sum);
				if ($this->input->post('id') && $this->input->post('id') == $row->id) {
					$bugSelected = $row->id;
				}
			}
		}
		$query->free_result();
		$form->select('id',$bugList,'Bug Id',$bugSelected,'required');
		
		$form->fieldset('',array('class'=>'button_bar'));
		$form->button('Cancel','cancel','button',array('class'=>'button'));
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$this->form = $form;
		$this->data['form'] = $form->get();
		$this->showForm();
	}
}
/* End of file bug_resolve */
/* Location: ./application/controllers/bug_resolve */