<?php
defined('KIDNEY_EXEC') or die('Not running kidney.');
Class BlogFramework{
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
		/*
			Start general change code
			this section deals with setting up various variables that we will use later
			sets scripts, base url, interprets the route
		*/
		require 'helper.php';
		require 'events.php';
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
		$path=Events::run('path_determined',$path);
		ob_start();
		require 'search.php';
		$searchForm=ob_get_contents();
		ob_end_clean();
		$searchForm=Events::run('search_form',$searchForm);
		// get the search form
		
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
		$finalParams=Events::run('params',$finalParams,$path);
		$searchName=$this->search;
		$inbox = imap_open(EMAIL_HOST,EMAIL_USERNAME,EMAIL_PASSWORD) or $this->throwError(imap_last_error());
		$inbox=Events::run('imap_open',$inbox);
		//////////////////////////////////////////////// End General //////////////////////////////////////////////////////////
		//if(!isset($_COOKIE['checkSettingChange'])){		this doesn't work because I don't know how to use the NOT syntax
		/*
			Start settings change code
			this section deals with emails that try to change a setting
			you can use 3 command via email- list, set, get 
			Subject always has to be kidney Settings
			list shows all the constants except email/pass (list password:pass)
			set sets any constant (set CONSTANT password:pass)
			get shows one constant except email/pass (get CONSTANT password:pass)
		*/
		if(defined(ALLOWED_EMAILS)){
			$addresses=explode(',', ALLOWED_EMAILS);
			$searchString='OR';
			foreach ($emails as $address) {
				$searchString.=' FROM "'.trim($address).'"';
			}
			$settingsEmail=imap_search($inbox,'SUBJECT "Kidney Settings" '.$searchString);//check if we have and settings emails
		}else{
			$settingsEmail=imap_search($inbox,'SUBJECT "Kidney Settings"');
		}
		if(!empty($settingsEmail)){
			$settingsEmail=Events::run('settings_change',$settingsEmail);
			//wow! They want to change a setting! How exciting!
			foreach($settingsEmail as $setting){
				$body = imap_fetchbody($inbox,$setting,2);
				$struct = imap_fetchstructure($inbox,$setting);
				$mailHeader=imap_fetch_overview($inbox, $setting);
				if(!isset($body)||$body==''){
					$body = imap_fetchbody($inbox,$setting,1);
				}
				if($struct->encoding!=0){
					$body=quoted_printable_decode($body);
				}
				$body=trim($body);
				$words=explode(' ', $body);
				$f=$words[0];//get the first word
				$pass=end(explode('password:', $body));//get the password
				if($pass!=EMAIL_PASSWORD){
					$from=$mailHeader[0]->from;
					mail($from,'Kidney Defined Settings','You supplied an invalid password.');//send it out
				}else{//don't let em in unless they know the password
					switch ($f) {//various commands the first word can be
						case 'list':
							$setting=Events::run('list',$setting);
							$messagelist=get_defined_constants(true);// get the list, we need to email the sender
							$messagelist=$messagelist['user'];
							unset($messagelist['EMAIL_PASSWORD']);
							unset($messagelist['EMAIL_USERNAME']);
							$message='';
							foreach($messagelist as $key=>$value){
								$message.=$key.' = '.$value."\n";
							}
							$from=$mailHeader[0]->from;

							mail($from,'Kidney Defined Settings',$message);//send it out
							break;
						case 'set':
							$setting=Events::run('set',$setting);
							$body=str_replace($f, '', $body);
							$body=str_replace('password:', '', $body);
							$body=trim(str_replace($pass, '', $body));
							$key=explode('=', $body);
							if(defined($key[0])){
								$contents=file_get_contents('config.php');
								$contents=preg_replace('#(.*)define\(.'.trim($key[0]).'.,..*.\)\;(.*)#','$1define(\''.trim($key[0]).'\','.trim($key[1]).');$2', $contents);
								//regex... i probably wont be able to read this in a minute, much less a year...
								// so ive sorta given up commenting it
								file_put_contents('config.php', $contents);
							}else{
								file_put_contents('config.php', "define('".trim($key[0])."','".trim($key[1])."');\n", FILE_APPEND);//add the define
							}
							break;
						case 'get':
							$setting=Events::run('get',$setting);
							$body=trim(str_replace($f, '', $body));
							$body=str_replace('password:', '', $body);
							$body=trim(str_replace($pass, '', $body));
							if(defined($body)){
								if($body=='EMAIL_PASSWORD'||$body=='EMAIL_USERNAME'){
									$message='Sorry, but you can\'t get your password or email address from the get or list command. Please open the file up via ftp to find these. You can change them with the set command via email.';//no hacking
								}else{
									$message=$body.' = '.constant($body);	
								}
							}else{
								$message='We couldn\'t find that constant. Try emailing the command "list" to get all the available constants and their values.';//give em some help
							}
							$from=$mailHeader[0]->from;
							mail($from,'Kidney Defined Constant',$message);//send it out
							break;
					}
				}
				imap_delete($inbox, $setting);
			}
			imap_expunge($inbox);
		}
		//////////////////////////////////////////////// End Settings Change //////////////////////////////////////////////////////////
		//}
		/*
			Start mail code
			this section gets the mail and translates the params to a search string
		*/
        if(empty($finalParams)){//if we aren't given params we'll just get everything.
        	if(defined('ALLOWED_EMAILS')){
        		if(strpos(ALLOWED_EMAILS, ',')){
					$addresses=explode(',', ALLOWED_EMAILS);
					$searchString='';
					foreach ($emails as $address) {
						$searchString.=' FROM "'.$address.'"';
					}
				}else{
					$searchString=' FROM "'.ALLOWED_EMAILS.'"';
				}
				$emails=imap_search($inbox,$searchString);
			}
        	
        }else{
        	$searchString='';
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
    					$searchString.=' '.$key.' "'.urldecode($value).'"';//filter the emails
    				}else{
    					$searchString.=' '.$key;
    				}
    			}//
    			array_shift($finalParams);//pop the param we just used off the array
    			$i++;
    		}
    	}
    	$searchString=Events::run('search',$searchString);
    	$emails=imap_search($inbox, $searchString);
    	$emails=Events::run('emails_gotten',$emails);
    	//////////////////////////////////////////////// End Email Get //////////////////////////////////////////////////////////
    	/*
			Start theme code
			this section gets the array of messages and translates them into the frontend
			gets the theme, feeds it the data
			also does a bit of logic to determine the template we want, and translate the message into markdown
		*/
    	if(!is_array($emails)||sizeof($emails)<1){
    		Events::runSilent('page_not_found',$path);
    		$fourohfourpath='themes/default/page_not_found.php';
    		if(file_exists('themes/'.ACTIVE_THEME_HANDLE.'/page_not_found.php')){
    			$fourohfourpath='themes/'.ACTIVE_THEME_HANDLE.'/page_not_found.php';
    		}
    		require $fourohfourpath;
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
			$list=Events::run('pagination_listed',$list,$nav);
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
				$title=str_replace(EMAIL_PASSWORD, '', $title);
				$body=str_replace(EMAIL_PASSWORD, '', $body);
				$body=strip_tags($body,'<a><p><div><b><i><img><span><br>');
				$body=Markdown($body);
				//mark it down!
				$description=Helper::shortenTextWord($body,DESCRIPTION_LENGTH);
				$date=strtotime($mailHeader[0]->date);
				$from=strip_tags($mailHeader[0]->from);
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
			$finalMail=Events::run('final_list',$finalMail);
			require 'themes/'.ACTIVE_THEME_HANDLE.'/list.php';
			//if we have a list  we load the list template
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
			$finalMail=Events::run('final',$finalMail);
			//get the data into a format that makes more sense for designers
			//we want it in an object and nto just a variable so that nothing gets overriden by accident
			$post=(object) $finalMail;
			//we turn it into an object so it looks prettier when you're making the theme. 
			//harder for designers to screw up the code too, which is key
			require 'themes/'.ACTIVE_THEME_HANDLE.'/post.php';
			//otherwise load a single post
		}
		//////////////////////////////////////////////// End theme interpreter //////////////////////////////////////////////////////////
	}
	public function stripPath(){
		$requestURI = explode('/', $_SERVER['REQUEST_URI']);
		$script = explode('/',$_SERVER['SCRIPT_NAME']);
		$path=array_diff($requestURI, $script);//get the diff (whats before REQUEST_URI)
		foreach($path as $key=>$part){
			if(strpos($part,'?page')){//search for the page get
				unset($path[$key]);
			}
		}
		//get the path beyond the base app url.
		return $path;
	}
	public function throwError($error){
		$error=Events::run('error',$error);
  		//we can't directly throw an exception, so we call it indirectly
  		throw new Exception('Couldn\'t connect to email: '.$error);
  	}
}