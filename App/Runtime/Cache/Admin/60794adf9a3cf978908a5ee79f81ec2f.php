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
		<h1>客户端版本管理</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/clientver');?>"
			class="current">客户端版本管理</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>

	<div class="container-fluid">

		<div class="row-fluid">
			<div class="span12">
				<div class="sousuo">
					<form class="form-horizontal" action="" method="post"
						id="searchform">
						<input type="hidden" name="m" value="System"> <input
							type="hidden" name="a" value="clientver">
						<table rules="none" border="0">
							<tbody>
								<tr>
									<td style="padding-right: 0"><select name="product">
											<option value="">请选择</option>
											<option value="0"
												<?php if($product === '0'){echo "selected";} ?>
												>大厅
											</option>
											<option value="1"
												<?php if($product === '1'){echo "selected";} ?>
												>擂台
											</option>
									</select></td>
									<td style="padding-right: 0"><select name="client_type">
											<option value="">请选择</option>
											<option value="0"
												<?php if($client_type === '0'){echo "selected";} ?>
												>Android
											</option>
											<option value="1"
												<?php if($client_type === '1'){echo "selected";} ?>
												>Win32
											</option>
									</select></td>
									<td style="padding-right: 0"><input type="text" name="pid"
										value="<?php if($pid){ echo $pid;}else{ echo 'PID';} ?>"
										onBlur="blur_input(this,'PID')"
										onClick="click_input(this,'PID')"></td>
									<td style="padding-right: 0"><input type="text" name="ver"
										value="<?php if($ver){ echo $ver;}else{ echo '版本号';} ?>"
										onBlur="blur_input(this,'版本号')"
										onClick="click_input(this,'版本号')"></td>
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
						<h5>客户端版本管理</h5>
						 <a
							href="<?php echo U('Game/clientver_edit');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								新增客户端版本</span></a>
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th width="10">ID</th>
										<th width="50">产品类型</th>
										<th width="50">客户端类型</th>
										<th width="20">PID（渠道）</th>
										<th width="20">版本</th>
										<th width="40">版本名</th>
										<th width="40">升级</th>
										<th width="400">描述</th>
										<th width="300">下载URL</th>
										<th width="100">创建时间</th>
										<th width="100">更新时间</th>
										<th width="100">备注</th>
										
										<th width="40" class="taskOptions">编辑</th>
										
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($Clientver)): foreach($Clientver as $key=>$row): ?><tr>
										<td><?php echo ($row["id"]); ?></td>
										<td><?php if($row['product'] == '0'){echo '大厅';}elseif($row['product'] == '1'){echo '擂台';} ?></td>
										<td><?php if($row['client_type'] == '0'){echo 'Android';}elseif($row['client_type'] == '1'){echo 'Win32';} ?></td>
										<td><?php echo ($row["pid"]); ?></td>
										<td><?php echo ($row["ver"]); ?></td>
										<td><?php echo ($row["name"]); ?></td>
										<td><?php if($row['force_update'] == '0'){echo '<span
												class="black">内部升级</span>';}elseif($row['force_update'] == '1'){echo '<span class="black">正常升级</span>';}elseif($row['force_update'] == '2'){echo '<span class="red">强制升级</span>';} ?></td>
										<td><?php echo ($row["desc"]); ?></td>
										<td><?php echo ($row["url"]); ?></td>
										<td><?php echo (date('Y-m-d H:i:s',$row["create_time"])); ?></td>
										<td><?php echo (date('Y-m-d H:i:s',$row["update_time"])); ?></td>
										<td><?php echo ($row["note"]); ?></td>
										
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('clientver_edit',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-pencil"></i></a></td>
										
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
	tableresize(1400);
});
$(window).resize(function(e){
	tableresize(1400);
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