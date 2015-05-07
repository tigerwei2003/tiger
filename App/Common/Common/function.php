<?php
/*
 *过滤数组中字段组成新数组返回
*/
function arr_to_arr($arr,$field)
{
	$data=array();
	if(!is_array($arr) || !$arr)
	{
		return $data;
	}
	if(!is_array($field))
	{
		$field=explode(',', $field);
	}
	if(count($arr)==count($arr,1))
	{
		foreach ($field as $val)
		{
			$data[$val]=$arr[$val];
		}
	}else
	{
		$cnt=count($arr);
		foreach ($field as $val)
		{
			for($i=0;$i<$cnt;$i++)
			{
				$data[$i][$val]=$arr[$i][$val];
			}
		}
	}
	return $data;
}
/**
 * 通过淘宝接口获取isp和region
 */
function update_ip_info($new_ip, $old_ip="", $old_isp_id=0){
	// 如果是内网Ip，则不做判断
	if (strpos($new_ip, "10.") === 0 || strpos($new_ip, "192.168.") === 0 || strpos($new_ip, "172.") === 0 || strpos($new_ip, "127.") === 0)
		return array('region_id'=>-1,'isp_id'=>-1,'region'=>'','isp'=>'');
	$ret = array();
	if ($old_isp_id == 0 || $new_ip != $old_ip) {
		$json_ret = @file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=".$new_ip);
		$json_array = json_decode($json_ret, true);
		if ($json_array["code"] === 0) {
			$ret["region_id"] = $json_array["data"]["region_id"] == '' ? -1 : $json_array["data"]["region_id"];
			$ret["isp_id"] = $json_array["data"]["isp_id"] == '' ? -1 : $json_array["data"]["isp_id"];
			$ret["region"] = $json_array["data"]["region"];
			$ret["isp"] = $json_array["data"]["isp"];
			//$ret["country_id"] = $json_array["data"]["country_id"];
			return $ret;
		}
	}
	return false;
}
/**
 * 过滤昵称敏感词
 */
function filter_nickname($nickname){
	$memcache_key='sensitive_list';
	$sensitive_list = S($memcache_key);
	if( !$sensitive_list ){
		$file = WEB_ROOT.'/sensitive.txt';
		$content = file_get_contents($file);
		$sensitive_list = explode("\r\n", $content);
		if (count($sensitive_list) == 0) {
			$sensitive_list = explode("\r", $content);
		}
		S($memcache_key, $sensitive_list);
	}
	foreach( $sensitive_list as $val){
		if ($val == "")
			continue;
		if ( substr_count($nickname, $val ) > 0) {
			return false;
		}
	}
	return true;
}

/**
 * 邮件发送函数
 */
function sendMail($to, $subject, $content) {
	Vendor('PHPMailer.PHPMailerAutoload');
	$config=C('EMAIL_CONFIG');
	$mail = new PHPMailer(); //实例化
	$mail->IsSMTP(); // 启用SMTP
	$mail->SMTPAuth = true; //启用smtp认证
	$mail->SMTPSecure = 'ssl';                 // 使用安全协议
	$mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
	$mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
	$mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
	$mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
	$mail->From =$config['FROM_EMAIL']; //发件人地址（也就是你的邮箱地址）
	$mail->FromName = $config['FROM_NAME']; //发件人姓名
	$mail->AddAddress($to);
	$mail->WordWrap = 50; //设置每行字符长度
	$mail->IsHTML(false); // 是否HTML格式邮件
	$mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
	$mail->Subject =$subject; //邮件主题
	$mail->Body = $content; //邮件内容
	$mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的备用显示
	if(!$mail->Send()) {
		//echo "Mailer Error: " . $mail->ErrorInfo;
		return false;
	} else {
		return true;
	}
}
function write_cache ($file, $contents, $mod = 'w')
{
	$fp = fopen($file, $mod);
	flock($fp, LOCK_EX);
	fwrite($fp, $contents);
	flock($fp, LOCK_UN);
	fclose($fp);
}
function addslashes_deep ($value)
{
	if (empty($value)) {
		return $value;
	} else {
		return is_array($value) ? array_map('addslashes_deep', $value) :htmlspecialchars(addslashes(
				$value));
	}
}
/**
 * 编辑器
 **/
function editor($editor_name, $default = '', $editor_id = '') {
	$str = '';
	if(!defined('EDITOR_INIT')) {
		$str .= '<script type="text/javascript" src="'. __ROOT__ .'/Public/static/ckeditor/ckeditor.js"></script>';

		define('EDITOR_INIT', 1);
	}
	if (empty($editor_id)) $editor_id = preg_replace("/\[\]/", "_", $editor_name);
	return $str.'<textarea class="ckeditor" name="'.$editor_name.'" id="'.$editor_id.'" >'.$default.'</textarea>';
}



function upload_image($name, $default = '', $multi = true) {
	$str = '';
	if (!defined('INIT_UPLOAD_IMAGE')) {
		$str .= '<script type="text/javascript" src="'. __ROOT__ .'/Public/static/upload/upload_image.js"></script>';
		$str .= '<link rel="stylesheet" href="'. __ROOT__. '/Public/static/css/upload_img.css" />';
		define('INIT_UPLOAD_IMAGE', 1);
	}

	$show = '';
	$values = array();
	if (!empty($default)) {
		if (preg_match('/^(\d+,?)+$/', $default)) {
			$db = M('attachment');
			$rs = $db->where("id in (".$default.")")->select();
			$i = 0;
			foreach($rs as $line) {
				$values[$i]['id'] = $line['id'];
				$values[$i]['thumb'] =  dirname($line['filepath']). "/thumb_80_60_" . basename($line['filepath']);
				$values[$i]['imgurl'] = $line['filepath'];
				$i++;
			}
		} else {
			$i = 0;
			foreach(explode(",", $default) as $img) {
				if (empty($img)) continue;
				$values[$i]['id'] = '';
				if (strpos($img, 'http://') === false) {
					$values[$i]['thumb'] = thumb($img, 80, 80);
				} else {
					$values[$i]['thumb'] = $img;
				}
				$values[$i]['imgurl'] = $img;
				$i++;
			}
		}
	}

	$close = $multi ? '<div class="closeimg"><a href="javascript:;" onclick="javascript:g_remove_img(this);" class="iclose"></a></div>' : '';
	$arr = $multi ? '[]' : '';
	foreach($values as $row) {
		$show .= '<div class="oneimg">'.$close.'<div class="imgdiv"><div class="outline"><img src="'.$row['imgurl'].'"  height="60" alt="点击查看大图" onclick="art.dialog.open(\''.$row['imgurl'] .'\', {lock:true, title:\'查看大图\',width:500,height:500});" /></div></div><div style="display:none"><input type="checkbox" name="'.$name.'[id][]" value="'.$row['id'].'" checked="checked"/><input type="checkbox" name="'.$name.'[imgurl][]" value="'.$row['imgurl'].'" checked="checked"  /><input type="checkbox" name="'.$name.'[thumb][]" value="'.$row['thumb'].'" checked="checked"/></div></div>';

	}

	$str .= '
	<table rules="none" border="0" cellpadding="0" cellspacing="0" class="upload_table">
	<tr>
	<td align="left">
	<div>
	<input type="button" onclick="javascript:g_upload_image(\''.U('Admin/Attachment/img_upload'). '\',\''. $name .'\','. $multi.');" class="sim_add" value="上传图片" style="width:80px;height:24px;display:inline-block;" />
	<label style="margin:0px;display:inline-block;"><small>&nbsp;&nbsp;请上传小于 1M 的 JPG、PNG、GIF 类型图片'. ($multi?' (可多选)':'') .'</small></label>
	</div>
	</td>
	</tr>
	<tr>
	<td>
	<div id="'.$name.'_show" class="imgs_show">'.$show.'
	</div>
	</td>
	</tr>
	</table>';

	return $str;
}


function upload_file($name, $default = '', $multi = true,$type='zip') {
	$str = '';
	/*
	 if (!defined('INIT_UPLOAD_IMAGE')) {
	$str .= '<script type="text/javascript" src="'. __ROOT__ .'/html/upload/upload_file.js"></script>';
	$str .= '<link rel="stylesheet" href="'. __ROOT__. '/html/css/upload_img.css" />';
	define('INIT_UPLOAD_IMAGE', 1);
	}
	*/

	if($type=='mp3'){
		$filestype = 'mp3';
		$filestypename = '音乐';
	}else if($type=='mp4'){
		$filestype = 'mp4';
		$filestypename = '视频';
	}else if($type=='zip'){
		$filestype = 'zip,rar,7z';
		$filestypename = '文件';
	}else if($type=='pdf'){
		$filestype = 'pdf';
		$filestypename = 'PDF';
	}else if($type=='doc'){
		$filestype = 'doc,docx,xls,xlsx,ppt,pptx';
		$filestypename = '文档';
	}

	$show = '';
	$values = array();
	if (!empty($default)) {
		$i=0;
		foreach(explode(",", trim($default,',')) as $img=>$v) {
			$val = explode("|",$v);
			$values[$i]['title'] =  $val[0];
			$values[$i]['fileurl'] = $val[1];
			$i++;
		}
	}

	foreach($values as $row) {
		/*
		 $show .= '<div class="oneimg">'.$close.'<div class="imgdiv"><div class="outline"><img src="__ROOT__/html/upload/file_'.$type.'.jpg"  height="60" /></div></div><div style="display:none"><input type="checkbox" name="'.$name.'[id]'.$arr.'" value="'.$row['id'].'" checked="checked"/><input type="checkbox" name="'.$name.'[fileurl]'.$arr.'" value="'.$row['fileurl'].'" checked="checked"  /></div></div>';
		*/
		$show .='<table rules="none" border="0" cellpadding="0" cellspacing="0"><tr><td><input type="text" name="'.$name.'[filetitle][]" value="'.$row['title'].'"  style="width:100px;"/></td><td><input type="text" name="'.$name.'[fileurl][]" value="'.$row['fileurl'].'" readonly style="width:200px;"/></td><td><a href="javascript:;" onclick="javascript:g_remove_file(this);" class="iclose"></a></td></tr></table>';

	}

	$str .= '
	<script type="text/javascript" src="'. __ROOT__ .'/html/upload/upload_file.js"></script>
	<link rel="stylesheet" href="'. __ROOT__. '/html/css/upload_img.css" />
	<table rules="none" border="0" cellpadding="0" cellspacing="0" class="upload_table">
	<tr>
	<td align="left">
	<div>
	<input type="button" onclick="javascript:g_upload_file(\''.U('Admin/Attachment/upload_file',array('filestype'=>$type)). '\',\''. $name .'\','. $multi.',\''.$type.'\');" class="sim_add" value="上传'.$filestypename.'" style="width:80px;height:24px;display:inline-block;" />
	<label style="margin:0px;display:inline-block;"><small>&nbsp;&nbsp;请上传小于 10M 的 '.$filestype.' 类型'.$filestypename.''. ($multi?' (可多选)':'') .'</small></label>
	</div>
	</td>
	</tr>
	<tr>
	<td>
	<div id="'.$name.'_show" class="imgs_show">'.$show.'</div>
	</td>
	</tr>
	</table>';

	return $str;
}



function p($var, $stop = true)
{
	echo '<pre>';
	print_r($var);
	echo '</pre>';
	if ($stop) die();
}


//信息类型
function shownewstype($id) {
	if($id){
		$row = M('article_type')->find($id);
		return $row['typename'];
	}
}

function countnews($id,$siteid){
	if($id){
		$row = M('article')->where(array('type'=>$id,'siteid'=>$siteid))->count();
		return $row;
	}
}


function checkcode($width='',$height='', $fontsize='', $charset=''){
	if (!empty($width)) $param .='&width='.$width;
	if (!empty($height)) $param .='height='.$height;
	if (!empty($charset)) $param .='charset='.$charset;
	if (!empty($fontsize)) $param .='fontsize='.$fontsize;

	return '__ROOT__/op.php?m=Api&a=checkcode'.$param;
}


function send_mail($sendto_email,$name,$subject,$body,$attachment=''){
	$config = C('EMAIL_CONFIG');
	vendor('PHPMailer.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
	$mail = new PHPMailer();
	$mail->IsSMTP();                  // send via SMTP
	$mail->Host = $config['SMTP_HOST'];   // SMTP servers
	$mail->SMTPAuth = true;           // turn on SMTP authentication
	if($config['SMTP_PORT'] != 25){
		$mail->SMTPSecure = 'ssl';              // 使用安全协议
		$mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
	}
	$mail->Username = $config['SMTP_USER'];     // SMTP username  注意：普通邮件认证不需要加 @域名
	$mail->Password = $config['SMTP_PASS']; // SMTP password
	$mail->From = $config['FROM_EMAIL'];      // 发件人邮箱
	$mail->FromName =  $config['FROM_NAME'];  // 发件人

	$mail->CharSet = "utf-8";   // 这里指定字符集！
	$mail->Encoding = "base64";
	$mail->AddAddress($sendto_email, $name);  // 收件人邮箱和姓名
	$replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
	$replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
	$mail->AddReplyTo($replyEmail,$replyName);
	// 添加附件
	if(is_array($attachment)){
		foreach ($attachment as $file){
			is_file($file) && $mail->AddAttachment($file);
		}
	}else{
		is_file($attachment) && $mail->AddAttachment($attachment);
	}
	// send as HTML
	$mail->IsHTML(true);
	// 邮件主题
	$mail->Subject = $subject;
	// 邮件内容
	$mail->Body = $body;
	$mail->AltBody ="text/html";
	return $mail->Send() ? 0 : $mail->ErrorInfo;
}

function ip() {
	return get_client_ip();
}

/**
 * 生成缩略图函数
 * @param  $imgurl 图片路径
 * @param  $width  缩略图宽度
 * @param  $height 缩略图高度
 * @param  $autocut 是否自动裁剪 默认不裁剪，当高度或宽度有一个数值为0是，自动关闭
 * @param  $smallpic 无图片是默认图片路径
 */
function thumb($imgurl, $width = 80, $height = 80 ,$autocut = 0, $nopic = 'html/img/noimage.gif') {

	if(empty($imgurl)) return __ROOT__ . '/' . $nopic;   //判断原图路径是否输入

	if(!extension_loaded('gd') || strpos($imgurl, '://')) return $imgurl;

	$root_path =  __ROOT__ . '/' ;
	if (strpos($imgurl, $root_path) === 0) {
		$imgurl_replace = substr_replace($imgurl, '', 0, strlen($root_path));
	} else {
		$imgurl_replace = $imgurl;
	}

	if(!file_exists($imgurl_replace)) return  __ROOT__ . '/' .$nopic; //判断原图是否存在

	$newimgurl = dirname($imgurl_replace).'/thumb_'.$width.'_'.$height.'_'.basename($imgurl_replace);   //缩略图路径

	if(file_exists($newimgurl)) return __ROOT__ . '/' . $newimgurl;  //如果缩略图存在则直接输入

	import('ORG.Util.Image');

	if ($autocut) {
		$dst = Image::thumb2($imgurl_replace, $newimgurl, '', $width, $height);
	} else {
		$dst = Image::thumb3($imgurl_replace, $newimgurl, '', $width, $height);
	}

	return empty($dst) ? $imgurl : __ROOT__ . '/' . $dst;
}

/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function password($password, $encrypt='') {
	$pwd = array();
	$pwd['encrypt'] =  $encrypt ? $encrypt : create_randomstr();
	$pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
	return $encrypt ? $pwd['password'] : $pwd;
}
/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function create_randomstr($lenth = 6) {
	return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}

/**
 * 产生随机字符串
 *
 * @param    int        $length  输出长度
 * @param    string     $chars   可选的 ，默认为 0123456789
 * @return   string     字符串
 */
function random($length, $chars = '0123456789') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}


/**
 * 记录日志
 *
 * @param    int        $type  日志类型，1:增加;2:修改;3:删除
 * @return   string     $sql   操作SQL语句
 * @param    string     $text  日志说明
 */
function systemlog($type,$table='',$sql='',$text='') {
	$info['log_type'] = $type;                 //日志类型
	$info['userid'] = cookie('userid');        //管理员ID
	$info['nickname'] = cookie('nickname');    //管理员名称
	$info['recording_time'] = time();          //记录时间
	$info['log_text'] = $text;                 //日志说明
	$info['log_sql'] = $sql;                   //操作SQL语句
	$info['table'] = $table;                   //操作数据表

	$row = M('system_log')->add($info);
	return $row;
}


/**
 * 日志类型
 *
 */
function log_type($type) {
	switch ($type)
	{
		case 1:
			return '<span class="green">增加数据</span>';
			break;
		case 2:
			return '<span class="blue">修改数据</span>';
			break;
		case 3:
			return '<span class="red">删除数据</span>';
			break;
		case 4:
			return '<span>后台登录</span>';
			break;
		default:
			return '<span class="gray">其他类型</span>';
	}

}


/**
 * 游戏类型
 *
 */
function game_type() {
	$data = array(
			'1'=>'主机游戏',
			'2'=>'街机游戏',
			'3'=>'网络游戏'
	);
	return $data;

}

/**
 * 数字转字母 （类似于Excel列标）
 * @param Int $index 索引值
 * @param Int $start 字母起始值
 * @return String 返回字母
 * @author Anyon Zou <Anyon@139.com>
 * @date 2013-08-15 20:18
 */
function IntToChr($index, $start = 65) {
	$str = '';
	if (floor($index / 26) > 0) {
		$str .= IntToChr(floor($index / 26)-1);
	}
	return $str . chr($index % 26 + $start);
}


/**
 * 导出EXCEL
 *
 * @param    string        $filename  导出文件名
 * @return   array         $title     标题,一维数组
 * @param    array         $content   导出内容,一维数组
 */

function export_excel($filename,$title,$content){
	import("@.Classes.PHPExcel");

	//开始准备导出
	$objPHPExcel = new PHPExcel();

	//数据
	$sheet = $objPHPExcel->setActiveSheetIndex(0);

	//标题
	$n=0;
	foreach($title as $v){
		$sheet->setCellValue(IntToChr($n).'1', $v);
		$n++;
	}

	//详细数据
	$aaaa = '';
	$m=2;
	foreach($content as $row){
		$j=0;
		foreach($title as $k=>$v){
			$kn = explode(".",$k);
			if(strpos($v, '时间')){
				$text =  date('Y-m-d H:i:s',$row[$kn[1]]);
			}else if($kn[1]=='game_id'){
				$text = $row['game_name'];
			}else{
				$text = $row[$kn[1]];
			}
			$sheet->setCellValue(IntToChr($j).($m), $text);
			$j++;
		}
		$m++;
	}


	//工作表名
	$objPHPExcel->getActiveSheet()->setTitle('预约记录');

	//设置默认行高
	$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);


	//导出
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
}


function merge_node($node, $access, $pid = 0) {
	$arr = array();
	foreach ($node as $v) {
		if (is_array($access)) {
			$v['access'] = in_array($v['id'], $access) ? 1 : 0;
		}
		if ($v['pid'] == $pid) {
			$v['child'] = merge_node($node, $access, $v['id']);
			$arr[] = $v;
		}
	}
	return $arr;
}

/**
 * 判断菜单是否显示
 * @text String 如"Index/index"
 * @style 1为有权限显示、0为无权限显示
 */
function rolemenu($text){
	$text = strtoupper($text);
	$menu = array();
	//默认样式
	$style =  0;
	//判断是否为开发者权限
	if(cookie('username')==C('RBAC_SUPER_ADMIN')){
		$style = 1;
	}else{
		if($_SESSION['_ACCESS_LIST']){
			if(is_array($_SESSION['_ACCESS_LIST']['ADMIN'])){
				foreach($_SESSION['_ACCESS_LIST']['ADMIN'] as $k=>$v){
					foreach($v as $a=>$b){
						$menu[] = $k.'/'.$a;
					}
				}
				if(in_array($text,$menu)) $style = 1;
			}
		}
	}
	return $style;
}


/*剩余时间*/
function remainingtime($time,$days){

	if($time){
		//到期时间
		$endday = $time+$days;
		//剩余时间
		$stime = $endday-time();
		if($stime>0){
			//$sd = floor($stime/86400).'天';
			$sd = ceil($stime/86400).'天';
		}else{
			$sd = '已过期';
		}
	}else{
		$sd = '未使用';
	}

	return $sd;
}

//用户关系递归
function Userrelation($id = 0) {
	global $str;
	$db = M('admins');
	$guanxibiao = $db->field('id,parentid')->where(array('parentid'=>$id))->select();
	if($guanxibiao){
		foreach ($guanxibiao as $row){
			$str .= $row['id']. ",";
			Userrelation($row['id']);
		}
	}
	return $str;
}

//角色关系递归
function Rolerelation($id = 0) {
	global $str;
	$db = M('roles');
	$guanxibiao = $db->field('id,parentid')->where(array('parentid'=>$id))->select();
	if($guanxibiao){
		foreach ($guanxibiao as $row){
			$str .= $row['id']. ",";
			Rolerelation($row['id']);
		}
	}
	return $str;
}

//渠道关系递归
function Dealerrelation($id = 0) {
	global $str;
	$db = M('dealer');
	$guanxibiao = $db->field('id,parentid')->where(array('parentid'=>$id))->select();
	if($guanxibiao){
		foreach ($guanxibiao as $row){
			$str .= $row['id']. ",";
			Dealerrelation($row['id']);
		}
	}
	return $str;
}

//获取当月第一天与最后一天
function getthemonth($date){
	$firstday = date('Ym01', strtotime($date));
	$lastday = date('Ymd', strtotime("$firstday +1 month -1 day"));
	return array($firstday,$lastday);
}

//获取最近半月
function months($type=0){

	for($i=0;$i<6;$i++){
		$y = date("Y");
		$m = date("m")-$i;
		if($m<=0){
			$m = sprintf("%02d",(date("m")+12)-$i);
			$y = $y-1;
		}else{
			$m = sprintf("%02d",$m);
		}
		if($type){
			$daxie = array('一','二','三','四','五','六','七','八','九','十','十一','十二');
			$month[] = 	$daxie[$m-1].'月';
		}else{
			$month[] = 	$y.'-'.$m;
		}
	}
	return array_reverse($month);
}
/**
 * @desc 上传图片方法（自定义）
 * @param $model 上传的模型
 * @param $paramval  $game_id等唯一的获取图片的参数
 * @param string $name
 * @param string $default
 * @param string $multi
 * @return string
 */
function dsy_upload_image($model,$name, $paramval='', $default = '', $multi = true){
	$str = '';
	if (!defined('INIT_UPLOAD_IMAGE')) {
		$str .= '<script type="text/javascript" src="'. __ROOT__ .'/Public/static/upload/upload_image.js"></script>';
		$str .= '<link rel="stylesheet" href="'. __ROOT__. '/Public/static/css/upload_img.css" />';
		define('INIT_UPLOAD_IMAGE', 1);
	}
	//
	$show = '';
	$values = array();
	if (!empty($default)) {
		if (preg_match('/^(\d+,?)+$/', $default)) {
			$db = M('attachment');
			$rs = $db->where("id in (".$default.")")->select();
			$i = 0;
			foreach($rs as $line) {
				$values[$i]['id'] = $line['id'];
				$values[$i]['thumb'] =  dirname($line['filepath']). "/thumb_80_60_" . basename($line['filepath']);
				$values[$i]['imgurl'] = $line['filepath'];
				$i++;
			}
		} else {
			$i = 0;
			foreach(explode(",", $default) as $img) {
				if (empty($img)) continue;
				$values[$i]['id'] = '';
				if (strpos($img, 'http://') === false) {
					$values[$i]['thumb'] = dsy_thumb_image($img, 80, 80);
				} else {
					$values[$i]['thumb'] = $img;
				}
				$values[$i]['imgurl'] = $img;
				$i++;
			}
		}
	}

	$close = $multi ? '<div class="closeimg"><a href="javascript:;" onclick="javascript:g_remove_img(this);" class="iclose"></a></div>' : '';
	$arr = $multi ? '[]' : '';
	foreach($values as $row) {
		$show .= '<div class="oneimg">'.$close.'<div class="imgdiv"><div class="outline"><img src="'.$row['imgurl'].'"  height="60" alt="点击查看大图" onclick="art.dialog.open(\''.$row['imgurl'] .'\', {lock:true, title:\'查看大图\',width:500,height:500});" /></div></div><div style="display:none"><input type="checkbox" name="'.$name.'[id][]" value="'.$row['id'].'" checked="checked"/><input type="checkbox" name="'.$name.'[imgurl][]" value="'.$row['imgurl'].'" checked="checked"  /><input type="checkbox" name="'.$name.'[thumb][]" value="'.$row['thumb'].'" checked="checked"/></div></div>';

	}

	$str .= '
	<table rules="none" border="0" cellpadding="0" cellspacing="0" class="upload_table">
	<tr>
	<td align="left">
	<div>
	<input type="button" onclick="javascript:g_upload_image(\''.U('Admin/DsyAttachment/img_upload?model='.$model.'&param_field='.$paramval). '\',\''. $name .'\','. $multi.');" class="sim_add" value="上传图片" style="width:80px;height:24px;display:inline-block;" />
	<label style="margin:0px;display:inline-block;"><small>&nbsp;&nbsp;请上传小于 1M 的 JPG、PNG、GIF 类型图片'. ($multi?' (可多选)':'') .'</small></label>
	</div>
	</td>
	</tr>
	<tr>
	<td>
	<div id="'.$name.'_show" class="imgs_show">'.$show.'
	</div>
	</td>
	</tr>
	</table>';

	return $str;
}
/**
 * @desc 缩略图方法
 * @param string $imgurl
 * @param number $width
 * @param number $height
 * @param number $autocut
 * @param string $nopic
 * @return string|unknown|Ambigous <string, unknown>
 */
function dsy_thumb_image($imgurl, $width = 80, $height = 80 ,$autocut = 0, $nopic = 'html/img/noimage.gif') {

	if(empty($imgurl)) return __ROOT__ . '/' . $nopic;   //判断原图路径是否输入

	if(!extension_loaded('gd') || strpos($imgurl, '://')) return $imgurl;

	$root_path =  __ROOT__ . '/' ;
	if (strpos($imgurl, $root_path) === 0) {
		$imgurl_replace = substr_replace($imgurl, '', 0, strlen($root_path));
	} else {
		$imgurl_replace = $imgurl;
	}

	if(!file_exists($imgurl_replace)) return  __ROOT__ . '/' .$nopic; //判断原图是否存在

	$newimgurl = dirname($imgurl_replace).'/thumb_'.$width.'_'.$height.'_'.basename($imgurl_replace);   //缩略图路径

	if(file_exists($newimgurl)) return __ROOT__ . '/' . $newimgurl;  //如果缩略图存在则直接输入

	import('ORG.Util.Image');

	if ($autocut) {
		$dst = Image::thumb2($imgurl_replace, $newimgurl, '', $width, $height);
	} else {
		$dst = Image::thumb($imgurl_replace, $newimgurl, '', $width, $height);
	}

	return empty($dst) ? $imgurl : __ROOT__ . '/' . $dst;
}

