<?php
namespace Home\Controller;
use Home\Controller\BaseController;
// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_QUIET_EVAL, 1);

//API接口文件
class ClientTestController extends BaseController {
	public function index(){
		$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
	}
	// Create a handler function
	function my_assert_handler($file, $line, $code, $desc = null)
	{
		echo "Assertion failed at $file:$line: $code";
		if ($desc) {
			echo ": $desc";
		}
		echo "\n";
		exit(0);
	}


	// 需要本地设置的值
	protected $url_prefix;
	protected $device_uuid;
	protected $client_ver;
	protected $client_type;
	protected $client_pid;
	protected $gamepack_id;
	protected $game_id;
	protected $nickname;
	protected $phone;
	protected $email;
	
	// 从服务器上返回的值
	protected $login_token;
	protected $account_id;
	protected $regions;
	protected $gamecats;
	protected $first_serial_id;

	public function test() {
		// Set up the callback
		assert_options(ASSERT_CALLBACK, array($this, 'my_assert_handler'));
		set_time_limit(120);
	
		$code = I('code','');
		if ($code != 'new52')
			return $this->respond(-100, "invalid code $code");
		
		$this->url_prefix = "http://c4test.51ias.com/api.php?";
		$this->device_uuid = "test_".$this->generateRandomBigInt();
		$this->client_ver = date("Ymd",time());
		$this->client_type = "testcase";
		$this->client_pid = "test";
		$this->gamepack_id = 1;
		$this->game_id = 1;
		$this->nickname = 'nick jack';
		
		$this->setup_db();

		
		// 登录该设备
		$ret = $this->login($this->device_uuid, 1);
		
		
		$this->login_token = $ret['device']['login_token'];
		$this->account_id = $ret['device']['account']['id'];
		
		$ret = $this->modify_nick_name();
		
		// 绑定手机和邮箱
		$this->phone = $this->account_id."_test.com";
		$this->email = $this->account_id."@test.com";
		$ret = $this->bind_phone($this->device_uuid, $this->login_token, $this->phone);
		$ret = $this->bind_email();
		
		// 创建一个额外的设备，绑定同样的手机，然后再解绑该设备
		$device_extra = $this->device_uuid."_extra";
		$ret = $this->login($device_extra, 0);
		$device_extra_id = $ret['device']['id'];
		$ret = $this->bind_phone($device_extra, "", $this->phone);
		$ret = $this->device_unbind($this->device_uuid, $this->login_token, $device_extra_id);
		
		$ret = $this->mygames();
		$ret = $this->played_games();
		$ret = $this->recommend_list();
		$ret = $this->coin_payment_list();
		$ret = $this->rmb_payment_list();
		$ret = $this->card_payment_list();
		
		$ret = $this->purchase();
		$ret = $this->use_recharge_card();
		
		$ret = $this->device_list();
		$ret = $this->region_list();
		$ret = $this->region_info();
		$ret = $this->gamecat_list();
		$ret = $this->game_list();
		$ret = $this->game_pack();
		$ret = $this->game_info();
		$ret = $this->continuously_sign_in();
		$ret = $this->my_wallet_info();
		$ret = $this->last_save();
		$ret = $this->serial_add();
		$ret = $this->serial_list();
		$ret = $this->serial_del();
		$ret = $this->logout();
	}
	
	function download_url($url) {
		$try = 0;
		while(true) {
			$try++;
			$ctx = stream_context_create( array( 'http' => array('timeout' => 10) ) ); 
			$ret = file_get_contents($url, 0, $ctx); 
			if ($ret === false) {
				if ($try > 3) { // 如果失败，则尝试3次
					$error = error_get_last();
					echo "HTTP request failed $try times. Error was: " . $error['message'];
					exit(0);
				}
				else {
					sleep(3); // 停顿3秒再试
					continue;
				}
			}
			else
				break;
		}
		
		return $ret;
	}
	
	function setup_db() {
		// TODO: 确保游戏包存在、游戏存在、计费点存在、充值卡存在
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=device_login&deviceid=xxx&ver=xxx&type=xxx&pid=xxx&logintoken=xxx&autonewacc=1
	// {"ret":0,"msg":"success","device":{"name":"\u7528\u62371403759007666","login_token":"pznhg7aZsCtnfWh4ko38UQLbJDSh0An3","account":{"id":"1499","status":"1","nickname":"\u7528\u62371403759007666","level":"100","exp":"116499","phone":"","email":"","bean":"4000","coin":"0","gold":"0"}}}
	function login($device_uuid,$autonewacc) {	
	
		echo "=====begin login...<br>\n";
		$url = $this->url_prefix."m=Client&a=device_login&deviceid=".$device_uuid."&ver=".$this->client_ver."&type=".$this->client_type."&pid=".$this->client_pid;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == -102);
		
		if ($autonewacc != 0) {
			echo "begin login with autonewacc=1...<br>\n";
			$url = $this->url_prefix."m=Client&a=device_login&deviceid=".$device_uuid."&ver=".$this->client_ver."&type=".$this->client_type."&pid=".$this->client_pid."&logintoken=&autonewacc=1";
			echo "url: $url<br>\n";
			$raw_ret = $this->download_url($url);
			echo "ret: $raw_ret<br>\n";
			$ret = json_decode($raw_ret, true);
			assert($ret['ret'] == 0);
			assert($ret['device']['byname'] == '');
			assert($ret['device']['login_token'] != '');
			assert($ret['device']['account']['id'] != '');
			assert($ret['device']['account']['nickname'] == '');
			assert($ret['device']['account']['level'] == '0');
			assert($ret['device']['account']['exp'] == '0');
			assert($ret['device']['account']['phone'] == '');
			assert($ret['device']['account']['email'] == '');
			assert($ret['device']['account']['bean'] == '0');
			assert($ret['device']['account']['coin'] == '0');
			assert($ret['device']['account']['gold'] == '0');
			return $ret;
		}
		else
			return $ret;
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=device_logout&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success"}
	function logout() {
		echo "=====begin logout...<br>\n";
		$url = $this->url_prefix."m=Client&a=device_logout&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=phone_verify&deviceid=xxx&logintoken=xxx&phone=xxx
	// {"ret":0,"msg":"success"}
	// 请求格式：形如http://localhost/api.php?m=Client&a=phone_activate&deviceid=xxx&logintoken=xxx&phone=xxx&random_code=xxx
	// {"ret":0,"msg":"success"}
	function bind_phone($device_uuid,$login_token,$phone) {
		echo "=====begin phone_verify...<br>\n";
		$url = $this->url_prefix."m=Client&a=phone_verify&deviceid=".$device_uuid."&logintoken=".$login_token."&phone=".$phone;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		
		echo "=====read random_code ...<br>\n";
		$db = M('tmp_bind');
		$condition = array();
		$condition['device_uuid'] = $device_uuid;
		$condition['phone_or_email'] = $phone;
		$condition['verify_done_time'] = 0;
		$db_verify = $db->field("random_code")->where($condition)->order('verify_sent_time desc')->limit(1)->select();
		assert(count($db_verify) == 1);
		$random_code = $db_verify[0]['random_code'];
		echo "random_code: $random_code<br>\n";
		echo "<br>\n";
		
		echo "=====begin phone_activate...<br>\n";
		$url = $this->url_prefix."m=Client&a=phone_activate&deviceid=".$device_uuid."&logintoken=".$login_token."&phone=".$phone."&random_code=".$random_code;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		
		echo "=====again phone_verify...<br>\n";
		$url = $this->url_prefix."m=Client&a=phone_verify&deviceid=".$device_uuid."&logintoken=".$login_token."&phone=".$phone;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		if ($login_token != "")
			assert($ret['ret'] == -108);
		else
			assert($ret['ret'] == 0);
		echo "<br>\n";
		
		echo "=====again phone_activate...<br>\n";
		$url = $this->url_prefix."m=Client&a=phone_activate&deviceid=".$device_uuid."&logintoken=".$login_token."&phone=".$phone."&random_code=".$random_code;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == -120);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=email_verify&deviceid=xxx&logintoken=xxx&email=xxx
	// {"ret":0,"msg":"success"}
	// 请求格式：形如http://localhost/api.php?m=Client&a=email_activate&deviceid=xxx&logintoken=xxx&email=xxx&random_code=xxx
	// {"ret":0,"msg":"success"}
	function bind_email() {
		echo "=====begin email_verify...<br>\n";
		$url = $this->url_prefix."m=Client&a=email_verify&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&email=".$this->email;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		
		echo "=====read random_code ...<br>\n";
		$db = M('tmp_bind');
		$condition = array();
		$condition['device_uuid'] = $this->device_uuid;
		$condition['phone_or_email'] = $this->email;
		$condition['verify_done_time'] = 0;
		$db_verify = $db->field("random_code")->where($condition)->order('verify_sent_time desc')->limit(1)->select();
		assert(count($db_verify) == 1);
		$random_code = $db_verify[0]['random_code'];
		echo "random_code: $random_code<br>\n";
		echo "<br>\n";
		
		echo "=====begin email_activate...<br>\n";
		$url = $this->url_prefix."m=Client&a=email_activate&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&email=".$this->email."&random_code=".$random_code;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		
		echo "=====again email_verify...<br>\n";
		$url = $this->url_prefix."m=Client&a=email_verify&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&email=".$this->email;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == -108);
		echo "<br>\n";
		
		echo "=====again email_activate...<br>\n";
		$url = $this->url_prefix."m=Client&a=email_activate&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&email=".$this->email."&random_code=".$random_code;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == -120);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=device_list&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","devices":[{"id":"11240","name":"","client_ver":"20141015","client_type":"testcase","last_login_time":"1413343384"}]}
	function device_list(){
		echo "=====get device_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=device_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['devices']) == 2);
		assert($ret['devices'][0]['name'] == '');
		assert($ret['devices'][0]['client_ver'] == $this->client_ver);
		assert($ret['devices'][0]['client_type'] == $this->client_type);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=device_unbind&deviceid=xxx&logintoken=xxx&unbinddeviceid=xxx
	// {"ret":0,"msg":"success"}
	function device_unbind($device_uuid, $login_token, $unbind_device){
		echo "=====begin device_unbind...<br>\n";
		$url = $this->url_prefix."m=Client&a=device_unbind&deviceid=".$device_uuid."&logintoken=".$login_token."&unbinddeviceid=".$unbind_device;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=region_info&deviceid=xxx&logintoken=xxx&regionid=xxx
	//  {"ret":0,"msg":"success","region":{"id":"1","name":"\u00e5\u00b9\u00bf\u00e4\u00b8\u0153\u00e7\u201d\u00b5\u00e4\u00bf\u00a1(\u00e6\u00ad\u00a3\u00e5\u00bc\u008f)","status":"1","level":"0","speed_test_addr":"121.11.91.228","speed_test_addr_backup":"121.11.91.230","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0}}

	function region_info(){
		foreach ($this->regions as $region) {
			echo "=====get region_info...<br>\n";
			$url = $this->url_prefix."m=Client&a=region_info&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&regionid=".$region['id'];
			echo "url: $url<br>\n";
			$raw_ret = $this->download_url($url);
			echo "ret: $raw_ret<br>\n";
			$ret = json_decode($raw_ret, true);
			assert($ret['ret'] == 0);
			assert($ret['region']['id'] != '');
			assert($ret['region']['name'] != '');
			assert($ret['region']['speed_test_addr'] != '');
			assert($ret['region']['gsm_addr'] != '');
			echo "<br>\n";
		}
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=region_list&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","regions":[{"id":"1","name":"\u00e5\u00b9\u00bf\u00e4\u00b8\u0153\u00e7\u201d\u00b5\u00e4\u00bf\u00a1(\u00e6\u00ad\u00a3\u00e5\u00bc\u008f)","status":"1","level":"0","speed_test_addr":"121.11.91.228","speed_test_addr_backup":"121.11.91.230","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0},{"id":"3","name":"\u00e5\u0152\u2014\u00e4\u00ba\u00ac\u00e5\u00a4\u0161\u00e7\u00ba\u00bf(\u00e4\u00b8\u00b4\u00e6\u2014\u00b6)","status":"1","level":"0","speed_test_addr":"106.3.35.251","speed_test_addr_backup":"106.3.35.251","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0},{"id":"4","name":"\u00e6\u00b2\u00b3\u00e5\u008d\u2014\u00e8\u0081\u201d\u00e9\u20ac\u0161(\u00e4\u00b8\u00b4\u00e6\u2014\u00b6)","status":"1","level":"0","speed_test_addr":"218.29.185.103","speed_test_addr_backup":"218.29.185.103","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0},{"id":"6","name":"\u00e6\u00b5\u2122\u00e6\u00b1\u0178\u00e7\u201d\u00b5\u00e4\u00bf\u00a1(\u00e4\u00b8\u00b4\u00e6\u2014\u00b6)","status":"1","level":"0","speed_test_addr":"115.238.250.92","speed_test_addr_backup":"61.174.60.173","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0},{"id":"7","name":"\u00e6\u00b5\u2122\u00e6\u00b1\u0178\u00e8\u0081\u201d\u00e9\u20ac\u0161(\u00e4\u00b8\u00b4\u00e6\u2014\u00b6)","status":"1","level":"0","speed_test_addr":"60.12.75.138","speed_test_addr_backup":"121.52.235.82","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0},{"id":"8","name":"\u00e9\u2021\u008d\u00e5\u00ba\u2020\u00e8\u0081\u201d\u00e9\u20ac\u0161(\u00e4\u00b8\u00b4\u00e6\u2014\u00b6)","status":"1","level":"0","speed_test_addr":"113.207.68.23","speed_test_addr_backup":"113.207.68.25","speed_test_port":"8081","gsm_addr":"114.215.196.190","gsm_port":"8080","full_load":0}]}

	function region_list(){
		echo "=====get region_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=region_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['regions']) > 1);
		assert($ret['regions'][0]['name'] != '');
		assert($ret['regions'][0]['speed_test_addr'] != '');
		assert($ret['regions'][0]['gsm_addr'] != '');
		$this->regions = $ret['regions'];
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=gamecat_list&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","categories":[{"cat_id":"8","cat_name":"\u00e6\u02c6\u2018\u00e7\u0161\u201e\u00e6\u00b8\u00b8\u00e6\u02c6\u008f","summary":""},{"cat_id":"2","cat_name":"\u00e8\u0081\u201d\u00e6\u0153\u00ba\u00e6\u00b8\u00b8\u00e6\u02c6\u008f","summary":""},{"cat_id":"4","cat_name":"\u00e5\u0160\u00a8\u00e4\u00bd\u0153\u00e6\u00a0\u00bc\u00e6\u2013\u2014","summary":""},{"cat_id":"5","cat_name":"\u00e5\u2020\u2019\u00e9\u2122\u00a9\u00e8\u00a7\u00a3\u00e8\u00b0\u0153","summary":""},{"cat_id":"6","cat_name":"\u00e4\u00bd\u201c\u00e8\u201a\u00b2\u00e7\u00ab\u017e\u00e9\u20ac\u0178","summary":""},{"cat_id":"7","cat_name":"\u00e9\u00a3\u017e\u00e8\u00a1\u0152\u00e5\u00b0\u201e\u00e5\u2021\u00bb","summary":""},{"cat_id":"3","cat_name":"\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e6\u00b8\u00b8\u00e6\u02c6\u008f","summary":""}]}
	function gamecat_list(){
		echo "=====get gamecat_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=gamecat_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['categories']) >= 1);
		assert($ret['categories'][0]['cat_id'] != 0);
		assert($ret['categories'][0]['cat_name'] != '');
		$this->gamecats = $ret['categories'];
		echo "<br>\n";
		return true;
	}


	// 请求格式：形如http://localhost/api.php?m=Client&a=game_list&deviceid=xxx&logintoken=xxx&cat=xxx
	// {"ret":0,"msg":"success","games":[{"game_id":"1","category":"1","game_name":"\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84\u00ef\u00bc\u0161\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e7\u2030\u02c6","coin":"450","max_player":"2","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/cdn.51ias.com\/client\/pic\/game\/title\/1.jpg","controller":"1","trial_time":"1800","single_pack_id":"2","chargepoints":{"id":"217","name":"\u00e5\u0152\u2026\u00e6\u0153\u02c6-\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84","type":"0","type_name":"\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e6\u2014\u00b6\u00e9\u2014\u00b4\u00e5\u0152\u2026","coin":"2250","gamepack_id":"2"}}]}
	function game_list(){
	
		foreach ($this->gamecats as $gamecat) {
			echo "=====get game_list...<br>\n";
			$url = $this->url_prefix."m=Client&a=game_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&cat=".$gamecat['cat_id'];
			echo "url: $url<br>\n";
			$raw_ret = $this->download_url($url);
			//echo "ret: $raw_ret<br>\n";
			$ret = json_decode($raw_ret, true);
			assert($ret['ret'] == 0);
			assert(count($ret['games']) >= 0);
			if (count($ret['games']) > 0) {
				assert($ret['games'][0]['game_id'] != 0);
				assert($ret['games'][0]['game_name'] != '');
				assert($ret['games'][0]['level'] != '');
			}
			echo "<br>\n";
		}
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=game_pack&deviceid=xxx&logintoken=xxx&packid=xxx
	// {"ret":0,"msg":"success","pack":{"pack_name":"\u00e7\u00bb\u008f\u00e5\u2026\u00b8\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e5\u0090\u02c6\u00e9\u203a\u2020\u00e5\u0152\u2026","create_time":"1410923916","status":"1","summary":"\u00e7\u00bb\u008f\u00e5\u2026\u00b8\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e5\u0090\u02c6\u00e9\u203a\u2020\u00e5\u0152\u2026\u00e9\u2014\u00aa\u00e4\u00ba\u00ae\u00e7\u2122\u00bb\u00e5\u0153\u00ba\u00ef\u00bc\u0152\u00e9\u203a\u2020\u00e5\u0090\u02c6\u00e4\u00ba\u2020\u00e8\u00bf\u2021\u00e5\u00be\u20ac\u00e7\u2030\u02c6\u00e6\u0153\u00ac\u00e4\u00ba\u2018\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e7\u0161\u201e\u00e6\u20ac\u00bb\u00e5\u2026\u00b128\u00e6\u00ac\u00be\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e5\u00a4\u00a7\u00e4\u00bd\u0153\u00ef\u00bc\u0081","head_pic":"http:\/\/cdn.51ias.com\/client\/pic\/gamepack\/1\/head.jpg","deadline_time":0,"games":[{"game_id":"7","game_name":"NBA2K13","coin":"450","max_player":"2","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/cdn.51ias.com\/client\/pic\/game\/title\/7.jpg","controller":"1","trial_time":"0","single_pack_id":"7","chargepoints":{"id":"222","name":"\u00e5\u0152\u2026\u00e6\u0153\u02c6-NBA2K13","type":"0","type_name":"\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e6\u2014\u00b6\u00e9\u2014\u00b4\u00e5\u0152\u2026","coin":"2250","gamepack_id":"7"}},{"game_id":"1","game_name":"\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84\u00ef\u00bc\u0161\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e7\u2030\u02c6","coin":"450","max_player":"2","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/cdn.51ias.com\/client\/pic\/game\/title\/1.jpg","controller":"1","trial_time":"1800","single_pack_id":"2","chargepoints":{"id":"217","name":"\u00e5\u0152\u2026\u00e6\u0153\u02c6-\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84","type":"0","type_name":"\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e6\u2014\u00b6\u00e9\u2014\u00b4\u00e5\u0152\u2026","coin":"2250","gamepack_id":"2"}},{"game_id":"41","game_name":"\u00e5\u02c6\u00ba\u00e5\u00ae\u00a2\u00e4\u00bf\u00a1\u00e6\u009d\u00a12","coin":"450","max_player":"1","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/cdn.51ias.com\/client\/pic\/game\/title\/41.jpg","controller":"1","trial_time":"0","single_pack_id":"31","chargepoints":{"id":"246","name":"\u00e5\u0152\u2026\u00e6\u0153\u02c6-\u00e5\u02c6\u00ba\u00e5\u00ae\u00a2\u00e4\u00bf\u00a1\u00e6\u009d\u00a12","type":"0","type_name":"\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e6\u2014\u00b6\u00e9\u2014\u00b4\u00e5\u0152\u2026","coin":"2250","gamepack_id":"31"}}],"chargepoints":{"id":"11","name":"\u00e3\u20ac\u0090\u00e5\u0152\u2026\u00e6\u0153\u02c6\u00e3\u20ac\u2018\u00e7\u00bb\u008f\u00e5\u2026\u00b8\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e5\u0152\u2026\u00e5\u0090\u02c6\u00e9\u203a\u2020","type":"0","type_name":"\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e6\u2014\u00b6\u00e9\u2014\u00b4\u00e5\u0152\u2026","coin":"30000"}}}
	function game_pack(){
		echo "=====get game_pack...<br>\n";
		$url = $this->url_prefix."m=Client&a=game_pack&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&packid=".$this->gamepack_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		//echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['pack']['pack_name'] != '');
		assert($ret['pack']['head_pic'] != '');
		assert(count($ret['pack']['games']) >= 0);
		echo "<br>\n";
		return true;
	}


	// 请求格式：形如http://localhost/api.php?m=Client&a=game_info&deviceid=xxx&logintoken=xxx&gameid=xxx
	// {"ret":0,"msg":"success.no memcache.","game":{"game_id":"1","game_name":"\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84\u00ef\u00bc\u0161\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e7\u2030\u02c6","coin":"450","max_player":"2","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/cdn.51ias.com\/client\/pic\/game\/title\/1.jpg","control_pic":"http:\/\/cdn.51ias.com\/client\/pic\/game\/control\/1.jpg","def_video_width":"1280","def_video_height":"720","low_bitrate":"3000","mid_bitrate":"5000","high_bitrate":"8000","uploader":"","category":"1","controller":"1","trial_time":"1800","desc":"\u0152\u00e4\u00b9\u0178\u00e6\u00b7\u00b1\u00e9\u2122\u00b7\u00e8\u00bf\u2122\u00e5\u0153\u00ba\u00e6\u00b7\u00b7\u00e4\u00b9\u00b1\u00e9\u00a3\u017d\u00e6\u0161\u00b4\u00e4\u00b9\u2039\u00e4\u00b8\u00ad\u00e3\u20ac\u201a<\/p>","cats":[{"cat_name":"\u00e8\u0081\u201d\u00e6\u0153\u00ba\u00e6\u00b8\u00b8\u00e6\u02c6\u008f","cat_id":"2"},{"cat_name":"\u00e5\u0160\u00a8\u00e4\u00bd\u0153\u00e6\u00a0\u00bc\u00e6\u2013\u2014","cat_id":"4"}],"pics":[{"pic_type":"0","pic_file":"http:\/\/cdn.51ias.com\/client\/pic\/game\/small\/1\/01_01.jpg"},{"pic_type":"0","pic_file":"http:\/\/cdn.51ias.com\/client\/pic\/game\/small\/1\/01_02.jpg"},{"pic_type":"0","pic_file":"http:\/\/cdn.51ias.com\/client\/pic\/game\/small\/1\/01_03.jpg"},{"pic_type":"0","pic_file":"http:\/\/cdn.51ias.com\/client\/pic\/game\/small\/1\/01_04.jpg"},{"pic_type":"0","pic_file":"http:\/\/cdn.51ias.com\/client\/pic\/game\/small\/1\/01_05.jpg"},{"pic_type":"0","pic_file":"http:\/\/cdn.51ias.com\/client\/pic\/game\/small\/1\/01_06.jpg"}],"chargepoints":{"id":"217","name":"\u00e5\u0152\u2026\u00e6\u0153\u02c6-\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84","type":"0","type_name":"\u00e6\u00b8\u00b8\u00e6\u02c6\u008f\u00e6\u2014\u00b6\u00e9\u2014\u00b4\u00e5\u0152\u2026","coin":"2250"},"left_trial_time":1800,"gamepack_id":0,"deadline_time":0}}
	function game_info(){
		echo "=====get game_info...<br>\n";
		$url = $this->url_prefix."m=Client&a=game_info&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['game']['game_id'] == $this->game_id);
		assert($ret['game']['game_name'] != '');
		assert($ret['game']['title_pic'] != '');
		assert($ret['game']['control_pic'] != '');
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=mygames&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","games":[{"game_id":"1","game_name":"\u8d85\u7ea7\u8857\u5934\u9738\u738b4","coin":"2000","max_player":"2","status":"1","level":"0","save_enabled":"0","title_pic":"","deadline_time":"123123123"},{"game_id":"2","game_name":"\u96f7\u66fc\uff1a\u8d77\u6e90","coin":"500","max_player":"1","status":"1","level":"0","save_enabled":"0","title_pic":"","deadline_time":"123123123"}]}
	function mygames(){
		echo "=====get mygames...<br>\n";
		$url = $this->url_prefix."m=Client&a=mygames&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['games']) >= 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=played_games&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","games":[{"game_id":"1","game_name":"\u8d85\u7ea7\u8857\u5934\u9738\u738b4","coin":"2000","max_player":"2","status":"1","level":"0","save_enabled":"1","title_pic":"http:\/\/client.51ias.com\/game_pic\/small_1.jpg","controller":"1","trial_time":"1000","last_end_time":"1405308946"},{"game_id":"10","game_name":"\u9e70\u51fb\u957f\u7a7a","coin":"100","max_player":"1","status":"1","level":"10","save_enabled":"1","title_pic":"http:\/\/client.51ias.com\/game_pic\/small_10.jpg","controller":"1","trial_time":"0","last_end_time":"1405266969"}]}
	function played_games(){
		echo "=====get played_games...<br>\n";
		$url = $this->url_prefix."m=Client&a=played_games&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['games']) >= 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=recommend_list&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","recommends":[{"id":"1","title":"game","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_52.jpg","type":"0","content":"1"},{"id":"2","title":"gamepack","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_51.jpg","type":"1","content":"100"},{"id":"3","title":"webview","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_1.jpg","type":"2","content":"http:\/\/www.51ias.com"},{"id":"4","title":"url","start_time":"0","end_time":"1409608267","pic_file":"client.51ias.com\/game_pic\/small_10.jpg","type":"3","content":"http:\/\/www.baidu.com"}]}
	function recommend_list(){
		echo "=====get recommend_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=recommend_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['recommends']) >= 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=coin_payment_list&deviceid=xxx&logintoken=xxx&page=xxx&rows=xxx
	// {"ret":0,"msg":"success","result":{"count":"4","page":"2","rows":"1","payments":[{"order_id":"1881951512329454","coin":"2500","chargepoint_id":"0","create_time":"1407831172","device_name":"","device_id":"1888","chargepoint_name":""}]}}
	function coin_payment_list(){
		$page = 1;
		$rows = 10;
		
		echo "=====get coin_payment_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=coin_payment_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&page=".$page."&rows=".$rows;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['result']['count'] >= 0);
		assert($ret['result']['page'] == $page);
		assert($ret['result']['rows'] == $rows);
		assert($ret['result']['payments'] >= 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=card_payment_list&deviceid=xxx&logintoken=xxx&page=xxx&rows=xxx
	// {"ret":0,"msg":"success","result":{"count":"1","page":"1","rows":"1","payments":[{"card_id":"1010107327375182","card_pass":"1359434072287444","type":"0","chargepoint_id":"7","charge_time":"1407828902","device_name":"","device_id":"1888","chargepoint_name":"\u9e4f\u6e38\u4e91\u6e38\u620f\u57fa\u7840\u5305\u534a\u5e74"}]}}
	function card_payment_list(){
		$page = 1;
		$rows = 10;
		
		echo "=====get card_payment_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=card_payment_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&page=".$page."&rows=".$rows;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['result']['count'] >= 0);
		assert($ret['result']['page'] == $page);
		assert($ret['result']['rows'] == $rows);
		assert($ret['result']['payments'] >= 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=rmb_payment_list&deviceid=xxx&logintoken=xxx&page=xxx&rows=xxx
	// {"ret":0,"msg":"success","result":{"count":"1","page":"1","rows":"1","payments":[{"id":"75182","rmb":"10000","coin":"10000","total_bought_coin":"7345345","create_time":"1407828902"}]}}
	function rmb_payment_list(){
		$page = 1;
		$rows = 10;
		
		echo "=====get card_payment_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=rmb_payment_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&page=".$page."&rows=".$rows;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['result']['count'] >= 0);
		assert($ret['result']['page'] == $page);
		assert($ret['result']['rows'] == $rows);
		assert($ret['result']['payments'] >= 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=purchase&deviceid=xxx&logintoken=xxx&chargepointid=xxx
	function purchase(){
		// TODO:
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=use_recharge_card&deviceid=xxx&logintoken=xxx&card=xxx
	function use_recharge_card(){
		// TODO:
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=continuously_sign_in&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","continuously_sign_in":{"gift_coin_num":7500,"exp":0}}
	function continuously_sign_in(){
		echo "=====get continuously_sign_in...<br>\n";
		$url = $this->url_prefix."m=Client&a=continuously_sign_in&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['continuously_sign_in']['gift_coin_num'] >= 0);
		assert($ret['continuously_sign_in']['exp'] >= 0);
		echo "<br>\n";
		
		echo "=====get continuously_sign_in...<br>\n";
		$url = $this->url_prefix."m=Client&a=continuously_sign_in&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == -106);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?a=my_wallet_info&logintoken=P6ZdCRzbJIAydeQAkTRaXzJN22tUNlND&m=Client&deviceid=ffffffff-874b-8e14-ffff-ffffb58a3d4e
	// {"ret":0,"msg":"success","my_wallet_info":{"static_sign_in_info":[{"sign_day":"1","gift_coin":"500","gift_exp":"0","extra_gift_coin":0},{"sign_day":"2","gift_coin":"500","gift_exp":"0","extra_gift_coin":"8000"},{"sign_day":"3","gift_coin":"600","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"4","gift_coin":"600","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"5","gift_coin":"700","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"6","gift_coin":"700","gift_exp":"0","extra_gift_coin":"0"},{"sign_day":"7","gift_coin":"800","gift_exp":"0","extra_gift_coin":"10000"}],"total_coin":7500,"continuously_day":"1","gift_coin_num":7500,"bought_coin_num":0,"is_sign_today":"1"}}
	function my_wallet_info(){
		echo "=====get my_wallet_info...<br>\n";
		$url = $this->url_prefix."m=Client&a=my_wallet_info&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['my_wallet_info']['static_sign_in_info']) >= 1);
		assert($ret['my_wallet_info']['total_coin'] > 0);
		assert($ret['my_wallet_info']['gift_coin_num'] > 0);
		assert($ret['my_wallet_info']['bought_coin_num'] == 0);
		assert($ret['my_wallet_info']['continuously_day'] == 1);
		assert($ret['my_wallet_info']['is_sign_today'] == 1);
		echo "<br>\n";
		return true;
	}

	// http://localhost/api.php?a=modify_nick_name&m=Client&deviceid=00000000-01e3-f5e0-0033-c58700000000&nick_name=XXXname&logintoken=XXXXXXXXXX
	// {"ret":0,"msg":"success"}
	function modify_nick_name(){
		echo "=====get modify_nick_name...<br>\n";
		$url = $this->url_prefix."m=Client&a=modify_nick_name&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&nick_name=".$this->nickname;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=last_save&deviceid=xxx&logintoken=xxx&gameid=xxx
	// {"ret":0,"msg":"success. new serial. no save.","serial":{"id":25438,"name":"\u6211\u7684\u5b58\u6863"}}
	function last_save() {
		echo "=====get last_save...<br>\n";
		$url = $this->url_prefix."m=Client&a=last_save&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['serial']['id'] != '');
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=serial_list&deviceid=xxx&logintoken=xxx&gameid=xxx
	// {"ret":0,"msg":"success.","serials":[{"id":"25438","name":"\u6211\u7684\u5b58\u6863","create_time":"1413352869","count":"0"}]}
	function serial_list() {
		echo "=====get serial_list...<br>\n";
		$url = $this->url_prefix."m=Client&a=serial_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert(count($ret['serials']) > 0);
		assert($ret['serials'][0]['id'] != '');
		assert($ret['serials'][0]['count'] == 0);
		$this->first_serial_id = $ret['serials'][0]['id'];
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=save_del&deviceid=xxx&logintoken=xxx&gameid=xxx&saveid=xxx
	function save_del() {
		// TODO:
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=serial_add&deviceid=xxx&logintoken=xxx&gameid=xxx&name=xxx
	// {"ret":0,"msg":"success.","new_serial":{"serial_id":25440}}
	// {"ret":-106,"msg":"\u6e38\u620f\u5b58\u6863\u5e8f\u5217\u4e2a\u6570:2\u8d85\u51fa\u9650\u5236\u3002\u4e0a\u9650\u4e3a:1"}
	function serial_add() {
		echo "=====get serial_add...<br>\n";
		$url = $this->url_prefix."m=Client&a=serial_add&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id."&name=test";
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0 || $ret['ret'] == -106);
		if ($ret['ret'] == 0) {
			assert($ret['new_serial']['serial_id'] != '');
		}
		echo "<br>\n";
		return true;
	}

	// 请求格式：形如http://localhost/api.php?m=Client&a=serial_del&deviceid=xxx&logintoken=xxx&gameid=xxx&serialid=xxx
	function serial_del() {
		echo "=====get serial_del...<br>\n";
		$url = $this->url_prefix."m=Client&a=serial_del&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id."&serialid=".$this->first_serial_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0 || $ret['ret'] == -107); // 第一个存档序列不允许删除的。
		echo "<br>\n";
		return true;
	}
}
