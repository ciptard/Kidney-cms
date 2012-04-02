<?php 
require 'config.php';
function check_imap(){
	ob_start();
	imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', 'emailwritrtest@gmail.com', 'writrtest');
	$c=ob_get_contents();
	ob_end_clean();
	if(isset($c)){
		return false;
	}
	return true;
}
if(defined('EMAIL_USERNAME')){
	Header('location: index.php');
}
if(!empty($_POST)){
	foreach($_POST as $post){
		if(!$post||$post==""){
			$error=1;
		}
	}
	if(!isset($error)){
		$configuration .= "define('EMAIL_USERNAME', '" . addslashes($_POST['user']) . "');\n";
		$configuration .= "define('EMAIL_PASSWORD', '" . addslashes($_POST['pass']) . "');\n";
		$configuration .= "define('SITE_NAME', '" . addslashes($_POST['site']) . "');\n";
		//update the config file
		file_put_contents('config.php', $configuration,FILE_APPEND);
		header('Location: index.php');
		exit;
	}else{
		header('Location: install.php?error='.$error);
	}
}
if (version_compare(PHP_VERSION, '5.2.0', '>')) {
	$phpClass='success';
	$phpMessage='Success! Your php version is greater than 5.2.';
}else{ 
	$phpClass='error';
	$phpMessage='Uh Oh! Your php version is less than 5.2. Please upgrade it before continuing.';
	$class='error';
	$message='Not all required services available.';
}
if(function_exists('imap_open')&&function_exists('imap_search')&&check_imap()==true){
	$msClass='success';
	$msMessage='Success! You have Imap enabled.';
}else{
	$msClass='error';
	$msMessage='Uh oh! You don\'t have Imap configured correctly. Please contact your host to enable it before continuing.';
	$class='error';
	$message='Not all required services available.';
}
if(!isset($class)){
	$class='success';
}
if(!isset($message)){
	$message='All required services available.';
}
?>
<html>
<head>
	<title>Install | Balloon3</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	<link rel="stylesheet" href="install.css"/>
</head>
<body class="install">
	<form method="post" class="form-stacked"action="install.php">
	<?php if(isset($_GET['error'])){
			 echo '<br/><span class="alert-message error">Please fill out all fields.</span><br/><br/>';
	}?>
		<h1 class="center heading">Balloon3</h1>
		<hr/>
		<h2 class="center heading">Required Items
		<a class="hide-show-tests pointer"onclick="$('#tests').fadeIn('fast');$('.hide-show-tests').toggle();">+</a>
		<a class="hide-show-tests pointer hide" onclick="$('#tests').fadeOut('fast');$('.hide-show-tests').toggle();">-</a>
		</h2>
		<span class="alert-message hide-show-tests <?php echo $class;?>"><?php echo $message;?></span>
		<div id="tests" class="hide">
		<h3>Php Version
		<a class="php-hide pointer hide"onclick="$('#php-version').fadeIn('fast');$('.php-hide').toggle();">+</a></h3>
		<div id="php-version" class="alert-message <?php echo $phpClass;?>">
        	<a class="close" onclick="$('#php-version').fadeOut('slow');$('.php-hide').toggle();">&times;</a>
        	<p><?php echo $phpMessage;?></p>
      	</div>
      	<h3>Imap Connectivity
      	<a class="ms-hide pointer hide"onclick="$('.ms-hide').toggle();$('#ms-enabled').fadeIn('fast');">+</a></h3>
		<div id="ms-enabled" class="alert-message <?php echo $msClass;?>">
        	<a class="close" onclick="$('#ms-enabled').fadeOut('slow');$('.ms-hide').toggle();">&times;</a>
        	<p><?php echo $msMessage;?></p>
      	</div>
		</div>
		<br/><br/>
		<hr/>
		<h2 class="center heading">Settings</h2>
		<label for="site_name">Site Name</label>
		<input type="text" class="xlarge" placeholder="My Awesome Site" name="site"/>
		<label for="email">Email Address</label>
		<input type="email" class="xlarge" placeholder="email@email.com" name="user"/>
		<label for="pass">Email Address Password</label>
		<input type="password" id="user-password" placeholder="password" class="xlarge" name="pass"/><br/><br/>
		<span class="help-block">
		<a href="javascript:void(0);" class="show-hide center" onclick="document.getElementById('user-password').type='text';$('.show-hide').toggle();">Reveal Password</a>
		<a href="javascript:void(0);" class="show-hide center hide" onclick="document.getElementById('user-password').type='password';$('.show-hide').toggle();">Hide Password</a>
		</span>
		<hr/>
		<?php if($class!='success'){ 
			$d='disabled';
		}?>
		<button type="submit" <?php echo $d;?> class="btn large full success">Install</button>
	</form>
</body>
</html>