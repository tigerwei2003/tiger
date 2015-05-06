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
		<h1>统计汇总</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="javascript:;" class="current">统计汇总</a>
		<a href="javascript:;" onClick="javascript:history.go(-1);"
			title="Go to Back" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<!--一个通栏BOX-->
				<div class="row-fluid">
					<div class="span12">
						<div class="widget-box widget-calendar">
							<div class="widget-title">
								<span class="icon"><i class="icon-search"></i></span>
								<h5 id="charttitle">统计汇总</h5>
							</div>
							<form class="form-horizontal" action="" method="get"
								id="searchform">
						
								<div class="searchunit">
									<div class="searchtitle">条件</div>
									<div class="searchlist" id="searchlist">
										<ul>
											<li><select name="game">
													<option value="">
														选择游戏
														<?php  foreach($gamelist as $v){ if($v['game_name']){ if($v['game_id']==$game){ $select = 'selected'; }else{ $select = ''; } echo '<option value="'.$v['game_id'].'" '.$select.'>'.$v['game_name'].'</option>'; } } ?>
											</select></li>
											<li><select name="region">
													<option value="">选择省份</option>
													<?php  foreach($regionlist as $v){ if($v['region']){ if($v['region']==$region){ $select = 'selected'; }else{ $select = ''; } echo '<option value="'.$v['region'].'" '.$select.'>'.$v['region'].'</option>'; } } ?>
											</select></li>
											<li><select name="isp">
													<option value="">选择运营商</option>
													<?php  foreach($isplist as $v){ if($v['isp']){ if($v['isp']==$isp){ $select = 'selected'; }else{ $select = ''; } echo '<option value="'.$v['isp'].'" '.$select.'>'.$v['isp'].'</option>'; } } ?>
											</select></li>
											<li><select name="type">
													<option value="">选择设备类型</option>
													<?php  foreach($clienttypelist as $v){ if($v['client_type']){ if($v['client_type']==$type){ $select = 'selected'; }else{ $select = ''; } echo '<option value="'.$v['client_type'].'" '.$select.'>'.$v['client_type'].'</option>'; } } ?>
											</select></li>
											<li><select name="ver">
													<option value="">选择客户端版本</option>
													<?php  foreach($clientverlist as $v){ if($v['client_ver']){ if($v['client_ver']==$ver){ $select = 'selected'; }else{ $select = ''; } echo '<option value="'.$v['client_ver'].'" '.$select.'>'.$v['client_ver'].'</option>'; } } ?>
											</select></li>
											<li><select name="pid">
													<option value="">选择客户渠道</option>
													<?php  foreach($pidlist as $v){ if($v['pid']){ if($v['pid']==$pid){ $select = 'selected'; }else{ $select = ''; } echo '<option value="'.$v['pid'].'" '.$select.'>'.$v['pid'].'</option>'; } } ?>
											</select></li>
											<li><input type="text" name="account"
												value="<?php if($account){ echo $account;}else{ echo '用户ID';} ?>"
												onBlur="blur_input(this,'用户ID')"
												onClick="click_input(this,'用户ID')"></li>
											<li><input type="text" name="device"
												value="<?php if($device){ echo $device;}else{ echo '设备ID';} ?>"
												onBlur="blur_input(this,'设备ID')"
												onClick="click_input(this,'设备ID')"></li>
											<li><input type="text" name="gsip"
												value="<?php if($gsip){ echo $gsip;}else{ echo 'GSIP';} ?>"
												onBlur="blur_input(this,'GSIP')"
												onClick="click_input(this,'GSIP')"></li>
											<li><input type="text" name="gsid"
												value="<?php if($gsid){ echo $gsid;}else{ echo 'GSID';} ?>"
												onBlur="blur_input(this,'GSID')"
												onClick="click_input(this,'GSID')"></li>
											<li><input type="text" name="end_code"
												value="<?php if($end_code){ echo $end_code;}else{ echo '游戏结束代码';} ?>"
												onBlur="blur_input(this,'游戏结束代码')"
												onClick="click_input(this,'游戏结束代码')"></li>
										</ul>
									</div>
									<div class="searchtitle">时间</div>
									<div class="searchlist" id="searchlist">
										<ul>
											<li><select name="times">
													<option value="">时间类型
													<option <?php if($times=='gt.create_time'){ echo 'selected';}?> value="gt.create_time">创建时间
													<option <?php if($times=='gt.gs_start_time'){ echo 'selected';}?> value="gt.gs_start_time">游戏开始时间
													<option <?php
 if($times=='gt.gs_last_report_time'){ echo 'selected';}?>
														value="gt.gs_last_report_time">最近汇报时间
													<option <?php if($times=='sb.update_time'){ echo 'selected';}?> value="sb.update_time">更新时间
											</select></li>
											<li><input type="text" name="startdate"
												value="<?php if($startdate){ echo $startdate;}else{ echo '开始时间';} ?>"
												onBlur="blur_input(this,'开始时间')"
												onClick="click_input(this,'开始时间')"></li>
											<li><input type="text" name="enddate"
												value="<?php if($enddate){ echo $enddate;}else{ echo '结束时间';} ?>"
												onBlur="blur_input(this,'结束时间')"
												onClick="click_input(this,'结束时间')"></li>
										</ul>
									</div>

									<div class="searchtitle">排序</div>
									<div class="searchlist" id="searchlist">
										<ul>
											<li><select name="order">
													<option <?php if($order=='gt.id'){ echo 'selected';}?> value="gt.id">按编号排序
													<option <?php if($order=='gt.create_time'){ echo 'selected';}?> value="gt.create_time">按创建时间排序
													<option <?php if($order=='gt.gs_start_time'){ echo 'selected';}?> value="gt.gs_start_time">按游戏开始时间排序
													<option <?php
 if($order=='gt.gs_last_report_time'){ echo 'selected';}?>
														value="gt.gs_last_report_time">按最近汇报时间排序
													<option <?php if($order=='sb.update_time'){ echo 'selected';}?> value="sb.update_time">按更新时间排序
													<option <?php if($order=='sb.region'){ echo 'selected';}?> value="sb.region">按省份排序
													<option <?php if($order=='gt.game_id'){ echo 'selected';}?> value="gt.game_id">按游戏排序
													<option <?php if($order=='sb.isp'){ echo 'selected';}?> value="sb.isp">按运营商排序
													<option <?php if($order=='sb.pid'){ echo 'selected';}?> value="sb.pid">按渠道排序
													<option <?php if($order=='sb.client_ver'){ echo 'selected';}?> value="sb.client_ver">按客户端版本排序
													<option <?php if($order=='sb.client_type'){ echo 'selected';}?> value="sb.client_type">按设备类型排序
											</select></li>
											<li><select name="order_type">
													<option <?php if($order_type=='desc'){ echo 'selected';}?> value="desc">倒序
													<option <?php if($order_type=='asc'){ echo 'selected';}?> value="asc">正序
											</select></li>
										</ul>
									</div>
									<div class="searchtitle">字段</div>
									<div class="searchckbox">
										<ul>
											<li><input type="checkbox" name="checkboxdate[gt.id]"<?php
 if($dosubmint!=1){ echo 'checked';}elseif($checkboxdate['gt.id']=='编号'){ echo 'checked'; } ?> value="编号"> 编号</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.game_id]"<?php
 if($dosubmint!=1){ echo 'checked';}elseif($checkboxdate['gt.game_id']=='游戏名称'){ echo 'checked'; } ?> value="游戏名称"> 游戏名称</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.account_id]"<?php
 if($dosubmint!=1){ echo 'checked';}elseif($checkboxdate['gt.account_id']=='用户'){ echo 'checked'; } ?> value="用户"> 用户</li>
											<li><input type="checkbox"
												name="checkboxdate[sb.region]"<?php
 if($dosubmint!=1){ echo 'checked';}elseif($checkboxdate['sb.region']=='省份'){ echo 'checked'; } ?> value="省份"> 省份</li>
											<li><input type="checkbox" name="checkboxdate[sb.isp]"<?php
 if($dosubmint!=1){ echo 'checked';}elseif($checkboxdate['sb.isp']=='运营商'){ echo 'checked'; } ?> value="运营商"> 运营商</li>
											<li><input type="checkbox" name="checkboxdate[sb.pid]"<?php
 if($dosubmint!=1){ echo 'checked';}elseif($checkboxdate['sb.pid']=='渠道'){ echo 'checked'; } ?> value="渠道"> 渠道</li>
											<li><input type="checkbox"
												name="checkboxdate[sb.device_uuid]"<?php
 if($checkboxdate['sb.device_uuid']=='设备编号'){ echo 'checked';} ?> value="设备编号"> 设备编号</li>
											<li><input type="checkbox"
												name="checkboxdate[sb.client_type]"<?php
 if($checkboxdate['sb.client_type']=='设备类型'){ echo 'checked';} ?> value="设备类型"> 设备类型</li>
											<li><input type="checkbox"
												name="checkboxdate[sb.client_ver]"<?php
 if($checkboxdate['sb.client_ver']=='客户端版本'){ echo 'checked';} ?> value="客户端版本"> 客户端版本</li>
											<li><input type="checkbox" name="checkboxdate[gt.gs_ip]"<?php
 if($checkboxdate['gt.gs_ip']=='GSIP'){ echo 'checked';} ?>
												value="GSIP"> GSIP</li>
											<li><input type="checkbox" name="checkboxdate[gt.gs_id]"<?php
 if($checkboxdate['gt.gs_id']=='GSID'){ echo 'checked';} ?>
												value="GSID"> GSID</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.is_online_gs]"<?php
 if($checkboxdate['gt.is_online_gs']=='线上GS'){ echo 'checked';} ?> value="线上GS"> 线上GS</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.end_code]"<?php
 if($checkboxdate['gt.end_code']=='游戏结束代码'){ echo 'checked';} ?> value="游戏结束代码"> 游戏结束代码</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.gs_start_time]"<?php
 if($checkboxdate['gt.gs_start_time']=='开始游戏时间'){ echo 'checked';} ?> value="开始游戏时间"> 开始游戏时间</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.gs_last_report_time]"<?php
 if($checkboxdate['gt.gs_last_report_time']=='最近一次汇报时间'){ echo 'checked';} ?> value="最近一次汇报时间"> 最近一次汇报时间</li>
											<li><input type="checkbox"
												name="checkboxdate[gt.create_time]"<?php
 if($checkboxdate['gt.create_time']=='创建时间'){ echo 'checked';} ?> value="创建时间"> 创建时间</li>
											<li><input type="checkbox"
												name="checkboxdate[sb.update_time]"<?php
 if($checkboxdate['sb.update_time']=='更新时间'){ echo 'checked';} ?> value="更新时间"> 更新时间</li>
										</ul>
									</div>

									<div class="searchtitle">汇总</div>
									<div class="searchckbox">
										<ul>
											<li style="width: 220px;"><select name="sum">
													<option value="">选择汇总类型
													<option value="1"<?php if($sum=='1'){ echo 'selected';} ?>>单款游戏按省份汇总
													<option value="2"<?php if($sum=='2'){ echo 'selected';} ?>>单款游戏按运营商汇总
													<option value="3"<?php if($sum=='3'){ echo 'selected';} ?>>单款游戏按渠道汇总
													<option value="4"<?php if($sum=='4'){ echo 'selected';} ?>>单款游戏按设备类型汇总
													<option value="5"<?php if($sum=='5'){ echo 'selected';} ?>>单个省份所有游戏汇总
													<option value="6"<?php if($sum=='6'){ echo 'selected';} ?>>单个运营商所有游戏汇总
													<option value="7"<?php if($sum=='7'){ echo 'selected';} ?>>单个渠道所有游戏汇总
													<option value="8"<?php if($sum=='8'){ echo 'selected';} ?>>单个设备类型所有游戏汇总
											</select></li>

										</ul>
									</div>
									<div class="searchsub">
										<button type="submit" name="dosubmint" value="1"
											class="btn btn-success">开始统计</button>
										&nbsp;&nbsp;&nbsp;&nbsp;
										<button type="submit" name="dosubmint" value="2"
											class="btn btn-warning">导出报表</button>
										&nbsp;&nbsp;&nbsp;&nbsp; <a class="btn btn-danger"
											href="<?php echo U(Chart/statistics);?>">清空表单</a>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<!--一个通栏BOX-->


				<!--汇总-->
				<div class="row-fluid phonenone" style="">
					<div class="span12">
						<div class="widget-box widget-calendar">
							<div class="widget-title">
								<span class="icon"><i class="icon-random"></i></span>
								<h5 id="charttitle">汇总图表</h5>
							</div>
							<div class="widget-content nopadding" style="overflow: hidden;">
								<div class="chartcontent" id="container_1"<?php
 if($sum>=5){ echo 'style="height:'.$sumheight.'px;"';} ?> ></div>
							</div>
						</div>
					</div>
				</div>

				<div class="widget-box" style="">
					<div class="widget-title">
						<span class="icon"><i class="icon-align-justify"></i></span>
						<h5><?php echo ($chart_title); ?>汇总明细</h5>
					</div>
					<div class="widget-content">
						<table class="table table-bordered table-striped with-check"
							style="width: 100%; background: #ffffff;">
							<thead>
								<tr>
									<th width="50%">类型</th>
									<th width="50%">游戏总时长(小时)</th>
								</tr>
							</thead>
							<tbody>
								<?php if(is_array($chart_more)): foreach($chart_more as $key=>$row): ?><tr>
									<td><?php echo ($row["chart_title"]); ?></td>
									<td><?php echo ($row["chart_data"]); ?></td>
								</tr><?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<!--汇总-->


				<div class="widget-box"
					<?php if(!$dosubmint || $yy){ echo 'style="display:none" ';} ?>>
					<div class="widget-title">
								<span class="icon"><i class="icon-align-justify"></i></span>
								<h5>统计结果明细</h5>
							</div>
							<div class="widget-content">
                            	<div class="cont_max">
								<table class="table table-bordered table-striped with-check" id="maxtable">
									<thead>
										<tr>
                                        	<?php foreach($checkboxdate as $k=>$v){ ?>
											<th><?php echo ($v); ?></th>
                                            <?php } ?>
										</tr>
									</thead>
									<tbody>
                                    <?php if(is_array($datalist)): foreach($datalist as $key=>$row): ?><tr>
                                        	<?php  foreach($checkboxdate as $k=>$v){ $kn = explode(".",$k); if(strpos($v, '时间')){ $text = date('Y-m-d H:i:s',$row[$kn[1]]); }else if($kn[1]=='game_id'){ $text = $row['game_name']; }else{ $text = $row[$kn[1]]; } ?>
											<td><?php echo $text; ?></td>
                                            <?php } ?>
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
			<div id="footer" class="span12"><?php echo C('SEO_COPYRIGHT');?></div>
		</div>

	</div>
</div>



<script src="/gloudapi2/Public/static/js/jquery.uniform.js"></script>
<script src="/gloudapi2/Public/static/js/unicorn.tables.js"></script>
<script src="/gloudapi2/Public/static/charts/highcharts.js" type="text/javascript"></script>
<script src="/gloudapi2/Public/static/charts/modules/exporting.js"
	type="text/javascript"></script>
<script type="text/javascript">

$(document).ready(function(e) {
	tableresize(<?php echo $countcheckbox; ?>);
});
$(window).resize(function(e){
	tableresize(<?php echo $countcheckbox; ?>);
});

<?php if($yy){ ?>
$(document).ready(function(e) {
	var chart;
    chart_user('container_1');
});

//用户统计
function chart_user(id){ 
	//声明报表对象  
	var chart = new Highcharts.Chart({  
		chart: {  
			//将报表对象渲染到层上以及图表类型
			<?php if($sum<=4){ ?>  
			renderTo: id
			<?php }else{ ?>
			renderTo: id,
			type: 'bar' 
			<?php } ?>
		},title: {
			text: '<?php echo $chart_title; ?>游戏总时间汇总'
		},credits: {
			 text: '云游戏',
			 href: 'http://www.pyou.com/'
		},xAxis: {
			categories: [<?php echo $yy; ?>],labels:{
				style:{
					font:'normal 12px 宋体',	
				}	
			}
		},yAxis: {
			title: {
				text: '小时'
			},
			min: 0
		},tooltip: {
			formatter: function() {
				return ''+
					this.x + this.series.name +': '+ this.y +' 小时';
			}
		}, 
		
		//设定报表对象的初始数据  
		series: [<?php echo $gamechartdata; ?>]  
	});  
  
} 
<?php } ?>
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