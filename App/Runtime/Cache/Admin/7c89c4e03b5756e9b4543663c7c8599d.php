<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>用户登陆</title>
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
            <form id="loginform" method="post" class="form-vertical" action="<?php echo ($login_url); ?>" />
            <input type="hidden" name="dosubmit" value="1" />
				<p>&nbsp;</p>
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-user"></i></span><input type="text" name="username" placeholder="用户名" />
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-lock"></i></span><input type="password" name="password" placeholder="密码" />
                        </div>
                    </div>
                </div>
                <div class="form-actions" style="border-bottom-right-radius:1.5em;border-bottom-left-radius:1.5em;">
                    <span class="pull-left"><a href="javascript:;" class="flip-link" id="to-recover">忘记密码?</a></span>
                    <span class="pull-right"><input type="submit" class="btn btn-inverse" value="登录" /></span>
                </div>
            </form>
            <form id="recoverform" method="post" action="/gloudapi2/op.php?m=Index&a=backpassword" class="form-vertical" />
            <input type="hidden" name="dosubmit" value="1" />
				<p>请输入您的注册时的邮箱地址</p>
				<div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-envelope"></i></span><input type="text" name="email" placeholder="邮箱地址" />
                        </div>
                    </div>
                </div>
                <div class="form-actions" style="margin-top:47px;border-bottom-right-radius:1.5em;border-bottom-left-radius:1.5em;">
                    <span class="pull-left"><a href="#" class="flip-link" id="to-login">返回登录</a></span>
                    <span class="pull-right"><input type="submit" class="btn btn-inverse" value="找回" /></span>
                </div>
            </form>
        </div>
        
        <script src="/gloudapi2/Public/static/js/jquery.min.js"></script>  
        <script src="/gloudapi2/Public/static/js/unicorn.login.js"></script> 
    </body>
</html>