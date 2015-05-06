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
		<h1>游戏包管理</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Game/gamepack');?>"
			class="current">游戏包管理</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span6" id="gamepack">
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"> <i class="icon-th-list"></i>
						</span>
						<h5>游戏包列表</h5>
						 <a
							href="<?php echo U('Game/gamepack_edit');?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								增加游戏包</span></a> 
						<a href="javascript:void(0);"> <span id="save_game_pack"
							class="label btn-success"><i class="icon-file icon-white"></i>保存排序结果</span></a>
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<form action="" name="game_pack" method="post">
								<table class="table table-bordered table-striped with-check"
									id="maxtable">
									<thead>
										<tr>
											<th width="80">游戏包ID</th>
											<th width="120">游戏包名称</th>
											<th width="90">创建时间</th>
											<th width="90">包含个数</th>
											<th width="40">状态</th>										
											<th width="40" class="taskOptions">游戏</th>																						
											<th width="40" class="taskOptions">编辑</th>																						
											<th width="40" class="taskOptions">删除</th>											
										</tr>
									</thead>
									<tbody>
										<input id="game_pack_id" name="pack_id" type="hidden" value="" />
										<?php if(is_array($gamepack)): foreach($gamepack as $key=>$row): ?><tr
											<?php if($id==$row['pack_id']){ echo 'style="background:#ddd;"';} ?> class="drag-item">
											<td><?php echo ($row["pack_id"]); ?></td>
											<td><?php echo ($row["pack_name"]); ?></td>
											<td><?php echo (date('Y-m-d',$row["create_time"])); ?></td>
                                            <td><?php echo ($row["game_count"]); ?></td>
                                            <td><?php if($row['status']==1){ echo '<span class="green">启用</span>'; }else{ echo '<span class="red">禁用</span>';} ?></td> 
										
											<td class="taskOptions"><a href="javascript:;"
												onMouseDown="javascript:window.location.href='<?php echo U('gamepack',array('id'=>$row['pack_id']));?>';"
												class="tip-top" data-original-title="Games" title="Games"><i
													class="icon-list-alt"></i></a></td>																						
											<td class="taskOptions"><a href="javascript:;"
												onMouseDown="javascript:window.location.href='<?php echo U('gamepack_edit',array('id'=>$row['pack_id']));?>';"
												class="tip-top" data-original-title="Update" title="Update"><i
													class="icon-pencil"></i></a></td>																					
											<td class="taskOptions"><a href="javascript:;"
												onMouseDown="javascript:ConfirmDel('<?php echo U('Delete/gamepack',array('id'=>$row['pack_id']));?>');"
												class="tip-top" data-original-title="Delete" title="Delete"><i
													class="icon-remove"></i></a></td>											
										</tr><?php endforeach; endif; ?>
									</tbody>
								</table>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="span6" id="games_pack_name">
				<form name="games_pack_name_list" action="" method="post" >
				<input name="id" type="hidden" value="<?php echo ($id); ?>" />
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"> <i class="icon-th-list"></i>
						</span>
						<h5>
							<font class="blue"><?php echo ($games["pack_name"]); ?></font> | 游戏列表
						</h5>
						<a
							href="<?php echo U('gamepack_game_add',array('id'=>$games['pack_id']));?>"><span
							class="label btn-success"><i class="icon-plus icon-white"></i>
								增加游戏</span></a> <a href="javascript:void(0);"> <span
							id="save_game_list" class="label btn-success"><i
								class="icon-file icon-white"></i>保存排序结果</span></a>
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th>游戏包</th>
										<th>游戏</th>
										<th>游戏等级</th>
										
										<th width="40" class="taskOptions">删除</th>
										
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($gamepack_game)): foreach($gamepack_game as $key=>$gm): ?><tr class="drag-item-list-name">
										<input name="game_pack_list[]" type="hidden"
											value="<?php echo ($gm["game_id"]); ?>">
										<td><?php echo ($gm["pack_name"]); ?></td>
										<td><?php echo ($gm["game_name"]); ?></td>
										<td><?php echo ($gm["level"]); ?></td>
										
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:ConfirmDel('<?php echo U('Delete/gamepack_game',array('id'=>$gm['id']));?>');"
											class="tip-top" data-original-title="Delete" title="Delete"><i
												class="icon-remove"></i></a></td>
										
									</tr><?php endforeach; endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				</form>
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
$(window).resize(function(e){
	tableresize(400);
});
</script>

<script>
$(function(){
		$(".taskOptions").mousedown(function(){
			return false;
		});
	 	$('.drag-item').draggable({
			revert:true,
			deltaX:null,
			deltaY:null,
				
		}).droppable({
			accept:'.drag-item',
			onDragEnter:function(){
				},
			onDrop:function(e,source){
					$(source).insertAfter(this);
			}
		});
		$('.drag-item-list-name').draggable({
			revert:true,
			deltaX:0,
			deltaY:0
		}).droppable({
			accept:'.drag-item-list-name',
			onDrop:function(e,source){
			$(source).insertAfter(this);
			}
		});	 
		$("#save_game_pack").click(function(){
			if(!confirm("确定排序是你要的吗？")){
				return false;
				}
			get_data();
			$("form:first").submit();
			});
		$("#save_game_list").click(function(){
			if(!confirm("确定排序是你要的吗？")){
				return false;
				}
			$("form:eq(1)").submit();
			});

	//获取排序后的gamepack id
	function get_data(){
		var keys = new Array();
		var gamepack_id =null;
		$("#gamepack").find("tr[class='drag-item droppable']").each(function(index, domEle){
			var key= null;
		    key = $(domEle).find("td:first").text();
		    keys.push(key);
			});
			function get_pack_id(){
				var gamepack_id = keys.join();
				return gamepack_id;
				}
			$("#game_pack_id").val(get_pack_id());
		}
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