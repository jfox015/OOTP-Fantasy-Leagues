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
	/**
	 *	NEWS LANDING LIMIT
	 *	@var $newsLandingLimit:Int
	 */
	var $newsLandingLimit = 20;
	/**
	 *	EXERPT MAX CHARS
	 *	@var $excerptMaxChars:Int
	 */
	var $excerptMaxChars = 125;
	/**
	 *	TYPE TITLE
	 *	@var $hasAccess:String
	 */
	var $typeTitle = 'Fantasy';
	/**
	 *	HAS ACCESS
	 *	@var $hasAccess:Boolean
	 */
	var $hasAccess = true;
	/**
	 *	LEAGUE DETAILS
	 *	@var $leagueDetails:Array
	 */
	var $leagueDetails = array();
	/**
	 *	TEAM DETAILS
	 *	@var $teamDetails:Array
	 */
	var $teamDetails = array();
	/**
	 *	PLAYER DETAILS
	 *	@var $playerDetails:Array
	 */
	var $playerDetails = array();
	/**
	 *	IS ADMIN
	 *	@var $isAdmin:Boolean
	 */
	var $isAdmin = false;
	/**
	 *	IS LEAGUE COMMISH
	 *	@var $isLeagueCommish:Boolean
	 */
	var $isLeagueCommish = false;
	/**
	 *	IS TOWN OWNER
	 *	@var $isTeamOwner:Boolean
	 */
	var $isTeamOwner = false;
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
		$this->views['VIEW'] = 'news/news_article';
		$this->views['FAIL'] = 'news/news_message';
		$this->views['SUCCESS'] = 'news/news_message';
		$this->views['IMAGE'] = 'news/news_image';
		$this->views['PREVIEW'] = 'news/news_preview';
		$this->views['ARTICLES'] = 'news/news_articles';
		$this->views['ARTICLE'] = 'news/news_article';
		$this->views['SECONDARY'] = 'news/news_secondary';
		
		$this->restrictAccess = true;
		$this->minAccessLevel = ACCESS_READ;
		
		//$this->enqueStyle('font-awesome.min.css');
		$this->enqueStyle('news.css');
		$this->debug= false;
	}
	/*--------------------------------
	/	PUBLIC FUNCTIONS
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
	 *  ARTICLES.
	 *	Displays the news landing page with a list of articles. Can be filtered by category
	 *
	 * 	@since 	1.0.3 PROD
	 *
	 */
	public function articles() {
		$this->getURIData();

		if (!$this->dataModel) {
			$this->load->model($this->modelName);
		}
		$typeId = -1;
		$varId = -1;
		
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

		if ($typeId > 1) { $this->setSupportingInformation($typeId, $varId); }

		if ($this->hasAccess) {

			$this->data['articles'] = $this->dataModel->getNewsByParams($typeId, $varId, $this->newsLandingLimit);
			$this->data['related'] = $this->dataModel->getRelatedArticles($this->dataModel->id);
			$this->data['excerptMaxChars'] = $this->excerptMaxChars;
			$this->data['news_types'] = loadSimpleDataList('newsType');
			$this->data['extra_data'] = array('isAdmin'=>$this->isAdmin,'isLeagueCommish'=>$isLeagueCommish,'isTeamOwner'=>$this->isTeamOwner,
											  'leagueDetails'=>$this->leagueDetails,'playerDetails'=>$this->playerDetails,
											  'teamDetails'=>$this->teamDetails,'isTeamOwner'=>$this->isTeamOwner,'typeTitle'=>$this->typeTitle);
			$this->data['type_id'] = $typeId;
			$this->data['news_type_name'] = $this->data['news_types'][$typeId];
			$this->data['var_id'] = $varId;

			$this->makeNav();						
			$this->data['subTitle'] = "News";
			$this->data['pageTitle'] = $this->typeTitle." News Articles";
			$this->data['secondary'] = $this->load->view($this->views['SECONDARY'], $this->data, true);
			$this->params['content'] = $this->load->view($this->views['ARTICLES'], $this->data, true);
			$this->displayView();
		} else {
			$this->data['subTitle'] = "Unauthorized Access";
			$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		}		
	}
	/**
	 *  ARTICLE.
	 *	Displays an individual news article
	 *
	 * 	@since 	1.0.3 PROD
	 *
	 */
	public function article() {
		$this->getURIData();

		if (!$this->dataModel) {
			$this->load->model($this->modelName);
		}
		if (isset($this->uriVars['id'])) {
			$this->dataModel->load($this->uriVars['id']);
		}

		$typeId = -1;
		$varId = -1;
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

		if ($typeId > 1) { $this->setSupportingInformation($typeId, $varId); }

		if ($this->hasAccess) {

			$this->data['article'] = $this->dataModel->dumpArray();
			$this->data['author'] = $this->dataModel->getArticleAuthor($this->dataModel->id);
			$this->data['articleType'] = $this->dataModel->getArticleType($this->dataModel->id);

			$this->data['related'] = $this->dataModel->getRelatedArticles($this->dataModel->id);
			$this->data['news_types'] = loadSimpleDataList('newsType');
			$this->data['extra_data'] = array('isAdmin'=>$this->isAdmin,'isLeagueCommish'=>$isLeagueCommish,'isTeamOwner'=>$this->isTeamOwner,
											  'leagueDetails'=>$this->leagueDetails,'playerDetails'=>$this->playerDetails,
											  'teamDetails'=>$this->teamDetails,'isTeamOwner'=>$this->isTeamOwner,'typeTitle'=>$this->typeTitle);
			$this->data['type_id'] = $typeId;
			$this->data['var_id'] = $varId;
			$this->data['article_id'] = $this->uriVars['id'];

			$this->makeNav();						
			$this->data['subTitle'] = $this->typeTitle." News Articles";
			$this->data['secondary'] = $this->load->view($this->views['SECONDARY'], $this->data, true);
			$this->params['content'] = $this->load->view($this->views['ARTICLE'], $this->data, true);
			$this->displayView();
		} else {
			$this->data['subTitle'] = "Unauthorized Access";
			$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		}			
	}
	/*---------------------------------------------------------------------
	/
	/	PROTECTED FUNCTIONS
	/
	/--------------------------------------------------------------------*/
	/**
	 *	SET SUPPORTING INFORMATION.
	 *	Creates a sub nav menu for the given content category.
	 *
	 *	@param		$typeId		{int}	The article type (category)
	 *	@param		$varId		{int}	The variable ID param
	 *	@return					{Void}	Sets Members vars with details
	 *	@since 					1.0.3 PROD
	 *	@access					protected
	 *	
	 */
	protected function setSupportingInformation($typeId = -1, $varId = -1) {
		
		$this->load->model('league_model');
		$this->load->model('team_model');
		if ($varId != -1) {
			if ($typeId == 2 || $typeId == 4) {
				$league_id = $varId;
				if ($typeId == 4) {
					$this->team_model->load($varId);
					$league_id = $this->team_model->league_id;
				}
				$this->league_model->load($league_id);
				$this->hasAccess = $this->league_model->userHasAccess($this->params['currUser']);
				$this->isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
				$this->isLeagueCommish = ($this->league_model->userIsCommish($this->params['currUser'])) ? true: false;
			}
		}
		switch ($typeId) {
			// League
			case 2:	
				$this->typeTitle = 'League';
				if ($varId != -1) {
					$this->leagueDetails = $this->league_model->getLeagueDetails();
					if (!empty($this->leagueDetails['league_name'])) $this->typeTitle = $this->leagueDetails['league_name'];
				} 
				break;
			// PLAYER
			case 3:
				$this->typeTitle = 'Player';
				if ($varId != -1) {
					$this->load->model('player_model');
					$this->player_model->load($varId);
					$this->playerDetails = $this->player_model->getPlayerDetails($varId);
					if (!empty($this->playerDetails['first_name'])) $this->typeTitle = $this->playerDetails['first_name']." ".$this->playerDetails['last_name'];
				}
				break;
			case 4:	
				$this->typeTitle = 'Team';
				if ($varId != -1) {
					$this->teamDetails = $this->team_model->getTeamDetails();
					if (!empty($this->teamDetails['teamname'])) $this->typeTitle = $this->teamDetails['teamname']." ".$this->teamDetails['teamnick'];
					$this->isTeamOwner = $this->teamDetails['owner_id'] == $this->params['currUser'];
				}
				break;
		}
		return true;
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
		$lg_admin = false;
		array_push($this->params['subNavSection'], news_nav($lg_admin));
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
		
		if ($typeId == NEWS_LEAGUE) {
			/*$leagueLabel = "Select ";
			$form->label('League', '');
			$this->load->model('league_model');
			if ($varId != -1) {
				$form->span($this->league_model->getLeagueName($varId),array('class'=>'form_span'));
				$form->br();
				$leagueLabel = "Change ";
			}*/
			$leagues = $this->league_model->getLeagues(-1);
			$leagueArr = array(-1 =>'Select League');
			if (sizeof($leagues) > 0) {
				$idx = 0;
				foreach($leagues as $league_id => $leagueData) {
					if (($this->league_model->userHasAccess($this->params['currUser'], $league_id) || $this->league_model->userIsCommish($this->params['currUser'], $league_id)) || $this->data['accessLevel'] == ACCESS_ADMINISTRATE) {
						$leagueArr[$league_id] = $leagueData['league_name'];
					}
				}
			}
			if (count($leagueArr) > 0)
				$form->select('var_id|var_id',$leagueArr,'League:',($this->input->post('var_id') ? $this->input->post('var_id') : $varId));
			else
				$form->hidden('var_id',$varId);
			
		} else if ($typeId == NEWS_TEAM) {
			if ($varId != -1) {
				$this->load->model('team_model');
				$teamDetails = $this->team_model->getTeamDetails($varId);
				$league_id = $teamDetails['league_id'];
				$this->league_model->load($league_id);
				$teams = $this->league_model->loadLeagueTeams($league_id);
				$teamsArr = array(-1 =>'Select Team');
				if (sizeof($teams) > 0) {
					$idx = 0;
					if (!function_exists('getTeamOwnerId')) {
						$this->load->helper('roster');
					}
					foreach($teams as $team_id => $teamname) {
						if (($this->league_model->userHasAccess($this->params['currUser'], $league_id) || $this->params['currUser'] == getTeamOwnerId($team_id) || $this->league_model->userIsCommish($this->params['currUser'], $league_id)) || $this->data['accessLevel'] == ACCESS_ADMINISTRATE) {
							$teamsArr[$team_id] = $teamname;
						}
					}
				}
				if (count($teamsArr) > 0)
					$form->select('var_id|var_id',$teamsArr,'Select Team:',($this->input->post('var_id') ? $this->input->post('var_id') : $varId),'required');
				else
					$form->hidden('var_id',$varId);
			} else {
				$form->hidden('var_id',$varId);
			}
		} else if ($typeId == NEWS_PLAYER) {
			// GET LIST OF PLAYERS
			$this->load->model('player_model');
			$players = $this->player_model->getOOTPPlayers(false,false,false,false,false,false,true);
			$form->br();
			$form->select('var_id|var_id',$players,'Select Player:',($this->input->post('var_id') ? $this->input->post('var_id') : $varId),'required');
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
			$this->data['mode'] = 'add';
		}
		$this->form = $form;
		$this->data['form'] = $form->get();

		if ($typeId > 1) { $this->setSupportingInformation($typeId, $varId); }

		$this->data['news_types'] = loadSimpleDataList('newsType');
		$this->data['extra_data'] = array('isAdmin'=>$this->isAdmin,'isLeagueCommish'=>$isLeagueCommish,'isTeamOwner'=>$this->isTeamOwner,
											'leagueDetails'=>$this->leagueDetails,'playerDetails'=>$this->playerDetails,
											'teamDetails'=>$this->teamDetails,'isTeamOwner'=>$this->isTeamOwner,'typeTitle'=>$this->typeTitle);
		$this->data['news_type_name'] = $this->data['news_types'][$typeId];
		$this->data['secondary'] = $this->load->view($this->views['SECONDARY'], $this->data, true);
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
		if (!function_exists('ascii_to_entities')) {
			$this->load->helper('text');
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