<?php
Class BlogFramework{
	public $controllers='controllers';
	public $view='view';
	public $search='search';
	public $translatefrom=array(
		'date',
		'before',
		'after',
		'title',
		'keywords',
		'basic',
		'by'
	);
	public $translateto=array(
		'ON',
		'BEFORE',
		'SINCE',
		'SUBJECT',
		'KEYWORD',
		'BODY',
		'FROM'
	);
	public function route(){
		require 'special.php';
		require 'helper.php';
		$headerItems='<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script><script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script><link rel="stylesheet" href="jquery.ui.datepicker.css"/>';// add the css and js we need
		$baseUrl=$_SERVER['SCRIPT_NAME'];
		$baseUrl=explode('/', $baseUrl);
		foreach ($baseUrl as $key=>$part) {
			if($part=='index.php'){
				unset($baseUrl[$key]);//remove index.php from the path
			}
		}
		$baseUrl=implode('/', $baseUrl);
		$path=$this->stripPath();
		//renumber all the values so we can get them easier.
		$path=array_values($path);
		ob_start();
		require 'search.php';
		$searchForm=ob_get_contents();
		ob_end_clean();
		// get the search form
		require 'config.php';
		if(empty($path)){
			$finalParams=array();// no search params
		}elseif($path[0]==$this->search){
			//we have a special task to do, like sorting by a tag
			//we'll hand it off to the email parser
			array_shift($path);
			$keys=array();
			$values=array();
			$i=0;
			foreach($path as $option){//we need to get what things are the commands and what are the parameters
				//odds are params
				//evens are commands
				if($i % 2){//we have an odd (remainder)
					$values[]=$option;
				}else{//even
					$keys[]=$option;
				}
				$i++;
			}
			if(!empty($keys)&&!empty($values)){
				$keys=str_replace($this->translatefrom, $this->translateto, $keys);//replace all the commands with their imap_search equivalents
				$finalParams=array_combine($keys, $values);//we got them in the same array
			}
		}else{
			//its a single post that we need to route
			if(isset($path[0])){
				$finalParams=array('SUBJECT'=>$path[0]);
			}else{
				//homepage with no search params.
				$finalParams=array();
			}
		}
		$searchName=$this->search;
		$inbox = imap_open(EMAIL_HOST,EMAIL_USERNAME,EMAIL_PASSWORD) or $this->throwError(imap_last_error());
        if(empty($finalParams)){//if we aren't given params we'll just get everything.
        	$emails=imap_search($inbox,'All');
        }else{
        	$count=count($finalParams);
        	//if we are given params we'll search them
        	$i=0;
        	while($count>$i){
        		$first=reset($finalParams);
        		//we go through all our parameters and filter the results.
    		    $value=strip_tags($first);//put the pointer to the start and get the value and its key
    		    $value=filter_var($string, FILTER_SANITIZE_STRING);
    		    $key=key($finalParams);
    		    if(in_array($key, $this->translateto)){
    			    if(isset($value)&&$value!=''){
    					$emails=imap_search($inbox,$key.' "'.urldecode($value).'"');//filter the emails
    				}else{
    					$emails=imap_search($inbox,$key);//some things might not need a value.
    				}
    			}//
    			array_shift($finalParams);//pop the param we just used off the array
    			$i++;
    		}
    	}
    	if(!is_array($emails)){
    		require 'themes/'.ACTIVE_THEME_HANDLE.'/page_not_found.php';
    		die;
    	}
    	rsort($emails);
		require 'markdown.php';
		if(sizeof($emails)>1||isset($_GET['page'])){
			$finalMail=array();
			require 'pagination.php';
			if(!isset($_GET['page'])){
				$_GET['page']=0;
			}
			$up = new Pagination($emails,$_GET['page'],'index.php','page');
			$list=$up->getList();
			$nav=$up->generateLinks();
			//get our pagination stuff
			foreach($list as $mail_number){
				$mailHeader=imap_fetch_overview($inbox, $mail_number);
				$body = imap_fetchbody($inbox,$mail_number,2);
				$struct = imap_fetchstructure($inbox,$mail_number);
				if(!isset($body)||$body==''){
					$body = imap_fetchbody($inbox,$mail_number,1);
				}
				if($struct->encoding!=0){
					$body=quoted_printable_decode($body);
				}
				$title=$mailHeader[0]->subject;
				if(STRIP_EMAIL==1){
					$title=str_replace(EMAIL_USERNAME, '', $title);
					$body=str_replace(EMAIL_USERNAME, '', $body);
				}
				$body=strip_tags($body,'<a><p><div><b><i><img><span><br>');
				$body=Markdown($body);
				//mark it down!
				$description=Helper::shortenTextWord($body,DESCRIPTION_LENGTH);
				$date=strtotime($mailHeader[0]->date);
				$from=$mailHeader[0]->from;
				$pID=$mailHeader[0]->uid;
				$marked=$mailHeader[0]->flagged;
				$finalMail[]=array(
					'description'=>$description,
					'body'=>$body,
					'date'=>$date,
					'poster'=>$from,
					'title'=>$title,
					'pID'=>$pID,
					'marked'=>$marked
				);
				//get the data into a format that makes more sense for designers
			}
			require 'themes/'.ACTIVE_THEME_HANDLE.'/list.php';
			//if we have a list  we load the list template
		}elseif(sizeof($emails)<1){
			require 'themes/'.ACTIVE_THEME_HANDLE.'/page_not_found.php';
			//no items yet load page not found.
			//we don't have to give em any more info
		}else{	
			$mail_number=$emails[0];
			$mailHeader=imap_fetch_overview($inbox, $mail_number);
			$struct = imap_fetchstructure($inbox,$mail_number);
			$body = imap_fetchbody($inbox,$mail_number,2);
			if(!isset($body)||$body==''){
				$body = imap_fetchbody($inbox,$mail_number,1);
			}
			if($struct->encoding!=0){
				$body=quoted_printable_decode($body);
			}
			$title=$mailHeader[0]->subject;
			if(STRIP_EMAIL==1){
				$title=str_replace(EMAIL_USERNAME, '', $title);
				$body=str_replace(EMAIL_USERNAME, '', $body);
			}
			$body=strip_tags($body,'<a><p><div><b><i><img><span><br>');
			$body=Markdown($body);
			//mark it down!
			$description=Helper::shortenTextWord($body,155);
			$keywords=Helper::generateKeywords($body,$title);
			$date=$mailHeader[0]->date;
			$from=$mailHeader[0]->from;
			$pID=$mailHeader[0]->uid;
			$marked=$mailHeader[0]->flagged;
			$finalMail=array(
				'description'=>$description,
				'body'=>$body,
				'date'=>$date,
				'poster'=>$from,
				'title'=>$title,
				'pID'=>$pID,
				'keywords'=>$keywords,
				'marked'=>$marked
			);
			//get the data into a format that makes more sense for designers
			$post=(object) $finalMail;
			//we turn it into an object so it looks prettier when you're making the theme. 
			//harder for designers to screw up the code too, which is key
			require 'themes/'.ACTIVE_THEME_HANDLE.'/post.php';
			//otherwise load a single post
		}
	}
	public function stripPath(){
		$requestURI = explode('/', $_SERVER['REQUEST_URI']);
		$script = explode('/',$_SERVER['SCRIPT_NAME']);
		$path=array_diff($requestURI, $script);
		foreach($path as $key=>$part){
			if(strpos($part,'?page')){//search for the page get
				unset($path[$key]);
			}
		}
		//get the path beyond the base app url.
		return $path;
	}
	public function throwError($error){
  		//we can't directly throw an exception, so we call it indirectly
  		throw new Exception('Couldn\'t connect to gmail: '.$error);
  	}
}