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
<script type="text/javascript">
	$(document)
			.ready(
					function() {
						$.formValidator.initConfig({
							autotip : true,
							formid : "basic_validate",
							onerror : function(msg) {
							}
						});
						$("#name").formValidator({
							onshow : "请输入计费点名称",
							onfocus : "请输入计费点名称"
						}).inputValidator({
							empty : false,
							min : 4,
							onerror : "请输入计费点名称！"
						});
						$("#bean").formValidator({
							onshow : "请输入云豆价格,-1为不可购买，0为免费，切勿留空！",
							onfocus : "请输入云豆价格,-1为不可购买，0为免费，切勿留空！"
						}).inputValidator({
							empty : false,
							min : -1,
							onerror : "请输入云豆价格,-1为不可购买，0为免费，切勿留空！"
						});
						$("#coin").formValidator({
							onshow : "请输入云贝价格,-1为不可购买，0为免费，切勿留空！",
							onfocus : "请输入云贝价格,-1为不可购买，0为免费，切勿留空！"
						}).inputValidator({
							empty : false,
							min : -1,
							onerror : "请输入云贝价格,-1为不可购买，0为免费，切勿留空！"
						});
						$("#gold").formValidator({
							onshow : "请输入G币价格,-1为不可购买，0为免费，切勿留空！",
							onfocus : "请输入G币价格,-1为不可购买，0为免费，切勿留空！"
						}).inputValidator({
							empty : false,
							min : -1,
							onerror : "请输入G币价格,-1为不可购买，0为免费，切勿留空！"
						});
						$("#deadline_time_increase")
								.formValidator(
										{
											onshow : "一天是86400秒，一周是604800秒，一个月31天是2678400，半年183天是15811200，一年366天是31622400",
											onfocus : "一天是86400秒，一周是604800秒，一个月31天是2678400，半年183天是15811200，一年366天是31622400"
										}).inputValidator({
									min : 0,
									onerror : "最小值为0"
								});
						$("#deadline_time").formValidator({
							onshow : "截止到该年月日23:59:59",
							onfocus : "截止到该年月日23:59:59"
						});
					});
</script>
<link rel="stylesheet" href="/gloudapi2/Public/static/css/uniform.css" />
<link rel="stylesheet" href="/gloudapi2/Public/static/css/jquery-calendar.css" />
<div id="content">
	<div id="content-header">
		<h1>编辑计费点</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('System/chargepoint');?>"
			class="current">计费点管理</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"><i class="icon-pencil"></i></span>
						<h5>编辑计费点</h5>
						 <a
							href="<?php echo U('chargepoint');?>"><span
							class="label btn-primary"><i
								class=" icon-list-alt icon-white"></i> 计费点管理</span></a> 
					</div>
					<div class="widget-content nopadding">
						<form class="form-horizontal" method="post"
							action="<?php echo U('chargepoint_edit');?>" name="basic_validate"
							id="basic_validate" enctype="multipart/form-data" novalidate >
						<input type="hidden" name="dosubmit" value="1" /> <input
							type="hidden" name="editdate" value="<?php echo ($row["id"]); ?>" />

						<div class="control-group">
							<label class="control-label">计费点名称</label>
							<div class="controls">
								<input type="text" name="info[name]" value="<?php echo ($row["name"]); ?>"
									id="name" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">云豆价格</label>
							<div class="controls">
								<input type="text" name="info[bean]" value="<?php echo ($row["bean"]); ?>"
									id="bean" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">云贝价格</label>
							<div class="controls">
								<input type="text" name="info[coin]" value="<?php echo ($row["coin"]); ?>"
									id="coin" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">G币价格</label>
							<div class="controls">
								<input type="text" name="info[gold]" value="<?php echo ($row["gold"]); ?>"
									id="gold" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">计费点类型</label>
							<div class="controls">
								<select name="info[type]"
									onChange="javascript:selecttype(this);" id="chargepointtype"<?php if(!empty($row)){echo 'disabled="disabled"';} ?>>
									<option <?php if($row['type']==0){ echo 'selected'; } ?> value="0">游戏时间包
									<option <?php if($row['type']==1){ echo 'selected'; } ?> value="1">游戏存档
									<option <?php if($row['type']==2){ echo 'selected'; } ?> value="2">购买虚拟币
									<option <?php if($row['type']==3){ echo 'selected'; } ?> value="3">单次游戏
									<option <?php if($row['type']==4){ echo 'selected'; } ?> value="4">街机投币
									<option <?php if($row['type']==5){ echo 'selected'; } ?> value="5">擂台赛</select>
									<?php if(!empty($row)){echo '<input type="hidden" name="info[type]" value="'.$row['type'].'" />';} ?>
							</div>
						</div>
						<div class="control-group chargepoint_gamepack">
							<label class="control-label">游戏包</label>
							<div class="controls">
								<select name="pack[gamepack_id]">
									<?php if(is_array($gamepacklist)): foreach($gamepacklist as $key=>$packval): ?><option <?php
 if($rowpack['gamepack_id']==$packval['pack_id']){ echo 'selected';} ?>
										value="<?php echo ($packval["pack_id"]); ?>"><?php echo ($packval["pack_name"]); endforeach; endif; ?>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">状态</label>
							<div class="controls">
								<label style="display: inline !important;"><input
									type="radio" name="info[status]" value="1"
								<?php if($row['status']==1){echo 'checked';} ?>/>启用</label> <label
									style="display: inline !important;"><input type="radio"
									name="info[status]" value="0"
								<?php if($row['status']==0){echo 'checked';} ?>/>禁用</label>
							</div>
						</div>
						<!--
                                    <div class="control-group chargepoint_gamepack">
                                        <label class="control-label">增加游戏时间(秒)</label>
                                        <div class="controls">
                                            <input type="text" name="pack[left_seconds_increase]" value="<?php echo ($rowpack["left_seconds_increase"]); ?>" id="left_seconds_increase"/>
                                        </div>
                                    </div>
									-->
						<div class="control-group chargepoint_gamepack">
							<label class="control-label">延长截止时间</label>
							<div class="controls">
								<input type="text" name="pack[deadline_time_increase]"
									value="<?php echo ($rowpack["deadline_time_increase"]); ?>"
									id="deadline_time_increase" onblur="check_change(this);" />
							</div>
						</div>
						<div class="control-group chargepoint_gamepack">
							<label class="control-label">绝对截止日期</label>
							<div class="controls">
								<input type="text" id="deadline_time"
									onblur="check_change(this);" name="pack[deadline_time]"
									value="<?php if($rowpack['deadline_time']){ echo date('Y-m-d H:i',$rowpack['deadline_time']);} ?>"
									maxlength="10" onfocus="$(this).calendar()" />
							</div>
						</div>
						<div class="control-group chargepoint_gamesave"
							style="display: none;">
							<label class="control-label">请选择游戏：</label>
							<div class="controls">
								<select name="save[game_id]" id="game_id">
									<?php if(is_array($gamelist)): foreach($gamelist as $key=>$gameval): ?><option <?php
 if(isset($rowsave) && $rowsave['game_id']==$gameval['game_id']){echo 'selected';}?>
										value="<?php echo ($gameval['game_id']); ?>"><?php echo ($gameval['game_name']); ?></option><?php endforeach; endif; ?>
								</select>
							</div>
						</div>
						<div class="control-group chargepoint_gamesave"
							style="display: none;">
							<label class="control-label">游戏存档的名称</label>
							<div class="controls">
								<input type="text" name="save[filename]"
									value="<?php echo ($rowsave["filename"]); ?>" id="filename" />
							</div>
						</div>
						<div class="control-group chargepoint_gamesave" style="display:none;">
							<label class="control-label">用户购买时看到的名称</label>
							<div class="controls">
								<input type="text" name="save[name_for_user]" value="<?php echo ($rowsave["name_for_user"]); ?>" id="name_for_user"/>
							</div>
						</div>
						<div class="control-group chargepoint_gamesave" style="display:none;">
							<label class="control-label">用户购买时看到的介绍文字</label>
							<div class="controls">
								<div style="width:95%">
								<?php  echo editor('desc',$rowsave['desc']); ?>
								</div>
							</div>
						</div>
						<div class="control-group chargepoint_gamesave">
							<label class="control-label">请选择上传存档文件：</label>
							<div class="controls">
								<input type="file" name="upload" id="save_file" value="请选择文件"
									onblur="check_save_filename()" /><br> <span id='up_file'></span>
							</div>
						</div>
						<div class="control-group chargepoint_gamesave"
							style="display: none;">
							<label class="control-label">存档大小</label>
							<div class="controls">
								<input type="text" name="save[compressed_size]"
									value="<?php echo ($rowsave["compressed_size"]); ?>" id="compressed_size"
									readonly="readonly" />
							</div>
						</div>
						<div class="control-group chargepoint_gamesave"
							style="display: none;">
							<label class="control-label">存档MD5</label>
							<div class="controls">
								<input type="text" name="save[compressed_md5]"
									value="<?php echo ($rowsave["compressed_md5"]); ?>" id="compressed_md5"
									readonly="readonly" />
							</div>
						</div>

						<div class="control-group chargepoint_buycoin"
							style="display: none;">
							<label class="control-label">获得云豆</label>
							<div class="controls">
								<input type="text" name="coin[bean]" value="<?php echo ($rowcoin["bean"]); ?>"
									id="bean" />
							</div>
						</div>
						<div class="control-group chargepoint_buycoin"
							style="display: none;">
							<label class="control-label">获得云贝</label>
							<div class="controls">
								<input type="text" name="coin[coin]" value="<?php echo ($rowcoin["coin"]); ?>"
									id="coin" />
							</div>
						</div>
						<div class="control-group chargepoint_buycoin"
							style="display: none;">
							<label class="control-label">获得G币</label>
							<div class="controls">
								<input type="text" name="coin[gold]" value="<?php echo ($rowcoin["gold"]); ?>"
									id="gold" />
							</div>
						</div>
						<div class="control-group chargepoint_runonce">
							<label class="control-label">游戏</label>
							<div class="controls">
								<select name="runonce[game_id]">
									<?php if(is_array($consolegamelist)): foreach($consolegamelist as $key=>$gameval): ?><option <?php
 if(isset($rowrunonce) && $rowrunonce['game_id']==$gameval['game_id']){ echo 'selected';} ?>
										value="<?php echo ($gameval["game_id"]); ?>"><?php echo ($gameval["game_name"]); endforeach; endif; ?>
								</select>
							</div>
						</div>

						<div class="control-group chargepoint_arcade">
							<label class="control-label">游戏</label>
							<div class="controls">
								<select name="arcade[game_id]">
									<?php if(is_array($arcadegamelist)): foreach($arcadegamelist as $key=>$gameval): ?><option <?php
 if(isset($rowarcade) && ($rowarcade['game_id']==$gameval['game_id'])){ echo 'selected';} ?>
										value="<?php echo ($gameval["game_id"]); ?>"><?php echo ($gameval["game_name"]); endforeach; endif; ?>
								</select>
							</div>
						</div>

						<div class="control-group chargepoint_arena">
							<label class="control-label">擂台</label>
							<div class="controls">
								<select name="arena[arena_id]">
									<?php if(is_array($arenalist)): foreach($arenalist as $key=>$val): ?><option <?php if(isset($rowarena) && ($rowarena['arena_id']==$val['id'])){ echo 'selected';} ?> value="<?php echo ($val["id"]); ?>"><?php echo ($val["arena_name"]); endforeach; endif; ?>
								</select>
							</div>
						</div>

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
<script src="/gloudapi2/Public/static/js/jquery-calendar.js"></script>
<script>
	//验证存档文件类型	
	function check_save_filename() {
		var gamesave = $("#save_file").val();
		//文件类型  7z 格式
		var regfile = /^.+\.(7z|7Z)$/;
		if (gamesave == "") {
			$('#up_file').html("<font color='red'>请选择一个存档文件</font>");
			return false;
		}
		if (!regfile.test(gamesave)) {
			$('#up_file').html("<font color='red'>文件格式必须为.7z！</font>");
			return false;
		}
		$('#up_file').html("<font color='green'>√</font>");
		return true;
	}

	function selecttype(obj) {
		var type = $(obj).val();
		show_items(type);
	}

	function show_items(type) {
		if (type == 0) {
			$('.chargepoint_gamepack').show();
			$('.chargepoint_gamesave').hide();
			$('.chargepoint_buycoin').hide();
			$('.chargepoint_runonce').hide();
			$('.chargepoint_arcade').hide();
			$('.chargepoint_arena').hide();
		} else if (type == 1) {
			$('.chargepoint_gamepack').hide();
			$('.chargepoint_gamesave').show();
			$('.chargepoint_buycoin').hide();
			$('.chargepoint_runonce').hide();
			$('.chargepoint_arcade').hide();
			$('.chargepoint_arena').hide();
		} else if (type == 2) {
			$('.chargepoint_gamepack').hide();
			$('.chargepoint_gamesave').hide();
			$('.chargepoint_buycoin').show();
			$('.chargepoint_runonce').hide();
			$('.chargepoint_arcade').hide();
			$('.chargepoint_arena').hide();
		} else if (type == 3) {
			$('.chargepoint_gamepack').hide();
			$('.chargepoint_gamesave').hide();
			$('.chargepoint_buycoin').hide();
			$('.chargepoint_runonce').show();
			$('.chargepoint_arcade').hide();
			$('.chargepoint_arena').hide();
		} else if (type == 4) {
			$('.chargepoint_gamepack').hide();
			$('.chargepoint_gamesave').hide();
			$('.chargepoint_buycoin').hide();
			$('.chargepoint_runonce').hide();
			$('.chargepoint_arcade').show();
			$('.chargepoint_arena').hide();
		} else if (type == 5) {
			$('.chargepoint_gamepack').hide();
			$('.chargepoint_gamesave').hide();
			$('.chargepoint_buycoin').hide();
			$('.chargepoint_runonce').hide();
			$('.chargepoint_arcade').hide();
			$('.chargepoint_arena').show();
		}
	}

	$(document).ready(function(e) {
		var type = $('#chargepointtype').val();
		show_items(type);
	});
</script>
<script type="text/javascript">
	$(document).ready(function(e) {
		$('.datepicker').datepicker();
	});

	function check_change(obj) {
		if (obj.id == 'deadline_time_increase') {
			$("#deadline_time").val('');
		} else {
			$("#deadline_time_increase").val('');
		}
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