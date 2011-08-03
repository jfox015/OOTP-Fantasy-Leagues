<?
require_once('base_editor.php');
/**
 *	Members.
 *	The primary controller for Admin membership administrator tool.
 *	@author			Jeff Fox
 *	@dateCreated	08/02/11
 *	@lastModified	08/02/11
 *
 */
class member extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	var $_NAME = 'member';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of Attendee.
	 */
	public function member() {
		parent::BaseEditor();
		$this->enqueStyle('list_picker.css');

	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects
	 *	to the login.
	 */
	public function index() {
		redirect('search/members/');
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	function init() {
		parent::init();
		$this->modelName = 'user_auth_model';

		$this->views['EDIT'] = 'member/member_editor';
		$this->views['VIEW'] = 'member/member_info';
		$this->views['FAIL'] = 'member/member_message';
		$this->views['SUCCESS'] = 'member/member_message';

		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_ADMINISTRATE;

		$this->debug = false;
	}
	/*--------------------------------
	/	PRIVATE FUNCTIONS
	/-------------------------------*/
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('memberId')) {
			$this->uriVars['memberId'] = $this->input->post('memberId');
		} // END if
		if ($this->input->post('type')) {
			$this->uriVars['type'] = $this->input->post('type');
		} // END if
		if ($this->input->post('param')) {
			$this->uriVars['param'] = $this->input->post('param');
		} // END if
	}
	protected function makeForm() {
		$form = new Form();

		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');

		$form->fieldset('Member Details');

		if (!empty($this->dataModel->dateCreated) && $this->dataModel->dateCreated != EMPTY_DATE_TIME_STR)  {
			$form->label('Signed Up:');
			$form->span(date('m/j/Y',strtotime($this->dataModel->dateCreated)));
			$form->space();
		}
		if ($this->dataModel->id != -1) {
			$form->label('Member ID');
			$form->span($this->dataModel->id);
			$form->space();
		}
		$form->text('username','UserName','required|trim',($this->input->post('username')) ? $this->input->post('username') : $this->dataModel->username,array('class','last'));
		$form->br();
		$form->text('newEmail','E-mail','required|email|trim',($this->input->post('newEmail')) ? $this->input->post('newEmail') : $this->dataModel->email);
		$form->br();

		$form->fieldset('Membership Details');
		$form->br();
		$form->select('accessId|accessId',loadSimpleDataList('accessLevel'),'Access Level',($this->input->post('accessId')) ? $this->input->post('accessId') : $this->dataModel->accessId,'required');
		$form->br();
		$form->select('levelId|levelId',loadSimpleDataList('userLevel'),'Membership Level',($this->input->post('levelId')) ? $this->input->post('levelId') : $this->dataModel->levelId,'required');
		$form->br();
		$form->select('typeId|typeId',loadSimpleDataList('userType'),'User Type',($this->input->post('typeId')) ? $this->input->post('typeId') : $this->dataModel->typeId,'required');
		$form->br();
		$responses[] = array('1','Yes');
		$responses[] = array('-1','No');
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('lockStatus',$responses,'Account Locked:',($this->input->post('lockStatus') ? $this->input->post('lockStatus') : $this->dataModel->lockStatus));
		$form->space();
        $form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('active',$responses,'User is Active:',($this->input->post('active') ? $this->input->post('active') : $this->dataModel->active));
		$form->space();

		if (isset($this->recordId) && $this->recordId != -1) {
			$form->fieldset('Password Reset');
			$form->span("Enter a password below to change the users password. Leave blank to leave unchanged.<br />");
			$form->br();
			$form->text('newPassword','New Password');
			$form->br();
			$form->text('confirmPassword','Confirm Password');
			$form->br();
		} else {
			$form->span("Enter a password belowd.<br />");
			$form->br();
			$form->text('password','Password');
		}

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
	}


	protected function showInfo() {
		// Setup header Data
		if ($this->dataModel->dateCreated != EMPTY_DATE_TIME_STR) {
			$dateCreated = date('m/j/Y h:m A',strtotime($this->dataModel->dateCreated));
		}
		$this->data['thisItem']['dateCreated'] = $dateCreated;

		$this->data['thisItem']['username'] = $this->dataModel->username;
		$this->data['thisItem']['email'] = $this->dataModel->email;

		$accessStr = '<b style="color:#c00;">Unknown!</b>';
		if ($this->dataModel->accessId != -1 && $this->dataModel->accessId != 0) {
			$accessList = loadSimpleDataList('accessLevel');
			foreach($accessList as $key => $value) {
				if ($this->dataModel->accessId == $key) {
					$accessStr = $value;
					break;
				}
			}
		}

		$this->data['thisItem']['accessStr'] = $accessStr;

		$this->data['thisItem']['locked'] = '<p style="display:inline;color:'.(($this->dataModel->locked == 1) ? '#040;">Avialable':'#f60;">Locked').'</p>';

		$this->data['thisItem']['active'] = '<p style="display:inline;color:'.(($this->dataModel->active == 1) ? '#040;">Active':'#f60;">Inactive').'</p>';

		parent::showInfo();
	}
}
/* End of file member.php */
/* Location: ./application/controllers/member.php */