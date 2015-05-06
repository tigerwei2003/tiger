<?php
namespace Admin\Controller;
defined('THINK_PATH') or exit;

class Gametimes extends Recordbase {
	
	private $mGame;
	private $mAccount;   // 账户
	private $mDevice;    // 设备
	private $GsIp;       // 服务器端ip
	private $mGsId;      // 服务器端id
	private $mEndCode;   // 结束代码
	private $mStartDate; // 开始日期
	private $mEndDate;   // 结束日期
		
	public function __construct() { 
		parent::__construct();
		
		//获取post过来的数据
		if(IS_POST) {
			$this->mGame      = I('post.game','');
			$this->mAccount   = I('post.account',0);
			$this->mDevice    = I('post.device','');
			$this->mGsIp      = I('post.gs_ip','');
			$this->mGsId      = I('post.gs_id',0);
			$this->mEndCode   = I('post.end_code','');
			$this->mStartDate = I('post.startdate','');
			$this->mEndDate   = I('post.enddate','');
		}
	}
	
	public function GetFunc($func_name) {		
		parent::Func($func_name);
	}
	
	final protected function Insert($value) {}
	final protected function Search($value) {}
	
	final protected function GetList() {
		$table = "{$this->mPrefix}history_account_game_time";
		$table_join = "{$this->mPrefix}game g RIGHT JOIN $table h ON g.game_id=h.game_id";
		$where = $this->GetWhereCondition();
		
		if($this->mGame) {
			$where .= " AND g.game_id='{$this->mGame}'";
			$table  = $table_join;
		}
	
		$pages = parent::GetPage($table,$where); 
		$this->pages = $pages['page'];
		
		$sql = "SELECT h.account_id,h.device_uuid,h.game_id,h.gs_ip,h.gs_id,h.is_online_gs,
					   h.gs_start_time,h.gs_last_report_time,h.end_code,h.create_time,g.game_id,g.game_name
				FROM  $table_join
				WHERE $where 
				ORDER BY h.id DESC
				LIMIT {$pages['first']},{$pages['num']}";
		$rs = $this->mDb->query($sql); 

		if($rs) {
			foreach($rs as $key=>$row) {
				$rs[$key]['end_code'] = SELF::GetEndCode($row['end_code'],$row['gs_last_report_time']);
			}
		}
	
		$sql = "SELECT game_name,game_name FROM {$this->mPrefix}game ORDER BY game_id ASC";
		$this->gamelist  = $this->mDb->query($sql);
		$this->gametimes = $rs;
		
		$this->display('gametimes');
	}
	
	/*
	 0: 游戏进行中
	 1：客户端无心跳
	 2：客户端长时间无操作
	 3：挂机到时
	 4：异常断开，进入等待.  断线重连等待结束
	 6：计费超时或无缴费记录
	 7：G币不足或无法正常扣币:
	 8: 试玩时间结束
	 17:游戏进程退出
	 18:gsm踢人
	 19:用户手动退出
	 20:显卡资源不足
	 21:擂台负场
	 */
	static private function GetEndCode($code_id=0,$last_time=0) {
		if(!$code_id) { return false; }
		
		switch($code_id) {
			case 0 :
				if ($last_time < time() - 300)
					$word = 'GS五分钟无汇报，可能已出错';
				else
					$word = '游戏进行中';
				break;
			case 1 :
					$word = '客户端无心跳断开';
				break;
			case 2 :
					$word = '客户端长时间无操作，已被踢';
				break;
			case 3 :
					$word = '客户端挂机结束';
				break;
			case 4 :
					$word = '客户端链接异常断开';
				break;
			case 6 :
					$word = '计费超时或无缴费记录';
				break;
			case 7 :
					$word = 'G币不足或无法正常扣币';
				break;
			case 8 :
					$word = '试玩时间结束';
				break;
			case 17 :
					$word = '游戏进程退出（用户通过游戏菜单退出或者游戏崩溃）';
				break;
			case 18 :
					$word = 'GSM服务器把用户踢了';
				break;
			case 19 :
					$word = '用户通过云游戏菜单手动退出';
				break;
			case 20 :
					$word = '无足够的显卡资源供游戏启动';
				break;
			case 21 :
					$word = '擂台负场';
				break;
			default :
					$word = '未知原因';
		}
		
		return $word;
	}
	
	/*
	 *获得数据库查询条件
	 */
	private function GetWhereCondition() { 
		$where = '1';
		
		if($this->mAccount && $this->mAccount != '账户') {
			$where .= " AND h.account_id='{$this->mAccount}'";
		}
		
		if($this->mDevice && $this->mDevice != '设备') {
			$where .= " AND h.device_uuid='{$this->mDevice}'";
		}
		
		if($this->mGsIp && $this->mGsIp != 'GSIP') {
			$where .= " AND h.gs_ip like '{$this->mGsIp}%'";
		}
		
		if($this->MGsId && $this->MGsId != 'GSID') {
			$where .= " AND h.gs_id like '{$this->MGsId}%'";
		}
		
		if($this->mEndCode && $this->mEndCode != '结束代码') {
			$where .= " AND h.end_code='{$this->mEndCode}'";
		}
		
		if($this->mStartDate && $this->mStartDate != '开始日期') {
			$where .= " AND h.create_time>='{strtotime($this->mStartDate)}'";
		}
		
		if($this->mEndDate && $this->mEndDate != '结束日期') {
			$where .= " AND h.create_time<='".(strtotime($this->mEndDatemEndDate)+86400)."'";
		}
		
		return $where;
	}
}