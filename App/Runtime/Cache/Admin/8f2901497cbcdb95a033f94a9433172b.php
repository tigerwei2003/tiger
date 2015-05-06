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
		<h1>战斗列表</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo U('Arena/arena_battle');?>"
			class="current">战斗列表</a> <a href="javascript:;"
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
									<td class="searchselect"><select name="arena_id">
											<option value="">擂台</option>
											<?php if(is_array($arenalist)): $i = 0; $__LIST__ = $arenalist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['id'] == $arena_id): ?><option value="<?php echo ($vo["id"]); ?>"  selected><?php echo ($vo["arena_name"]); ?></option>
											<?php else: ?>
											<option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["arena_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
									</select></td>
									<td class="searchselect"><select name="game_id">
											<option value="">游戏</option>
											<?php if(is_array($gamelist)): $i = 0; $__LIST__ = $gamelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['game_id'] == $game_id): ?><option value="<?php echo ($vo["game_id"]); ?>" selected><?php echo ($vo["game_name"]); ?></option>
											<?php else: ?>
											<option value="<?php echo ($vo["game_id"]); ?>"><?php echo ($vo["game_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
									</select></td>
									<td style="padding-right: 0"><input type="text"
										name="player"
										value="<?php if($player){ echo $player;}else{ echo 'PLAYER';} ?>"
										onBlur="blur_input(this,'PLAYER')"
										onClick="click_input(this,'PLAYER')"></td>
									<td style="padding-right: 0"><input type="text" name="id"
										value="<?php if($id){ echo $id;}else{ echo 'BATTLEID';} ?>"
										onBlur="blur_input(this,'BATTLEID')"
										onClick="click_input(this,'BATTLEID')"></td>
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
						<h5>战斗列表</h5>
					</div>
					<div class="widget-content">
						<div class="cont_max" id="table_sort">
							<table class="table table-bordered table-striped with-check"
								id="maxtable">
								<thead>
									<tr>
										<th width="50">ID</th>
										<th width="80">擂台ID</th>
										<th width="240">游戏ID</th>
										<th width="50">player1</th>
										<th width="50">player2</th>
										<th width="100">name1</th>
										<th width="100">name2</th>
										<th width="50">score1</th>
										<th width="50">score2</th>
										<th width="50">support1</th>
										<th width="50">support2</th>
										<th width="50">status1</th>
										<th width="100">status2</th>
										<th width="100">active_time1</th>
										<th width="100">active_time2</th>
										<th width="100">phase</th>
										  <th width="100">是否送礼</th>
										<th width="100">start_time</th>
										<th width="100">end_time</th>
										<th width="100">create_time</th>
										<!-- <th width="100">排队列表</th> -->
										<th width="100">支持列表</th>
									<!-- 	<th width="100">评论列表</th>
										<th width="100">擂主列表</th> -->
									</tr>
								</thead>
								<tbody>
									<?php if(is_array($battlelist)): foreach($battlelist as $key=>$row): ?><tr>
										<td><?php echo ($row["id"]); ?></td>
										<td><?php echo ($row["arena_name"]); ?></td>
										<td><?php echo ($row["game_name"]); ?></td>
										<td><?php echo ($row["player1"]); ?></td>
										<td><?php echo ($row["player2"]); ?></td>
										<td><?php echo ($row["name1"]); ?></td>
										<td><?php echo ($row["name2"]); ?></td>
										<td><?php echo ($row["score1"]); ?></td>
										<td><?php echo ($row["score2"]); ?></td>
										<td><?php echo ($row["support1"]); ?></td>
										<td><?php echo ($row["support2"]); ?></td>
										<td><?php if($row['status1']=='-1'){echo "空缺";}elseif($row['status1']=='0'){echo '在线';}elseif($row['status1']=='2'){echo '离开';}elseif($row['status1']=='5'){echo '断线';}else{ echo '异常状态';} ?></td>
										<td><?php if($row['status2']=='-1'){echo "空缺";}elseif($row['status2']=='0'){echo '在线';}elseif($row['status2']=='2'){echo '离开';}elseif($row['status2']=='5'){echo '断线';}else{ echo '异常状态';} ?></td>
										<td><?php if($row['active_time1'] != 0): echo (date('Y-m-d
											H:i:s',$row["active_time1"])); else: echo ($row["active_time1"]); endif; ?></td>
										<td><?php if($row['active_time2'] != 0): echo (date('Y-m-d
											H:i:s',$row["active_time2"])); else: echo ($row["active_time2"]); endif; ?></td>
										<td><?php if($row['phase'] == 0){echo '等人中';}else{echo '战斗中';} ?></td>
											<td><?php if($row['is_gifts']=='0'){echo "未送礼";}elseif($row['is_gifts'] == 1){echo '已送礼';} ?></td>
										<td><?php if($row['start_time'] != 0): echo (date('Y-m-d
											H:i:s',$row["start_time"])); else: echo ($row["start_time"]); endif; ?></td>
										<td><?php if($row['end_time'] != 0): echo (date('Y-m-d
											H:i:s',$row["end_time"])); else: echo ($row["end_time"]); endif; ?></td>
										<td><?php if($row['create_time'] != 0): echo (date('Y-m-d
											H:i:s',$row["create_time"])); else: echo ($row["create_time"]); endif; ?></td>
							<!-- 			<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_queue',array('id'=>$row['arena_id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td> -->
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_support',array('id'=>$row['id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td>
								<!-- 		<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='#';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td>
										<td class="taskOptions"><a href="javascript:;"
											onClick="javascript:window.location.href='<?php echo U('Arena/arena_rank',array('id'=>$row['arena_id']));?>';"
											class="tip-top" data-original-title="Update" title="Update"><i
												class="icon-list-alt"></i></a></td> -->
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