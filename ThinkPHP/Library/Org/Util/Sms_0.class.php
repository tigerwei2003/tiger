<?php
namespace Org\Util;
/**
 * @author chunyingXu
 * @time 2014/07/31
 * @desc :宜信通平台http接口短信发送API
 */
class Sms_0{
	/* //宜信通返回下行响应错误码
		const SMS_SUCCESS = 0; //成功
	const SMS_AUTH_ERR = -1; //认证错误
	const SMS_PHONE_NO_ERR = -2; //手机号码错误
	const SMS_CONTENT_LENTTH_ERR = -3; //消息长度错误
	const SMS_CONTERNT_ERR = -4; //消息内容错误
	const SMS_PRODUCT_ID_ERR = -5; //产品编号错误
	const SMS_NO_ALLOW_GROUP_SEND = -6; //不允许群发
	const SMS_TIMEOUT_ERR = -7; //发送超时错误
	const SMS_STEAM_CONTROL_ERR = -8; //流量控制错误
	const SMS_SEND_FAIL_ERR = -9; //发送失败错
	const SMS_BLACK_LIST = -10; //黑名单
	const SMS_NO_IN_ORDER_USER_ERR = -11; //非订购用户
	const SMS_ROUTE_ERR = -12; //路由处理错
	const SMS_PROGRAM_ABORT = -13; //程序终止清空滑动窗口
	const SMS_REPEAT_MESSAGE = -14; //消息重复
	const SMS_SAVE_DATA_IN_HUB_FAIL = -15; //数据存入Hub失败
	const SMS_MODULE_ROUTE_FAIL = -16; //模块路由失败
	const SMS_SETTIME_IS_INVALID = -17; //定时时间不在有效期内
	const SMS_PERSONAL_STYLE_ERR = -18; //个性化短信模板匹配错误
	const SMS_RESOURCE_CENTER_RETURN_ERR = -19; //资源中心返回rid为null
	const SMS_VALID_TIME_NOT_SEND = -20; //超时未发
	const SMS_REPORTED_SIGNATURE_ERR = -21; //报备签名错误
	const SMS_GATE_PIPE_SENSITIVE_WORD = -22; //网关通道敏感词
	const SMS_GATE_PIPE_BLACK_LIST = -23; //网关通道黑名单
	const SMS_OVER_PAY_MONEY = -24; //预付费超量
	const SMS_INTERFACE_NOT_PASSED = -25; //接口审核未通过
	const SMS_INTERFACE_VERIFY_TIMEOUT = -26; //接口审核超时
	const SMS_PHONE_NO_REPEAT = -27; //手机号码重复
	const SMS_MMS_GET_HANDLER_FAIL = -28; //彩信获取资源失败
	const SMS_REEAT_FILTER = -29; //重复过滤 */
	private static $cid = "8201"; //客户端userid
	private static $pwd = "123456"; //客户端pwd
	private static $productid = "201407301";//通道组id，由宜信通平台分配
	private static $sms_url = "http://58.68.247.137:9053/communication/sendSms.ashx"; //短信端口
	private static $mms_cid = "8815";
	private static $mms_pwd = "8815";
	private static $mms_productid = "6767";
	private static $mms_url = "http://58.68.247.139:1721/sendMms.ashx"; //彩信端口
	private static $fetch_sms_report_url = "http://58.68.247.139:9021/communication/fetchReports.ashx";
	private static $fetch_mms_report_url = "http://58.68.247.139:1721/communication/fetchMmsReports.ashx";
	private static $fetch_deliver_url = "http://58.68.247.139:9021/communication/fetchDelivers.ashx";
	/**
	 * @desc 发送短信、短信最多不能大于200条
	 * @param array $mobile_arr
	 * @param string $content
	 * @param string $lcode
	 * @param string $ssid
	 * @param string $format
	 * @param string $sign
	 * @param string $custom
	 * @return $response (短信服务器端的状态码)   {"ret"=>10344,"msg"=>"asdfasdfasdf","response"=>"......"}
	 */
	public static function sendMessage($mobile_arr = array(),$content="",$lcode="",$ssid="",$format="15",$sign="",$custom=""){
		//   cid,pwd,productid,mobile,content,lcode,ssid,format,sign,custom
		$url_param = array(
				'cid' => "", 		//必选	客户端ID，等同于登录的用户ID.使用Base64算法加密
				'pwd' => "",		//必选	登录密码。使用Base64算法加密
				'productid' => "",	//必选	通道组id，由宜信通平台分配。
				'mobile' => "",		//必选	接收手机号码。可以单发也可以群发，群发时以半角“,”隔开,使用Base64算法加密每次最多发200个号码
				'content' => "",	//必选	发送短信内容。使用Base64算法加密
				'lcode' => "",		//可选	扩展号码（长号码）。如果不选表示没有扩展号码
				'ssid' => "",		//可选	接口使用侧的唯一ID，如果不选回推的状态报告将不能正确匹配
				'format' => "",		//可选	消息格式。15 短信 32 长短信
				'sign' => "",		//可选	签名。如果不选时则表示客户端无签名。使用Base64算法加密
				'custom' => "",		//可选	扩展字段。默认为空字符串“”

		);
		$url = self::$sms_url;
		$url_param['cid'] = base64_encode(self::$cid);
		$url_param['pwd'] = base64_encode(self::$pwd);
		$url_param['productid'] = self::$productid;
		$mobile_num = count($mobile_arr);
		if($mobile_num>0 && $mobile_num<=200){
			$url_param['mobile'] = base64_encode(implode(',', $mobile_arr));
		}else{
			return self::respond(10200);
		}
		$content_length =  strlen(trim($content));
		if($content_length>0){
			$url_param['content'] = base64_encode(urlencode($content));
		}else{
			return self::respond(10300);
		}
		$url_param['lcode'] = $lcode;
		$url_param['ssid'] = $ssid;
		$url_param['format'] = ($format==15)?15:32;
		$sign_length = strlen(trim($sign));
		if($sign_length>0){
			$url_param['sign'] = base64_encode($sign);
		}
		$url_param['custom'] = $custom;
		$url_param = self::build_url_param($url_param);
		$response = self::http_send_post($url,$url_param);
		if($response===false){
			return self::respond(10403);
		}
		return array('ret'=>0, 'msg'=>'success', 'response'=>json_decode($response,true));
	}
	/**
	 * @desc 发送彩信的方法  一次最多发100个号
	 * @param array $mobile_arr
	 * @param string $title
	 * @param string $content
	 * @param string $lcode
	 * @param string $ssid
	 * @return $response (返回彩信端服务器的状态码)
	 */
	public static function sendMMessage($mobile_arr = array(),$title="",$content="",$lcode="",$ssid=""){
		//   cid,pwd,productid,mobile,{title}content,lcode,ssid,
		$mss_url_param = array(
				'cid' => "", 		//必选	客户端ID，等同于登录的用户ID.使用Base64算法加密
				'pwd' => "",		//必选	登录密码。使用Base64算法加密
				'productid' => "",	//必选	通道组id，由宜信通平台分配。
				'mobile' => "",		//必选	接收手机号码。可以单发也可以群发，群发时以半角“,”隔开,使用Base64算法加密每次最多发100个号码
				'title' => "",      //彩信标题,使用Base64算法加密
				'content' => "",	//必选	1.发送彩信内容。
				'lcode' => "",		//可选	扩展号码（长号码）。如果不选表示没有扩展号码
				'ssid' => "",		//可选	接口使用侧的唯一ID，如果不选回推的状态报告将不能正确匹配
		);
		$url = self::$mms_url;
		$mss_url_param['cid'] = base64_encode(self::$mms_cid);
		$mss_url_param['pwd'] = base64_encode(self::$mms_pwd);
		$mss_url_param['productid'] = self::$mms_productid;
		$mobile_num = count($mobile_arr);
		if($mobile_num>0 && $mobile_num<=100){
			$mss_url_param['mobile'] = base64_encode(implode(',', $mobile_arr));
		}else{
			return self::respond(10100);
		}
		$title_length = strlen(trim($title));
		if($title_length>0){
			$mss_url_param['title'] = base64_encode($title);
		}
		//content格式处理
		//使用Base64算法加密.内容格式:文件名 + 英文逗号 + 文件的base64编码字符串 + 分号
		//例如:“1.txt,” + 文件base64编码字符串 + “;” + “2.jpg,” + 文件base64编码字符串+”;”+” 3.mid,”+文件base64编码字符串发送彩信的资源id,
		//如1013121614230100020007,需要上传提前上传彩信资源.使用Base64算法加密
		$content_length = strlen(trim($content));
		if($content_length>0){
			$mss_url_param['content'] = base64_encode($content);
		}else{
			return self::respond(10300);
		}
		$mss_url_param['lcode'] = $lcode;
		$mss_url_param['ssid'] = $ssid;
		$mss_url_param = self::build_url_param($mss_url_param);
		$response = self::http_send_post($url,$mss_url_param);
		if($response===false){
			return self::respond(10400);
		}
		return array('ret'=>0, 'msg'=>'success', 'response'=>json_decode($response,true));
	}
	/**
	 * @desc 获取发送短信报告
	 * @type sms||mss
	 * @param number $cnt 获取状态报告的条数 默认100 最大 500
	 * @response json data
	 */
	public static function getReport($cnt=100,$type = "sms"){
		//   cid,pwd,{cnt}
		$url = ($type == 'sms')?self::$fetch_sms_report_url:self::$fetch_mms_report_url;
		$fetch_report_param = array(
				'cid' => '',
				'pwd' => '',
				'cnt' => '',
		);
		$fetch_report_param['cid'] = ($type == 'sms')?self::$cid:self::$mms_cid;
		$fetch_report_param['pwd'] = ($type == 'sms')?self::$pwd:self::$mms_pwd;
		$fetch_report_param['cnt'] = intval($cnt);
		$fetch_report_param = self::build_url_param($fetch_report_param);
		$response = self::http_send_post($url,$fetch_report_param);
		if($response===false){
			return self::respond(10400);
		}
		return array('ret'=>0, 'msg'=>'success', 'response'=>json_decode($response,true));
			
	}
	/**
	 * @desc 上行数据
	 *
	 * @param 获取上行数据的数量 $cnt
	 * @response json data
	 */
	public static function getDeliver($cnt){
		//   cid,pwd,{cnt}
		$fetch_deliver_param = array(
				'cid'=>'',
				'pwd'=>'',
				'cnt'=>'',
		);
		$url = self::$fetch_deliver_url;
		$fetch_deliver_param['cid'] = self::$cid;
		$fetch_deliver_param['pwd'] = self::$pwd;
		$fetch_deliver_param['cnt'] = intval($cnt);
		$fetch_deliver_param = self::build_url_param($fetch_deliver_param);
		$response = self::http_send_post($url,$fetch_deliver_param);
		if($response===false){
			return self::respond(10400);
		}
		return array('ret'=>0, 'msg'=>'success', 'response'=>json_decode($response,true));
			
	}
	/**
	 * @desc 定义发送数据方法
	 * @param unknown $url
	 * @param unknown $curlPost
	 */
	public static function http_send_post($url,$curlPost){
		$exectimeout = 12;
		$connecttime = 3;
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url);//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);//POST数据
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$connecttime);
		curl_setopt($ch, CURLOPT_TIMEOUT, $exectimeout);
		$response = curl_exec($ch);//接收返回信息
		if(curl_errno($ch)){//出错则显示错误信息
			return false;
		}
		curl_close($ch); //关闭curl链接
		return  $response;//显示返回信息
			
	}
	/**
	 * @desc parse url 字符串
	 * @param unknown $url_param
	 * @return string
	 */
	public static function build_url_param($url_param = array()){
		$url_param_str = "";
		$param_num =  count($url_param);
		if($param_num<=0){
			return $url_param_str;
		}
		foreach($url_param as $key =>$val){
			$url_param_str .="&$key";
			$url_param_str .="=$val";
		}
		$url_param_str = substr($url_param_str,1);
		return $url_param_str;
			
	}
	public static function respond($status){
		$response_arr = array(
				'10100'  =>"please confirm param the mobile is null or the mobile num big 100!",
				'10200'  =>"please confirm param the mobile is null or the mobile num big 200!",
				'10300'  =>"please confirm param the content of length!",
				'10400'  =>"'curl_errno' has a errno!",
				'10403'  =>"the curl request is invalid!"
		);
		$description =  $response_arr[$status];
		$response_res =  array("ret"=>$status,"msg"=>$description);
		return $response_res;
	}

}
