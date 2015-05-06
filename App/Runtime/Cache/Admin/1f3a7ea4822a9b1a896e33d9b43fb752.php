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
		<h1>存档列表</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/gamesave');?>"
			class="current">存档列表</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="sousuo">
					<form class="form-horizontal" action="" method="post"
						id="searchform">
						
						<table rules="none" border="0">
							<tbody>
								<tr>
									<td class="searchselect"><select name="status">
											<option value="">上传结果</option>
											<option value="1"<?php if($status==1){ echo 'selected';} ?>>已上传</option>
											<option value="2"<?php if($status==2){ echo 'selected';} ?>>未上传</option>
									</select></td>
									<td style="padding-right: 0"><input type="text"
										name="serialid"
										value="<?php if($serialid){ echo $serialid;}else{ echo '序列ID';} ?>"
										onBlur="blur_input(this,'序列ID')"
										onClick="click_input(this,'序列ID')"></td>
									<td style="padding-right: 0"><input type="text"
										name="saveid"
										value="<?php if($saveid){ echo $saveid;}else{ echo '存档ID';} ?>"
										onBlur="blur_input(this,'存档ID')"
										onClick="click_input(this,'存档ID')"></td>
									<td style="padding-right: 0"><input type="text"
										name="accountid"
										value="<?php if($accountid){ echo $accountid;}else{ echo '账户ID';} ?>"
										onBlur="blur_input(this,'账户ID')"
										onClick="click_input(this,'账户ID')"></td>
									<td style="padding-right: 0"><input type="text"
										name="deviceid"
										value="<?php if($deviceid){ echo $deviceid;}else{ echo '设备ID';} ?>"
										onBlur="blur_input(this,'设备ID')"
										onClick="click_input(this,'设备ID')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gameid"
										value="<?php if($gameid){ echo $gameid;}else{ echo '游戏ID';} ?>"
										onBlur="blur_input(this,'游戏ID')"
										onClick="click_input(this,'游戏ID')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gsid"
										value="<?php if($gsid){ echo $gsid;}else{ echo 'GS ID';} ?>"
										onBlur="blur_input(this,'GS ID')"
										onClick="click_input(this,'GS ID')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gsip"
										value="<?php if($gsip){ echo $gsip;}else{ echo 'GS IP';} ?>"
										onBlur="blur_input(this,'GS IP')"
										onClick="click_input(this,'GS IP')"></td>
									<td>
										<button class="btn btn-success"
											onClick="javascript:$('#searchform').submit();">
											<i class="icon-search icon-white"></i>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"> <i class="icon-th"></i>
						</span>
						<h5>存档列表</h5>
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th>序列ID</th>
										<th>序列名</th>
										<th>账户ID</th>
										<th>游戏ID</th>
										<th>游戏</th>
										<th>序列创建时间</th>
										<th>序列删除时间</th>
										<th>存档ID</th>
										<th>GS ID</th>
										<th>GS IP</th>
										<th>父存档</th>
										<th>存档创建时间</th>
										<th>设备ID</th>
										<th>上传时间</th>
										<th>累计游戏时间时间</th>
										<th>游戏模式</th>
										<th>GS汇报时间</th>
										<th>存档字节数</th>
										<th>存档MD5</th>
										<th>被继承次数</th>
										<th>存档删除时间</th>
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($gamesaves)): foreach($gamesaves as $key=>$row): ?><tr>
										<td><?php echo ($row["serial_id"]); ?></td>
										<td><?php echo ($row["name"]); ?></td>
										<td><?php echo ($row["account_id"]); ?></td>
										<td><?php echo ($row["game_id"]); ?></td>
										<td><?php echo ($row["game_name"]); ?></td>
										<td><?php echo ($row["create_time"]); ?></td>
										<td><?php echo ($row["delete_time"]); ?></td>
										<td><?php echo ($row["save_id"]); ?></td>
										<td><?php echo ($row["gs_id"]); ?></td>
										<td><?php echo ($row["gs_ip"]); ?></td>
										<td><?php echo ($row["derived_from"]); ?></td>
										<td><?php echo ($row["create_time_s"]); ?></td>
										<td><?php echo ($row["device_uuid"]); ?></td>
										<td><?php echo ($row["upload_time"]); ?></td>
										<td><?php echo ($row["total_play_time"]); ?></td>
										<td><?php echo ($row["game_mode"]); ?></td>
										<td><?php echo ($row["gs_report_time"]); ?></td>
										<td><?php echo ($row["compressed_size"]); ?></td>
										<td><?php echo ($row["compressed_md5"]); ?></td>
										<td><?php echo ($row["derived_count"]); ?></td>
										<td><?php echo ($row["delete_time_s"]); ?></td>
									</tr><?php endforeach; endif; ?>
								</tbody>
							</table>
						</div>
						<div class="pagination" style="text-align: center"><?php echo ($pages); ?></div>
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
		tableresize(2400);
	});
	$(window).resize(function(e) {
		tableresize(2400);
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