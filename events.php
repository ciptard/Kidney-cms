<?php           
defined('KIDNEY_EXEC') or die('Not running kidney.');
class Events {
	// info for making your own events
    // first, make a folder of the event you want to run. 
    // So if you want to run the event path_determined you'd make a folder called path_determined in the event folder
    // make a new php file in it, name it anything- I'll name it jacks_class.php
    // If i wanted to make this highpriority- be run first- i'd name it 1jacks_clas.php or jacks_class1.php 
    // see natsort for more info
    // The classname will be EventsPathDeterminedJacksClass1
    // in it you need the run function.
    // The first arg you accept will be the principal, which will be what you modify
    // everything else is secondary
    public function run() {
        $args=func_get_args();
        $name=$args[0];
        unset($args[0]);
        $args=array_values($args);
        $pristine=$argsl
        $d="$baseUrl/events/$name/";
        if(is_dir($d)){//its a valid event and has files to use
            //get all event files with a .php extension.
            $events = glob($d . "*.php");
            $events=natsort($events);//sort them by 1,2,3,4 etc. This is the priority
            foreach($events as $event){
                include $d.'/'.$event;
                $n=str_replace('.php', '', $event);
                $n=Helper::camelcase($n);
                $name=Helper::camelcase($name);
                $class='Events'.$name.$n;//this should output something like EventsOnPageRunJacksClass
                $return=call_user_func_array(array($class,'run'),$args);//call it!
                if(isset($return)&&$return!=''){
                    //return will be the principal argument, so args[0]
                    //we don't want to return this immediately though, so we just replace it
                    $args[0]=$return;
                }
            }
        }
        //so we've run the event now... we just need to return our principal value
        return $args[0];
    }
    public function runSilent() {//this doesn't allow you to change anything
        $args=func_get_args();
        $name=$args[0];
        unset($args[0]);
        $args=array_values($args);
        $pristine=$argsl
        $d="$baseUrl/events/$name/";
        if(is_dir($d)){//its a valid event and has files to use
            //get all event files with a .php extension.
            $events = glob($d . "*.php");
            $events=natsort($events);//sort them by 1,2,3,4 etc. This is the priority
            foreach($events as $event){
                include $d.'/'.$event;
                $n=str_replace('.php', '', $event);
                $n=Helper::camelcase($n);
                $name=Helper::camelcase($name);
                $class='Events'.$name.$n;//this should output something like EventsOnPageRunJacksClass
                call_user_func_array(array($class,'run'),$args);//call it!
            }
        }
    }
}

?>