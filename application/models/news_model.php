<?php
/**
 *	NEWS MODEL CLASS.
 *	
 *
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
*/
class news_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'news_model';
	
	var $news_date = EMPTY_DATE_TIME_STR;
	var $type_id = -1;
	var $var_id = -1;
	var $author_id = -1;
	var $news_subject = '';
	var $news_body	 = '';
	var $image = '';
	var $imageFile = '';
	var $imageField = '';
	var $fantasy_analysis = '';
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of news_model
	/
	/---------------------------------------------*/
	function news_model() {
		parent::__construct();
		
		$this->tblName = 'fantasy_news';
		
		$this->fieldList = array('type_id','var_id','author_id','news_subject','news_body','fantasy_analysis');
		$this->conditionList = array('imageFile','storyDateM','storyDateD','storyDateY');
		$this->readOnlyList = array('image','news_date');
		$this->textList = array('news_subject','news_body','fantasy_analysis','uploadedImage');  
		
		$this->columns_select = array('id','type_id','var_id','author_id','news_subject','news_date');
		$this->columns_text_search = array('news_subject','news_body');
		
		$this->addSearchFilter('type_id','News Type','newsType','newsType');
		parent::_init();
	}
	/**
	 * 	APPLY DATA.
	 *
	 *	Applies custom data values to the object. 
	 *
	 * 	@return 	TRUE on success, FALSE on failure
	 *
	 */
	public function applyData($input,$userId = -1) {
		if (parent::applyData($input,$userId)) {
			if ($input->post('storyDateM') && $input->post('storyDateD') && $input->post('storyDateY')) {
				$this->news_date = $input->post('storyDateY').'-'.$input->post('storyDateM').'-'.$input->post('storyDateD');
			}
			//echo("uploadedImage = '".$this->uploadedImage.'"<br />');
			if (!empty($this->uploadedImage))
				$success = $this->useUploadedImage($this->uploadedImage);
			else if (empty($this->uploadedImage) && isset($_FILES['imageFile']['name']) && !empty($_FILES['imageFile']['name']))
				$success = $this->uploadFile('image',PATH_NEWS_IMAGES_WRITE,$input,'image',$this->var_id.$_FILES['imageFile']['name']);														 	
			return true;
		} else {
			$this->statusMess = "An error occured applying the data recieved.";
			$this->errorCode = 1;
			return false;
		} // END if
	}	
	/**
	* 	DELETE NEWS.
	* 	This function clears news articles for a given article type which can be global, league, player or team. 
	* 	Articles can be deleted by author id as well.
	*
	* 	@param	$type_id		(int)	The article type identifier (Global, league or player)
	* 	@param	$var_id			(int)	Article Type value
	* 	@param	$author_id		(int)	OPTIONAL Author identifier
	* 	@return					(int)	Affected Row count
	*
	* 	@since	1.0.6
	*/
	public function deleteNews($type_id = false, $var_id = false, $author_id = false) {
		if ($type_id === false || $var_id === false) {
			$this->errorCode = 1;
			$this->statusMess = "No article type or identifier value was recieved.";
			return false;
		}
	
		$this->db->where("type_id",$type_id);
		$this->db->where("var_id",$var_id);
		if ($author_id !== false) {
			$this->db->where("author_id",$author_id);
		}
		$this->db->delete($this->tblName);
		return $this->db->affected_rows();
	}
	
	public function useUploadedImage($image) {
		$success = false;
		$newImg = DIR_WRITE_PATH.PATH_NEWS_IMAGES_PREV_WRITE.$image;
		if (file_exists($newImg)) {
			if (copy($newImg, DIR_WRITE_PATH.PATH_NEWS_IMAGES_WRITE.$image)) {
				chmod(DIR_WRITE_PATH.PATH_NEWS_IMAGES_WRITE.$image,0755);
				unlink($newImg);
				$success = true;
				$this->image = $image;
				$this->statusMess = "File upload completed successfully.";
			} else {
				$this->errorCode = 3;
				$this->statusMess .= "The file upload process did not complete successfully. The file ".basename(PATH_NEWS_IMAGES_PREV_WRITE.$image)." could not be saved on the server.";
			} // END if
		}
		return $success;
	}
	/**
	* 	GET ARTICLE AUTHOR
	* 	Returns the author information for the passed article
	*
	* 	@param	$article_id		(int)	The article ID)
	* 	@return					(Array)	Aarray of author information
	* 	@since	1.0.3 PROD
	*/
	public function getArticleAuthor($article_id = false) {
		
		if ($article_id === false) $article_id = $this->id;
		
		if ($article_id == -1) return;

		$author = array();
		$this->db->select($this->tblName.'.id, firstName, lastName, username, author_id');
		$this->db->join('users_core',$this->tblName.'.author_id = users_core.id','left');
		$this->db->join('users_meta',$this->tblName.'.author_id = users_meta.userId','left');
		$this->db->from($this->tblName);
		$this->db->where($this->tblName.'.id',$article_id);
		$query = $this->db->get();
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$author = $query->row_array();
			$authorName = (!empty($author['firstName']) && !empty($author['lastName'])) ? $author['firstName']." ".$author['lastName'] : $author['username'];
			$author['authorName'] = $authorName;
		}
		$query->free_result();
		return $author;
	}/**
	* 	GET ARTICLE TYPE (Category)
	* 	Returns the category name for the passed article
	*
	* 	@param	$type_id		(int)	The article type identifier (Global, league, team or player)
	* 	@param	$var_id			(int)	Article Type value
	* 	@param	$articleLimit	(int)	OPTIONAL Record count limiter
	* 	@param	$excludeId		(int)	OPTIONAL ID to 
	* 	@return					(int)	Affected Row count
	*
	* 	@since	1.0
	*	@changelog	1.0.3 PROD		Expanded return values for new news->articles page
	*/
	public function getArticleType($article_id = false) {
		
		if ($article_id === false) $article_id = $this->id;
		
		if ($article_id == -1) return;

		$type = array();
		$this->db->select('type_id, newsType');
		$this->db->join('fantasy_news_type',$this->tblName.'.type_id = fantasy_news_type.id','left');
		$this->db->from($this->tblName);
		$this->db->where($this->tblName.'.id',$article_id);
		$query = $this->db->get();
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$type = $query->row_array();
		}
		$query->free_result();
		return $type;
	}

	/**
	* 	GET NEWS BY PARAMS.
	* 	This function call news articles. If a given article type, which can be global, league, player or team, 
	* 	is passed, it overrides the basic global type.
	*
	* 	@param	$type_id		(int)	The article type identifier (Global, league, team or player)
	* 	@param	$var_id			(int)	Article Type value
	* 	@param	$articleLimit	(int)	OPTIONAL Record count limiter
	* 	@param	$excludeId		(int)	OPTIONAL ID to 
	* 	@return					(int)	Affected Row count
	*
	* 	@since	1.0
	*	@changelog	1.0.3 PROD		Expanded return values for new news->articles page
	*/
	public function getNewsByParams($type_id = 1, $var_id = -1, $articleLimit = 1, $excludeId = false) {
		
		$news = array();
		$this->db->select($this->tblName.'.id, firstName, lastName, author_id, news_subject, news_body, fantasy_analysis, image, news_date, type_id, newsType, var_id');
		$this->db->join('users_meta',$this->tblName.'.author_id = users_meta.userId','left');
		$this->db->join('fantasy_news_type',$this->tblName.'.type_id = fantasy_news_type.id','left');
		$this->db->from($this->tblName);
		$this->db->where('type_id',$type_id);
		$this->db->where('var_id',$var_id);
		$this->db->order_by("id",'desc');
		if ($articleLimit > 0) {
			$this->db->limit($articleLimit);
		}
		$query = $this->db->get();
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if ($excludeId === false || ($excludeId !== false && $row->id != $excludeId)) {
					$authorName = '';
					if (isset($row->firstName) && !empty($row->firstName) && isset($row->lastName) && !empty($row->lastName)) {
						$authorName = $row->firstName." ".$row->lastName;
					} else {
						$authorName = "Unknown Author";
					}
					array_push($news,array('id'=>$row->id,'author_name'=>$authorName,'author_id'=>$row->author_id, 
														  'news_subject'=>$row->news_subject,'news_body'=>$row->news_body,
														  'fantasy_analysis'=>$row->fantasy_analysis,'image'=>$row->image,
														  'news_date'=>$row->news_date,'type_id'=>$row->type_id,'newsType'=>$row->newsType,
														  'var_id '=>$row->var_id));
				}
			}
		}
		return $news;
	}
	
	public function getRelatedArticles($article_id = false, $articleLimit = 10) {
		
		if ($article_id === false) $article_id = $this->id;
		
		if ($article_id == -1) return;
		
		// TEST FOR ARTICLES MATCHING BOTH TYPE AND VAR
		$news = $this->getNewsByParams($this->type_id, $this->var_id, $articleLimit, $article_id);
		
		if (isset($news) && sizeof($news) > 0) {
			return $news;
		} else {
			return array();
		}
	}
}  