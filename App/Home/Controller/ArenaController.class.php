<?php
namespace Home\Controller;
use Home\Controller\BaseController;
use Think\Cache;
use Think\Log;
//擂台操作类
class ArenaController extends BaseController {
	public function index(){
		echo "welcome to yunyouxi";
		exit;
	}

	public function clear()
	{
		$cache = S(array('type'=>'Gloudmemcached'));
		$result = $cache->clear();
		if($result === true)
		{
			echo "clear memcache ok\n";
			var_dump($cache->get("arena_info_queue_1"));
		}
		else{
			echo "clear memcache no\n";
			var_dump($cache->get("arena_info_queue_1"));
		}
	}

	public function get_memcache()
	{
		$key = I("key",'');
		$cache = S(array('type'=>'Gloudmemcached'));
		$result = $cache->get($key);
		var_dump($result);
		echo "memcache no";
		exit;
	}

	public function set_redis(){
		$redis = S(array('type'=>'Gloudredis'));
		var_Dump($redis->set('sunzheng','sunzheng'));
		var_dump($redis->get('sunzheng'));
		echo 'ok';
		exit;
	}

	//////////////////////////////////////////////////////////////////////
	//                        以下是客户端调用的接口
	//////////////////////////////////////////////////////////////////////
	/*
		查询擂台游戏列表。
	请求格式：形如http://localhost/api.php?m=Arena&a=arena_list&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	regionid是32位整数，表示一个区。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arenas":[{"game_id":"1","game_name":"\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84\u00ef\u00bc\u0161\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e7\u2030\u02c6"},{"game_id":"1020","game_name":"1941"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无法查询擂台信息
	非零值均表示失败。
	*/
	public function arena_game_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$arena_game_model=D("ArenaGame");
		$game_list=$arena_game_model->api_get_all_data();
		G('end');
		$e_time=G('begin','end').'s';
		if ($game_list === false)
			return $this->respond(-104, "未找到游戏信息，请重启程序。",$e_time);
		return $this->respond_ex(0, "success", "games", $game_list,$e_time);
	}
	/*
		查询擂台列表。
	请求格式：形如http://localhost/api.php?m=Arena&a=arena_list&deviceid=xxx&logintoken=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	regionid是32位整数，表示一个区。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arenas":[{"game_id":"1","game_name":"\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84\u00ef\u00bc\u0161\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e7\u2030\u02c6"},{"game_id":"1020","game_name":"1941"}]}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无法查询擂台信息
	非零值均表示失败。
	*/
	public function arena_list(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$game_id = I('gameid','');
		$arena_live_server = I('arena_live_server', 0);
		$clientip = get_client_ip();
		
		$account_id = 0;
		$db = M('arena');
		//go servers
		if ($arena_live_server != 1){
			$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
			if ($ret['ret'] != 0)
				return $this->respond($ret['ret'], $ret['msg']);
			$db_device = $ret['msg'];
			$account_id = $db_device['bind_account'];
		}
		//memcache
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_list_".$game_id;
		$db_arena = $cache->get($key);
		if($db_arena === false){
			$db = M('arena');
			$condition = array();
			if($game_id != '')
				$condition['ag.game_id'] = $game_id;
			$condition['a.status'] = array('neq','-1');   //过滤   前端不显示的数据
			$condition['ca.type'] = 0;
			$db_arena = $db->field("a.id,a.status,a.arena_type,a.arena_name,i.level_name,i.integral,i.integral_multiple,a.arena_pic,a.max_number,a.live_url,a.hd_live_url,a.fluent_live_url,a.bitrate,a.game_id,a.arena_pic,a.region_id,a.nettest_ip,a.nettest_port,r.name as region_name,a.open_time,IFNULL(c.coin,0) as coin,a.close_time,a.min_skill_level")
							->table('july_arena as a')
							->join("july_arena_integral as i on i.id = a.arena_integral_id")
							->join("july_arena_game as ag on ag.game_id = a.game_id")
							->join("july_chargepoint_arena as ca on ca.arena_id = a.id")
							->join("july_chargepoint as c on c.id = ca.chargepoint_id")
							->join("july_region as r on r.id=a.region_id")
							->where($condition)->select();
			if ($db_arena === false)
				return $this->respond(-104, "未找到擂台信息，请退回游戏选择界面，重新进入。");
			$result = $cache->set($key,$db_arena,10);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$arenas = array();

		if (count($db_arena) > 0){
			$redis = S(array('type'=>'Gloudredis'));
			foreach($db_arena as $key=>$val){
				// 动视云公司的IP，则返回更多隐藏的擂台列表, min_skill_level>=0的都返回
				if ($arena_live_server != 1 && $db_arena[$key]['min_skill_level'] > 0 && $clientip != '124.207.55.164')
					continue;

				$db_arena[$key]['hot'] = "0";
				// 非禁用且非不显示的状态下，才查询更具体的擂台状态信息
				if($val['status'] != 0 && $val['status'] != -1){
					$condition = array();
					$condition['arena_id'] = $val['id'];
					$battle_gs_time = M("arena_battle")->field("gs_time")->where($condition)->order("id desc")->find();
					if($battle_gs_time === false)
						return $this->respond(-105, "无法连接擂台服务器，请稍后再试。");
					elseif (count($battle_gs_time) <= 0)
					$battle_gs_time['gs_time'] = 0; // 没找到battle记录，GS还没开启过
						
					$db_arena[$key]['status'] = $this->check_gs_status($val['open_time'], $val['close_time'], $battle_gs_time['gs_time']);
					if($db_arena[$key]['status'] == 1){
						//获取最新的  hot_nums
						$hot = $redis->zRange("active_user_arenaid_".$val['id'],'-1','-1');
						$hot = unserialize($hot[0]);
						$db_arena[$key]['hot'] = "".intval($hot[0]); // 字符串形式的数字，如果是null会变成"0"
						//擂台已满
						if($val['max_number'] != "0" && $db_arena[$key]['hot'] >= $val['max_number'])
							$db_arena[$key]['status'] = 5;
					}
				}
				if($account_id > 0){
						//获取用户的上次测试结果
						$condition = array();
						$condition['n.stsip'] = $val['nettest_ip'];
						$condition['n.stsport'] = $val['nettest_port'];
						$condition['n.account_ip'] = $clientip;
						$condition['n.device_uuid'] = $deviceid;
						$nettest_model = M('nettest');
						$nettest = $nettest_model->table('july_nettest as n')->join('july_region as r on n.region_id = r.id','left')->field('n.account_ip,r.name as region_name,n.stsip,n.stsport,n.ping,n.kbps,n.create_time')->where($condition)->order('n.id desc')->find();
						if($nettest === false)
							return $this->respond(-106,'查询测速记录失败，请稍后再试。');
						$db_arena[$key]['nettest'] = $nettest == null?(object)array():$nettest;
					}
				array_push($arenas, $db_arena[$key]);
			}
		}
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond_ex(0, "success", "arenas", $arenas,$e_time);
	}

	// 返回GS的状态，不包含满员的状态
	function check_gs_status($open_time, $close_time, $gs_time) {
		$now_time = time();
		// 提前两分钟开启GS，延后五分钟关闭GS
		if (($open_time  != '00:00:00' && $open_time  > Date("H:i:s",$now_time-120)) ||
				($close_time != '00:00:00' && $close_time < Date("H:i:s",$now_time-300)))
			return 4; // 已关闭状态

		// 关闭时间之后的五分钟内，擂台处于即将关闭状态
		if ($close_time != '00:00:00' && $close_time > Date("H:i:s",$now_time-300) && $close_time < Date("H:i:s",$now_time))
			return 3; // 即将关闭状态

		if((time() - $gs_time) > 30)
			return 2; // 擂台暂时不可用，因为GS不活跃
		return 1; // 开启状态
	}

	// 判断GS的开关时机
	function check_gs_open( $open_time, $close_time){
		$now_time = time();
		// 提前两分钟开启GS，延后五分钟关闭GS
		if (($open_time  != '00:00:00' && $open_time  > Date("H:i:s",$now_time-120)) ||
				($close_time != '00:00:00' && $close_time < Date("H:i:s",$now_time-300)))
			return false;
		return true;
	}

	//获取个人信息
	public function arena_account(){
		G('begin');
		$deviceid = I('deviceid','');
		$arenaid = I('arenaid',0);
		$gameid = I('gameid',0);
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];

		//获取用户的信息,没有就创建一条
		$arena_account = $this->found_arena_account($account_id,$gameid,$arenaid);
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond_ex(0, "success", "arena_account", $arena_account['msg'],$e_time);
	}
	/*
		查询擂台信息。
	请求格式：形如形如ttp://localhost/api.php?m=Arena&a=arena_info&deviceid=xxx&logintoken=xxx&arenaid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arena":{"arena":{"game_id":"1","gs_ip":"10.0.4.120","gs_port":"9000","live_url":"rtmp:\/\/10.0.4.120\/live\/1001000"},"account":{"skill_level":"1","total_battles":"0","total_wins":"0","max_combo_num":"0","first_battle_time":"0","last_battle_time":"0"},"battle":{"id":"382","player1":"100015","player2":"0","name1":"\u00e7\u201d\u00a8\u00e6\u02c6\u00b71410687398620","name2":"","score1":"0","score2":"0","status1":"0","status2":"0","phase":"0","create_time":"1415163198"},"queue":[{"account_id":"100011","account_name":"a924669518"}],"rank":[{"rank":"1","account_id":"100000","account_name":"","update_time":"0"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:没找到该擂台
	-105:无法查询用户在该游戏的信息
	-106:无法查询擂台排队信息
	-107:无法查询擂台排名信息
	-108:查找不到战斗的信息
	-109:创建arena_account失败
	-110:查找连斩信息错误
	非零值均表示失败。
	*/
	public function arena_info(){
		G('begin');
		$deviceid = I('deviceid','');
		$arenaid = I('arenaid',0);
		$logintoken = I('logintoken','');
		$arena_live_server = I('arena_live_server', 0);
		$usernum = I('usernum',0);

		// 给arenalive请求这个接口开个后门，相当于可以匿名访问
		$account_id = 0;
		if ($arena_live_server == 0) {
			$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
			if ($ret['ret'] != 0)
				return $this->respond($ret['ret'], $ret['msg']);
			$db_device = $ret['msg'];
			$account_id = $db_device['bind_account'];
		}
		$arena_model=D("Arena");
		$arena = array();
		$arena['arena'] = array();
		$arena['battle'] = array();
		$arena['queue'] = array();
		$arena['rank'] = array();

		$memcache_test = "";
		//判断擂台的状态,是否开启
		$db_arena = $arena_model->get_info_by_id($arenaid);
		if ($db_arena === false)
			return $this->respond(-104, "无法查询该擂台信息，ID：$arenaid");
		if(count($db_arena) <= 0)
			return $this->respond(-1041, "未找到指定擂台，ID：$arenaid");
		if($db_arena['status'] == 0 || $db_arena['status'] == -1)
			return $this->respond(-105,"该擂台已经关闭");

		// 检查擂台状态是否有其他异常
		$db_arena['status'] = $this->check_gs_status($db_arena['open_time'], $db_arena['close_time'], time());
		$arena['arena'] = $db_arena;
		if ($account_id > 0) {
			//获取用户的信息,没有就创建一条
			$db_account = $this->found_arena_account($account_id,$db_arena['game_id'],$arenaid);
			if(count($db_account['msg']) > 0)
				$arena['account'] = $db_account['msg'];
		}

		//擂台战斗信息
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_info_battle_".$arenaid;
		$db_battle = $cache->get($key);
		if($db_battle === false){
			$db_battle = $this->get_battle_info($arenaid);
			if($db_battle === false)
				return $this->respond(-108, "擂台状态异常,未能获得状态信息，请退出重试。");
			$resule = $cache->set($key,$db_battle,10);
			if($resule == false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}else{
			$memcache_test = 'memcache_battle';
		}
		if (count($db_battle) > 0)
			$arena['battle'] = $db_battle;

		//获取擂台队列信息
		$key = "arena_info_queue_".$arenaid;
		$db_queue = $cache->get($key);
		if($db_queue === false){
			$db_queue = $this->get_queue_list($arenaid);
			if($db_queue['ret'] != 0)
				return $this->respond(-106, $db_queue['msg']);
			$resule = $cache->set($key,$db_queue['msg'],10);
			if($resule == false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
			$db_queue = $db_queue['msg'];
		}
		if (count($db_queue) > 0)
			$arena['queue'] = $db_queue;

		//获得擂台点赞信息,点赞数据来源于redis
		$redis = S(array('type'=>'Gloudredis'));
		$arena['battle']['player'][0]['support'] = (string)$redis->hlen("support_".$db_battle['id']."_1");
		$arena['battle']['player'][1]['support'] = (string)$redis->hlen("support_".$db_battle['id']."_2");

		//获得擂台上连斩信息
		$key = "arena_info_cut_".$db_battle['id'];
		$arena_cut = $cache->get($key);
		if($arena_cut === false){
			$ret = $this->get_even_cut($arena['battle']['player'][0]['account_id'],$arenaid);
			if($ret === false)
				return $this->respond(-113,"查询连胜信息异常，请退出重试。");
			$arena_cut['p1'] = $ret;
			$ret = $this->get_even_cut($arena['battle']['player'][1]['account_id'],$arenaid);
			if($ret === false)
				return $this->respond(-110,"查询连胜信息异常，请退出重试。");
			$arena_cut['p2'] = $ret;
			$resule = $cache->set($key,$arena_cut,10);
			if($resule == false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$arena['battle']['player'][0]['cur_combo_num'] = (string)$arena_cut['p1'];
		$arena['battle']['player'][1]['cur_combo_num'] = (string)$arena_cut['p2'];
		//获取上期擂主的信息
		$key = "arena_info_rank_".$arenaid;
		$db_rank = $cache->get($key);
		if($db_rank === false){
			$db = M('arena_rank');
			$condition = array();
			$condition['ar.arena_id'] = $arenaid;
			$condition['_string'] = "ar.account_id = a.id";
			$db_rank = $db->table('july_arena_rank as ar,july_account as a')->field("ar.date,ar.over_nums,ar.nums,ar.rank,ar.account_id,ar.account_name,ar.update_time,a.avatar")
			->where($condition)->order('ar.date desc, ar.rank')->limit(1)->select();
			if ($db_rank === false)
				return $this->respond(-107, "查询上期擂主信息异常，请退出重试。");
			$resule = $cache->set($key,$db_rank);
			if($resule == false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		if (count($db_rank) > 0)
			$arena['rank'] = $db_rank;

		//获取擂台倒计时
		$key = "countdown_battleid_".$db_battle['id'];
		$countdown = $cache->get($key);
		if($countdown !== false && $countdown !== null){
			$arena['countdown'] = $countdown;
		}
		//每次请求的时候记录到redis中,用于统计擂台热度
		if ($account_id == 0) {
			$this->redis_set_sorteset("active_user_arenaid_".$arenaid,$usernum);
			$redis->zRemRangeByScore("active_user_arenaid_".$arenaid,0,time()-120);
		}
		Log::write("arena_info_json.battle_player1:".json_encode($arena['battle']['player'][0]['account_id']).",battle_player2:".json_encode($arena['battle']['player'][1]['account_id'].",memcache:{$memcache_test}"), Log::INFO);
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0, "success", "arena", $arena,$e_time);
	}

	//redis 每次请求的人加入redis SorteSet
	function redis_set_sorteset($key,$value){
		$time = time();
		$redis = S(array('type'=>'Gloudredis'));
		$result = $redis->zadd($key, $time, serialize(array($value,$time)));
		if($result === false)
			return false;
	}


	/*
	获取上一场battle的信息
	请求格式：形如http://localhost/api.php?m=Arena&a=last_battle&deviceid=xxx&logintoken=xxx&arenaid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arena":{"arena":{"game_id":"1","gs_ip":"10.0.4.120","gs_port":"9000","live_url":"rtmp:\/\/10.0.4.120\/live\/1001000"},"account":{"skill_level":"1","total_battles":"0","total_wins":"0","max_combo_num":"0","first_battle_time":"0","last_battle_time":"0"},"battle":{"id":"382","player1":"100015","player2":"0","name1":"\u00e7\u201d\u00a8\u00e6\u02c6\u00b71410687398620","name2":"","score1":"0","score2":"0","status1":"0","status2":"0","phase":"0","create_time":"1415163198"},"queue":[{"account_id":"100011","account_name":"a924669518"}],"rank":[{"rank":"1","account_id":"100000","account_name":"","update_time":"0"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无法查找last_battle信息
	非零值均表示失败。
	*/
	public function last_battle(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$arena_id = I("arenaid",'');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$arena_battle_model=D("ArenaBattle");
		$field="id,player1,player2,name1,name2,status1,status2,score1,score2";
		$last_battle=$arena_battle_model->get_last_battle_info_by_arena($arena_id,$field);
		if($last_battle === false)
			return $this->respond(-104,"没有找到上场战斗信息，请退出重试。");
		if(count($last_battle) > 0)
			$battle = $last_battle;
		
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0,"success","last_battle",$battle,$e_time);
	}

	/*
		获取上一场battle的信息(GS  SERVER)  和 随机获奖用户
	*/
	public function last_battle_result(){
		G('begin');
		$arena_id = I("arenaid",'');
		$arena_live_server = I('arena_live_server', 0);

		if($arena_live_server != '1')
			return $this->respond(-101,'Parameter error');

		$battle = array();
		$battle['gifts'] = array();
		//上一场比赛战报
		$arena_battle_model=D("ArenaBattle");
		$field="id,player1,player2,name1,name2,status1,status2,score1,score2,is_gifts";
		$last_battle=$arena_battle_model->get_last_battle_info_by_arena($arena_id,$field);
		if($last_battle === false)
			return $this->respond(-104,"Play the fighting information is not found, please exit and try again.");
		if(count($last_battle) > 0){
			$Account_model = D('Account');
			$battle['id'] = $last_battle['id'];
			for($i = 1; $i <= 2; $i++){
				$avatar = $Account_model->get_info_by_id($last_battle['player'.$i],'avatar');
				$battle['battle'][] = array('player'=>$last_battle['player'.$i],
						'name'=>$last_battle['name'.$i],
						'status'=>$last_battle['status'.$i],
						'score'=>$last_battle['score'.$i],
						'avatar'=>$avatar['avatar']
				);
			}
		}

		//从点赞中获取随机获奖用户
		if( $last_battle['score1'] != $last_battle['score2'] && $last_battle['is_gifts'] == '0' ) {
			$support_model = M('arena_support');
			$condition = array();
			$condition['battle_id'] = $last_battle['id'];
			$condition['player_index'] = $last_battle['score1'] > $last_battle['score2'] ? "1" : "2";
			$support_list = $support_model->field("s.account_voters as accountid,a.nickname")->table('july_arena_support as s')->join("july_account as a on a.id=s.account_voters")->where($condition)->select();
			if($support_list === false)
				return $this->respond(-105,"Play Praise information is not found, please exit and try again");
			if( count($support_list) > 1){
				$keys = array_rand($support_list,2);
				foreach($keys as $val){
					$battle['gifts'][] = $support_list[$val];
				}
			}else{
				$battle['gifts'] = (object)array();
			}
		}
		unset($battle['is_gifts']); // 去除返回值中的多余字段
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0,"success","battle_result",$battle,$e_time);
	}
	/*
		总成绩前十排名。
	请求格式：形如http://localhost/api.php?m=Arena&a=get_arena_rank&deviceid=xxx&logintoken=xxx&game_id=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arena":{"arena":{"game_id":"1","gs_ip":"10.0.4.120","gs_port":"9000","live_url":"rtmp:\/\/10.0.4.120\/live\/1001000"},"account":{"skill_level":"1","total_battles":"0","total_wins":"0","max_combo_num":"0","first_battle_time":"0","last_battle_time":"0"},"battle":{"id":"382","player1":"100015","player2":"0","name1":"\u00e7\u201d\u00a8\u00e6\u02c6\u00b71410687398620","name2":"","score1":"0","score2":"0","status1":"0","status2":"0","phase":"0","create_time":"1415163198"},"queue":[{"account_id":"100011","account_name":"a924669518"}],"rank":[{"rank":"1","account_id":"100000","account_name":"","update_time":"0"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:没有找到battle_account信息
	非零值均表示失败。
	*/
	public function get_account_rank(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$game_id = I("game_id",'');
		$account = array();

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_account_rank_".$game_id;
		$account = $cache->get($key);
		if($account === false){
			$db_arena_account = M("arena_account");
			$condition = array();
			$condition['aa.game_id'] = $game_id;
			$condition['_string'] = "aa.account_id = a.id";
			$arena_account_rank = $db_arena_account->field("aa.account_id,aa.game_id,aa.skill_level,aa.total_battles,aa.total_wins,aa.max_combo_num,a.nickname as account_name")->table("july_account as a,july_arena_account as aa")->where($condition)->order("aa.total_wins DESC, aa.account_id")->limit(10)->select();
			if($arena_account_rank === false)
				return $this->respond(-104,"查找擂台排行榜失败，请稍后重试。");
			if(count($arena_account_rank) > 0 )
				$account['total_wins'] = $arena_account_rank;

			$arena_account_rank = $db_arena_account->field("aa.account_id,aa.game_id,aa.skill_level,aa.total_battles,aa.total_wins,aa.max_combo_num,a.nickname as account_name")->table("july_account as a,july_arena_account as aa")->where($condition)->order("aa.max_combo_num DESC, aa.account_id")->limit(10)->select();
			if($arena_account_rank === false)
				return $this->respond(-105,"查找擂台排行榜失败，请稍后重试。");
			if(count($arena_account_rank) > 0 )
				$account['max_combo_num'] = $arena_account_rank;
			
			$arena_account_rank = $db_arena_account->field("aa.account_id,aa.game_id,aa.skill_level,aa.integral,aa.integral_rank,a.nickname as account_name")->table("july_account as a,july_arena_account as aa")->where($condition)->order("aa.integral desc, aa.account_id")->limit(10)->select();
			if($arena_account_rank === false)
				return $this->respond(-106,"查找擂台积分排行榜失败，请稍后重试。");
			if(count($arena_account_rank) > 0 )
				$account['score'] = $arena_account_rank;
				
			$result = $cache->set($key,$account);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		G('end');
		$e_time=G('begin','end').'s';
			
		return $this->respond_ex(0,"success","account_total_win",$account,$e_time);
	}
	
	//获取积分排行榜
	public function get_integral_rank(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$game_id = I("game_id",'');
		$account = array();

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_integral_rank_".$game_id;
		$account = $cache->get($key);
		if($account === false){
			$db_arena_account = M("arena_account");
			$condition = array();
			$condition['aa.game_id'] = $game_id;
			$condition['_string'] = "aa.account_id = a.id";
			//获取排行榜
			$arena_integral_rank = $db_arena_account->field("aa.account_id,aa.game_id,aa.skill_level,aa.integral,aa.integral_rank,a.nickname as account_name")->table("july_account as a,july_arena_account as aa")->where($condition)->order("aa.integral desc, aa.account_id")->limit(1000)->select();
			if($arena_integral_rank === false)
				return $this->respond(-106,"查找擂台积分排行榜失败，请稍后重试。");
			if(count($arena_integral_rank) > 0 )
				$account['score'] = $arena_integral_rank;	
			
			$result = $cache->set($key,$account,86400);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		G('end');
		$e_time=G('begin','end').'s';
			
		return $this->respond_ex(0,"success","account_integral",$account,$e_time);
	}
	/*
		获取擂台规则
	请求格式：形如http://localhost/api.php?m=Arena&a=get_arena_rule&deviceid=xxx&logintoken=xxx&arena_id=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arena":{"arena":{"game_id":"1","gs_ip":"10.0.4.120","gs_port":"9000","live_url":"rtmp:\/\/10.0.4.120\/live\/1001000"},"account":{"skill_level":"1","total_battles":"0","total_wins":"0","max_combo_num":"0","first_battle_time":"0","last_battle_time":"0"},"battle":{"id":"382","player1":"100015","player2":"0","name1":"\u00e7\u201d\u00a8\u00e6\u02c6\u00b71410687398620","name2":"","score1":"0","score2":"0","status1":"0","status2":"0","phase":"0","create_time":"1415163198"},"queue":[{"account_id":"100011","account_name":"a924669518"}],"rank":[{"rank":"1","account_id":"100000","account_name":"","update_time":"0"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:没有找到battle_account信息
	非零值均表示失败。
	*/
	public function get_arena_rule()
	{
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$arena_id = I("arena_id",'');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_rule_".$arena_id;
		$arena_rule['arena_rule'] = $cache->get($key);
		if($arena_rule['arena_rule'] === false){
			$db_arena = M("arena");
			$condition = array();
			$condition['arena_id'] = $arena_id;
			$arena_rule = $db_arena->field("arena_rule")->where($condition)->find();
			if($arena_rule === false)
				return $this->respond(-104,"擂台规则读取失败，请稍后重试。");
			$result = $cache->set($key,$arena_rule['arena_rule']);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		if(is_null($arena_rule['arena_rule']) || $arena_rule['arena_rule'] == '0')
			$arena_rule['arena_rule'] = '';

		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond_ex(0,'success',"arena_rule",$arena_rule,$e_time);
	}

	//获取用户当天的胜利信息
	function get_today_win($arena_id, $account_id)
	{
		$time = strtotime("today");
		$nowtime = time();
		$db_battle = M("arena_battle");
		$player1_win_nums = $db_battle->where("player1=".$account_id." and arena_id = ".$arena_id." and ((score1 > score2)) and (player1 != 0) and (player2 != 0) and create_time >= ".$time." and create_time < ".$nowtime)->count();
		$player2_win_nums = $db_battle->where("player2=".$account_id." and arena_id = ".$arena_id." and ((score2 > score1)) and (player1 != 0) and (player2 != 0) and create_time >= ".$time." and create_time < ".$nowtime)->count();
		if($player1_win_nums === false || $player2_win_nums === false)
			return $this->return_ex(-1);
		$win_nums = (string)($player1_win_nums + $player2_win_nums);
		return $win_nums;
	}

	//查询连斩
	function get_even_cut( $accountId, $arenaId, $evenkill = 0)
	{
		$db_battle = M("arena_battle");
		$condition = array();
		$condition['arena_id'] = $arenaId;
		$condition['end_time'] = array('neq',0);
		$battle = $db_battle->field("player1,player2,score1,score2")->where($condition)->order('id desc')->limit(50)->select();
		if($battle === false)
			return false;
		if(count($battle) > 0 ){
			foreach($battle as $data){
				if($data['player1'] == $accountId && $data['player1'] != 0 && $data['score1'] > $data['score2']){
					$evenkill++;
					continue;
				}
				elseif($data['player2'] == $accountId && $data['player2'] != 0 && $data['score2'] > $data['score1']){
					$evenkill++;
					continue;
				}
				else{
					break;
				}
			}
		}
		else{
			$evenkill = 0;
		}

		return $evenkill;
	}

	/**
	 * 获取队列信息
	 * @param $arenaid 擂台id
	 * @return array
	 * @return -701 队列信息查找失败
	 */
	function get_queue_list($arenaid){
		G('begin');
		$db_queue = M('arena_queue');
		$condition = array();
		$condition['a.arena_id'] = $arenaid;
		$condition['_string'] = " a.account_id=b.id";
		$queue_list = $db_queue->table('july_arena_queue as a,july_account as b')->field('a.account_id,b.nickname as account_name,b.avatar')->where($condition)->select();
		if($queue_list === false)
			return $this->return_ex(-701, "队列信息查找失败，请退出重试。");
		if(count($queue_list) == 0)
			$queue_list = array();
		
		G('end');
		$e_time=G('begin','end').'s';
		return $this->return_ex(0,$queue_list,$e_time);
	}

	//获取战斗的信息
	function get_battle_info($arenaid){
		$arena_battle_model=D("ArenaBattle");
		$battle_info=$arena_battle_model->get_curr_battle_info_by_arena($arenaid);
		if($battle_info === false || $battle_info == null)
			return $battle_info = array('id'=>'0','game_id'=>'0','player1'=>'0','player2'=>'0','phase'=>'0','start_time'=>'0','end_time'=>'0','player'=>array(0=>array('account_id'=>'0','account_name'=>'','score'=>'0','status'=>'-1','avatar'=>'','total_wins'=>'0','max_combo_num'=>'0','rank'=>'0','today_combo_num'=>'0'),1=>array('account_id'=>'0','account_name'=>'','score'=>'0','status'=>'-1','avatar'=>'','total_wins'=>'0','max_combo_num'=>'0','rank'=>'0','today_combo_num'=>'0')));
		$battle_info['player'][] = $this->get_player_data($arenaid, $battle_info['id'], $battle_info['game_id'], 1);
		$battle_info['player'][] = $this->get_player_data($arenaid, $battle_info['id'], $battle_info['game_id'], 2);
		//清除多余的字段
		unset($battle_info['player1']);
		unset($battle_info['player2']);
		return $battle_info;
	}

	function get_player_data($arenaid, $battleid, $game_id, $player_index){
		$db_battle = M("arena_battle");
		$field = '';
		$condition = array();
		$condition['ab.id'] = $battleid;
		if($player_index == 1){
			$field .=" ab.player1 as account_id,ab.name1 as account_name,ab.score1 as score,ab.status1 as status,a.avatar,aa.total_wins,aa.max_combo_num,aa.rank";
			$condition['_string'] = " ab.player1=a.id and ab.player1=aa.account_id and aa.game_id={$game_id}";
		}else{
			$field .=" ab.player2 as account_id,ab.name2 as account_name,ab.score2 as score,ab.status2 as status,a.avatar,aa.total_wins,aa.max_combo_num,aa.rank";
			$condition['_string'] = " ab.player2=a.id and ab.player2=aa.account_id and aa.game_id={$game_id}";
		}
		$data = $db_battle->table("july_arena_battle as ab,july_account as a,july_arena_account as aa")->field($field)->where($condition)->find();

		if($data === false || $data === null)
			$data = array('account_id'=>'0','account_name'=>'','score'=>'0','status'=>'-1','avatar'=>'','total_wins'=>'0','max_combo_num'=>'0','rank'=>'0','today_combo_num'=>'0');
		//获取本期胜场
		$data['today_combo_num'] = $this->get_today_win($arenaid, $data['account_id']);

		return $data;
	}

	//创建arena_account信息
	function found_arena_account($account_id,$game_id,$arena_id){
		$account_model=D("Account");
		$arena_account_model=D("ArenaAccount");
		$account_info=$account_model->get_info_by_id($account_id);
		if($account_info === false)
			return $this->return_ex(-109,"未找到玩家信息，请退出重试。");
		$account=$arena_account_model->get_arena_account_by_account_game($account_id,$game_id);
		if($account===false){
			return $this->return_ex(-110,"创建玩家战绩信息失败，请退出重试。");
		}
		if($account['avatar'] == "")
			$account['avatar'] = $this->get_auto_avatar($account_id);
		if($account['nickname'] == "")
			$account['nickname'] = $this->get_auto_name($account_id);
		//计算本期的排名
		$account['today_combo_num'] = $this->get_today_win($arena_id,$account_id);
		$arena_account = $account;
		return $this->return_ex(0,$arena_account);
	}

	/*
		加入排队。
	请求格式：形如http://localhost/api.php?m=Arena&a=join_queue&deviceid=xxx&logintoken=xxx&arenaid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
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
	-104:没找到该擂台
	-105:无法查询擂台排队信息
	-106:无法查询擂台排名信息
	-107:加入排队失败
	-108:你已经在队列中
	-606 查找用户coin失败
	-303 查找chargepoint失败
	-304 用户G coin 不足
	非零值均表示失败。
	*/
	public function join_queue(){
		G('begin');
		$deviceid = I('deviceid','');
		$arenaid = I('arenaid',0);
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];

		//更新擂台记录表
		$data = array('account_id'=>$account_id,
				'arena_id'=>$arenaid,
				'leave_time'=>time(),
				'queue_nums'=>array('exp','queue_nums+1')
		);
		$this->history_account_arena_time($data);

		//查找擂台的信息
		//先读取memcache的信息
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_info_arena_".$arenaid;
		$db_arena = $cache->get($key);
		if($db_arena === false){
			$db = M('arena');
			$condition = array();
			$condition['id'] = $arenaid;
			$db_arena = $db->field("game_id,arena_integral_id,status,max_queue_num,open_time,close_time")->where($condition)->find();
		}
		if ($db_arena === false)
			return $this->respond(-104, "无法查询该擂台，报名失败。");
		if(count($db_arena) <= 0)
			return $this->respond(-1041, "未找到指定擂台，ID：$arenaid");
		if($db_arena['status'] == 0 || $db_arena['status'] == -1)
			return $this->respond(-105,"擂台已关闭，请移步其他擂台，或改日再战。");

		// 检查擂台状态是否有其他异常
		$db_arena['status'] = $this->check_gs_status($db_arena['open_time'], $db_arena['close_time'], time());
		if ($db_arena['status'] == 3)
			return $this->respond(-106,"擂台即将闭馆，停止接受报名。");
		elseif ($db_arena['status'] == 4)
		return $this->respond(-1061,"擂台开放时间已结束，请移步其他擂台，或改日再战。");
			
		//检查排队人数是否已经超限，如果没有则添加一条排队记录
		//读取memcache中的queue信息
		$key = "arena_info_queue_".$arenaid;
		$db_queue = $cache->get($key);
		if($db_queue === false){
			$db = M('arena_queue');
			$condition = array();
			$condition['arena_id'] = $arenaid;
			$db_queue = $db->where($condition)->select();
			if ($db_queue === false)
				return $this->respond(-107, "未找到队列信息，报名失败，请稍后重试。");
		}
		if (count($db_queue) >= $db_arena['max_queue_num']) {
			return $this->respond(-108, "挑战队列已满，下次出现空位时，记得要眼疾手快！或者，出门左转到其他擂台报名挑战！");
		}
		foreach($db_queue as $val){
			if($val['account_id'] == $account_id)
				return $this->respond(-109,"您已经在队列里了，抖擞精神准备上台吧。");
		}
			
		//检查battle中是否存在用户
		$key = "arena_info_battle_".$arenaid;
		$db_battle = $cache->get($key);
		if($db_battle === false){
			$db_battle = M("arena_battle");
			$condition = array();
			$condition['arena_id'] = $arenaid;
			$condition['_string'] = "(player1 = {$account_id} or player2 = {$account_id}) and end_time=0";
			$db_result = $db_battle->where($condition)->count();
			if($db_result >= 1)
				return $this->respond(-110,"出问题啦，已经上擂的您怎么还在报名呢。稍等一会再报名吧。");
		}
		else{
			if($db_battle['player'][0]['account_id'] == $account_id || $db_battle['player'][1]['account_id'] == $account_id)
				return  $this->respond(-111,"出问题啦，已经上擂的您怎么还在报名呢。稍等一会再报名吧。");
		}
		
		//检查用户的积分是否足够
		$ret = $this->cheack_integral($account_id,$db_arena['game_id'],$db_arena['arena_integral_id']);
		if($ret['ret'] != 0)
			return $this->respond($ret['ret'],$ret['msg']);
			
		//检查用户的G币是否足够
		$ret=$this->check_account_coin($account_id,$arenaid);
		if($ret['ret'] != 0)
			return $this->respond($ret['ret'],$ret['msg']);
			
		//添加信息
		$key = "arena_info_queue_".$arenaid;
		$lock_key = "lock_queue_".$arenaid;
		if($cache->add($lock_key,1) || C("MEMCACHED_STATUS") === false){

			$model = M();
			$model->startTrans();

			$db_queue = $cache->get($key);
			if($db_queue === false){
				//$db = M('arena_queue');
				$condition = array();
				$condition['arena_id'] = $arenaid;
				$db_queue = $model->table("july_arena_queue")->lock(true)->where($condition)->select();
				if ($db_queue === false){
					$cache->rm($lock_key);
					$model->rollback();
					return $this->respond(-112, "未找到队列信息，报名失败，请稍后重试。");
				}
			}
			if (count($db_queue) >= $db_arena['max_queue_num']) {
				$cache->rm($lock_key);
				$model->rollback();
				return $this->respond(-113, "挑战队列已满，下次出现空位时，记得要眼疾手快！或者，出门左转到其他擂台报名挑战！");
			}

			foreach($db_queue as $val){
				if($val['account_id'] == $account_id){
					$cache->rm($lock_key);
					$model->rollback();
					return $this->respond(-116,"您已经在队列里了，抖擞精神准备上台吧。");
				}
			}
			//检查battle中是否存在用户
			$key = "arena_info_battle_".$arenaid;
			$db_battle = $cache->get($key);
			if($db_battle === false){
				//$db_battle = M("arena_battle");
				$condition = array();
				$condition['arena_id'] = $arenaid;
				$condition['_string'] = "(player1 = {$account_id} or player2 = {$account_id}) and end_time=0";
				$db_battle = $model->table("july_arena_battle")->lock(true)->where($condition)->count();
				if($db_battle >= 1){
					$cache->rm($lock_key);
					$model->rollback();
					return $this->respond(-117,"出问题啦，已经上擂的您怎么还在报名呢。稍等一会再报名吧。");
				}
			}
			else{
				if($db_battle['player'][0]['account_id'] == $account_id || $db_battle['player'][1]['account_id'] == $account_id){
					$cache->rm($lock_key);
					$model->rollback();
					return  $this->respond(-118,"出问题啦，已经上擂的您怎么还在报名呢。稍等一会再报名吧。");
				}
			}
			
			//  查询用名在其他擂台报名中
			$condition = array('account_id'=>$account_id);
		 	$old_arena_id = $model->table('july_arena_queue')->field('arena_id')->where($condition)->find();
		 	if( $old_arena_id === false ){
		 		$cache->rm($lock_key);
				$model->rollback();
		 		return $this->respond(-119, '报名失败，请稍后重试。');
		 	}
		 	if( count($old_arena_id) > 0 ){
		 		// 如果存在其他擂台,就清除该用户的排队
		 		$condition['arena_id'] = $old_arena_id['arena_id'];
		 		$ret = $model->table('july_arena_queue')->where($condition)->delete();
		 		if($ret === false){
		 			$cache->rm($lock_key);
					$model->rollback();
			 		return $this->respond(-121, '报名失败，请稍后重试。');
		 		}
		 	}

			//添加到队列表中
			$update = array();
			$update['account_id'] = $account_id;
			$update['arena_id'] = $arenaid;
			$update['enqueue_time'] = time();
			$update['active_time'] = time();
			$db_ret = $model->table("july_arena_queue")->add($update);
			if ($db_ret === false){
				$model->rollback();
				$cache->rm($lock_key);
				return $this->respond(-114, "报名失败，请稍后重试。");
			}
			//只有在memcache开发的时候才会set
			if(C("MEMCACHED_STATUS") === true){
				$queue_date = $this->get_queue_list($arenaid);
				if($queue_date['ret'] != 0) {
					$model->rollback();
					$cache->rm($lock_key);
					return $this->respond(-115,$queue_date['msg']);
				}
				$key = "arena_info_queue_".$arenaid;
				$result = $cache->set($key,$queue_date['msg']);
				if($result === false)
					$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
					
				if( count($old_arena_id) > 0 ){
					// 如果该用户在其他擂台排队中,更新该擂台的排队信息
					$old_queue_date = $this->get_queue_list($old_arena_id['arena_id']);
					if($old_queue_date['ret'] != 0) {
						$model->rollback();
						$cache->rm($lock_key);
						return $this->respond(-115,$old_queue_date['msg']);
					}
					$key = "arena_info_queue_".$old_arena_id['arena_id'];
					$result = $cache->set($key,$old_queue_date['msg']);
					if($result === false)
						$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
				}
			}

			$cache->rm($lock_key);
			$model->commit();
			Log::write("Trend the accountid:$account_id join_queue to arenaid:$arenaid", Log::INFO);
		}
		else{
			return $this->respond(-120,"报名失败，请稍后重试。");
		}

		//推送,通知客户端更新排队列表
		G('end');
		$e_time=G('begin','end').'s';
		return $this->respond(0, "success",$e_time);
	}

	/**
	 * 判断用户的积分是否满足擂台门槛
	 * 
	*/
	function cheack_integral($account_id, $game_id, $arena_integral_id){
		$arena_integral_model = M('arena_integral');
		$access_integral = $arena_integral_model->field('integral')->where('id='.$arena_integral_id)->find();
		if($access_integral === false)
			return $this->return_ex(-1,'无法查找擂台积分，请稍后重试。');
		if($access_integral['integral'] != 0){
			$memcache_key='arena_account_info_by_account_id_game_id_'.$account_id.$game_id;
			$info=S($memcache_key);
			if( $info == false){
				$arena_account_model = D("ArenaAccount");
				$arena_account = $arena_account_model->get_info_by_game_account($account_id, $game_id);
				if($arena_account['integral'] < $access_integral)
					return $this->return_ex(-1,'您的积分不够，报名失败。');
			}
		}
		return $this->return_ex(0,'success');
	}
	
	
	//判断用户的云贝是否足够
	/**
	 * 判断用户的云贝是否足够
	 * @param string account_id 用户id
	 * @param string arena_id 擂台id
	 * @return -606 查找用户coin失败
	 * @return -303 查找chargepoint失败
	 * @return -1 用户G coin 不足
	 */
	function check_account_coin($account_id,$arena_id){
		$db_account = M("account");
		$condition = array();
		$condition['id'] = $account_id;
		$db_account = $db_account->field("gift_coin_num,bean,gold")->where($condition)->find();
		if(!$db_account)
			return $this->return_ex(-606,"无法查询账户余额，请稍后重试。");
		//查找对应的擂台计费点需要的G币
		$db = M('chargepoint');
		$condition = array();
		$condition['cpa.arena_id'] = $arena_id;
		$condition['cp.status'] = 1;
		$condition['cpa.type'] = 0;
		$condition['_string'] = "cp.id = cpa.chargepoint_id";
		$cp = $db->table('july_chargepoint cp, july_chargepoint_arena cpa')->field('cp.coin,cp.bean,cp.gold,cp.id')->where($condition)->find();
		if (!$cp)
			return $this->return_ex(-303, "报名失败，请稍后重试。");
		
		// 确认bean,coin,gold中至少有一个是有效值（>=0），且该账户付得起
		$bean_affordable = ($cp['bean'] >= 0 && $db_account['bean'] >= $cp['bean']);
		$coin_affordable = ($cp['coin'] >= 0 && $db_account['gift_coin_num'] >= $cp['coin']);
		$gold_affordable = ($cp['gold'] >= 0 && $db_account['gold'] >= $cp['gold']);
		if (!$bean_affordable && !$coin_affordable && !$gold_affordable) {
			$money = "";
			$money .= ($cp['bean'] > 0 && $db_account['bean'] < $cp['bean']) ? "云豆" : " ";
			$money .= ($cp['coin'] > 0 && $db_account['gift_coin_num'] < $cp['coin']) ? "云贝" : " ";
			$money .= ($cp['gold'] > 0 && $db_account['gold'] < $cp['gold']) ? "G币" : " ";
			return $this->return_ex(-1, "当前账户余额不足: ".$money." 。去格来云游戏签个到，或者参加活动领点奖金再来吧！");
		}
		return $this->return_ex(0,"success");
	}


	/*
		退出排队。
	请求格式：形如http://localhost/api.php?m=Arena&a=leave_queue&deviceid=xxx&logintoken=xxx&arenaid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
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
	-104:没找到该擂台
	-105:无法查询擂台排队信息
	-106:无法查询擂台排名信息
	-107:加入排队失败
	非零值均表示失败。
	*/
	public function leave_queue(){
		G('begin');
		$deviceid = I('deviceid','');
		$arenaid = I('arenaid',0);
		$logintoken = I('logintoken','');

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];

		//处理缓存数据
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_info_queue_".$arenaid;
		$lock_key = "lock_queue_".$arenaid;
		if($cache->add($lock_key,1) || C("MEMCACHED_STATUS") === false){
			//删除该帐号的排队记录
			//处理数据库数据
			$db_queue = M("arena_queue");
			$condition = array();
			$condition['arena_id'] = $arenaid;
			$condition['account_id'] = $account_id;
			$result = $db_queue->where($condition)->delete();
			if ($result === false){
				$cache->rm($lock_key);
				return $this->respond(-105, "退出比赛队列失败，请稍后重试。");
			}

			//处理memcache中的缓存
			if(C("MEMCACHED_STATUS") === true){
				$queue_date = $this->get_queue_list($arenaid);
				if($queue_date['ret'] != 0) {
					$cache->rm($lock_key);
					return $this->respond(-115,$queue_date['msg']);
				}
				$result = $cache->set($key,$queue_date['msg']);
				if($result === false)
					$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
			}
			$cache->rm($lock_key);
			Log::write("Trend the accountid:$account_id leave_queue to arenaid:$arenaid", Log::INFO);
		}else{
			return $this->respond(-106, "退出比赛队列失败，请稍后重试。");
		}
		// TODO:推送,通知客户端更新排队列表
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond(0, "success",$e_time);
	}

	/*
		点赞
	请求格式：形如http://localhost/api.php?m=Arena&a=set_support&deviceid=xxx&logintoken=xxx&arenaid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success","arena":{"queue":[{"queue_pos":"1","account_id":"12","nickname":null}],"rank":[{"rank":"1","account_id":"100000","nickname":"test","update_time":"0"}]}}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:无效请求
	-101:未找到设备ID
	-102:设备尚未绑定到某个帐号
	-103:login token不正确
	-104:无效参数
	非零值均表示失败。
	*/
	public function set_support(){
		G('begin');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$battleid = I('battleid','');
		$arenaid = I('arenaid','');
		$type = I('type',0);        //点赞的类型  0 为点赞   1 为下注
		$player_index = I('player_index','');     //支持1p或者2p
		$arena_live_server = I('arena_live_server', 0);

		if($player_index == '')
			return $this->respond(-104,"操作失败，参数无效。");

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];

		//判断arenaid
		$condition = array();
		if($arenaid != '' && $battleid == ''){
			$arena_battle_model = M('arena_battle');
			$condition = array();
			$condition['arena_id'] = $arenaid;
			$battle = $arena_battle_model->field('id')->where($condition)->order('id desc')->find();
			if($battle === false)
				return $this->respond(-109,'The failure to find battle_id');
			$battleid = $battle['id'];
		}
		//判断battle的状态
		$db_battle = M("arena_battle");
		$condition = array();
		$condition['id'] = $battleid;
		$battle = $db_battle->field("name1,name2,phase,end_time")->where($condition)->find();
		Log::write("set_support battle:".json_encode($battle), Log::INFO);
		if(!$battle)
			return $this->respond(-105,"操作失败，擂场信息错误。");
		if($battle['phase'] != 1)
			return $this->respond(-109,"比赛还未开始，请等待比赛开始后点赞。");
		if($battle['end_time'] != 0)
			return $this->respond(-106,"操作失败，比赛已结束。");

		$add = array();
		$add['battle_id'] = $battleid;
		$add['account_voters'] = $account_id;
		$add['player_index'] = $player_index;
		$add['create_time'] = time();

		$support_date = false;
		$redis = S(array('type'=>'Gloudredis'));
		if( $type == 0){
			$name = "support_".$battleid."_".$player_index;
			$key = "account_".$account_id;
			$support_date_1 = $redis->hget("support_".$battleid."_1",null,true);
			$support_date_2 = $redis->hget("support_".$battleid."_2",null,true);
		}else{
			$name = "bet_".$battleid."_".$player_index;
			$key = "account_".$account_id;
			$support_date_1 = $redis->hget("bet_".$battleid."_1",null,true);
			$support_date_2 = $redis->hget("bet_".$battleid."_2",null,true);
		}
		//  合并  support_1 和 support_2 
		if($support_date_1 != false && $support_date_2 != false)
			$support_date = array_merge($support_date_1,$support_date_2);
		else if($support_date_1 != false && $support_date_2 == false)
			$support_date = $support_date_1;
		else if($support_date_1 == false && $support_date_2 != false)
			$support_date = $support_date_2;

		if($support_date !== false){
			//判断之前点过赞没有..
			$support_nums = 0;
			foreach($support_date as $k=>$val){
				if($val['account_voters'] == $account_id){
					$support_nums++;
					break;
				}
			}
			if($support_nums > 0)
				return $this->respond(-107,"你已经支持过了，一局只有一次机会，不能贪心哦！");
		}
		//redis中不存在hash table,创建一个hash table
		$result = $redis->hset($name,$key,$add);
		if(!$result){
			return $this->respond(-108,"操作失败，发生未知错误。");
		}

		$nickname = $player_index == '1' ? $battle['name1'] : $battle['name2'];
		G('end');
		$e_time=G('begin','end').'s';
		
		if( $type == 0 ){
			$msg = "谢谢你，成功支持P{$player_index}玩家:{$nickname}";	
		}else{
			$msg = "谢谢你，成功支持P{$player_index}玩家:{$nickname}";
		}
		return $this->respond(0,$msg,$e_time);
	}

	/*
		alias 验证用户身份
	*/
	public function arena_login(){
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$arena_live_server = I('arena_live_server', 0);
		if($arena_live_server != '1')
			return $this->respond(-104,"Parameter error");

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		return $this->respond(0,"success");
	}

	/*
		进入擂台
	*/
	public function enter_arena(){
		G('begini');
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$arena_id = I('arenaid',0);

		if($arena_id == 0)
			return false;
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];

		$info = array();
		$info['account_id'] = $db_device['bind_account'];
		$info['arena_id'] = $arena_id;
		$info['create_time'] = time();
		$info['leave_time'] = time();
		$this->history_account_arena_time($info,"add");
		G('end');
		$e_time=G('begin','end').'s';

		return $this->respond(0,"success",$e_time);
	}

	/*
		退出擂台
	*/
	public function exit_arena(){
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$arena_id = I('arenaid',0);

		if($arena_id == 0)
			return false;
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];

		$info = array();
		$info['account_id'] = $db_device['bind_account'];
		$info['arena_id'] = $arena_id;
		$info['leave_time'] = time();
		$this->history_account_arena_time($update);

		return true;
	}
	//////////////////////////////////////////////////////////////////////
	//                        以下是GS调用的接口
	//////////////////////////////////////////////////////////////////////


	/*
		GS的心跳。
	请求格式：形如http://localhost/api.php?m=Arena&a=gs_hearbeat&gsid=xxx&phase=xxx&battleid=xxx&status1=xxx&status2=xxx&$player1=xxx&$player2=xxx
	gsid是32位整数，表示一个GS。
	battleid是32位整数，表示一场战斗。
	phase是32位整数，表示当前擂台状态，0表示等待玩家，1表示游戏中。
	（可选）status1和status2，如果没有参数说明对应的玩家还没上线，如果有值则：0表示玩家在线，2表示玩家离开，5表示断线
	返回格式：
	{"ret":0,"msg":"success"}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	1 : battleid = -1
	-100:无效请求,没有找到这个擂台
	-101:结束异常战斗失败
	-102:创建新的battle失败
	-103:arena没有重置set_reboot_time

	2: GS
	-201:找不到battle,通知GSD重启GS
	-202:battle的phase不正常,通知GSD重启GS
	-203:GS上的用户和数据库中分配的用户对应不上

	3:正常流程
	-301:创建新的battle失败
	-302:准备人数齐全
	-303:查找擂台对应的计费点G币失败
	-304:用户G币不足
	-305:用户没有要准备的比赛

	6:SQL ERROR
	-601:SQL ERROR 查找battle错误
	-602:SQL ERROR 查找队列错误
	-603:SQL ERROR 查找队列准备人数错误
	-604:SQL ERROR 队列状态更新错误
	-605:SQL ERROR 队列状态更新错误
	-606:SQL ERROR 查找用户COIN错误
	-607:SQL ERROR 队列状态更新错误
	-608:SQL ERROR 查找队列准备用户失败
	-609:SQL ERROR battle信息更新失败
	-610:SQL ERROR 清楚队列中无效用户失败
	-611:SQL ERROR 用户队列状态更新错误
	非零值均表示失败。
	*/
	public function gs_heartbeat(){
		$gsid = I('gsid',0);
		$gs_pid = I('gs_pid','');
		$battleid = I('battleid',-1);
		$phase = I('phase','');
		$player1 = I('player1',0);
		$player2 = I('player2',0);
		$status1 = I('status1',-1);
		$status2 = I('status2',-1);
		$gs_ip = get_client_ip();
		$push = array();
			
		$db_battle = M('arena_battle');
		$db_arena = M('arena');
		$condition = array();
		$condition['gs_id'] = $gsid;
		$arena = $db_arena->field("a.id,a.open_time,a.close_time,a.game_id,a.set_reboot_time,ab.id as battleid")->table("july_arena as a")->join("july_arena_battle as ab on a.id = ab.arena_id")->where($condition)->order("ab.id desc")->find();
		if(!$arena)
			return $this->respond_ex(-100,"GS could not find the arena,gs:$gsid",'battle',array('battle_id'=>$battleid));
		//如果收到的battleid于数据库中的最新battleid不对应,忽略异常.返回最新battleid
		if($arena['battleid'] != $battleid && $battleid != -1)
			return $this->respond_ex(0, "success",'battle',array('battle_id'=>$arena['battleid']));

		//memcache 清除battle的信息
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_info_battle_".$arena['id'];
		$cache->rm($key);

		// check battle player 根据gs上的player 替换 db中的player位置
		if( ($player1 != '0' || $player2 != '0') && $phase == '0' && $battleid > 0){   //只有在phase == 0时替换player位置.
			$condition = array();
			$condition['id'] = $battleid;
			$battle = $db_battle->field('player1,player2,name1,name2,active_time1,active_time2,status1,status2')->where($condition)->find();
			if($battle === false)
				return $this->respond_ex(-631,"SQL ERROR,Find the battle failure, arenaid:{$arena['id']}, battleid:$battleid",'battle',array('battle_id'=>$battleid));
			if($battle['player1'] != "0" || $battle['player2'] != "0"){
				if( ($player1 != "0" && $player1 == $battle['player2']) ||
						($player2 != "0" && $player2 == $battle['player1'])
				){
					$update_battle['player1'] = $battle['player2'];
					$update_battle['player2'] = $battle['player1'];
					$update_battle['name1'] = $battle['name2'];
					$update_battle['name2'] = $battle['name1'];
					$update_battle['active_time1'] = $battle['active_time2'];
					$update_battle['active_time2'] = $battle['active_time1'];
					$update_battle['status2'] = $battle['status1'];
					$update_battle['status1'] = $battle['status2'];
					$result = $db_battle->where($condition)->save($update_battle);
					if($result === false){   // 通知GSD重启GS
						$ret = $this->set_gs_reboot($gsid);
						return $this->respond_ex(-205,"fail to find battle $battleid. arenaid:{$arena['id']}, reboot gs: ".$ret['msg'],'battle',array('battle_id'=>$battleid));
					}
					Log::write("Trend Replace the battle_player, player1:{$update_battle['player1']}, player2:{$update_battle['player2']}, arenaid:".$arena['id'].",battleid:".$battleid, Log::INFO);
				}
			}

			$redis = S(array('type'=>'Gloudredis'));
			//验证battle_player 是否改变,改变了清除 support
			if($player1 != $battle['player1'] && $battle['player1'] != '0'){
				$name = "support_".$battleid."_1";
				//清除redis support hash 表
				$redis->hdel($name);
			}
			if($player2 != $battle['player2'] && $battle['player2'] != '0'){
				$name = "support_".$battleid."_2";
				//清除redis support hash 表
				$redis->hdel($name);
			}
		}

		// 判断是否为第一下心跳
		if($battleid <= 0){
			// 检查是否有尚未结束的battle,如果有的话，结束掉该battle
			$condition = array();
			$condition['arena_id'] = $arena['id'];
			$condition['game_id'] = $arena['game_id'];
			$condition['end_time'] = 0;
			$update_endtime = array();
			$update_endtime['phase'] = 3;    //GS重启,结束之前所有未结束的battle
			$update_endtime['end_time'] = time();
			$result = $db_battle->where($condition)->save($update_endtime);
			if($result === false)
				return $this->respond_ex(-101,"finish previous battles failed. arenaid:".$arena['id'].", gs:$gsid",'battle',array('battle_id'=>$battleid));

			// 创建新的battle
			$new_battleid = $this->set_new_battle($db_battle, $arena['id'], $arena['game_id'], $player1, $status1, $player2, $status2);
			if(!$new_battleid)
				return $this->respond_ex(-102,"new battle failed. arenaid:".$arena['id'].",gs $gsid",'battle',array('battle_id'=>$battleid));

			// 将arena的set_reboot_time设置为0，表示GS已经重启过了
			$reboot_done = array();
			$reboot_done['id'] = $arena['id'];
			$reboot_done['set_reboot_time'] = 0;
			$result = $db_arena->save($reboot_done);
			if($result === false)
				return $this->respond_ex(-103,"arena Could not reset set_reboot_time,gs:$gsid,arenaid:{$arena['id']}");

			return $this->respond_ex(0,'success','battle',array('battle_id'=>$new_battleid));
		}
		// 判断GS是否已经被设置为需要重启，如果是，则直接返回错误，让GS自己重启。
		if ($arena['set_reboot_time'] != 0)
			return $this->respond_ex(-1000,"tell gs:$gsid to restart",'battle',array('battle_id'=>$battleid));
		// 根据id获取战斗的擂台ID和上次gs汇报时间
		$condition = array();
		$condition['id'] = $battleid;
		$battle = $db_battle->field('phase,player1,player2,name1,name2,active_time1,active_time2,status1,status2,end_time')->where($condition)->find();
		if($battle === false)
			return $this->respond_ex(-601,"SQL ERROR,Find the battle failure, arenaid:{$arena['id']}, battleid:$battleid",'battle',array('battle_id'=>$battleid));
		if(!$battle) {
			// 通知GSD重启GS
			$ret = $this->set_gs_reboot($gsid);
			return $this->respond_ex(-201,"fail to find battle $battleid. arenaid:{$arena['id']}, reboot gs: ".$ret['msg'],'battle',array('battle_id'=>$battleid));
		}
		if($battle['end_time'] != 0){
			// 上场战斗已经结束,创建新的battle
			$new_battleid = $this->set_new_battle($db_battle, $arena['id'], $arena['game_id'], $player1, $status1, $player2, $status2);
			if(!$new_battleid)
				return $this->respond_ex(-301,"new battle failed. gs $gsid, arenaid:{$arena['id']}",'battle',array('battle_id'=>$battleid));
			return $this->respond_ex(0,'success','battle',array('battle_id'=>$new_battleid));
		}

		$battle_update = array();
		$battle_update['gs_time'] = time();
		$battle_update['phase'] = $phase;
		//只有当GS为等人状态时,player空缺才会放人
		if($phase == 0) {
			$ret = $this->update_player($db_battle, $gsid, $battleid, $arena['id'],
					1, $player1, $status1, $battle['player1'], $battle['status1'], $battle['active_time1'], $arena['open_time'], $arena['close_time']);
			if ($ret['ret'] != 0)
				return $this->respond_ex($ret['ret'], $ret['msg'], 'battle', array('battle_id'=>$battleid));

			$ret = $this->update_player($db_battle, $gsid, $battleid, $arena['id'],
					2, $player2, $status2, $battle['player2'], $battle['status2'], $battle['active_time2'], $arena['open_time'], $arena['close_time']);
			if ($ret['ret'] != 0)
				return $this->respond_ex($ret['ret'], $ret['msg'], 'battle', array('battle_id'=>$battleid));
		}
		else if($phase == 1) { //当战斗开打了..直接更新状态.当状态发生变化时,推送客户端
			$battle_update['player1'] = $player1;
			$battle_update['name1'] = $this->get_account_name($player1);
			$battle_update['player2'] = $player2;
			$battle_update['name2'] = $this->get_account_name($player2);
			$battle_update['status1'] = $status1;
			$battle_update['status2'] = $status2;
			if($player1 != $battle['player1'] || $player2 != $battle['player2'] || $status1 != $battle['status1'] || $status2 != $battle['status2'])
				Log::write("Trend the battle player status change, arenaid:".$arena['id'].", battleid:".$battleid.",phase=>".$phase.",player1=>".$player1.",status1=>".$status1.",player2=>".$player2.",status2=>".$status2, Log::INFO);
		}
		//更新arena_battle的状态
		$condition = array();
		$condition['id'] = $battleid;
		$result = $db_battle->where($condition)->save($battle_update);
		if($result === false)
			return $this->respond_ex(-609,"SQL ERROR,fail to update battle, battleid:$battleid, arenaid:{$arena['id']}",'battle',array('battle_id'=>$battleid));
		return $this->respond_ex(0, "success",'battle',array('battle_id'=>$battleid));
	}

	//创建一个新的battle
	function set_new_battle($db_battle, $arena_id, $game_id, $player1 = 0, $status1 = 0, $player2 = 0, $status2 = 0)
	{
		// 创建新的battle
		$data = array();
		$data['arena_id'] = $arena_id;
		$data['game_id'] = $game_id;
		$data['player1'] = $player1;
		$data['player2'] = $player2;
		$data['status1'] = $status1;
		$data['status2'] = $status2;
		if($player1 != 0)
			$data['name1'] = $this->get_account_name($player1);
		if($player2 != 0)
			$data['name2'] = $this->get_account_name($player2);
		$data['create_time'] = time();
		$data['gs_time'] = time();
		$new_battleid = $db_battle->add($data);
		if($new_battleid !== false)
			Log::write("Trend Create a new battle, arenaid:".$arena_id.", new_battleid:".$new_battleid.",data:".json_encode($data), Log::INFO);
		return $new_battleid;
	}

	//设置GS重启时间
	function set_gs_reboot($gsid) {
		$nowtime = time();
		$db_arena = M('arena');
		$condition = array();
		$condition['gs_id'] = $gsid;
		$condition['_string'] = 'set_reboot_time = 0 or '.$nowtime.' - set_reboot_time > 300';
		$update = array();
		$update['set_reboot_time'] = $nowtime;
		$result = $db_arena->where($condition)->save($update);
		if($result === false)
			return $this->return_ex(-1,"SQL ERROR,Could not set set_reboot_time");
		return $this->return_ex(-1,"success set set_reboot_time ");
	}

	function update_player($db_battle, $gsid, $battleid, $arenaid, $player_index, $player, $status, $db_player, $db_status, $db_active_time, $open_time, $close_time) {
		$nowtime = time();
		//status 为 -1 时,标识player 空位放人
		if($status == '-1'){
			$replace_player = false;

			if ($db_player != 0) {
				if ($db_status == '-1') {
					if ( ($nowtime - $db_active_time) <= 20 )
						$replace_player = false; // Web已经分配了一个人，且尚未超时, 什么都不做
					else {
						$replace_player = true; // Web已分配但是用户尚未连接GS，且该用户已经超时，则清除该用户记录并放新用户上台
						Log::write("Trend assigned player$player_index $db_player is timeout. will be removed, number:1, arenaid:{$arenaid}, battleid:{$battleid}", Log::INFO);
					}
				}
				else { // ($db_status == 2 || $db_status == 5 || $db_status == 0)
					$replace_player = true; // 用户离开或者掉线或者在线（按道理不应该在线的），立刻清除该用户记录并放新用户上台
					Log::write("Trend assigned player$player_index $db_player is quit/leave/online. invalid status. will be removed, arenaid:{$arenaid}, battleid:{$battleid}", Log::INFO);
				}
			}
			else {
				$replace_player = true; // 数据库里也没分配用户，那么就放新用户上台
				Log::write("Trend assigned player$player_index is null. will be removed, arenaid:{$arenaid}, battleid:{$battleid}", Log::INFO);
			}

			if ($replace_player === true) {
				if ($db_player != 0) {
					// remove player from battle
					$condition = array();
					$condition["id"] = $battleid;
					$update = array();
					if ($player_index == 1) {
						$update['player1'] = 0;
						$update['active_time1'] = 0;
						$update['name1'] = '';
						$update['score1'] = 0;
						$update['status1'] = -1;    //20秒超时,重新置回空位
					}
					else if ($player_index == 2) {
						$update['player2'] = 0;
						$update['active_time2'] = 0;
						$update['name2'] = '';
						$update['score2'] = 0;
						$update['status2'] = -1;   //20秒超时,重新置回空位
					}
					$val = $db_battle->where($condition)->save($update);
					if($val === false)
						return $this->return_ex(-603,"index:$player_index. fail to remove timeout player from battle, number:1, arenaid:".$arenaid.", battleid:{$battleid}");
				}

				// 分配一个新用户，如果擂台还处于开启状态的话。
				if($this->check_gs_status($open_time, $close_time, time()) == 1){
					$ret = $this->get_player($player_index, $battleid, $arenaid);
					if($ret['ret'] != 0)
						return $this->return_ex($ret['ret'],"index:$player_index. ".$ret['msg']);
				}
			}
		}
		else {
			if($player != 0) {  //正常状态.status不为-1空缺,player也有人,正常记录
				$update = array();
				$update["id"] = $battleid;
				if ($player_index == 1) {
					$update['player1'] = $player;
					$update['active_time1'] = $nowtime;
					$update['name1'] = $this->get_account_name($player);
					$update['score1'] = 0;
					$update['status1'] = $status;
				}
				else if ($player_index == 2) {
					$update['player2'] = $player;
					$update['active_time2'] = $nowtime;
					$update['name2'] = $this->get_account_name($player);
					$update['score2'] = 0;
					$update['status2'] = $status;
				}
				$val = $db_battle->save($update);
				if($val === false)
					return $this->return_ex(-603,"index:$player_index. fail to update player from battle,arena_id:".$arenaid.", battleid:{$battleid}");
			}
			else {
				// status不是-1，但是player为空，状态异常，只能通知GSD重启GS
				$ret = $this->set_gs_reboot($gsid);
				return $this->return_ex(-222,"index:$player_index. status: $status but player is empty,arena_id:".$arenaid.", battleid:{$battleid}");
			}
		}
		return $this->return_ex(0, 'success');
	}

	function get_account_name($account_id) {
		$db = M('account');
		$condition = array();
		$condition['id'] = $account_id;
		$result = $db->field('nickname')->where($condition)->select();
		if($result === false || count($result) == 0){
			return $this->get_auto_name($account_id);
		}
		return $result[0]['nickname'];
	}

	//返回一个新的玩家
	function get_player($player_index, $battleid, $arena_id){
		$nowtime = time();
		$cache = S(array('type'=>'Gloudmemcached'));
		$lock_key = "lock_queue_".$arena_id;
		if($cache->add($lock_key,1) || C("MEMCACHED_STATUS") === false)
			$ret = $this->get_user_from_queue($arena_id);
		else
			return $this->return_ex(0,"success");

		if($ret['ret'] == -1) { // no user
			$cache->rm($lock_key);
			return $this->return_ex(0,"success");
		}
		else if($ret['ret'] != 0) {// error
			$cache->rm($lock_key);
			return $this->return_ex($ret['ret'],"{$player} placed the failure ".$ret['msg']);
		}

		if($player_index == 1){
			$update['player1'] = $ret['account_id'];
			$update['name1'] = $this->get_account_name($ret['account_id']);
			$update['active_time1'] = $nowtime;
			$update['score1'] = 0;
			$update['status1'] = -1; // 未连上GS之前都是空缺状态
		}
		if($player_index == 2){
			$update['player2'] = $ret['account_id'];
			$update['name2'] = $this->get_account_name($ret['account_id']);
			$update['active_time2'] = $nowtime;
			$update['score2'] = 0;
			$update['status2'] = -1; // 未连上GS之前都是空缺状态
		}

		$db_battle = M('arena_battle');
		$result = $db_battle->where(array('id'=>$battleid))->save($update);
		echo $db_battle->getLastSql();
		if($result === false) {
			$cache->rm($lock_key);
			return $this->return_ex(-609,"SQL ERROR, fail to update player,battleid:$battleid",'battle',array('battle_id'=>$battleid));
		}

		//memcache 清除battle的信息
		$key = "arena_info_battle_".$arena_id;
		$cache->rm($key);
		$cache->rm($lock_key);

		Log::write("Trend send player ".$ret['account_id']." to player ".$player_index.",battleid:".$battleid, Log::INFO);
		return $this->return_ex(0,"success");
	}

	/**
	 *	获取排队中第一个有效的用户,删除已经失效的用户
	 *	返回一个有效的用户
	 *	@return -201 查找用户coin失败
	 *	@return -202 查找chargepoint失败
	 *	@return -203 用户G coin 不足
	 */
	function get_user_from_queue($arenaid){
		$ret = array();
		$ret['ret'] = -1;
		$ret['msg'] = 'no valid user';
		$ret['account_id'] = 0;

		$db_queue = M("arena_queue");

		$model = M();
		$model->startTrans();
		// 按顺序读取所有可以上台的用户
		$condition = array();
		$condition["arena_id"] = $arenaid;
		$ready_account = $model->lock(true)->table("july_arena_queue")->field("id,account_id,active_time")->where($condition)->order("enqueue_time")->select();
		if($ready_account === false){
			$model->rollback();
			return $this->return_ex(-603,"SQL ERROR,fail to select users in queue,arena_id:".$arenaid);
		}
		if(count($ready_account) == 0){
			$model->rollback();
			return $this->return_ex(-1,"empty queue,arenaid:".$arenaid);
		}
			
		//判断最新battle的状态,如果battle中一个人都没有时.只有在queue中有两个人的时候才会放人入场
		//$db_battle = M("arena_battle");
		$condition = array();
		$condition['arena_id'] = $arenaid;
		$battle_data = $model->lock(true)->table("july_arena_battle")->field('player1,player2,end_time')->where($condition)->order("id desc")->find();
		if($battle_data === false){
			$model->rollback();
			return $this->return_ex(-612,"SQL ERROR,fail to select battle,arena_id:".$arenaid);
		}
		if($battle_data['end_time'] != 0){
			$model->rollback();
			return $this->return_ex(-306,"The battle Abnormal,arena_id:".$arenaid);
		}
		if($battle_data['player1'] == 0 && $battle_data['player2'] == 0 && count($ready_account) < 2){
			$model->rollback();
			return $this->return_ex(-1,"empty battle player and queue count not enough 2,arena_id:".$arenaid);
		}
		foreach($ready_account as $key=>$ready){
			//查看用户的coin是否足够
			$check_coin = $this->check_account_coin($ready['account_id'],$arenaid);
			if($check_coin['ret'] != 0 && $check_coin['ret'] != -1){
				return $this->return_ex(-109,$check_coin["msg"]);
			}
			// remove user from queue if no-coin or OK
			$condition = array();
			$condition["id"] =  $ready['id'];
			$val = $model->table("july_arena_queue")->where($condition)->delete();
			if($val === false){
				$model->rollback();
				return $this->return_ex(-603,"SQL ERROR,fail to delete user in queue,arena_id:".$arenaid);
			}

			if($check_coin['ret'] == -1){ // not enough coin
				Log::write("Trend the account not sufficient funds delete accountid:".$ready['account_id'].",arenaid:".$arenaid, Log::INFO);
				unset($ready_account[$key]);
				continue;
			}

			// available user
			$ret['ret'] = 0;
			$ret['account_id'] = $ready['account_id'];
			break;
		}

		//更新memcache   arena_queue的数据
		if(C("MEMCACHED_STATUS") === true){
			$cache = S(array('type'=>'Gloudmemcached'));
			$key = "arena_info_queue_".$arenaid;
			$queue_list = $this->get_queue_list($arenaid);
			if($queue_list['ret'] != 0)
				return $this->return_ex($queue_list['ret'], $queue_list['msg']);
			$result = $cache->set($key,$queue_list['msg']);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$model->commit();
		return $ret;
	}

	/*
		战斗结束。
	请求格式：形如http://localhost//api.php?m=Arena&a=battle_end&battleid=46&starttime=794105359&endtime=1414566357&score1=0&score2=2&player1=16&player2=100007&gsid=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
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
	-104:没找到该擂台
	-105:更新数据失败
	非零值均表示失败。
	*/
	public function battle_end(){
		$battleid = I('battleid',0);
		$gsid = I('gsid',0);
		$startime = I('startime',0);
		$endtime = I('endtime',0);
		$player1 = I('player1',0);
		$player2 = I('player2',0);
		$score1 = I('score1',0);
		$score2 = I('score2',0);
		$status1 = I('status1',-1);
		$status2 = I('status2',-1);

		//判断该擂台的状态
		$db = M('arena');
		$condition = array();
		$condition['gs_id'] = $gsid;
		$arena = $db->field("id,game_id,arena_type,open_time,close_time")->where($condition)->find();
		if(!$arena)
			return $this->respond(-104,"GS could not find the arena,GSID:$gsid,battleid:$battleid");

		//更新记录本场battle的信息
		$db_battle = M('arena_battle');
		$update = array();
		$update['id'] = $battleid;
		$update['score1'] = $score1;
		$update['score2'] = $score2;

		if($startime != 0)
			$update['start_time'] = $startime;
		if($endtime != 0){
			$update['phase'] = 2;    //battle正常结束
			$update['status1'] = $status1;
			$update['status2'] = $status2;
			$update['end_time'] = $endtime;
		}
		$result = $db_battle->save($update);
		if($result === false)
			return $this->respond(-105,"Data update failed,battleid:$battleid");

		//得分修改,更新memcache arena_battle的值
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = 'arena_info_battle_'.$arena['id'];
		$arena_battle = $this->get_battle_info($arena['id']);
		$result = $cache->set($key, $arena_battle);
		if($result === false)
			$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		//战斗结束时,判断队列中人数,通知gs是否踢人
		$kick_flag = false;
		if($endtime != 0){
			//if($arena['arena_type'] != '1'){   // 练习场擂台,不记录用户的胜场和最高连胜
			$key = "arena_info_cut_".$battleid;
			$data_cut = $cache->get($key);
			if($data_cut == false){  //没有查找到cut信息,
				$data_cut = array('p1'=>'0','p2'=>'0');
			}
			//添加战斗信息到arena_account
			if($player1 != 0){
				$ret = $this->set_arena_account($player1,$arena['id'],$arena['game_id'],$score1-$score2,$data_cut['p1']);
				if($ret['ret'] != 0)
					return $this->respond(-106,"player1 ".$ret['msg']);
				//清除擂台上两个的arena_account  memcache中的数据
				$key = 'arena_account_info_by_account_id_game_id_'.$player1.$arena['game_id'];
				$cache->rm($key);
			}
			if($player2 != 0){
				$ret = $this->set_arena_account($player2,$arena['id'],$arena['game_id'],$score2-$score1,$data_cut['p2']);
				if($ret['ret'] != 0)
					return $this->respond(-107,"player2 ".$ret['msg']);
				//清除擂台上两个的arena_account  memcache中的数据
				$key = 'arena_account_info_by_account_id_game_id_'.$player2.$arena['game_id'];
				$cache->rm($key);
			}
			//}

			//查询队列信息,判断是否需要踢出
			$db_queue = M("arena_queue");
			$condition = array();
			$condition['arena_id'] = $arena['id'];
			$queuecount = $db_queue->where($condition)->count();
			if($queuecount === false)
				return $this->respond(-106,"Failed to select the queue_list");
			if($queuecount > 0)
				$kick_flag = true;
			//memcache   战斗结束
			//把support信息放入数据库
			$redis = S(array('type'=>'Gloudredis'));
			$name = "support_".$battleid."_1";
			$data_support = $redis->hget($name,null,true);
			if($data_support != false && count($data_support) > 0){
				$db_support = M("arena_support");
				foreach($data_support as $key=>$val){
					$db_support->add($val);
				}
			}

			//清除redis support hash 表
			$redis->hdel($name);

			$name = "support_".$battleid."_2";
			$data_support = $redis->hget($name,null,true);
			if($data_support != false && count($data_support) > 0){
				$db_support = M("arena_support");
				foreach($data_support as $key=>$val){
					$db_support->add($val);
				}
			}
			//清除redis support hash 表
			$redis->hdel($name);

			//清除cut信息
			$key = "arena_info_cut_".$battleid;
			$data_cut = $cache->get($key);
			if($data_cut != false){
				$result = $cache->rm($key);
				if($result === false)
					$this->memcache_error_log("Failed to delete the cache,key:".$key.",error_code:".$cache->getResultCode());
			}
		}
		Log::write("Trend the battleid:$battleid battle_end,kick_flag:".json_encode($kick_flag), Log::INFO);

		return $this->respond_ex(0, "success","kick_flag",$kick_flag);
	}

	//更新arena_account信息
	function set_arena_account($account_id,$arena_id,$game_id,$difference,$cut){
		$db_arena_account = M("arena_account");
		$update = array();
		$condition = array();
		$condition['account_id'] = $account_id;
		$condition['game_id'] = $game_id;
		$arena_account = $db_arena_account->where($condition)->select();
		if($arena_account === false || count($arena_account) <= 0)
			return $this->return_ex(-1,'failed to select arena_account,account_id:'.$account_id.',game_id:'.$game_id);
		if($arena_account[0]['first_battle_time'] == 0)
			$update['first_battle_time'] = time();
		$update['last_battle_time'] = time();
		$update['total_battles'] = $arena_account[0]['total_battles'] + 1;
		
		$integral_model = M('integral');
		if($difference > 0){  //赢
			$update['total_wins'] = $arena_account[0]['total_wins'] + 1;
			$cut = $this->get_even_cut($account_id,$arena_id);
			if($arena_account[0]['max_combo_num'] < $cut){
				$update['max_combo_num'] = $cut;
			}
			if($cut >= 6){
				$win_nums = '6';
			}else{
				$win_nums = $cut + 1;
			}
			$integral = $integral_model->field('reward_integral')->where("win_nums = {$win_nums}")->find();
		}elseif( $difference == 0){ //平
			$integral = $integral_model->field('reward_integral')->where('win_nums = 0')->find();
		}elseif( $difference < 0 ){ //输
			$integral = $integral_model->field('reward_integral')->where('win_nums = -1')->find();
		}
		$update['integral'] = $arena_account[0]['integral'] + $integral['reward_integral']; // 更新积分
		
		$result = $db_arena_account->where($condition)->data($update)->save();
		if($result === false)
			return $this->return_ex(-1,"failed to update arena_account,account_id:".$account_id.",game_id:".$game_id);
		
		$ret = $this->save_stat_integral($game_id, $account_id, $integral);
		if( $ret['ret'] != '0')
			return $this->return_ex(-1,$ret['msg']);
			
		return $this->return_ex(0,'success');
	}
	
	/**
	 * 更新今天的积分记录
	*/
	function save_stat_integral($game_id, $account_id, $integral){
		$stat_integral_model = M('stat_integral');
		$condition = array();
		$condition['game_id'] = $game_id;
		$condition['account_id'] = $account_id;
		$condition['date'] = Date('Ymd',time());
		$old_integral = $stat_integral_model->field('integral')->where($condition)->find();
		if($old_integral == false)
			return $this->return_ex(-1,"failed to select stat_integral.account_id:{$account_id},game_id:{$game_id}");
		if( count($old_integral) > 0 ){
			$condition['integral'] = array('exp',"integral+{$integral}");
			$ret = $stat_integral_model->save($condition);
		}else{
			$condition['integral'] = $integral;
			$ret = $stat_integral_model->add($condition);
		}
		if($ret == false )
			return $this->return_ex(-1,"failed to update stat_integral.account_id:{$account_id},game_id:{$game_id}");
		return $this->return_ex(0,'success');
	}

	//////////////////////////////////////////////////////////////////////
	//                        以下是GS SERVER调用的接口
	//////////////////////////////////////////////////////////////////////

	/*
		客户端退出,清除必要信息(queue,battle)
	*/
	public function client_disconnect(){
		$deviceid = I('deviceid','');
		$arenaid = I('arenaid',0);
		$logintoken = I('logintoken','');
		$arena_live_server = I('arena_live_server', 0);

		// 给arenalive请求这个接口开个后门，相当于可以匿名访问
		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];

		//查看是否在queue里面
		$cache = S(array('type'=>'Gloudmemcached'));
		//读取memcache中的queue信息
		$key = "arena_info_queue_".$arenaid;
		$db_queue = $cache->get($key);
		if($db_queue === false){
			$db = M('arena_queue');
			$condition = array();
			$condition['arena_id'] = $arenaid;
			$db_queue = $db->where($condition)->select();
			if ($db_queue === false)
				return $this->respond(-105, "未找到队列信息，报名失败，请稍后重试。");
		}
		if (count($db_queue) > 0) {
			//return $this->respond(-106, "挑战队列已满，一会再报名吧！拼人品的时候到了！场上KO的时刻，就是报名的最佳时机！");
			foreach($db_queue as $val){
				if($val['account_id'] == $account_id){
					//删除queue信息
					$db = M('arena_queue');
					$condition = array();
					$condition['arena_id'] = $arenaid;
					$condition['account_id'] = $account_id;
					$ret = $db->where($condition)->delete();
					if($ret === false)
						return $this->respond(-107,'删除失败');

					//清除queue的数据,只有在memcache开发的时候才会rm
					$lock_key = "lock_queue_".$arenaid;
					if(C("MEMCACHED_STATUS") === true && $cache->add($lock_key,1)){
						$cache->rm($key);
						$cache->rm($lock_key);
					}
				}
			}
		}

		//检查battle中是否存在用户
		$key = "arena_info_battle_".$arenaid;
		$db_battle = $cache->get($key);
		if($db_battle === false){
			$db_battle = M("arena_battle");
			$condition = array();
			$condition['arena_id'] = $arenaid;
			$condition['_string'] = "(player1 = {$account_id} or player2 = {$account_id}) and end_time=0";
			$db_result = $db_battle->where($condition)->count();
			if($db_result >= 1){
				//清除battle的数据,只有在memcache开发的时候才会rm
				$cache->rm($key);
			}
		}
		else{
			if($db_battle['player'][0]['account_id'] == $account_id || $db_battle['player'][1]['account_id'] == $account_id){
				$cache->rm($key);
			}
		}
		return $this->respond(0,'success');
	}

	/*
		GSD的心跳。
	请求格式：形如http://localhost/api.php?m=Arena&a=gsd_hearbeat&localip=xxx
	deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
	arenaid是32位整数，表示一个擂台。
	(可选)logintoken是长度不超过32字节的字符串，上次登录获取的Token。打开应用时不传这个参数，获取账户信息时才传。
	返回格式：
	{"ret":0,"msg":"success"}

	ret是一个32位整数，具体值定义见下方。
	msg是一个长度不超过128字节的字符串。
	ret定义：
	0: 成功
	-100:查询失败
	非零值均表示失败。
	*/
	public function watcher_heartbeat(){
		return $this->watcher_hearbeat();
	}
	public function watcher_hearbeat(){
		$localip = get_client_ip();

		$arena_model = M("arena");
		$condition = array();
		$condition['status'] = '1';
		$condition['gsd_ip'] = array('like',"%".$localip."%");
		$datalist = $arena_model->field("gsd_id as watcher_id,game_id,gs_port,open_time,close_time")->where($condition)->order('gsd_id asc')->select();
		if($datalist === false)
			return $this->respond(-100,"Failed to query the arena gs_list");
		// 查找GSDID，GS列表：ID，端口
		$gsdlist = array();
		$list = array();
		if(count($datalist) > 0){
			$gsdlist['watcher_id'] = (int)$datalist[0]['watcher_id'];
			foreach($datalist as $key=>$val){
				//判断擂台开启时间,时间过期则不返回
				if(!$this->check_gs_open( $val['open_time'], $val['close_time'])){
					continue;
				}
				$list[] = array("start_mode"=>"START_ARENA",
						"proc_mode"=>"PROC_VM",
						"port"=>(int)$val['gs_port'],
						"game_id"=>(int)$val['game_id'],
						"gs_idx"=>(int)substr($val['gs_port'],-2,2)
				);
			}
			$gsdlist['gs_list']	= $list;
		}else{
			$gsdlist = (object)$gsdlist;
		}
		return $this->respond_ex(0, "success", 'watcher', $gsdlist);
	}

	//////////////////////////////////////////////////////////////////////
	//                        以下是统计的接口
	//////////////////////////////////////////////////////////////////////
	/*
		每日擂台数据统计
	*/
	public function stat_arena(){
		Log::write("stat_arena", Log::INFO);

		$startdate = I('startdate',  ''); // YYYYMMDD
		$enddate = I('enddate',  ''); // YYYYMMDD
		$today = I('today',  '');

		if ($startdate == '') {
			$startdate = date("Ymd");
			$start_time = strtotime($startdate) - 86400*3; // calculate last 3 days by default
		}
		else
			$start_time = strtotime($startdate); // calculate specified date
		if ($enddate == '')
			$enddate = date("Ymd");
		$end_time = strtotime($enddate) + 86400; // 24:00 of that day

		// update today only
		if ($today == 1) {
			$startdate = date("Ymd"); // YYYYMMDD
			$start_time = strtotime($startdate);
			$enddate = date("Ymd"); // YYYYMMDD
			$end_time = strtotime($enddate) + 86400; // 24:00 of day
		}

		if ($start_time < 1200000000 || $start_time > 2000000000 || $end_time < 1200000000 || $end_time > 2000000000 )
			return $this->respond(-100, 'invalid startdate or enddate');

		$arena_id = I('arenaid','');
		if($arena_id == '')
			return $this->respond(-101, 'the arenaid cannot be empty');
		$join_queue = I('join_queue_nums',0);
		$join_queue_error = I('join_queue_error',0);
		$leave_queue = I('leave_queue',0);
		$timeout = I('timeout',0);

		$model = M();

		$day_begin = $start_time;
		$day_date = date("Ymd", $day_begin);
		$day_end = $day_begin + 86400;

		$data['date'] = $day_date;
		$data['arena_id'] = $arena_id;
		//daily_user_nums 当日观众人数
		$sql = "select COUNT(DISTINCT account_id) as daily_user_nums from july_history_account_arena_time where arena_id = {$arena_id} and create_time > $day_begin and create_time < $day_end";
		$list = $model->query($sql);
		foreach($list as $v){
			$data['daily_user_nums'] = $v['daily_user_nums'];
		}

		//daily_battle_nums 当日battle的场次
		$sql = "select COUNT(DISTINCT id) as daily_battle_nums from july_arena_battle where create_time > $day_begin and create_time < $day_end and arena_id = {$arena_id}";
		$list = $model->query($sql);
		foreach($list as $v){
			$data['daily_battle_nums'] = $v['daily_battle_nums'];
		}

		//max_concurrent_user 当日最大并发观众
		$sql = "select create_time,leave_time from july_history_account_arena_time where arena_id = {$arena_id}";
		$array = $model->query($sql);
		if(count($array) > 0){
			$tt = $day_begin;
			while($tt < $day_end){
				foreach($array as $v){
					$count = 0;
					if($v['create_time'] < $tt && $v['leave_time'] > $tt){
						$count++;
					}
					if(empty($data['max_concurrent_user'])){
						$data['max_concurrent_user'] = 0;
					}
					$data['max_concurrent_user'] = max($data['max_concurrent_user'],$count);
				}
				$tt += 120;
			}
		}else{
			$data['max_concurrent_user'] = 0;
		}


		//daily_consume_coin 当日消耗货币
		$sql ="select IFNULL(COUNT(DISTINCT aat.battle_nums) * c.coin,0) as daily_consume_coin from july_chargepoint_arena as ca ";
		$sql.="left join july_chargepoint as c on c.id=ca.chargepoint_id ";
		$sql.="left join july_history_account_arena_time as aat on ca.arena_id=aat.arena_id ";
		$sql.="where aat.create_time > $day_begin and aat.create_time < $day_end and aat.arena_id = {$arena_id} and ca.type = 0";
		$list = $model->query($sql);
		foreach($list as $v){
			$data['daily_consume_coin'] = $v['daily_consume_coin'];
		}

		//daily_join_queue_nums 当日报名记录总数
		$data['daily_join_queue_nums'] = $join_queue;
		//daily_leave_queue_nums 当日退出队列总数
		$data['daily_leave_queue_nums'] = $leave_queue;
		//daily_join_queue_failed_nums 当日报名失败记录总数
		$data['daily_join_queue_failed_nums'] = $join_queue_error;
		//daily_account_timeout_nums 当日用户超时被踢记录总数
		$data['daily_account_timeout_nums'] = $timeout;

		$now = time();
		$sql = "replace into `july_stat_arena`(`date`,`arena_id`,`daily_user_nums`,`daily_battle_nums`,`max_concurrent_user`,`daily_consume_coin`,`daily_join_queue_nums`,`daily_leave_queue_nums`,`daily_join_queue_failed_nums`,`daily_account_timeout_nums`,`update_time`)";
		$sql.= "values({$data['date']},{$data['arena_id']},{$data['daily_user_nums']},{$data['daily_battle_nums']},{$data['max_concurrent_user']},{$data['daily_consume_coin']},{$data['daily_join_queue_nums']},{$data['daily_leave_queue_nums']},{$data['daily_join_queue_failed_nums']},{$data['daily_account_timeout_nums']},{$now})";
		$result = $model->execute($sql);
		if($result === false)
			return $this->respond(-102,"Data update failed");
		return $this->respond(0,"success");
	}

	/*
		统计擂主。
	*/
	public function arena_rank_stat(){
		Log::write("arena_rank_stat", Log::INFO);
		$startdate = I('startdate',  ''); // YYYYMMDD
		$enddate = I('enddate',  ''); // YYYYMMDD
		$today = I('today',  '');

		if ($startdate == '') {
			$startdate = date("Ymd");
			$start_time = strtotime($startdate) - 86400*3; // calculate last 3 days by default
		}
		else
			$start_time = strtotime($startdate); // calculate specified date
		if ($enddate == '')
			$enddate = date("Ymd");
		$end_time = strtotime($enddate) + 86400; // 24:00 of that day

		// update today only
		if ($today == 1) {
			$startdate = date("Ymd"); // YYYYMMDD
			$start_time = strtotime($startdate);
			$enddate = date("Ymd"); // YYYYMMDD
			$end_time = strtotime($enddate) + 86400; // 24:00 of day
		}

		if ($start_time < 1200000000 || $start_time > 2000000000 || $end_time < 1200000000 || $end_time > 2000000000 )
			return $this->respond('-100', 'invalid startdate or enddate');

		$db_arena = M("arena");
		$condition = array();
		$arena = $db_arena->field("id")->where($condition)->select();

		$db_battle = M("arena_battle");
		$day_begin = $start_time;
		while ($day_begin < $end_time) {
			$day_date = date("Ymd", $day_begin);
			$day_end = $day_begin + 86400;

			foreach($arena as $a)
			{
				//得到两个位置上的获胜人
				$sql1 = "select arena_id,player1 as win_player,player2 as lost_player,name1 as win_name from july_arena_battle where arena_id = {$a['id']} and create_time > $day_begin and create_time < $day_end and ((score1 > score2)) and (player1 != 0) and (player2 != 0)";
				$player1_win = $db_battle->query($sql1);
				$sql2 = "select arena_id,player2 as win_player,player1 as lost_player,name2 as win_name from july_arena_battle where arena_id = {$a['id']} and create_time > $day_begin and create_time < $day_end and ((score1 < score2)) and (player1 != 0) and (player2 != 0)";
				$player2_win = $db_battle->query($sql2);
				//合并数组
				$new_win = array_merge($player1_win,$player2_win);
				$val = array();
				foreach($new_win as $key=>$new)
				{
					$val[$day_date."_".$new['win_player']]['date'] = $day_date;
					$val[$day_date."_".$new['win_player']]['arena_id'] = $new['arena_id'];
					$val[$day_date."_".$new['win_player']]['account_id'] = $new['win_player'];
					//计算战胜的次数，同一天打败同一人多次计算多次
					if(empty($val[$day_date."_".$new['win_player']]['over_nums']))
						$val[$day_date."_".$new['win_player']]['over_nums'] = 0;
					$val[$day_date."_".$new['win_player']]['over_nums'] = $val[$day_date."_".$new['win_player']]['over_nums'] + 1;
					//计算战胜的人数,同一天打败同一人只计算胜利一次
					if(empty($val[$day_date."_".$new['win_player']]['lost_player']) || array_search($new['lost_player'],$val[$day_date."_".$new['win_player']]['lost_player']) === false)
					{
						if(empty($val[$day_date."_".$new['win_player']]['nums']))
							$val[$day_date."_".$new['win_player']]['nums'] = 0;
						$val[$day_date."_".$new['win_player']]['nums'] = $val[$day_date."_".$new['win_player']]['nums']+1;
					}
					else
					{
						continue;
					}
					$val[$day_date."_".$new['win_player']]['lost_player'][] = $new['lost_player'];
					$val[$day_date."_".$new['win_player']]['account_name'] = $new['win_name'];
				}
				$nums = array();
				foreach($val as $v)
				{
					$nums[] = $v['nums'];
				}
				//按照nums的大小排序
				array_multisort($nums,SORT_DESC,$val);
				//提取数组前10位
				$data = array_slice($val,0,10);
				$rank = 1;
				$db_rank = M('arena_rank');
				foreach($data as $d)
				{
					$add = array();
					$add['arena_id'] = $d['arena_id'];
					$add['date'] = $d['date'];
					$add['rank'] = $rank;
					$add['account_id'] = $d['account_id'];
					$add['account_name'] = $d['account_name'];
					$add['over_nums'] = $d['over_nums'];
					$add['nums'] = $d['nums'];
					$add['update_time'] = time();
					$sql = "replace into `july_arena_rank`(`arena_id`,`date`,`rank`,`account_id`,`account_name`,`over_nums`,`nums`,`update_time`)";
					$sql.= "values('{$add['arena_id']}','{$add['date']}','{$add['rank']}','{$add['account_id']}','{$add['account_name']}','{$add['over_nums']}','{$add['nums']}','{$add['update_time']}')";
					$db_rank->execute($sql);
					$rank++;
				}
			}
			$day_begin += 86400;
		}
		return $this->respond(0, "success");
	}

	/*
		统计排名。
	*/
	public function arena_account_rank_stat(){
		set_time_limit(0);
		Log::write("arena_account_rank_stat", Log::INFO);
		$db_arena_account = M("arena_account");
		$games = $db_arena_account->field("game_id")->group("game_id")->select();
		foreach($games as $a)
		{
			$data = $db_arena_account->field("account_id,game_id,total_wins")->where(array("game_id"=>$a['game_id']))->select();
			$integral_nums = array();
			foreach($data as $v){
				$nums[] = $v['total_wins'];
			}
			array_multisort($nums,SORT_DESC,$data);
			$rank = 1;
			foreach($data as $key=>$r){
				$data[$key]['rank'] = $rank;
				$rank++;
			}
			foreach($data as $d)
			{
				$condition = array();
				$condition['account_id'] = $d['account_id'];
				$condition['game_id'] = $d['game_id'];
				$update = array();
				$update['rank'] = $d['rank'];
				$db_arena_account->where($condition)->data($update)->save();
				usleep(10000);
			}
		}
		return $this->respond(0, "success");
	}
	
	/*
		统计积分排名。
	*/
	public function arena_account_integral_stat(){
		set_time_limit(0);
		Log::write("arena_account_integral_stat", Log::INFO);
		$db_arena_account = M("arena_account");
		$games = $db_arena_account->field("game_id")->group("game_id")->select();
		foreach($games as $a)
		{
			$data = $db_arena_account->field("account_id,game_id,integral")->where(array("game_id"=>$a['game_id']))->select();
			$integral_nums = array();
			foreach($data as $v){
				$integral_nums[] = $v['integral'];
			}
			array_multisort($integral_nums,SORT_DESC,$data);
			$integral_rank = 1;
			foreach($data as $key=>$r){
				$data[$key]['integral_rank'] = $integral_rank;
				$integral_rank++;
			}
			foreach($data as $d)
			{
				$condition = array();
				$condition['account_id'] = $d['account_id'];
				$condition['game_id'] = $d['game_id'];
				$update = array();
				$update['integral_rank'] = $d['integral_rank'];
				$db_arena_account->where($condition)->data($update)->save();
				usleep(10000);
			}
		}
		return $this->respond(0, "success");
	}

	// 每1分钟统计一次hot_nums
	public function get_hot_nums(){
		$time = time();
		$redis = S(array('type'=>'Gloudredis'));

		$arena_model = M('arena');
		$arena_id = $arena_model->field('id')->where('status=1')->select();
		if($arena_id === false || count($arena_id) < 1)
			return $this->respond(-101,'Failed to query the arena list');
		foreach($arena_id as $val){
			//获取时间点的hot_nums
			$hot_list = $redis->zRangeByScore('active_user_arenaid_'.$val['id'],time()-30,time());
				
			$hot_nums = 0;
			if (count($hot_list) > 0) {
				foreach($hot_list as $v){
					$hot = unserialize($v);
					$hot_nums = max($hot_nums, $hot[0]);
				}
			}
			if( $redis->zcount('arena_hot_nums_'.$val['id'], '-inf', '+inf') >= 60){
				//清楚第一个元素
				$redis->zRemRangeByRank('arena_hot_nums_'.$val['id'], '0', '0');
			}
			//放入redis
			$result = $redis->zadd('arena_hot_nums_'.$val['id'], $time, serialize(array($hot_nums,$time)));
		}
		return $this->respond(0, "success");
	}
}
