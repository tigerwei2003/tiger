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
		<h1>擂台列表</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Arena/index');?>"
			class="current">擂台列表</a> <a href="javascript:;"
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
									<td class="searchselect">
									<select name="status">
										<option value="">状态</option>
										<option value="1"<?php if($status==1){ echo 'selected';} ?>>启用</option>
										<option value="0"<?php if($status==0){ echo 'selected';} ?>>禁用</option>
									</select>
									</td>
									<td class="searchselect">
										<select name="game_id">
											<option value="">游戏</option>
											<?php if(is_array($gamelist)): $i = 0; $__LIST__ = $gamelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["game_id"]); ?>" <?php if($vo['game_id'] == $game_id){ echo 'selected';} ?> ><?php echo ($vo["game_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
										</select>
									</td>
									<td class="searchselect">
										<select name="region_id">
											<option value="">渠道</option>
											<?php if(is_array($regionlist)): $i = 0; $__LIST__ = $regionlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" <?php if($vo['id'] == $region_id){ echo 'selected';} ?>><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
										</select>
									</td>
									<td style="padding-right: 0"><input type="text" name="gs_id" value="<?php if($gs_id){ echo $gs_id;}else{ echo 'GSID';} ?>" onBlur="blur_input(this,'GSID')" onClick="click_input(this,'GSID')"></td>
									<td style="padding-right: 0"><input type="text" name="gs_ip" value="<?php if($gs_ip){ echo $gs_ip;}else{ echo 'GSIP';} ?>" onBlur="blur_input(this,'GSIP')" onClick="click_input(this,'GSIP')"></td>
									<td>
									<button class="btn btn-success"onClick="javascript:$('#searchform').submit();"><i class="icon-search icon-white"></i></button>
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
						<h5>擂台列表</h5>

						<a href="<?php echo U('Arena/arena_edit');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								新增擂台</span></a>

					</div>
					<div class="widget-content">
						<div class="cont_max" id="table_sort">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th width="50">ID</th>
										<th width="80">擂台名</th>
										<th width="240">所属游戏</th>
										<th width="50">擂台状态</th>
										<th width="50">最低级别</th>
										<th width="100">擂台图片</th>
										<th width="100">擂台参战人数</th>
										<th width="50">擂台排队人数</th>
										<th width="50">所属区域</th>
										<th width="50">gsd_id</th>
										<th width="100">gs_id</th>
										<th width="100">gs_ip</th>
										<th width="100">gs_port</th>
										<th width="100">gs_pid</th>
										<th width="100">gs_last_hb_time</th>
										<th width="100">开启时间</th>
										<th width="100">关闭时间</th>
										<th width="100">直播的URL</th>

										<th width="40" class="taskOptions">编辑</th>
										<th width="100">擂主列表</th>
										<th width="100">评论列表</th>
										<th width="100">排队列表</th>
										<th width="100">擂台实时数据</th>

									</tr>
								</thead>
								<tbody>
									<?php if(is_array($arenalist)): foreach($arenalist as $key=>$row): ?><tr>
										<td><?php echo ($row["id"]); ?></td>
										<td><?php echo ($row["arena_name"]); ?></td>
										<td><?php echo ($row["game_name"]); ?></td>
										<td><?php if($row['status']==1){echo "启用";}else{echo '关闭';} ?></td>
										<td><?php echo ($row["min_skill_level"]); ?></td>
										<td><?php echo ($row["arena_pic"]); ?></td>
										<td><?php echo ($row["max_player"]); ?></td>
										<td><?php echo ($row["max_queue_num"]); ?></td>
										<td><?php echo ($row["region_name"]); ?></td>
										<td><?php echo ($row["gsd_id"]); ?></td>
										<td><?php echo ($row["gs_id"]); ?></td>
										<td><?php echo ($row["gs_ip"]); ?></td>
										<td><?php echo ($row["gs_port"]); ?></td>
										<td><?php echo ($row["gs_pid"]); ?></td>
										<td><?php echo ($row["gs_last_hb_time"]); ?></td>
										<td><?php if($row['open_time'] != 0){echo $row['open_time'];} ?></td>
										<td><?php if($row['open_time'] != 0){echo $row['close_time'];} ?></td>
										<td><?php echo ($row["live_url"]); ?></td>

										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_edit',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-pencil"></i></a></td>
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_rank',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td>
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='#';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td>
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_queue',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td>
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_watcher',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td>

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