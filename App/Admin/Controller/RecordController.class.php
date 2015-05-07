<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Think\Cache;

class RecordController extends BaseController
{
	public function gametimes()
	{
		$this->game = I('game','');
		$this->account = I('account','');
		$this->device = I('device','');
		$this->gs_ip = I('gs_ip','');
		$this->region_id = I('region_id','');
		$this->gsd_id = I('gsd_id','');
		$this->gs_id = I('gs_id','');
		$this->end_code = I('end_code','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = '1=1 ';
		if($this->account && $this->account!='账户'){
			$where .= ' and h.account_id = \''.$this->account.'\'';
		}
		if($this->device && $this->device!='设备'){
			$where .= ' and h.device_uuid = \''.$this->device.'\'';
		}
		if($this->gs_ip && $this->gs_ip!='GSIP'){
			$where .= ' and h.gs_ip = \''.$this->gs_ip.'\'';
		}
		if($this->region_id && $this->region_id!='区域ID'){
			$where .= ' and FLOOR(h.gs_id/1000000) = \''.$this->region_id.'\'';
		}
		if($this->gsd_id && $this->gsd_id!='GSDID'){
			$where .= ' and FLOOR(h.gs_id/1000) = \''.$this->gsd_id.'\'';
		}
		if($this->gs_id && $this->gs_id!='GSID'){
			$where .= ' and h.gs_id = \''.$this->gs_id.'\'';
		}
		if($this->end_code && $this->end_code!='结束代码'){
			$where .= ' and h.end_code = \''.$this->end_code.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and h.create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and h.create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db=M();
		if($this->game){
			// 如果指定了
			$where .= ' and h.game_id = g.game_id ';
			$where .= ' and g.game_id = \''.$this->game.'\'';
			$page = new \Think\Page($db->table('july_history_account_game_time h,july_game g')->where($where)->count(), PAGE_NUM);
		}
		else {
			// 如果没有指定游戏，则count july_history_account_game_time就行
			$page = new \Think\Page($db->table('july_history_account_game_time h')->where($where)->count(), PAGE_NUM);
			$where .= ' and h.game_id = g.game_id ';
		}
			
		$this->pages = $page->show();
			
		$this->gametimes = $db->table('july_history_account_game_time h,july_game g')->field('h.account_id,h.device_uuid,h.game_id,h.gs_ip,h.gs_id,h.is_online_gs,h.gs_start_time,h.gs_last_report_time,h.end_code,h.create_time,g.game_id,g.game_name')->where($where)->order('h.create_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$gametimes = $this->gametimes;
		foreach ($gametimes as &$row) {
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
			$raw_code = intval($row["end_code"]);
			switch ($raw_code) {
				case 0:
					if ($row["gs_last_report_time"] < $now - 300)
						$row["end_code"] = "GS五分钟无汇报，可能已出错";
					else
						$row["end_code"] = "游戏进行中";
					break;
				case 1:
					$row["end_code"] = "客户端无心跳断开";
					break;
				case 2:
					$row["end_code"] = "客户端长时间无操作，已被踢";
					break;
				case 3:
					$row["end_code"] = "客户端挂机结束";
					break;
				case 4:
					$row["end_code"] = "客户端链接异常断开";
					break;
				case 6:
					$row["end_code"] = "计费超时或无缴费记录";
					break;
				case 7:
					$row["end_code"] = "G币不足或无法正常扣币";
					break;
				case 8:
					$row["end_code"] = "试玩时间结束";
					break;
				case 17:
					$row["end_code"] = "游戏进程退出（用户通过游戏菜单退出或者游戏崩溃）";
					break;
				case 18:
					$row["end_code"] = "GSM服务器把用户踢了";
					break;
				case 19:
					$row["end_code"] = "用户通过云游戏菜单手动退出";
					break;
				case 20:
					$row["end_code"] = "无足够的显卡资源供游戏启动";
					break;
				case 21:
					$row["end_code"] = "擂台负场";
					break;
			}
			$row["end_code"] .= "($raw_code)";
		}
		$this->gametimes = $gametimes;
			
		//游戏列表
		$game_db = M('game');
		$this->gamelist = $game_db->order('game_id asc')->select();
		$this->display('gametimes');
	}

	public function gamepack()
	{
		$this->account = I('account','');
		$this->packid= I('packid','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		$where = '1=1 ';
		if($this->account && $this->account!='账户'){
			$where .= ' and agp.account_id = \''.$this->account.'\'';
		}
		if($this->packid && $this->packid!='游戏包ID'){
			$where .= ' and agp.gamepack_id = \''.$this->packid.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and agp.create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and agp.create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db = M('link_account_gamepack');
		$page = new \Think\Page($db->table('july_link_account_gamepack agp')->join('LEFT JOIN july_gamepack gp on agp.gamepack_id=gp.pack_id')->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->gamepacklist = '';
		$this->gamepacklist = $db->table('july_link_account_gamepack agp')->join('LEFT JOIN july_gamepack gp on agp.gamepack_id=gp.pack_id')->field('gp.pack_name,agp.*')->where($where)->order('agp.id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//echo M()->getLastSql();
		$this->display();
	}


	/*用户消费记录*/
	public function payment(){
		$this->user = I('user','');
		$this->name = I('name','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = '1=1  ';
		if($this->user && $this->user!='用户'){
			$where .= ' and p.account_id = \''.$this->user.'\'';
		}
		if($this->name && $this->name!='消费计费点'){
			$where .= ' and c.name like \'%'.$this->name.'%\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and p.create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and p.create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}

		$db = M('payment_coin');
		$page = new \Think\Page($db->table('july_payment_coin p')
				->join('LEFT JOIN july_chargepoint c on p.chargepoint_id=c.id')
				->join('LEFT JOIN july_game g on p.game_id=g.game_id')
				->join('LEFT JOIN july_gamepack gp on p.gamepack_id=gp.pack_id')
				->where($where)->count(), PAGE_NUM);

		$this->pages = $page->show();

		$this->paymentlist = $db->table('july_payment_coin p')
		->join('LEFT JOIN july_chargepoint c on p.chargepoint_id=c.id')
		->join('LEFT JOIN july_game g on p.game_id=g.game_id')
		->join('LEFT JOIN july_gamepack gp on p.gamepack_id=gp.pack_id')
		->field('p.*,g.game_name,gp.pack_name,c.name')
		->where($where)->order('p.create_time desc')->limit($page->firstRow . ',' . $page->listRows)
		->select();


		$this->display('payment');

	}

	/*用户收入记录*/
	public function income(){
		$this->user = I('user','');
		$this->income_type = I('income_type','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = 'income_type != -1115  '; // 强制使用值比较少的索引
		if($this->user && $this->user!='用户'){
			$where .= ' and account_id = \''.$this->user.'\'';
		}
		if($this->income_type && $this->income_type!='收入类型'){
			$where .= ' and income_type = \''.$this->income_type.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db = M('income_coin');
		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->incomelist = $db->where($where)->order('create_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display('income');
	}

	/*用户签到记录*/
	public function sign_in(){
		//查找配置信息
		$db = M("sign_in");
		$this->sign_in=$db->select();
		$this->user = I('user','');
		$this->continuously_day = I('continuously_day','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = '1=1  ';
		if($this->user && $this->user!='用户'){
			$where .= ' and account_id = \''.$this->user.'\'';
		}
		if($this->continuously_day && $this->continuously_day!='连续签到的天数'){
			$where .= ' and continuously_day = \''.$this->continuously_day.'\'';
		}
		if($this->startdate){
			$where .= ' and sign_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate){
			$where .= ' and sign_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db = M('continuously_sign_in');
		$page = new  \Think\Page($db->where($where)->count(), 15);
		$this->pages = $page->show();
		$this->sign_in_list = $db->where($where)->order('sign_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display('sign_in');
	}



	/*设备记录*/
	public function device(){
		$db = M('device');
		//查询条件
		$this->region = I('region','');
		$this->isp = I('isp','');
		$this->pid = I('pid','');
		$this->type = I('type','');
		$this->ver = I('ver','');
		$this->device = I('device','');
		$this->account = I('account','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		//$where = ' d.bind_account=a.id or d.bind_account=0';
		$where = '1=1 ';

		if($this->region){
			$where .= ' and region_id = \''.$this->region.'\'';
		}
		if($this->isp){
			$where .= ' and isp_id = \''.$this->isp.'\'';
		}
		if($this->pid){
			$where .= ' and pid = \''.$this->pid.'\'';
		}
		if($this->type && $this->type!="设备类型"){
			$where .= ' and client_type = \''.$this->type.'\'';
		}
		if($this->ver){
			$where .= ' and client_ver = \''.$this->ver.'\'';
		}
		if($this->device && $this->device!='设备ID'){
			$where .= ' and device_uuid = \''.$this->device.'\'';
		}
		if($this->account && $this->account!='帐号ID'){
			$where .= ' and bind_account = \''.$this->account.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and update_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and update_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$cache = Cache::getInstance();
		//获取省份
		$key = "regionlist";
		$regionlist = $cache->get($key);
		if($regionlist === false)
		{
			$regionlist = $db->group('region')->order('COUNT(*) desc')->select();
			$result = $cache->set($key,$regionlist,1800);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$this->assign('regionlist',$regionlist);

		//获取运营商
		$key = "isplist";
		$isplist = $cache->get($key);
		if($isplist === false)
		{
			$isplist = $db->group('isp')->order('COUNT(*) desc')->select();
			$result = $cache->set($key,$isplist,1800);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$this->assign('isplist',$isplist);
		//获取渠道
		$key = "pidlist";
		$pidlist = $cache->get($key);
		if($pidlist === false)
		{
			$pidlist = $db->group('pid')->order('COUNT(*) desc')->select();
			$result = $cache->set($key,$pidlist,1800);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$this->assign('pidlist',$pidlist);
		//获取客户端版本
		$key = "clientverlist";
		$clientverlist = $cache->get($key);
		if($clientverlist === false)
		{
			$clientverlist = $db->group('client_ver')->order('COUNT(*) desc')->select();
			$result = $cache->set($key,$clientverlist,1800);
			if($result === false)
				$this->memcache_error_log("Failed to set the cache,key:".$key.",error_code:".$cache->getResultCode());
		}
		$this->assign('clientverlist',$clientverlist);

		//分页
		$page = new \Think\Page($db->table('july_device')->where($where)->count(), 15);
		$this->pages = $page->show();

		//设备信息列表
		$this->devicelist = $db->table('july_device d')->field('d.id,d.device_uuid,d.bind_account,d.client_type,d.client_ver,d.byname,d.ip,d.region,d.region_id,d.isp,d.isp_id,d.pid,d.create_time,d.update_time,d.last_login_time,d.model')->where($where)->order('d.id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->display('device');
	}

	//订单记录
	public function order()
	{
		$this->order_id = I('order_id','');
		$this->account_id = I('account_id','');
		$this->chargepoint_id = I('chargepoint_id','');
		$this->buyer_userid = I('buyer_userid','');
		$this->trade_no = I('trade_no','');
		$this->status = I('status','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');

		$where = '1=1 ';
		if($this->order_id && $this->order_id!='订单ID'){
			$where .= ' and order_id = \''.$this->order_id.'\'';
		}
		if($this->account_id && $this->account_id!='购买人ID'){
			$where .= ' and account_id = \''.$this->account_id.'\'';
		}
		if($this->chargepoint_id && $this->chargepoint_id!='计费点ID'){
			$where .= ' and chargepoint_id = \''.$this->chargepoint_id.'\'';
		}
		if($this->buyer_userid && $this->buyer_userid!='第三方买家账号'){
			$where .= ' and buyer_userid = \''.$this->buyer_userid.'\'';
		}
		if($this->trade_no && $this->trade_no!='第三方交易号'){
			$where .= ' and trade_no = \''.$this->trade_no.'\'';
		}
		if($this->status!='' && $this->status!='订单状态'){
			$where .= ' and status = \''.$this->status.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db=M('order');

		//获取订单信息列表
		$page = new \Think\Page($db->where($where)->count(), 15);
		$this->pages = $page->show();
		$this->orderlist=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();

		$this->display('order');
	}
}

