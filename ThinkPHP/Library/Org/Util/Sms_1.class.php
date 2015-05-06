<?php
namespace Org\Util;
/**
 * @author chunyingXu
 * @time 2014/08/22
 * @ the SMS Platform support company :北京容大友信科技有限公司
 * @desc :http接口短信发送API
 */
class Sms_1{
	private static $username = "dsykj"; //客户端用户名
	private static $password = "dsykj"; //客户端pwd
	private static $sms_url = "http://116.213.72.20/SMSHttpService/send.aspx"; //短信发送url
	private static $query_balance_url = "http://116.213.72.20/SMSHttpService/Balance.aspx"; //查询余额url
	/**
	 * 发送短信
	 * @param array $mobile_arr
	 * @param string $content
	 * @param string $extcode
	 * @param string $senddate
	 * @param string $batchID
	 * @return multitype:unknown string |multitype:number string mixed
	 */
	public static function sendMessage($mobile_arr = array(),$content="",$batchID="",$extcode="",$senddate=""){
		//username/password/content/mobile/extcode/senddate/batchID
			
		$url_param = array(
				'username' => "", 	//必选	用户名
				'password' => "",	//必选	登录密码。
				'mobile' => "",		//必选	接收手机号码。可以单发也可以群发，群发时以半角“,”隔开,使用Base64算法加密每次最多发200个号码
				'content' => "",	//必选	如为中文一定要使用一下urlencode函数
				'extcode' => "",	//可选	通道扩展代码	短信报告及短信回复此字段必选，由客服提供
				'senddate' => "",	//可选	发送时间，格式：yyyy-MM-dd HH:mm:ss，可选
				'batchID' => "",	//可选	批次号，可选 //返回状态报告时必须用一个唯一此处默认设置成了time
		);

		$url = self::$sms_url;
		$url_param['username'] = self::$username;
		$url_param['password'] = self::$password;
		$mobile_num = count($mobile_arr);
		if($mobile_num>0 && $mobile_num<=200){
			$url_param['mobile'] = implode(';', $mobile_arr);
		}else{
			return self::respond(10200);
		}
		$content_length =  strlen(trim($content));
		if($content_length>0){
			$url_param['content'] = urlencode($content);
		}else{
			return self::respond(10300);
		}
		$url_param['batchID'] = time();
		$extcode_length = strlen(trim($extcode));
		if($extcode_length>0){
			$url_param['extcode'] = $extcode;
		}
		$url_param = self::build_url_param($url_param);
		$response['status'] = self::http_send_post($url,$url_param);
		if($response===false){
			return self::respond(10403);
		}
		return array('ret'=>0, 'msg'=>'success', 'response'=>$response);
	}
	/**
	 * 获取账号里的余额
	 */
	public static function getBalance(){
		$url_param = array(
				'username'=>"",
				'password'=>""
		);
		$url = self::$query_balance_url;
		$url_param['username'] = self::$username;
		$url_param['password'] = self::$password;
		$url_param = self::build_url_param($url_param);
		$response['status'] = self::http_send_post($url,$url_param);
		if($response===false){
			return self::respond(10403);
		}
		return array('ret'=>0, 'msg'=>'success', 'response'=>$response);
			
	}
	/**
	 * @desc 获取短信状态报告
	 * @type sms
	 * @param number $cnt
	 * @response String
	 */
	public static function getReport(){
		$report_param = @file_get_contents("php://input");
		return $report_param;
	}
	/**
	 * @desc 定义发送数据方法
	 * @param unknown $url
	 * @param unknown $curlPost
	 */
	public static function http_send_post($url,$curlPost){
		$exectimeout = 12;
		$connecttime = 3;
		$this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url);//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_HTTPHEADER,$this_header);
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
