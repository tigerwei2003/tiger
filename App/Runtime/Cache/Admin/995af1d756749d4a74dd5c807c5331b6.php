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
		<h1>生成兑换券</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Code/exchange');?>"
			class="current">特殊卡管理</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"><i class="icon-pencil"></i></span>
						<h5>编辑兑换券</h5>

						<a href="<?php echo U('Code/exchange');?>"><span
							class="label btn-primary"><i
								class=" icon-list-alt icon-white"></i> 特殊卡管理</span></a>

					</div>
					<div class="widget-content nopadding">
						<form class="form-horizontal" method="post"
							action="<?php echo U('Code/exchange_edit');?>"
							onsubmit="return check_data();" name="basic_validate"
							id="basic_validate" enctype="multipart/form-data" novalidate>
							<input type="hidden" name="dosubmit" value="1" /> <input
								type="hidden" name="type_mark" id="type_mark"
								value="<?php echo ($row["type_mark"]); ?>" />
							<div class="control-group">
								<label class="control-label">类别</label>
								<div class="controls">
									<select name="info[type_id]" id="type_id"
										onchange="type_change();">
										<option value="">选择类别</option>
										<?php if(is_array($typelist)): foreach($typelist as $key=>$v): ?><option value="<?php echo ($v["type_id"]); ?>"<?php
 if($row['type_id']==$v['type_id']){ echo 'selected';}?>><?php echo ($v["type_name"]); ?></option><?php endforeach; endif; ?>
									</select>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">渠道：</label>
								<div class="controls">
									<select name="info[pid]" id="pid">
										<option value="">请选择渠道</option>
										<?php if(is_array($dealerlist)): foreach($dealerlist as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>"<?php
 if($v['id']==$row['pid']) echo 'selected'; ?>
											><?php echo ($v["dealer_name"]); ?></option><?php endforeach; endif; ?>
									</select>
								</div>
							</div>
							<?php if(!$row): ?><div class="control-group">
								<label class="control-label">批次</label>
								<div class="controls">
									<input type="text" name="card_id_prefix" id="card_id_prefix"
										style="float: left;" />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">生成数量</label>
								<div class="controls">
									<input type="text" name="codenum" id="codenum" />
								</div>
							</div><?php endif; ?>
							<?php if($row): ?><div class="control-group">
								<label class="control-label">兑换券卡号</label>
								<div class="controls">
									<input type="text" value="<?php echo ($row["card_id"]); ?>" readonly />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">兑换券密码</label>
								<div class="controls">
									<input type="text" value="<?php echo ($row["card_pass"]); ?>" readonly />
								</div>
							</div><?php endif; ?>
							<div class="form-actions">
								<input type="submit" value="保存" class="btn btn-primary" />
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div id="footer" class="span12"><?php echo C('SEO_COPYRIGHT');?></div>
		</div>
	</div>
</div>



<script src="/gloudapi2/Public/static/js/jquery.uniform.js"></script>
<script src="/gloudapi2/Public/static/js/unicorn.tables.js"></script>
<script type="text/javascript">
	$(document).ready(function(e) {
		$('.datepicker').datepicker();
	});

	$(document).ready(function() {
		$.formValidator.initConfig({
			autotip : true,
			formid : "basic_validate",
			onerror : function(msg) {
			}
		});
		$("#codenum").formValidator({
			onshow : "请输入生成数量",
			onfocus : "请输入生成数量"
		}).inputValidator({
			min : 1,
			onerror : "请输入生成数量!"
		});
		$("#card_id_prefix").formValidator({
			onshow : "请输入批次",
			onfocus : "请输入批次"
		}).inputValidator({
			min : 6,
			max : 6,
			onerror : "请输入批次(默认6位)!"
		});
	});

	function type_change() {
		var type_id = $("#type_id").val();
		if (type_id != '0') {
			var url = "<?php echo U('Code/exchange_type_mark');?>";
			$.post(url, {
				"type_id" : type_id
			}, function(data) {
				if (data == '1') {
					$("#codenum").attr("readonly", "1");
					$("#codenum").val("1");
				} else {
					$("#codenum").attr("readonly", false);
					$("#codenum").val("2");
				}
				$("#type_mark").val(data);
			});
		}
	}

	function check_data() {
		var type_id = $("#type_id").val();
		var pid = $("#pid").val();
		if (type_id == '' || pid == '') {
			alert("请选择类型或者渠道!");
			return false;
		}
		return true;
	}
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