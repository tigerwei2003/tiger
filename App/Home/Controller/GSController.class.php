<?php
namespace Home\Controller;
use Home\Controller\BaseController;
use Think\Log;
use Think\Model;
//API接口文件
class GSController extends BaseController {
	public function index(){
		$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
	}


	/*
		验证用户是否有权限启动该游戏。
	请求格式：形如http://localhost/api.php?m=GS&a=start_game&deviceid=00000000-35ae-97e0-0033-c5870033c587&logintoken=yfVLwB1zyztk7dvTa9QBcsGiaKKkw7hx&accountid=1550&gameid=1005&gsid=1&gamepackid=100&paymenttype=4&playmode=4
	accountid是一个32位整数，代表一个唯一的账户。
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gsid是一个32位整数，代表一个唯一的GS。
	gameid是一个32位整数，代表一个唯一的游戏。
	gamepackid是一个32位整数，代表一个唯一的游戏包。
	playmode是一个32位整数，表示单人游戏、多人游戏、是否有存档。
	paymenttype是一个32位整数，表示计费类型。
	返回格式：
	{"ret":0,"msg":"success","game":{"status":"1","level":"0","coin":"100","left_trial_time":"0","charge_after_start":"10","save_enabled":"0","category":"2","max_player":"2"}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	game.coin是一个32位整数，表示该游戏的币值消耗，如果是街机则是每次投币的币值，如果是其他游戏则是按次计费的币值。
	game.left_trial_time是一个32位整数，表示该游戏剩余的试玩时间，只有在试玩模式下才会返回。
	game.left_trial_time是一个32位整数，表示该游戏剩余的试玩时间，只有在试玩模式下才会返回。
	game.charge_after_start是一个32位整数，表示该游戏启动多少秒之后可以开始投币或者按次计费
	game.save_enabled是一个32位整数，表示该游戏是否支持存档。
	game.max_player是一个32位整数，表示该游戏支持同屏玩家数。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到此设备的记录。
	-102:此设备尚未绑定帐号。
	-103:此设备的登录Token不正确。
	-104:此设备的绑定帐号不吻合。
	-105:未找到绑定帐号
	-106:未找到该游戏
	-107:该游戏游戏已经禁用。
	-108:账户级别不够，不能玩这个游戏。
	-109:此游戏只支持单人模式，请使用单人模式运行游戏。
	-110:此游戏不支持存档，请使用无存档模式运行游戏。
	-111:同一个账户不能同时启动两个游戏。而此账户于xxxx:xx:xx x:x:x运行的游戏尚未结束。
	-200:此游戏不支持试玩。
	-201:您在此游戏的试玩时间已经用完。
	-210:此游戏不是街机游戏，不能用街机模式启动。
	-220:这个游戏需要xx个币，您的剩余币不足。
	-221:这个游戏是街机游戏，不能使用按次付费的方式运行。
	-222:没找到对应的游戏
	-223:无法此次赠送虚拟币
	-224:无法记录此次虚拟币增加记录
	-230:暂不支持按照游戏时长付费的模式.
	-240:游戏包不存在或者此游戏不在该游戏包内。
	-241:此游戏包已经被禁用。
	-242:未找到本账户购买此游戏包的记录。
	-243:游戏包已经过期。
	-250:暂不支持数字发行的模式.
	-300:暂不支持未知付费模式
	-401:battle错误
	-402:用户连接超时  (超过20秒)
	-403:参战人数不齐
	-405:该战斗已经结束
	-406:该战斗的状态异常
	非零值均表示失败。
	*/
	public function start_game(){
		$accountid = I('accountid','');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$gsid = I('gsid','');
		$gamepackid = I('gamepackid','');
		$playmode = I('playmode','');
		$paymenttype = I('paymenttype','');
		$saveserialid = I('saveserialid',0);
		$saveid = I('saveid',0);
		$battleid = I("battleid",'');
		$gsip = get_client_ip();

		if ($deviceid == '' || $accountid == '' || $gameid == '' || $gamepackid == '' || $playmode == '' || $paymenttype == '')
			return $this->respond(-100, "invalid request. no deviceid or accountid or gameid or gamepackid or playmode or paymenttype");

		// 读取该设备绑定的帐号ID
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		if ($accountid == 0)
			return $this->respond(-112, "accountid is zero");
		if ($db_device["bind_account"] != $accountid)
			return $this->respond(-104, "accountid $accountid does not match bind_account ".$db_device["bind_account"]);
			
		// get account info
		$db = M('account');
		$condition = array();
		$condition['id'] = $db_device["bind_account"];
		$db_account = $db->field("level,gift_coin_num,bean,gold")->where($condition)->find();
		if (!$db_account)
			return $this->respond(-105, "can not find bind_account ".$db_device["bind_account"]);

		// 查询游戏信息
		$db = M('game');
		$condition = array();
		$condition['game_id'] = $gameid;
		$db_game = $db->field('status,level,coin,trial_time,charge_after_start,save_enabled,category,max_player')->where($condition)->select();
		if (!$db_game || count($db_game) == 0)
			return $this->respond(-106, "can not find game $gameid");
		$game = $db_game[0];

		if ($game["status"] != 1)
			return $this->respond(-107, "this game $gameid is disabled.");
		if ($game["level"] > $db_account["level"])
			return $this->respond(-108, "account level is lower than game level ".$game["level"]);
		if ($game["max_player"] == 1 && ($playmode == 3 || $playmode == 4))
			return $this->respond(-109, "this game support single player only. please use single player mode to start game.");
		if ($game["save_enabled"] == 0 && ($playmode == 1 || $playmode == 3))
			return $this->respond(-110, "this game $gameid does not support save. please use no save mode to start game.");
			
		// 检查是否有正在运行的游戏，同一个账户同时只能运行一个游戏。
		$db = M('history_account_game_time');
		$condition = array();
		$condition['account_id'] = $accountid;
		$condition['end_code'] = '0';
		$condition['is_online_gs'] = '1';
		$condition['gs_last_report_time'] = array('gt', (time()-60));
		$db_history = $db->field('game_id, gs_start_time, create_time')->where($condition)->select();
		if (count($db_history) > 0)
			return $this->respond(-111, "one account can start only one game at the same time. this account has started game ".$db_history[0]["game_id"]." at ".date("Y-m-d H:i:s",$db_history[0]["create_time"]));


		// 游戏支付的方式
		// enum GamePayment {
		// 	  TRIAL         = 0;    // 试玩，支持存档，但只能玩几十分钟，目前只允许单人模式
		// 	  ARCADE_COIN   = 1;    // 街机投币模式，允许单人模式、多人模式
		// 	  RUN_CHARGE    = 2;    // 按次付费，允许单人模式、多人模式
		// 	  TIME_CHARGE   = 3;    // 按游戏时长付费，允许单人模式、多人模式
		// 	  SUBSCRIPTION  = 4;    // 已订阅，截止日期之前都可以玩，允许单人模式、多人模式
		// 	  DIGIT_COPY    = 5;    // 已购买，可能是无限期的，允许单人模式、多人模式
		// 	  RING_CHARGE   = 6;    // 擂台赛模式
		// }
		if ($paymenttype == 0) {
			if ($game["trial_time"] == 0)
				return $this->respond(-200, "this game does not support trial mode.");
				
			// 查询游戏的剩余试玩时间
			$db = M('history_account_game_time');
			$condition = array();
			$condition['game_id'] = $gameid;
			$condition['_string'] = 'account_id='.$db_device["bind_account"]." and (gs_last_report_time-gs_start_time)>0 and gs_start_time>".(time()-86400*7);
			$db_trial_time = $db->field('SUM(gs_last_report_time-gs_start_time) t')->where($condition)->select();
			if ($db_trial_time != null && count($db_trial_time) > 0) {
				$game["left_trial_time"] = max(0, $game["trial_time"]-$db_trial_time[0]["t"]);
				if ($game["left_trial_time"] <= 0)
					return $this->respond(-201, "trial time is over");
			}
		}
		else if ($paymenttype == 1) {
			if ($game["category"] != 2)
				return $this->respond(-210, "this game $gameid is not arcade game. can not start with arcade mode.");
		}
		else if ($paymenttype == 2) {
			if ($game["category"] == 2)
				return $this->respond(-221, "this game is an arcade game. can not start with charge-per-run mode.");
			
			$db = M('chargepoint');
			$condition = array();
			$condition['cpr.game_id'] = $gameid;
			$condition['cp.status'] = 1;
			$condition['_string'] = "cp.id = cpr.chargepoint_id";
			$cp = $db->table('july_chargepoint cp, july_chargepoint_runonce cpr')->field('cp.bean, cp.coin, cp.gold, cp.id')->where($condition)->find();
			if (!$cp)
				return $this->respond(-222, "can not find chargepoint of game $gameid paymenttype $paymenttype");

			// 判断是否是单款游戏的APK：判断条件是device->pid是否结尾是数字。如果是，则扣币为0
			$db = M('device');
			$pid = $db->field("pid")->where(array("device_uuid"=>$deviceid))->find();
			if ($pid && preg_match('/\d+$/',$pid['pid'])) {
				$cp['bean'] = 0;
				$cp['coin'] = 0;
			}
			
			// 确认bean,coin,gold中至少有一个是有效值（>=0），且该账户付得起
			$bean_affordable = ($cp['bean'] >= 0 && $db_account['bean'] >= $cp['bean']);
			$coin_affordable = ($cp['coin'] >= 0 && $db_account['gift_coin_num'] >= $cp['coin']);
			$gold_affordable = ($cp['gold'] >= 0 && $db_account['gold'] >= $cp['gold']);
			if (!$bean_affordable && !$coin_affordable && !$gold_affordable) {
				return $this->respond(-220, "you can not afford this game.");
			}
		}
		else if ($paymenttype == 3) {
			return $this->respond(-230, "do not support payment-per-time mode.");
		}
		else if ($paymenttype == 4) {
			// 查询游戏包
			$db = M('gamepack');
			$condition = array();
			$condition['gp.pack_id'] = $gamepackid;
			$condition['gpg.gamepack_id'] = $gamepackid;
			$condition['gpg.game_id'] = $gameid;
			$db_gamepack = $db->table('july_gamepack gp, july_link_gamepack_game gpg')->field("gp.status")->where($condition)->select();
			if (!$db_gamepack || count($db_gamepack) == 0)
				return $this->respond(-240, "this gamepack $gamepackid is not found or invalid or this game $gameid is not in this pack.");
			if ($db_gamepack[0]["status"] != 1)
				return $this->respond(-241, "this gamepack $gamepackid is disabled.");
				
			// 读取用户购买游戏包的记录
			$db = M('link_account_gamepack');
			$condition = array();
			$condition['account_id'] = $db_device["bind_account"];
			$condition['gamepack_id'] = $gamepackid;
			$db_record = $db->field("deadline_time")->where($condition)->select();
			if (!$db_record || count($db_record) == 0)
				return $this->respond(-242, "this account has never purchased this gamepack $gamepackid");
			if ($db_record[0]["deadline_time"] <= time())
				return $this->respond(-243, "the purchase of this gamepack has expired.");
		}
		else if ($paymenttype == 5) {
			return $this->respond(-250, "do not support digital publish mode.");
		}
		else if ($paymenttype == 6) {
			//擂台分支。根据$gsid,$gameid,$battleid判断arena的状态
			$nowtime = time();
			$db_battle = M("arena_battle");
			$condition = array();
			$condition['b.id'] = $battleid;
			$condition['a.game_id'] = $gameid;
			$condition['a.gs_id'] = $gsid;
			$condition['a.status'] = 1;
			$condition['_string'] = "b.arena_id = a.id";
			$battle = $db_battle->table("july_arena as a,july_arena_battle as b")->field("arena_id,player1,player2,status1,status2,phase,active_time1,active_time2,end_time")->where($condition)->find();
			if(!$battle)
				return $this->respond(-401,"Fail to find battle,battleid:$battleid");
			if($battle['end_time'] != 0)
				return $this->respond(-402,"The battle has ended,battleid:$battleid");
			if($accountid != $battle['player1'] && $accountid != $battle['player2'])
				return $this->respond(-403,"The user is not player1 or player2,battleid:$battleid,accountid:$accountid");
			if (($accountid == $battle['player1'] && ($nowtime - $battle['active_time1']) > 40) && $battle['status1'] != 5 ||
					($accountid == $battle['player2'] && ($nowtime - $battle['active_time2']) > 40) && $battle['status2'] != 5)
				return $this->respond(-404,"join game timeout,battleid:$battleid,accountid:$accountid");

			Log::write("Trend the account_id:$accountid battleid:$battleid start_game", Log::INFO);
			//修改history_account_arena_time中的battle_nums字段
			$info = array('account_id'=>$accountid,
					'arena_id'=>$battle['arena_id'],
					'leave_time'=>time(),
					'battle_nums'=>array('exp','battle_nums+1')
			);
			$this->history_account_arena_time($info);
		}
		else {
			return $this->respond(-300, "do not support unknown payment type: ".$paymenttype);
		}

		$is_online_gs = 0;
		// TODO: 这里使用一个不太严谨的办法来判断GS IP是否是线上GS。根据region表里的测速服务器IP，如果a.b.c.d的a.b.c相同，则认为是线上GS。
		$db = M('region');
		$db_regions = $db->field("speed_test_addr,speed_test_addr_backup")->select();
		if (!$db_regions || count($db_regions) == 0)
			return $this->respond(-500, "fail to select table region to find out if gs is online gs.");
		foreach ($db_regions as $region) {
			$speed_test_addr = substr($region["speed_test_addr"], 0, strrpos($region["speed_test_addr"], "."));
			$speed_test_addr_backup = substr($region["speed_test_addr_backup"], 0, strrpos($region["speed_test_addr_backup"], "."));
			$temp_gs_ip = substr($gsip, 0, strrpos($gsip, "."));

			if ($temp_gs_ip == $speed_test_addr || $temp_gs_ip == $speed_test_addr_backup)
				$is_online_gs = 1;
		}

		// 添加新的游戏记录
		$db = M('history_account_game_time');
		$data = array();
		$data["account_id"] = $accountid;
		$data["device_uuid"] = $deviceid;
		$data["gs_id"] = $gsid;
		$data["game_id"] = $gameid;
		$data["gamepack_id"] = $gamepackid;
		$data["gs_start_time"] = 0;
		$data["gs_ip"] = $gsip;
		$data["is_online_gs"] = $is_online_gs;
		$data["play_mode"] = $playmode;
		$data["payment_type"] = $paymenttype;
		$data["serial_id"] = $saveserialid;
		$data["save_id"] = $saveid;
		$data["create_time"] = time();
		$data["gs_last_report_time"] = time();
		$data["end_code"] = 0;
		$db_game_session_id = $db->add($data);
		if (!$db_game_session_id)
			return $this->respond(-901, "fail to insert history_account_game_time. account:$accountid gs:$gsid game:$gameid");

		// 将本次游戏的唯一编号返回给GS，稍后time_report里会用
		$game['session_id'] = $db_game_session_id;

		return $this->respond_ex(0, "success", "game", $game);
	}


	/*
		GS向Web汇报用户正在进行的游戏。
	请求格式：形如http://localhost/api.php?m=GS&a=time_report&deviceid=00000000-35ae-97e0-0033-c5870033c587&logintoken=yfVLwB1zyztk7dvTa9QBcsGiaKKkw7hx&accountid=1550&gameid=1005&gsid=1&gamepackid=100&paymenttype=4&playmode=4&starttime=xxx&lastreporttime=xxx&endcode=xxx&sessionid=xxx
	accountid是一个32位整数，代表一个唯一的账户。
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gsid是一个32位整数，代表一个唯一的GS。
	gameid是一个32位整数，代表一个唯一的游戏。
	gamepackid是一个32位整数，代表一个唯一的游戏包。
	playmode是一个32位整数，表示单人游戏、多人游戏、是否有存档。
	paymenttype是一个32位整数，表示计费类型。
	starttime是一个32位整数，表示游戏开始的epoch time。
	lastreporttime是一个32位整数，表示最后记录到的游戏epoch time。
	endcode是一个32位整数，表示用户退出游戏的状态。
	sessionid是一个32位整数，表示用户本次游戏的唯一编号。
	返回格式：
	{"ret":0,"msg":"success"}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到此设备的记录。
	-102:此设备尚未绑定帐号。
	-103:此设备的登录Token不正确。
	-104:此设备的绑定帐号不吻合。
	-105:未找到绑定帐号
	-106:无法插入新的游戏时间数据
	-107:无法更新游戏时间数据
	-108:上次记录的游戏已经结束，为什么还有重复汇报？
	-109:没找到对应的帐号
	-110:无法更新帐号的游戏时间/经验值/级别
	非零值均表示失败。
	*/
	public function time_report(){
		$accountid = I('accountid','');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$gsid = I('gsid','');
		$gamepackid = I('gamepackid','');
		$playmode = I('playmode','');
		$paymenttype = I('paymenttype','');
		$starttime = I('starttime','');
		$lastreporttime = I('lastreporttime','');
		$endcode = I('endcode','');
		$sessionid = I('sessionid',0);

		$gsip = get_client_ip();

		if ($deviceid == '' || $accountid == '' || $gameid == '' || $gamepackid == '' || $playmode == '' || $paymenttype == '' || $starttime == '' || $lastreporttime == '' || $endcode == '')
			return $this->respond(-100, "invalid request. no deviceid or accountid or gameid or gamepackid or playmode or paymenttype or starttime or lastreporttime or endcode");

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		if ($accountid == 0)
			$accountid = $db_device["bind_account"];
		if ($db_device["bind_account"] != $accountid)
			return $this->respond(-104, "unmatched accountid.");

		// 查找需要更新的记录
		$db = M('history_account_game_time');
		$condition = array();
		$condition['account_id'] = $accountid;
		$condition['device_uuid'] = $deviceid;
		$condition['gs_id'] = $gsid;
		//$condition['gs_ip'] = $gsip; 因为部分GS有多个出口IP，所以这里不再校验IP。
		$condition['game_id'] = $gameid;
		$condition['end_code'] = 0;
		if ($sessionid == 0) {
			// 老的GS，还没支持sessionid，只能利用gs_start_time来判断：要么是0，要么是$starttime
			$condition['gs_start_time'] = array('between',array(0,$starttime));
		}
		else {
			// 新的GS，已经支持sessionid
			$condition['id'] = $sessionid;
		}
		$db_history = $db->field("gs_start_time")->where($condition)->order('id desc')->limit(1)->select();
		if (!$db_history || count($db_history) == 0)
			return $this->respond(-108, "record is not found.");

		// 更新gs_start_time gs_last_report_time end_code
		$update = array();
		if ($db_history[0]["gs_start_time"] == 0) { // 如果是首次time_report，则更新gs_start_time
			$update["gs_start_time"] = $starttime;
			$condition['gs_start_time'] = 0;
		}
		else
			$condition['gs_start_time'] = $starttime;
		$update["gs_last_report_time"] = $lastreporttime;
		$update["end_code"] = $endcode;
		$db_link = $db->where($condition)->save($update);
		if ($db_link === false)
			return $this->respond(-107, "fail to update data.");

		// 如果游戏已经结束，则更新用户的经验、等级等
		$game_time = $lastreporttime - $starttime;
		if ($endcode != 0 && $game_time > 0 && $game_time < 86400) {
			$db = M('account');
			$condition = array();
			$condition['id'] = $accountid;
			$db_account = $db->field("total_play_time,exp,level")->where($condition)->select();
			if (!$db_account || count($db_account) == 0)
				return $this->respond(-109, "fail to select account");

			$update = array();
			$update["total_play_time"] = $db_account[0]["total_play_time"] + $game_time;
			$update["exp"] = $db_account[0]["exp"] + $game_time;
			$new_lvl = $this->get_level_from_exp($update["exp"]);
			$update["level"] = max($new_lvl, $db_account[0]["level"]);
			$db_link = $db->where($condition)->save($update);
			if (!$db_link)
				return $this->respond(-110, "fail to update account.");
			
			// 清除帐号信息缓存
			$account_model=D("Account");
			$account_model->clear_cache($accountid);
		}

		return $this->respond(0, "success");
	}

	/*
		GS向Web调用扣除用户虚拟币的接口。
	请求格式：形如http://localhost/api.php?m=GS&a=use_coin&deviceid=00000000-35ae-97e0-0033-c5870033c587&logintoken=yfVLwB1zyztk7dvTa9QBcsGiaKKkw7hx&accountid=1550&gameid=1005&gsid=1&gamepackid=100&paymenttype=4&playmode=4
	accountid是一个32位整数，代表一个唯一的账户。
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gsid是一个32位整数，代表一个唯一的GS。
	gameid是一个32位整数，代表一个唯一的游戏。
	gamepackid是一个32位整数，代表一个唯一的游戏包。
	playmode是一个32位整数，表示单人游戏、多人游戏、是否有存档。
	paymenttype是一个32位整数，表示计费类型。
	返回格式：
	{"ret":0,"msg":"success","coins":{"used_coin":"100","left_gift_coin":997600,"left_bought_coin":"1000","total_used_coin":6400}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到此设备的记录。
	-102:此设备尚未绑定帐号。
	-103:此设备的登录Token不正确。
	-104:此设备的绑定帐号不吻合。
	-105:非投币模式、按次付费的情况下，不支持调用use_coin
	-106:没找到对应的游戏
	-107:没找到对应的帐号
	-108:帐号剩余币不足
	-109:无法更新帐号的剩余币
	-110:无法记录此次扣币
	-111:未找到擂台
	非零值均表示失败。
	*/
	public function use_coin(){
		$accountid = I('accountid','');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$gsid = I('gsid','');
		$gamepackid = I('gamepackid','');
		$playmode = I('playmode','');
		$paymenttype = I('paymenttype','');

		$gsip = get_client_ip();
		$is_online_gs = 0; // 默认不是线上GS

		if ($deviceid == '' || $accountid == '' || $gameid == '' || $gamepackid == '' || $playmode == '' || $paymenttype == '')
			return $this->respond(-100, "invalid request. no deviceid or accountid or gameid or gamepackid or playmode or paymenttype");

		// 读取该设备绑定的帐号ID
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		if ($accountid == 0)
			$accountid = $db_device["bind_account"];
		if ($db_device["bind_account"] != $accountid)
			return $this->respond(-104, "unmatched accountid.");

		// 游戏支付的方式
		// enum GamePayment {
		// 	  TRIAL         = 0;    // 试玩，支持存档，但只能玩几十分钟，目前只允许单人模式
		// 	  ARCADE_COIN   = 1;    // 街机投币模式，允许单人模式、多人模式
		// 	  RUN_CHARGE    = 2;    // 按次付费，允许单人模式、多人模式
		// 	  TIME_CHARGE   = 3;    // 按游戏时长付费，允许单人模式、多人模式
		// 	  SUBSCRIPTION  = 4;    // 已订阅，截止日期之前都可以玩，允许单人模式、多人模式
		// 	  DIGIT_COPY    = 5;    // 已购买，可能是无限期的，允许单人模式、多人模式
		// 	  RING_CHARGE   = 6;    // 擂台赛模式
		// }
		$cp = array();
		if ($paymenttype == 1) // 街机投币
		{
			$db = M('chargepoint');
			$condition = array();
			$condition['cpa.game_id'] = $gameid;
			$condition['cp.status'] = 1;
			$condition['_string'] = "cp.id = cpa.chargepoint_id";
			$db_cp = $db->table('july_chargepoint cp, july_chargepoint_arcade cpa')->field('cp.coin, cp.bean, cp.gold, cp.id')->where($condition)->find();
			if (!$db_cp)
				return $this->respond(-106, "can not find chargepoint of game $gameid paymenttype $paymenttype");
			$cp = $db_cp;
		}
		else if ($paymenttype == 2) // 按次游戏
		{
			$db = M('chargepoint');
			$condition = array();
			$condition['cpr.game_id'] = $gameid;
			$condition['cp.status'] = 1;
			$condition['_string'] = "cp.id = cpr.chargepoint_id";
			$db_cp = $db->table('july_chargepoint cp, july_chargepoint_runonce cpr')->field('cp.coin, cp.bean, cp.gold, cp.id')->where($condition)->find();
			if (!$db_cp)
				return $this->respond(-106, "can not find chargepoint of game $gameid paymenttype $paymenttype");
			$cp = $db_cp;
				
			// 判断是否是单款游戏的APK：判断条件是device->pid是否结尾是数字。如果是，则扣云贝、云豆为0，但是G币不会为0
			$db = M('device');
			$pid = $db->field("pid")->where(array("device_uuid"=>$deviceid))->find();
			if ($pid && preg_match('/\d+$/',$pid['pid'])) {
				$cp['coin'] = 0;
				$cp['bean'] = 0;
			}
		}
		else if ($paymenttype == 6) // 擂台赛模式
		{
			$db_arena = M("arena");
			$condition = array();
			$condition['gs_id'] = $gsid;
			$condition['game_id'] = $gameid;
			$condition['status'] = 1;
			$arena = $db_arena->field("id")->where($condition)->select();
			if(!$arena)
				return $this->respond(-111,"GS could not find the arena,GSID:$gsid,gameid:$gameid");
			$db = M('chargepoint');
			$condition = array();
			$condition['cpa.arena_id'] = $arena[0]['id'];
			$condition['cp.status'] = 1;
			$condition['_string'] = "cp.id = cpa.chargepoint_id";
			$db_cp = $db->table('july_chargepoint cp, july_chargepoint_arena cpa')->field('cp.coin, cp.bean, cp.gold, cp.id')->where($condition)->find();
			if (!$db_cp)
				return $this->respond(-106, "can not find chargepoint of game $gameid paymenttype $paymenttype");
			$cp = $db_cp;
		}
		else // 只有街机投币模式、按次付费需要调用use_coin接口。
			return $this->respond(-105, "do not call use_coin when paymenttype=".$paymenttype);
			
		// 使用事务处理
		$model = new Model();
		$model->startTrans();

		// 扣币
		$ret = $this->use_account_money($model, $accountid, $cp);
		if($ret['ret'] != 0) {
			$model->rollback();
			return $this->respond($ret['ret'], "use_account_money failed.");// $ret['msg']); no chinese character
		}

		// 扣币之后的账户信息
		$coins = $ret['msg'];
		$coins["left_gift_coin"] = $ret['msg']["gift_coin_num"];
		$coins["total_used_coin"] = $ret['msg']["used_coin_num"];
		$coins["left_bought_coin"] = 0;
		$coins["used_bean"] = max(0, $cp['bean']);
		$coins["used_coin"] = max(0, $cp['coin']);
		$coins["used_gold"] = max(0, $cp['gold']);

		if ($cp['bean'] > 0 || $cp['coin'] > 0 || $cp['gold'] > 0) {
			// 添加虚拟币消费记录
			$data = array();
			$data["order_id"] = $this->generateRandomBigInt();
			$data["account_id"] = $accountid;
			$data["device_uuid"] = $deviceid;
			$data["bean"] = max(0, $cp['bean']);
			$data["coin"] = max(0, $cp['coin']);
			$data["gold"] = max(0, $cp['gold']);
			$data["chargepoint_id"] = $cp['id'];
			$data["game_id"] = $gameid;
			$data["gamepack_id"] = $gamepackid;
			$data["gs_id"] = $gsid;
			$data["play_mode"] = $playmode;
			$data["payment_type"] = $paymenttype;
			$data["create_time"] = time();
			$db_link = $model->table('july_payment_coin')->add($data);
			if (!$db_link) {
				$model->rollback();
				return $this->respond(-110, "fail to insert new record.");
			}
		}

		$model->commit();

		return $this->respond_ex(0, "success", "coins", $coins);
	}
	/*
		下载一个指定的存档。如果失败，则Http Response Header是404，内容是json；如果成功，Header是200，内容是文件数据。
	请求格式：形如http://localhost/api.php?m=GS&a=save_download&deviceid=xxx&logintoken=xxx&gameid=xxx&serialid=xxx&saveid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	serialid是Int32，指定的游戏存档序列ID。
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
	-104:未找到该存档的记录
	-105:存档文件大小与数据库记录不匹配
	-106:存档文件MD5与数据库记录不匹配
	-107:未找到该存档的文件
	非零值均表示失败。
	*/
	public function save_download() {
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$serialid = I('serialid','');
		$saveid = I('saveid','');
		$file = I('file',1);

		if ($deviceid == '')
			return $this->respond_404_if_failed(-100, "invalid request. deviceid is empty.");

		// 读取该设备绑定的帐号ID
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond_404_if_failed($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 检查要下载的存档是否存在
		$db = M('game_save');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['serial_id'] = $serialid;
		$condition['id'] = $saveid;
		$condition['delete_time'] = 0;
		$condition['upload_time'] = array('gt', 0);
		$condition['compressed_md5'] = array('neq', '');
		$db_save = $db->field('compressed_md5,compressed_size')->where($condition)->select();
		if (!$db_save && count($db_save) == 0)
			return $this->respond_404_if_failed(-104, "fail to find save $saveid.");

		// 如果不需要下载文件，则返回该存档的信息即可
		if ($file == 0) {
			$save['compressed_md5'] = $db_save[0]["compressed_md5"];
			$save['compressed_size'] = $db_save[0]["compressed_size"];
			return $this->respond_ex(0, "success.", "save", $save);
		}
			
		//var_dump($db_save);
		$compressed_md5 = $db_save[0]["compressed_md5"];
		$compressed_size = $db_save[0]["compressed_size"];
		$accountid = $db_device["bind_account"];

		// TODO：未来负载高时，可以把部分请求redirect到其他HTTP下载服务器上。

		// 存档文件命名：<存档ID>_<存档MD5，小写>.save, 如123_4387904830a4245a8ab767e5937d722c.save
		$compressedname = $saveid."_".strtolower($compressed_md5).".save";
		$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");

		// 检查存档文件是否存在，如果存在且大小相同，且MD5相同，则下载该文件
		// 存档目录格式：每层1000个目录，帐号ID，游戏ID，存档序列ID，存档文件。
		$path = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($accountid/1000000000).DIRECTORY_SEPARATOR.intval($accountid/1000000).DIRECTORY_SEPARATOR.intval($accountid/1000).DIRECTORY_SEPARATOR.$accountid.DIRECTORY_SEPARATOR.$gameid.DIRECTORY_SEPARATOR.$serialid.DIRECTORY_SEPARATOR.$compressedname;
		if (!file_exists($path)) {
			if (C("ENABLE_OSS") === true) {
				// 本地文件不存在，尝试从阿里云OSS下载
				// Read and write for owner, read for everybody else
				$final_dir = dirname($path);
				if (!file_exists($final_dir) && !mkdir($final_dir, 0744, true))
					return $this->respond_404_if_failed(-207, "Failed create $final_dir");

				$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
				if ($ret['ret'] != 0)
					return $this->respond_404_if_failed($ret['ret'], $ret['msg']);
				$client = $ret['msg'];

				// 阿里云OSS上的路径
				$key = "u"."/".intval($accountid/1000000000)."/".intval($accountid/1000000)."/".intval($accountid/1000)."/".$accountid."/".$gameid."/".$serialid."/".$compressedname;
				$ret = $this->getObjectAsFile($client, C("OSS_UDS_BUCKET"), $key, $path);
				if ($ret['ret'] != 0)
					return $this->respond_404_if_failed($ret['ret'], $ret['msg']);
			}
			else
				return $this->respond_404_if_failed(-107, "fail to find file of save: $saveid. path: $path");
		}
			
		if (filesize($path) != $compressed_size)
			return $this->respond_404_if_failed(-105, "$path size ".filesize($path)." doesn't match ".$compressed_size);

		// 检查MD5是否匹配
		$file_md5 = strtolower(md5_file($path));
		if ($file_md5 != $compressed_md5)
			return $this->respond_404_if_failed(-106, "$path md5 $file_md5 doesn't match $compressed_md5");

		Log::write("save_download,ret:0,msg:success.begin readfile", Log::INFO);

		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		header("Cache-Control: public"); // needed for i.e.
		header("Content-Type: application/zip");
		header("Content-Transfer-Encoding: Binary");
		header("gloud_compressed_md5: ".$compressed_md5);
		header("Content-Length:".filesize($path));
		header("Content-Disposition: attachment; filename=$compressedname");
		readfile($path);
		die();
	}

	/*
		创建一个新存档。
	请求格式：形如http://localhost/api.php?m=GS&a=save_create&deviceid=xxx&logintoken=xxx&gameid=xxx&gsid=xxx&serialid=xxx&parentsaveid=xxx&gamemode=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的已登录设备。
	logintoken是一个长度不超过32字节的字符串，表示该设备此次登录的Token。
	gameid是Int32，指定的游戏ID。
	gsid是Int32，是调用者GS的ID。
	serialid是Int32，指定的游戏存档序列ID。
	parentsaveid是Int32，从指定的存档继承，如果是-1则说明不指定父存档。
	gamemode是Int32，游戏模式，单人、多人。
	返回格式：
	{"ret":0,"msg":"success.","new_save":{"upload_token":573071289,"save_id":37}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:未找到父存档的记录
	-105:添加新存档记录失败
	-106:存档序列ID信息不匹配
	非零值均表示失败。
	*/
	public function save_create() {
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$gsid = I('gsid','');
		$serialid = I('serialid','');
		$parentsaveid = I('parentsaveid','');
		$gamemode = I('gamemode','');
		$gsip = get_client_ip();

		if ($deviceid == '')
			return $this->respond(-100, "invalid request. deviceid is empty.");

		// 读取该设备绑定的帐号ID
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		// 检查存档序列的信息是否正确
		$db = M('game_save_serial');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $db_device["bind_account"];
		$condition['id'] = $serialid;
		$condition['delete_time'] = 0;
		$db_serial = $db->field('COUNT(*) count')->where($condition)->select();
		if (!$db_serial && count($db_serial) == 0)
			return $this->respond(-106, "serial: $serialid is not found or invalid or mismatch.");

		// 检查要继承的存档是否存在、有效，如果在，则更新derived_count
		$parent_total_play_time = 0;
		if ($parentsaveid != -1) {
			$db = M('game_save');
			$condition = array();
			$condition['game_id'] = $gameid;
			$condition['account_id'] = $db_device["bind_account"];
			$condition['serial_id'] = $serialid;
			$condition['id'] = $parentsaveid;
			$condition['delete_time'] = 0;
			$condition['upload_time'] = array('gt', 0);
			$condition['compressed_md5'] = array('neq', '');
			$db_save = $db->where($condition)->setInc('derived_count', 1);
			if (!$db_save && count($db_save) == 0)
				return $this->respond(-104, "parentsaveid: $parentsaveid is not found or invalid.");
			
			// 如果父存档存在，且total_play_time>0，则记录该值
			$parent_save = $db->where($condition)->find();
			if ($parent_save && $parent_save['total_play_time'] > 0) {
				$parent_total_play_time = $parent_save['total_play_time'];
			}
		}

		$new_save = array("upload_token" => rand(100000000, 999999999)); // upload token 是随机值

		// 创建新存档
		$db = M('game_save');
		$data = array();
		$data['game_id'] = $gameid;
		$data['account_id'] = $db_device["bind_account"];
		$data['device_uuid'] = $deviceid;
		$data['serial_id'] = $serialid;
		$data['gs_id'] = $gsid;
		$data['gs_ip'] = $gsip;
		$data['game_mode'] = $gamemode;
		$data['derived_from'] = $parentsaveid;
		$data['upload_token'] = $new_save["upload_token"];
		$data['create_time'] = time();
		$data['total_play_time'] = $parent_total_play_time;
		$db_ret = $db->add($data);
		if ($db_ret === false)
			return $this->respond(-105, "fail to create new save from parent:".$parentsaveid);

		$new_save["save_id"] = $db_ret;
			
		return $this->respond_ex(0, 'success.', "new_save", $new_save);
	}

	/*
		GS向Web声明持有一个存档。
	请求格式：形如http://localhost/api.php?m=GS&a=save_update_gs&saveid=xxx
	saveid是Int32，是GS声明持有的存档ID。
	返回格式：
	{"ret":0,"msg":"success."}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:无法更新存档记录
	非零值均表示失败。
	*/
	public function save_update_gs() {
		$saveid = I('saveid','');

		if ($saveid == '')
			return $this->respond(-100, "invalid request. saveid is empty.");

		$db = M('game_save');
		$condition = array();
		$condition['id'] = $saveid;
		$update = array();
		$update['gs_report_time'] = time();
		$db_save = $db->where($condition)->save($update);
		if (!$db_save && count($db_save) == 0)
			return $this->respond(-101, "fail to update save $saveid.");
			
		return $this->respond(0, 'success.');
	}


	/*
		上传一个存档。
	请求格式：形如http://localhost/api.php?m=GS&a=save_upload
	HTTP Post:
	deviceid长度不超过128的字符串，用户当前使用的设备ID。
	logintoken长度不超过128的字符串，用户当前使用的设备ID。
	gameid是Int32，指定的游戏ID。
	gsid是Int32，上传存档的GS ID。
	serialid是Int32，要上传的存档序列ID。
	saveid是Int32，要上传的存档ID。
	uploadtoken是长度不超过32的字符串，上传此存档所需的Token。
	compressedmd5是长度不超过32的字符串，上传存档压缩包的MD5。
	compressedsize是Int32，上传存档压缩包的字节数。
	contenthash是长度不超过32的字符串，存档所有内容的Hash值。

	上传文件的名称: upload

	返回格式：
	{“ret”:0,”msg”:”success.”}


	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:没找到目标存档记录
	-105:该存档已经上传过了
	-106:更新存档记录失败，无法设置为已上传状态
	-200:上传error值无效
	-201:无上传文件
	-202:上传文件大小超出FORM限制
	-203:上传error值未知
	-204:上传文件大小超出
	-205:上传文件大小和compressed_size不一样
	-206:上传文件md5和compressed_md5不一样
	-207:上传文件无法从临时目录移动到目标目录
	-208:上传文件无法从临时文件名改为正式文件名
	非零值均表示失败。
	*/
	public function save_upload() {
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$gameid = I('gameid','');
		$gsid = I('gsid','');
		$serialid = I('serialid','');
		$saveid = I('saveid','');
		$uploadtoken = I('uploadtoken','');
		$compressedmd5 = I('compressedmd5','');
		$compressedsize = I('compressedsize','');
		$contenthash = I('contenthash','');
		$file = I('file',1);

		$replacement_uri = "deviceid=$deviceid&logintoken=$logintoken&gameid=$gameid&gsid=$gsid&serialid=$serialid&saveid=$saveid&uploadtoken=$uploadtoken&compressedmd5=$compressedmd5&compressedsize=$compressedsize&contenthash=$contenthash&file=$file";

		if ($deviceid == '')
			return $this->respond(-100, "invalid request. deviceid is empty: ".$replacement_uri);
		// 检查设备信息是否正确

		$device_model=D("Device");
		$field="bind_account,login_token";
		$db_device=$device_model->get_info_by_uuid($deviceid,$field);
		//print_r($db_device);
		if (!$db_device)
			return $this->respond(-101, "device $deviceid is not found:".$replacement_uri);
		if ($db_device["bind_account"] == 0)
			return $this->respond(-102, "device $deviceid was not bind to an account.".$replacement_uri);
		// 上传存档时，可能设备的logintoken已经改变，所以这里不再检查logintoken的正确性
		//if ($db_device[0]["login_token"] == '' || $db_device[0]["login_token"] != $logintoken)
		//	return $this->respond(-103, "login token of device $deviceid doesn't match.".$replacement_uri);
		$accountid = $db_device["bind_account"];
		// 检查存档是否存在，且状态正常
		$db = M('game_save');
		$condition = array();
		$condition['game_id'] = $gameid;
		$condition['account_id'] = $accountid;
		$condition['id'] = $saveid;
		$condition['gs_id'] = $gsid;
		$condition['serial_id'] = $serialid;
		$condition['uploadtoken'] = $uploadtoken;
		$db_save = $db->field('derived_from,compressed_md5,compressed_size,create_time,upload_time,gs_report_time,total_play_time')->where($condition)->select();
		if (!$db_save && count($db_save) == 0)
			return $this->respond(-104, "fail to find save $saveid: ".$replacement_uri);

		if ($db_save[0]['compressed_md5'] != '') {
			// 如果md5一致，则说明已经上传过了，无需再上传
			if ($db_save[0]['compressed_md5'] == $compressedmd5 && $db_save[0]['compressed_size'] == $compressedsize)
				return $this->respond(0, "success. already uploaded.".$replacement_uri);
			else
				return $this->respond(-105, "this save $saveid has already been uploaded another md5 ".$db_save[0]['compressed_md5']." size ".$db_save[0]['compressed_size']." at time ".$db_save[0]['upload_time']." new md5 is $compressedmd5 size is $compressedsize.".$replacement_uri);
		}

		// 只有file=1才需要上传文件，否则只是通知web上传成功
		if ($file == 1) {
			// http://www.php.net/manual/en/features.file-upload.php

			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if (!isset($_FILES['upload']['error']) || is_array($_FILES['upload']['error']))
				return $this->respond(-200, "Invalid _FILES[upload][error]): ".$replacement_uri);

			// Check $_FILES['upload']['error'] value.
			switch ($_FILES['upload']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					return $this->respond(-201, "UPLOAD_ERR_NO_FILE.".$replacement_uri);
				case UPLOAD_ERR_INI_SIZE:
					return $this->respond(-202, "Exceeded filesize limit.UPLOAD_ERR_INI_SIZE.".$replacement_uri);
				case UPLOAD_ERR_FORM_SIZE:
					return $this->respond(-202, "Exceeded filesize limit.UPLOAD_ERR_FORM_SIZE.".$replacement_uri);
				default:
					return $this->respond(-203, "Unknown error:".$_FILES['upload']['error']." ".$replacement_uri);
			}

			$MAX_UPLOAD_FILE_SIZE = C("MAX_UPLOAD_FILE_SIZE");
			// You should also check filesize here.
			if ($_FILES['upload']['size'] > $MAX_UPLOAD_FILE_SIZE)
				return $this->respond(-204, "Exceeded filesize limit:".$MAX_UPLOAD_FILE_SIZE." ".$replacement_uri);
			if ($_FILES['upload']['size'] != $compressedsize)
				return $this->respond(-205, "file size ".$_FILES['upload']['size']." does not equal to $compressedsize.".$replacement_uri);
				
			// validate md5
			$file_md5 = strtolower(md5_file($_FILES['upload']['tmp_name']));
			if ($file_md5 != $compressedmd5)
				return $this->respond(-206, "file md5 $file_md5 does not equal to $compressedmd5.".$replacement_uri);

			// 不使用上传的原始文件名。
			// 存档文件命名：<存档ID>_<存档MD5，小写>.save, 如123_4387904830a4245a8ab767e5937d722c.save
			$compressedname = $saveid."_".$compressedmd5.".save";
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");
				
			$final_dir = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($accountid/1000000000).DIRECTORY_SEPARATOR.intval($accountid/1000000).DIRECTORY_SEPARATOR.intval($accountid/1000).DIRECTORY_SEPARATOR.$accountid.DIRECTORY_SEPARATOR.$gameid.DIRECTORY_SEPARATOR.$serialid.DIRECTORY_SEPARATOR;
			// Read and write for owner, read for everybody else
			if (!file_exists($final_dir) && !mkdir($final_dir, 0744, true))
				return $this->respond(-207, "Failed create $final_dir.".$replacement_uri);

			// 存档目录格式：每层1000个目录，帐号ID，游戏ID，存档序列ID，存档文件。
			$final_path = $final_dir.$compressedname;
			$tmp_path = $final_path.".tmp";
			if (!move_uploaded_file($_FILES['upload']['tmp_name'], $tmp_path))
				return $this->respond(-207, "Failed to move uploaded file from ".$_FILES['upload']['tmp_name']." to $tmp_path.".$replacement_uri);
		}

		// 更新存档记录，已经上传完成
		$db = M('game_save');
		$condition = array();
		$condition['id'] = $saveid;
		$update = array();
		$update['upload_time'] = time();
		$update['compressed_size'] = $compressedsize;
		$update['compressed_md5'] = $compressedmd5;
		// 计算此次游戏的时间，稍后可能需要加上父存档的时间. 最少也要算成1秒钟，因为0是尚未计算total_play_time的标志
		if ($db_save[0]['gs_report_time']-$db_save[0]['create_time'] < 0 || $db_save[0]['gs_report_time']-$db_save[0]['create_time'] > 86400)
			$update['total_play_time'] = max(1, $update['upload_time'] - $db_save[0]['create_time']);
		else 
			$update['total_play_time'] = max(1, $db_save[0]['gs_report_time'] - $db_save[0]['create_time']);
		
		// 如果在创建时已经记录了total_play_time，则直接加上即可
		if ($db_save[0]['total_play_time'] > 0) {
			$update['total_play_time'] += $db_save[0]['total_play_time'];
		}
		else if ($db_save[0]['derived_from'] > 0) {
			// 如果创建时没记录，那么看看如果有父存档，就把父存档的total_play_time叠加上来。
			$cond = array();
			$cond['id'] = $db_save[0]['derived_from'];
			$parent_save = $db->where($cond)->find();
			if ($parent_save && $parent_save['total_play_time'] > 0) {
				// 找到了父存档，则把父存档的total_play_time叠加上来，前提是父存档的total_play_time > 0
				$update['total_play_time'] += $parent_save['total_play_time'];
			}
			else {
				// 没有找到父存档哦，很奇怪，那么先不记录total_play_time了，等待之后修复
				$update['total_play_time'] = 0;
			}
		}
		
		$db_save = $db->where($condition)->save($update);
		if (!$db_save && count($db_save) == 0)
			return $this->respond(-106, "fail to find save $saveid.".$replacement_uri);

		// 只有file=1才需要上传文件，否则只是通知web上传成功
		if ($file == 1) {
			// 数据库更新完毕，把临时文件名改为最终文件名
			if(file_exists($final_path)) {
				unlink($final_path);
				$ok = rename($tmp_path, $final_path);
			} else {
				$ok = rename($tmp_path, $final_path);
			}
				
			if (!$ok)
				return $this->respond(-208, "Failed to move uploaded file from ".$tmp_path." to $final_path.".$replacement_uri);
		}

		return $this->respond(0, 'success.'.$replacement_uri);
	}
}
