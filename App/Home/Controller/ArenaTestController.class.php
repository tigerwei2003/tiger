<?php
namespace Home\Controller;
use Home\Controller\BaseController;
// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_QUIET_EVAL, 1);

class ArenaTestController extends BaseController {
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
	protected $game_id;
	protected $arena_id;
	protected $nickname;
	
	// 从服务器上返回的值
	protected $login_token;
	protected $account_id;
	
	public function test()
	{
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
		$this->game_id = 1;
		$this->arena_id = 1;
		$this->nickname = 'nick jack';
		
		// 登录该设备
		$ret = $this->login($this->device_uuid, 1);
		$this->login_token = $ret['device']['login_token'];
		$this->account_id = $ret['device']['account']['id'];
		
		$ret = $this->arena_game_list();
		$ret = $this->arena_list();
		$ret = $this->arena_info();
		$ret = $this->get_account_rank();
		$ret = $this->modify_nick_name();
		//$ret = $this->get_arena_account();
		$ret = $this->logout();
	}
	
	
	function download_url($url) 
	{
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
	
	// 擂台游戏列表
	// 请求格式：形如：http://localhost/api.php?m=Arena&a=arena_game_list&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success"}
	function arena_game_list(){
		echo "=====begin arena_game_list...<br>\n";
		$url = $this->url_prefix."m=Arena&a=arena_game_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret: $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}
	
	//擂台列表
	//请求格式:形如http://localhost/api.php?m=Arena&a=arena_list&deviceid=xxx&logintoken=xxx
	// {"ret":0,"msg":"success","arenas":[{"game_id":"1","game_name":"\u00e8\u00b6\u2026\u00e7\u00ba\u00a7\u00e8\u00a1\u2014\u00e9\u0153\u00b84\u00ef\u00bc\u0161\u00e8\u00a1\u2014\u00e6\u0153\u00ba\u00e7\u2030\u02c6"},{"game_id":"1020","game_name":"1941"}]}
	function arena_list() {
		echo "======begin arena_list...<br>\n";
		$url = $this->url_prefix."m=Arena&a=arena_list&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret : $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}	
	
	//擂台详情
	//请求格式:形如ttp://localhost/api.php?m=Arena&a=arena_list&deviceid=xxx&logintoken=xxx&arenaid=xxx
	function arena_info() {
		echo "======begin arena_info...<br>\n";
		$url = $this->url_prefix."m=Arena&a=arena_info&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&arenaid=".$this->arena_id;
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret : $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		assert($ret['arena']['arena']['gs_ip'] != '');
		assert($ret['arena']['arena']['gs_port'] != '');
		assert($ret['arena']['arena']['live_url'] != '');
		//assert(!is_null($ret['arena']['account']));
		echo "<br>\n";
		return true;
	}
	
	//我的擂台
//	function get_arena_account() {
//		echo "======begin get_arena_account...<br>\n";
//		$url = $this->url_prefix."m=Arena&a=get_arena_account&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&gameid=".$this->game_id."&accountid=".$this->account_id;
//		echo "url: $url<br>\n";
//		$raw_ret = $this->download_url($url);
//		echo "ret : $raw_ret<br>\n";
//		$ret = json_decode($raw_ret, true);
//		assert($ret['ret'] == 0);
//		assert($ret['arena_account']['account_id'] == $this->account_id);
//		assert($ret['arena_account']['game_id'] == $this->game_id);
//		assert(isset($ret['arena_account']['gift_coin_num']));
//		assert($ret['arena_account']['nickname'] == $this->nickname);
//		echo "<br>\n";
//		return true;
//	}
	
	//修改昵称
	//请求格式:形如ttp://localhost/api.php?m=Client&a=modify_nick_name&deviceid=xxx&logintoken=xxx&nick_name=xxx
	function modify_nick_name() {
		echo "======begin modify_nick_name...<br>\n";
		$url = $this->url_prefix."m=Client&a=modify_nick_name&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&nick_name=".$this->nickname."";
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret : $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}
	
	//排行榜
	function get_account_rank(){
		echo "======begin get_account_rank...<br>\n";
		$url = $this->url_prefix."m=Arena&a=get_account_rank&deviceid=".$this->device_uuid."&logintoken=".$this->login_token."&game_id=".$this->game_id."";
		echo "url: $url<br>\n";
		$raw_ret = $this->download_url($url);
		echo "ret : $raw_ret<br>\n";
		$ret = json_decode($raw_ret, true);
		assert($ret['ret'] == 0);
		echo "<br>\n";
		return true;
	}	
}

?>
