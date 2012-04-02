<?php           

class Email {
	
	public function getMail($searchType=array()) {
        $inbox = imap_open(EMAIL_HOST,EMAIL_USERNAME,EMAIL_PASSWORD) or $this->throwError(imap_last_error());
        if(empty($searchType)){//if we aren't given params we'll just get everything.
        	$emails=imap_search($inbox,'All');
        }else{
        	$first=reset($searchType);
        	//if we are given params we'll search them
        	while(isset($first)&&$first!=''){
        		//we go through all our parameters and filter the results.
    		    $value=$first;//put the pointer to the start and get the value and its key
    		    $key=key($searchType);
    		    if(isset($value)&&$value!=''){
    				$emails=imap_search($inbox,$key.' "'.$value.'"');//filter the emails
    			}else{
    				$emails=imap_search($inbox,$key);//some things might not need a value.
    			}
    			array_shift($searchType);//pop the param we just used off the array
    		}
    	}
    	imap_close($inbox);//close the inbox
    	return $emails;
    }

  	public function throwError($error){
  		//we can't directly throw an exception, so we call it indirectly
  		throw new Exception('Couldn\'t connect'.$error);
  	}
}

?>