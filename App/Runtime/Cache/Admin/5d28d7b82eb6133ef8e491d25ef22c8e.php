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
		<h1>充值卡售卡统计汇总</h1>
	</div>
	<div id="breadcrumb">
		<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i
			class="icon-home"></i> 后台首页</a> <a href="javascript:;" class="current">统计图表</a>
		<a href="javascript:;" onClick="javascript:history.go(-1);"
			title="Go to Back" class="tip-bottom" id="gotoback"><i
			class="icon-share-alt"></i> 返回</a>
	</div>

	<div class="container-fluid">

		<div class="row-fluid">
			<div class="span12">
				<?php if(cookie('dealer_id')==1){ ?>
				<div class="sousuo">
					<form class="form-horizontal" action="" method="post"
						id="searchform">
						<table rules="none" border="0">
							<tbody>
								<tr>
									<td class="searchselect"><select name="dealer">
											<option value="">所有渠道</option>
											<?php if(is_array($dealerlist)): foreach($dealerlist as $key=>$row): ?><option value="<?php echo ($row["id"]); ?>"<?php
 if($dealer==$row['id']){ echo 'selected';} ?>><?php echo ($row["dealer_name"]); ?></option><?php endforeach; endif; ?>
									</select></td>
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
				<?php } ?>
				<!--两个并排的BOX-->
				<div class="row-fluid" style="margin-top: 0px;">
					<div class="span6">
						<div class="widget-box">
							<div class="widget-title">
								<span class="icon"><i class="icon-signal"></i></span>
								<h5>近半年销售汇总</h5>
								<div class="buttons">
									<a class="btn btn-mini" href="javascript:;"
										onClick="openchart(1);" title="查看大图"><i
										class="icon-eye-open"></i></a>
								</div>
							</div>
							<div class="widget-content nopadding">
								<div class="content" id="chart_1"></div>
							</div>
						</div>
					</div>
					<div class="span6">
						<div class="widget-box">
							<div class="widget-title">
								<span class="icon"><i class="icon-adjust"></i></span>
								<h5>本月销售比例</h5>
								<div class="buttons">
									<a class="btn btn-mini" href="javascript:;"
										onClick="openchart(2);" title="查看大图"><i
										class="icon-eye-open"></i></a>
								</div>
							</div>
							<div class="widget-content nopadding">
								<div class="content" id="chart_2"></div>
							</div>
						</div>
					</div>
				</div>
				<!--两个并排的BOX-->

				<!--一个通栏BOX-->
				<div class="row-fluid" style="margin-top: -16px;">
					<div class="span12">
						<div class="widget-box widget-calendar">
							<div class="widget-title">
								<span class="icon"><i class="icon-random"></i></span>
								<h5>近半月售卡与用户开卡比例</h5>
								<div class="buttons">
									<a class="btn btn-mini" href="javascript:;"
										onClick="openchart(3);" title="查看大图"><i
										class="icon-eye-open"></i></a>
								</div>
							</div>
							<div class="widget-content nopadding">
								<div class="content" id="chart_3" style="height: 320px;"></div>
							</div>
						</div>
					</div>
				</div>
				<!--一个通栏BOX-->

				<!--一个通栏BOX-->
				<!--
                        <div class="row-fluid" style="margin-top:-16px;">
                            <div class="span12">
                                <div class="widget-box widget-calendar">
                                    <div class="widget-title"><span class="icon"><i class="icon-random"></i></span><h5>销售与用户开卡比例</h5><div class="buttons"><a class="btn btn-mini"  href="javascript:;" onClick="openchart(3);" title="查看大图"><i class="icon-eye-open"></i></a></div></div>
                                    <div class="widget-content nopadding">
        								<div class="content" id="chart_4" style="height:320px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        -->
				<!--一个通栏BOX-->

				<div id="openchart" style="width: 1000px; height: 580px;"></div>


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
	$(function() {
		var chart;
		$(document).ready(function() {
			chart_1('chart_1');
			chart_2('chart_2');
			chart_3('chart_3');
			//chart_4('chart_4');
		});

	});
	//图表一
	function chart_1(id) {
		chart = new Highcharts.Chart({
			chart : {
				renderTo : id,
				type : 'column'
			},
			title : {
				text : '近半年销售汇总'
			},
			credits : {
				text : '云游戏',
				href : 'http://www.pyou.com/'
			},
			xAxis : {
				categories : [ <?php echo $yuefen; ?> ],
				labels : {
					style : {
						font : 'normal 13px 宋体',
					}
				}
			},
			yAxis : {
				min : 0,
				title : {
					text : '数量'
				}
			},
			legend : {
				layout : 'vertical',
				backgroundColor : '#FFFFFF',
				align : 'left',
				verticalAlign : 'top',
				x : 100,
				y : 20,
				floating : true,
				shadow : true
			},
			tooltip : {
				formatter : function() {
					return '' + this.x + ': ' + this.y + ' 张';
				}
			},
			plotOptions : {
				column : {
					pointPadding : 0.2,
					borderWidth : 0
				}
			},
			series : [ {
				name : '总销量',
				data : [ <?php echo $xiaoshouliang; ?> ]

			}, {
				name : '月卡销量',
				data : [ <?php echo $xiaoshou_yueka; ?> ]

			}, {
				name : '半年卡销量',
				data : [ <?php echo $xiaoshou_bannian; ?> ]

			}, {
				name : '年卡销量',
				data : [ <?php echo $xiaoshou_nianka; ?> ]

			} ]
		});
	}

	//图表二
	function chart_2(id) {
		chart = new Highcharts.Chart({
			chart : {
				renderTo : id,
				plotBackgroundColor : null,
				plotBorderWidth : null,
				plotShadow : false
			},
			title : {
				text : '当月售卡类型比例'
			},
			credits : {
				text : '云游戏',
				href : 'http://www.pyou.com/'
			},
			tooltip : {
				pointFormat : '{series.name}: <b>{point.percentage}%</b>',
				percentageDecimals : 1
			},
			plotOptions : {
				pie : {
					allowPointSelect : true,
					cursor : 'pointer',
					dataLabels : {
						enabled : false
					},
					showInLegend : true
				}
			},
			series : [ {
				type : 'pie',
				name : '所占比例',
				data : [ [ '年卡', <?php echo $nianka; ?> ],
						[ '半年卡', <?php echo $bannianka; ?> ],
						[ '月卡', <?php echo $yueka; ?> ] ]
			} ]
		});
	}

	//图表三
	function chart_3(id) {
		chart = new Highcharts.Chart({
			chart : {
				renderTo : id,
				type : 'spline'
			},
			title : {
				text : '近半月售卡与用户开卡比例'
			},
			credits : {
				text : '云游戏',
				href : 'http://www.pyou.com/'
			},
			xAxis : {
				categories : [ <?php echo $days; ?> ],
				labels : {
					style : {
						font : 'normal 13px 宋体',
					}
				}
			},
			yAxis : {
				title : {
					text : '数量'
				},
				min : 0
			},
			tooltip : {
				formatter : function() {
					return '' + this.x + this.series.name + ': ' + this.y
							+ ' 张';
				}
			},

			series : [ {
				name : '售卡数量',
				data : [ <?php echo $jihuoshu; ?> ]

			}, {
				name : '用户开卡数量',
				data : [ <?php echo $shiyongshu; ?> ]

			} ]
		});
	}

	//图表四
	function chart_4(id) {
		chart = new Highcharts.Chart({
			chart : {
				renderTo : id,
				type : 'spline'
			},
			title : {
				text : '今日在线峰值'
			},
			credits : {
				text : '云游戏',
				href : 'http://www.pyou.com/'
			},
			xAxis : {
				categories : [ '江岸区', '江汉区', '硚口区', '汉阳区', '武昌区', '青山区', '洪山区',
						'东西湖区', '汉南区', '蔡甸区', '江夏区', '黄陂区', '新洲区' ],
				labels : {
					style : {
						font : 'normal 13px 宋体',
					}
				}
			},
			yAxis : {
				title : {
					text : '数量'
				},
				min : 0
			},
			tooltip : {
				formatter : function() {
					return '' + this.x + this.series.name + ': ' + this.y
							+ ' 数量';
				}
			},

			series : [
					{
						name : '总销售量',
						data : [ 1000, 860, 543, 345, 1000, 860, 543, 345,
								1000, 860, 543, 345, 860 ]

					},
					{
						name : '年卡销售量',
						data : [ 280, 180, 234, 344, 280, 180, 234, 344, 280,
								180, 234, 344, 123, 89 ]

					},
					{
						name : '年卡销售量',
						data : [ 530, 550, 231, 23, 134, 530, 550, 231, 23, 34,
								530, 550, 231 ]

					},
					{
						name : '月卡销售量',
						data : [ 190, 100, 123, 323, 190, 100, 123, 323, 190,
								100, 123, 323, 121 ]

					} ]
		});
	}

	//图表五
	function chart_5(id) {
		//alert(id);
	}

	function openchart(id) {
		switch (id) {
		case 1:
			chart_1('openchart')
			break;
		case 2:
			chart_2('openchart')
			break;
		case 3:
			chart_3('openchart')
			break;
		case 4:
			chart_4('openchart')
			break;
		case 5:
			chart_5('openchart')
			break;
		case 6:
			chart_6('openchart')
			break;
		case 7:
			chart_7('openchart')
			break;
		case 8:
			chart_8('openchart')
			break;
		default:
			alert('错误');
		}
		art.dialog({
			title : '数据视图',
			content : document.getElementById("openchart"),
			width : 1000,
			height : 580,
			lock : true
		});
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