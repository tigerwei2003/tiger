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
		function check(menuid)
		{
			var c='.cc'+menuid;
			if($(c).css("display")=="none")
			{
				$(c).css("display","");;
			}

		}
			
		</script>

<link rel="stylesheet" href="/gloudapi2/Public/static/css/uniform.css" />

<div id="content">
	<div id="content-header">
		<h1>节点管理</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo ($index_url); ?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="<?php echo ($node_index); ?>" class="current">节点管理</a>
		<a href="javascript:;" onClick="javascript:history.go(-1);"
			class="tip-bottom" id="gotoback"><i class="icon-share-alt"></i>
			返回</a>
	</div>

	<div class="container-fluid">

		<div class="row-fluid">
			<div class="span12">

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"> <i class="icon-th"></i>
						</span>
						<h5>节点管理</h5>
						<a href="<?php echo ($add_url); ?>"><span class="label btn-success"><i
								class="icon-plus icon-white"></i> 添加顶级菜单</span></a>
					</div>
					<div class="widget-content">
						<div class="cont_max">
							<form name="myform" id="myform" action="<?php echo U('Role/priv');?>"
								method="post">
								<input type="hidden" name="role_id" value="<?php echo ($role_id); ?>" /> <input
									type="hidden" name="dosubmit" value="1" />
								<table class="table table-bordered table-striped with-check"
									id="maxtable">
									<thead>
										<tr>

											<th width="160">URL</th>
											<th width="">节点名称</th>
											<th width="80">类型</th>
											<th width="80">状态</th>
											<th width="40" class="taskOptions">权限状态</th>

										</tr>
									</thead>
									<?php if(empty($menu_group_id_str)): ?><tbody>
										<?php if(is_array($parent_menu_arr['parent'])): $i = 0; $__LIST__ = $parent_menu_arr['parent'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id="quanxian<?php echo ($vo['id']); ?>" bgcolor="#ccc">
											<td>&nbsp;<a href=''><?php echo ($vo['url']); ?></a></td>
											<td><?php echo ($vo['title']); ?></td>
											<td>顶级菜单</td>
											<td><?php echo ($vo["status"]); ?></td>
											<td align=center><input type=checkbox id="quanxian"
												name="quanxian[]" value="<?php echo ($vo['id']); ?>" onclick="check(<?php echo ($vo['id']); ?>)"></td>
										</tr>
										<?php if(is_array($parent_menu_arr['child'][$vo['id']])): $i = 0; $__LIST__ = $parent_menu_arr['child'][$vo['id']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voo): $mod = ($i % 2 );++$i;?><tr class="cc<?php echo ($vo['id']); ?>" style="display:none">
											<td>&nbsp;&nbsp;&nbsp;&nbsp;|-<a href=''><?php echo ($voo['url']); ?></a></td>
											<td width=20%>&nbsp;<a href=''><?php echo ($voo['title']); ?></a></td>
											<td width=10% align=center>&nbsp;<a href=''>二级菜单</a></td>

											<td><?php echo ($vo["status"]); ?></td>
											<td align=center><input type=checkbox id="quanxian"
												name="quanxian[]" value="<?php echo ($voo['id']); ?>" onclick="check(<?php echo ($voo['id']); ?>)"></td>

										</tr>

										<?php if(is_array($parent_menu_arr['func'][$voo['id']])): $i = 0; $__LIST__ = $parent_menu_arr['func'][$voo['id']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vooo): $mod = ($i % 2 );++$i;?><tr class="cc<?php echo ($vo['id']); ?>" style="display:none">
											<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-<a
												href=''><?php echo ($vooo['url']); ?></a></td>
											<td width=20%>&nbsp;<a href=''><?php echo ($vooo['title']); ?></a></td>
											<td width=10% align=center>&nbsp;<a href=''>功能点</a></td>
											<td><?php echo ($vooo["status"]); ?></td>
											<td align=center><input type=checkbox id="quanxian"
												name="quanxian[]" value="<?php echo ($vooo['id']); ?>"></td>

										</tr><?php endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
									</tbody>
									<?php else: ?>
									<tbody>
										<?php if(is_array($parent_menu_arr['parent'])): $i = 0; $__LIST__ = $parent_menu_arr['parent'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id="quanxian<?php echo ($vo['id']); ?>" bgcolor="#ccc">
											<td>&nbsp;<a href=''><?php echo ($vo['url']); ?></a></td>
											<td><?php echo ($vo['title']); ?></td>
											<td>顶级菜单</td>
											<td><?php echo ($vo["status"]); ?></td>
											<?php if(in_array(($vo["id"]), is_array($menu_group_id_str)?$menu_group_id_str:explode(',',$menu_group_id_str))): ?><td width=20% align=center><input type=checkbox
												id="quanxian<?php echo ($vo['id']); ?>" name="quanxian[]"
												value="<?php echo ($vo['id']); ?>" onclick="check(<?php echo ($vo['id']); ?>)" checked></td>
											<?php else: ?>
											<td width=20% align=center><input type=checkbox
												id="quanxian<?php echo ($vo['id']); ?>" name="quanxian[]"
												value="<?php echo ($vo['id']); ?>" onclick="check(<?php echo ($vo['id']); ?>)"></td><?php endif; ?>
										</tr>
										<?php if(is_array($parent_menu_arr['child'][$vo['id']])): $i = 0; $__LIST__ = $parent_menu_arr['child'][$vo['id']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voo): $mod = ($i % 2 );++$i;?><tr class="cc<?php echo ($vo['id']); ?>" style="display:">
											<td>&nbsp;&nbsp;&nbsp;&nbsp;|-<a href=''><?php echo ($voo['url']); ?></a></td>
											<td width=20%>&nbsp;<a href=''><?php echo ($voo['title']); ?></a></td>
											<td width=10% align=center>&nbsp;<a href=''>二级菜单</a></td>

											<td><?php echo ($voo["status"]); ?></td>
											<?php if(in_array(($voo["id"]), is_array($menu_group_id_str)?$menu_group_id_str:explode(',',$menu_group_id_str))): ?><td width=20% align=center><input type=checkbox
												id="quanxian<?php echo ($voo['id']); ?>" name="quanxian[]"
												value="<?php echo ($voo['id']); ?>" onclick="check(<?php echo ($voo['id']); ?>)"
												checked></td>
											<?php else: ?>
											<td width=20% align=center><input type=checkbox
												id="quanxian<?php echo ($voo['id']); ?>" name="quanxian[]"
												value="<?php echo ($voo['id']); ?>" onclick="check(<?php echo ($voo['id']); ?>)"></td><?php endif; ?>
										</tr>

										<?php if(is_array($parent_menu_arr['func'][$voo['id']])): $i = 0; $__LIST__ = $parent_menu_arr['func'][$voo['id']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vooo): $mod = ($i % 2 );++$i;?><tr class="cc<?php echo ($vo['id']); ?>" style="display:">
											<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-<a
												href=''><?php echo ($vooo['url']); ?></a></td>
											<td width=20%>&nbsp;<a href=''><?php echo ($vooo['title']); ?></a></td>
											<td width=10% align=center>&nbsp;<a href=''>功能点</a></td>
											<td><?php echo ($vooo["status"]); ?></td>
											<?php if(in_array(($vooo["id"]), is_array($menu_group_id_str)?$menu_group_id_str:explode(',',$menu_group_id_str))): ?><td width=20% align=center><input type=checkbox
												id="quanxian<?php echo ($vooo['id']); ?>" name="quanxian[]"
												value="<?php echo ($vooo['id']); ?>" 
												checked></td>
											<?php else: ?>
											<td width=20% align=center><input type=checkbox
												id="quanxian<?php echo ($vooo['id']); ?>" name="quanxian[]"
												value="<?php echo ($vooo['id']); ?>"></td><?php endif; ?>
										</tr><?php endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
									</tbody><?php endif; ?>
								</table>
								<div class="form-actions">
									<input type="submit" value="保存" class="btn btn-primary" />
								</div>
							</form>
						</div>
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
		tableresize(900);
	});
	$(window).resize(function(e){
		tableresize(900);
	});
</script>

<div id="showcontent"></div>


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