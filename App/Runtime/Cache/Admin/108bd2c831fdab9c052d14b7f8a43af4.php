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
		<h1>游戏列表</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/index');?>"
			class="current">游戏列表</a> <a href="javascript:;"
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
											<option value="1"<?php if($status==1){ echo 'selected';} ?>>正常</option>
											<option value="2"<?php if($status==2){ echo 'selected';} ?>>下线</option>
									</select></td>
									<td class="searchselect"><select name="type">
											<option value="">类型</option>
											<option value="1"<?php if($type==1){ echo 'selected';} ?> >主机游戏</option>
											<option value="2"<?php if($type==2){ echo 'selected';} ?>>街机游戏</option>
											<option value="3"<?php if($type==3){ echo 'selected';} ?>>网络游戏</option>
									</select></td>
									<td style="padding-right: 0"><input type="text"
										name="gamename"
										value="<?php if($gamename){ echo $gamename;}else{ echo '游戏名称';} ?>"
										onBlur="blur_input(this,'游戏名称')"
										onClick="click_input(this,'游戏名称')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gameid"
										value="<?php if($gameid){ echo $gameid;}else{ echo '游戏ID';} ?>"
										onBlur="blur_input(this,'游戏ID')"
										onClick="click_input(this,'游戏ID')"></td>
									<td class="searchselect"><select name="save_enabled">
											<option value="">存档支持</option>
											<option value="1"<?php if($save_enabled==1){ echo 'selected';} ?>>不支持</option>
											<option value="2"<?php if($save_enabled==2){ echo 'selected';} ?>>支持</option>
									</select></td>
									<td style="padding-right: 0"><input type="text"
										name="gamelevelmin"
										value="<?php if($gamelevelmin){ echo $gamelevelmin;}else{ echo '级别大于';} ?>"
										onBlur="blur_input(this,'级别大于')"
										onClick="click_input(this,'级别大于')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gamelevelmax"
										value="<?php if($gamelevelmax){ echo $gamelevelmax;}else{ echo '级别小于';} ?>"
										onBlur="blur_input(this,'级别小于')"
										onClick="click_input(this,'级别小于')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gameplayermin"
										value="<?php if($gameplayermin){ echo $gameplayermin;}else{ echo '支持人数大于';} ?>"
										onBlur="blur_input(this,'支持人数大于')"
										onClick="click_input(this,'支持人数大于')"></td>
									<td style="padding-right: 0"><input type="text"
										name="gameplayermax"
										value="<?php if($gameplayermax){ echo $gameplayermax;}else{ echo '支持人数小于';} ?>"
										onBlur="blur_input(this,'支持人数小于')"
										onClick="click_input(this,'支持人数小于')"></td>
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
						<h5>游戏列表</h5>
					 <a
							href="<?php echo U('Game/add');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								新增游戏</span></a> 
					</div>
					<div class="widget-content">
						<div class="cont_max" id="table_sort">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th width="50">ID</th>
										<th width="80">类别</th>
										<th width="240">游戏名称</th>
										<th width="50">状态</th>
										<th width="50"
											title="1001-适配有问题；&#013;1000-录入新游戏；&#013;900-显示和操控没问题；&#013;800-服务器没问题；&#013;700-运营素材准备完毕；&#013;600-部署完毕；&#013;500-测试完毕；&#013;100-计划上线（可以让玩家看到了）；&#013;99-即将上线（玩家可以进入，看游戏的截图、视频，甚至可以开始预订游戏了）；&#013;1-90备用，可做考量用户等级的限制；&#013;0级，正常上线游戏">级别</th>
										<th width="100">试玩时间</th>
										<th width="100">存档支持</th>
										<th width="50">流畅</th>
										<th width="50">标清</th>
										<th width="50">高清</th>
										<th width="100">多人</th>
										<th width="100">cpu负载</th>
										<th width="100">gpu负载</th>
									
										<th width="40" class="taskOptions">编辑</th>
									
										
										<th width="40" class="taskOptions">删除</th>
									
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($Games)): foreach($Games as $key=>$row): ?><tr>
										<td><?php echo ($row["game_id"]); ?></td>
										<td>
											<?php echo $game_type["$row[category]"];?>
										</td>
										<td><?php echo ($row["game_name"]); ?></td>
										<td>
											<?php echo $row['status']?'<span class="green">正常</span>':'<span class="red">下线</span>' ?>
										</td>
										<td>
											<?php  if ($row['level']>800) echo '<span class="red">'.$row['level'].'</span>'; else if ($row['level']>100) echo '<span class="yellow">'.$row['level'].'</span>'; else echo '<span class="green">'.$row['level'].'</span>'; ?>
										</td>
										<td><?php echo ($row["trial_time"]); ?></td>
										<td>
											<?php echo $row['save_enabled']?'<span class="green">支持</span>':'<span class="red">不支持</span>' ?>
										</td>
										<td><?php echo ($row["low_bitrate"]); ?></td>
										<td><?php echo ($row["mid_bitrate"]); ?></td>
										<td><?php echo ($row["high_bitrate"]); ?></td>
										<td><?php echo ($row["max_player"]); ?></td>
										<td><?php echo ($row["cpu_load"]); ?></td>
										<td><?php echo ($row["gpu_load"]); ?></td>
									
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Game/add',array('id'=>$row['game_id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-pencil"></i></a></td>
									
										
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:ConfirmDel('<?php echo U('Delete/games',array('id'=>$row['game_id']));?>');"
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
	function create_sort_obj(i,request_url,field){
		var sort_obj = {};
		sort_obj.click_num = <?php echo ($click_num); ?>;
		var from_php_field = "<?php echo ($field); ?>";
		sort_obj.click_event_obj=$("#table_sort tr:first th").get(i);
		$(sort_obj.click_event_obj).click(function(){
			var index = $(this).index();
			var c_num = (++sort_obj.click_num);
			if(from_php_field!=field){
				c_num=1;
				}
			var field_val = field;
			var status_val ="<?php echo ($status); ?>";
			var type_val = "<?php echo ($type); ?>";
			var gamename_val = "<?php echo ($gamename); ?>";
			var gameid_val = "<?php echo ($gameid); ?>";
			var save_enabled_val = "<?php echo ($save_enabled); ?>";
			var gamelevelmin_val = "<?php echo ($gamelevelmin); ?>";
			var gamelevelmax_val = "<?php echo ($gamelevelmax); ?>";
			var url = request_url+"&field="+field_val+"&click_num="+c_num;
			url+=+"&status="+status_val+"&type="+type_val+"&gamename="+gamename_val+"&gameid="+gameid_val+"&save_enabled="+save_enabled_val+"&gamelevelmin="+gamelevelmin_val+"&gamelevelmax="+gamelevelmax_val;
		    window.location.href=url;
			});
		}
	
	var fields = ["game_id","category","game_name","status","level","coin","save_enabled","low_bitrate","mid_bitrate","high_bitrate","max_player","cpu_load","gpu_load","uploader"];
	var request_url="op.php?m=System&a=games";
	for(i=0;i<fields.length;i++){
		var field = fields[i];
		create_sort_obj(i,request_url,field);
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