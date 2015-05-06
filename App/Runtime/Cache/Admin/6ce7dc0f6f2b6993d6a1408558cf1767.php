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
		<h1>计费点管理</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/chargepoint');?>"
			class="current">计费点管理</a> <a href="javascript:;"
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
											<option value="">状态</option>
											<option value="1"<?php if($status==='1'){ echo 'selected';} ?>>启用</option>
											<option value="0"<?php if($status==='0'){ echo 'selected';} ?>>禁用</option>
									</select></td>
									<td class="searchselect"><select name="type">
											<option value="">计费点类型</option>
											<option value="0"<?php if($type==='0'){ echo 'selected';} ?> >游戏包</option>
											<option value="1"<?php if($type==='1'){ echo 'selected';} ?> >存档</option>
											<option value="2"<?php if($type==='2'){ echo 'selected';} ?> >虚拟币</option>
											<option value="3"<?php if($type==='3'){ echo 'selected';} ?> >单次游戏</option>
											<option value="4"<?php if($type==='4'){ echo 'selected';} ?> >街机投币</option>
											<option value="5"<?php if($type==='5'){ echo 'selected';} ?> >擂台赛</option>
									</select></td>
									<td style="padding-right: 0"><input type="text"
										name="name"
										value="<?php if($name){ echo $name;}else{ echo '计费点名称';} ?>"
										onBlur="blur_input(this,'计费点名称')"
										onClick="click_input(this,'计费点名称')"></td>
									<td style="padding-right: 0"><input type="text"
										name="startdate"
										value="<?php if($startdate){ echo $startdate;}else{echo '开启时间';} ?>"
										data-date-format="yyyy-mm-dd" class="datepicker"></td>
									<td>-</td>
									<td><input type="text" name="enddate"
										value="<?php if($enddate){ echo $enddate;}else{echo '结束时间';} ?>"
										data-date-format="yyyy-mm-dd" class="datepicker"></td>
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
						<h5>计费点管理</h5>
						 <a
							href="<?php echo U('Game/chargepoint_edit');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								新增计费点</span></a> 
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th>计费点ID</th>
										<th>计费点名称</th>
										<th>计费点类型</th>
										<th>云豆价格</th>
										<th>云贝价格</th>
										<th>G币价格</th>
										<th>创建时间</th>
										<th>更新时间</th>
										<th>状态</th>
										<th width="40" class="taskOptions">编辑</th>								
										<th width="40" class="taskOptions">删除</th>
										
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($chargepoint)): foreach($chargepoint as $key=>$row): ?><tr>
										<td><?php echo ($row["id"]); ?></td>
										<td><?php echo ($row["name"]); ?></td>
										<td><?php echo ($row["type_name"]); ?></td>
										<td>
											<?php if($row['bean']>=0){ echo $row['bean']; }else{ echo '<span class="red">不可购买</span>';} ?>
										</td>
										<td>
											<?php if($row['coin']>=0){ echo $row['coin']; }else{ echo '<span class="red">不可购买</span>';} ?>
										</td>
										<td>
											<?php if($row['gold']>=0){ echo $row['gold']; }else{ echo '<span class="red">不可购买</span>';} ?>
										</td>
										<td><?php if($row['create_time']): echo (date('Y-m-d
											H:i:s',$row["create_time"])); endif; ?></td>
										<td><?php if($row['update_time']): echo (date('Y-m-d
											H:i:s',$row["update_time"])); endif; ?></td>
								
										<td>
											<?php if($row['status']==1){ echo '<span class="green">正常</span>'; }else{ echo '<span class="red">异常</span>';} ?>
										</td>
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('chargepoint_edit',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-pencil"></i></a></td>
										
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:ConfirmDel('<?php echo U('Delete/chargepoint',array('id'=>$row['id']));?>');"
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
		$('.datepicker').datepicker();
	});
	$(document).ready(function(e) {
		tableresize(1600);
	});
	$(window).resize(function(e){
		tableresize(1600);
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