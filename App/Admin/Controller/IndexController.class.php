<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class IndexController extends BaseController {
	public function index(){
		$db = M('stat_daily');
		//云游戏近半月运营数据
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


		/**************************近半月销卡与用户开卡比例*****************************/
		/* 		$users = Userrelation(cookie('userid')).cookie('userid');
		 if(cookie('dealer_id')==1){
		$wheres= '`pid` >=10  and `pid` !=99  and  ';
		}else{
		$wheres= '`subpid` in ('.$users.')  and  ';
		} */

		//获取最近半月日期
		$days = array();
		$jihuoshu = array();
		$shiyongshu = array();
		for($i=0;$i<=14;$i++){
			//分日统计
			$dayswhere = $wheres.' `valid_date`='.date('Ymd',time()-86400*$i);
			$jihuo = M('payment_card')->where($dayswhere)->count();
			$shiyong = M('payment_card')->where($dayswhere.' and `charge_time`>0')->count();
			$jihuoshu[] = $jihuo;
			$shiyongshu[] = $shiyong;
			//每日
			$days[] = date('Ymd',time()-86400*$i);
		}
		$this->days = rtrim(implode(',',array_reverse($days)), ",");
		$this->jihuoshu = rtrim(implode(',',array_reverse($jihuoshu)), ",");
		$this->shiyongshu = rtrim(implode(',',array_reverse($shiyongshu)), ",");
		$this->display();
	}
	public function cc()
	{
		echo asdfasdf;
		exit;
	}
}
