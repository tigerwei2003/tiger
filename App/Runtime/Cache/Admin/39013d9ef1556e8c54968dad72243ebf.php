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
		<h1>服务器管理</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/server');?>"
			class="current">服务器管理</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>



	<div class="container-fluid">

		<div class="alert alert-error">
			点击“新增服务器”“编辑服务器”“删除服务器”等按钮进行操作.<br> 1、一般来讲，单卡开20个GS；双卡开40个GS<br>
			2、total_cpu_capacity=1000是双E5-2630v2，以此为参：照单CPU一般是500，双E5-2650是1500<br>
			3、per_gpu_capacity=1000是单个K340GPU，以此为参照：单个K520的GPU是2000，单个760的CPU是2000。注意！！！和GPU的数目无关，只和单颗GPU的能力有关。<br>
			4、per_vpu_capacity=165888000是单个K340GPU的编码能力，720P*30FPS，以此为参照：单个K520GPU与之相同，单个760的GPU与之相同。<br>
			注意！！！和GPU的数目无关，只和单颗GPU的能力有关。 <a href="#" data-dismiss="alert"
				class="close">×</a>
		</div>

		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"> <i class="icon-th"></i>
						</span>
						<h5>服务器管理</h5>
						<a
							href="<?php echo U('Game/server_edit');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								新增服务器</span></a> 
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th width="50">ID</th>
										<th width="50">区域ID</th>
										<th width="50">状态</th>
										<th width="200">连接GSM的IP</th>
										<th width="200">本机IP</th>
										<th width="200">供客户端连接的IP</th>
										<th width="100">GS个数</th>
										<th width="100">CPU总容量</th>
										<th width="100">每个GPU容量</th>
										<th width="100">每个VPU容量</th>
										<th width="100">外网起始端口</th>
										<th width="100">GS起始端口</th>
										<th width="200">备注</th>
									
										<th width="40" class="taskOptions">编辑</th>
										
										
										<th width="40" class="taskOptions">删除</th>
										
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($Server)): foreach($Server as $key=>$row): ?><tr>
										<td><?php echo ($row["id"]); ?></td>
										<td><?php echo ($row["region_id"]); ?></td>
										<td><?php echo ($row["status"]); ?></td>
										<td><?php echo ($row["ip"]); ?></td>
										<td><?php echo ($row["nat_ip"]); ?></td>
										<td><?php echo ($row["wan_ip"]); ?></td>
										<td><?php echo ($row["gs_num"]); ?></td>
										<td><?php echo ($row["total_cpu_capacity"]); ?></td>
										<td><?php echo ($row["per_gpu_capacity"]); ?></td>
										<td><?php echo ($row["per_vpu_capacity"]); ?></td>
										<td><?php echo ($row["begin_wan_port"]); ?></td>
										<td><?php echo ($row["begin_gs_listening_port"]); ?></td>
										<td><?php echo ($row["note"]); ?></td>										
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('server_edit',array('id'=>$row['sid']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-pencil"></i></a></td>
										
										
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:ConfirmDel('<?php echo U('Delete/server',array('id'=>$row['sid']));?>');"
											class="tip-top" data-original-title="Delete" title="Delete"><i
												class="icon-remove"></i></a></td>										
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