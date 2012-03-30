<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>

  <title><?php echo SITE_NAME;?></title>
  <link rel="stylesheet" href="<?php echo $baseUrl.'/themes/'.ACTIVE_THEME_HANDLE;?>/theme.css"/>
</head>

<body>

  <div id="container">
<div id="inside">

    <h1><a href="<?php echo $baseUrl;?>"><?php echo SITE_NAME;?></a></h1><hr/>


<div>
<?php 
foreach($finalMail as $mail){
?>
<div class="clearfix">
	<div style="width:50px;clear:both;float:left;margin-right:20px;">
	</div><div><a href="<?php echo $baseUrl;?>/index.php/<?php echo $searchName;?>/title/<?php echo $mail['title'];?>"><h2 style="margin-bottom:-10px;"><?php echo $mail['title']?></a></h2> 
	<p style="font-size:10px;"> posted by <?php echo $mail['poster'];?> on <?php echo date('M j,Y',$mail['date']);?>. <a href="<?php echo $baseUrl;?>/index.php/<?php echo $searchName;?>/title/<?php echo $mail['title'];?>#disqus_thread" data-disqus-identifier="<?php echo $mail['pID'];?>">Comments</a></p>
	<p class="small-description"><?php echo $mail['description']?> <a href="<?php echo $baseUrl;?>/index.php/<?php echo $searchName;?>/title/<?php echo $mail['title'];?>">Read More &raquo;</a></p></div></div><hr/>
<?php }?>
</div>
<?php echo $pagination;
if(isset($pagination)&&$pagination!=''){?>
<hr/>
<?php } ?>
<div class="center">Powered by <a href="http://writrapp.org">Writr</a>.</div>
</div>
</div>
</body>
</html>