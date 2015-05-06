<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class ChartController extends BaseController
{
	public function index(){
		$db = M('stat_daily');
		//图标数据
		$data = $db->order('date DESC')->limit(1,16)->select();
		$data = array_reverse($data);
		$date_7 = '';
		foreach($data as $v){
			$daily_new_account[] = $v['daily_new_account'];
			$daily_new_device[] = $v['daily_new_device'];
			$daily_new_valid_account[] = $v['daily_new_valid_account'];
			$max_concurrent_user[] = $v['max_concurrent_user'];
			$daily_play_time[] = $v['daily_play_time'];
			$daily_console_game[] = $v['daily_console_game'];
			$daily_insert_coin[] = $v['daily_insert_coin'];
			$daily_active_user[] = $v['daily_active_user'];
			$weekly_active_user[] = $v['weekly_active_user'];
			$monthly_active_user[] = $v['monthly_active_user'];
			$date_7  .=  '\''.substr($v['date'],4,2).'-'.substr($v['date'],6,2).'\',';
		}
		$this->yy = rtrim($date_7, ",");  //Y坐标
		$this->d1 = rtrim(implode(",",$daily_new_account), ",");
		$this->d2 = rtrim(implode(",",$daily_new_device), ",");
		$this->d3 = rtrim(implode(",",$daily_new_valid_account), ",");
		$this->d4 = rtrim(implode(",",$max_concurrent_user), ",");
		$this->d5 = rtrim(implode(",",$daily_play_time), ",");
		$this->d6 = rtrim(implode(",",$daily_console_game), ",");
		$this->d7 = rtrim(implode(",",$daily_insert_coin), ",");
		$this->d8 = rtrim(implode(",",$daily_active_user), ",");
		$this->d9 = rtrim(implode(",",$weekly_active_user), ",");
		$this->d10 = rtrim(implode(",",$monthly_active_user), ",");
		//每日统计详细
		$page = new \Think\Page($db->count(), 15);
		$this->pages = $page->show();
		$this->datalist = $db->field("*, daily_play_time/daily_active_user as daily_average_time")->order('date DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->display('index');
	}

	//游戏统计
	public function game()
	{
		//获取游戏信息
		$game=M('game');
		$this->game=$game->field('game_id,game_name')->order('game_id asc')->select();
		$db=M("stat_game");
		$game_id=I('game_id',0);
		$this->assign('game_id',$game_id);
		$this->game_name = I("game_name",'');
		$this->startdate=I('startdate','');
		$this->enddate=I('enddate','');
		//图标数据
		//$data = $db->order('date DESC')->limit(1,16)->select();
		if($game_id==0)
		{
			$sql="select date,sum(daily_active_user) as daily_active_user,sum(daily_play_time) as daily_play_time,max(max_concurrent_user) as max_concurrent_user,sum(daily_trial_count) as daily_trial_count,sum(daily_start_count) as daily_start_count,sum(daily_active_device) as daily_active_device,sum(weekly_active_user) as weekly_active_user,sum(monthly_active_user) as monthly_active_user";
			$sql.=" from july_stat_game group by date order by date desc limit 0,15";
		}
		else
		{
			$sql="select date,daily_active_user,daily_play_time,max_concurrent_user,daily_trial_count,daily_start_count,daily_active_device,weekly_active_user,monthly_active_user";
			$sql.=" from july_stat_game where game_id={$game_id} group by date order by date desc limit 0,15 ";
		}
		
		$data =$db->query($sql);
		$data = array_reverse($data);
		$date_7 = '';
		foreach($data as $v){
			$daily_active_user[] = $v['daily_active_user'];
			$daily_play_time[] = $v['daily_play_time'];
			$max_concurrent_user[] = $v['max_concurrent_user'];
			$daily_trial_count[] = $v['daily_trial_count'];
			$daily_start_count[] = $v['daily_start_count'];
			$daily_active_device[] = $v['daily_active_device'];
			$weekly_active_user[] = $v['weekly_active_user'];
			$monthly_active_user[] = $v['monthly_active_user'];
			$date_7  .=  '\''.substr($v['date'],4,2).'-'.substr($v['date'],6,2).'\',';
		}
		$this->yy = rtrim($date_7, ",");  //Y坐标
		$this->d1 = rtrim(implode(",",$daily_active_user), ",");
		$this->d2 = rtrim(implode(",",$daily_play_time), ",");
		$this->d3 = rtrim(implode(",",$max_concurrent_user), ",");
		$this->d4 = rtrim(implode(",",$daily_trial_count), ",");
		$this->d5 = rtrim(implode(",",$daily_start_count), ",");
		$this->d6 = rtrim(implode(",",$daily_active_device), ",");
		$this->d7 = rtrim(implode(",",$weekly_active_user), ",");
		$this->d8 = rtrim(implode(",",$monthly_active_user), ",");
		$where = '1=1 ';
		if($game_id!=0)
			$where .= " and  july_stat_game.game_id={$game_id} ";
		if($this->game_name && $this->game_name!='游戏名称'){
			$where .= ' and july_game.`game_name` like \'%'.$this->game_name.'%\'';
			
		}
		if($this->startdate && $this->startdate!="开始时间"){
			$where .= ' and july_stat_game.date >= \''.$this->startdate.'\'';
		}
		if($this->enddate && $this->enddate!="结束时间"){
			$where .= ' and july_stat_game.date <= \''.$this->enddate.'\'';
		}
		//每日统计详细
		if($this->game_name && $this->game_name!='游戏名称'){
			$count=$db->join("LEFT JOIN july_game on july_stat_game.game_id=july_game.game_id")
		->where($where)->count();	
		}else 
			$count=$db->where($where)->count();
		$page = new \Think\Page($count, 15);
		$this->pages = $page->show();
		$this->datalist = $db->field("july_stat_game.*,july_game.game_name")
		->join("LEFT JOIN july_game on july_stat_game.game_id=july_game.game_id")
		->where($where)
		->order('july_stat_game.date DESC,july_stat_game.game_id ASC')
		->limit($page->firstRow.','.$page->listRows)->select();
		
		$this->display('game_index');
	}

	//区域统计
	public function region()
	{
		//获取区域信息
		$region=M('region');
		$this->region=$region->field('id,name')->select();
		$db=M("stat_region");
		$region_id=I('region_id',0);
		$this->assign('region_id',$region_id);
		//图标数据
		//$data = $db->order('date DESC')->limit(1,16)->select();
		if($region_id==0)
		{
			$sql="select date,sum(daily_active_user) as daily_active_user,sum(daily_play_time) as daily_play_time,max(max_concurrent_user) as max_concurrent_user,sum(daily_trial_count) as daily_trial_count,sum(daily_start_count) as daily_start_count,sum(daily_active_device) as daily_active_device,sum(weekly_active_user) as weekly_active_user,sum(monthly_active_user) as monthly_active_user";
			$sql.=" from july_stat_region group by date order by date desc limit 0,15";
		}
		else
		{
			$sql="select date,daily_active_user,daily_play_time,max_concurrent_user,daily_trial_count,daily_start_count,daily_active_device,weekly_active_user,monthly_active_user";
			$sql.=" from july_stat_region where region_id={$region_id} group by date order by date desc limit 0,15";
		}

		$data =$db->query($sql);
		$data = array_reverse($data);
		$date_7 = '';
		foreach($data as $v){
			$daily_active_user[] = $v['daily_active_user'];
			$daily_play_time[] = $v['daily_play_time'];
			$max_concurrent_user[] = $v['max_concurrent_user'];
			$daily_trial_count[] = $v['daily_trial_count'];
			$daily_start_count[] = $v['daily_start_count'];
			$daily_active_device[] = $v['daily_active_device'];
			$weekly_active_user[] = $v['weekly_active_user'];
			$monthly_active_user[] = $v['monthly_active_user'];
			$date_7  .=  '\''.substr($v['date'],4,2).'-'.substr($v['date'],6,2).'\',';
		}
		$this->yy = rtrim($date_7, ",");  //Y坐标
		$this->d1 = rtrim(implode(",",$daily_active_user), ",");
		$this->d2 = rtrim(implode(",",$daily_play_time), ",");
		$this->d3 = rtrim(implode(",",$max_concurrent_user), ",");
		$this->d4 = rtrim(implode(",",$daily_trial_count), ",");
		$this->d5 = rtrim(implode(",",$daily_start_count), ",");
		$this->d6 = rtrim(implode(",",$daily_active_device), ",");
		$this->d7 = rtrim(implode(",",$weekly_active_user), ",");
		$this->d8 = rtrim(implode(",",$monthly_active_user), ",");
		if($region_id!=0)
		{
			$where=" july_stat_region.region_id={$region_id} ";
		}
		else
		{
			$where = "";
		}
		//每日统计详细
		$page = new \Think\Page($db->where($where)->count(),15);
		$this->pages = $page->show();
		$this->datalist = $db->field("july_stat_region.*,july_region.name as region_name")
		->join("LEFT JOIN july_region on july_stat_region.region_id=july_region.id")
		->where($where)
		->order('july_stat_region.date DESC,july_stat_region.region_id ASC')
		->limit($page->firstRow.','.$page->listRows)->select();
		$this->display('region_index');
	}

	//渠道统计
	public function pid()
	{
		//获取渠道信息
		$devicedb=M('device');
		$this->device=$devicedb->field('pid')->where("pid!=''")->group("pid")->select();
		$this->pid=I('pid','');
		$this->startdate=I('startdate','');
		$this->enddate=I('enddate','');
		$where = '1=1 ';
		if($this->pid && $this->pid!='渠道'){
			$where .= ' and `pid` like \'%'.$this->pid.'%\'';
		}
		if($this->startdate && $this->startdate!="开始时间"){
			$where .= ' and date >= \''.$this->startdate.'\'';
		}
		if($this->enddate && $this->enddate!="结束时间"){
			$where .= ' and date <= \''.$this->enddate.'\'';
		}
		$db=M('stat_pid');
		//图标数据
		$data = $db->field("date,sum(daily_new_account) as daily_new_account,sum(daily_new_device) as daily_new_device,sum(daily_active_user) as daily_active_user,sum(daily_play_time) as daily_play_time,max(max_concurrent_user) as max_concurrent_user,sum(daily_trial_count) as daily_trial_count,sum(daily_start_count) as daily_start_count,sum(daily_active_device) as daily_active_device,sum(weekly_active_user) as weekly_active_user,sum(monthly_active_user) as monthly_active_user,SUM(daily_new_active_user) AS daily_new_active_user,SUM(daily_new_play_time) AS daily_new_play_time,SUM(daily_new_trial_count) AS daily_new_trial_count,SUM(daily_new_start_count) AS daily_new_start_count,SUM(daily_times_nums) AS daily_times_nums,SUM(daily_payment_nums) AS daily_payment_nums,SUM(daily_new_times_nums) AS daily_new_times_nums,SUM(daily_new_payment_nums) AS daily_new_payment_nums")
		->where($where)->order('date DESC')->group('date')->limit(0,15)->select();
		$date_7 = '';
		$data = array_reverse($data);
		foreach($data as $v){
			$daily_new_account[] = $v['daily_new_account'];
			$daily_new_device[] = $v['daily_new_device'];
			$daily_active_user[] = $v['daily_active_user'];
			$daily_play_time[] = $v['daily_play_time'];
			$max_concurrent_user[] = $v['max_concurrent_user'];
			$daily_trial_count[] = $v['daily_trial_count'];
			$daily_start_count[] = $v['daily_start_count'];
			$daily_active_device[] = $v['daily_active_device'];
			$weekly_active_user[] = $v['weekly_active_user'];
			$monthly_active_user[] = $v['monthly_active_user'];
			$daily_new_active_user[] = $v['daily_new_active_user'];
			$daily_new_play_time[] = $v['daily_new_play_time'];
			$daily_new_trial_count[] = $v['daily_new_trial_count'];
			$daily_new_start_count[] = $v['daily_new_start_count'];
			$daily_times_nums[] = $v['daily_times_nums'];
			$daily_payment_nums[] = $v['daily_payment_nums'];
			$daily_new_times_nums[] = $v['daily_new_times_nums'];
			$daily_new_payment_nums[] = $v['daily_new_payment_nums'];
			$date_7  .=  '\''.substr($v['date'],4,2).'-'.substr($v['date'],6,2).'\',';
		}
		$this->yy = rtrim($date_7, ",");  //Y坐标
		$this->d1 = rtrim(implode(",",$daily_new_account), ",");
		$this->d2 = rtrim(implode(",",$daily_new_device), ",");
		$this->d3 = rtrim(implode(",",$daily_active_user), ",");
		$this->d4 = rtrim(implode(",",$daily_play_time), ",");
		$this->d5 = rtrim(implode(",",$max_concurrent_user), ",");
		$this->d6 = rtrim(implode(",",$daily_trial_count), ",");
		$this->d7 = rtrim(implode(",",$daily_start_count), ",");
		$this->d8 = rtrim(implode(",",$daily_active_device), ",");
		$this->d9 = rtrim(implode(",",$weekly_active_user), ",");
		$this->d10 = rtrim(implode(",",$monthly_active_user), ",");
		$this->d11 = rtrim(implode(",",$daily_new_active_user), ",");
		$this->d12 = rtrim(implode(",",$daily_new_play_time), ",");
		$this->d13 = rtrim(implode(",",$daily_new_trial_count), ",");
		$this->d14 = rtrim(implode(",",$daily_new_start_count), ",");
		$this->d15 = rtrim(implode(",",$daily_times_nums), ",");
		$this->d16 = rtrim(implode(",",$daily_payment_nums), ",");
		$this->d17 = rtrim(implode(",",$daily_new_times_nums), ",");
		$this->d18 = rtrim(implode(",",$daily_new_payment_nums), ",");
		//每日统计详细
		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->datalist = $db->where($where)->order('date DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->display('pid_index');

	}

	//测速记录
	public function nettest()
	{
		$searchtype=I('searchtype','');
		$search=trim(I('search',''));
		$this->assign('searchtype',$searchtype);
		$this->assign('search',$search);
		$where="1=1 ";
		if($search!='' && $searchtype!='')
		{
			$where.=" and  july_nettest.`{$searchtype}`='{$search}' ";
		}
		$db=M('nettest');
		//测试详细
		$page = new \Think\Page($db->where($where)->count(), 15);
		$this->pages = $page->show();
		$this->nettestlist = $db->join("LEFT JOIN july_region as r on r.id=july_nettest.region_id")->join("LEFT JOIN july_device d on d.device_uuid=july_nettest.device_uuid")
		->field("july_nettest.*,r.name,d.region,d.isp")->where($where)->order('id DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->display('nettest_index');
	}
	//自定义统计
	public function statistics(){
		$db = M('history_account_game_time');
		$game_db = M('game');
		$device_db = M('device');
		//游戏列表
		$this->gamelist = $game_db->field('game_id,game_name')->select();
		//获取省份
		$this->regionlist = $device_db->field('region,region_id')->group('region')->order('region_id')->select();
		//获取运营商
		$this->isplist = $device_db->field('isp,isp_id')->group('isp')->order('isp_id')->select();
		//获取渠道
		$this->pidlist = $device_db->field('pid')->group('pid')->order('pid')->select();
		//获取设备类型
		$this->clienttypelist = $device_db->field('client_type')->group('client_type')->order('client_type')->select();
		//获取客户端版本
		$this->clientverlist = $device_db->field('client_ver')->group('client_ver')->order('client_ver')->select();
		$this->dosubmint = I('dosubmint','');
		if($this->dosubmint){
			//接受查询条件数据
			$where = 'gt.device_uuid=sb.device_uuid and gm.game_id=gt.game_id ';
			//游戏
			$fields[] = 'gt.game_id';
			$fields[] = 'gm.game_id';
			$fields[] = 'gm.game_name';
			$this->game = I('game','');
			if($this->game){
				$where .= ' and gt.game_id = \''.$this->game.'\'';
				$gamename = $game_db->field('game_id,game_name')->where(array('game_id'=>$this->game))->select();
			}
			//省份
			$this->region = I('region','');
			if($this->region){
				$where .= ' and sb.region = \''.$this->region.'\'';
				$fields[] = 'sb.region';
			}
			//运营商
			$this->isp = I('isp','');
			if($this->isp){
				$where .= ' and sb.isp = \''.$this->isp.'\'';
				$fields[] = 'sb.isp';
			}
			//设备类型
			$this->type = I('type','');
			if($this->type){
				$where .= ' and sb.client_type = \''.$this->type.'\'';
				$fields[] = 'sb.client_type';
			}
			//客户端版本
			$this->ver = I('ver','');
			if($this->ver){
				$where .= ' and sb.client_ver = \''.$this->ver.'\'';
				$fields[] = 'sb.client_ver';
			}
			//客户渠道
			$this->pid = I('pid','');
			if($this->pid){
				$where .= ' and sb.pid = \''.$this->pid.'\'';
				$fields[] = 'sb.pid';
			}
			//用户ID
			$this->account = I('account','');
			if($this->account && $this->account!='用户ID'){
				$where .= ' and gt.account_id = \''.$this->account.'\'';
				$fields[] = 'gt.account_id';
			}
			//设备ID
			$this->device = I('device','');
			if($this->device && $this->device!='设备ID'){
				$where .= ' and gt.device_uuid = \''.$this->device.'\'';
				$fields[] = 'gt.device_uuid';
			}
			//GSIP
			$this->gsip = I('gsip','');
			if($this->gsip && $this->gsip!='GSIP'){
				$where .= ' and gt.gsip = \''.$this->gsip.'\'';
				$fields[] = 'gt.gsip';
			}
			//GSID
			$this->gsid = I('gsid','');
			if($this->gsid && $this->gsid!='GSID'){
				$where .= ' and gt.gsid = \''.$this->gsid.'\'';
				$fields[] = 'gt.gsid';
			}
			//结束代码
			$this->end_code = I('end_code','');
			if($this->end_code && $this->end_code!='游戏结束代码'){
				$where .= ' and gt.end_code = \''.$this->end_code.'\'';
				$fields[] = 'gt.end_code';
			}
			//时间类型
			$this->times = I('times','');
			if($this->times){
				//开始时间
				$this->startdate = I('startdate','');
				if($this->startdate && $this->startdate!='开始日期'){
					$where .= ' and '.$this->times.' >= \''.strtotime($this->startdate).'\'';
					$fields[] = $this->times;
				}
				//结束时间
				$this->enddate = I('enddate','');
				if($this->enddate && $this->enddate!='结束日期'){
					$where .= ' and '.$this->times.' <= \''.(strtotime($this->enddate)+86400).'\'';
				}
			}
			//排序条件
			$this->order = I('order','');
			if($this->order){
				$fields[] = $this->order;
			}
			$this->order_type = I('order_type','');

			//处理字段
			$this->checkboxdate = I('checkboxdate','');
			foreach($this->checkboxdate as $k=>$v){
				$fields[] = $k;
			}
			//剔除重复字段
			foreach(array_unique($fields) as $v){
				if($v){
					$fieldtext[] = $v;
				}
			}
			//组合查询字段
			$field = rtrim(implode(",",array_unique($fieldtext)), ",");
				
			//排序
			$order = $this->order.' '.$this->order_type;
				
			//联表
			$table = 'july_history_account_game_time gt,july_device sb,july_game gm';
				
			//分页
			$page = new Page($db->table($table)->where($where)->count(), PAGE_NUM);
			$this->pages = $page->show();
				
				
			//汇总图表数据整理
			$this->sum = I('sum','');
			//列出省份
			$regionchart = '';
			$gamechartdata = '';
			$onegamedata = array();


			//汇总
			if($this->sum){
				if($this->sum==1 && $this->game){
					//按游戏按省份列出时间
					$this->chart_title = $gamename[0]['game_name'];
					$chartdata = $this->regionlist;
					if($this->region) $chartdata = array(array('region'=>$this->region));
					$i=0;
					foreach($chartdata as $row){
						if($row['region']){
							$chartwhere = '';
							if(!$this->region) $chartwhere = ' and sb.region=\''.$row['region'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个游戏单个省份的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['region'].'\','; //列出所有省份
								$chart_more[$i]['chart_title'] = $row['region'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$gamechartdata .= '{name: \''.$gamename[0]['game_name'].'\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}elseif($this->sum==2 && $this->game){
					//按游戏按运营商列出时间
					$this->chart_title = $gamename[0]['game_name'];
					$chartdata = $this->isplist;
					if($this->isp) $chartdata = array(array('isp'=>$this->isp));
					$i=0;
					foreach($chartdata as $row){
						if($row['isp']){
							$chartwhere = '';
							if(!$this->isp) $chartwhere = ' and sb.isp=\''.$row['isp'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个游戏单个运营商的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['isp'].'\','; //列出所有运营商
								$chart_more[$i]['chart_title'] = $row['isp'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$gamechartdata .= '{name: \''.$gamename[0]['game_name'].'\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}elseif($this->sum==3 && $this->game){
					//按游戏按渠道列出时间
					$this->chart_title = $gamename[0]['game_name'];
					$chartdata = $this->pidlist;
					if($this->pid) $chartdata = array(array('pid'=>$this->pid));
					$i=0;
					foreach($chartdata as $row){
						if($row['pid']){
							$chartwhere = '';
							if(!$this->pid) $chartwhere = ' and sb.pid=\''.$row['pid'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个游戏单个渠道的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['pid'].'\','; //列出所有渠道
								$chart_more[$i]['chart_title'] = $row['pid'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$gamechartdata .= '{name: \''.$gamename[0]['game_name'].'\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}elseif($this->sum==4 && $this->game){
					//按游戏按设备类型列出时间
					$this->chart_title = $gamename[0]['game_name'];
					$chartdata = $this->clienttypelist;
					if($this->type) $chartdata = array(array('client_type'=>$this->type));
					$i=0;
					foreach($chartdata as $row){
						if($row['client_type']){
							$chartwhere = '';
							if(!$this->type) $chartwhere = ' and sb.client_type=\''.$row['client_type'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个游戏单个设备类型的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['client_type'].'\','; //列出所有设备类型
								$chart_more[$i]['chart_title'] = $row['client_type'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$gamechartdata .= '{name: \''.$gamename[0]['game_name'].'\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}elseif($this->sum==5 && $this->region){
					//单个省份所有游戏汇总
					$this->chart_title = $this->region;
					$chartdata = $this->gamelist;
					if($this->game) $chartdata = array(array('game_id'=>$this->game,'game_name'=>$gamename[0]['game_name']));
					$i=0;
					foreach($chartdata as $row){
						if($row['game_name']){
							$chartwhere = '';
							if(!$this->game) $chartwhere = ' and gt.game_id=\''.$row['game_id'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个省份单个游戏的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['game_name'].'\','; //列出所有游戏
								$chart_more[$i]['chart_title'] = $row['game_name'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$sumheight = count($onegamedata)*30;
					if($sumheight<180) $sumheight = 180;
					$this->sumheight = $sumheight;
					$gamechartdata .= '{name: \'总时间\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}elseif($this->sum==6 && $this->isp){
					//单个运营商所有游戏汇总
					$this->chart_title = $this->isp;
					$chartdata = $this->gamelist;
					if($this->game) $chartdata = array(array('game_id'=>$this->game,'game_name'=>$gamename[0]['game_name']));
					$i=0;
					foreach($chartdata as $row){
						if($row['game_name']){
							$chartwhere = '';
							if(!$this->game) $chartwhere = ' and gt.game_id=\''.$row['game_id'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个运营商单个游戏的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['game_name'].'\','; //列出所有游戏
								$chart_more[$i]['chart_title'] = $row['game_name'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$sumheight = count($onegamedata)*30;
					if($sumheight<180) $sumheight = 180;
					$this->sumheight = $sumheight;
					$gamechartdata .= '{name: \'总时间\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}elseif($this->sum==7 && $this->pid){
					//单个渠道所有游戏汇总
					$this->chart_title = $this->pid;
					$chartdata = $this->gamelist;
					if($this->game) $chartdata = array(array('game_id'=>$this->game,'game_name'=>$gamename[0]['game_name']));
					$i=0;
					foreach($chartdata as $row){
						if($row['game_name']){
							$chartwhere = '';
							if(!$this->game) $chartwhere = ' and gt.game_id=\''.$row['game_id'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个渠道单个游戏的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['game_name'].'\','; //列出所有渠道
								$chart_more[$i]['chart_title'] = $row['game_name'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$sumheight = count($onegamedata)*30;
					if($sumheight<180) $sumheight = 180;
					$this->sumheight = $sumheight;
					$gamechartdata .= '{name: \'总时间\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}
				elseif($this->sum==8 && $this->type){
					//单个设备类型所有游戏汇总
					$this->chart_title = $this->type;
					$chartdata = $this->gamelist;
					if($this->game) $chartdata = array(array('game_id'=>$this->game,'game_name'=>$gamename[0]['game_name']));
					$i=0;
					foreach($chartdata as $row){
						if($row['game_name']){
							$chartwhere = '';
							if(!$this->game) $chartwhere = ' and gt.game_id=\''.$row['game_id'].'\'';
							$sumlast = $db->table($table)->field('gt.gs_last_report_time')->where($where.$chartwhere)->sum('gt.gs_last_report_time');
							$sumstart = $db->table($table)->field('gt.gs_start_time')->where($where.$chartwhere)->sum('gt.gs_start_time');
							$sumtime = $sumlast-$sumstart;
							if($sumtime>0){
								$onegamedata[] = round($sumtime/360,2); //得到单个设备类型单个游戏的游戏时间,分钟，保留小数点后两位
								$regionchart  .=  '\''.$row['game_name'].'\','; //列出所有游戏
								$chart_more[$i]['chart_title'] = $row['game_name'];
								$chart_more[$i]['chart_data'] = round($sumtime/360,2);
							}
						}
						$i++;
					}
					$sumheight = count($onegamedata)*30;
					if($sumheight<180) $sumheight = 180;
					$this->sumheight = $sumheight;
					$gamechartdata .= '{name: \'总时间\', data: ['.rtrim(implode(",",$onegamedata), ",").']}';
				}
				if($this->sum){
						
					$this->yy = rtrim($regionchart, ",");  //汇总图表Y坐标
					$this->gamechartdata =  rtrim($gamechartdata, ",");	 //汇总图表数据
					$this->chart_more = $chart_more;    //汇总明细
				}


			}
				
			//控制查询详情表格宽度暂设每个字段为100像素宽度
			$this->countcheckbox = count($this->checkboxdate)*100;
				
			if($this->dosubmint==2){
				//导出EXICEL
				$this->datalist = $db->table($table)->field($field)->where($where)->order($order)->select();
				export_excel('云游戏数据('.date('Ymd').'导出)',$this->checkboxdate,$this->datalist);
			}elseif($this->dosubmint==1){
				//查询
				$this->datalist = $db->table($table)->field($field)->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
			}
		}

		$this->display('statistics');

	}
}
