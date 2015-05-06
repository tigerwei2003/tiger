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
				<h1>签到记录</h1>
			</div>
			<div id="breadcrumb">
				<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> 后台首页</a>
				<a href="<?php echo U('Record/sign_in');?>" class="current">签到记录</a>
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
                                    <input type="text" name="user"  value="<?php if($user){ echo $user;}else{ echo '用户';} ?>" onBlur="blur_input(this,'用户')" onClick="click_input(this,'用户')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="continuously_day"  value="<?php if($continuously_day){ echo $continuously_day;}else{ echo '连续签到的天数';} ?>" onBlur="blur_input(this,'连续签到的天数')" onClick="click_input(this,'连续签到的天数')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="startdate" value="<?php if($startdate){ echo $startdate;}else{ echo date('Y-m-d',time()-1296000);} ?>"  data-date-format="yyyy-mm-dd" class="datepicker">
                                    </td>
                                    <td>-</td>
                                    <td>
                                    <input type="text" name="enddate"  value="<?php if($enddate){ echo $enddate;}else{ echo date('Y-m-d');} ?>" data-date-format="yyyy-mm-dd" class="datepicker">
                                    </td>
                                    <td>
                                    <button class="btn btn-success" onClick="javascript:$('#searchform').submit();"><i class="icon-search icon-white"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>    
                        </form>
                        </div>
                        <div class="span6" style="width: 20%; margin-left: 0px !important;">
                        	<div class="widget-box">
								<div class="widget-title">
									<span class="icon"><i class="icon-leaf"></i></span>
									<h5>签到设置</h5>
								</div>
								<div class="widget-content">
	                            	<div class="cont_max">
									<table class="table table-bordered table-striped with-check">
										<thead>
											<tr>
	                                        	<th>签到第几天</th>
												<th>签到领取的G币</th>
	                                            <th>签到领取的经验</th>
												<th>额外领取的G币</th>
											</tr>
										</thead>
										<tbody>
	                                    <?php if(is_array($sign_in)): foreach($sign_in as $key=>$row): ?><tr>
	                                        	<td><?php echo ($row["sign_day"]); ?></td>
	                                            <td><?php echo ($row["gift_coin"]); ?></td>
	                                            <td><?php echo ($row["gift_exp"]); ?></td>
	                                            <td><?php echo ($row["extra_gift_coin"]); ?></td>
											</tr><?php endforeach; endif; ?>										
										</tbody>
									</table>
	                                </div>		  					
								</div> 
							</div>
						</div>
						<div class="span6" style="width: 78%; margin-left: 10px !important;">
	                        <div class="widget-box">
								<div class="widget-title">
									<span class="icon"><i class="icon-leaf"></i></span>
									<h5>签到记录</h5>
	                                <!--
	                                <a href="<?php echo U('Export/payment');?>"><span class="label btn-warning"><i class=" icon-download-alt icon-white"></i> 导出</span></a>
	                                -->
								</div>
								<div class="widget-content">
	                            	<div class="cont_max">
									<table class="table table-bordered table-striped with-check">
										<thead>
											<tr>
	                                        	<th>编号</th>
												<th>签到账户ID</th>
	                                            <th>连签的天数</th>
												<th>签到时间</th>
												<th>领取的G币</th>
												<th>额外加成的G币</th>
												<th>领取的经验</th>
												<th>是否为连续签到</th>
												<th>今天是否已经签到</th>
												<th>创建时间</th>
											</tr>
										</thead>
										<tbody>
	                                    <?php if(is_array($sign_in_list)): foreach($sign_in_list as $key=>$row): ?><tr>
	                                        	<td><?php echo ($row["id"]); ?></td>
	                                            <td><?php echo ($row["account_id"]); ?></td>
	                                            <td><?php echo ($row["continuously_day"]); ?></td>
	                                            <td><?php if($row['sign_time']): echo (date('Y-m-d H:i:s',$row["sign_time"])); endif; ?></td>
												<td><?php echo ($row["gift_coin"]); ?></td>
												<td><?php echo ($row["extra_gift_coin"]); ?></td>
												<td><?php echo ($row["gift_exp"]); ?></td>
												<td><?php if($row['flag']==1){echo '签到';}elseif($row['flag']==3){echo '客服赠送';} ?></td>
												<td><?php if($row['is_sign_today']==1){echo '已签';}elseif($row['is_sign_today']==0){echo '未签';} ?></td>
												<td><?php if($row['created_time']): echo (date('Y-m-d H:i:s',$row["created_time"])); endif; ?></td>
											</tr><?php endforeach; endif; ?>										
										</tbody>
									</table>
	                                </div>	
	                                <div class="pagination" style="text-align:center"><?php echo ($pages); ?></div>	  					
								</div> 
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
		$('.datepicker').datepicker();
	});
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