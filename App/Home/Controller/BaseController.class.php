<?php
namespace Home\Controller;
use Think\Controller;
use Think\Log;
use Think\Model;
Vendor('AliyunOss.aliyun');

use Aliyun\OSS\OSSClient;
use Aliyun\OSS\Exceptions\OSSException;
use Aliyun\Common\Exceptions\ClientException;

// 开启GZIP压缩。
/* if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
	ob_start("ob_gzhandler");
else
	ob_start(); */
class BaseController extends Controller {

	public function _initialize(){
		//入口
		header("Content-Type:text/html; charset=utf-8");
		/* 对用户传入的变量进行转义操作。 */
		if (! get_magic_quotes_gpc()) {
			if (! empty($_GET)) {
				$_GET = addslashes_deep($_GET);
			}
			if (! empty($_POST)) {
				$_POST = addslashes_deep($_POST);
			}
			$_COOKIE = addslashes_deep($_COOKIE);
			$_REQUEST = addslashes_deep($_REQUEST);
		}
	}
	function return_ex($ret, $msg) {
		return array('ret'=>$ret, 'msg'=>$msg);
	}
	function respond($ret, $msg,$time=null) {
		Log::write("respond,ret:$ret,msg:$msg,e_time:$time", $ret==0?Log::INFO:Log::ERR);
		$obj = array('ret' => $ret, 'msg' => $msg);
		echo json_encode($obj);
		return false;
	}
	function respond_ex($ret, $msg, $key, $val,$time=null) {
		Log::write("respond_ex,ret:$ret,msg:$msg,e_time:$time", $ret==0?Log::INFO:Log::ERR);
		$obj = array('ret' => $ret, 'msg' => $msg, $key => $val);
		echo json_encode($obj);
		return false;
	}
	function respond_alipay($ret,$msg='')
	{
		if(!$ret)
		{
			Log::write("respond_alipay,msg:$msg",Log::ERR);
			return false;
		}
		else
		{
			return true;
		}
	}

	function memcache_error_log($msg)
	{
		if(C('MEMCACHED_LOG'))
			Log::write("memcache_error_log,msg:$msg",Log::ERR);
	}


	// 如果返回值是非零值，则设置HTTP Response Header为404
	function respond_404_if_failed($ret, $msg) {
		Log::write("respond_404_if_failed,ret:$ret, msg:$msg", $ret==0?Log::INFO:Log::ERR);
		if ($ret != 0)
			header("HTTP/1.0 404 Not Found");
		$obj = array('ret' => $ret, 'msg' => $msg);
		echo json_encode($obj);
	}
	function get_level_from_exp($exp) {
		$lvl_exp_arr = C('LEVEL_EXP');
		$lvl = 0;
		$i = 0;
		foreach($lvl_exp_arr as $lvl_exp) {
			if ($exp >= $lvl_exp)
				$lvl = $i;
			$i++;
		}
		return $lvl;
	}

	function generateRandomString($length = 32) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	function generateRandomBigInt() {
		$characters = '0123456789';
		$randomString = '';
		for ($i = 0; $i < 16; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
			if ($i == 0 && $randomString == '0')
				$randomString = '1';
		}
		return $randomString;
	}

	/**
	 * @desc 检查device and account and login_token 是否正常
	 * -101 : device $deviceid is not found.
	 * -102	: device $deviceid was not bind to an account.
	 * -103	: login token of device $deviceid doesn't match.".$db_device[0]["login_token"]."=$logintoken"
	 * 返回值: {'ret'=>'0', 'msg'=>'success'}
	 */
	function check_device_account_logintoken($deviceid,$logintoken){
		if ($deviceid == '')
			return $this->return_ex(-100, '无效请求。当前设备UUID为空。');
        //获取该设备信息
		$device_model=D("Device");
		$device_info=$device_model->get_info_by_uuid($deviceid);
		if (!$device_info)
			return $this->return_ex(-101, "系统中未找到当前设备的记录。");
		if ($device_info["bind_account"] == 0)
			return $this->return_ex(-102, "当前设备尚未绑定任何帐号，请重新打开客户端。");
		if ($device_info["login_token"] == '' || $device_info["login_token"] != $logintoken)
			return $this->return_ex(-103, "登录令牌不正确，请重新打开客户端。");

		return $this->return_ex(0, $device_info);
	}

	/**
	 * @desc 检查电话号的格式是否正确
	 * @param int $phone
	 * @return {'ret'=>'0', 'msg'=>'success'}
	 */
	function is_check_phone($phone){
		// 带test的表示是测试数据，允许通过
		if (strpos($phone, "test") !== false)
			return array('ret'=>0, 'msg'=>"ok");

		$phone_three = array(134,135,136,137,138,139,150,151,152,157,158,159,147,182,183,184,187,188,170,177,178,130,131,132,145,155,156,185,186,176,133,153,180,181,189);
		if(strlen(trim($phone))==11){
			$prefix_three = substr($phone, 0,3);
			//echo $prefix_three;
			if(in_array($prefix_three, $phone_three)){
				$pattern = "/[\d]{11}/";
				$preg_res = preg_match($pattern, $phone);
				if(!$preg_res){
					return array('ret'=>-152, 'msg'=>"手机号码格式不正确。");
				}
				return array('ret'=>0, 'msg'=>"ok");
			}else{
				return array('ret'=>-151, 'msg'=>"手机号码前三位不正确。");
			}
				
		}else{
			return array('ret'=>-150, 'msg'=>"手机号码长度不是11位。");
		}
	}
	/**
	 * @desc 检查邮件格式是否正确
	 * @param unknown $email
	 * @return boolean
	 */
	function is_check_email($email){
		// 带test的表示是测试数据，允许通过
		if (strpos($email, "test.com") !== false)
			return array('ret'=>0, 'msg'=>"ok");
			
		$pattern = "/(^\w+[-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
		if(!preg_match($pattern,$email))
			return array('ret'=>-160, 'msg'=>"邮箱格式错误。");
		return array('ret'=>0, 'msg'=>"ok");
	}
	/**
	 * @desc 自定义GET请求方法
	 * @param unknown $url
	 * @param unknown $curlPost
	 */
	function http_send_requet($url){
		$exectimeout = 12;
		$connecttime = 3;
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url);//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置HTTP头
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$connecttime);
		curl_setopt($ch, CURLOPT_TIMEOUT, $exectimeout);
		$response = curl_exec($ch);//接收返回信息
		if(curl_errno($ch)){//出错则显示错误信息
			return false;
		}
		curl_close($ch); //关闭curl链接
		return  $response;//显示返回信息
	}

	function use_account_money($model, $account_id, $cp) {
		// 首先确认是已经开放的计费点。即bean,coin,gold至少有一个>=0。全都是0也是可能的。
		if ($cp['bean'] < 0 && $cp['coin'] < 0 && $cp['gold'] < 0)
			return $this->return_ex(-999, "计费点尚未开放，无法付费。");
		
		// 查找该账户的余额
		$condition = array();
		$condition['id'] = $account_id;
		$condition['status'] = 1;
		$db_account = $model->lock(true)->table('july_account')->field('gift_coin_num, bean, gold, used_coin_num, used_bean_num, used_gold_num exp, level')->where($condition)->find();
		if ($db_account === false)
			return $this->return_ex(-600, "找不到该账户：".$account_id);
		
		// 确认bean,coin,gold中至少有一个是有效值（>=0），且该账户付得起
		$bean_affordable = ($cp['bean'] >= 0 && $db_account['bean'] >= $cp['bean']);
		$coin_affordable = ($cp['coin'] >= 0 && $db_account['gift_coin_num'] >= $cp['coin']);
		$gold_affordable = ($cp['gold'] >= 0 && $db_account['gold'] >= $cp['gold']);
		if (!$bean_affordable && !$coin_affordable && !$gold_affordable) {
			$money = "";
			$money .= ($cp['bean'] > 0 && $db_account['bean'] < $cp['bean']) ? "云豆" : " ";
			$money .= ($cp['coin'] > 0 && $db_account['gift_coin_num'] < $cp['coin']) ? "云贝" : " ";
			$money .= ($cp['gold'] > 0 && $db_account['gold'] < $cp['gold']) ? "G币" : " ";
			return $this->return_ex(-601, "当前账户余额不足: ".$money);
		}
		
		// 按照bean,coin,gold优先级，逐个尝试扣费
		$update = array();
		// for GSController to return values
		$update["gift_coin_num"] = $db_account["gift_coin_num"];
		$update["used_coin_num"] = $db_account["used_coin_num"];
		
		if ($cp['bean'] >= 0 && $db_account['bean'] >= $cp['bean']) {
			$update["bean"] = $db_account["bean"] - $cp['bean'];
			$update["used_bean_num"] = $db_account["used_bean_num"] + $cp['bean'];
		}
		else if ($cp['coin'] >= 0 && $db_account['gift_coin_num'] >= $cp['coin']) {
			$update["gift_coin_num"] = $db_account["gift_coin_num"] - $cp['coin'];
			$update["used_coin_num"] = $db_account["used_coin_num"] + $cp['coin'];
		
			// TODO: 暂时只有云贝会增加经验值和等级
			$update["exp"] = $db_account["exp"] + $cp['coin'];
			$new_lvl = $this->get_level_from_exp($update["exp"]);
			$update["level"] = max($new_lvl, $db_account["level"]);
		}
		else if ($cp['gold'] >= 0 && $db_account['gold'] >= $cp['gold']) {
			$update["gold"] = $db_account["gold"] - $cp['gold'];
			$update["used_gold_num"] = $db_account["used_gold_num"] + $cp['gold'];
		}
		
		if (count($update) > 0) {
			$db_link = $model->table('july_account')->where($condition)->save($update);
			if ($db_link === false)
				return $this->return_ex(-666, "无法更新当前账户的余额。");
		
			// 清除帐号信息缓存
			$account_model=D("Account");
			$account_model->clear_cache($account_id);
		}

		return $this->return_ex(0, $update);
	}

	// 购买游戏包
	function purchase_chargepoint_gamepack($model, $account_id, $chargepoint_id) {

		$db_chargepoint = $model->table("july_chargepoint_gamepack cpg, july_gamepack gp")->field('cpg.gamepack_id,cpg.left_seconds_increase,cpg.deadline_time_increase,cpg.deadline_time')->where('cpg.chargepoint_id='.$chargepoint_id.' and cpg.gamepack_id=gp.pack_id and gp.status=1')->select();
		if (!$db_chargepoint || count($db_chargepoint) == 0)
			return $this->return_ex(-110, "计费点ID $chargepoint_id 未找到或失效。");
		if ($db_chargepoint[0]['deadline_time'] < time() && $db_chargepoint[0]['deadline_time'] > 0)
			return $this->return_ex(-110, "该计费点ID $chargepoint_id 已经过期。");
		$gamepack_id = $db_chargepoint[0]['gamepack_id'];

		$db_link = $model->lock(true)->table('july_link_account_gamepack')->field('deadline_time,left_seconds')->where('account_id='.$account_id.' and gamepack_id='.$gamepack_id)->select();
		if (!$db_link || count($db_link) == 0) {
			$data = array();
			$data['gamepack_id'] = $gamepack_id;
			$data['account_id'] = $account_id;
				
			// 如果该计费点有绝对截止日期，则直接使用该日期；否则使用当前时刻+延长时间。
			if ($db_chargepoint[0]['deadline_time'] != 0)
				$data['deadline_time'] = $db_chargepoint[0]['deadline_time'];
			else
				$data['deadline_time'] = time()+$db_chargepoint[0]['deadline_time_increase'];

			$data['left_seconds'] = $db_chargepoint[0]['left_seconds_increase'];
			$data['create_time'] = $data['last_recharge_time'] = time();
			$data['played_seconds'] = $data['last_charge_time'] = 0;
			$db_link = $model->table('july_link_account_gamepack')->add($data);
			if (!$db_link)
				return $this->return_ex(-111, "无法添加当前账户 $account_id 购买游戏包 $gamepack_id 的记录。");
		}
		else {
			$update = array();
			$update['gamepack_id'] = $gamepack_id;
			$update['account_id'] = $account_id;
				
			// 如果该计费点有绝对截止日期，则直接使用该日期；否则使用当前时刻+延长时间。
			if ($db_chargepoint[0]['deadline_time'] != 0) {
				if ($db_chargepoint[0]['deadline_time'] < $update['deadline_time'])
					return $this->return_ex(-110, "当前账户在该游戏包上的剩余时间充足，比该计费点的截止日期还长。不需要购买或者使用该计费点。");
				$update['deadline_time'] = $db_chargepoint[0]['deadline_time'];
			}
			else {
				if ($db_link[0]['deadline_time'] < time())
					$update['deadline_time'] = time()+$db_chargepoint[0]['deadline_time_increase'];
				else
					$update['deadline_time'] = $db_link[0]['deadline_time'] + $db_chargepoint[0]['deadline_time_increase'];
			}
				
			$update['left_seconds'] = $db_link[0]['left_seconds'] + $db_chargepoint[0]['left_seconds_increase'];
			$update['last_recharge_time'] = time();
			$db_link = $model->table('july_link_account_gamepack')->where('account_id='.$account_id.' and gamepack_id='.$gamepack_id)->save($update);
			if (!$db_link)
				return $this->return_ex(-112, "无法更新当前账户 $account_id 购买游戏包 $gamepack_id 的记录。");
		}
			
		return $this->return_ex(0, "success");
	}

	// 向一个帐号添加（更新）一个游戏包，截止日期为指定时间
	function add_gamepack_with_deadline($account_id, $gamepack_id, $deadline_time) {
		$db = M('link_account_gamepack');
		$condition = array();
		$condition['account_id'] = $account_id;
		$condition['gamepack_id'] = $gamepack_id;
		$db_agp = $db->field("deadline_time")->where($condition)->select();
		if (count($db_agp) > 0) {
			$now = time();
			if ($db_agp[0]['deadline_time'] < $deadline_time) {
				$condition = array();
				$condition['account_id'] = $account_id;
				$condition['gamepack_id'] = $gamepack_id;
				$update = array();
				$update['deadline_time'] = $deadline_time;
				$ret = $db->where($condition)->save($update);
				if ($ret === false) {
					return -1; // update failed.
				}
				else {
					return 1; // update success
				}
			}
			else {
				return 0; // no change
			}
		}
		else {
			$data = array();
			$data['account_id'] = $account_id;
			$data['gamepack_id'] = $gamepack_id;
			$data['played_seconds'] = 0;
			$data['left_seconds'] = 0;
			$data['deadline_time'] = $deadline_time;
			$data['create_time'] = time();
			$data['last_charge_time'] = 0;
			$data['last_recharge_time'] = 0;
			$ret = $db->add($data);
			if ($ret === false) {
				return -2; // insert failed.
			}
			else {
				return 2; // insert success
			}
		}
	}

	// 获取用户的默认存档卡，如果没有的话自动创建
	function get_default_save_serial($model, $device_uuid, $account_id, $game_id)
	{
		// 查找用户是否有存档卡, delete_time = 0的项才是有效项
		$condition = array();
		$condition['account_id'] = $account_id;
		$condition['game_id'] = $game_id;
		$condition['delete_time'] = 0;
		$db_serial = $model->table('july_game_save_serial')->field("id, name, create_time, deletable, default_time")->where($condition)->order('default_time desc, id desc')->limit(1)->select();
		if ($db_serial === false)
			return $this->return_ex(-200, "无法查询用户的默认存档卡。".$model->table('july_game_save_serial')->getLastSql());

		if (count($db_serial) == 0) {
			// 添加存档卡
			$data = array();
			$data['account_id'] = $account_id;
			$data['game_id'] = $game_id;
			$data['deletable'] = 0; //  默认存档卡不允许删除
			$data['name'] = '我的存档卡';
			$data['create_time'] = time();
			$data['delete_time'] = 0;
			$data['default_time'] = 1; // 第一个存档卡就是默认存档卡，但是default_time只有1 ：）
			$new_serial_id = $model->table('july_game_save_serial')->add($data);
			if($new_serial_id === false)
				return $this->return_ex(-201, "无法添加默认存档卡。".$model->table('july_game_save_serial')->getLastSql());

			$serial = array();
			$serial["id"] = $new_serial_id;
			$serial["name"] = $data['name'];
			$serial["create_time"] = $data['create_time'];
			$serial["deletable"] = $data['deletable'];
			$serial["default_time"] = 1;
			return $this->return_ex(0, $serial);
		}
		return $this->return_ex(0, $db_serial[0]);
	}

	/**
		a、给用户新建一个存档序列，在该序列下新建一个存档。
		b、将售卖的存档复制到新建存档的目录，改名为“新存档ID_MD5.save”。

		返回状态码:
		-300	没有找到计费点
		-301	存档文件已损坏，md5不符
		-302	无法创建新的存档序列
		-303	无法创建新的存档
		-304	无法建立用户存档目录
		-305	无法复制存档到用户存档目录

		成功后返回：
		{"ret":0,"msg":"success"}
		**/
	function copy_sale_gamesave($model, $device_uuid, $account_id, $chargepoint_id)
	{
		$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");

		// 是否需要检查用户是否已经购买过该存档
		$condition = array();
		$condition['charge_to_account_id'] = $account_id;
		$condition['chargepoint_id'] = $chargepoint_id;
		$db_ret = $model->table('july_payment_card')->field('charge_time')->where($condition)->find();
		if ($db_ret === false)
			return $this->return_ex(-330, "无法查询您是否购买过该存档。");
		if ($db_ret != null && $db_ret['charge_time'] > 0)
			return $this->return_ex(-331, "您已经在".date('Y-m-d H:i:s', $db_ret['charge_time'])."使用充值卡购买过该存档。不能重复购买。");

		$condition = array();
		$condition['account_id'] = $account_id;
		$condition['chargepoint_id'] = $chargepoint_id;
		$db_ret = $model->table('july_payment_coin')->field('create_time')->where($condition)->find();
		if ($db_ret === false)
			return $this->return_ex(-340, "无法查询您是否购买过该存档。");
		if ($db_ret != null && $db_ret['create_time'] > 0)
			return $this->return_ex(-341, "您已经在".date('Y-m-d H:i:s', $db_ret['create_time'])."使用虚拟币购买过该存档。不能重复购买。");

		// 查询计费点的信息
		$condition = array();
		$condition['cp.status'] = 1;
		$condition['cp.id'] = $chargepoint_id;
		$condition['_string'] = "cgps.chargepoint_id = cp.id";
		$db_cp = $model->table('july_chargepoint_gamesave cgps, july_chargepoint cp')->where($condition)->find();
		if ($db_cp === false)
			return $this->return_ex(-300, "没有找到计费点");
		$game_id = $db_cp['game_id'];
		$sale_file = $db_cp['filename'];
		$file_md5 = $db_cp['compressed_md5'];
		$file_size = $db_cp['compressed_size'];
		$name_for_user = $db_cp['name_for_user'];
		// 检查要售卖的存档是否正常
		$save_file_path = $GAME_SAVE_DIR."sale".DIRECTORY_SEPARATOR.$game_id.DIRECTORY_SEPARATOR.$chargepoint_id."_".$file_md5.".save";
		if (!file_exists($save_file_path)) {
			if (C("ENABLE_OSS") === true) {
				// 本地文件不存在，尝试从阿里云OSS下载
				// Read and write for owner, read for everybody else
				$final_dir = dirname($save_file_path);
				if (!file_exists($final_dir) && !mkdir($final_dir, 0744, true))
					return $this->return_ex(-310, "从阿里云OSS同步时，无法建立售卖存档文件夹");

				$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
				if ($ret['ret'] != 0)
					return $this->return_ex(-311, "从阿里云OSS同步时，连接阿里云OSS失败. ret: ".$ret['ret']);
				$client = $ret['msg'];

				// 阿里云OSS上的路径
				$key = "g"."/".$game_id."/".$chargepoint_id."_".$file_md5.".save";
				$ret = $this->getObjectAsFile($client, C("OSS_UDS_BUCKET"), $key, $save_file_path);
				if ($ret['ret'] != 0)
					return $this->return_ex(-312, "从阿里云OSS同步时，下载售卖存档失败. ret: ".$ret['ret']);
			}
			else
				return $this->return_ex(-301, "存档文件不存在");
		}
		$md5file = strtolower(md5_file($save_file_path));
		if ($file_md5 != $md5file)
			return $this->return_ex(-301, "存档文件已损坏，md5不符");

		// 确保用户有至少一个非购买的基础存档卡
		$ret = $this->get_default_save_serial($model, $device_uuid, $account_id, $game_id);
		if ($ret['ret'] != 0)
			return $this->return_ex($ret['ret'], $ret['msg']);
			
		// 给用户建立新购买的存档卡和存档拷贝
		$data = array();
		$data['account_id'] = $account_id;
		$data['game_id'] = $game_id;
		$data['name'] = $name_for_user;
		$data['deletable'] = 0; // 购买的存档卡不允许删除
		$data['default_time'] = time(); // 新购买的存档卡立即成为默认存档
		$data['create_time'] = time();
		$data['delete_time'] = 0;
		$serial_id = $model->table('july_game_save_serial')->add($data);
		if ($serial_id === false)
			return $this->return_ex(-302, "无法创建新的存档序列");
		$data = array();
		$data['account_id'] = $account_id;
		$data['game_id'] = $game_id;
		$data['deletable'] = 0; // 购买的存档不允许删除
		$data['serial_id'] = $serial_id;
		$data['gs_id'] = 0;
		$data['gs_ip'] = '';
		$data['upload_token'] = '';
		$data['derived_from'] = 0;
		$data['device_uuid'] = $device_uuid;
		$data['upload_time'] = time();
		$data['game_mode'] = 0;
		$data['gs_report_time'] = 0;
		$data['compressed_size'] = $file_size;
		$data['compressed_md5'] = $file_md5;
		$data['derived_count'] = 0;
		$data['create_time'] = time();
		$data['total_play_time'] = 1; // 新购买的存档默认游戏时间为1秒
		$data['delete_time'] = 0;
		$save_id = $model->table('july_game_save')->add($data);
		if ($save_id === false)
			return $this->return_ex(-303, "无法创建新的存档");
		// 建立用户的存档目录 格式为：每层1000个目录，帐号ID，游戏ID，存档序列ID，存档文件。
		$user_save_path = $GAME_SAVE_DIR.intval($account_id/1000000000).
		DIRECTORY_SEPARATOR.intval($account_id/1000000).DIRECTORY_SEPARATOR.intval($account_id/1000).
		DIRECTORY_SEPARATOR.$account_id.DIRECTORY_SEPARATOR.$game_id.DIRECTORY_SEPARATOR.$serial_id.DIRECTORY_SEPARATOR;
		if (!file_exists($user_save_path) && !mkdir($user_save_path, 0744, true))
			return $this->return_ex(-304, "无法建立用户存档目录");
		// 复制存档文件
		$source_file = $save_file_path;
		$target_file = $user_save_path.$save_id."_".$file_md5.".save";
		if (FALSE === copy($source_file, $target_file))
			return $this->return_ex(-305, "无法复制存档到用户存档目录");

		if (C("ENABLE_OSS") === true) {
			// 同步上传到阿里云OSS
			$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
			if ($ret['ret'] != 0)
				return $this->return_ex(-320, "上传用户存档到阿里云OSS时，连接OSS失败. ret:".$ret['ret']);
			$client = $ret['msg'];

			$key = "u"."/".intval($account_id/1000000000)."/".intval($account_id/1000000)."/".intval($account_id/1000)."/".
					$account_id."/".$game_id."/".$serial_id."/".$save_id."_".$file_md5.".save";
			$ret = $this->multipartUpload($client, C("OSS_UDS_BUCKET"), $key, $target_file);
			if ($ret['ret'] != 0)
				return $this->return_ex(-321, "上传用户存档到阿里云OSS时，上传失败. ret:".$ret['ret']);
		}

		return $this->return_ex(0, "success");
	}

	/**
	 * 获取默认账户头像
	 * @param $account_id int
	 * @return string
	 */
	function get_auto_avatar($account_id = 0){
		$db_avatar = M("avatar");
		$condition = array();
		$condition['type'] = 1;
		$avatar = $db_avatar->field("pic_url")->where($condition)->select();
		if($avatar === false) {
			Log::write("get_auto_avatar,list avatar failed", Log::ERR);
			return "";
		}
		$auto_avatar = "";
		if(count($avatar) > 0)
			$auto_avatar = $avatar[array_rand($avatar,1)]['pic_url'];
		if($account_id != 0 && $auto_avatar != ""){
			$db_account = M("account");
			$condition = array();
			$condition['id'] = $account_id;
			$condition["avatar"] = $auto_avatar;
			$result = $db_account->save($condition);
			if($result === false) {
				Log::write("get_auto_avatar,save to account $account_id failed.", Log::ERR);
				return "";
			}
		}
		return $auto_avatar;
	}

	/**
	 * 获取默认账户昵称
	 * @param $account_id int
	 * @return string
	 */
	function get_auto_name($account_id = 0){
		$db_avatar = M("avatar");
		$condition = array();
		$condition['type'] = 2;
		$avatar = $db_avatar->field("name")->where($condition)->select();
		if($avatar === false) {
			Log::write("get_auto_name,list name failed", Log::ERR);
			return "";
		}
		$auto_name = "";
		if(count($avatar) > 0)
			$auto_name = $avatar[array_rand($avatar,1)]['name'];
		if($account_id != 0 && $auto_name != ""){
			$db_account = M("account");
			$condition = array();
			$condition['id'] = $account_id;
			$condition['nickname'] = $auto_name;
			$result = $db_account->save($condition);
			if($result === false) {
				Log::write("get_auto_name,save to account $account_id, failed.", Log::ERR);
				return "";
			}
		}
		return $auto_name;
	}


	// Sample of create client
	function createClient($endPoint, $accessKeyId, $accessKeySecret) {
		try {
			return $this->return_ex(0,
					OSSClient::factory(array(
							'Endpoint' => $endPoint,
							'AccessKeyId' => $accessKeyId,
							'AccessKeySecret' => $accessKeySecret,
					)));
		} catch (OSSException $ex) {
			return $this->return_ex(-800, "createClient failed. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-801, "createClient failed. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}


	// Sample of get object
	function getObjectAsFile($client, $bucket, $key, $fileName) {
		$key = C('OSS_KEY_PREFIX').$key;
		try {
			$object = $client->getObject(array(
					'Bucket' => $bucket,
					'Key' => $key,
			));
				
			file_put_contents($fileName, $object->getObjectContent());
			return $this->return_ex(0, "success");
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "getObjectAsFile failed. key: $key. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "getObjectAsFile failed. key: $key. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}
	
	function checkObject($client, $bucket, $key) {
		$key = C('OSS_KEY_PREFIX').$key;
		try {
			$object = $client->getObjectMetadata(array(
					'Bucket' => $bucket,
					'Key' => $key,
			));
			return $this->return_ex(0, "success");
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "getObjectAsFile failed. key: $key. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "getObjectAsFile failed. key: $key. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}
	
	function deleteObject(OSSClient $client, $bucket, $key) {
		$key = C('OSS_KEY_PREFIX').$key;
		try {
			$client->deleteObject(array(
				'Bucket' => $bucket,
				'Key' => $key,
			));
			return $this->return_ex(0, 'success');
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "deleteObject failed. key: $key. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "deleteObject failed. key: $key. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}

	function listObjects(OSSClient $client, $bucket, $prefix) {
		try {
			$result = $client->listObjects(array(
					'Bucket' => $bucket,
					'Prefix' => $prefix,
			));
			return $this->return_ex(0, $result->getObjectSummarys());
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "listObjects failed. prefix: $prefix. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "listObjects failed. prefix: $prefix. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}

	// Sample of multipart upload
	function multipartUpload($client, $bucket, $key, $fileName) {
		$key = C('OSS_KEY_PREFIX').$key;
		$partSize = 5 * 1024 * 1024; // 5M for each part，阿里云要求每个块不小于5M

		//echo "Start uploading $fileName to $key \n";

		if (!file_exists($fileName))
			return $this->return_ex(-1, "$fileName does not exist");
			
		$content_type = "application/octet-stream";
		$file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		if ($file_ext == "jpeg" || $file_ext == "jpg")
			$content_type = "image/jpeg";
		else if ($file_ext == "png")
			$content_type = "image/png";
		else if ($file_ext == "gif")
			$content_type = "image/gif";
		else if ($file_ext == "html" || $file_ext == "htm")
			$content_type = "text/html";

		try {
			// Init multipart upload
			$uploadId = $client->initiateMultipartUpload(array(
					'Bucket' => $bucket,
					'Key' => $key,
					'ContentType' => $content_type,
			))->getUploadId();

			// upload parts
			$fileSize = filesize($fileName);
			$partCount = (int) ($fileSize / $partSize);
			if ($fileSize % $partSize > 0) {
				$partCount += 1;
			}

			$partETags = array();
			for ($i = 0; $i < $partCount ; $i++) {
				$uploadPartSize = ($i + 1) * $partSize > $fileSize ? $fileSize - $i * $partSize : $partSize;
				$file = fopen($fileName, 'r');
				fseek($file, $i * $partSize);
				$partETag = $client->uploadPart(array(
						'Bucket' => $bucket,
						'Key' => $key,
						'UploadId' => $uploadId,
						'PartNumber' => $i + 1,
						'PartSize' => $uploadPartSize,
						'Content' => $file,
				))->getPartETag();
				$partETags[] = $partETag;

				fseek($file, $i * $partSize);
				$data = fread($file, $uploadPartSize);
				$real_md5 = md5($data);

				//echo "Completed: part:$i/$partCount size:$uploadPartSize PartNumber:".$partETag['PartNumber']. " ETag:".$partETag['ETag']." realmd5:$real_md5\n";

				if (strtolower($partETag['ETag']) != $real_md5)
					return $this->return_ex(-2, "md5 not match");
			}

			//echo "call completeMultipartUpload $key \n";
			$result =  $client->completeMultipartUpload(array(
					'Bucket' => $bucket,
					'Key' => $key,
					'UploadId' => $uploadId,
					'PartETags' => $partETags,
			));
		} catch (OSSException $ex) {
			return $this->return_ex(-1, "multipartUpload failed. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-2, "multipartUpload failed. ClientExcetpion, Message: " . $ex->getMessage());
		}

		return $this->return_ex(0, "Completed: " . $result->getETag());
	}

	//操作arena_time 历史记录表
	function history_account_arena_time($data, $type = 'save'){
		$arena_time_model = M("history_account_arena_time");
		$condition = array();
		$condition['account_id'] = $data['account_id'];
		$condition['arena_id'] = $data['arena_id'];
		$arena_time_data = $arena_time_model->where($condition)->select();
		if($arena_time_data === false)
			return false;
		if(count($arena_time_data) < 1 || $type == 'add'){
			$data['create_time'] = time();
			$resule = $arena_time_model->add($data);
		}else{
			$data['leave_time'] = time();
			$resule = $arena_time_model->where($condition)->order("id desc")->limit(1)->save($data);
		}
		return true;
	}

}
