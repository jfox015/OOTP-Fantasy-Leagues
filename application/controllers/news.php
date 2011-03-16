<?php
require_once('base_editor.php');
/**
 *	Attendee.
 *	The primary controller for news manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	03/23/10
 *	@lastModified	07/26/10
 *
 */
class news extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'news';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of news.
	 */
	public function news() {
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
		redirect('search/news/');
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	function init() {
		parent::init();
		$this->modelName = 'news_model';
		
		$this->views['EDIT'] = 'news/news_editor';
		$this->views['VIEW'] = 'news/news_info';
		$this->views['FAIL'] = 'news/news_message';
		$this->views['SUCCESS'] = 'news/news_message';
		$this->views['IMAGE'] = 'news/news_image';
		$this->views['PREVIEW'] = 'news/news_preview';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_WRITE;
		
		$this->debug= false;
	}
	/*--------------------------------
	/	PRIVATE FUNCTIONS
	/-------------------------------*/
	public function image() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			$this->data['imageLink'] = $this->dataModel->imageLink;
			$this->data['articleId'] = $this->dataModel->id;
			$this->data['news_subject'] = $this->dataModel->news_subject;
			$this->data['subTitle'] = 'Upload an Image';
			
			//echo("Submitted = ".(($this->input->post('submitted')) ? 'true':'false')."<br />");
			if (!($this->input->post('submitted')) || ($this->input->post('submitted') && !isset($_FILES['imageFile']['name']))) {
				if ($this->input->post('submitted') && !isset($_FILES['imageFile']['name'])) {
					$fv = & _get_validation_object();
					$fv->setError('imageFile','An image path is required to continue.');
				}
				$this->params['content'] = $this->load->view($this->views['IMAGE'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$imgFile = strtolower($_FILES['imageFile']['name']);
				//echo("loc of .pdf = ".$posPDF ."<br />");
				if (!strpos($imgFile,'.jpg') && !strpos($imgFile,'.jpeg') && !strpos($imgFile,'.gif') && !strpos($imgFile,'.png')) {
					$fv = & _get_validation_object();
					$fv->setError('pdfFile','The file selected is not a valid image file.');  
					$this->params['content'] = $this->load->view($this->views['IMAGE'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					if ($_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
						$change = $this->dataModel->applyData($this->input, $this->params['currUser']); 
						if ($change) {
							$this->dataModel->save();
							$this->session->set_flashdata('message', '<span class="success">The image has been successfully updated.</span>');
							redirect('news/info/'.$this->dataModel->id);
						} else {
							$message = '<span class="error">The Image Update Operation Failed</span >';
							$this->session->set_flashdata('message', $message);
							redirect('news/image/'.$this->dataModel->id);
						}
					} else {
						throw new UploadException($_FILES['file']['error']);
					}
				}
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('home/login');
	    }
	}
	public function removeImage() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->dataModel->id != -1) {
				$success = $this->dataModel->deleteFile('imageFile',PATH_NEWS_IMAGES_WRITE,true);
			}
			if ($success) {
				$this->session->set_flashdata('message', '<span class="success">The image has been successfully deleted.</span>');
				redirect('news/info/'.$this->dataModel->id);
			} else {
				$message = '<span class="error">Image Delete Failed.';
				$message .= '<b>'.$this->dataModel->statusMess.'</b></span >';
				$this->session->set_flashdata('message', $message);
				redirect('news/image/'.$this->dataModel->id);
			}
		}
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('type_id')) {
			$this->uriVars['type_id'] = $this->input->post('type_id');
		} // END if
		if ($this->input->post('var_id')) {
			$this->uriVars['var_id'] = $this->input->post('var_id');
		} // END if
	}
	protected function makeForm() {
		
		$this->data['preview'] = $this->preview();
		
		$form = new Form();
		
		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');
		
		$form->fieldset('Article Details');
		
		if (isset($this->uriVars['type_id'])) {
			$typeId = $this->uriVars['type_id'];
		} else {
			$typeId = NEWS_FANTASY_GAME;
		}
		if (isset($this->uriVars['var_id'])) {
			$varId = $this->uriVars['var_id'];
		} else {
			$varId = -1;
		}
		if ($this->dataModel->id != -1) {
			$form->label('Article ID');
			$form->span($this->dataModel->id);
			$form->br();
			if($this->dataModel->type_id != -1) {
				$typeId = $this->dataModel->type_id;
			}
			if($this->dataModel->var_id != -1) {
				$varId = $this->dataModel->var_id;
			}
		}
		if ($this->dataModel->id != -1) {
			$authorId = $this->dataModel->author_id;
		} else {
			$authorId = $this->params['currUser'];
		}
		$authorName = resolveUsername($authorId);
		if (empty($authorName)) {
			$authorName = "Unknown Author";
		}
		$form->label('Author');
		$form->span($authorName,array('class'=>'form_span'));
		$form->br();
		
		$savedPublishDate = '';
		if ($this->dataModel->news_date != EMPTY_DATE_TIME_STR) {
			$savedNewsDate = $this->dataModel->news_date;
		}
		$datesArr= array('','','');
		if (!empty($newsDate)) {
			$datesArr = explode("-",date('Y-m-d',strtotime($newsDate)));
		} else {
			$datesArr = explode("-",date('Y-m-d'));
		}
		$form->label('Story Date', '', array('class'=>'required'));
		if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->league_model->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)){
			$form->nobr();
			$form->select('storyDateM|storyDateM',getMonths(),'',($this->input->post('storyDateM') ? $this->input->post('storyDateM') : $datesArr[1]),'required');
			$form->nobr();
			$form->select('storyDateD|storyDateD',getDays(),'',($this->input->post('storyDateD') ? $this->input->post('storyDateD') : $datesArr[2]),'required');
			$form->nobr();
			$form->select('storyDateY|storyDateY',getYears(),'',($this->input->post('storyDateY') ? $this->input->post('storyDateY') : $datesArr[0]),'required');
			
		} else {
			$form->hidden('storyDateM',$datesArr[1]);
			$form->hidden('storyDateD',$datesArr[2]);
			$form->hidden('storyDateY',$datesArr[0]);
			$form->span($datesArr[1]."/".$datesArr[2]."/".$datesArr[0],array('class'=>'form_span'));
		}
		if ($typeId == NEWS_PLAYER) {
			// GET LIST OF PLAYERS
			$this->load->model('player_model');
			$players = $this->player_model->getOOTPPayers(false,false,false,false,false,false,true);
			$form->br();
			$form->select('varId|varId',$players,'Select Player:',($this->input->post('varId') ? $this->input->post('varId') : $varId),'required');
			$form->nobr();
			$form->html('<div style="margin-top:5px;">'.anchor('/players/stats/','See all Players').'</div>');
		} else {
			$form->hidden('var_id',$varId);
		}
		$this->data['var_id'] = $varId;
		$form->br();
		$form->text('news_subject','Title','required|trim',($this->input->post('news_subject')) ? $this->input->post('news_subject') : $this->dataModel->news_subject,array("class"=>"longtext"));
		$form->br();
		$form->label('Article Body', '', array('class'=>'required'));
		$form->html('<div class="richEditor">');
		$form->html('<div id="myNicPanel" class="nicEdit-panel"></div>');
		$form->textarea('news_body','','required|trim',($this->input->post('news_body')) ? $this->input->post('news_body') : $this->dataModel->news_body,array("cols"=>50,"rows"=>8));
		$form->html('</div>');
		$form->br();
		if ($typeId == NEWS_PLAYER) {
			$form->textarea('fantasy_analysis','Fantasy Analysis','trim',($this->input->post('fantasy_analysis')) ? $this->input->post('fantasy_analysis') : $this->dataModel->fantasy_analysis,array("cols"=>50,"rows"=>8));
			$form->br();
		}
		
		//$imgField = 'img'.substr(md5(time()),0,4).'File';
		$form->iupload ('imageFile','Image',false,array("class"=>"longtext"));
		//$form->hidden('imgField',$imgField);
		$imageFile = '';
		$imagePath = '';
		if (isset($this->data['thisItem']['uploadedImage'])) {
			$form->hidden('uploadedImage',$this->data['thisItem']['uploadedImage']);
			$imageFile = $this->data['thisItem']['uploadedImage'];
			$imagePath = PATH_NEWS_IMAGES_PREV.$imageFile;
		} else if (isset($this->dataModel->image) && !empty($this->dataModel->image)) {
			$imageFile = $this->dataModel->image;
			$imagePath = PATH_NEWS_IMAGES.$imageFile;
		}
		if (!empty($imageFile) && !empty($imagePath)) {
			$form->span('Current Image: '.$imageFile.' &nbsp;<img src="'.$imagePath.'" style="width:45px; height:45px;" align="absmiddle" />',array('class'=>'field_caption'));
			$form->space();
		}
		$form->fieldset('Review Options');

		$form->fieldset('',array('class'=>'radioGroup'));
		
		$responses[] = array('1','Yes');
		$responses[] = array('-1','No');
		$form->radiogroup ('showPreview',$responses,'Preview',($this->input->post('showPreview') ? '-1' : '1'));
       	$form->fieldset('');
		$form->fieldset('',array('class'=>'button_bar'));
		if ($this->dataModel->id != -1) {
			$form->button('Delete','delete','button',array('class'=>'button'));	
			$form->nobr();
			$form->span(' ','style="margin-right:8px;display:inline;"');
		}
		$form->button('Cancel','cancel','button',array('class'=>'button'));
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$form->hidden('submitted',1);
		$this->data['type_id'] = $typeId;
		$form->hidden('type_id',$typeId);
		$form->hidden('author_id',$authorId);
		$form->hidden('preview','0');
		if ($this->recordId != -1) {
			$form->hidden('mode','edit');
			$form->hidden('id',$this->recordId);
		} else {
			$form->hidden('mode','add');
		}
		$this->form = $form;
		$this->data['form'] = $form->get();
	}
	
	protected function preview() {
		
		if (($this->input->post('showPreview') && $this->input->post('showPreview') != -1) && $this->input->post('news_subject')) {
			
			$this->data['thisItem']['news_date'] = date('m/j/Y',strtotime($this->input->post('storyDateY')."-".$this->input->post('storyDateM')."-".$this->input->post('storyDateD')));
			$this->data['thisItem']['news_subject'] = $this->input->post('news_subject');
			$this->data['thisItem']['news_body'] = $this->input->post('news_body');
			$this->data['thisItem']['author_id'] = $this->input->post('author_id');
			$this->data['thisItem']['author'] = resolveOwnerName($this->input->post('author_id'));
			$this->data['thisItem']['image'] = '';
			$this->data['thisItem']['imageFile'] = '';
			//$imgField = $this->input->post('imgField');
			if (isset($_FILES['imageFile']['tmp_name']) && !empty($_FILES['imageFile']['tmp_name'])) {
				// SAVE THE FILE TO TMP WRITE DIR
				if (is_writable(DIR_WRITE_PATH.PATH_NEWS_IMAGES_PREV_WRITE)) {
					$_FILES['imageFile']['name'] = str_replace(" ","_",$_FILES['imageFile']['name']);
					$target_file_name = DIR_WRITE_PATH.PATH_NEWS_IMAGES_PREV_WRITE.$_FILES['imageFile']['name'];
					if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $target_file_name)) {
						chmod($target_file_name,0755);
						$success = true;
						$this->data['thisItem']['image'] = $_FILES['imageFile']['name'];
						$this->data['thisItem']['uploadedImage'] = $_FILES['imageFile']['name'];
					} // END if
				}
			}
			$this->data['thisItem']['type_id'] = $this->input->post('type_id');
			$this->data['thisItem']['var_id'] = $this->input->post('var_id');
			return $this->load->view($this->views['PREVIEW'],$this->data, true);
		} else {
			return '';
		}
	}
	
	protected function showInfo() { 
		// Setup header Data 
		$this->data['thisItem']['news_date'] = '';
		if ($this->dataModel->news_date != EMPTY_DATE_TIME_STR) { 
			$this->data['thisItem']['news_date'] = date('m/j/Y',strtotime($this->dataModel->news_date));
		}
		$this->data['thisItem']['news_subject'] = $this->dataModel->news_subject;
		$this->data['thisItem']['news_body'] = $this->dataModel->news_body;
		$this->data['thisItem']['author_id'] = $this->dataModel->author_id;
		$this->data['thisItem']['author'] = resolveOwnerName($this->dataModel->author_id);
		$this->data['thisItem']['image'] = $this->dataModel->image;
		$this->data['thisItem']['type_id'] = $this->dataModel->type_id;
		$this->data['thisItem']['var_id'] = $this->dataModel->var_id;
		
		$this->data['thisItem']['related'] = $this->dataModel->getRelatedArticles($this->dataModel->id, 5);
		$this->params['subTitle'] = "News";
		
		parent::showInfo();
	}
}
/* End of file news.php */
/* Location: ./application/controllers/news.php */ 