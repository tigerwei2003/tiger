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
				<h1>每日渠道统计</h1>
			</div>
			<div id="breadcrumb">
				<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> 后台首页</a>
				<a href="javascript:;" class="current">每日渠道统计</a>
                <a href="javascript:;" onClick="javascript:history.go(-1);" title="Go to Back" class="tip-bottom" id="gotoback"><i class="icon-share-alt"></i> 返回</a>
			</div>
            
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span12">
                        <!--一个通栏BOX-->
                        <div class="row-fluid phonenone" style="margin-top:-5px;">
                            <div class="span12">
                                <div class="widget-box widget-calendar">
                                    <div class="widget-title">
                                    	<span class="icon"><i class="icon-random"></i></span>
                                        <h5 id="charttitle">近半月数据</h5>
                                    </div>
                                    <div class="widget-content nopadding" style="overflow:hidden;">
                                        <div class="chartcontent" id="container_1"></div>
                                        <div class="chartcontent" id="container_2" style="display:none;"></div>
                                        <div class="chartcontent" id="container_3" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--一个通栏BOX-->      
                        <div class="sousuo">
                        <form class="form-horizontal" action="" method="post" id="searchform">
                    
                        <table rules="none" border="0">
                            <tbody>
                            	<tr>
                                	<td class="searchselect">
                                    <select name="">
                                        <option value="">渠道提示</option>
                                        <?php if(is_array($device)): $i = 0; $__LIST__ = $device;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><option value="<?php echo ($data['pid']); ?>" <?php if($data['pid']==$pid){echo 'selected';} ?> ><?php echo ($data['pid']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>   
                                    </select>
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="pid"  value="<?php if($pid){ echo $pid;}else{ echo '渠道';} ?>" onBlur="blur_input(this,'渠道')" onClick="click_input(this,'渠道')">
                                    </td>
                                    <td style="padding-right:0">
                                    <input type="text" name="startdate" value="<?php if($startdate){ echo $startdate;}else{echo '开始时间';} ?>" onBlur="blur_input(this,'开始时间')" onClick="click_input(this,'开始时间')" >
                                    </td>
                                    <td>-</td>
                                    <td>
                                    <input type="text" name="enddate"  value="<?php if($enddate){ echo $enddate;}else{echo '结束时间';} ?>" onBlur="blur_input(this,'结束时间')" onClick="click_input(this,'结束时间')" >
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
								<span class="icon"><i class="icon-leaf"></i></span>
								<h5>每日渠道统计明细</h5>
                               
                                <a href="<?php echo U('Export/pidchart',array('pid'=>$pid,'startdate'=>$startdate,'enddate'=>$enddate));?>"><span class="label btn-warning"><i class=" icon-download-alt icon-white"></i> 导出</span></a>
                                
							</div>
							<div class="widget-content">
                            	<div class="cont_max">
								<table class="table table-bordered table-striped with-check" id="maxtable">
									<thead>
										<tr>
											<th>日期</th>
                                            <th>渠道</th>
                                            <th>活跃设备</th>
                                            <th>活跃用户</th>
                                            <th>新增设备</th>
                                            <th>新增用户</th>
                                            <th>新增活跃</th>
                                            <th>总游戏时间</th>
                                            <th>新增游戏时间</th>
                                            <th>最高在线</th>
                                            <th>启动游戏次数</th>
                                            <th>新增启动游戏次数</th>
                                            <th>试玩次数</th>
                                            <th>新增试玩次数</th>
                                            <th>按次游戏次数</th>
                                            <th>新增按次游戏次数</th>
                                            <th>购买包月的次数</th>
                                            <th>新增购买包月的次数</th>
                                            <th>最近7天活跃</th>
                                            <th>最近30天活跃</th>
                                            <th>历史累计活跃用户</th>
                                            <th>历史累计活跃设备</th>
                                            <th>历史累计游戏时间</th>
                                            <th>更新时间</th>
										</tr>
									</thead>
									<tbody>
                                    <?php if(is_array($datalist)): foreach($datalist as $key=>$row): ?><tr>
											<td><?php echo ($row["date"]); ?></td>
                                            <td><?php echo ($row["pid"]); ?></td>
                                            <td><?php echo ($row["daily_active_device"]); ?></td>
                                            <td><?php echo ($row["daily_active_user"]); ?></td>
                                            <td><?php echo ($row["daily_new_device"]); ?></td>
                                            <td><?php echo ($row["daily_new_account"]); ?></td>
                                            <td><?php echo ($row["daily_new_active_user"]); ?></td>
                                            <td><?php echo ($row["daily_play_time"]); ?></td>
                                            <td><?php echo ($row["daily_new_play_time"]); ?></td>
                                            <td><?php echo ($row["max_concurrent_user"]); ?></td>
                                            <td><?php echo ($row["daily_start_count"]); ?></td>
                                            <td><?php echo ($row["daily_new_start_count"]); ?></td>
                                            <td><?php echo ($row["daily_trial_count"]); ?></td>
                                            <td><?php echo ($row["daily_new_trial_count"]); ?></td>
                                            <td><?php echo ($row["daily_times_nums"]); ?></td>
                                            <td><?php echo ($row["daily_new_times_nums"]); ?></td>
                                            <td><?php echo ($row["daily_payment_nums"]); ?></td>
                                            <td><?php echo ($row["daily_new_payment_nums"]); ?></td>
                                            <td><?php echo ($row["weekly_active_user"]); ?></td>
                                            <td><?php echo ($row["monthly_active_user"]); ?></td>
                                            <td><?php echo ($row["accumulated_active_user"]); ?></td>
                                            <td><?php echo ($row["accumulated_active_device"]); ?></td>
                                            <td><?php echo ($row["accumulated_play_time"]); ?></td>
                                            <td><?php echo (date('Y-m-d H:i:s',$row["update_time"])); ?></td>
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
<script src="/gloudapi2/Public/static/charts/highcharts.js" type="text/javascript"></script>
<script src="/gloudapi2/Public/static/charts/modules/exporting.js" type="text/javascript"></script>
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
$(document).ready(function(e) {
	var chart;
    chart_user('container_1');
	//chart_game('container_2');
	//chart_huoyue('container_3');
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
			name: '新增用户', 
			data: [<?php echo $d1; ?>]          
		},{ 
			name: '新增设备', 
			data: [<?php echo $d2; ?>]         
		},{ 
			name: '活跃用户', 
			data: [<?php echo $d3; ?>]         
		},{ 
			name: '总游戏时间', 
			data: [<?php echo $d4; ?>]         
		},{ 
			name: '最高在线', 
			data: [<?php echo $d5; ?>] 
		},{ 
			name: '试玩次数', 
			data: [<?php echo $d6; ?>] 
		},{ 
			name: '启动游戏次数', 
			data: [<?php echo $d7; ?>],
			visible:false                    
		},{ 
			name: '活跃设备', 
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
		,{ 
			name: '新增活跃', 
			data: [<?php echo $d11; ?>],
			visible:false                    
		}
		,{ 
			name: '新增游戏时间', 
			data: [<?php echo $d12; ?>],
			visible:false                    
		}
		,{ 
			name: '新增试玩次数', 
			data: [<?php echo $d13; ?>],
			visible:false                    
		}
		,{ 
			name: '新增启动游戏次数', 
			data: [<?php echo $d14; ?>],
			visible:false                    
		}
		,{ 
			name: '按次游戏次数', 
			data: [<?php echo $d15; ?>],
			visible:false                    
		}
		,{ 
			name: '购买包月的次数', 
			data: [<?php echo $d16; ?>],
			visible:false                    
		}
		,{ 
			name: '新增按次游戏次数', 
			data: [<?php echo $d17; ?>],
			visible:false                    
		}
		,{ 
			name: '新增购买包月次数', 
			data: [<?php echo $d18; ?>],
			visible:false                    
		}
		]  
	});  
  
} 

/*
//游戏统计
function chart_game(id){ 
	//声明报表对象  
	var chart = new Highcharts.Chart({  
		chart: {  
			//将报表对象渲染到层上  
			renderTo: id
		},title: {
			text: '游戏统计'
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
				text: '次数'
			},
			min: 0
		},tooltip: {
			formatter: function() {
				return ''+
					this.x + this.series.name +': '+ this.y +' 次数';
			}
		}, 
		
		//设定报表对象的初始数据  
		series: [{ 
			name: '主机游戏', 
			data: [<?php echo $d4; ?>]          
		},{ 
			name: '街机投币', 
			data: [<?php echo $d5; ?>]          
		}]  
	});  
  
} 

//活跃统计
function chart_huoyue(id){ 
	//声明报表对象  
	var chart = new Highcharts.Chart({  
		chart: {  
			//将报表对象渲染到层上  
			renderTo: id
		},title: {
			text: '活跃统计'
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
				text: '数量'
			},
			min: 0
		},tooltip: {
			formatter: function() {
				return ''+
					this.x + this.series.name +': '+ this.y +' 数量';
			}
		}, 
		
		//设定报表对象的初始数据  
		series: [{ 
			name: '当日活跃', 
			data: [<?php echo $d6; ?>]          
		},{ 
			name: '7天活跃', 
			data: [<?php echo $d7; ?>]          
		},{ 
			name: '30天活跃', 
			data: [<?php echo $d8; ?>]          
		}]  
	});  
  
} 


//切换图标
function getForm(obj){
	var type = $(obj).val();
	$('.chartcontent').hide();
	$('#container_'+type).show();
}
*/


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