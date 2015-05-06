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
				<h1>帐号管理</h1>
			</div>
			<div id="breadcrumb">
				<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> 后台首页</a>
				<a href="<?php echo U('Account/index');?>" class="current">帐号管理</a>
                <a href="javascript:;" onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i class="icon-share-alt"></i> 返回</a>
			</div>
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span12">
                    	<div class="sousuo">
                        <form class="form-horizontal" action="" method="post" id="searchform">
                        <table rules="none" border="0">
                            <tbody>
                            	<tr>
                                    <td style="padding-right:0">
                                    <input type="text" name="id"  value="<?php if($id){ echo $id;}else{ echo 'ID';} ?>" onBlur="blur_input(this,'ID')" onClick="click_input(this,'ID')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="device_uuid"  value="<?php if($device_uuid){ echo $device_uuid;}else{ echo '设备UUID';} ?>" onBlur="blur_input(this,'设备UUID')" onClick="click_input(this,'设备UUID')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="nickname"  value="<?php if($nickname){ echo $nickname;}else{ echo '昵称';} ?>" onBlur="blur_input(this,'昵称')" onClick="click_input(this,'昵称')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="bind_phone"  value="<?php if($bind_phone){ echo $bind_phone;}else{ echo '手机';} ?>" onBlur="blur_input(this,'手机')" onClick="click_input(this,'手机')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="bind_email"  value="<?php if($bind_email){ echo $bind_email;}else{ echo '邮箱';} ?>" onBlur="blur_input(this,'邮箱')" onClick="click_input(this,'邮箱')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="level"  value="<?php if($level){ echo $level;}else{ echo '级别';} ?>" onBlur="blur_input(this,'级别')" onClick="click_input(this,'级别')">
                                    </td>
                                    <td>
                                    <button class="btn btn-success" onClick="javascript:$('#searchform').submit();"><i class="icon-search icon-white"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>    
                        </form>
                        </div>
                        <div class="widget-box">
							<div class="widget-title">
								<span class="icon">
									<i class="icon-th"></i>
								</span>
								<h5>帐号管理</h5>
                             
                                <a href="<?php echo U('Export/account',array('id'=>$id,'device_uuid'=>$device_uuid,'nickname'=>$nickname,'bind_phone'=>$bind_phone,'bind_email'=>$bind_email,'level'=>$level));?>" class="phonenone"><span class="label btn-warning"><i class=" icon-download-alt icon-white"></i> 导出</span></a>
                              
							</div>
							<div class="widget-content">
                            	<div class="cont_max">
								<table class="table table-bordered table-striped with-check" id="maxtable" >
									<thead>
										<tr>
                                        	<th width="50">帐号ID</th>
                                            <th width="50">是否启用</th>
                                            <th width="150">昵称</th>
                                            <th width="100">手机</th>
                                            <th width="200">邮箱</th>
                                            <th width="50">级别</th>
                                            <th width="50">经验</th>
											<th width="50">云豆</th>
                                            <th width="50">云贝</th>
											<th width="50">G币</th>
                                            <th width="60">消费云豆</th>
                                            <th width="60">消费云贝</th>
                                            <th width="60">消费G币</th>
                                            <th width="60">游戏时间</th>
                                            <th width="60">游戏时间(秒)</th>
                                            <th width="100">设备类型</th>
                                            <th width="150">创建日期</th>
                                            <th width="150">最近登录</th>
                                         
                                            <th width="40" class="taskOptions">编辑</th>
                                          
										</tr>
									</thead>
									<tbody>
                                    <?php if(is_array($Account)): foreach($Account as $key=>$row): ?><tr>
											<td><?php echo ($row["id"]); ?></td>
                                            <td><?php echo ($row["status"]); ?></td>
                                            <td><?php echo ($row["nickname"]); ?></td>
                                            <td><?php echo ($row["bind_phone"]); ?></td>
                                            <td><?php echo ($row["bind_email"]); ?></td>
                                            <td><?php echo ($row["level"]); ?></td>
                                            <td><?php echo ($row["exp"]); ?></td>
                                            <td><?php echo ($row["bean"]); ?></td>
                                            <td><?php echo ($row["gift_coin_num"]); ?></td>
                                            <td><?php echo ($row["gold"]); ?></td>
											<td><?php echo ($row["used_bean_num"]); ?></td>
											<td><?php echo ($row["used_coin_num"]); ?></td>
											<td><?php echo ($row["used_gold_num"]); ?></td>
                                            <td><?php $hour = str_pad(intval($row['total_play_time']/3600), 2, '0', STR_PAD_LEFT); $minute = str_pad(intval($row['total_play_time']%3600/60), 2, '0', STR_PAD_LEFT); $second = str_pad(intval($row['total_play_time']%60), 2, '0', STR_PAD_LEFT); echo "$hour:$minute:$second"; ?></td>
											<td><?php echo ($row["total_play_time"]); ?></td>
											<td><?php echo ($row["client_type"]); ?></td>
                                            <td><?php if($row['create_time']): echo (date('Y-m-d H:i:s',$row["create_time"])); endif; ?></td>
                                            <td><?php if($row['last_login_time']): echo (date('Y-m-d H:i:s',$row["last_login_time"])); endif; ?></td>
                                         
                                            <td class="taskOptions">
                                            	<a href="javascript:;" onClick="javascript:window.location.href='<?php echo U('Account/edit',array('id'=>$row['id']));?>';" class="tip-top" data-original-title="Update" title="Update"><i class="icon-pencil"></i></a>
                                            </td>
                                           
										</tr><?php endforeach; endif; ?>										
									</tbody>
								</table>	
                                </div>
                                <div class="pagination" style="text-align:center"><?php echo ($pages); ?></div>						
							</div>
						</div>
					</div>
				</div>
				

				<div class="row-fluid">
					<div id="footer" class="span12">
						<?php echo C('SEO_COPYRIGHT');?>
					</div>
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