<?php
ini_set("display_errors", 1);
require 'config.php';
if(!defined('EMAIL_USERNAME')){
	Header('location: install.php');
}
require 'framework.php';
$bf=New BlogFramework();
$bf->route();