<?php
namespace Home\Controller;
use Home\Controller\BaseController;
use Think\Log;
use Think\Model;
class ClientController extends BaseController
{
	/*
	 设备登录。
	请求格式：形如http://localhost/api.php?m=Client&a=device_login&deviceid=xxx&ver=xxx&type=xxx&pid=xxx&logintoken=xxx&autonewacc=1
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	type是长度不超过32字节的字符串，表示客户端的类型，比如stb_xiaomi2。
	ver是32位整数，表示客户端的版本号。
	pid是长度不超过64字节的字符串，代表一个唯一的渠道。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","device":{"name":"\u7528\u62371403759007666","login_token":"pznhg7aZsCtnfWh4ko38UQLbJDSh0An3","account":{"id":"1499","nickname":"\u7528\u62371403759007666","level":"100","exp":"116499","phone":"","email":"","bean":"4000","coin":"0","gold":"0"}}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	device.name是一个长度不超过64字节的字符串，表示该设备的名称。
	device.login_token是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	device.account.id是一个32位整数，表示该账户的ID。
	device.account.status是一个32位整数，表示该账户是否启用。
	device.account.level是一个32位整数，表示该账户的等级。
	device.account.exp是一个32位整数，表示该账户的经验值。
	device.account.nickname是一个长度不超过64字节的字符串，表示该账户的昵称。
	device.account.email是一个长度不超过64字节的字符串。
	device.account.phone是一个长度不超过24字节的字符串。
	device.account.bean是一个32位整数，表示剩余多少云豆。
	device.account.coin是一个32位整数，表示剩余多少云贝。
	device.account.gold是一个32位整数，表示剩余多少G币。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无法更新设备信息
	-105:未找到设备绑定的帐号
	-106:暂不支持在该设备上运行
	非零值均表示失败。
	*/
	public function device_login()
	{
		G('begin');
		$deviceid = I('deviceid','');
		$ver = I('ver','');
		$type = I('type','');
		$pid = I('pid','');
		$autoname = I('autoname',0);
		$logintoken = I('logintoken','');
		$autonewacc = I('autonewacc',0);
		$paccount = I('paccount','');
		$nickname = I('nickname','');
		$als = I('als',0);
		$clientip = get_client_ip();

		if ($deviceid == '')
			return $this->respond(-100, "无效请求，设备UUID为空。");
		if ($ver == '')
			$ver = I('version','');
		// get device info
		$account_model=D("Account");
		$device_model = D('Device');
		$device = array();
		$field="id,bind_account,byname,ip,region,isp_id,isp,last_login_time,login_token";
		$device_info = $device_model->get_info_by_uuid($deviceid,$field);
		//新设备第一次访问
		if (!$device_info) {
			$data = array();
			$data["device_uuid"] = $deviceid;
			$data["bind_account"] = 0;
			$data["client_type"] = $type;
			$data["client_ver"] = $ver;
			$data["byname"] = "";
			$data["pid"] = $pid;
			$data["ip"] = $clientip;
			//获取ip地区改成计划任务
			$data["region_id"] = 0;
			$data["region"] = "";
			$data["isp_id"] = 0;
			$data["isp"] = "";
			$data["create_time"] = $data["update_time"] = time();
			$data["login_token"] = "";
			$data["last_login_time"] = time();
			$db_new_id = $device_model->add_data($data);
			if (!$db_new_id)
				return $this->respond(-101, "数据库无法记录新增设备。");
			$device["id"] = $db_new_id;
			$device["bind_account"] = 0;
			$device["byname"] = "";
			$device["ip"] = $data["ip"];
			$device["region_id"] = $data["region_id"];
			$device["region"] = $data["region"];
			$device["isp_id"] = $data["isp_id"];
			$device["isp"] = $data["isp"];
			$device["last_login_time"] = time();
			$device["login_token"] = "";
		}
		else {
			// find the device
			$device = $device_info;
		}
		$device_update = array();
		// 查找是否是已绑定的第三方帐号，如果是，则赋值给bind_account；如果不是，则新建帐号并赋值。
		if ($autonewacc == 1 && ($pid == "galabox" || $pid == "galapad") && $paccount != "") {
			$account_external_model = D("AccountExternal");
			$ret = $account_external_model->find_account("gala", $paccount);
			if ($ret) {
				$device_update['bind_account'] = $ret['account_id'];
				$device["bind_account"] = $ret['account_id'];
			}
			else {
				// 只要是新的第三方账户，一定新建账户
				$device["login_token"] = "";
				$avatar = "";
				if ($autoname == 1) {
					if ($nickname == "")
						$nickname = $this->get_auto_name();
					$avatar = $this->get_auto_avatar();
				}

				// 自动创建帐号
				$account_id = $this->create_account($account_model, $nickname, $avatar);
				if($account_id === false)
					return $this->respond(-904, "创建新帐号失败。");

				$device_update['bind_account'] = $account_id;
				$device["bind_account"] = $account_id;

				// 新创建帐号，在account_external表中添加记录
				$ret = $account_external_model->save_data($account_id, "gala", $paccount);
				if (!$ret)
					return $this->respond(-905, "无法添加第三方帐号绑定记录: $paccount");
			}
		}

		//获取account账号模型对象
		if ($device["bind_account"] == 0) {
			$device["login_token"] = "";

			// 如果允许不自动创建帐号，则返回错误值
			if ($autonewacc != 1)
				return $this->respond_ex(-102, "当前设备尚未绑定账号。", "device", $device);
			$avatar = "";
			if ($autoname == 1) {
				if ($nickname == "")
					$nickname = $this->get_auto_name();
				$avatar = $this->get_auto_avatar();
			}

			// 自动创建帐号
			$account_id = $this->create_account($account_model, $nickname, $avatar);
			if($account_id === false)
				return $this->respond(-904, "创建新帐号失败。");

			$device_update['bind_account'] = $account_id;
			$device['bind_account'] = $account_id;
		}
		// 如果logintoken为空，说明是登录操作，生成一个新的token
		if ($logintoken == "") {
			$device["login_token"] = $this->generateRandomString(32);
			$device_update["login_token"] = $device["login_token"];
			$device_update["last_login_time"] = time();
		}
		else if ($logintoken != $device["login_token"])
			return $this->respond(-103, "登录令牌不正确。");
		else
			;
		// 说明是获取账户信息的操作，不重新生成token
		// update device info
		$device_update["client_type"] = $type;
		$device_update["client_ver"] = $ver;
		$device_update["pid"] = $pid;
		$db_ret = $device_model->save_data($deviceid, $device_update);

		if ($db_ret === false)
			return $this->respond_ex(-104, "无法更新当前设备的信息", "device", $device);
		// get account inf
		$db_account=$account_model->get_info_by_id($device["bind_account"]);
		if (!$db_account || $db_account['status'] != 1)
			return $this->respond_ex(-105, "未找到当前设备绑定的帐号 ".$device["bind_account"], "device", $device);

		$account = array("id"=>$device["bind_account"],
				"nickname"=>$db_account["nickname"],"avatar"=>$db_account["avatar"], "level"=>$db_account["level"], "exp"=>$db_account["exp"],
				"phone"=>$db_account["bind_phone"], "email"=>$db_account["bind_email"],
				"bean"=>$db_account["bean"],"coin"=>$db_account["gift_coin_num"],"gold"=>$db_account["gold"], // 新的客户端使用这三个字段
				"gift_coin"=>$db_account["gift_coin_num"], "bought_coin"=>"0"); // 旧的客户端还需要这两个字段
		$account['curr_lvl_exp'] = $account['next_lvl_exp'] = 100000000;
		$lvl_exp = C('LEVEL_EXP');
		if ($db_account["level"] >= 0) {
			if ($db_account["level"] < count($lvl_exp)-1)
				$account['curr_lvl_exp'] = $lvl_exp[$db_account["level"]];
			if ($db_account["level"] < count($lvl_exp))
				$account['next_lvl_exp'] = $lvl_exp[$db_account["level"]+1];
		}

		// 如果允许自动生成名字，则自动生成名字和头像
		if ($autoname == 1) {
			if($account['avatar'] == "")
				$account['avatar'] = $this->get_auto_avatar($device["bind_account"]);
			if($account['nickname'] == "")
				$account['nickname'] = $this->get_auto_name($device["bind_account"]);
		}

		// 如果昵称有变化，则更新数据库
		if ($nickname != "" && $nickname != $account['nickname']) {
			$update_data = array();
			$update_data['nickname'] = $nickname;
			$db_res = $account_model->save_data($device['bind_account'],$update_data);
			if($db_res === false){
				return $this->respond_ex(-107, "更新昵称失败", "device", $device);
			}
			$account['nickname'] = $nickname;
		}

		// 如果需要的话，返回擂台赛ALS地址
		if ($als == 1) {
			$device["als"]["host"] = C('ALS_HOST');
			$device["als"]["port"] = C('ALS_port');
		}

		$account['show_code_page'] = 0;
		$device["account"] = $account;
			
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "device", $device,$e_time);
	}
	/*
	 设备退出登录。
	请求格式：形如http://localhost/api.php?m=Client&a=device_logout&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success"}
	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:login_token不正确
	-102:更新设备信息失败
	非零值均表示失败。
	*/
	public function device_logout()
	{
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		if ($deviceid == '')
			return $this->respond(-100, "无效请求。当前设备的UUID为空。");
		//实例化devicemodel
		$device_model=D("Device");
		//获取该设备的信息
		$device_info=$device_model->get_info_by_uuid($deviceid);
		if (!$device_info || ($device_info['login_token']!=$logintoken))
			return $this->respond(-101, "当前设备的登录信息不正确。");
		$update["login_token"] = "";
		$res=$device_model->save_data($deviceid,$update);
		if ($res === false)
			return $this->respond(-102, "无法记录当前设备退出登录的信息。");
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond(0, "success",$e_time);
	}
	/*
	 向手机发送验证码，最短间隔1分钟。
	请求格式：形如http://localhost/api.php?m=Client&a=phone_verify&deviceid=xxx&phone=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	phone是长度不超过32字节的字符串，代表一个手机号码。
	返回格式：
	{"ret":0,"msg":"success"}
	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:无法查询绑定表
	-102:已经达到单IP每日发送上限
	-103:已经达到单手机号每日发送上限
	-104:一分钟内只能发送一条
	-105:无法更新绑定表
	-120:发送手机验证码失败
	10100 : please confirm param the mobile is null or the mobile num big 100!
	10200 : please confirm param the mobile is null or the mobile num big 200!
	10300 : please confirm param the content of length!
	10400 : curl_errno has a errno!
	非零值均表示失败。
	*/
	public function phone_verify(){
		G('begin');
		$deviceid = I('deviceid','');
		$phone = I('phone','');
		$is_logintoken = I('logintoken','');
		$clientip = get_client_ip();
		if ($deviceid == '' || $phone == '')
			return $this->respond(-100, "无效请求。设备UUID或者手机号为空。");
		//判断手机号是否是正确的格式
		$ret = $this->is_check_phone($phone);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		// 登录状态检查该Phone是否被别的账户使用了
		if(!empty($is_logintoken)){
			$account_model = D('Account');
			$account_info=$account_model->get_info_by_phone($phone);
			if($account_info)
				return $this->respond(-108, "当前手机已经被其他帐号使用。如果这是你的手机号，你可以使用账户中心的切换帐号功能，切换到该手机号对应的帐号。");
		}
		// 检查每个IP每天最多发送多少条验证码，目前设置最大值为200
		$tmp_bind_model=D("TmpBind");
		$ip_num=$tmp_bind_model->get_num_by_ip($clientip);
		if ($ip_num > 200)
			return $this->respond(-102, "每个IP每天最多发送的短信数已达限制。");

		// 检查每个手机每天最多发送多少条验证码，目前设置最大值为20
		$phone_num=$tmp_bind_model->get_num_by_phone_or_email($phone);
		if ($phone_num > 20)
			return $this->respond(-103, "每个手机号每天最多发送的短信数已达限制。");

		// 检查是否有之前的发送记录
		$condition['date']=date("Y-m-d");
		$condition['phone_or_email'] = $phone;
		$condition['verify_done_time'] = 0;

		$last_info = $tmp_bind_model->get_last_row($condition);
		if ($last_info) {
			// 如果已经有记录，且上次发送时间还没超过180秒，则拒绝
			$sec = time() - $last_info["verify_sent_time"];
			if ( $sec < 180)
				return $this->respond(-104, "请在".(180-$sec)."秒后重试。");
		}
		// 生成4位验证码
		$random_code = mt_rand(1000, 9999);
		$mobile_arr = array($phone);
		$content = "【".C('SEO_TITLE')."】".$random_code."（手机动态码，用于验证登录或其他重要操作，请在30分钟内使用，切勿告知他人。）【云游戏】";
		// 写入发送记录
		$insert_temp_data = array();
		$insert_temp_data["account_id"] = 0;
		$insert_temp_data["device_uuid"] = $deviceid;
		$insert_temp_data["ip"] = $clientip;
		$insert_temp_data["phone_or_email"] = $phone;
		$insert_temp_data["verify_sent_time"] = time();
		$insert_temp_data["verify_done_time"] = 0; //需要确认
		$insert_temp_data["random_code"] = $random_code;
		$insert_temp_data["error_code"] = 1; //默认初始值
		$insert_temp_data['date']=date("Y-m-d");
		$db_ret = $tmp_bind_model->add_data($insert_temp_data);
		if (!$db_ret)
			return $this->respond(-105, "无法添加短信发送记录。");
		// 带test的表示是测试数据，允许通过
		if (strpos($phone, "test.com") !== false) {
			$db_ret = 0;
		}
		else {
			//加载短信类并发送验证信息
			$sms_event=A('Sms','Event');
			$response_data = $sms_event->SmsSend($mobile_arr,$content,$db_ret);
			if ($response_data['ret'] != 0 || !isset($response_data['response']['status'])) {
				return $this->respond(-106,"短信发送失败。代码:".$response_data['ret'].' 消息:'.$response_data['msg']);
			}
			if ($response_data['response']['status'] != 0){
				$update=$condition=array();
				$condition['id'] = $db_ret;
				$update['error_code'] = $response_data['response']['status'];
				$tmp_bind_model->save_send_code($condition,$update); //记录短信发送失败时的错误码
				return $this->respond(-107,"短信发送失败。状态码：".$response_data['response']['status']);
			}
			$update=$condition=array();
			$condition['id'] = $db_ret;
			$update['error_code'] = $response_data['response']['status'];
			$tmp_bind_model->save_send_code($condition,$update);//记录短信发送成功时的状态码
		}
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond(0, "success",$e_time);
	}
	/**
	 * @time  2014/07/31
	 * @desc 用验证码验证用户手机是否为本人及正确的电话号
	 *	请求格式：形如http://localhost/game/api.php?m=Client&a=phone_activate&deviceid=xxx&phone=xxx&logintoken=xxxx&phone=xxxx&random_code=xxxx
	 *	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	 *	phone是长度不超过32字节的字符串，代表一个手机号码。
	 *	返回格式：
	 *	{"ret":0,"msg":"success"}
	 *	ret是一个32位整数，具体值定义见下方。
	 *	msg是一个长度不超过128字节的字符串。
	 *	ret定义：
	 *	0: 成功
	 * -101 : device $deviceid is not found.
	 * -102	: device $deviceid was not bind to an account.
	 * -103	: login token of device $deviceid doesn't match.".$db_device[0]["login_token"]."=$logintoken"
	 * -105 : the db_device is not right!
	 * -106 : the random code is not found or invalid!
	 * -107	: invalid random code .$random_code!
	 * -108	: create account is fail!
	 * -109 : the device bind fail the new account!
	 * -110 : the random code is time out.
	 * -111 : this phone is used by another account.
	 * -112 : fail to enum devices of account.
	 * -113 : fail to unbind existing device.
	 * -130 : fail to update has existed account!
	 * -170  ：fail to update account table the field of the bind_phone!
	 * -173 : Renewal of equipment status of migration failure
	 *	非零值均表示失败。
	 *
	 */
	public function phone_activate(){
		G('begin');
		$deviceid = I('deviceid','');
		$random_code = I('random_code','');
		$phone = I('phone','');
		$logintoken = I('logintoken','');
		if($phone==''){
			return $this->respond(-100, "无效请求。手机号码为空。");
		}
		//判断手机号是否是正确的格式
		$ret = $this->is_check_phone($phone);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		//查询该设备发送的最后一个验证码
		$condition['device_uuid'] = $deviceid;
		$condition['phone_or_email'] = $phone;
		$condition['random_code'] = $random_code;
		$db_tmp_bind = D('TmpBind');
		$data_tmp_bind = $db_tmp_bind->get_last_row($condition);
		if(!$data_tmp_bind)
			return $this->respond(-106, "验证码 $random_code 不存在或者已失效。");
		if ($data_tmp_bind['verify_done_time'] != 0)
			return $this->respond(-120, "验证码 $random_code 已经使用过。");
		$tmp_random_code = $data_tmp_bind['random_code'];
		if($random_code != $tmp_random_code)
			return $this->respond(-107, "验证码 $random_code 不正确。");
		//判断验证码是否已经超时
		$is_time_out = time() - (int)$data_tmp_bind['verify_sent_time'];

		if ($is_time_out < 0 || $is_time_out > 1800)
			return $this->respond(-110, "验证码 $random_code 已过期。请重新发送。");
		$account_id = 0;
		if(strlen(trim($logintoken))>0){
			// 如果$logintoken非空，则说明是已登录的账户要绑定手机
			$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
			if ($ret['ret'] != 0)
				return $this->respond($ret['ret'], $ret['msg']);
			$db_device = $ret['msg'];
			$account_id = $db_device['bind_account'];

			// 检查该手机是否被别的账户使用了
			$db_account=D("Account");
			$is_exsited_phone = $db_account->get_info_by_phone($phone);
			if (!$is_exsited_phone) {
				// 没人用这个手机，把手机更新到该帐号里
				$ret=$db_account->bind_phone($account_id,$phone);
				if(!$ret)
					return $this->respond(-170, "无法修改当前帐号的手机号。");
			}
			else if ($account_id != $is_exsited_phone['id'])
				return $this->respond(-111, "此手机号 $phone 已经被其他帐号使用。如果这是你的手机号，你可以使用用户中心的账号切换功能，切换到该手机号对应的帐号。");
			else
				; // this phone is already binded to this account
		}else {
			// 如果logintoken为空，说明是登录过程。
			// 检查该手机是否已经注册了账户
			$device_model=D("Device");
			$db_account=D("Account");
			$is_exsited_phone = $db_account->get_info_by_phone($phone);
			if (!$is_exsited_phone) {
				// 该手机没人用，可以创建一个新帐号了。
				$ret = $this->create_account($db_account,"","","",$phone);
				if(!$ret)
					return $this->respond(-108, "创建新帐号失败。");
				$account_id = $ret; // new account id
			}
			else {
				// 该手机已经注册了账户，那么就把当前设备绑定到这个账户上吧。
				$account_id = $is_exsited_phone['id'];
				// 存在单个帐号绑定设备个数的限制，检查该账户绑定的设备个数
				$SINGLE_ACCOUNT_MULTI_DEVICE = C('SINGLE_ACCOUNT_MULTI_DEVICE');
				// get device list
				$db_device=$device_model->get_bind_device_by_account($account_id);
				if ($db_device === false)
					return $this->respond(-112, "无法枚举账号 $account_id 绑定的设备列表。");
				// 如果超出了设备个数限制，则解绑一个现有设备，目前选择“最近一次登录时间最久远的那个”。
				if (count($db_device) >= $SINGLE_ACCOUNT_MULTI_DEVICE) {
					$unbind_device=$db_device[count($db_device)-1]['device_uuid'];
					$db_ret=$device_model->unbind_device($account_id,$unbind_device);

					if (!$db_ret)
						return $this->respond(-113, "受到绑定设备个数限制。但是无法解绑旧的设备。");
				}
			}
			// 更新device 中的bind_account字段
			$update = array();
			$update['bind_account'] = $account_id;
			$ret=$device_model->save_data($deviceid,$update);
			if($ret === false)
				return $this->respond(-109, "无法将当前设备绑定到新帐号上。");
		}
		//更新verify_done_time
		$condition = array();
		$condition['device_uuid'] = $deviceid;
		$condition['phone_or_email'] = $phone;
		$condition['random_code'] = $random_code;
		$update = array();
		$update['verify_done_time'] = time();
		$update['account_id'] = $account_id;
		$update['error_code'] = 0; // success
		$ret = $db_tmp_bind->save_send_code($condition,$update);
		// 忽略错误，因为这一项更新失败也无所谓

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond(0, "the phone is activated!",$e_time);
	}
	/*
	 * @desc 给邮箱发送验证码
	* 接口请求路径 http://localhost/game/api.php?a=email_verify&logintoken=&m=Client&deviceid=ffffffff-8357-a407-0033-c5870033c587&email=XXX
	* $ret
	* -100：invalid request. deviceid is empty.
	* -101：fail to select table july_tmp_bind.
	* -102：max verify SMS per IP reached.
	* -103：max verify SMS per email reached.
	* -104：please wait another ".(60-$sec)." seconds
	* -105：fail to insert july_tmp_bind.
	* -106：send to email.$email. is fail!
	* -107 ：this email is used by another account.
	*/
	public function email_verify(){
		G('begin');
		$deviceid = I('deviceid','');
		$email = I('email','');
		$is_logintoken = I('logintoken','');
		$clientip = get_client_ip();
		//判断邮件的格式是否是正确的格式
		$ret = $this->is_check_email($email);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		if ($deviceid == '' || $email == '')
			return $this->respond(-100, "无效请求。设备UUID为空。");
		// 登录状态检查该Email是否被别的账户使用了
		if(!empty($is_logintoken)){
			$account_model=D("Account");
			$is_exsited_email=$account_model->get_info_by_email($email);
			if($is_exsited_email)
				return $this->respond(-108, "此邮箱 $email 已经被其他帐号使用。如果这是你的邮箱，你可以使用用户中心的账号切换功能，切换到该邮箱对应的帐号。");
		}
		// 检查每个IP每天最多发送多少条验证码，目前设置最大值为200
		$tmp_bind_model=D("TmpBind");
		$ip_num=$tmp_bind_model->get_num_by_ip($clientip);
		if ($ip_num > 200)
			return $this->respond(-102, "每个IP每天最多发送的邮件数已达限制。");

		// 检查每个手机每天最多发送多少条验证码，目前设置最大值为20
		$email_num=$tmp_bind_model->get_num_by_phone_or_email($email);
		if ($email_num > 20)
			return $this->respond(-103, "每个手机号每天最多发送的邮件数已达限制。");
		// 检查是否有之前的发送记录
		$condition['date']=date("Y-m-d");
		$condition['phone_or_email'] = $email;
		$condition['verify_done_time'] = 0;
		$last_info = $tmp_bind_model->get_last_row($condition);
		if ($last_info) {
			// 如果已经有记录，且上次发送时间还没超过180秒，则拒绝
			$sec = time() - $last_info["verify_sent_time"];
			if ( $sec < 30)
				return $this->respond(-104, "请在".(180-$sec)."秒后重试。");
		}

		// 生成4位验证码
		$random_code = mt_rand(1000, 9999);
		// 带test的表示是测试数据，允许通过
		if (strpos($email, "test.com") === false) {
			//发送邮件
			$subject = C('SEO_TITLE')."账户邮箱验证";
			$message = "欢迎成为".C('SEO_TITLE')."的用户，开启快乐游戏世界！ \r\n\r\n";
			$message.= "为了保障您的帐号安全，建议您尽快进行邮箱验证。 \r\n";
			$message.= "邮箱验证码：".$random_code."\r\n";
			$message.= "该验证码仅在30分钟内有效，如果超过30分钟，请返回客户端重新发送。 \r\n\r\n";
			$message.= "感谢您的使用！ \r\n";
			$message.= "\r\n\r\n此邮件由系统自动发送，请勿回复。 \r\n";
			$email_event=A("Email","Event");
			$res=$email_event->sendMail($email, $subject,$message);
			if(!$res)
			{
				return $this->respond(-106, "无法发送验证信到 $email");
			}

		}
		// 写入发送记录
		$insert_temp_data = array();
		$insert_temp_data["account_id"] = 0;
		$insert_temp_data["device_uuid"] = $deviceid;
		$insert_temp_data["ip"] = $clientip;
		$insert_temp_data["phone_or_email"] = $email;
		$insert_temp_data["verify_sent_time"] = time();
		$insert_temp_data["verify_done_time"] = 0; //需要确认
		$insert_temp_data["random_code"] = $random_code;
		$insert_temp_data["date"] = date('Y-m-d');
		$db_ret = $tmp_bind_model->add_data($insert_temp_data);
		G('end');
		$e_time=G('begin','end').'s';
		if (!$db_ret)
			return $this->respond(-105, "无法添加发送验证信的记录。",$e_time);

		return $this->respond(0, "success",$e_time);
	}
	/* @desc 通过验证码验证邮箱的正确性
	 * 请求路径：http://localhost/game/api.php?a=email_activate&m=Client&deviceid=ffffffff-8357-a407-0033-c5870033c587&email=XXXX&random_code=XXXX
	* $ret
	* -106 : the random code is null
	* -107 : invalid random code .$random_code
	* -108 : create account is fail!
	* -109 : the device bind fail the new account!
	* -110 : the random code is time out.
	* -111 : this email is used by another account.
	* -112 : fail to enum devices of account.
	* -113 : fail to unbind existing device.
	* -170：fail to update account table the field of the bind_email!
	* -173 : Renewal of equipment status of migration failure
	*/
	public function email_activate(){
		G('begin');
		$deviceid = I('deviceid','');
		$random_code = I('random_code','');
		$email = I('email','');
		$logintoken = I('logintoken','');
		if($email==''){
			return $this->respond(-100, "无效请求。邮箱为空。");
		}
		//判断邮件的格式是否是正确的格式
		$ret = $this->is_check_email($email);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);

		$condition['device_uuid'] = $deviceid;
		$condition['phone_or_email'] = $email;
		$condition['random_code'] = $random_code;
		$db_tmp_bind = D('TmpBind');
		$data_tmp_bind = $db_tmp_bind->get_last_row($condition);
		if(!$data_tmp_bind )
			return $this->respond(-106, "验证码无效。");
		if ($data_tmp_bind['verify_done_time'] != 0)
			return $this->respond(-120, "验证码 $random_code 已经使用过。");
		$tmp_random_code = $data_tmp_bind['random_code'];
		if($random_code =='' || $random_code != $tmp_random_code)
			return $this->respond(-107, "错误的验证码：$random_code");
		//判断验证码是否已经超时
		$is_time_out = time() - (int)$data_tmp_bind['verify_sent_time'];
		if ($is_time_out < 0 || $is_time_out > 86400)
			return $this->respond(-110, "验证码已经超时。请重新发送。");

		$account_id = 0;
		$account_model=D("Account");
		$device_model=D("Device");
		if(strlen(trim($logintoken))>0){
			// 如果$logintoken非空，则说明是已登录的账户要绑定邮箱
			$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
			if ($ret['ret'] != 0)
				return $this->respond($ret['ret'], $ret['msg']);
			$db_device = $ret['msg'];
			$account_id = $db_device['bind_account'];
			// 检查该Email是否被别的账户使用了

			$is_exsited_email=$account_model->get_info_by_email($email);
			if (!$is_exsited_email) {
				// 没人用这个Email，把email更新到该帐号里
				$data['bind_email']=$email;
				$ret=$account_model->save_data($account_id,$data);
				if(!$ret)
					return $this->respond(-170, "无法更新当前设备的邮箱。");
			}
			else if ($account_id != $is_exsited_email['id'])
				return $this->respond(-111, "此邮箱 $email 已经被其他帐号使用。如果这是你的邮箱，你可以使用用户中心的账号切换功能，切换到该邮箱对应的帐号。");
			else
				; // this email is already binded to this account.
		}
		else {
			// 如果logintoken为空，说明是登录过程。
			// 检查该Email是否已经注册了账户
			$is_exsited_email = $account_model->get_info_by_email($email);
			if (!$is_exsited_email) {
				// 该Email没人用，可以创建一个新帐号了。
				$ret = $this->create_account($account_model,"","",$email,"");
				if(!$ret)
					return $this->respond(-108, "创建新帐号失败。");
				$account_id = $ret; // new account id
			}
			else {
				// 该Email已经注册了账户，那么就把当前设备绑定到这个账户上吧。
				$account_id = $is_exsited_email['id'];
				// 存在单个帐号绑定设备个数的限制，检查该账户绑定的设备个数
				$SINGLE_ACCOUNT_MULTI_DEVICE = C('SINGLE_ACCOUNT_MULTI_DEVICE');
				// get device list

				$db_device=$device_model->get_bind_device_by_account($account_id);
				if ($db_device === false)
					return $this->respond(-112, "无法枚举当前帐号 $account_id 的已绑定设备。");

				// 如果超出了设备个数限制，则解绑一个现有设备，目前选择“最近一次登录时间最久远的那个”。
				if (count($db_device) >= $SINGLE_ACCOUNT_MULTI_DEVICE) {
					$db_ret =$device_model->unbind_device($account_id,$db_device[count($db_device)-1]['device_uuid']);
					if (!$db_ret)
						return $this->respond(-113, "受到绑定设备个数限制。但是无法解绑旧的设备。");
				}
			}
			// 更新device 中的bind_account字段
			$data['bind_account']=$account_id;
			$ret=$device_model->save_data($deviceid,$data);
			if($ret === false)
				return $this->respond(-109, "无法将当前设备绑定到新帐号。");
		}

		//更新verify_done_time
		$condition = array();
		$condition['device_uuid'] = $deviceid;
		$condition['phone_or_email'] = $email;
		$condition['random_code'] = $random_code;
		$update = array();
		$update['verify_done_time'] = time();
		$update['account_id'] = $account_id;
		$update['error_code'] = 0; // success
		$ret = $db_tmp_bind->save_send_code($condition,$update);
		// 忽略错误，因为这一项更新失败也无所谓
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond(0, "the email is activated!",$e_time);
	}
	/*
	 获取绑定设备列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=device_list&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","devices":[{"id":"1499","name":"\u7528\u62371403759007666","client_ver":"12312","client_type":"stb_test","last_login_time":"1406516701"},{"id":"1500","name":"\u7528\u62371403767390470","client_ver":"20140710","client_type":"stb_damai","last_login_time":"0"},{"id":"1501","name":"\u7528\u62371403767764286","client_ver":"20140630","client_type":"stb_damai","last_login_time":"0"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	devices.id是一个32位整数，表示该设备的编号。
	devices.name是一个长度不超过64字节的字符串，表示该设备的名称。
	devices.client_ver是一个32位整数，表示该设备上的客户端版本。
	devices.client_type是一个长度不超过64字节的字符串，表示该设备的类型。
	devices.last_login_time是一个32位整数，表示该设备上次登录的时间。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:没有找到账号对应的设备列表
	非零值均表示失败。
	*/
	public function device_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		// get device list
		$device_model=D("Device");
		$device_list=$device_model->get_bind_device_by_account($db_device["bind_account"]);
		if ($device_list === false)
			return $this->respond(-104, "无法查询当前账号".$db_device["bind_account"]."的绑定设备列表。");
		$devices = array();
		foreach ($device_list as $device) {
			$d = array("id"=>$device["id"], "name"=>$device["byname"], "client_ver"=>$device["client_ver"], "client_type"=>$device["client_type"], "last_login_time"=>$device["last_login_time"]);
			array_push($devices, $d);
		}
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "devices", $devices,$e_time);
	}
	/*
	 设备解除绑定，必须由一个已登录的设备发起，可以解绑自身或者其他已绑定设备。
	请求格式：形如http://localhost/api.php?m=Client&a=device_unbind&deviceid=xxx&logintoken=xxx&unbinddeviceid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	unbinddeviceid是Int32，代表一个唯一的待解绑设备。
	返回格式：
	{"ret":0,"msg":"success"}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:login_token不正确
	-102:解绑设备失败
	非零值均表示失败。
	*/
	public function device_unbind(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$unbinddeviceid = I('unbinddeviceid','');
		if ($deviceid == '' || $unbinddeviceid == '')
			return $this->respond(-100, "无效请求。当前设备UUID或待解绑设备UUID为空。");
		// get device info
		$device_model=D("Device");
		$device_info=$device_model->get_info_by_uuid($deviceid);
		if (!$device_info || ($device_info['login_token'] !=$logintoken))
			return $this->respond(-101, "未找到当前设备或者登录令牌错误。");
		// 解绑指定设备
		$res=$device_model->unbind_device($device_info['bind_account'],$unbinddeviceid);
		G('end');
		$e_time=G('begin','end').'s';
		if ($res === false)
			return $this->respond(-102, "无法解绑该设备。",$e_time);
		return $this->respond(0, "success",$e_time);
	}
	/*
	 获取区域信息，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=region_info&deviceid=xxx&logintoken=xxx&regionid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","region":{"id":"1","name":"\u5929\u6d25","status":"1","level":"0","speed_test_addr":"211.161.90.49","speed_test_addr_backup":"211.161.90.49","speed_test_port":"8081","gsm_addr":"124.202.146.241","gsm_port":"8080","full_load":0}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	region.id是一个32位整数，表示该区域的编号。
	region.name是一个长度不超过64字节的字符串，表示该区域的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:绑定帐号不存在
	-105:对应级别的区域不存在
	非零值均表示失败。
	*/
	public function region_info(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$regionid = I('regionid',0);
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$device_info = $ret['msg'];
		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$account_model=D("Account");
		$account_info=$account_model->get_info_by_id($device_info['bind_account']);
		if (!$account_info)
			return $this->respond(-104, "未找到当前账号：".$device_info["bind_account"]);
		// 读取区域信息
		$region_model=D("Region");
		$field="id,name,status,level,speed_test_addr,speed_test_addr_backup,speed_test_port,gsm_addr,gsm_port";
		$region_info=$region_model->get_info_by_id($regionid,$field);
		if ($region_info === false)
			return $this->respond(-105, "无法查询当前区域信息。");
			
		if ($region_info)
		{
			$region_info["full_load"] = 0;
		}
		else
			return $this->respond(-106, "没找到当前区域。");

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "region",$region_info,$e_time);
	}
	/*
	 获取区域列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=region_list&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","regions":[{"id":"1","name":"\u5929\u6d25","status":"1","level":"0","speed_test_addr":"211.161.90.49","speed_test_addr_backup":"211.161.90.49","speed_test_port":"8081","gsm_addr":"124.202.146.241","gsm_port":"8080","full_load":0},{"id":"2","name":"\u9752\u5c9b","status":"1","level":"0","speed_test_addr":"175.190.255.186","speed_test_addr_backup":"175.190.255.186","speed_test_port":"8081","gsm_addr":"124.202.146.241","gsm_port":"8080","full_load":0}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	regions.id是一个32位整数，表示该区域的编号。
	regions.name是一个长度不超过64字节的字符串，表示该区域的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:绑定帐号不存在
	-105:对应级别的区域不存在
	非零值均表示失败。
	*/
	public function region_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		//新增 pid渠道参数
		$pid=I("pid");

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$bind_account=$db_device['bind_account'];
		$account_model=D("Account");
		$account_info=$account_model->get_info_by_id($bind_account);
		if (!$account_info)
			return $this->respond(-104, "未找到当前账号：".$bind_account);
		// 读取区域列表
		$field="id,name,status,level,speed_test_addr,speed_test_addr_backup,speed_test_port,gsm_addr,gsm_port";
		$region_model=D("Region");
		if($pid)
		{
			$region_list=$region_model->get_region_data_by_pid($pid,$field);
		}else
			$region_list=$region_model->get_all_region_data($field);
		$regions=array();
		foreach ($region_list as $region) {
			if($region['level']<=$account_info['level'])
			{
				$region["full_load"] = 0;
				array_push($regions, $region);
			}
		}
		G('end');
		$e_time=G('begin','end').'s';
		if ($region_list === false)
			return $this->respond(-105, "无法查询区域列表。",$e_time);
		return $this->respond_ex(0, "success", "regions",$regions,$e_time);
	}
	/*
	 获取游戏类别列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=gamecat_list&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","categories":[{"cat_id":"3","cat_name":"\u5c04\u51fb\u7c7b","create_time":"1406522282","status":"1","summary":""},{"cat_id":"2","cat_name":"\u89d2\u8272\u626e\u6f14","create_time":"1406522224","status":"1","summary":""},{"cat_id":"1","cat_name":"\u52a8\u4f5c\u7c7b","create_time":"1406522168","status":"1","summary":"\u52a8\u4f5c\u7c7b\u7684\u6e38\u620f"},{"cat_id":"4","cat_name":"\u4f53\u80b2\u7c7b","create_time":"0","status":"1","summary":"0"},{"cat_id":"5","cat_name":"\u683c\u6597\u7c7b","create_time":"0","status":"1","summary":"0"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	categories.cat_id是一个32位整数，表示该类别的编号。
	categories.cat_name是一个长度不超过64字节的字符串，表示该类别的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到游戏类别
	非零值均表示失败。
	*/
	public function gamecat_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		// 读取游戏类别列表
		$gamecatgory_model=D("Gamecategory");
		$cat_arr=$gamecatgory_model->get_all_api_data();
		G('end');
		$e_time=G('begin','end').'s';
		if ($cat_arr === false)
			return $this->respond(-101, "无法查询游戏类别列表。 ",$e_time);

		return $this->respond_ex(0, "success", "categories", $cat_arr,$e_time);
	}
	/*
	 获取游戏类别里的游戏列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=game_list&deviceid=xxx&logintoken=xxx&cat=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	cat是一个32位整数，表示该类别的编号。
	返回格式：
	{"ret":0,"msg":"success","games":[{"game_id":"23","game_name":"\u6234\u65af\u73ed\u514b","coin":"2000","max_player":"1","status":"1","level":"0","save_enabled":"0","title_pic":""},{"game_id":"24","game_name":"\u6234\u65af\u73ed\u514b\uff1a\u96ea\u5c71\u5730\u7262","coin":"1500","max_player":"1","status":"1","level":"0","save_enabled":"0","title_pic":""}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	games.game_id是一个32位整数，表示该游戏的编号。
	games.game_name是一个长度不超过64字节的字符串，表示该游戏的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:绑定帐号不存在
	-105:对应级别的游戏不存在
	非零值均表示失败。
	*/
	public function game_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$cat_id = I('cat',0);
		//新增渠道
		$pid=I("pid");
		if($pid)
		{
			$pid_set_model=D("PidSet");
			$deny_game_arr=$pid_set_model->get_info_by_pid($pid);
			$deny_game_arr=explode(',', $deny_game_arr['deny_gid']);
		}

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$account_model=D("Account");
		$db_account=$account_model->get_info_by_id($db_device["bind_account"]);
		if (!$db_account)
			return $this->respond(-104, "未找到当前账号：".$db_device["bind_account"]);
		$visible_level = max(C("VISIBLE_GAME_LEVEL"), $db_account['level']);
		// 读取游戏类别内的游戏列表
		$link_gamecategory_game_model=D("LinkGamecategoryGame");
		$game_arr=$link_gamecategory_game_model->get_game_by_catid($cat_id);
		$games=array();
		$game_model=D("Game");
		$cnt=count($game_arr);
		for($i=0;$i<$cnt;$i++)
		{
			$game_id=$game_arr[$i]['game_id'];
			$field = 'game_id,category,game_name,max_player,status,level,save_enabled,title_pic,controller,trial_time';
			$game_info=$game_model->get_info_by_id($game_id, $field);
			if($game_info['status']==1 && $game_info['level']<=$visible_level)
			{
				if($pid)
				{
					if(!in_array($game_info['game_id'], $deny_game_arr))
					{
						$games[]=$game_info;
					}
				}
				else
					$games[]=$game_info;
					
			}
		}
		// 更新游戏的虚拟币价格
		$chargepoint_model=D("Chargepoint");
		$chargepoint_runonce_model=D("ChargepointRunonce");
		$chargepoint_arcade_model=D("ChargepointArcade");
		//循环游戏列表
		foreach ($games as &$game) {
			$chargepoint_runonce_info=$chargepoint_runonce_model->get_info_by_game_id($game['game_id']);
			$chargepoint_arcade_info=$chargepoint_arcade_model->get_info_by_game_id($game['game_id']);
			if($chargepoint_arcade_info)
			{
				$chargepoint_id=$chargepoint_arcade_info['chargepoint_id'];
				$chargepoint_info=$chargepoint_model->get_info_by_id($chargepoint_id);
				if($chargepoint_info['status']==1)
				{
					$game['bean']=$chargepoint_info['bean'];
					$game['coin']=$chargepoint_info['coin'];
					$game['gold']=$chargepoint_info['gold'];
				}
			}elseif($chargepoint_runonce_info)
			{
				$chargepoint_id=$chargepoint_runonce_info['chargepoint_id'];
				$chargepoint_info=$chargepoint_model->get_info_by_id($chargepoint_id);
				if($chargepoint_info['status']==1)
				{
					$game['bean']=$chargepoint_info['bean'];
					$game['coin']=$chargepoint_info['coin'];
					$game['gold']=$chargepoint_info['gold'];
				}
			}
		}
		// 查询所有的单款游戏包
		$gamepack_string = "";
		$gamepack_model=D("Gamepack");
		$link_gamepack_game_model=D("LinkGamepackGame");
		$gamepack_data=$gamepack_model->api_get_all_data();
		if($gamepack_data)
		{
			foreach($gamepack_data as $val)
			{
				$gamepack_id=$val['pack_id'];
				$game_data=$link_gamepack_game_model->get_all_game_by_packid($gamepack_id);
				if(count($game_data)==1)
				{
					$gamepack_string .= $gamepack_id.",";
				}
			}

		}
		// 查询所有单款游戏包对应的包月计费点
		if ($gamepack_string) {
			$gamepack_string=rtrim($gamepack_string,',');
			$db = M('chargepoint_gamepack');
			$condition = array();
			$condition['cp.status'] = 1;
			$condition['cpg.deadline_time_increase'] = 2678400; // 只查询包月的
			$condition['_string'] = "cp.id = cpg.chargepoint_id and cpg.gamepack_id in ($gamepack_string)";
			$db_ret = $db->table('july_chargepoint cp, july_chargepoint_gamepack cpg')->field('cp.id,cp.name,cp.type,cp.type_name,cp.bean,cp.coin,cp.gold,cpg.gamepack_id')->where($condition)->select();
			if ($db_ret != null && count($db_ret) > 0) {
				foreach($db_ret as $cp) {
					foreach($games as &$game) {
						if(isset($game["single_pack_id"]))
						{
							if ($game["single_pack_id"] == $cp["gamepack_id"])
								$game["chargepoints"] = $cp;
						}
					}
				}
			}
		}
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "games", $games,$e_time);
	}
	/*
	 获取游戏包信息，含包内游戏列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=game_pack&deviceid=xxx&logintoken=xxx&packid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	cat是一个32位整数，表示该类别的编号。
	返回格式：
	{"ret":0,"msg":"success","pack":{"pack_name":"\u96f7\u66fc\uff1a\u8d77\u6e90","create_time":"0","status":"1","summary":"\u96f7\u66fc\uff1a\u8d77\u6e90\u5355\u6b3e\u6e38\u620f\u5305","played_seconds":"0","deadline_time":"123","games":[{"game_id":"2","game_name":"\u96f7\u66fc\uff1a\u8d77\u6e90","coin":"500","max_player":"1","status":"1","level":"0","save_enabled":"0","title_pic":"","controller":"1"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	pack.create_time是一个32位整数，表示该游戏包的创建时间。
	pack.pack_name是一个长度不超过64字节的字符串，表示该游戏包的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:绑定帐号不存在
	-105:对应级别的游戏包不存在
	非零值均表示失败。
	*/
	public function game_pack(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$packid = I('packid',0);
		//新增渠道
		$pid=I("pid");
		if($pid)
		{
			$pid_set_model=D("PidSet");
			$deny_game_arr=$pid_set_model->get_info_by_pid($pid);
			$deny_game_arr=explode(',', $deny_game_arr['deny_gid']);
		}

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$account_model=D("Account");
		$db_account=$account_model->get_info_by_id($db_device["bind_account"]);
		if (!$db_account)
			return $this->respond(-104, "未找到当前账号：".$db_device["bind_account"]);
		$visible_level = max(C("VISIBLE_GAME_LEVEL"), $db_account['level']);
		// 读取游戏包信息
		$gamepack_model=D("Gamepack");
		$pack=$gamepack_model->get_info_by_packid($packid);
		if (!$pack || $pack['status'] == 0)
			return $this->respond(-105, "未找到游戏包：$packid");
		// 读取该账户是否拥有该游戏包
		$link_account_gamepack_model=D("LinkAccountGamepack");
		$db_purchase=$link_account_gamepack_model->get_info_by_account_id_gamepack_id($db_device["bind_account"],$packid);
		if (!$db_purchase || count($db_purchase) == 0)
			$pack["deadline_time"] = 0; // 表示尚未购买
		else {
			$pack["played_seconds"] = $db_purchase["played_seconds"];
			$pack["deadline_time"] = $db_purchase["deadline_time"];
			$pack["create_time"] = $db_purchase["create_time"];
		}
		$pack["expired"] = 0;
		if (time() > $pack["deadline_time"])
			$pack["expired"] = 1;
		// 读取游戏包内的游戏列表
		$link_gamepack_game_model=D("LinkGamepackGame");
		$game_model=D("Game");
		$game_arr=$link_gamepack_game_model->get_all_game_by_packid($packid);
		$game_data=array();
		if($game_arr)
		{
			foreach ($game_arr as $game_nifo)
			{
				$game_id=$game_nifo['game_id'];
				$field = 'game_id,game_name,coin,max_player,status,level,save_enabled,title_pic,category,controller,trial_time';
				$game_info=$game_model->get_info_by_id($game_id, $field);
				if($game_info['status']==1 && $game_info['level']<=$visible_level)
				{
					//$game_data[]=$game_info;
					if($pid)
					{
						if(!in_array($game_info['game_id'], $deny_game_arr))
						{
							$game_data[]=$game_info;
						}
					}
					else
						$game_data[]=$game_info;
				}
			}
		}
		if (count($game_data) > 0) {
			$pack["games"] = $game_data;
		}

		// 更新游戏的虚拟币价格
		$chargepoint_model=D("Chargepoint");
		$chargepoint_runonce_model=D("ChargepointRunonce");
		$chargepoint_arcade_model=D("ChargepointArcade");
		//循环游戏列表
		foreach ($pack["games"] as &$game) {
			$chargepoint_runonce_info=$chargepoint_runonce_model->get_info_by_game_id($game['game_id']);
			$chargepoint_arcade_info=$chargepoint_arcade_model->get_info_by_game_id($game['game_id']);
			if($chargepoint_arcade_info)
			{
				$chargepoint_id=$chargepoint_arcade_info['chargepoint_id'];
				$chargepoint_info=$chargepoint_model->get_info_by_id($chargepoint_id);
				if($chargepoint_info['status']==1)
				{
					$game['bean']=$chargepoint_info['bean'];
					$game['coin']=$chargepoint_info['coin'];
					$game['gold']=$chargepoint_info['gold'];
				}
			}elseif($chargepoint_runonce_info)
			{
				$chargepoint_id=$chargepoint_runonce_info['chargepoint_id'];
				$chargepoint_info=$chargepoint_model->get_info_by_id($chargepoint_id);
				if($chargepoint_info['status']==1)
				{
					$game['bean']=$chargepoint_info['bean'];
					$game['coin']=$chargepoint_info['coin'];
					$game['gold']=$chargepoint_info['gold'];
				}
			}
		}
		// 查询所有的单款游戏包
		$gamepack_string = "";
		$db = M('gamepack');
		$condition = array();
		$condition['gp.status'] = 1;
		$condition['_string'] = 'gp.pack_id = gpg.gamepack_id';
		$db_ret = $db->table('july_gamepack gp, july_link_gamepack_game gpg')->field('gp.pack_id,gpg.game_id,COUNT(*) count')->where($condition)->group('gpg.gamepack_id')->select();
		if ($db_ret != null && count($db_ret) > 0) {
			foreach($db_ret as $gamepack) {
				if ($gamepack['count'] == 1) {
					foreach ($pack["games"] as &$game) {
						if ($gamepack['game_id'] == $game['game_id']) {

							$game["single_pack_id"] = $gamepack['pack_id'];
							$gamepack_string .= $gamepack['pack_id'].",";
						}
					}
				}
			}
		}
		$gamepack_string .= "0";

		// 查询所有单款游戏包对应的包月计费点
		if ($gamepack_string != 0) {
			$db = M('chargepoint_gamepack');
			$condition = array();
			$condition['cp.status'] = 1;
			$condition['cpg.deadline_time_increase'] = 2678400; // 只查询包月的
			$condition['_string'] = "cp.id = cpg.chargepoint_id and cpg.gamepack_id in ($gamepack_string)";
			$db_ret = $db->table('july_chargepoint cp, july_chargepoint_gamepack cpg')->field('cp.id,cp.name,cp.type,cp.type_name,cp.bean,cp.coin,cp.gold,cpg.gamepack_id')->where($condition)->select();
			if ($db_ret != null && count($db_ret) > 0) {
				foreach($db_ret as $cp) {
					foreach($pack["games"] as &$game) {
						if(isset($game["single_pack_id"]))
						{
							if ($game["single_pack_id"] == $cp["gamepack_id"])
								$game["chargepoints"] = $cp;
						}
					}
				}
			}
		}

		// 查询该游戏包对应的包月计费点
		$db = M('chargepoint_gamepack');
		$condition = array();
		$condition['cpg.gamepack_id'] = $packid;
		$condition['cp.status'] = 1;
		$condition['cpg.deadline_time_increase'] = 2678400; // 只查询包月的
		$condition['_string'] = "cp.id = cpg.chargepoint_id";
		$db_ret = $db->table('july_chargepoint cp, july_chargepoint_gamepack cpg')->field('cp.id,cp.name,cp.type,cp.type_name,cp.bean,cp.coin,cp.gold')->where($condition)->select();
		if ($db_ret != null && count($db_ret) > 0)
			$pack['chargepoints'] = $db_ret[0];

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "pack", $pack,$e_time);
	}
	/*
	 获取游戏详细信息，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=game_info&deviceid=xxx&logintoken=xxx&gameid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是一个32位整数，表示目标游戏的编号。
	返回格式：
	{"ret":0,"msg":"success","game":{"game_id":"1","game_name":"\u8d85\u7ea7\u8857\u5934\u9738\u738b4","coin":"2000","max_player":"2","status":"1","level":"0","save_enabled":"0","title_pic":"","def_video_width":"1280","def_video_height":"720","low_bitrate":"3000","mid_bitrate":"5000","high_bitrate":"8000","uploader":"\u5343\u5c71\u6708","category":"1","cats":[{"cat_name":"\u683c\u6597","cat_id":"5"}],"pics":[{"pic_type":"0","pic_file":"234234234234"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	game.game_id是一个32位整数，表示该游戏的编号。
	game.game_name是一个长度不超过64字节的字符串，表示该游戏的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:绑定帐号不存在
	-105:游戏不存在
	非零值均表示失败。
	*/
	public function game_info(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid',0);

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device["bind_account"];

		$game = $this->game_info_general($gameid);
		if ($game === false)
			return false;
		$update = $this->game_info_user($gameid, $account_id, $game['trial_time']);
		if ($update === false)
			return false;
		$str = "no memcache.";

		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$account_model=D("Account");
		$db_account=$account_model->get_info_by_id($account_id);
		if (!$db_account )
			return $this->respond(-104, "未找到当前账号：".$db_device["bind_account"]);
		$visible_level = max(C("VISIBLE_GAME_LEVEL"), $db_account['level']);
		if ($game['level'] > $visible_level)
			return $this->respond(-200, "账户级别未达到该游戏所需级别。");
		if ($update) {
			$game["left_trial_time"] = $update["left_trial_time"];
			$game["gamepack_id"] = $update["gamepack_id"];
			$game["deadline_time"] = $update["deadline_time"];
			$game["expired"] = 0;
			if (time() > $game["deadline_time"])
				$game["expired"] = 1;
		}
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success.".$str, "game", $game,$e_time);
	}
	public function game_info_general($gameid) {
		// 读取游戏详细信息
		$game_model=D("Game");
		$game=$game_model->get_info_by_id($gameid);
		if (!$game)
			return $this->respond(-105, "未找到游戏ID：$gameid");
		// 查询游戏的类别信息
		$game["cats"] = array();
		$gamecategory_model=D("Gamecategory");
		$link_gamecategory_game_model=D("LinkGamecategoryGame");
		$gamecategory_arr=$link_gamecategory_game_model->get_category_by_game_id($gameid);
		if($gamecategory_arr)
		{
			foreach ($gamecategory_arr as $val)
			{
				$gamecategory_info=$gamecategory_model->get_info_by_id($val['gamecategory_id']);
				if($gamecategory_info)
				{
					array_push($game["cats"],array('cat_name'=>$gamecategory_info['cat_name'],'cat_id'=>$val['gamecategory_id']));
				}
			}
		}
		// 查询游戏的图片信息
		$db = M('game_pic');
		$db_game_pic = $db->field('pic_type,pic_file')->where('game_id='.$gameid)->select();
		if ($db_game_pic != null && count($db_game_pic) > 0)
			$game["pics"] = $db_game_pic;

		// 更新游戏的虚拟币价格
		$game['bean'] = 0;
		$game['coin'] = 0;
		$game['gold'] = 0;
		if ($game['category'] == 1) { // 主机游戏
			$db = M('chargepoint_runonce');
			$condition = array();
			$condition['cpr.game_id'] = $gameid;
			$condition['cp.status'] = 1;
			$condition['_string'] = 'cp.id = cpr.chargepoint_id';
			$db_ret = $db->table('july_chargepoint cp, july_chargepoint_runonce cpr')->field('cp.bean,cp.coin,cp.gold')->where($condition)->select();
			if ($db_ret != null && count($db_ret) > 0) {
				$game['bean'] = $db_ret[0]['bean'];
				$game['coin'] = $db_ret[0]['coin'];
				$game['gold'] = $db_ret[0]['gold'];
			}
		}
		else if ($game['category'] == 2) { // 街机游戏
			$db = M('chargepoint_arcade');
			$condition = array();
			$condition['cpa.game_id'] = $gameid;
			$condition['cp.status'] = 1;
			$condition['_string'] = 'cp.id = cpa.chargepoint_id';
			$db_ret = $db->table('july_chargepoint cp, july_chargepoint_arcade cpa')->field('cp.bean,cp.coin,cp.gold')->where($condition)->select();
			if ($db_ret != null && count($db_ret) > 0) {
				$game['bean'] = $db_ret[0]['bean'];
				$game['coin'] = $db_ret[0]['coin'];
				$game['gold'] = $db_ret[0]['gold'];
			}
		}

		// 查询游戏对应的单款包月游戏包
		$db = M('gamepack');
		$condition = array();
		$condition['gp.status'] = 1;
		$condition['_string'] = 'gp.pack_id = gpg.gamepack_id';
		$db_ret = $db->table('july_gamepack gp, july_link_gamepack_game gpg')->field('gp.pack_id,gpg.game_id,COUNT(*) count')->where($condition)->group('gpg.gamepack_id')->select();
		if ($db_ret != null && count($db_ret) > 0) {
			foreach($db_ret as $gamepack) {
				if ($gamepack['count'] == 1 && $gamepack['game_id'] == $gameid) {
					// 查询该单款游戏包对应的计费点
					$db = M('chargepoint_gamepack');
					$condition = array();
					$condition['cpg.gamepack_id'] = $gamepack['pack_id'];
					$condition['cp.status'] = 1;
					$condition['cpg.deadline_time_increase'] = 2678400; // 只查询包月的
					$condition['_string'] = "cp.id = cpg.chargepoint_id";
					$db_ret = $db->table('july_chargepoint cp, july_chargepoint_gamepack cpg')->field('cp.id,cp.name,cp.type,cp.type_name,cp.bean,cp.coin,cp.gold')->where($condition)->select();
					if ($db_ret != null && count($db_ret) > 0)
						$game['chargepoints'] = $db_ret[0];
					break;
				}
			}
		}
		//$game['desc']=htmlentities($game['desc']);
		return $game;
	}
	public function game_info_user($gameid, $accountid, $trial_time=0) {
		// 查询游戏的剩余试玩时间
		$game["left_trial_time"] = 0;
		if ($trial_time > 0) {
			$db = M('history_account_game_time');
			$condition = array();
			$condition['game_id'] = $gameid;
			$condition['_string'] = 'account_id='.$accountid." and (gs_last_report_time-gs_start_time)>0 and gs_start_time>".(time()-86400*7);
			$db_trial_time = $db->field('SUM(gs_last_report_time-gs_start_time) t')->where($condition)->select();
			if ($db_trial_time != null && count($db_trial_time) > 0) {
				$game["left_trial_time"] = max(0, $trial_time-$db_trial_time[0]["t"]);
				if ($game["left_trial_time"] < 300) // 如果剩余试玩时间不足5分钟，则不允许试玩.
					$game["left_trial_time"] = 0;
			}
			else
				$game["left_trial_time"] = $trial_time;
		}

		// 查询游戏的过期时间，如果用户曾经购买过包含该游戏的游戏包的话。
		$db = M('link_account_gamepack');
		$condition = array();
		$condition['agp.account_id'] = $accountid;
		$condition['gpg.game_id'] = $gameid;
		$condition['gp.status'] = 1;
		$condition['_string'] = 'agp.gamepack_id=gpg.gamepack_id and gp.pack_id=agp.gamepack_id';
		$db_deadline_time = $db->table('july_link_account_gamepack agp, july_link_gamepack_game gpg, july_gamepack gp')->field('agp.deadline_time, agp.gamepack_id')->where($condition)->order('agp.deadline_time desc')->limit('1')->select();
		if ($db_deadline_time != null && count($db_deadline_time) > 0) {
			$game["gamepack_id"] = $db_deadline_time[0]["gamepack_id"];
			$game["deadline_time"] = $db_deadline_time[0]["deadline_time"];
		}
		else {
			$game["gamepack_id"] = 0;
			$game["deadline_time"] = 0;
		}

		return $game;
	}
	/*
	 获取推荐列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=recommend_list&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","recommends":[{"id":"1","title":"game","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_52.jpg","type":"0","content":"1"},{"id":"2","title":"gamepack","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_51.jpg","type":"1","content":"100"},{"id":"3","title":"webview","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_1.jpg","type":"2","content":"http:\/\/www.51ias.com"},{"id":"4","title":"url","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_10.jpg","type":"3","content":"http:\/\/www.baidu.com"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	recommends.id是一个32位整数，表示该推荐的编号。
	recommends.title是一个长度不超过64字节的字符串，表示该推荐的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:推荐列表不存在
	非零值均表示失败。
	*/
	public function recommend_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$client_ip = get_client_ip();
		$pid=I("pid");

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		// 查询推荐列表
		$recommend_model = D('Recommend');
		$field='id,title,desc,start_time,end_time,pic_file,type,content,flag,discount';
		if($pid)
		{
			$recommend_list=$recommend_model->get_pid_recommend_data_by_pid($pid,$field);
		}else
			$recommend_list = $recommend_model->get_api_all_data($field);
		if ($recommend_list === false)
			return $this->respond(-104, "无法查询推荐列表。");
			
		$recommends = array();
		if (count($recommend_list) > 0) {
			$recommends = $recommend_list;
		}

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "recommends", $recommends,$e_time);
	}
	/*
	 获取最近10条G币消费记录，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=coin_payment_list&deviceid=xxx&logintoken=xxx&page=xxx&rows=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","result":{"count":"4","page":"2","rows":"1","payments":[{"order_id":"1881951512329454","coin":"2500","chargepoint_id":"0","create_time":"1407831172","device_name":"","device_id":"1888","chargepoint_name":""}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	payments.order_id是一个32位整数，表示该次消费的账单号。
	payments.account_id是一个32位整数，表示该次消费的账号。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:消费列表不存在
	非零值均表示失败。
	*/
	public function coin_payment_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$page = I('page',1);
		$rows = I('rows',10);
		$offset = ($page-1)*$rows;

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$payment_coin_model=D("PaymentCoin");
		$num=$payment_coin_model->get_num_by_account_id($db_device["bind_account"]);
		if ($num === false)
			return $this->respond(-104, "无法获取虚拟币消费记录个数。");
		$result['count'] = $num;
		$result['page'] = $page;
		$result['rows'] = $rows;
		$result['payments'] = array();

		// 查询该账户的G币消费列表
		$field="order_id,bean,coin,gold,chargepoint_id,create_time,device_uuid";
		$db_payment = $payment_coin_model->api_get_list_by_account_id($db_device["bind_account"],$offset,$rows,$field);
		if ($db_payment === false)
			return $this->respond(-104, "无法获取虚拟币消费记录。");
		if (count($db_payment) > 0)
			$result['payments'] = $db_payment;

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "result", $result,$e_time);
	}
	/*
	 获取最近10条充值码消费记录，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=card_payment_list&deviceid=xxx&logintoken=xxx&page=xxx&rows=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","result":{"count":"1","page":"1","rows":"1","payments":[{"card_id":"1010107327375182","card_pass":"1359434072287444","type":"0","chargepoint_id":"7","charge_time":"1407828902","device_name":"","device_id":"1888","chargepoint_name":"\u9e4f\u6e38\u4e91\u6e38\u620f\u57fa\u7840\u5305\u534a\u5e74"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	payments.card_id是一个64位整数，表示该充值卡卡号。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104/-105:无法获取充值卡消费记录个数。
	-106/-107:无法获取充值卡消费记录。
	非零值均表示失败。
	*/
	public function card_payment_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$page = I('page',1);
		$rows = I('rows',10);
		$offset = ($page-1)*$rows;
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$db = M('payment_card');
		$db_ret = $db->field('count(id) count')->where("charge_to_account_id=".$db_device["bind_account"])->select();
		if ($db_ret === false)
			return $this->respond(-104, "无法获取充值卡消费记录个数。");

		$db_ex_ret=$db->table("july_exchange_code ec")->join('july_exchange_record er on ec.id=er.code_id')->where("er.account_id=".$db_device["bind_account"])->count();
		if($db_ex_ret === false)
			return $this->respond(-105, "无法获取特殊充值卡消费记录个数。");
		//sunzheng//
		$result['count'] = $db_ret[0]['count']+$db_ex_ret;
		$result['page'] = $page;
		$result['rows'] = $rows;

		$result['payments'] = array();

		// 查询该账户的充值卡消费列表
		$db_payment = $db->table("july_payment_card pc")
		->join("july_device d on pc.charge_to_device_uuid=d.device_uuid")
		->join("july_chargepoint cp on pc.chargepoint_id=cp.id")
		->field('pc.card_id,pc.card_pass,pc.type,pc.chargepoint_id,pc.charge_time,IFNULL(d.byname,"") device_name,d.id device_id,cp.type_name chargepoint_type,IFNULL(cp.name,"") chargepoint_name')
		->where("pc.charge_to_account_id=".$db_device["bind_account"]." and pc.charge_time <> 0 ")
		->order("pc.id desc")->select();
		if ($db_payment === false)
			return $this->respond(-106, "无法获取充值卡消费记录。");

		//查询该账户的兑换券列表
		$db_exchange=$db->table("july_exchange_code ec")
		->join("july_exchange_record er on ec.id=er.code_id")
		->join("july_exchange_type et on et.type_id=ec.type_id")
		->join("july_device d on er.device_uuid=d.device_uuid")
		->join("july_chargepoint cp on et.chargepoint_id=cp.id")
		->field('ec.card_pass as card_pass,et.chargepoint_id,er.charge_time,IFNULL(d.byname,"") device_name,d.id device_id,cp.type_name chargepoint_type,IFNULL(cp.name,"") chargepoint_name')
		->where("er.account_id=".$db_device["bind_account"]." and er.charge_time <> 0 ")
		->order("er.id desc")->select();
		if ($db_exchange === false)
			return $this->respond(-107, "无法获取特殊充值卡消费记录。");
		$data = array();
		if($db_payment === null || $db_exchange === null)
		{
			if($db_payment===null && $db_exchange!=null)
			{
				$data=$db_exchange;
			}
			elseif($db_payment!=null && $db_exchange===null)
			{
				$data=$db_payment;
			}
		}
		else
		{
			$data=array_merge($db_payment,$db_exchange);
		}
		$list=array();
		foreach($data as $key=>$v)
		{
			$list[$v['charge_time']]=$v;
		}
		$data_list=array_slice($list,$offset,$rows);
		if (count($list) > 0)
			$result['payments'] = $data_list;

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "result", $result,$e_time);
	}
	/*
	 获取最近10条人民币充值记录，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=rmb_payment_list&deviceid=xxx&logintoken=xxx&page=xxx&rows=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","result":{"count":"1","page":"1","rows":"1","payments":[{"id":"75182","rmb":"10000","coin":"10000","total_bought_coin":"7345345","create_time":"1407828902"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	payments.id是一个32位整数，表示该次充值的编号。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:消费列表不存在
	非零值均表示失败。
	*/
	public function rmb_payment_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$page = I('page',1);
		$rows = I('rows',10);
		$offset = ($page-1)*$rows;
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$payement_rmb_model=D("PaymentRmb");
		$db_ret=$payement_rmb_model->get_data_by_account_id($db_device["bind_account"]);
			
		if ($db_ret === false)
			return $this->respond(-104, "无法获取人民币消费记录个数。");
		$count=count($db_ret);
		$result['count'] = $count;
		$result['page'] = $page;
		$result['rows'] = $rows;
		$result['payments'] = array();

		// 查询该账户的充值卡消费列表
		$field='id,rmb,coin,total_bought_coin,create_time';
		$db_payment=$payement_rmb_model->get_data_by_account_id_limit($db_device["bind_account"],$offset,$rows,$field);
		if ($db_payment === false)
			return $this->respond(-104, "无法获取人民币消费记录。");

		if (count($db_payment) > 0)
			$result['payments'] = $db_payment;

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "result", $result,$e_time);
	}
	/*
	 购买某个计费点。
	请求格式：形如http://localhost/api.php?m=Client&a=purchase&deviceid=xxx&logintoken=xxx&chargepointid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success"}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	payments.id是一个32位整数，表示该次充值的编号。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:消费列表不存在

	-300:没有找到计费点
	-301:存档文件已损坏，md5不符
	-302:无法创建新的存档序列
	-303:无法创建新的存档
	-304:无法建立用户存档目录
	-305:无法复制存档到用户存档目录
	非零值均表示失败。
	*/
	public function purchase(){
		G('begin');

		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$chargepointid = I('chargepointid',0);

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device["bind_account"];

		$chargepoint_model=D("Chargepoint");
		$db_cp=$chargepoint_model->get_info_by_id($chargepointid);
		if ($db_cp === false || $db_cp['status']!=1)
			return $this->respond(-104, "找不到该计费点ID：$chargepointid");
		//开启事务
		$model = new Model();
		$model->startTrans();
		// 扣币
		$ret = $this->use_account_money($model, $account_id, $db_cp);
		if($ret['ret'] != 0) {
			$model->rollback();
			return $this->respond($ret['ret'], $ret['msg']);
		}
		// 0, 游戏包；1，存档；2，虚拟币；3，单次游戏；4，街机投币；5，擂台赛；
		if ($db_cp['type'] == 0) { // 游戏包
			$ret = $this->purchase_chargepoint_gamepack($model, $account_id, $chargepointid);
			if ($ret['ret'] != 0) {
				$model->rollback();
				return $this->respond($ret['ret'], $ret['msg']);
			}
		}
		else if ($db_cp['type'] == 1) { // 存档
			//复制存档
			$ret_gs = $this->copy_sale_gamesave($model, $deviceid, $account_id, $chargepointid);
			if ($ret_gs['ret'] != 0) {
				$model->rollback();
				return $this->respond($ret_gs['ret'], $ret_gs['msg']);
			}
		}
		else {
			$model->rollback();
			return $this->respond(-120, "暂不支持该计费点类型：".$db_cp['type_name']);
		}
		// 添加虚拟币消费记录
		$data = array();
		$data["order_id"] = $this->generateRandomBigInt();
		$data["account_id"] = $account_id;
		$data["device_uuid"] = $deviceid;
		$data["bean"] = max(0, $db_cp["bean"]);
		$data["coin"] = max(0, $db_cp["coin"]);
		$data["gold"] = max(0, $db_cp["gold"]);
		$data["chargepoint_id"] = $chargepointid;
		$data["game_id"] = 0;
		$data["gamepack_id"] = 0;
		$data["gs_id"] = 0;
		$data["play_mode"] = 0;
		$data["payment_type"] = 0;
		$data["create_time"] = time();

		$payment_coin_model=D("PaymentCoin");
		$db_link=$payment_coin_model->add_data($data);

		if (!$db_link) {
			$model->rollback();
			return $this->respond(-130, "无法添加虚拟币消费记录。");
		}
		$model->commit();

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond(0, "success",$e_time);
	}
	/*
	 使用充值卡，只有已登录的设备才能使用。
	请求格式：形如http://localhost/api.php?m=Client&a=use_recharge_card&deviceid=xxx&logintoken=xxx&card=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success"}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:充值卡不存在
	-105:充值卡失效
	-106:充值卡对应的计费点失效
	-107:充值卡已过期
	-108:充值卡已被使用
	-109:充值卡已经成功使用
	-110:充值卡对应的计费点无效（游戏包）
	-111:充值卡对应的计费点无法添加到账户记录（游戏包）
	-112:充值卡对应的计费点更新账户记录（游戏包）
	-130:充值卡对应的计费点无效（虚拟币）
	-131:充值卡对应的计费点无法更新到账户记录（虚拟币）
	-132:虚拟币收入记录新增失败
	-200:充值卡对应的计费点类型尚不支持。
	-201:兑换券还未开到开启时间.
	-202:此卡的次数已经用完
	非零值均表示失败。
	*/
	public function use_recharge_card(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$card = I('card','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device["bind_account"];

			
		$model =M();
		$model->startTrans();
		$account_model=D("Account");
			
		// 查询该充值卡的信息，先查询普通卡，再查询特殊卡
		$condition = array();
		$condition['card_pass'] = $card;
		$card_data = $model->table('july_payment_card')->where($condition)->find();
		if(!$card_data)
			$exchange = $model->table('july_exchange_code')->where($condition)->find();

		if($card_data)
		{
			$condition = array();
			$condition['_string'] = 'pc.chargepoint_id=cp.id';
			$condition['card_pass'] = $card;
			$db_card = $model->lock(true)->table("july_payment_card pc, july_chargepoint cp")->field('cp.id,cp.type,pc.valid,cp.status,pc.charge_to_account_id,pc.charge_time,pc.expire_time')->where($condition)->select();
			if ($db_card == false) {
				$model->rollback();
				return $this->respond(-104, "查询卡信息失败：$card");
			}
			if (count($db_card) == 0) {
				$model->rollback();
				return $this->respond(-105, "找不到这张卡：$card");
			}
			if ($db_card[0]["valid"] != 1) {
				$model->rollback();
				return $this->respond(-106, "此卡已失效：$card");
			}
			if ($db_card[0]["status"] != 1) {
				$model->rollback();
				return $this->respond(-107, "此卡对应的计费点失效：$card");
			}
			if ($db_card[0]["expire_time"] <= time()) {
				$model->rollback();
				return $this->respond(-108, "此卡已经过期：$card");
			}
			if ($db_card[0]["charge_to_account_id"] != 0 || $db_card[0]["charge_time"] != 0) {
				$model->rollback();
				return $this->respond(-109, "此卡已经被使用：$card");
			}
		}
		else if($exchange)
		{
			$condition = array();
			$condition['card_pass'] = $card;
			$db_card = $model->table('july_exchange_code')->lock(true)->table("july_exchange_code as ec")
			->join("july_exchange_type as et on et.type_id=ec.type_id")
			->join("july_chargepoint as cp on cp.id=et.chargepoint_id")
			->field('cp.id,cp.type,cp.status,ec.id as code_id,et.type_id,et.valid_time,et.expire_time,et.type_mark,ec.num,ec.surplus_num,et.num as type_num,et.account_make_num as type_make_num,et.surplus_num as type_surplus_num')
			->where($condition)->select();
			if ($db_card == false) {
				$model->rollback();
				return $this->respond(-114, "查询卡信息失败：$card");
			}
			if (count($db_card) == 0) {
				$model->rollback();
				return $this->respond(-115, "找不到这张卡：$card");
			}
			if ($db_card[0]["status"] != 1) {
				$model->rollback();
				return $this->respond(-116, "此卡对应的计费点失效。");
			}
			if ($db_card[0]["valid_time"] >= time()) {
				$model->rollback();
				return $this->respond(-117,"此卡尚未到启用时间。");
			}
			if ($db_card[0]["expire_time"] <= time()) {
				$model->rollback();
				return $this->respond(-118,"此卡已经过期：$card");
			}
			if ($db_card[0]["surplus_num"] <= 0 && $db_card[0]["num"] != 0) {
				$model->rollback();
				return $this->respond(-119, "此卡的使用次数已耗尽：$card");
			}
			if ($db_card[0]["type_surplus_num"] <= 0 && $db_card[0]["type_num"] != 0) {
				$model->rollback();
				return $this->respond(-120, "此类卡的使用次数已耗尽：$card");
			}

			$where = array();
			$where['account_id'] = $account_id;
			$where['type_id'] = $db_card[0]['type_id'];
			$record_count = $model->table('july_exchange_record')->where($where)->count();
			if ($record_count === false) {
				$model->rollback();
				return $this->respond(-121, "无法查询此卡的使用记录：$card");
			}

			// type_mark: 类别标识(1:一码多次,2:多码多次)
			if ($db_card[0]['type_mark'] == 1 && $record_count >= 1) {
				$model->rollback();
				return $this->respond(-122, "此卡已被使用：$card");
			}
			else if ($db_card[0]['type_mark'] == 2) {
				if ($record_count >= $db_card[0]['type_make_num']) {
					$model->rollback();
					return $this->respond(-123, "此类卡最多只能用 $record_count 张");
				}
			}
		}
		else
		{
			$model->rollback();
			return $this->respond(-144, "未找到该卡：$card");
		}
			
		// 使用该充值卡
		$id = $db_card[0]["id"];
		$type = $db_card[0]["type"];
		if ($type == 0) { // 游戏包
			$ret_cp = $this->purchase_chargepoint_gamepack($model, $account_id, $id);
			if ($ret_cp['ret'] != 0) {
				$model->rollback();
				return $this->respond($ret_cp['ret'], $ret_cp['msg']);
			}
		}
		else if ($type == 1) { // 存档
			//复制存档
			$ret_gs =$this->copy_sale_gamesave($model, $deviceid, $account_id, $id);
			if ($ret_gs['ret'] != 0) {
				$model->rollback();
				return $this->respond($ret_gs['ret'], $ret_gs['msg']);
			}
		}
		else if ($type == 2) { // 虚拟币
			$db_coin = $model->table('july_chargepoint_coin')->field('bean,coin,gold')->where('chargepoint_id='.$id)->select();
			if (!$db_coin || count($db_coin) == 0) {
				$model->rollback();
				return $this->respond(-130, "此卡对应的计费点ID $id 无效。");
			}

			// 将虚拟币充入用户帐号
			$data=$account_model->get_info_by_id($account_id);
			if (!$data)
				return $this->respond(-133, "未找到当前账号：".$account_id);

			$data['bean']+=$db_coin[0]['bean'];
			$data['gift_coin_num']+=$db_coin[0]['coin'];
			$data['gold']+=$db_coin[0]['gold'];

			$data['bean']+=max(0,$db_coin[0]['bean']);
			$data['gift_coin_num']+=max(0,$db_coin[0]['coin']);
			$data['gold']+=max(0,$db_coin[0]['gold']);


			$db_account = $account_model->save_data($account_id,$data);
			if (!$db_account || count($db_account) == 0) {
				$model->rollback();
				return $this->respond(-131, "无法将此卡对应的虚拟币充入账户：".$account_id."。请联系客服。");
			}

			//虚拟币收入记录
			$data = array();
			$data['account_id'] = $account_id;
			$data['bean'] = max(0,$db_coin[0]["bean"]);
			$data['coin'] = max(0,$db_coin[0]["coin"]);
			$data['gold'] = max(0,$db_coin[0]["gold"]);
			$data['income_type'] =6;
			$data['create_time'] = time();
			$result = $model->table(C('DB_PREFIX').'income_coin')->add($data);
			if(!$result)
			{
				$model->rollback();
				return $this->respond(-132,"无法记录虚拟币收入记录。");
			}
		}
		else {
			$model->rollback();
			return $this->respond(-200, "暂不支持该类型的充值卡。计费点类型：".$type);
		}

		if($card_data)
		{
			// 充值卡使用完毕，更新记录
			$condition = array();
			$condition['card_pass'] = $card;
			$update = array();
			$update['charge_to_account_id'] = $account_id;
			$update['charge_to_device_uuid'] = $deviceid;
			$update['charge_time'] = time();
			$db_card = $model->table('july_payment_card')->where($condition)->save($update);
			if (!$db_card){
				$model->rollback();
				return $this->respond(-109, "无法更新此卡 $card 的使用记录。");
			}
		}
		elseif($exchange)
		{
			if($db_card[0]['num']!=0)
			{
				$condition=array();
				$condition['type_id']=$db_card[0]['type_id'];
				$result = $model->table('july_exchange_type')->where($condition)->setDec("surplus_num",1);
				if($result === false)
				{
					$model->rollback();
					return $this->respond(-110, "无法更新此卡类别的剩余使用次数。");
				}
				$condition['card_pass']=$card;
				$result=$model->table('july_exchange_code')->where($condition)->setDec("surplus_num",1);
				if($result === false)
				{
					$model->rollback();
					return $this->respond(-111, "无法更新此卡的剩余使用次数。");
				}
			}

			$add = array();
			$add['account_id'] = $account_id;
			$add['type_id'] = $db_card[0]['type_id'];
			$add['device_uuid'] = $deviceid;
			$add['code_id'] = $db_card[0]['code_id'];
			$add['charge_time'] = time();
			$result = $model->table('july_exchange_record')->add($add);
			if($result === false) {
				$model->rollback();
				return $this->respond(-112, "无法添加使用特殊卡的记录。");
			}
		}

		$model->commit();

		//返回充值后的金币信息
		$my_db = M('account');
		$my_condition['id'] = $account_id;
		$my_data = $my_db->field("gift_coin_num,bought_coin_num,bean,gift_coin_num as coin,gold")->where($my_condition)->select();

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "use_recharge_card_my_data", $my_data,$e_time);
	}
	/*
	 签到领取奖励，只有已登录的设备才能使用。
	请求格式：形如http://localhost/api.php?m=Client&a=continuously_sign_in&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","payments":[{}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。

	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-105：设备信息不正确
	-106: 今天你已经签过到了
	-132:虚拟币收入记录新增失败
	非零值均表示失败。
	*/
	public function continuously_sign_in(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$insert_data = array(
				'account_id' =>0,
				'continuously_day'=>'',
				'sign_time' =>time(),
				'gift_coin' =>0,
				'gift_exp' =>0,
				'flag' =>0,
				'is_sign_today'=>0,
				'created_time'=>time()
		);
		//校验用户的信息
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$insert_data['account_id'] = $condition['account_id'] =$db_device['bind_account'];

		//实例化签到奖励数据对象
		$db = M('sign_in');
		$data_sign_in = $db->select();
		//print_r($data_sign_in);
		//实例化签到的对象
		$model = new Model();
		$model->startTrans();

		//$db_continuously_sign_in = M('continuously_sign_in');
		$data_continuously_sign_in = $model->table(C('DB_PREFIX').'continuously_sign_in')->where($condition)->order('id desc')->limit(1)->select();
		//print_r($data_continuously_sign_in);
		if($data_continuously_sign_in){
			$last_time = $data_continuously_sign_in['0']['sign_time']; //上次签到时间
			$nowtime = time();
			$now_date = date("Y-m-d",$nowtime);
			$last_date = date("Y-m-d",$last_time);//上次签到的日期

			//检验今天是否已经签过到了
			if($now_date == $last_date)
				return $this->respond(-106, "今天已经签到过了。");
			$judge_time = $last_time+24*3600;
			$judge_date = date("Y-m-d",$judge_time);
			//判断是否是连续的签到
			if($now_date != $judge_date){
				//置换为第一次签到
				$insert_data['continuously_day'] = 1;
				$insert_data['is_sign_today'] = 1;
				$insert_data['gift_coin'] = (int)$data_sign_in['0']['gift_coin'];
				$insert_data['gift_exp'] = (int)$data_sign_in['0']['gift_exp'];
			}else{
				//连续签到中（只现在是时间段内是连续的）
				$insert_data['flag'] = 1; //连续签到
				$insert_data['is_sign_today'] = 1; //已经签过到了
				$continuously_day = (int)$data_continuously_sign_in['0']['continuously_day'];
				$current_continuously_day = $continuously_day+1;
				$insert_data['continuously_day'] = $current_continuously_day;
				//设置额外加成变量
				$extra_gift_coin = $this->getSignExtraCoin($model->table(C('DB_PREFIX').'continuously_sign_in'), $insert_data['account_id']);
				if($current_continuously_day<=7){
					$insert_data['gift_coin'] = (int)$data_sign_in[$continuously_day]['gift_coin']+$extra_gift_coin;
					$insert_data['extra_gift_coin'] = $extra_gift_coin;
					$insert_data['gift_exp'] = (int)$data_sign_in[$continuously_day]['gift_exp'];
				}else{
					$insert_data['gift_coin'] = (int)$data_sign_in['6']['gift_coin'];
					$insert_data['gift_exp'] = (int)$data_sign_in['6']['gift_exp'];
				}
			}
		}else{
			//首次签到
			//设置额外加成变量
			$extra_gift_coin = $this->getSignExtraCoin($model->table(C('DB_PREFIX').'continuously_sign_in'), $insert_data['account_id']);
			$insert_data['continuously_day'] = 1;
			$insert_data['is_sign_today'] = 1;
			$insert_data['flag'] = 1;
			$insert_data['gift_coin'] = (int)$data_sign_in['0']['gift_coin']+$extra_gift_coin;
			$insert_data['extra_gift_coin'] = $extra_gift_coin;
			$insert_data['gift_exp'] = (int)$data_sign_in['0']['gift_exp'];
		}
		//存储签到信息

		//$insert_res = $db_continuously_sign_in->add($insert_data);
		$insert_res = $model->table(C('DB_PREFIX').'continuously_sign_in')->add($insert_data);
		//print_r($insert_res);
		if(!$insert_res)
		{
			$model->rollback();
			return $this->respond(-107, "记录签到数据失败。");
		}
			
		//更新设备账户的金币和经验的信息
		$account_model=D("Account");

		$account_model->lock(true);
		$account_data=$account_model->get_info_by_id($db_device['bind_account']);
		if (!$account_data)
			return $this->respond(-109, "未找到当前账号：".$db_device['bind_account']);

		$account_update['gift_coin_num'] = $account_data['gift_coin_num']+$insert_data['gift_coin'];
		$account_update['exp'] = $account_data['exp']+$insert_data['gift_exp'];
		$db_res=$account_model->save_data($db_device['bind_account'],$account_update);
		if (!$db_res)
		{
			$model->rollback();
			return $this->respond(-108, "无法将签到奖励添加到当前账户：".$db_device['bind_account']);
		}
		//虚拟币收入记录
		$data=array();
		$data['account_id']=$db_device["bind_account"];
		$data['coin']=$insert_data["gift_coin"];
		$data['income_type']=1;
		$data['create_time']=time();
		$result=$model->table(C('DB_PREFIX').'income_coin')->add($data);
		if(!$result)
		{
			$model->rollback();
			return $this->respond(-132,"无法记录本次签到收入。");
		}

		$model->commit();

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "continuously_sign_in", $account_update,$e_time);
	}
	/*
	 获取我的游戏列表，只有已登录的设备才能获列表
	请求格式：形如http://localhost/api.php?m=Client&a=mygames&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","games":[{"game_id":"1","game_name":"\u8d85\u7ea7\u8857\u5934\u9738\u738b4","coin":"2000","max_player":"2","status":"1","level":"0","save_enabled":"0","title_pic":"","deadline_time":"123123123"},{"game_id":"2","game_name":"\u96f7\u66fc\uff1a\u8d77\u6e90","coin":"500","max_player":"1","status":"1","level":"0","save_enabled":"0","title_pic":"","deadline_time":"123123123"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	game.game_id是一个32位整数，表示该游戏的编号。
	game.game_name是一个长度不超过64字节的字符串，表示该游戏的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:绑定帐号不存在
	-105:游戏不存在
	非零值均表示失败。
	*/
	public function mygames(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
			
		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$account_model=D("Account");
		$account_info=$account_model->get_info_by_id($db_device['bind_account']);

		if (!$account_info)
			return $this->respond(-104, "未找到当前账号：".$db_device["bind_account"]);
		$visible_level = max(C("VISIBLE_GAME_LEVEL"), $account_info['level']);
		// 查询我的游戏列表
		//$link_account_gamepack=D("LinkAccountGamepack");


		$db = M('link_account_gamepack');
		$db_games = $db->table("july_link_account_gamepack agp, july_link_gamepack_game gpg, july_gamepack gp, july_game g")
		->field('g.game_id,g.game_name,g.max_player,g.status,g.level,g.save_enabled,g.title_pic,g.controller,g.trial_time,MAX(agp.deadline_time) deadline_time')
		->where('agp.account_id='.$db_device["bind_account"]." and gpg.gamepack_id=agp.gamepack_id and g.game_id=gpg.game_id and gp.pack_id=agp.gamepack_id and gp.status=1 and g.status=1 and g.level<=".$visible_level)
		->group("g.game_id")->order("g.level, MAX(agp.deadline_time) desc")->select();
		if ($db_games === false)
			return $this->respond(-105, "无法查询我的游戏列表。");

		$games = array();
		if (count($db_games) > 0) {
			foreach ($db_games as $key=>$g) {
				if($g['deadline_time'] < time())
					continue;
				array_push($games, $g);
			}
		}
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "games", $games,$e_time);
	}
	/*
	 获取最近玩过的游戏列表，只有已登录的设备才能获列表
	请求格式：形如http://localhost/api.php?m=Client&a=played_games&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	返回格式：
	{"ret":0,"msg":"success","games":[{"game_id":"1","game_name":"\u8d85\u7ea7\u8857\u5934\u9738\u738b4","coin":"2000","max_player":"2","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/client.51ias.com\/game_pic\/small_1.jpg","controller":"1","trial_time":"1000","last_end_time":"1405308946"},{"game_id":"10","game_name":"\u9e70\u51fb\u957f\u7a7a","coin":"100","max_player":"1","status":"1","level":"10","save_enabled":"1","title_pic":"http:\/\/client.51ias.com\/game_pic\/small_10.jpg","controller":"1","trial_time":"0","last_end_time":"1405266969"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	game.game_id是一个32位整数，表示该游戏的编号。
	game.game_name是一个长度不超过64字节的字符串，表示该游戏的名称。
	。。。
	。。。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	非零值均表示失败。
	*/
	public function played_games(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 读取绑定帐号的等级和权限，以便下面返回对应的区域列表
		$account_model=D("Account");
		$field="level";
		$db_account=$account_model->get_info_by_id($db_device["bind_account"],$field);
		if (!$db_account)
			return $this->respond(-104, "未找到当前账号：".$db_device["bind_account"]);
		$visible_level = max(C("VISIBLE_GAME_LEVEL"), $db_account['level']);
		$MAX_RECENT_GAMES = C('MAX_RECENT_GAMES');
		// 查询最近玩过的几个游戏，一个月以内
		$db = M('history_account_game_time');
		$condition = array();
		$condition['agt.account_id'] = $db_device['bind_account'];
		$condition['agt.gs_start_time'] = array('gt', time()-86400*30);
		$condition['_string'] = 'agt.game_id=g.game_id and g.status=1 and g.level<='.$visible_level;
		$db_games = $db->table('july_history_account_game_time agt,july_game g')
		->field('g.game_id,g.game_name,g.coin,g.max_player,g.status,g.level,g.save_enabled,g.title_pic,g.controller,g.trial_time,g.category,MAX(agt.gs_last_report_time) last_end_time')
		->where($condition)->group('agt.game_id')->order('MAX(agt.gs_last_report_time) desc')->limit($MAX_RECENT_GAMES)->select();
		$games = array();
		if (count($db_games) > 0) {
			$games = $db_games;
		}
		// 如果游戏不足$MAX_RECENT_GAMES个，则用-1代替
		$more_game_count = ($MAX_RECENT_GAMES - count($games));
		if ($more_game_count > 0 && !empty($games)) {
			for($i=1;$i<=$more_game_count;$i++){
				$g = array('game_id'=>'-1','game_name'=>'','coin'=>'0','max_player'=>'0','status'=>'1','level'=>'0','save_enabled'=>'0','title_pic'=>'','controller'=>'1','trial_time'=>'0','category'=>'1','last_end_time'=>'0');
				array_push($games, $g);
			}
		}
		/*
			// 如果游戏不足$MAX_RECENT_GAMES个，则用游戏排行榜补足
		$more_game_count = ($MAX_RECENT_GAMES - count($games));
		if ($more_game_count > 0) {
		$condition = array();
		$condition['_string'] = 'gr.game_id=g.game_id and g.status=1 and g.level<='.$visible_level;
		$db_rank = $db->table('july_game_rank gr,july_game g')->field('g.game_id,g.game_name,g.coin,g.max_player,g.status,g.level,g.save_enabled,g.title_pic,g.controller,g.trial_time')->where($condition)->order('gr.rank')->limit($more_game_count)->select();
		if (count($db_rank) > 0) {
		foreach ($db_rank as $g) {
		$g['last_end_time'] = 0;
		array_push($games, $g);
		}
		}
		}
		*/
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success.", "games", $games,$e_time);
	}
	/*
	 * @desc:获取我的钱包信息只有登录的可以使用
	* @modifytime:2014/09/11
	* @modify desc 把以设备为基础的赠金币修改为以账号为基础的
	* @modify author chunyingXu
	* 请求格式：http://localhost/api.php?a=my_wallet_info&logintoken=P6ZdCRzbJIAydeQAkTRaXzJN22tUNlND&m=Client&deviceid=ffffffff-874b-8e14-ffff-ffffb58a3d4e
	* 返回格式：{"ret":0,"msg":"success","my_wallet_info":{"static_sign_in_info":[{"sign_day":"1","gift_coin":"500","gift_exp":"0","extra_gift_coin":0},{"sign_day":"2","gift_coin":"500","gift_exp":"0","extra_gift_coin":"8000"},{"sign_day":"3","gift_coin":"600","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"4","gift_coin":"600","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"5","gift_coin":"700","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"6","gift_coin":"700","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"7","gift_coin":"800","gift_exp":"0","extra_gift_coin":"10000"}],"total_coin":7500,"continuously_day":"1","gift_coin_num":7500,"bought_coin_num":0,"is_sign_today":"1"}}
	*/
	public function my_wallet_info(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		//$condition['device_uuid'] = $deviceid;
		$response_data = array(
				'static_sign_in_info' =>array(),
				'total_coin' =>0,
				'continuously_day' =>0,
				'gift_coin_num' =>0,
				'bought_coin_num'=>0,
				'is_sign_today' =>0,

		);
		//校验用户的信息
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$condition['account_id'] =$db_device['bind_account'];

		//实例化签到奖励数据对象
		$db = M('sign_in');
		$data_sign_in = $db->select();
		$sign_data_num = count($data_sign_in);
		//获取历史数据中最大的连续签到天数
		//实例化签到的对象
		$db_continuously_sign_in_history = M('continuously_sign_in');
		$history_max_day = $this->getMaxContinuouslyDay($db_continuously_sign_in_history, $db_device['bind_account']);
		for($i=0;$i<$sign_data_num;$i++){
			if ($i<$history_max_day)
				$data_sign_in[$i]['extra_gift_coin'] = 0;
		}
		$response_data['static_sign_in_info'] = $data_sign_in;
		//print_r($data_sign_in);
		//实例化account对象
		$account_model=D("Account");
		$account_data=$account_model->get_info_by_id($db_device['bind_account']);
		if($account_data){
			$response_data['gift_coin_num'] = (int)$account_data['gift_coin_num'];
			$response_data['bought_coin_num'] = 0;
			$response_data['total_coin'] = (int)$account_data['gift_coin_num'];
		}
		//实例化签到的对象
		$db_continuously_sign_in = M('continuously_sign_in');
		$data_continuously_sign_in = $db_continuously_sign_in->where($condition)->order('id desc')->limit(1)->select();
		if($data_continuously_sign_in){
			$last_time = $data_continuously_sign_in['0']['sign_time']; //上次签到时间
			$nowtime = time();
			$now_date = date("Y-m-d",$nowtime);
			$last_date = date("Y-m-d",$last_time);//上次签到的日期
			if($now_date == $last_date){
				//今天已经签过的返回
				$response_data['continuously_day'] = $data_continuously_sign_in['0']['continuously_day'];
				$response_data['is_sign_today'] = $data_continuously_sign_in['0']['is_sign_today'];
				return $this->respond_ex(0, "success", "my_wallet_info", $response_data);
			}
			$judge_time = $last_time+24*3600;
			$judge_date = date("Y-m-d",$judge_time);
			//判断是否是连续的签到
			if($now_date == $judge_date){
				$response_data['continuously_day'] = $data_continuously_sign_in['0']['continuously_day'];
			}
		}

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "my_wallet_info", $response_data,$e_time);
	}
	/**
	 * 修改昵称
	 * API http://10.0.4.66/game/index.php?a=modify_nick_name&m=Client&deviceid=00000000-01e3-f5e0-0033-c58700000000&nick_name=XXXname&logintoken=XXXXXXXXXX
	 * -105:the db_device is not right
	 * -106： the nick name is null.
	 * -108：the nickname update fail
	 * 0：the nick name modify success
	 */
	public function modify_nick_name(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$nick_name = I('nick_name','');
		//校验用户的信息
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		if(strlen(trim($nick_name))==0){
			return $this->respond(-106,"昵称不能为空。");
		}

		//过滤掉昵称中的铭感词
		if( filter_nickname($nick_name) === false){
			return $this->respond(-110,"昵称不合法，请重新输入。");
		}

		$update_data['nickname'] = $nick_name;
		$account_model=D("Account");
		$db_res=$account_model->save_data($db_device['bind_account'],$update_data);

		if($db_res === false){
			return $this->respond(-108, "无法设置新昵称。");
		}

		// 如果存在擂台用户表中，清除缓存.
		$db_arena_account = M("arena_account");
		$condition = array();
		$condition['account_id'] = $db_device['bind_account'];
		$arena_account = $db_arena_account->field("account_id,game_id")->where($condition)->select();
		if($arena_account === false)
			return $this->respond(-109,"更新昵称失败!");
		$cache = S(array("type"=>"Gloudmemcached"));
		if(count($arena_account) > 0) {
			foreach($arena_account as $key=>$val) {
				$key = "arena_account_info_by_account_id_game_id_".$val['account_id'].$val['game_id'];
				$cache->rm($key);
			}
		}

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond(0, "success",$e_time);
	}
	/*
	 获取当前帐号在这个游戏下最近玩过的一次存档，如果有必要则创建新的存档序列。
	请求格式：形如http://localhost/api.php?m=Client&a=last_save&deviceid=xxx&logintoken=xxx&gameid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	返回格式：
	{"ret":0,"msg":"success.","serial":{"id":"2","name":"test1","create_time":"1416221480","deletable":"1","default_time":"0","save":{"id":"46737","serial_id":"27164","derived_from":"46736","create_time":"1416372714","upload_time":"1416372795","gs_report_time":"1416372774","compressed_size":"10585","compressed_md5":"db1d5f3d859ee460fefd0bd712a77b1a",,"derived_count":"0","game_mode":"1"}}}
	{"ret":0,"msg":"success. existing serial. no save.","serial":{"id":"22","name":"\u6211\u7684\u5b58\u6863"}}
	{"ret":0,"msg":"success. new serial. no save.","serial":{"id":23,"name":"\u6211\u7684\u5b58\u6863"}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:最近一次存档对应的序列不正常
	-105:无法添加新的存档序列
	非零值均表示失败。
	*/
	public function last_save() {
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 读取默认存档卡，如果没有就新建一个
		$model = new Model(); // 无需事务
		$ret = $this->get_default_save_serial($model, $deviceid, $db_device["bind_account"], $gameid);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$serial = $ret['msg'];

		// 读取默认存档卡的最近的一个存档, delete_time = 0的项才是有效项
		$db = M('game_save');
		$condition = array();
		$condition['account_id'] = $db_device["bind_account"];
		$condition['game_id'] = $gameid;
		$condition['serial_id'] = $serial['id'];
		$condition['delete_time'] = 0;
		$db_save = $db->field("id, serial_id, derived_from, create_time, upload_time, gs_report_time, compressed_size, compressed_md5, derived_count, game_mode")
		->where($condition)->order('id desc')->limit('1')->select();
		if (count($db_save) > 0)
			$serial['save'] = $db_save[0];

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, 'success.', 'serial', $serial,$e_time);
	}

	/*
	 获取当前账号下指定游戏的存档列表，只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=serial_list&deviceid=xxx&logintoken=xxx&gameid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	返回格式：
	{"ret":0,"msg":"success.","serials":[{"id":"1","name":"test","create_time":"0","saves":[{"id":"1","derived_from":"0","create_time":"0","upload_time":"1233","gs_report_time":"0","compressed_size":"0","compressed_md5":"123","derived_count":"0"},{"id":"12","derived_from":"1","create_time":"1404230190","upload_time":"0","gs_report_time":"0","compressed_size":"0","compressed_md5":"","derived_count":"0"}]}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	非零值均表示失败。
	*/
	public function serial_list() {
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
			
		// 读取存档卡列表, delete_time = 0的项才是有效项
		$db = M('game_save_serial');
		$condition = array();
		$condition['account_id'] = $db_device["bind_account"];
		$condition['game_id'] = $gameid;
		$condition['delete_time'] = 0;
		$serials = $db->field("id, name, create_time, deletable, default_time")->where($condition)->order('create_time desc, id desc')->select();
		if ($serials === false)
			return $this->respond(-105, '无法查询存档卡列表');

		if (count($serials) == 0) {
			// 新建一个
			$model = new Model(); // 无需事务
			$ret = $this->get_default_save_serial($model, $deviceid, $db_device["bind_account"], $gameid);
			if ($ret['ret'] != 0)
				return $this->respond($ret['ret'], $ret['msg']);
			$serial = $ret['msg'];
			$serial['count'] = 0;
			$serials = array();
			array_push($serials, $serial);
		}
		else {
			// 读取每个存档序列的存档列表, delete_time = 0的项才是有效项
			foreach ($serials as &$serial) {
				$db = M('game_save');
				$condition = array();
				$condition['account_id'] = $db_device["bind_account"];
				$condition['serial_id'] = $serial['id'];
				$condition['delete_time'] = 0;
				$db_save = $db->field("COUNT(id) count")->where($condition)->select();
				if (!$db_save || count($db_save) == 0)
					continue;
				$serial['count'] = $db_save[0]["count"];

				$db_save = $db->field("id, derived_from, create_time, deletable, upload_time, gs_report_time, compressed_size, compressed_md5, derived_count, total_play_time, game_mode")->where($condition)->order('id desc')->limit('5')->select();
				if (!$db_save || count($db_save) == 0)
					continue;

				$serial['saves'] = $db_save;
			}
		}

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, 'success.', 'serials', $serials,$e_time);
	}

	/*
	 删除当前账号下指定游戏的指定存档。
	请求格式：形如http://localhost/api.php?m=Client&a=save_del&deviceid=xxx&logintoken=xxx&gameid=xxx&saveid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	saveid是Int32，指定的游戏存档ID。
	返回格式：
	{"ret":0,"msg":"success."}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无法将存档改为已删除状态
	非零值均表示失败。
	*/
	public function save_del() {
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$saveid = I('saveid','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 删除存档，其实把delete_time置为当前时间
		$db = M('game_save');
		$condition = array();
		$condition['account_id'] = $db_device["bind_account"];
		$condition['game_id'] = $gameid;
		$condition['id'] = $saveid;
		$condition['deletable'] = 1; // 只能删除允许删除的存档
		$update = array();
		$update['delete_time'] = time();
		$result = $db->where($condition)->save($update);
		G('end');
		$e_time=G('begin','end').'s';
		if($result === false)
			return $this->respond(-104, "无法删除存档拷贝：".$saveid,$e_time);

		return $this->respond(0, 'success.',$e_time);
	}
	/*
	 添加一个当前账号下指定游戏的存档序列。
	请求格式：形如http://localhost/api.php?m=Client&a=serial_add&deviceid=xxx&logintoken=xxx&gameid=xxx&name=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	name是一个长度不超过32字节的字符串，表示该存档序列的名字。
	返回格式：
	{"ret":0,"msg":"success.","new_serial":{"serial_id":16}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:未找到该游戏
	-105:该游戏不支持存档
	-106:存档序列个数已达上限
	-200:无法新增存档序列
	非零值均表示失败。
	*/
	public function serial_add() {
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$name = I('name','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 检查游戏是否存在
		$db = M('game');
		$condition = array();
		$condition['game_id'] = $gameid;
		$db_game = $db->field("save_enabled")->where($condition)->select();
		if (!$db_game || count($db_game) == 0)
			return $this->respond(-104, "未找到游戏ID：$gameid");
		if ($db_game[0]['save_enabled'] == 0)
			return $this->respond(-105, "该游戏不支持存档。");

		// 检查用户是否对这个游戏有访问权限
			
		// 检查每个用户、每个游戏的存档序列不能超过？？？个
		$max_num = C('MAX_SERIAL_NUM_PER_GAME');
		$db = M('game_save_serial');
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$db_serial = $db->field("id")->where($condition)->select();
		if ($db_serial != null && count($db_serial) > $max_num)
			return $this->respond(-106, "游戏存档卡个数:".count($db_serial)."超出限制。上限为:".$max_num);

		$deletable = 1; // 默认情况下，新建的存档序列可以删除
		if (count($db_serial) == 0)
			$deletable = 0; // 如果是第一个存档序列，则不可删除

		// 添加存档序列
		$db = M('game_save_serial');
		$data = array();
		$data['account_id'] = $db_device["bind_account"];
		$data['game_id'] = $gameid;
		$data['deletable'] = $deletable;
		$data['name'] = $name;
		$data['create_time'] = time();
		$data['delete_time'] = 0;
		$data['default_time'] = time(); // 新建存档卡自动成为默认
		$result = $db->add($data);
		if($result === false)
			return $this->respond(-104, "无法添加新的存档卡。");

		$new_serial["serial_id"] = $result;
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond_ex(0, 'success.', "new_serial", $new_serial,$e_time);
	}
	/*
	 设置一个当前账号下指定游戏的存档序列为默认序列。
	请求格式：形如http://localhost/api.php?m=Client&a=serial_def&deviceid=xxx&logintoken=xxx&gameid=xxx&serialid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	name是一个长度不超过32字节的字符串，表示该存档序列的名字。
	返回格式：
	{"ret":0,"msg":"success.","new_serial":{"serial_id":16}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:未找到该游戏
	-105:该游戏不支持存档
	-106:存档序列个数已达上限
	-200:无法新增存档序列
	非零值均表示失败。
	*/
	public function serial_def() {
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid',0);
		$serialid = I('serialid',0);

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 设置存档序列为默认
		$db = M('game_save_serial');
		$condition = array();
		$condition['id'] = $serialid;
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['delete_time'] = 0;
		$data = array();
		$data['default_time'] = time();
		$db_ret = $db->where($condition)->save($data);
		if ($db_ret === false)
			return $this->respond(-106, "无法设置存档卡 $serialid 为默认存档卡.");
		if ($db_ret === 0)
			return $this->respond(-107, "没有找到存档卡 $serialid .");

		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond(0, 'success.',$e_time);
	}

	/*
	 删除一个当前账号下指定游戏的存档序列。
	请求格式：形如http://localhost/api.php?m=Client&a=serial_del&deviceid=xxx&logintoken=xxx&gameid=xxx&serialid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	serialid是Int32，指定的游戏存档序列ID。
	返回格式：
	{"ret":0,"msg":"success."}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	非零值均表示失败。
	*/
	public function serial_del() {
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$serialid = I('serialid','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// TODO: 使用事务

		// 查找该序列下是否有不可删除的存档，如果是，则不能删除序列
		$db = M('game_save');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['serial_id'] = $serialid;
		$condition['deletable'] = 0;
		$db_save = $db->where($condition)->select();
		if ($db_save === false)
			return $this->respond(-104, "无法查询该存档卡下的存档拷贝。");
		if (count($db_save) > 0)
			return $this->respond(-105, "该存档卡下有不允许删除的存档拷贝。");

		// 查询该序列是否可以删除
		$db = M('game_save_serial');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['id'] = $serialid;
		$db_serial = $db->where($condition)->select();
		if ($db_serial === false)
			return $this->respond(-106, "无法查询存档卡：$serialid");
		if ($db_serial[0]['deletable'] != 1)
			return $this->respond(-107, "该存档卡不允许删除。");

		// 删除存档序列，其实把delete_time置为当前时间
		$db = M('game_save_serial');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['id'] = $serialid;
		$update = array();
		$update['delete_time'] = time();
		$db_serial = $db->where($condition)->save($update);
		if ($db_serial === false)
			return $this->respond(-108, "无法删除存档卡：$serialid");

		// 删除存档序列下的存档，其实把delete_time置为当前时间
		$db = M('game_save');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['serial_id'] = $serialid;
		$update = array();
		$update['delete_time'] = time();
		$db_save = $db->where($condition)->save($update);
		G('end');
		$e_time=G('begin','end').'s';
		if ($db_save === false)
			return $this->respond(-109, "无法删除存档卡下的存档拷贝。",$e_time);

		return $this->respond(0, 'success.',$e_time);
	}
	/**
	 * @desc 创建一个新的账号
	 * @param unknown $db_account
	 * @param string $nick_name
	 * @param number $avatar
	 * @param string $bind_email
	 * @param string $bind_phone
	 * @return boolean|unknown
	 */
	public function create_account($db_account,$nick_name="",$avatar="",$bind_email="",$bind_phone=""){
		//create account
		$insert_data = array(
				'status'=>1,  				//0，禁用；1，启用
				'level'=>0,  				//用户等级，默认都是0级
				'bind_email'=>$bind_email,
				'bind_phone'=>$bind_phone,
				'password'=>"",				//密码的MD5
				'nickname'=>$nick_name,		//用户昵称
				'avatar'=>$avatar,      	//用户头像
				'total_play_time'=>0,		//总游戏时间
				'bean'=>0,					//剩余云豆
				'gift_coin_num'=>0,			//剩余云贝
				'gold'=>0,					//剩余G币
				'used_bean_num'=>0,			//总消费的云豆
				'used_coin_num'=>0,			//总消费的云贝
				'used_gold_num'=>0,			//总消费的G币
				'exp'=>0,					//经验值，和等级相关
				'create_time'=>time(),
				'update_time'=>time(),
				'bought_coin_num'=>0,		//废弃
		);
		$ret = $db_account->add($insert_data);
		if(!$ret)
			return false;

		return $ret;
	}
	/**
	 * @desc 获取最大连续签到的天数
	 * @param object $obj
	 * @param int $account_id
	 * @return int 存在则返回 否则返回0
	 */
	public function getMaxContinuouslyDay($obj,$account_id){
		$condition = array();
		$condition['account_id'] = $account_id;
		$data = $obj->field("continuously_day")->where($condition)->order("continuously_day desc")->limit(1)->select();
		if($data)
			return $data['0']['continuously_day'];
		return 0;
	}
	/**
	 * @desc 第一次连续签到获取额外加成金币信息
	 * @return array $extra_gift_coin_info
	 */
	public function getSignExtraCoinInfo(){
		$extra_gift_coin_info = array();
		$db = M('sign_in');
		$data = $db->select();
		if($data){
			foreach ($data as $key =>$val){
				$extra_gift_coin_info[$val['sign_day']] = $val['extra_gift_coin'];
			}
		}
		return $extra_gift_coin_info;
	}
	/**
	 * @desc 获取加成G币
	 * @param object $db_continuously_sign_in
	 * @param int $accout_id
	 * @return Ambigous <number, unknown>
	 */
	public function getSignExtraCoin($db_continuously_sign_in,$accout_id){
		$extra_gift_coin = 0;
		//获取历史数据中最大的连续签到天数
		$history_max_day = $this->getMaxContinuouslyDay($db_continuously_sign_in, $accout_id);
		$history_max_day = (int)$history_max_day + 1;
		//检查是否还可以领取额外加成G币
		if($history_max_day<=7){
			$extra_gift_info = $this->getSignExtraCoinInfo();
			$extra_gift_coin = $extra_gift_info[$history_max_day];
			if ($extra_gift_coin == null)
				$extra_gift_coin = 0;
		}
		return $extra_gift_coin;
	}

	//获取指定游戏的可售卖存档列表。
	/*
	 获取指定游戏的存档列表,只有已登录的设备才能获取。
	请求格式：形如http://localhost/api.php?m=Client&a=gamesave_sale_list&deviceid=xxx&logintoken=xxx&gameid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	返回格式：
	{"ret":0,"msg":"success","gamesaves":[{"id":"4","chargepoint_id":"4","game_id":"14","filename":"test","compressed_size":"123","compressed_md5":"asdfasdfasdfasdf","name":"\u96f7\u66fc\u4f20\u5947\u901a\u5173\u5b58\u6863","type":"1","type_name":"\u6e38\u620f\u5b58\u6863","status":"1","coin":"1000","create_time":"1406191637","update_time":"1409287327"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无法查询该游戏的可售卖存档。
	非零值均表示失败。
	*/
	public function gamesave_sale_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');//游戏id

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device["bind_account"];

		// 读取指定游戏存档序列列表
		$db = M('chargepoint_gamesave'); //售卖存档表
		$condition = array();
		$condition['_string'] = "cpgs.chargepoint_id = cp.id";
		$condition['cpgs.game_id'] = $gameid;
		$condition['cp.type'] = 1;
		$condition['cp.status'] = 1;   // 不读取禁用的计费点
		$gamesaves = $db->table('july_chargepoint_gamesave cpgs, july_chargepoint cp')->field('cp.*,cpgs.game_id,cpgs.name_for_user,cpgs.filename,cpgs.desc,cpgs.compressed_size')->where($condition)->select();
		if ($gamesaves === false)
			return $this->respond(-104, "无法查询该游戏的可售卖存档。".$db->getLastSql());

		if (count($gamesaves) > 0) {
			// 找出用户购买过的游戏存档计费点
			$bought_chargepoints = array();
			$condition = array();
			$condition['pc.charge_to_account_id'] = $account_id;
			$db_ret = $db->table('july_payment_card pc')->join('july_chargepoint cp on pc.chargepoint_id=cp.id and cp.type=1')->field('pc.chargepoint_id')->where($condition)->select();
			if ($db_ret === false)
				return $this->return_ex(-330, "无法查询您是否购买过该存档。");
			if ($db_ret != null)
				$bought_chargepoints = array_merge($bought_chargepoints, $db_ret);

			$condition = array();
			$condition['account_id'] = $account_id;
			$db_ret = $db->table('july_payment_coin pc')->join('july_chargepoint cp on pc.chargepoint_id=cp.id and cp.type=1')->field('pc.chargepoint_id')->where($condition)->select();
			if ($db_ret === false)
				return $this->return_ex(-340, "无法查询您是否购买过该存档。");
			if ($db_ret != null)
				$bought_chargepoints = array_merge($bought_chargepoints, $db_ret);

			// 查询用户是否购买过该存档
			foreach ($gamesaves as &$gamesave) {
				$gamesave['bought'] = 0;
				foreach ($bought_chargepoints as $cp) {
					if ($cp['chargepoint_id'] == $gamesave['id']) {
						$gamesave['bought'] = 1;
						break;
					}
				}
			}
		}
		else
			$gamesaves = array();

		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond_ex(0, "success", "gamesaves", $gamesaves,$e_time);
	}

	/*
		获取指定渠道,设备,产品的启动logo图
	*/
	public function pid_logo(){
		$pid = I('pid','');
		$product = I('product','');
		$client_type = I('client_type','');

		$pid_logo_model = D('PidLogo');
		$pid_logo = $pid_logo_model->get_pid_logo($pid, $product, $client_type, 'logo_url');
		if(count($pid_logo) > 1)
			$pid_logo = $pid_logo[0];
		if(count($pid_logo) < 1)
			$pid_logo = (object)array();
		return $this->respond_ex(0, 'success', 'pid_logo', $pid_logo);
	}
}

