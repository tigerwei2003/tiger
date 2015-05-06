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
		<h1><?php echo C('SEO_TITLE');?></h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="javascript:;"
			onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<ul class="quick-actions">
					<li><a href="<?php echo U('Rechargecard/activation');?>"> <i
							class="icon-book"></i> <span>激活充值卡</span>
					</a></li>


					<li><a href="<?php echo U('Record/bespeak');?>"> <i
							class="icon-search"></i> 预约记录
					</a></li>


					<li><a href="<?php echo U('System/account');?>"> <i
							class="icon-people"></i> 帐号管理
					</a></li>




					<li><a href="<?php echo U('Rechargecard/countchart');?>"> <i
							class="icon-piechart"></i> 售卡汇总
					</a></li>



					<li><a href="<?php echo U('Chart/statistics');?>"> <i
							class="icon-chart"></i> 统计汇总
					</a></li>



					<li><a href="<?php echo U('Chart/index');?>"> <i class="icon-graph"></i>
							每日报表
					</a></li>

				</ul>
			</div>
		</div>

		<!-- <div class="row-fluid">
			<div class="span12">

				一个通栏BOX
				<div class="row-fluid phonenone" style="margin-top: -5px;">
					<div class="span12">
						<div class="widget-box widget-calendar">
							<div class="widget-title">
								<span class="icon"><i class="icon-random"></i></span>
								<h5 id="charttitle">近半月数据</h5>
							</div>
							<div class="widget-content nopadding" style="overflow: hidden;">
								<div class="chartcontent" id="container_1"></div>
								<div class="chartcontent" id="container_2"
									style="display: none;"></div>
								<div class="chartcontent" id="container_3"
									style="display: none;"></div>
							</div>
						</div>
					</div>
				</div>
				一个通栏BOX



				一个通栏BOX
				<div class="row-fluid" style="margin-top: -16px;">
					<div class="span12">
						<div class="widget-box widget-calendar">
							<div class="widget-title">
								<span class="icon"><i class="icon-random"></i></span>
								<h5>近半月售卡与用户开卡曲线图</h5>
							</div>
							<div class="widget-content nopadding">
								<div class="content" id="chart_3" style="height: 320px;"></div>
							</div>
						</div>
					</div>
				</div>
				一个通栏BOX


				
                        <?php if(rolemenu('Chart/index')): ?><div class="widget-box" style="margin-top:10px;">
							<div class="widget-title">
								<span class="icon">
									<i class="icon-th"></i>
								</span>
								<h5>最新预约信息</h5>
							</div>
							<div class="widget-content">
								<div class="cont_max">
								<table class="table table-bordered table-striped with-check" id="maxtable">
									<thead>
										<tr>
											<th>用户</th>
                                            <th>姓名</th>
                                            <th>电话</th>
											<th>城市</th>
                                            <th>小区</th>
                                            <th>宽带</th>
                                            <th>填表时间</th>
										</tr>
									</thead>
									<tbody>
                                    <?php if(is_array($bespeak)): foreach($bespeak as $key=>$row): ?><tr>
											<td><?php echo ($row["username"]); ?></td>
                                            <td><?php echo ($row["realname"]); ?></td>
                                            <td><?php echo ($row["phone"]); ?></td>
											<td><?php echo ($row["city"]); ?></td>
                                            <td><?php echo ($row["village"]); ?></td>
                                            <td><?php if($row['adsl']){ echo $row['adsl'].'M';}else{ echo '未装';} ?></td>
                                            <td><?php echo (date('Y-m-d',$row["inputtime"])); ?></td>
										</tr><?php endforeach; endif; ?>										
									</tbody>
								</table>
                                </div>	   				
							</div>
						</div><?php endif; ?>
                       





			</div>
		</div> -->


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
$(function () {
    var chart;
    $(document).ready(function() {
        chart_user('container_1');
		 chart_3('chart_3'); 
		//chart_4('chart_4');
    });
    
});


//用户统计
function chart_user(id){ 
	//声明报表对象  
	var chart = new Highcharts.Chart({  
		chart: {  
			//将报表对象渲染到层上  
			renderTo: id
		},title: {
			text: '云游戏近半月数据曲线图'
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
				text: ''
			},
			min: 0
		},tooltip: {
			formatter: function() {
				return ''+
					this.x + this.series.name +': '+ this.y +' ';
			}
		}, 
		
		//设定报表对象的初始数据  
		series: [{ 
			name: '当日新增用户', 
			data: [<?php echo $d1; ?>]          
		},{ 
			name: '当日新增设备', 
			data: [<?php echo $d2; ?>]         
		},{ 
			name: '当日新增有效用户', 
			data: [<?php echo $d3; ?>] 
		},{ 
			name: '最高并发', 
			data: [<?php echo $d4; ?>],
			visible:false  
		},{ 
			name: '游戏时间', 
			data: [<?php echo $d5; ?>],
			visible:false                    
		},{ 
			name: '主机游戏', 
			data: [<?php echo $d6; ?>],
			visible:false                    
		},{ 
			name: '投币次数', 
			data: [<?php echo $d7; ?>],
			visible:false                    
		},{ 
			name: '当日活跃', 
			data: [<?php echo $d8; ?>],  
			visible:false  
		},{ 
			name: '7天活跃', 
			data: [<?php echo $d9; ?>],
			visible:false                    
		},{ 
			name: '30天活跃', 
			data: [<?php echo $d10; ?>],
			visible:false                    
		}
		]  
	});  
  
} 


//图表三
function chart_3(id){
	chart = new Highcharts.Chart({
		chart: {
			renderTo: id,
			type: 'spline'
		},title: {
			text: '近半月售卡与用户开卡曲线图'
		},credits: {
			 text: '云游戏',
			 href: 'http://www.pyou.com/'
		},xAxis: {
			categories: [<?php echo $days; ?>],labels:{
				style:{
					font:'normal 13px 宋体',	
				}	
			}
		},
		yAxis: {
			title: {
				text: '值'
			},
			min: 0
		},
		tooltip: {
			formatter: function() {
				return ''+
					this.x + this.series.name +': '+ this.y +' 单位';
			}
		},
		
		series: [{
			name: '售卡数量',
			data: [<?php echo $jihuoshu; ?>]

		}, {
			name: '用户开卡数量',
			data: [<?php echo $shiyongshu; ?>]

		}]
	});	
}


$(document).ready(function(e) {
	tableresize(900);
	$('.cont_max').css('width','100%');
});
$(window).resize(function(e){
	tableresize(900);
	$('.cont_max').css('width','100%');
});

</script>

<div id="sendweixin" style="width: 400px; height: 200px; display: none;">
	<input type="hidden" id="fakeid" value="" /> <input type="hidden"
		id="nickname" value="" /> <input type="hidden" id="openid" value="" />
	<table width="100%" rules="none" border="0" cellpadding="0"
		cellspacing="0">
		<tr>
			<td style="padding-bottom: 10px; font-weight: bold;">发送内容</td>
		</tr>
		<tr>
			<td><textarea style="width: 340px; height: 100px;"
					id="weixinmsg"></textarea></td>
		</tr>
		<tr>
			<td><input type="button" value="发送"
				class="btn btn-warning btn-mini" onclick="javascript:sendweixin();" /></td>
		</tr>
	</table>
</div>



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