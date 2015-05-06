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
		<h1>硬件信息列表</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/hardware');?>"
			class="current">硬件信息列表</a> <a href="javascript:;"
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
									<td class="searchselect"><select name="hardware">
											<option value="">硬件</option>
											<?php if(is_array($hardwareoption)): $i = 0; $__LIST__ = $hardwareoption;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><option value="<?php echo ($data['hardware']); ?>"
												<?php if($data['hardware']==$hardware){echo 'selected';} ?>
												><?php echo ($data['hardware']); ?>
											</option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select></td>
									<td class="searchselect"><select name="product">
											<option value="">产品</option>
											<?php if(is_array($productoption)): $i = 0; $__LIST__ = $productoption;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><option value="<?php echo ($data['product']); ?>"
												<?php if($data['product']==$product){echo 'selected';} ?>
												><?php echo ($data['product']); ?>
											</option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select>
									<td class="searchselect"><select name="model">
											<option value="">型号</option>
											<?php if(is_array($modeloption)): $i = 0; $__LIST__ = $modeloption;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><option value="<?php echo ($data['model']); ?>"
												<?php if($data['model']==$model){echo 'selected';} ?>
												><?php echo ($data['model']); ?>
											</option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select>
									<td class="searchselect"><select name="manu">
											<option value="">制造商</option>
											<?php if(is_array($manuoption)): $i = 0; $__LIST__ = $manuoption;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><option value="<?php echo ($data['manu']); ?>"
												<?php if($data['manu']==$manu){echo 'selected';} ?>
												><?php echo ($data['manu']); ?>
											</option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select>
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
						<h5>硬件信息列表</h5>
						<a href="<?php echo U('Export/hardware_ex');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								导出硬件信息</span></a> <a href="<?php echo U('Game/hardware_edit');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								新增硬件信息</span></a>
						<!--<?php if(rolemenu('Export/hardware')): ?><a href="<?php echo U('Export/hardware');?>"><span class="label btn-success"><i class="icon-plus icon-white"></i> 导入硬件信息</span></a><?php endif; ?>-->
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead id="table_sort">
									<tr>
										<th width="50">ID</th>
										<th width="80">类别</th>
										<th width="240">硬件</th>
										<th width="50">产品</th>
										<th width="50">型号</th>
										<th width="100">制造商</th>
										<th width="100">名称</th>
										<th width="40" class="taskOptions">编辑</th>
										<th width="40" class="taskOptions">删除</th>
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($list)): foreach($list as $key=>$row): ?><tr>
										<td><?php echo ($row["id"]); ?></td>
										<td><?php if($row['type']==0){echo '未知';}elseif($row['type']==1){echo '手机';}elseif($row['type']==2){echo 'Pad';}elseif($row['type']==3){echo '盒子';}elseif($row['type']==4){echo 'TV';}elseif($row['type']==5){echo '掌机';} ?></td>
										<td><?php echo ($row["hardware"]); ?></td>
										<td><?php echo ($row["product"]); ?></td>
										<td><?php echo ($row["model"]); ?></td>
										<td><?php echo ($row["manu"]); ?></td>
										<td><?php echo ($row["device_name"]); ?></td>

										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('hardware_edit',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-pencil"></i></a></td>


										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:ConfirmDel('<?php echo U('Delete/hardware',array('id'=>$row['id']));?>');"
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
	$("#table_sort tr:first").click(function(){
		//alert($(this).text());
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