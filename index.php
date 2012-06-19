<?php
require 'config.php';
defined('KIDNEY_EXEC') or die('Not running kidney.');
ini_set("display_errors", 1);
if(!defined('EMAIL_USERNAME')){
	Header('location: install.php');
}
require 'framework.php';
$bf=New BlogFramework();
$bf->route();