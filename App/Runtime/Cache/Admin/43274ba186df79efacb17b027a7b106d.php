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
	/*$(document).ready(function(){
		$.formValidator.initConfig({autotip:true,formid:"basic_validate",onerror:function(msg){}});
		$("#pid").formValidator({onshow:"请选择渠道",onfocus:"渠道不能为空"}).inputValidator({min:1,onerror:"请选择渠道！"});
		$("#source").formValidator({onshow:"请输入来源作为记录",onfocus:"请输入来源作为记录"}).inputValidator({min:1,onerror:"请输入来源作为记录！"});
		$("#expire_time").formValidator({onshow:"请输入过期时间，如2014-10-30",onfocus:"请输入过期时间，如2014-10-30"}).inputValidator({min:1,onerror:"请输入过期时间，如2014-10-30！"});
		$("#chargepoint_id").formValidator({onshow:"请选择游戏包",onfocus:"请选择游戏包"}).inputValidator({min:1,onerror:"请选择游戏包！"});
		$("#card_id_prefix").formValidator({onshow:"请输入批次",onfocus:"请输入批次"}).inputValidator({min:6,max:6,onerror:"请输入批次(默认6位)!"});
	});*/
</script>
<link rel="stylesheet" href="/gloudapi2/Public/static/css/uniform.css" />

		<div id="content">
			<div id="content-header">
				<h1>多选卡生成</h1>
			</div>
			<div id="breadcrumb">
				<a href="<?php echo U('Index/index');?>" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> 后台首页</a>
				<a href="<?php echo U('Code/rechargecard');?>" class="current">多选卡管理</a>
                <a href="javascript:;" onClick="javascript:history.go(-1);" class="tip-bottom" id="gotoback"><i class="icon-share-alt"></i> 返回</a>
			</div>
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span12">
                        
						<div class="widget-box">
							<div class="widget-title">
                            	<span class="icon"><i class="icon-pencil"></i></span>
								<h5>编辑多选卡值卡</h5>
                               
                                <a href="<?php echo U('Code/rechargecard');?>"><span class="label btn-primary"><i class=" icon-list-alt icon-white"></i> 多选卡管理</span></a>
                               
							</div>
							<div class="widget-content nopadding">
								<form class="form-horizontal" method="post" action="<?php echo U('Codecard/card_create');?>" name="basic_validate" id="basic_validate" enctype="multipart/form-data" novalidate >
                                <input type="hidden" name="dosubmit" value="1" />
                                <input type="hidden" name="card_id" value="<?php echo ($row["card_id"]); ?>" />
                                <input type="hidden" name="card_pass" value="<?php echo ($row["card_pass"]); ?>" />
                                    <?php if(!$row): ?><div class="control-group">
                                        <label class="control-label">渠道：</label>
                                        <div class="controls">
                                            <select name="pid" id="pid">
                                            <option value="1">请选择渠道</option>
                                            <?php if(is_array($dealerlist)): foreach($dealerlist as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if($v['id']==$row['dealer_id']) echo 'selected'; ?> ><?php echo ($v["dealer_name"]); ?></option><?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label class="control-label">批次</label>
                                        <div class="controls">
                                            <input type="text" value="123456" name="card_id_prefix" id="card_id_prefix" style="float: left;"/>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label class="control-label">生成数量</label>
                                        <div class="controls">
                                            <select name="codenum">
                                            	<option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                <option value="10">10</option>
                                                <option value="20">20</option>
                                                <option value="50">50</option>
												<option value="100">100</option>
												<option value="200">200</option>
												<option value="500">500</option>
                                                <option value="1000">1000</option>
                                                <option value="1000">2000</option>
                                            </select>
                                        </div>
                                    </div><?php endif; ?>
                                    <?php if($row): ?><div class="control-group">
                                        <label class="control-label">充值卡卡号</label>
                                        <div class="controls">
                                            <input type="text" value="<?php echo ($row["card_id"]); ?>" readonly/>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label class="control-label">充值卡密码</label>
                                        <div class="controls">
                                            <input type="text" value="<?php echo ($row["card_pass"]); ?>" readonly/>
                                        </div>
                                    </div><?php endif; ?>
                                    <div class="control-group">
                            	<label class="control-label">关联计费点：</label>
                                <div class="controls">
                                	<div class="game_names">
                                    	<ul>
                                        <?php if(is_array($gamepace)): foreach($gamepace as $key=>$row): ?><li><label class="lleft"><input type="checkbox" name="game_id[]" value="<?php echo ($row["id"]); ?>|<?php echo urlencode($row['name']);?>"></label> <label class="lright"><?php echo ($row["name"]); ?></label>	</li><?php endforeach; endif; ?>
                                        </ul>
                                    </div>
                                </div>
                              </div>
                                    <div class="control-group">
                                        <label class="control-label">来源</label>
                                        <div class="controls">
                                            <input type="text" name="source" value="<?php echo ($row["source"]); ?>" id="source"/>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label class="control-label">过期时间</label>
                                        <div class="controls">
                                            <input type="text" name="expire_time" value="<?php if($row['expire_time']){ echo date('Y-m-d',$row['expire_time']);} ?>" id="expire_time"  data-date-format="yyyy-mm-dd" class="datepicker"/>
                                        </div>
                                    </div>
                                 <!--   <div class="control-group">
                                        <label class="control-label">是否需要激活</label>
                                        <div class="controls">
                                        	<select name="info[valid]" id="status">
                                                <option value="0" <?php if($row['valid']=='0'){ echo 'selected';}?> >需要激活
                                                <option value="1" <?php if($row['valid']=='1'){ echo 'selected';}?> >免激活
                                            </select>
                                        </div>
                                    </div>
                                    -->
                                    <div class="form-actions">
                                        <input type="submit" value="保存" class="btn btn-primary" />
                                    </div>
                                </form>
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