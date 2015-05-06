<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<!-- container-fluid -->
<head>
<title><?php echo C('SEO_TITLE');?></title>
<meta charset="UTF-8" />
<meta
	content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"
	id="viewport" name="viewport">
<link rel="stylesheet" href="/gloudapi2/Public/static/css/bootstrap.min.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/jquery.gritter.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/unicorn.main.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/artDialog.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/unicorn.grey.css"
	class="skin-color" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/form.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/public.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/datepicker.css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="/gloudapi2/Public/static/js/jquery.min.js"></script>
<script src="/gloudapi2/Public/static/js/jquery.ui.custom.js"></script>
<script src="/gloudapi2/Public/static/js/bootstrap.min.js"></script>
<script src="/gloudapi2/Public/static/js/comm.js"></script>
<script src="/gloudapi2/Public/static/js/artDialog.js"></script>
<script src="/gloudapi2/Public/static/js/iframeTools.js"></script>
<script src="/gloudapi2/Public/static/js/formvalidator.js"></script>
<script src="/gloudapi2/Public/static/js/formvalidatorregex.js"></script>
<script src="/gloudapi2/Public/static/js/bootstrap-datepicker.js"></script>
<script src="/gloudapi2/Public/static/js/jquery.jUploader-1.0.min.js"
	type="text/javascript"></script>
<script src="/gloudapi2/Public/static/js/uploader.js" type="text/javascript"></script>
<style type="text/css">
.jUploader-button {
	background: url(/gloudapi2/Public/static/img/up.gif) no-repeat 0 0;
	height: 23px;
	width: 43px;
	border: 0;
	padding: 0px;
	margin: 0px;
	cursor: pointer;
}

.jUploader-button-hover {
	background-color: #111111;
	color: #fff;
}
</style>
</head>
<body>
	<div id="header">
		<h1>
			<a href="javascript:;"><?php echo ($nickname); ?></a>
		</h1>
	</div>
	<div id="user-nav" class="navbar navbar-inverse">
		<ul class="nav btn-group">
			<li class="btn btn-inverse"><a title=""
				href="<?php echo ($userinfo_edit_url); ?>"><i class="icon icon-user"></i> <span
					class="text"><?php echo ($username); ?></span></a></li>
			<li class="btn btn-inverse" style="*border-right: none;"><a
				title="注销" href="<?php echo ($logout_url); ?>"><i class="icon icon-share-alt"></i>
					<span class="text">注销</span></a></li>
		</ul>
	</div>
	<div id="sidebar">
		<a href="#" class="visible-phone"><i
			class="icon icon-align-justify"></i> 系统导航</a>
		<ul>

			<?php if(is_array($menu_parent_arr['parent'])): $i = 0; $__LIST__ = $menu_parent_arr['parent'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($node_info_header['parent_1_title'] == $vo['title']): ?><li class="submenu open active"><?php else: ?><li class="submenu" ><?php endif; ?> <a href="#"><i class="icon icon-qrcode"></i> <span><?php echo ($vo['title']); ?></span></a>
			<ul>
				<?php if(is_array($menu_parent_arr['child'][$vo['id']])): $i = 0; $__LIST__ = $menu_parent_arr['child'][$vo['id']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voo): $mod = ($i % 2 );++$i; if(($node_info_header['title'] == $voo['title']) OR ($node_info_header['parent_2_title'] == $voo['title'])): ?><li class="active"><?php else: ?><li><?php endif; ?> <a href="<?php echo U($voo['url']);?>"><i class="icon icon-ok-sign"></i> <span><?php echo ($voo["title"]); ?></span></a>
				</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
			<!-- </li> --><?php endforeach; endif; else: echo "" ;endif; ?>
		</ul>
	</div>

<link rel="stylesheet" href="/gloudapi2/Public/static/css/uniform.css" />
<div id="content">
	<div id="content-header">
		<h1>存档复制</h1>
	</div>
	<br>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a
			href="<?php echo U('Tools/copy_gamesave_show');?>" class="current">存档复制</a> <a
			href="javascript:;" onClick="javascript:history.go(-1);"
			class="tip-bottom" id="gotoback"><i class="icon-share-alt"></i>
			返回</a>
	</div>
	<br>

	<div class="widget-box">
		<div class="widget-title">
			<span class="icon"><i class="icon-pencil"></i></span>
			<h5>存档复制</h5>

		</div>
		<div class="widget-content nopadding">
			<form action="<?php echo U('Tools/copy_gamesave_sale');?>" method="post"
				class="dropzone dz-clickable" enctype="multipart/form-data"
				onsubmit="javascript:return checkForm()">
				<br>
				<div class="control-group chargepoint_gamepack"
					style="margin-left: 50px;">
					<label class="control-label game_save_id">请填入任意存档ID：</label>
					<div class="controls">
						<input type="text" name="save_id" id="save_id"
							onblur="check_saveid()" /><span style="display: none" id="s_id"></span>
					</div>
				</div>

				<div class="control-group account_id" style="margin-left: 50px;">
					<label class="control-label">请填入目标账户ID：</label>
					<div class="controls">
						<input type="text" name="account_id" id="account_id"
							onblur="check_actid()" /><span style="display: none" id="a_id"></span>
					</div>
				</div>


				<div class="form-actions">
					<input type="submit" value="复制" class="btn btn-primary" />
				</div>
			</form>
		</div>
	</div>




	<div class="row-fluid">
		<div id="footer" class="span12"><?php echo C('SEO_COPYRIGHT');?></div>
	</div>
</div>
</div>
<script src="/gloudapi2/Public/static/js/jquery.uniform.js"></script>
<script src="/gloudapi2/Public/static/js/unicorn.tables.js"></script>
<script src="/gloudapi2/Public/static/js/jquery.easyui.min.js"></script>
<script>
	var flag = 0;
	var intger = /^([1-9]\d+|[0-9])$/; //正整数验证
	function check_saveid() {
		var save_id = $('#save_id').val()//存档id
		if (save_id == "") {
			$('#s_id').attr("style", "display:block");
			$('#s_id').html("<font color='red'>请填入存档id</font>");
			flag = 0;
			return false;
		}
		if (!intger.test(save_id)) {
			$('#s_id').html("<font color='red'>请填入正确的数值！</font>");
			flag = 0;
			return false;
		}
		$.ajax({
			url : "/gloudapi2/op.php?m=Admin&c=Tools&a=check_saveid_exist",
			data : {
				"save_id" : save_id
			},
			type : "post",
			success : function(e) {
				{
					if (e == 404) {
						$('#s_id').attr("style", "display:block");
						$('#s_id').html(
								"<font color='red'>该存档id" + save_id
										+ "不存在</font>");
						flag = 0;
						$("#s_id").focus();
						return false;
					}
					$('#s_id').html("<font color='green'>√</font>");
					flag = 1;
					return true;
				}
			}
		})
	}
	var flag1 = 0;
	function check_actid() {
		var account_id = $('#account_id').val()//存档id
		if (account_id == "") {
			$('#a_id').attr("style", "display:block");
			$('#a_id').html("<font color='red'>请填入账户id</font>");
			flag1 = 0;
			return false;
		}
		if (!intger.test(account_id)) {
			$('#a_id').html("<font color='red'>请填入正确的数值！</font>");
			flag1 = 0;
			return false;
		}
		$.ajax({
			url : "/gloudapi2/op.php?m=Admin&c=Tools&a=check_actid_exist",
			data : {
				"account_id" : account_id
			},
			type : "post",
			success : function(e) {
				{
					if (e == 404) {
						$('#a_id').attr("style", "display:block");
						$('#a_id').html(
								"<font color='red'>该账户id" + account_id
										+ "不存在</font>");
						flag1 = 0;
						$("#a_id").focus();
						return false;
					}
					$('#a_id').html("<font color='green'>√</font>");
					flag1 = 1;
					return true;
				}
			}
		})
	}
	//表单验证
	function checkForm() {
		if (check_saveid() == false && check_actid() == false) {
			return false;
		}
		if (flag != 1) {
			return false;
		}
		if (flag1 != 1) {
			return false;
		}
		return true;

	}
</script>
<script type="text/javascript">
	$(document).ready(function(e) {
		tableresize(400);
	});
	$(window).resize(function(e) {
		tableresize(400);
	});
</script>

	</body>
</html>
<script src="/gloudapi2/Public/static/js/jquery.gritter.min.js"></script>
<script type="text/javascript">
function newmessage(){

	art.dialog({
		time: 2,
		lock: true,
		fixed: true,
		title: '提示',
		content: '此模块现在还正在开发中……',
		icon: 'warning'
	});
	
}

function artmessage(msg,type){
	art.dialog({
		time: 2,
		lock: true,
		fixed: true,
		title: '提示',
		content: msg,
		icon: type
	});
	
}

function showcontent(fileurl,filetitle){

	art.dialog.open(fileurl,{
		width:344,
		height:480,
		title:'预览',
		lock:true
	});
	
}
function ConfirmDel(url) {
	if (confirm("真的要删除吗？")){
		window.location.href=url;
	}else{
		return false;
	}
}
</script>