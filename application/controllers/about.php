<?php
/**
 *	About Controller.
 *	The primary controller for the About Section.
 *	@author			Jeff Fox
 *	@dateCreated	4/18/10
 *	@lastModified	6/16/10
 *
 */
class about extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'about';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of about.
	 */
	public function about() {
		parent::MY_Controller();	
		$this->lang->load('about');
		$this->views['GENERAL'] = 'about/about_general';
		$this->views['SITE'] = 'about';
		$this->views['MOD'] = 'about/about_mod';
		$this->views['PENDING'] = 'content_pending';
		$this->views['CONTACT_FORM'] = 'about/contact';
		$this->views['BUG_REPORT_FORM'] = 'about/bug_report';
		$this->views['BUG_REPORT_RESPONSE'] = 'about/bug_response';
		
		$this->debug = false;
	}
	/*--------------------------------
	/	PUBLIC FUNCTIONS
	/-------------------------------*/
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	public function index() {
		
		$this->makeNav();
		$this->data['subTitle'] = $this->lang->line('about_title');
		$this->data['theContent'] = $this->load->view($this->views['SITE'], false, true);
		$this->params['content'] = $this->load->view($this->views['GENERAL'], $this->data, true);
	    $this->displayView();
	}
	public function about_mod() {
		$this->mod();
	}
	/**
	 * 	GENERAL CONTACT FORM
	 *
	 * 	@return void
	 *	@since	1.0.3
	 **/
	function contact() {
		$this->makeNav();
		$this->params['subTitle'] = $this->data['subTitle'] = $this->lang->line('about_contact_title');
		$this->data['theContent'] = $this->lang->line('about_contact_body');
		
		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'E-Mail Address', 'required|trim|email');
		$this->form_validation->set_rules('subject', 'Subject', 'required|trim');
		$this->form_validation->set_rules('details', 'Message Body', 'required|trim');
		
		if ($this->form_validation->run() == false) {
			
			// EDIT 1.0.6 - SECURITY
			if ($this->params['config']['security_enabled'] != -1 && $this->params['config']['security_class'] >= 1) {
				$this->data = $this->data + getSecurityCode($this->views['RECAPTCHA_JS']);
			} // END if
			
			$this->data['input'] = $this->input;
			$this->data['config'] = $this->params['config'];	
			$this->params['content'] = $this->load->view($this->views['CONTACT_FORM'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		} else {
			// GET ADMIN EMAIL
			$outMess = "";
			$data = array('siteName'=>$this->params['config']['site_name'],'name'=>$this->input->post('name'),'email'=>$this->input->post('email'),
						  'details'=>$this->input->post('details'));
			
			$message = $this->load->view('email_templates/contact', $data, true);
			
			$toMail = $this->user_auth_model->getEmail($this->params['config']['primary_contact']);
			if (isset($toMail) && !empty($toMail)) {
			
				$sent = sendEmail($toMail, $this->input->post('email'), $this->input->post('name'),
									$this->input->post('subject'), $message, "Site Admin", 'email_contact_');									   
				
				if ($sent) {
					$outMess = "Thank you. Your submission has been sent successfully.<p />
					<b>Hera re the details of your submission:</b><p />
					<b>From:</b> ".$this->input->post('name')."<br />
					<b>Subject:</b> ".$this->input->post('subject')."<p />
					<b>Details:</b> ".$this->input->post('details');
					if ($this->debug) {
						$outMess .= "<h3>Technical Details</h3>
						<b>To:</b> ".$toMail;
					} // END if
				} else {
					$outMess  = "There was a problem with your submission. The email could not be sent at this time. Please try again later.";
				} // END if
			} else {
				$outMess  = "There was a problem with your submission. A propper recipient email address could not be found.";
			} // END if
			$this->data['theContent'] = $outMess;
			$this->params['content'] = $this->load->view($this->views['GENERAL'], $this->data, true);
	   		$this->displayView();
		} // END if
	}
	
	/**
	 * MOD DETAILS
	 *
	 * 	@return void
	 *	@since	1.0.3
	 **/
	function mod() {
		$this->makeNav();
		$this->data['subTitle'] = $this->lang->line('about_mod_title');
		$this->data['theContent'] = str_replace('[SITE_VERSION]',SITE_VERSION,$this->lang->line('about_mod_body'));
		$this->params['content'] = $this->load->view($this->views['GENERAL'], $this->data, true);
	    $this->displayView();
	}
	/**
	 * Report Bug
	 *
	 * @return void
	 **/
	function bug_report() {
	    $this->load->helper('datalist');
	    $this->load->helper('display');
	    $this->form_validation->set_rules('summary', 'Summary', 'required|max_length[1000]');
	    $this->form_validation->set_rules('description', 'Description', 'required|max_length[10000]');
	    $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	    
	    if ($this->form_validation->run() == false) {
	       	
	    	// EDIT 1.0.6 - SECURITY
	    	if ($this->params['config']['security_enabled'] != -1 && $this->params['config']['security_class'] >= 2) {
	    		$this->data = $this->data + getSecurityCode($this->views['RECAPTCHA_JS']);
	    	} // END if
	    			
	    	$this->data['subTitle'] = $this->lang->line('about_report_bug_title');
			$this->data['theContent'] = $this->lang->line('about_report_bug_body');
		   	$this->makeNav();
			$this->params['content'] = $this->load->view($this->views['BUG_REPORT_FORM'], $this->data, true);
	        $this->session->set_flashdata('message', '');
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
	    } else {
	        $this->load->model('bug_model');
	        if ($this->bug_model->applyData($this->input,$this->params['currUser'])) {    
	            $added = $this->bug_model->save(); 
    			if ($added) {
    				$this->data['subTitle'] = $this->lang->line('about_report_bug_title');
    				$this->data['theContent'] = '<p class="success">Your bug report was submitted successfully</p><br />'.$this->lang->line('about_report_bug_success').'';
    	           	$this->makeNav(); 
					$this->params['content'] = $this->load->view($this->views['BUG_REPORT_RESPONSE'], $this->data, true);
    	        	$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
    			} else {
    				$message = '<p class="error">An error occured when trying to submit your bug report.';
    				if ($this->bug_model->errorCode != 0) {
    					$message .= ' '.$this->bug_model->statusMess.'</p>';
    				}
    				$message .= '</p >';
    				$this->session->set_flashdata('message', $message);
    	            redirect('about/report_bug');
    			}
	        } else {
	            $message = '<p class="error">An error occured when trying to submit your bug report.';
				if ($this->bug_model->errorCode != 0) {
					$message .= ' '.$this->bug_model->statusMess.'</p>';
				}
				$message .= '</p >';
				$this->session->set_flashdata('message', $message);
	            redirect('about/bug_report');
	        }
	    }
	}
	protected function makeNav() {
		$bugLink = '';
		if (isset($this->params['config']['bug_db'])) {
			$bugLink = 'internal';
		}
		array_push($this->params['subNavSection'],about_nav($bugLink));
	}
}
/* End of file about.php */
/* Location: ./application/controllers/about.php */