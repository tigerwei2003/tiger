<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>系统提示</title>
		<meta charset="UTF-8" />
        <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" id="viewport" name="viewport">
		<link rel="stylesheet" href="/gloudapi2/Public/static/css/bootstrap.min.css" />
		<link rel="stylesheet" href="/gloudapi2/Public/static/css/bootstrap-responsive.min.css" />
        <link rel="stylesheet" href="/gloudapi2/Public/static/css/unicorn.login.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
    <body>
        <div id="logo">
            <img src="/gloudapi2/Public/static/img/logo.png" alt="" />
        </div>
        <div id="loginbox">   
				<div class="showmessage">
                	<div class="s_img"><img src="/gloudapi2/Public/static/img/icons/error.png"></div>
                    <div class="s_font"><?php echo($error); ?></div>
                </div>
                <div class="form-actions">
                    <span class="pull-left">页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b></span>
                    <span class="pull-right"><input type="button" onClick="javascript:window.location.href='<?php echo($jumpUrl); ?>';" class="btn btn-inverse"  value="跳转" /></span>
                </div>
        </div>
        
        <script src="/gloudapi2/Public/static/js/jquery.min.js"></script>  
        <script src="/gloudapi2/Public/static/js/unicorn.login.js"></script> 
        <script type="text/javascript">
		(function(){
			var wait = document.getElementById('wait'),href = document.getElementById('href').href;
			var interval = setInterval(function(){
				var time = --wait.innerHTML;
				if(time <= 0) {
					location.href = href;
					clearInterval(interval);
				};
			}, 1000);
		})();
		</script>
    </body>
</html>