<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name="description" content="<?php echo $post->description;?>">
  <meta name="keywords" content="<?php echo $post->keywords;?>">
  <title><?php echo $post->title;?></title>
  <link rel="stylesheet" href="<?php echo $baseUrl.'/themes/'.ACTIVE_THEME_HANDLE;?>/theme.css"/>
  <?php echo $headerItems;?>
</head>

<body>

  <div id="container">
    <div class="clearfix">
  <div id="inside">


     <h1><a href="<?php echo $baseUrl;?>"><?php echo SITE_NAME;?></a></h1><hr/>

<div>
<?php 
echo $post->body;
?>
<div id="disqus_thread"></div>
<script type="text/javascript">
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
</div>
<hr/>
<div class="center">Powered by Balloon3.</div>
</div>
<div id="sidebar">
  <?php echo $searchForm;?>
</div>
</div>
</div>
</body>
</html>