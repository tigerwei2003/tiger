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
		<h1>存档上传</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Tools/upload_show');?>"
			class="current">存档上传</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"><i class="icon-pencil"></i></span>
						<h5>存档上传</h5>

					</div>
					<div class="widget-content nopadding">
						<form class="form-horizontal" method="post"
							action="<?php echo U('Tools/upload_gamesave_do');?>" name="basic_validate"
							id="basic_validate" enctype="multipart/form-data" novalidate
							onsubmit="javascript:return checkForm()" >
						<input type="hidden" name="gssubmit" value="1" />

						<div class="control-group account_account_id">
							<label class="control-label">请填入目标账户ID：</label>
							<div class="controls">
								<input type="text" name="account_id" id="accountid"
									onblur="check_account_id()" /> <br>
								<span id='a_id'></span>
							</div>
						</div>
						<div class="control-group game_save_serial_serial_id">
							<label class="control-label">请填入存档序列ID：</label>
							<div class="controls">
								<input type="text" name="serial_id" id="serial_id"
									onblur="check_serial_id()" /><br> <span id='serial'></span>
							</div>
						</div>
						<div class="control-group game_category" id="cate">
							<label class="control-label">请选择游戏类别：</label>
							<div class="controls">
								<select name="category" onchange="javascript:selectlist(this);"
									id="category" onblur="check_game_id()">
									<option value="1">主机/PC游戏</option>
									<option value="2">街机游戏</option>
									<option value="3">网络游戏</option>
								</select><br> <br>
								<span id='games'></span>
							</div>
						</div>

						<!---	<div class="controls">
                                            <input type="text" name="game_id" id="gameid" onblur="check_gameid()"/><br>
											<span id='g_id'></span>
                                        </div>  --->

						<div class="control-group game_save_name">
							<label class="control-label">请填入存档名称：</label>
							<div class="controls">
								<input type="text" name="save_name" id="savename"
									onblur="check_save_name()" /><br> <span id='names'></span>
							</div>
						</div>

						<div class="control-group game_save_compressed_md5">
							<label class="control-label">请选择一个存档文件：</label>
							<div class="controls">
								<input type="file" name="upload" id="files" value="请选择文件"
									onblur="check_files()" /><br> <span id='up_file'></span>
							</div>
						</div>
						<div class="form-actions">
							<input type="submit" value="上传" class="btn btn-primary" />
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
<script src="/gloudapi2/Public/static/js/jquery.easyui.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(e) {
		tableresize(400);
	});
	$(window).resize(function(e) {
		tableresize(400);
	});
</script>
<script type="text/javascript">
	//全局  正整数正则  
	var regactid = /^([1-9]\d+|[0-9]){0,11}$/;

	//开关
	var flag = 0;
	var flag1 = 0;
	var flag2 = 0;
	var flag3 = 0;
	var flag4 = 0;
	//验证账户id
	function check_account_id() {
		var account_id = $("#accountid").val();//账户id	
		if (account_id == "") {

			$('#a_id').html("<font color='red'>请填入目标账户id</font>");
			flag = 0;
			return false;
		}
		if (!regactid.test(account_id)) {
			$('#a_id').html("<font color='red'>请填入正确的数值！</font>");
			flag = 0;
			return false;
		}
		$.ajax({
			url : "<?php echo U('Tools/check_actid_exist');?>",
			data : {
				"account_id" : account_id
			},
			type : "post",
			success : function(e) {
				{
					if (e == 404) {
						$('#a_id').html(
								"<font color='red'>该账户id" + account_id
										+ "不存在</font>");
						flag = 0;
						$("#a_id").focus();
						return false;
					}
					$('#a_id').html("<font color='green'>√</font>");
					flag = 1;
					return true;
				}
			}
		})
	}

	//验证存档id	
	function check_serial_id() {

		var serial_id = $("#serial_id").val();//存档id	
		if (!regactid.test(serial_id)) {
			$('#serial').html("<font color='red'>请填入正确的数值！</font>");
			flag1 = 0;
			return false;
		}
		$('#serial').html("<font color='green'>√</font>");
		flag1 = 1;
		return true;
	}
	// 验证游戏id是否选择
	function check_game_id() {
		if ($("div").hasClass("control-group game_game_id")) {

			$('#games').html("<font color='green'>√</font>");
			flag2 = 1;
			return true;

		} else {

			$('#games').html("<font color='red'>请选择游戏类别下的游戏！</font>");
			flag2 = 0;
			return false;
		}
	}

	//获取游戏列表
	function selectlist() {
		var category = $('#category').val();
		$
				.ajax({
					url : "<?php echo U('Tools/game_list');?>",
					data : {
						"category" : category
					},
					type : "post",
					success : function(e) {
						{
							if (e == 404) {

								$('#games').html(
										"<font color='red'>该游戏分类为" + category
												+ "下的游戏为空或不存在！</font>");
								$("#game").remove();
								flag2 = 0;
								return false;
							}
							var o = eval('(' + e + ')');
							var str = "<div class='control-group game_game_id' id='game'><label class='control-label'>请选择游戏：</label><div class='controls'><select name='game_id'  id='game_id'>";
							for ( var a in o) {
								str += "<option value='"+o[a].game_id+"'>"
										+ o[a].game_name + "<\/option>";
							}
							$('#games').html("");
							str += "<\/select><\/div><\/div>";
							//alert(str);
							$("#cate").after(str);
							$("#game").next('#game').remove();
							flag2 = 1;
							return true;
						}
					}
				})
	}

	//验证存档名称
	function check_save_name() {
		var save_name = $('#savename').val();
		//存档名称 中文、数字、 字母、下划线  
		var regname = /^[\u4E00-\u9FA5A-Za-z0-9_]{0,16}$/img;
		if (!regname.test(save_name)) {
			$('#names')
					.html(
							"<font color='red'>存档名称只能为空或者中文、数字、字母、下划线组成,长度0-16位！</font>");
			flag3 = 0;
			return false;
		}
		$('#names').html("<font color='green'>√</font>");
		flag3 = 1;
		return true;
	}
	//验证存档文件类型	
	function check_files() {
		var gamesave = $("#files").val();
		//文件类型  7z 格式
		var regfile = /^.+\.(7z|7Z)$/;
		if (gamesave == "") {
			$('#up_file').html("<font color='red'>请选择一个存档文件</font>");
			flag4 = 0;
			return false;
		}
		if (!regfile.test(gamesave)) {
			$('#up_file').html("<font color='red'>文件格式必须为.7z！</font>");
			flag4 = 0;
			return false;
		}
		$('#up_file').html("<font color='green'>√</font>");
		flag4 = 1;
		return true;
	}
	//表单验证
	function checkForm() {
		if (check_account_id() == false && check_serial_id() == false
				&& check_game_id() == false && selectlist() == false
				&& check_save_name() == false && check_files() == false) {
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