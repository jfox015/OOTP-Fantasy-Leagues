<?php
/**
 *	OOTP Fantasy Leagues Home.
 *	The primary controller for the OOTP Fantasy Leagues Web site.
 *	@author			Jeff Fox
 *	@dateCreated	04/15/10
 *	@lastModified	05/04/10
 */
class home extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'home';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of home.
	 */
	public function home() {
		parent::MY_Controller();
		$this->enqueStyle('content.css');
	}
	/*--------------------------------
	/	PUBLIC FUNCTIONS
	/-------------------------------*/
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 */
	public function index() {
		// GET Fantasy Details
		$status = getFantasyStatus();
		$this->data['fantasyStatus'] = ($status == 1)? "Pre-Season" : "Active OOTP Season";
		$this->data['fantasyStatusID'] = $status;
		$this->data['fantasyStartDate'] = date('m/d/Y',(strtotime($this->params['config']['season_start'])));
		
		$this->data['leagueStatus'] = "Unknown";
		$this->data['leagueName'] = "";
		$this->data['leagueAbbr'] = "";
		$this->data['events'] = array();
		$this->data['current_date'] = "Unknown";
		// GET OOTP League status
		if ($this->ootp_league_model->league_id != -1) {
			$this->data['leagueStatus'] = $this->ootp_league_model->get_state();
			$this->data['leagueName'] =  $this->ootp_league_model->name;
			$this->data['leagueAbbr'] =  $this->ootp_league_model->abbr;
			$this->data['events'] =  $this->ootp_league_model->getNextEvents();
			$this->data['current_date'] =  date('m/d/Y',(strtotime($this->ootp_league_model->current_date)));
		}
		$seasonStartTime =strtotime($this->params['config']['season_start']);
		$lastprocessTime =strtotime($this->params['config']['last_process_time']);
		$lastprocess = $this->params['config']['last_process_time'];
		if ($lastprocess != EMPTY_DATE_TIME_STR && $lastprocess != EMPTY_DATE_STR && $lastprocessTime >= $seasonStartTime) {
			$this->data['nextSimDate'] =  date('m/d/Y',(strtotime($lastprocess) + ((60*60*24)*3)));
		} else {
			$this->data['nextSimDate'] = "Not yet determined";
		}
		
		// GET LATEST NEWS ARTICLE FOR THIS LEAGUE
		$this->load->model('news_model');
		$this->data['news'] = $this->news_model->getNewsByParams(NEWS_FANTASY_GAME);
		$this->data['excerptMaxChars'] = 350;
		
		$this->data['leagues'] = $this->league_model->getLeagues(1, 1);
		$this->data['amCommish'] = false;
		if ($this->params['loggedIn']) {
			$this->data['amCommish'] = $this->league_model->userIsCommish($this->params['currUser']);
		}
		$this->data['splashContent'] = $this->load->view('home_splash', false, true);
		$this->params['content'] = $this->load->view('homepage', $this->data, true);
		$this->params['subTitle'] = "Welcome to OOTP Fantasy Leagues";
	    $this->displayView();
	}
		
}
/* End of file home.php */
/* Location: ./application/controllers/home.php */