
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <link rel="stylesheet" href="<?php echo $baseUrl.'/themes/'.ACTIVE_THEME_HANDLE;?>/theme.css"/>
  <?php echo $headerItems;?>
</head>

<body>

  <div id="container">
  	<div id="inside">


     <h1><a href="<?php echo $baseUrl;?>"><?php echo SITE_NAME;?></a></h1><hr/>

<p>The post you requested can't be found. <a href="index.php">&laquo; Back Home</a></p>
</div>
<div id="sidebar">
	<?php echo $searchForm;?>
</div>
<hr/>
<div class="center">Powered by Kidney Cms.</div>
</div>
</body>
</html>