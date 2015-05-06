<?php
// 全系统导出操作，仅对超级管理员开放
namespace Admin\Controller;
use Admin\Controller\BaseController;

class ExportController extends BaseController {

	/*导出充值卡*/
	public function code(){
		ini_set('memory_limit','256M');
		set_time_limit(120);

		$db = M('payment_card');

		$this->type = I('type','');
		$this->source = I('source','');
		$this->pid = I('pid','');
		$this->cid = I('cid','');
		$this->batch = I('batch','');
		$this->user = I('user','');
		$this->valid = I('valid',0);
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = 'p.chargepoint_id=c.id ';
		if($this->type==1){
			$where .= ' and p.charge_time > 0';
		}elseif($this->type==2){
			$where .= ' and p.charge_time = 0';
		}
		if($this->source && $this->source!='来源'){
			$where .= ' and p.source = \''.$this->source.'\'';
		}
		if($this->pid){
			$where .= ' and p.pid = \''.$this->pid.'\'';
		}
		if($this->cid){
			$where .= ' and p.chargepoint_id = \''.$this->cid.'\'';
		}
		if($this->batch && $this->batch!='批次'){
			$where .= ' and p.batch_id like \'%'.$this->batch.'%\'';
		}
		if($this->user && $this->user!='用户'){
			$where .= ' and p.charge_to_account_id = \''.$this->user.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and p.create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and p.create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}


		$rechargecardlist = $db->table('july_payment_card p,july_chargepoint c')
		->field('p.id,p.card_id,p.card_pass,p.source,p.pid,p.batch_id,p.create_time,p.expire_time,p.charge_to_account_id,p.charge_to_device_uuid,p.charge_time,p.valid,c.name')
		->where($where)->order('p.create_time desc')->select();

		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '充值卡');
		$sheet->setCellValue('B1', '密码');
		$sheet->setCellValue('C1', '充值卡类型');
		$sheet->setCellValue('D1', '来源');
		$sheet->setCellValue('E1', '创建时间');
		$sheet->setCellValue('F1', '有效期至');
		$sheet->setCellValue('H1', '充值账户');
		$sheet->setCellValue('I1', '充值时间');

		$i=1;
		foreach($rechargecardlist as $v){
			$i++;
			$sheet->setCellValue('A'.$i, $v['card_id']);
			$sheet->setCellValue('B'.$i, $v['card_pass']);
			$sheet->setCellValue('C'.$i, $v['name']);
			$sheet->setCellValue('D'.$i, $v['source']);
			$sheet->setCellValue('E'.$i, date('Y-m-d',$v['create_time']));
			$sheet->setCellValue('F'.$i, date('Y-m-d',$v['expire_time']));
			$sheet->setCellValue('H'.$i, $v['charge_to_account_id']);
			$sheet->setCellValue('I'.$i, date('Y-m-d',$v['charge_time']));

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,$v['card_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$i,$v['card_pass'], \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('充值卡');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('40');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('20');

		//导出文件名
		$filename = '云游戏充值卡（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}

	/*导出充值卡*/
	public function checkcard(){
		ini_set('memory_limit','256M');
		set_time_limit(120);
	
		$db = M('mulcard_payment_card');
	
		$this->source = I('source','');
		$this->pid = I('pid','');
		$this->cid = I('cid','');
		$this->batch = I('batch','');
		$this->user = I('user','');
		$this->valid = I('valid',0);
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = '1 ';
		if($this->source && $this->source!='来源'){
			$where .= ' and source = \''.$this->source.'\'';
		}
		if($this->pid){
			$where .= ' and pid = \''.$this->pid.'\'';
		}
		if($this->cid){
			$where .= ' and chargepoint_id = \''.$this->cid.'\'';
		}
		if($this->batch && $this->batch!='批次'){
			$where .= ' and batch_id like \'%'.$this->batch.'%\'';
		}
		if($this->user && $this->user!='用户'){
			$where .= ' and charge_to_account_id = \''.$this->user.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
	
	
		$rechargecardlist = $db->field('id,card_id,card_pass,source,pid,batch_id,create_time,expire_time,charge_to_account_id,charge_to_device_uuid,charge_time,valid,chargepoint_name')
		->where($where)->order('id desc')->select();
	
		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();
	
		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '充值卡');
		$sheet->setCellValue('B1', '密码');
		$sheet->setCellValue('C1', '充值卡类型');
		$sheet->setCellValue('D1', '来源');
		$sheet->setCellValue('E1', '创建时间');
		$sheet->setCellValue('F1', '有效期至');
		$sheet->setCellValue('H1', '充值账户');
		$sheet->setCellValue('I1', '充值时间');
	
		$i=1;
		foreach($rechargecardlist as $v){
			$i++;
			$sheet->setCellValue('A'.$i, $v['card_id']);
			$sheet->setCellValue('B'.$i, $v['card_pass']);
			$sheet->setCellValue('C'.$i, $v['changepoint_name']);
			$sheet->setCellValue('D'.$i, $v['source']);
			$sheet->setCellValue('E'.$i, date('Y-m-d',$v['create_time']));
			$sheet->setCellValue('F'.$i, date('Y-m-d',$v['expire_time']));
			$sheet->setCellValue('H'.$i, $v['charge_to_account_id']);
			$sheet->setCellValue('I'.$i, date('Y-m-d',$v['charge_time']));
	
			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,$v['card_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$i,$v['card_pass'], \PHPExcel_Cell_DataType::TYPE_STRING);
		}
	
		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('充值卡');
	
		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
	
		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('40');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('20');
	
		//导出文件名
		$filename = '云游戏多选卡（'.date('Ymd').'导出）';
	
		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	
	}
	
	//导出每日报表
	public function dailychart(){
		$db = M('stat_daily');


		//查询结果
		$daily = $db->order('date desc')->select();

		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '日期');
		$sheet->setCellValue('B1', '新增用户');
		$sheet->setCellValue('C1', '新增设备');
		$sheet->setCellValue('D1', '新增有效用户');
		$sheet->setCellValue('E1', '最高并发');
		$sheet->setCellValue('F1', '总游戏时间');
		$sheet->setCellValue('G1', '总消耗币');
		$sheet->setCellValue('H1', '总收入币');
		$sheet->setCellValue('I1', '试玩次数');
		$sheet->setCellValue('J1', '主机游戏次数');
		$sheet->setCellValue('K1', '街机投币次数');
		$sheet->setCellValue('L1', '活跃用户');
		$sheet->setCellValue('M1', '活跃设备');
		$sheet->setCellValue('N1', '最近7天活跃用户');
		$sheet->setCellValue('O1', '最近30天活跃用户');
		$sheet->setCellValue('P1', '历史累计活跃用户');
		$sheet->setCellValue('Q1', '历史累计活跃设备');
		$sheet->setCellValue('R1', '历史累计游戏时间');
		$sheet->setCellValue('S1', '更新时间');


		$i=1;
		foreach($daily as $v){
			$i++;
			$sheet->setCellValue('A'.$i, substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2));
			$sheet->setCellValue('B'.$i, $v['daily_new_account']);
			$sheet->setCellValue('C'.$i, $v['daily_new_device']);
			$sheet->setCellValue('D'.$i, $v['daily_new_valid_account']);
			$sheet->setCellValue('E'.$i, $v['max_concurrent_user']);
			$sheet->setCellValue('F'.$i, $v['daily_play_time']);
			$sheet->setCellValue('G'.$i, $v['daily_used_coin']);
			$sheet->setCellValue('H'.$i, $v['daily_used_income_coin']);
			$sheet->setCellValue('I'.$i, $v['daily_trial_count']);
			$sheet->setCellValue('J'.$i, $v['daily_console_game']);
			$sheet->setCellValue('K'.$i, $v['daily_insert_coin']);
			$sheet->setCellValue('L'.$i, $v['daily_active_user']);
			$sheet->setCellValue('M'.$i, $v['daily_active_device']);
			$sheet->setCellValue('N'.$i, $v['weekly_active_user']);
			$sheet->setCellValue('O'.$i, $v['monthly_active_user']);
			$sheet->setCellValue('P'.$i, $v['accumulated_active_user']);
			$sheet->setCellValue('Q'.$i, $v['accumulated_active_device']);
			$sheet->setCellValue('R'.$i, $v['accumulated_play_time']);
			$sheet->setCellValue('S'.$i, date('Y-m-d',$v['update_time']));

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2), \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('每日报表');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth('20');
		//导出文件名
		$filename = '云游戏每日报表（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}


	//导出每日游戏报表
	public function gamechart(){
		$db = M('stat_game');

		$this->game_id = I('game_id',0);
		$this->game_name = I("game_name",'');
		$this->startdate=I('startdate','');
		$this->enddate=I('enddate','');
		$where = '1=1 ';
		if($this->game_id != 0)
			$where .=' july_stat_game.game_id = \''.$this->game_id.'\'';
		if($this->game_name && $this->game_name!='游戏'){
			$where .= ' and july_game.`game_name` like \'%'.$this->game_name.'%\'';
		}
		if($this->startdate && $this->startdate!="开始时间"){
			$where .= ' and july_stat_game.date >= \''.$this->startdate.'\'';
		}
		if($this->enddate && $this->enddate!="结束时间"){
			$where .= ' and july_stat_game.date <= \''.$this->enddate.'\'';
		}
		//查询结果
		$game = $db->field("july_stat_game.*,july_game.game_name")
		->join("LEFT JOIN july_game on july_stat_game.game_id=july_game.game_id")->where($where)
		->order('july_stat_game.date desc,july_stat_game.game_id ASC')->select();
		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '日期');
		$sheet->setCellValue('B1', '游戏ID');
		$sheet->setCellValue('C1', '游戏名称');
		$sheet->setCellValue('D1', '当日活跃用户');
		$sheet->setCellValue('E1', '当日总游戏时间');
		$sheet->setCellValue('F1', '当日活跃设备');
		$sheet->setCellValue('G1', '当日最高在线');
		$sheet->setCellValue('H1', '当日试玩次数');
		$sheet->setCellValue('I1', '当日启动游戏次数');
		$sheet->setCellValue('J1', '最近7天活跃用户');
		$sheet->setCellValue('K1', '最近30天活跃用户');
		$sheet->setCellValue('L1', '历史累计活跃用户');
		$sheet->setCellValue('M1', '历史累计活跃设备');
		$sheet->setCellValue('N1', '历史累计游戏时间');
		$sheet->setCellValue('O1', '更新时间');


		$i=1;
		foreach($game as $v){
			$i++;
			$sheet->setCellValue('A'.$i, substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2));
			$sheet->setCellValue('B'.$i, $v['game_id']);
			$sheet->setCellValue('C'.$i, $v['game_name']);
			$sheet->setCellValue('D'.$i, $v['daily_active_user']);
			$sheet->setCellValue('E'.$i, $v['daily_play_time']);
			$sheet->setCellValue('F'.$i, $v['daily_active_device']);
			$sheet->setCellValue('G'.$i, $v['max_concurrent_user']);
			$sheet->setCellValue('H'.$i, $v['daily_trial_count']);
			$sheet->setCellValue('I'.$i, $v['daily_start_count']);
			$sheet->setCellValue('J'.$i, $v['weekly_active_user']);
			$sheet->setCellValue('K'.$i, $v['monthly_active_user']);
			$sheet->setCellValue('L'.$i, $v['accumulated_active_user']);
			$sheet->setCellValue('M'.$i, $v['accumulated_active_device']);
			$sheet->setCellValue('N'.$i, $v['accumulated_play_time']);
			$sheet->setCellValue('O'.$i, date('Y-m-d',$v['update_time']));

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2), \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('每日游戏报表');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth('20');


		//导出文件名
		$filename = '云游戏每日游戏报表（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	//导出每日区域报表
	public function regionchart(){
		$db = M('stat_region');
			
		//查询结果
		$game = $db->field("july_stat_region.*,july_region.name as region_name")
		->join("LEFT JOIN july_region on july_stat_region.region_id=july_region.id")
		->order('july_stat_region.date DESC,july_stat_region.region_id ASC')
		->select();

		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '日期');
		$sheet->setCellValue('B1', '区域ID');
		$sheet->setCellValue('C1', '区域名称');
		$sheet->setCellValue('D1', '当日活跃用户');
		$sheet->setCellValue('E1', '当日总游戏时间');
		$sheet->setCellValue('F1', '当日活跃设备');
		$sheet->setCellValue('G1', '当日最高在线');
		$sheet->setCellValue('H1', '当日试玩次数');
		$sheet->setCellValue('I1', '当日启动游戏次数');
		$sheet->setCellValue('J1', '最近7天活跃用户');
		$sheet->setCellValue('K1', '最近30天活跃用户');
		$sheet->setCellValue('L1', '历史累计活跃用户');
		$sheet->setCellValue('M1', '历史累计活跃设备');
		$sheet->setCellValue('N1', '历史累计游戏时间');
		$sheet->setCellValue('O1', '更新时间');


		$i=1;
		foreach($game as $v){
			$i++;
			$sheet->setCellValue('A'.$i, substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2));
			$sheet->setCellValue('B'.$i, $v['region_id']);
			$sheet->setCellValue('C'.$i, $v['region_name']);
			$sheet->setCellValue('D'.$i, $v['daily_active_user']);
			$sheet->setCellValue('E'.$i, $v['daily_play_time']);
			$sheet->setCellValue('F'.$i, $v['daily_active_device']);
			$sheet->setCellValue('G'.$i, $v['max_concurrent_user']);
			$sheet->setCellValue('H'.$i, $v['daily_trial_count']);
			$sheet->setCellValue('I'.$i, $v['daily_start_count']);
			$sheet->setCellValue('J'.$i, $v['weekly_active_user']);
			$sheet->setCellValue('K'.$i, $v['monthly_active_user']);
			$sheet->setCellValue('L'.$i, $v['accumulated_active_user']);
			$sheet->setCellValue('M'.$i, $v['accumulated_active_device']);
			$sheet->setCellValue('N'.$i, $v['accumulated_play_time']);
			$sheet->setCellValue('O'.$i, date('Y-m-d',$v['update_time']));

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2), \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('每日区域报表');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth('20');


		//导出文件名
		$filename = '云游戏每日区域报表（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	//导出每日渠道报表
	public function pidchart(){
		set_time_limit(0);
		$db = M('stat_pid');
			
		//筛选
		$pid=I('pid','');
		$startdate=I('startdate','');
		$enddate=I('enddate','');

		$where = '1=1 ';
		if($pid && $pid!='渠道'){
			$where .= ' and `pid` like \'%'.$pid.'%\'';
		}
		if($startdate && $startdate!="开始时间"){
			$where .= ' and date >= \''.$startdate.'\'';
		}
		if($enddate && $enddate!="结束时间"){
			$where .= ' and date <= \''.$enddate.'\'';
		}
		//查询结果
		$game = $db->where($where)->order('date DESC')->select();
		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '日期');
		$sheet->setCellValue('B1', '渠道');
		$sheet->setCellValue('C1', '活跃设备');
		$sheet->setCellValue('D1', '活跃用户');
		$sheet->setCellValue('E1', '新增设备');
		$sheet->setCellValue('F1', '新增用户');
		$sheet->setCellValue('G1', '新增活跃');
		$sheet->setCellValue('H1', '总游戏时间');
		$sheet->setCellValue('I1', '新增游戏时间');
		$sheet->setCellValue('J1', '最高在线');
		$sheet->setCellValue('K1', '启动游戏次数');
		$sheet->setCellValue('L1', '新增启动游戏次数');
		$sheet->setCellValue('M1', '试玩次数');
		$sheet->setCellValue('N1', '新增试玩次数');
		$sheet->setCellValue('O1', '按次游戏次数');
		$sheet->setCellValue('P1', '新增按次游戏次数');
		$sheet->setCellValue('Q1', '购买包月的次数');
		$sheet->setCellValue('R1', '新增购买包月的次数');
		$sheet->setCellValue('S1', '最近7天活跃');
		$sheet->setCellValue('T1', '最近30天活跃');
		$sheet->setCellValue('U1', '历史累计活跃用户');
		$sheet->setCellValue('V1', '历史累计活跃设备');
		$sheet->setCellValue('W1', '历史累计游戏时间');
		$sheet->setCellValue('X1', '更新时间');

		$i=1;
		foreach($game as $v){
			$i++;
			$sheet->setCellValue('A'.$i, substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2));
			$sheet->setCellValue('B'.$i, $v['pid']);
			$sheet->setCellValue('C'.$i, $v['daily_active_device']);
			$sheet->setCellValue('D'.$i, $v['daily_active_user']);
			$sheet->setCellValue('E'.$i, $v['daily_new_device']);
			$sheet->setCellValue('F'.$i, $v['daily_new_account']);
			$sheet->setCellValue('G'.$i, $v['daily_new_active_user']);
			$sheet->setCellValue('H'.$i, $v['daily_play_time']);
			$sheet->setCellValue('I'.$i, $v['daily_new_play_time']);
			$sheet->setCellValue('J'.$i, $v['max_concurrent_user']);
			$sheet->setCellValue('K'.$i, $v['daily_start_count']);
			$sheet->setCellValue('L'.$i, $v['daily_new_start_count']);
			$sheet->setCellValue('M'.$i, $v['daily_trial_count']);
			$sheet->setCellValue('N'.$i, $v['daily_new_trial_count']);
			$sheet->setCellValue('O'.$i, $v['daily_times_nums']);
			$sheet->setCellValue('P'.$i, $v['daily_new_times_nums']);
			$sheet->setCellValue('Q'.$i, $v['daily_payment_nums']);
			$sheet->setCellValue('R'.$i, $v['daily_new_payment_nums']);
			$sheet->setCellValue('S'.$i, $v['weekly_active_user']);
			$sheet->setCellValue('T'.$i, $v['monthly_active_user']);
			$sheet->setCellValue('U'.$i, $v['accumulated_active_user']);
			$sheet->setCellValue('V'.$i, $v['accumulated_active_device']);
			$sheet->setCellValue('W'.$i, $v['accumulated_play_time']);
			$sheet->setCellValue('X'.$i, date('Y-m-d',$v['update_time']));

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2), \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('每日渠道报表');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth('20');

		//导出文件名
		$filename = '云游戏每日渠道报表（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//导入信息
	public function avatar()
	{
		import("Org.Util.PHPExcel");
		/*获取Excel文件类型，确定版本*/
		$file='./html/upload/avatar.xlsx';
		$extend=pathinfo($file);
		$extend = strtolower($extend["extension"]);
		$extend=='xlsx'?$reader_type='Excel2007':$reader_type='Excel5';
		$objReader = \PHPExcel_IOFactory::createReader($reader_type);
		if(!$objReader){
			$this->error('抱歉！excel文件不兼容。'); //执行失败，直接抛出错误中断
		}
		$objPHPExcel= $objReader->load($file);
		$objWorksheet= $objPHPExcel->getActiveSheet();
		$highestRow= $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
		$headtitle =array();
		for($cols =0 ;$cols<=$highestColumnIndex;$cols++){
			$headtitle[$cols] =(string)$objWorksheet->getCellByColumnAndRow($cols, 1)->getValue();
		}
		if(empty($headtitle[0])){
			for($cols =0 ;$cols<=$highestColumnIndex;$cols++){
				$headtitle[$cols] =(string)$objWorksheet->getCellByColumnAndRow($cols, 2)->getValue();
			}
		}
		$strs=array();
		/*第二行开始读取*/
		for ($row=2;$row <= $highestRow;$row++){
			for($cols=0;$cols<$highestColumnIndex;$cols++){
				$strs[$row][$cols] =urldecode(urldecode((string)$objWorksheet->getCellByColumnAndRow($cols, $row)->getValue()));
			}
		}
		//var_dump($strs);//显示结果
		//exit;
		$sql="INSERT INTO `july_avatar`(`type`,`name`)VALUES";
		foreach($strs as $str){
			$sql.="('2','{$str[0]}'),";
		}
		$sql=substr($sql,0,-1);
		$m=new Model();
		$m->execute($sql);
	}

	//导入硬件信息
	public function hardware()
	{
		import("Org.Util.PHPExcel");
		/*获取Excel文件类型，确定版本*/
		$file='./html/upload/hardware.xlsx';
		$extend=pathinfo($file);
		$extend = strtolower($extend["extension"]);
		$extend=='xlsx'?$reader_type='Excel2007':$reader_type='Excel5';
		$objReader = \PHPExcel_IOFactory::createReader($reader_type);
		if(!$objReader){
			$this->error('抱歉！excel文件不兼容。'); //执行失败，直接抛出错误中断
		}
		$objPHPExcel= $objReader->load($file);
		$objWorksheet= $objPHPExcel->getActiveSheet();
		$highestRow= $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
		$headtitle =array();
		for($cols =0 ;$cols<=$highestColumnIndex;$cols++){
			$headtitle[$cols] =(string)$objWorksheet->getCellByColumnAndRow($cols, 1)->getValue();
		}
		if(empty($headtitle[0])){
			for($cols =0 ;$cols<=$highestColumnIndex;$cols++){
				$headtitle[$cols] =(string)$objWorksheet->getCellByColumnAndRow($cols, 2)->getValue();
			}
		}
		$strs=array();
		/*第二行开始读取*/
		for ($row=2;$row <= $highestRow;$row++){
			for($cols=0;$cols<$highestColumnIndex;$cols++){
				$strs[$row][$cols] =urldecode(urldecode((string)$objWorksheet->getCellByColumnAndRow($cols, $row)->getValue()));
			}
		}
		//        dump($strs);//显示结果
		//        exit;
		$sql="INSERT INTO `july_hardware`(`hardware`,`product`,`model`,`manu`,`type`,`device_name`)VALUES";
		foreach($strs as $str)
		{
			switch($str[4])
			{
				case '手机':
					$str[4]=1;
					break;
				case 'Pad':
					$str[4]=2;
					break;
				case '盒子':
					$str[4]=3;
					break;
				case 'TV':
					$str[4]=4;
					break;
				case '电视':
					$str[4]=4;
					break;
				case '掌机':
					$str[4]=5;
					break;
				case '游戏机':
					$str[4]=5;
					break;
				default:
					$str[4]=0;
					break;
			}
			$sql.="('{$str[0]}','{$str[1]}','{$str[2]}','{$str[3]}','{$str[4]}','{$str[5]}'),";
		}
		$sql=substr($sql,0,-1);
		$m=new Model();
		$m->execute($sql);
	}


	//导出硬件信息
	public function hardware_ex()
	{
		$db = M('hardware');
			
		//查询结果
		$hardware = $db->select();

		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', 'hardware(硬件)');
		$sheet->setCellValue('B1', 'product(产品)');
		$sheet->setCellValue('C1', 'model(型号)');
		$sheet->setCellValue('D1', 'manu(制造商)');
		$sheet->setCellValue('E1', 'type(类别)');
		$sheet->setCellValue('F1', 'device_name(设备名称)');

		foreach($hardware as $key=>$v)
		{
			switch($v['type'])
			{
				case '0':
					$hardware[$key]['type']='未知';
					break;
				case '1':
					$hardware[$key]['type']='手机';
					break;
				case '2':
					$hardware[$key]['type']='Pad';
					break;
				case '3':
					$hardware[$key]['type']='盒子';
					break;
				case '4':
					$hardware[$key]['type']='TV';
					break;
				case '5':
					$hardware[$key]['type']='掌机';
					break;
			}
		}
		$i=1;
		foreach($hardware as $v){
			$i++;
			$sheet->setCellValue('A'.$i, $v['hardware']);
			$sheet->setCellValue('B'.$i, $v['product']);
			$sheet->setCellValue('C'.$i, $v['model']);
			$sheet->setCellValue('D'.$i, $v['manu']);
			$sheet->setCellValue('E'.$i, $v['type']);
			$sheet->setCellValue('F'.$i, $v['device_name']);

			//设置文本格式
			//$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,substr($v['date'],0,4).'-'.substr($v['date'],4,2).'-'.substr($v['date'],6,2), PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('硬件信息报表');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');

		//导出文件名
		$filename = '云游戏硬件信息报表('.date('Ymd').'导出)';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	public function rechargecardcount(){

	}


	/*导出已售充值卡*/
	public function soldcode(){

		$this->type = I('type','');
		$this->card_id = I('card_id','');
		$this->valid = I('valid',0);
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		$this->subpid = I('subpid','');
		$this->dealer = I('dealer','');
		$this->sales = I('sales','');

		//基础联表条件
		$where = 'p.chargepoint_id=c.id and p.chargepoint_id=g.chargepoint_id and p.subpid=a.id and d.id=p.pid';

		//如果为游戏中心用户则看分渠道查看
		if(cookie('dealer_id')==1){
			if($this->dealer){
				$where .= ' and p.pid='.$this->dealer;
			}else{
				$where .= ' and p.pid>=10 and p.pid!=99';
			}
		}else{
			if($this->subpid && $this->subpid!=cookie('userid')){
				$users = Userrelation($this->subpid).$this->subpid;
			}else{
				$users = Userrelation(cookie('userid')).cookie('userid');
			}

			$where .= ' and p.subpid in ('.$users.') and p.pid='.cookie('dealer_id');
		}


		if($this->type==1){
			$where .= ' and p.charge_time > 0';
		}elseif($this->type==2){
			$where .= ' and p.charge_time = 0';
		}
		if($this->card_id && $this->card_id!='卡号'){
			$where .= ' and p.card_id = \''.$this->card_id.'\'';
		}
		if($this->sales && $this->sales!='售卡人'){
			$where .= ' and p.sales like \'%'.$this->sales.'%\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and p.valid_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and p.valid_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}

		$db = M('payment_card');
		//联表
		$table = array('july_payment_card'=>'p','july_chargepoint'=>'c','july_chargepoint_gamepack'=>'g','july_admins'=>'a','july_dealer'=>'d');
		//字段
		$field = 'p.id,p.batch_id,p.card_id,p.create_time,p.expire_time,p.chargepoint_id,p.charge_to_account_id,p.charge_time,p.pid,p.subpid,p.sales,p.remarks,p.valid,p.valid_time,p.valid_date,c.id,c.name,a.id,a.nickname,g.id,g.chargepoint_id,g.left_seconds_increase,g.deadline_time_increase,d.id,d.dealer_name';
		//排序
		$order = 'p.valid_time desc';

		//查表
		$rechargecardlist = $db->table($table)->field($field)->where($where)->order($order)->select();

		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '充值卡');
		$sheet->setCellValue('B1', '充值卡类型');
		$sheet->setCellValue('C1', '渠道');
		$sheet->setCellValue('D1', '激活者');
		$sheet->setCellValue('E1', '售卡人');
		$sheet->setCellValue('F1', '激活时间');
		$sheet->setCellValue('G1', '是否使用');
		$sheet->setCellValue('H1', '使用时间');
		$sheet->setCellValue('I1', '备注');

		$i=1;
		foreach($rechargecardlist as $v){
			$i++;

			$shiyong = $v['charge_to_account_id']?'已使用':'';
			$shiyongtime = $v['charge_to_account_id']?date('Y-m-d',$v['charge_time']):'';

			$sheet->setCellValue('A'.$i, $v['card_id']);
			$sheet->setCellValue('B'.$i, $v['name']);
			$sheet->setCellValue('C'.$i, $v['dealer_name']);
			$sheet->setCellValue('D'.$i, $v['nickname']);
			$sheet->setCellValue('E'.$i, $v['sales']);
			$sheet->setCellValue('F'.$i, date('Y-m-d',$v['valid_time']));
			$sheet->setCellValue('G'.$i, $shiyong);
			$sheet->setCellValue('H'.$i, $shiyongtime);
			$sheet->setCellValue('I'.$i, $v['remarks']);

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,$v['card_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('充值卡');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('100');

		//导出文件名
		$filename = '已售充值卡（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}


	/*导出特俗卡记录*/
	public function exchange()
	{
		ini_set('memory_limit','256M');
		set_time_limit(120);
			
		$this->pid = I('pid','');
		$this->cid = I('cid','');
		$this->tid = I('tid','');
		$this->type_mark = I('type_mark','');
		$this->card_pass = I('card_pass','');
		$this->source = I('source','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = '1=1';
		if($this->pid){
			$where .= ' and ec.pid = \''.$this->pid.'\'';
		}
		if($this->cid){
			$where .= ' and et.chargepoint_id = \''.$this->cid.'\'';
		}
		if($this->tid)
		{
			$where .= ' and ec.type_id = \''.$this->tid.'\'';
		}
		if($this->type_mark)
		{
			$where .= ' and et.type_mark = \''.$this->type_mark.'\'';
		}
		if($this->source && $this->source!='来源'){
			$where .= ' and et.source like \'%'.$this->source.'%\'';
		}
		if($this->card_pass && $this->card_pass!='卡号'){
			$where .= ' and ec.card_pass  = \''.$this->card_pass.'\'';
		}
		if($this->startdate && $this->startdate!='开启时间'){
			$where .= ' and et.valid_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='过期时间'){
			$where .= ' and et.expire_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db = M('exchange_code');
		$exchangelist = $db->table('july_exchange_code as ec')
		->join('LEFT JOIN july_exchange_type as et on ec.type_id=et.type_id')
		->join('LEFT JOIN july_chargepoint c on et.chargepoint_id=c.id')
		->join('LEFT JOIN july_dealer d on d.id=ec.pid')
		->field('ec.id,ec.card_id,ec.card_pass,et.type_name,et.type_mark,d.dealer_name,et.source,et.valid_time,et.expire_time,c.name,ec.num,ec.surplus_num')
		->where($where)->order('ec.create_time desc')->select();
		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', 'ID');
		$sheet->setCellValue('B1', '特殊卡卡号');
		$sheet->setCellValue('C1', '特殊卡卡密');
		$sheet->setCellValue('D1', '类别');
		$sheet->setCellValue('E1', '类别标识');
		$sheet->setCellValue('F1', '渠道');
		$sheet->setCellValue('G1', '来源');
		$sheet->setCellValue('H1', '开启时间');
		$sheet->setCellValue('I1', '过期时间');
		$sheet->setCellValue('J1', '计费点');
		$sheet->setCellValue('K1', '可使用次数');
		$sheet->setCellValue('L1', '剩余使用次数');

		$i=1;
		foreach($exchangelist as $v){
			$i++;
			$sheet->setCellValue('A'.$i, $v['id']);
			$sheet->setCellValue('B'.$i, $v['card_id']);
			$sheet->setCellValue('C'.$i, $v['card_pass']);
			$sheet->setCellValue('D'.$i, $v['type_name']);
			$sheet->setCellValue('E'.$i, $v['type_mark']==1?"一码多次":"多码多次");
			$sheet->setCellValue('F'.$i, $v['dealer_name']);
			$sheet->setCellValue('G'.$i, $v['source']);
			$sheet->setCellValue('I'.$i, date('Y-m-d',$v['valid_time']));
			$sheet->setCellValue('I'.$i, date('Y-m-d',$v['expire_time']));
			$sheet->setCellValue('J'.$i, $v['name']);
			$sheet->setCellValue('K'.$i, $v['num']);
			$sheet->setCellValue('L'.$i, $v['surplus_num']);

			//设置文本格式
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$i,$v['card_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$i,$v['card_pass'], \PHPExcel_Cell_DataType::TYPE_STRING);
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('特殊卡记录');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('20');
		//导出文件名
		$filename = '特殊卡（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/*特殊卡使用记录导出*/
	public function exchange_record()
	{
		ini_set('memory_limit','256M');
		set_time_limit(120);

		$this->type_id=I("type_id",'');
		$this->card_pass=I("card_pass",'');
		$this->device_uuid=I('device_uuid','');
		$this->pid=I('pid','');
		$where="1=1";
		if($this->type_id)
		{
			$where.=' and er.type_id=\''.$this->type_id.'\'';
		}
		if($this->card_pass && $this->card_pass!='卡号')
		{
			$where.=' and ec.card_pass=\''.$this->card_pass.'\'';
		}
		if($this->device_uuid && $this->device_uuid!='设备UUID')
		{
			$where.=' and er.device_uuid=\''.$this->device_uuid.'\'';
		}
		if($this->pid){
			$where.=' and ec.pid=\''.$this->pid.'\'';
		}
		$db=M("exchange_record");
		$recordlist = $db->table("july_exchange_record as er")
		->join("LEFT JOIN july_exchange_code as ec on er.code_id=ec.id")
		->join("LEFT JOIN july_exchange_type as et on et.type_id=er.type_id")
		->join("LEFT JOIN july_account as a on a.id=er.account_id")
		->join("LEFT JOIN july_dealer as d on ec.pid = d.id")
		->field("er.id,d.dealer_name,ec.card_pass,et.type_name,et.type_mark,a.nickname as account_name,er.account_id,er.device_uuid,er.charge_time")
		->where($where)->order("er.charge_time DESC,er.account_id DESC")->select();

		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', 'ID');
		$sheet->setCellValue('B1', '兑换券');
		$sheet->setCellValue('C1', '类别');
		$sheet->setCellValue('D1', '类别标识');
		$sheet->setCellValue('E1', '使用人ID');
		$sheet->setCellValue('F1', '使用人');
		$sheet->setCellValue('G1', '使用设备');
		$sheet->setCellValue('H1', '渠道');
		$sheet->setCellValue('I1', '使用时间');

		$i=1;
		foreach($recordlist as $v){
			$i++;
			$sheet->setCellValue('A'.$i, $v['id']);
			$sheet->setCellValue('B'.$i, $v['card_pass']);
			$sheet->setCellValue('C'.$i, $v['type_name']);
			$sheet->setCellValue('D'.$i, $v['type_mark']==1?"一码多次":"多码多次");
			$sheet->setCellValue('E'.$i, $v['account_id']);
			$sheet->setCellValue('F'.$i, $v['account_name']);
			$sheet->setCellValue('G'.$i, $v['device_uuid']);
			$sheet->setCellValue('H'.$i, $v['dealer_name']);
			$sheet->setCellValue('I'.$i, date('Y-m-d',$v['charge_time']));
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('特殊卡使用记录');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('50');
		//导出文件名
		$filename = '特殊卡使用记录（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}


	/*导出账户信息*/
	public function account()
	{
		ini_set('memory_limit','512M');
		set_time_limit(120);

		$this->nickname = I('nickname','');
		$this->id = I('id',0);
		$this->device_uuid=I('device_uuid','');
		$this->bind_phone = I('bind_phone',0);
		$this->bind_email = I('bind_email',0);
		$this->level = I('level',0);
		//查询条件
		$where = '1=1 ';
		if($this->id && $this->id!='ID'){
			$where .= ' and july_account.`id` = \''.$this->id.'\'';
		}
		if($this->device_uuid && $this->device_uuid!='设备UUID'){
			$where .= ' and d.`device_uuid` = \''.$this->device_uuid.'\'';
		}
		if($this->nickname && $this->nickname!='昵称'){
			$where .= ' and july_account.`nickname` like \'%'.$this->nickname.'%\'';
		}
		if($this->bind_phone && $this->bind_phone!='手机'){
			$where .= ' and july_account.`bind_phone` like \'%'.$this->bind_phone.'%\'';
		}
		if($this->bind_email && $this->bind_email!='邮箱'){
			$where .= ' and july_account.`bind_email` like \'%'.$this->bind_email.'%\'';
		}
		if($this->level && $this->level!='级别'){
			$where .= ' and july_account.`level` = \''.$this->level.'\'';
		}

		$db = M('account');
		$Accountlist = $db->field("july_account.*,d.device_uuid,d.client_type")
		->join("LEFT JOIN july_device d on july_account.id=d.bind_account")
		->where($where)->order('july_account.id desc')->select();
		//开始准备导出
		import("Org.Util.PHPExcel");
		$objPHPExcel = new \PHPExcel();

		//数据
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		$sheet->setCellValue('A1', '账户ID');
		$sheet->setCellValue('B1', '是否启用');
		$sheet->setCellValue('C1', '昵称');
		$sheet->setCellValue('D1', '手机');
		$sheet->setCellValue('E1', '邮箱');
		$sheet->setCellValue('F1', '级别');
		$sheet->setCellValue('G1', '经验');
		$sheet->setCellValue('H1', '云贝');
		$sheet->setCellValue('I1', '云豆');
		$sheet->setCellValue('J1', 'G币');
		$sheet->setCellValue('K1', '累计消耗云豆');
		$sheet->setCellValue('L1', '累计消耗云贝');
		$sheet->setCellValue('M1', '累计消耗G币');
		$sheet->setCellValue('N1', '累计游戏时间');
		$sheet->setCellValue('O1', '设备类型');
		$sheet->setCellValue('P1', '创建日期');
		$sheet->setCellValue('Q1', '更新日期');

		$i=1;
		foreach($Accountlist as $v){
			$hour = str_pad(intval($v['total_play_time']/3600), 2, '0', STR_PAD_LEFT);
			$minute = str_pad(intval($v['total_play_time']%3600/60), 2, '0', STR_PAD_LEFT);
			$second = str_pad(intval($v['total_play_time']%60), 2, '0', STR_PAD_LEFT);
			$i++;
			$sheet->setCellValue('A'.$i, $v['id']);
			$sheet->setCellValue('B'.$i, $v['status']);
			$sheet->setCellValue('C'.$i, $v['nickname']);
			$sheet->setCellValue('D'.$i, $v['bind_phone']);
			$sheet->setCellValue('E'.$i, $v['bind_email']);
			$sheet->setCellValue('F'.$i, $v['level']);
			$sheet->setCellValue('G'.$i, $v['exp']);
			$sheet->setCellValue('H'.$i, $v['gift_coin_num']);
			$sheet->setCellValue('I'.$i, $v['bean']);
			$sheet->setCellValue('J'.$i, $v['gold']);
			$sheet->setCellValue('K'.$i, $v['used_bean_num']);
			$sheet->setCellValue('L'.$i, $v['used_coin_num']);
			$sheet->setCellValue('M'.$i, $v['used_gold_num']);
			$sheet->setCellValue('N'.$i, "$hour:$minute:$second");
			$sheet->setCellValue('O'.$i, $v['client_type']);
			$sheet->setCellValue('P'.$i, date('Y-m-d H:i:s',$v['create_time']));
			$sheet->setCellValue('Q'.$i, date('Y-m-d H:i:s',$v['update_time']));
		}

		//工作表名
		$objPHPExcel->getActiveSheet()->setTitle('账号信息记录');

		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

		//设置列宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth('50');

		//导出文件名
		$filename = '账号信息记录（'.date('Ymd').'导出）';

		//导出
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

}
