<?php
namespace Admin\Controller;
use Home\Controller\BaseController;
use Think\Cache;
use Think\Log;
//API接口文件
set_time_limit(600);
class CronController extends BaseController{
	
	public function delete_old_game_save() {
		Log::write("delete_old_game_save", Log::INFO);
		$m_read = M('','','DB_CONFIG2');
		$m_write = M();
		
		// 一、7天之前的、未上传的存档，将从数据库中删除。
		$sql = "SELECT COUNT(*) as old_not_upload FROM july_game_save WHERE upload_time=0 AND compressed_size = 0 AND deletable=1 AND create_time < UNIX_TIMESTAMP() - 86400*7";
		$ret = $m_read->query($sql);
		$old_not_upload = $ret[0]['old_not_upload'];
		Log::write("old and not uploaded save count: ".$old_not_upload, Log::INFO);
		if ($old_not_upload > 0) {
			$sql = "DELETE FROM july_game_save WHERE upload_time=0 AND compressed_size = 0 AND deletable=1 AND create_time < UNIX_TIMESTAMP() - 86400*7";
			$ret = $m_write->execute($sql);
			Log::write("old and not uploaded save deleted: ".$old_not_upload." ret: ".$ret, Log::INFO);
		}
		
		Log::write("connecting to aliyun oss...", Log::INFO);
		$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$client = $ret['msg'];
		
		// 二、7天之前的、标记为删除的存档，将从数据库中删除，并且从OSS上删除。
		$sql = "SELECT COUNT(*) as old_deleted FROM july_game_save WHERE delete_time!=0 AND deletable=1 AND create_time < UNIX_TIMESTAMP() - 86400*7";
		$ret = $m_read->query($sql);
		$old_deleted = $ret[0]['old_deleted'];
		Log::write("old and deleted save count: ".$old_deleted, Log::INFO);
		
		// 不追求一次删除完，每次运行最多删除TURNS*BATCH_COUNT个。
		if ($old_deleted > 0) {
			$TURNS = 20;
			$BATCH_COUNT = 100;
			for ($i = 0; $i < $TURNS; $i++) {
				$sql = "SELECT * FROM july_game_save WHERE delete_time!=0 AND deletable=1 AND create_time < UNIX_TIMESTAMP() - 86400*7 LIMIT ".$BATCH_COUNT;
				$ret = $m_read->query($sql);
				Log::write("TURN $i. Batch delete: ".count($ret), Log::INFO);
				if (count($ret) == 0)
					break;
				foreach ($ret as $row) {
					// 阿里云OSS上的路径
					$accountid = $row['account_id'];
					$gameid = $row['game_id'];
					$serialid = $row['serial_id'];
					$saveid = $row['id'];
					$compressed_md5 = $row['compressed_md5'];
					$compressedname = $saveid."_".strtolower($compressed_md5).".save";;
					$key = "u"."/".intval($accountid/1000000000)."/".intval($accountid/1000000)."/".intval($accountid/1000)."/".$accountid."/".$gameid."/".$serialid."/".$compressedname;
				
					// 从阿里云OSS上删除（如果存在的话）
					$ret = $this->checkObject($client, C("OSS_UDS_BUCKET"), $key);
					if ($ret['ret'] !== 0 && strpos($ret['msg'], 'NoSuchKey') === FALSE)
						return $this->respond(-111, "OSS上未找到: ".$key." ret:".$ret['ret']." msg:".$ret['msg']);
					if (strpos($ret['msg'], 'NoSuchKey') === FALSE) {
						$ret = $this->deleteObject($client, C("OSS_UDS_BUCKET"), $key);
						if ($ret['ret'] !== 0)
							return $this->respond(-112, "OSS删除失败: ".$key." ret:".$ret['ret']." msg:".$ret['msg']);
						Log::write("delete from OSS: ".$key." ret: ".$ret['ret']." msg: ".$ret['msg'], Log::INFO);
					}
					else
						Log::write("delete from OSS: ".$key." NoSuchKey on OSS.", Log::INFO);
					
					// 从数据库中删除
					$sql = "DELETE FROM july_game_save WHERE id=".$row['id'];
					$ret = $m_write->execute($sql);
					Log::write("delete from DB: ".$row['id']." ret: ".$ret, Log::INFO);
				}
			}
		}
		
		// 三、同一存档序列中最近20个之外的存档，将从数据库中删除，并且从OSS上删除。
		// 不追求一次删除完，每次运行最多检查TURNS*BATCH_COUNT个序列，删除其中多余的存档。
		Log::write("begin to delete extra game saves in each serial...", Log::INFO);
		$MAX_SAVE_PER_SERIAL = 30;
		$TURNS = 10;
		$BATCH_COUNT = 50;
		for ($i = 0; $i < $TURNS; $i++) {
			// 找出超出$MAX_SAVE_PER_SERIAL个的存档序列
			$sql = "SELECT account_id,game_id,serial_id,COUNT(*) c FROM july_game_save WHERE deletable=1 GROUP BY serial_id HAVING COUNT(*) > $MAX_SAVE_PER_SERIAL LIMIT ".$BATCH_COUNT;
			$ret = $m_read->query($sql);
			Log::write("TURN $i. Batch: ".count($ret), Log::INFO);
			if (count($ret) == 0)
				break;
			foreach ($ret as $serial) {
				$tobedelete = $serial['c'] - $MAX_SAVE_PER_SERIAL;
				Log::write("SERIAL: ".$serial['serial_id']." GAME: ".$serial['game_id']." ACCOUNT: ".$serial['account_id']." COUNT: ".$serial['c']." TO_BE_DELTE: ".$tobedelete, Log::INFO);
				if ($tobedelete <= 0)
					continue;
				// 找出需要删除的前N个存档
				$sql = "SELECT * FROM july_game_save WHERE deletable=1 AND serial_id=".$serial['serial_id']." ORDER BY id LIMIT ".$tobedelete;
				$ret = $m_read->query($sql);
				foreach ($ret as $row) {
					$accountid = $row['account_id'];
					$gameid = $row['game_id'];
					$serialid = $row['serial_id'];
					$saveid = $row['id'];
					$compressed_md5 = $row['compressed_md5'];
					$compressedname = $saveid."_".strtolower($compressed_md5).".save";;
					$key = "u"."/".intval($accountid/1000000000)."/".intval($accountid/1000000)."/".intval($accountid/1000)."/".$accountid."/".$gameid."/".$serialid."/".$compressedname;
				
					// 从阿里云OSS上删除（如果存在的话）
					$ret = $this->checkObject($client, C("OSS_UDS_BUCKET"), $key);
					if ($ret['ret'] !== 0 && strpos($ret['msg'], 'NoSuchKey') === FALSE)
						return $this->respond(-111, "OSS上未找到: ".$key." ret:".$ret['ret']." msg:".$ret['msg']);
					if (strpos($ret['msg'], 'NoSuchKey') === FALSE) {
						$ret = $this->deleteObject($client, C("OSS_UDS_BUCKET"), $key);
						if ($ret['ret'] !== 0)
							return $this->respond(-112, "OSS删除失败: ".$key." ret:".$ret['ret']." msg:".$ret['msg']);
						Log::write("delete from OSS: ".$key." ret: ".$ret['ret']." msg: ".$ret['msg'], Log::INFO);
					}
					else
						Log::write("delete from OSS: ".$key." NoSuchKey on OSS.", Log::INFO);
					
					// 从数据库中删除
					$sql = "DELETE FROM july_game_save WHERE id=".$row['id'];
					$ret = $m_write->execute($sql);
					Log::write("delete from DB: ".$row['id']." ret: ".$ret, Log::INFO);
				}
			}
		}
		
		return $this->respond(0, "success");
	}
	
    //每日统计
	public function daily_stat() {
		Log::write("daily_stat", Log::INFO);

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
		
		$m=M('','','DB_CONFIG2');
		
		$day_begin = $start_time;
		while ($day_begin < $end_time) {
			$day_date = date("Ymd", $day_begin);
			$day_end = $day_begin + 86400;
			
			$daily_new_account = 0;
			$daily_new_device = 0;
			$daily_new_valid_account = 0;
			$max_concurrent_user = 0;
			$daily_play_time = 0;
			$daily_used_coin = 0;
			$daily_trial_count = 0;
			$daily_console_game = 0;
			$daily_insert_coin = 0;
			$daily_active_user = 0;
			$daily_active_device = 0;
			$weekly_active_user = 0;
			$monthly_active_user = 0;
			$accumulated_active_user = 0;
			$accumulated_active_device = 0;
			$accumulated_play_time = 0;

			// daily_new_device
			$sql = "SELECT COUNT(id) as daily_new_device FROM july_device WHERE create_time >= $day_begin AND create_time < $day_end";
			$list = $m->query($sql);
			$daily_new_device = $list[0]['daily_new_device'];

			// daily_new_account
			$sql = "SELECT COUNT(id) as daily_new_account FROM july_account WHERE create_time >= $day_begin AND create_time < $day_end";
			$list = $m->query($sql);
			$daily_new_account = $list[0]['daily_new_account'];

			// daily_new_valid_account
			$sql = "SELECT COUNT(id) as daily_new_valid_account FROM july_account WHERE create_time >= $day_begin AND create_time < $day_end AND total_play_time > 0";
			$list = $m->query($sql);
			$daily_new_valid_account = $list[0]['daily_new_valid_account'];
            
            // max_concurrent_user
    		// 统计每天的最高在线
	        $arr=array();
	        $sql ="SELECT id,gs_start_time,gs_last_report_time FROM july_history_account_game_time WHERE gs_start_time > $day_begin and gs_last_report_time < $day_end AND is_online_gs=1";
			$arr = $m->query($sql);
	        
    		$tt = $day_begin;
    		while ($tt < $day_end) {
                $count=0;
                foreach($arr as $v){
                   if($v['gs_start_time'] < $tt && $v['gs_last_report_time'] > $tt){
                    $count++;
                   }
                }
                if(empty($max_concurrent_user)){
                    $max_concurrent_user=0;
                }
                $max_concurrent_user = max($max_concurrent_user,$count);
    			$tt += 120;
    		}
            
			// daily_play_time
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
			$sql = "SELECT IFNULL(SUM(gs_last_report_time-gs_start_time),0) as daily_play_time FROM july_history_account_game_time WHERE create_time >= $day_begin AND create_time < $day_end AND is_online_gs=1 AND gs_last_report_time-gs_start_time<86400";
			$list = $m->query($sql);
			$daily_play_time = $list[0]['daily_play_time'];
			
			// daily_used_coin
			$sql = "SELECT IFNULL(SUM(coin),0) as daily_used_coin FROM july_payment_coin WHERE create_time >= $day_begin AND create_time < $day_end";
			$list = $m->query($sql);
			$daily_used_coin = $list[0]['daily_used_coin'];
			
			// daily_used_income_coin
			$sql = "SELECT IFNULL(SUM(coin),0) as daily_used_income_coin FROM july_income_coin WHERE create_time >= $day_begin AND create_time < $day_end";
			$list = $m->query($sql);
			$daily_used_income_coin = $list[0]['daily_used_income_coin'];
				
			// daily_trial_count
			$sql = "SELECT COUNT(id) as daily_trial_count FROM july_history_account_game_time WHERE create_time >= $day_begin AND create_time < $day_end AND is_online_gs=1 AND payment_type=0";
			$list = $m->query($sql);
			$daily_trial_count = $list[0]['daily_trial_count'];
				
			// daily_console_game
			$sql = "SELECT COUNT(id) as daily_console_game FROM july_history_account_game_time WHERE create_time >= $day_begin AND create_time < $day_end AND is_online_gs=1 AND payment_type<>1 AND payment_type<>0";
			$list = $m->query($sql);
			$daily_console_game = $list[0]['daily_console_game'];
				
			// daily_insert_coin
			$sql = "SELECT COUNT(id) as daily_insert_coin FROM july_payment_coin WHERE create_time >= $day_begin AND create_time < $day_end AND payment_type = 1";
			$list = $m->query($sql);
			$daily_insert_coin = $list[0]['daily_insert_coin'];
				
			// daily_active_user
			$sql = "SELECT COUNT(DISTINCT account_id) as daily_active_user FROM july_history_account_game_time WHERE create_time >= $day_begin AND create_time < $day_end AND is_online_gs = 1";
			$list = $m->query($sql);
			$daily_active_user = $list[0]['daily_active_user'];
				
			// daily_active_device
			$sql = "SELECT COUNT(DISTINCT device_uuid) as daily_active_device FROM july_history_account_game_time WHERE create_time >= $day_begin AND create_time < $day_end AND is_online_gs = 1";
			$list = $m->query($sql);
			$daily_active_device = $list[0]['daily_active_device'];
				
			// weekly_active_user
			$week_begin = $day_end - 86400 * 7;
			$sql = "SELECT COUNT(DISTINCT account_id) as weekly_active_user FROM july_history_account_game_time WHERE create_time >= $week_begin AND create_time < $day_end AND is_online_gs=1";
			$list = $m->query($sql);
			$weekly_active_user = $list[0]['weekly_active_user'];
				
			// monthly_active_user
			$month_begin = $day_end - 86400 * 30;
			$sql = "SELECT COUNT(DISTINCT account_id) as monthly_active_user FROM july_history_account_game_time WHERE create_time >= $month_begin AND create_time < $day_end AND is_online_gs=1";
			$list = $m->query($sql);
			$monthly_active_user = $list[0]['monthly_active_user'];
				
			// accumulated_active_user
			$sql = "SELECT COUNT(DISTINCT account_id) as accumulated_active_user FROM july_history_account_game_time WHERE create_time < $day_end AND is_online_gs=1";
			$list = $m->query($sql);
			$accumulated_active_user = $list[0]['accumulated_active_user'];
				
			// accumulated_active_device
			$sql = "SELECT COUNT(DISTINCT device_uuid) as accumulated_active_device FROM july_history_account_game_time WHERE create_time < $day_end AND is_online_gs=1";
			$list = $m->query($sql);
			$accumulated_active_device = $list[0]['accumulated_active_device'];
			
			// accumulated_play_time
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
			$sql = "SELECT IFNULL(SUM(gs_last_report_time-gs_start_time), 0) as accumulated_play_time FROM july_history_account_game_time WHERE create_time < $day_end AND is_online_gs=1 AND gs_last_report_time-gs_start_time<86400";
			$list = $m->query($sql);
			$accumulated_play_time = $list[0]['accumulated_play_time'];
			
			$now = time();
			$sql = "REPLACE INTO 
			`july_stat_daily` (date,daily_new_account,daily_new_device,daily_new_valid_account,max_concurrent_user,daily_play_time,daily_used_coin,daily_used_income_coin,daily_trial_count,daily_console_game,daily_insert_coin,daily_active_user,daily_active_device,weekly_active_user,monthly_active_user,accumulated_active_user,accumulated_active_device,accumulated_play_time,update_time)
			VALUES($day_date, $daily_new_account, $daily_new_device, $daily_new_valid_account, $max_concurrent_user, $daily_play_time, $daily_used_coin, $daily_used_income_coin, $daily_trial_count, $daily_console_game, $daily_insert_coin, $daily_active_user,$daily_active_device,$weekly_active_user, $monthly_active_user, $accumulated_active_user, $accumulated_active_device, $accumulated_play_time, $now)";
			
			$m_pri=M();
			$m_pri->execute($sql);
		
			$day_begin += 86400;
		}
		
		return $this->respond(0, "success");
	}
    
    
    //每日游戏统计
    public function game_stat() {
		Log::write("game_stat", Log::INFO);
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
		
		$m=M('','','DB_CONFIG2');
		//	获取游戏
        $items=$m->table("july_game")->field('game_id')->select();
        
		$day_begin = $start_time;
		while ($day_begin < $end_time) {
			$day_date = date("Ymd", $day_begin);
			$day_end = $day_begin + 86400;

            $val = array();
            
			// daily_active_user 当日活跃用户
            // daily_play_time  当日总游戏时间
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
            $sql="SELECT game_id,COUNT(DISTINCT account_id) as daily_active_user,IFNULL(SUM(gs_last_report_time-gs_start_time),0) as daily_play_time FROM july_history_account_game_time ";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end AND is_online_gs=1 AND gs_last_report_time-gs_start_time<86400";
            $sql.=" group by game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['daily_active_user'] = $l['daily_active_user'];
                $val[$day_date."_".$l['game_id']]['daily_play_time'] = $l['daily_play_time'];
            }
            
			// max_concurrent_user
			// 统计每天的最高在线
            $arr=array();
            foreach($items as $item)
            {
                $sql ="SELECT game_id,id,gs_start_time,gs_last_report_time FROM july_history_account_game_time  WHERE game_id={$item['game_id']} and gs_start_time > {$day_begin} and gs_last_report_time < {$day_end} and is_online_gs=1";
                $arr[$item['game_id']]=$m->query($sql);
            }
            $tt = $day_begin;
    		while ($tt < $day_end){
                foreach($arr as $key=>$value)
                {
                    $count=0;
                    foreach($value as $v)
                    {
                       if($v['gs_start_time'] < $tt && $v['gs_last_report_time'] > $tt)
                       {
                        $count++;
                       }
                    }
                    $val[$day_date."_".$key]['date'] = $day_date;
                    $val[$day_date."_".$key]['game_id'] = $key;
                    if(empty($val[$day_date."_".$key]['max_concurrent_user']))
                    {
                        $val[$day_date."_".$key]['max_concurrent_user']=0;
                    }
                    $val[$day_date."_".$key]['max_concurrent_user']=max($val[$day_date."_".$key]['max_concurrent_user'],$count);
                }
    			$tt += 120;
    		}

			// daily_trial_count 当日试玩次数
            $sql="SELECT game_id,COUNT(DISTINCT id) as daily_trial_count FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end AND is_online_gs=1 AND payment_type=0";
            $sql.=" GROUP BY game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['daily_trial_count']=$l['daily_trial_count'];
            }
				
			// daily_start_count 当日游戏启动次数
            $sql="SELECT game_id,COUNT(DISTINCT id) as daily_start_count FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end AND is_online_gs=1 AND payment_type<>1 AND payment_type<>0";
            $sql.=" group by game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['daily_start_count']=$l['daily_start_count'];
            }
				
			// daily_active_device  当日活跃的设备
            $sql="SELECT game_id,COUNT(DISTINCT device_uuid) as daily_active_device FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end AND is_online_gs=1";
            $sql.=" group by game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['daily_active_device']=$l['daily_active_device'];
            }
				
			// weekly_active_user  最近7天活跃用户
			$week_begin = $day_end - 86400 * 7;
            $sql="SELECT game_id,COUNT(DISTINCT account_id) as weekly_active_user FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$week_begin AND create_time<$day_end AND is_online_gs=1";
            $sql.=" GROUP BY game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['weekly_active_user']=$l['weekly_active_user'];
            }
				
			// monthly_active_user 最近30天活跃用户
			$month_begin = $day_end - 86400 * 30;
            $sql="SELECT game_id,COUNT(DISTINCT account_id) as monthly_active_user FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$month_begin AND create_time<$day_end AND is_online_gs=1";
            $sql.=" GROUP BY game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['monthly_active_user']=$l['monthly_active_user'];
            }
				
			// accumulated_active_user  历史累计活跃用户
            $sql="SELECT game_id,COUNT(DISTINCT account_id) as accumulated_active_user,COUNT(DISTINCT device_uuid) as accumulated_active_device,IFNULL(SUM(gs_last_report_time-gs_start_time), 0) as accumulated_play_time FROM july_history_account_game_time";
            $sql.=" WHERE create_time<$day_end AND is_online_gs=1 AND gs_last_report_time-gs_start_time<86400";
            $sql.=" GROUP BY game_id";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['game_id']]['date'] = $day_date;
                $val[$day_date."_".$l['game_id']]['game_id'] = $l['game_id'];
                $val[$day_date."_".$l['game_id']]['accumulated_active_user']=$l['accumulated_active_user'];
                $val[$day_date."_".$l['game_id']]['accumulated_active_device']=$l['accumulated_active_device'];
                $val[$day_date."_".$l['game_id']]['accumulated_play_time']=$l['accumulated_play_time'];
            }
            //填充没有的字段值，避免sql语句报错	
            foreach($val as $key=>$row)
            {
                if(empty($row['daily_active_user']))
                {
                    $val[$key]['daily_active_user']=0;
                }
                if(empty($row['daily_play_time']))
                {
                    $val[$key]['daily_play_time']=0;
                }
                if(empty($row['max_concurrent_user']))
                {
                    $val[$key]['max_concurrent_user']=0;
                }
                if(empty($row['daily_trial_count']))
                {
                    $val[$key]['daily_trial_count']=0;
                }
                if(empty($row['daily_start_count']))
                {
                    $val[$key]['daily_start_count']=0;
                }
                if(empty($row['daily_active_device']))
                {
                    $val[$key]['daily_active_device']=0;
                }
                if(empty($row['weekly_active_user']))
                {
                    $val[$key]['weekly_active_user']=0;
                }
                if(empty($row['monthly_active_user']))
                {
                    $val[$key]['monthly_active_user']=0;
                }
                if(empty($row['accumulated_active_user']))
                {
                    $val[$key]['accumulated_active_user']=0;
                }
                if(empty($row['accumulated_active_device']))
                {
                    $val[$key]['accumulated_active_device']=0;
                }
                if(empty($row['accumulated_play_time']))
                {
                    $val[$key]['accumulated_play_time']=0;
                }
            }
			
			$now = time();
			$m_pri=M();
            foreach($val as $row)
            {
              $sql="REPLACE INTO `july_stat_game`(date,game_id,daily_active_user,daily_play_time,max_concurrent_user,daily_trial_count,daily_start_count,daily_active_device,weekly_active_user,monthly_active_user,accumulated_active_user,accumulated_active_device,accumulated_play_time,update_time)";
        	  $sql.="VALUES({$day_date},{$row['game_id']},{$row['daily_active_user']},{$row['daily_play_time']},{$row['max_concurrent_user']},{$row['daily_trial_count']},{$row['daily_start_count']},{$row['daily_active_device']},{$row['weekly_active_user']},{$row['monthly_active_user']},{$row['accumulated_active_user']},{$row['accumulated_active_device']},{$row['accumulated_play_time']},{$now})";
              $m_pri->execute($sql);
            }
			$day_begin += 86400;
		}
		return $this->respond(0, "success");
	}
    
    
     //每日区域统计
    public function region_stat() {
		Log::write("region_stat", Log::INFO);
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

		$m=M('','','DB_CONFIG2');
		//	获取渠道
        $items = $m->table("july_region")->field('id')->select();

		$day_begin = $start_time;
		while ($day_begin < $end_time) {
			$day_date = date("Ymd", $day_begin);
			$day_end = $day_begin + 86400;

            $val = array();
            
			// daily_active_user 当日活跃用户
            // daily_play_time  当日总游戏时间
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT account_id) as daily_active_user,IFNULL(SUM(gs_last_report_time-gs_start_time),0) as daily_play_time FROM july_history_account_game_time ";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1 AND gs_last_report_time-gs_start_time<86400";
            $sql.=" group by FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['daily_active_user'] = $l['daily_active_user'];
                $val[$day_date."_".$l['region_id']]['daily_play_time'] = $l['daily_play_time'];
            }
			// max_concurrent_user
			// 统计每天的最高在线
            $arr=array();
            foreach($items as $item)
            {
                $sql ="SELECT FLOOR(gs_id/1000000) as region_id,id,gs_start_time,gs_last_report_time FROM july_history_account_game_time  WHERE FLOOR(gs_id/1000000)={$item['id']} and gs_start_time > {$day_begin} and gs_last_report_time < {$day_end} and is_online_gs=1";
                $arr[$item['id']]=$m->query($sql);
            }
            $tt = $day_begin;
    		while ($tt < $day_end){
                foreach($arr as $key=>$value)
                {
                    $count=0;
                    foreach($value as $v)
                    {
                       if($v['gs_start_time'] < $tt && $v['gs_last_report_time'] > $tt)
                       {
                        $count++;
                       }
                    }
                    $val[$day_date."_".$key]['date'] = $day_date;
                    $val[$day_date."_".$key]['region_id'] = $key;
                    if(empty($val[$day_date."_".$key]['max_concurrent_user']))
                    {
                        $val[$day_date."_".$key]['max_concurrent_user']=0;
                    }
                    $val[$day_date."_".$key]['max_concurrent_user']=max($val[$day_date."_".$key]['max_concurrent_user'],$count);
                }
    			$tt += 120;
    		}

			// daily_trial_count 当日试玩次数
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT id) as daily_trial_count FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1 AND payment_type=0";
            $sql.=" GROUP BY FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['daily_trial_count']=$l['daily_trial_count'];
            }
				
			// daily_start_count 当日游戏启动次数
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT id) as daily_start_count FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1 AND payment_type<>1 AND payment_type<>0";
            $sql.=" group by FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['daily_start_count']=$l['daily_start_count'];
            }
				
			// daily_active_device  当日活跃的设备
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT device_uuid) as daily_active_device FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$day_begin AND create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1";
            $sql.=" group by FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['daily_active_device']=$l['daily_active_device'];
            }
				
			// weekly_active_user  最近7天活跃用户
			$week_begin = $day_end - 86400 * 7;
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT account_id) as weekly_active_user FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$week_begin AND create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1";
            $sql.=" GROUP BY FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['weekly_active_user']=$l['weekly_active_user'];
            }
				
			// monthly_active_user 最近30天活跃用户
			$month_begin = $day_end - 86400 * 30;
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT account_id) as monthly_active_user FROM july_history_account_game_time";
            $sql.=" WHERE create_time>=$month_begin AND create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1";
            $sql.=" GROUP BY FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['monthly_active_user']=$l['monthly_active_user'];
            }
				
			// accumulated_active_user  历史累计活跃用户
            $sql="SELECT FLOOR(gs_id/1000000) as region_id,COUNT(DISTINCT account_id) as accumulated_active_user,COUNT(DISTINCT device_uuid) as accumulated_active_device,IFNULL(SUM(gs_last_report_time-gs_start_time), 0) as accumulated_play_time FROM july_history_account_game_time";
            $sql.=" WHERE create_time<$day_end and FLOOR(gs_id/1000000)!=0 AND is_online_gs=1 AND gs_last_report_time-gs_start_time<86400";
            $sql.=" GROUP BY FLOOR(gs_id/1000000)";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['region_id']]['date'] = $day_date;
                $val[$day_date."_".$l['region_id']]['region_id'] = $l['region_id'];
                $val[$day_date."_".$l['region_id']]['accumulated_active_user']=$l['accumulated_active_user'];
                $val[$day_date."_".$l['region_id']]['accumulated_active_device']=$l['accumulated_active_device'];
                $val[$day_date."_".$l['region_id']]['accumulated_play_time']=$l['accumulated_play_time'];
            }
            //填充没有的字段值，避免sql语句报错	
            foreach($val as $key=>$row)
            {
                if(empty($row['daily_active_user']))
                {
                    $val[$key]['daily_active_user']=0;
                }
                if(empty($row['daily_play_time']))
                {
                    $val[$key]['daily_play_time']=0;
                }
                if(empty($row['max_concurrent_user']))
                {
                    $val[$key]['max_concurrent_user']=0;
                }
                if(empty($row['daily_trial_count']))
                {
                    $val[$key]['daily_trial_count']=0;
                }
                if(empty($row['daily_start_count']))
                {
                    $val[$key]['daily_start_count']=0;
                }
                if(empty($row['daily_active_device']))
                {
                    $val[$key]['daily_active_device']=0;
                }
                if(empty($row['weekly_active_user']))
                {
                    $val[$key]['weekly_active_user']=0;
                }
                if(empty($row['monthly_active_user']))
                {
                    $val[$key]['monthly_active_user']=0;
                }
                if(empty($row['accumulated_active_user']))
                {
                    $val[$key]['accumulated_active_user']=0;
                }
                if(empty($row['accumulated_active_device']))
                {
                    $val[$key]['accumulated_active_device']=0;
                }
                if(empty($row['accumulated_play_time']))
                {
                    $val[$key]['accumulated_play_time']=0;
                }
            }
            
			$now = time();
			$m_pri=M();
            foreach($val as $row)
            {
              $sql="REPLACE INTO `july_stat_region`(date,region_id,daily_active_user,daily_play_time,max_concurrent_user,daily_trial_count,daily_start_count,daily_active_device,weekly_active_user,monthly_active_user,accumulated_active_user,accumulated_active_device,accumulated_play_time,update_time)";
        	  $sql.="VALUES({$day_date},{$row['region_id']},{$row['daily_active_user']},{$row['daily_play_time']},{$row['max_concurrent_user']},{$row['daily_trial_count']},{$row['daily_start_count']},{$row['daily_active_device']},{$row['weekly_active_user']},{$row['monthly_active_user']},{$row['accumulated_active_user']},{$row['accumulated_active_device']},{$row['accumulated_play_time']},{$now})";
              $m_pri->execute($sql);
            }
			$day_begin += 86400;
		}
		
		return $this->respond(0, "success");
	}
    
    
     //每日区域统计
    public function pid_stat() {
		Log::write("pid_stat", Log::INFO);
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
			
		$m=M('','','DB_CONFIG2');
		
		//	获取渠道
        $device=M("device");
        $items=$device->field('pid')->where("pid!=''")->group("pid")->select();
        
		$day_begin = $start_time;
		while ($day_begin < $end_time) {
			$day_date = date("Ymd", $day_begin);
			$day_end = $day_begin + 86400;

            $val = array();
            
            // daily_new_device 当日新增的设备
            $sql="SELECT pid,COUNT(DISTINCT id) as daily_new_device FROM july_device where create_time >= $day_begin AND create_time < $day_end AND pid!='' GROUP BY pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date']=$day_date;
                $val[$day_date."_".$l['pid']]['pid']=$l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_device']=$l['daily_new_device'];
            }

			// daily_new_account 当日新增的用户
            $sql="SELECT d.pid,COUNT(DISTINCT a.id)as daily_new_account FROM july_account as a RIGHT JOIN july_device as d ON a.id=d.bind_account where a.create_time>=$day_begin AND a.create_time<$day_end AND d.pid!='' GROUP BY d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date']=$day_date;
                $val[$day_date."_".$l['pid']]['pid']=$l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_account']=$l['daily_new_account'];
            }
            
			// daily_active_user 当日活跃用户
            // daily_play_time  当日总游戏时间
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
            $sql="SELECT d.pid,COUNT(DISTINCT a.account_id) as daily_active_user,IFNULL(SUM(a.gs_last_report_time - a.gs_start_time),0) as daily_play_time";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time>=$day_begin AND a.create_time<$day_end AND d.pid!='' AND a.is_online_gs=1 AND a.gs_last_report_time - a.gs_start_time<86400";
            $sql.=" group by d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_active_user'] = $l['daily_active_user'];
                $val[$day_date."_".$l['pid']]['daily_play_time'] = $l['daily_play_time'];
            }
            
            // daily_active_user 当日活跃用户
            // daily_play_time  当日总游戏时间
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
            $sql="SELECT d.pid,COUNT(DISTINCT a.account_id) as daily_active_user,IFNULL(SUM(a.gs_last_report_time - a.gs_start_time),0) as daily_play_time";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time>=$day_begin AND a.create_time<$day_end AND d.pid!='' AND a.is_online_gs=1 AND a.gs_last_report_time - a.gs_start_time<86400";
            $sql.=" group by d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_active_user'] = $l['daily_active_user'];
                $val[$day_date."_".$l['pid']]['daily_play_time'] = $l['daily_play_time'];
            }
            
            // daily_new_active_user 当日新增活跃用户
            // daily_new_play_time  当日新增总游戏时间
			// 只有来自线上GS的，且游戏时间<86400秒的记录才被视为正常记录
            $sql="SELECT d.pid,COUNT(DISTINCT a.account_id) as daily_new_active_user,IFNULL(SUM(a.gs_last_report_time - a.gs_start_time),0) as daily_new_play_time";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid RIGHT JOIN july_account as ja on ja.id=a.account_id";
            $sql.=" WHERE a.create_time>=$day_begin AND a.create_time<$day_end and ja.create_time>=$day_begin AND ja.create_time<$day_end AND d.pid!='' AND a.is_online_gs=1 AND a.gs_last_report_time - a.gs_start_time<86400";
            $sql.=" group by d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_active_user'] = $l['daily_new_active_user'];
                $val[$day_date."_".$l['pid']]['daily_new_play_time'] = $l['daily_new_play_time'];
            }
            
			// max_concurrent_user
			// 统计每天的最高在线
            $arr=array();
            foreach($items as $item)
            {
                $sql ="SELECT d.pid,a.id,a.gs_start_time,a.gs_last_report_time FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid WHERE d.pid='{$item['pid']}' and a.gs_start_time > {$day_begin} and a.gs_last_report_time < {$day_end} and a.is_online_gs=1";
                $arr[$item['pid']]=$m->query($sql);
            }
            
            $tt = $day_begin;
    		while ($tt < $day_end){
                foreach($arr as $key=>$value)
                {
                    $count=0;
                    foreach($value as $v)
                    {
                       if($v['gs_start_time'] < $tt && $v['gs_last_report_time'] > $tt)
                       {
                        $count++;
                       }
                    }
                    $val[$day_date."_".$key]['date'] = $day_date;
                    $val[$day_date."_".$key]['pid'] = $key;
                    if(empty($val[$day_date."_".$key]['max_concurrent_user']))
                    {
                        $val[$day_date."_".$key]['max_concurrent_user']=0;
                    }
                    $val[$day_date."_".$key]['max_concurrent_user']=max($val[$day_date."_".$key]['max_concurrent_user'],$count);
                }
    			$tt += 120;
    		}
    		
            // daily_new_trial_count 当日新增试玩次数
            $sql="SELECT d.pid,COUNT(DISTINCT a.id) as daily_new_trial_count";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid RIGHT JOIN july_account as ja on ja.id=a.account_id";
            $sql.=" WHERE a.create_time >= $day_begin AND a.create_time < $day_end and ja.create_time>=$day_begin AND ja.create_time<$day_end and d.pid!='' AND a.is_online_gs=1 AND a.payment_type=0";
            $sql.=" GROUP BY d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_trial_count']=$l['daily_new_trial_count'];
            }
            
			// daily_trial_count 当日试玩次数
            $sql="SELECT d.pid,COUNT(DISTINCT a.id) as daily_trial_count";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time >= $day_begin AND a.create_time < $day_end and d.pid!='' AND a.is_online_gs=1 AND a.payment_type=0";
            $sql.=" GROUP BY d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_trial_count']=$l['daily_trial_count'];
            }
			
			// daily_start_count 当日游戏启动次数
            $sql="SELECT d.pid,COUNT(DISTINCT a.id) as daily_start_count";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time >= $day_begin AND a.create_time < $day_end and d.pid!='' AND a.is_online_gs=1 AND a.payment_type<>1 AND a.payment_type<>0";
            $sql.=" group by d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_start_count']=$l['daily_start_count'];
            }
            
            // daily_new_start_count 当日新增游戏启动次数
            $sql="SELECT d.pid,COUNT(DISTINCT a.id) as daily_new_start_count";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid RIGHT JOIN july_account as ja on ja.id=a.account_id";
            $sql.=" WHERE a.create_time >= $day_begin AND a.create_time < $day_end and ja.create_time>=$day_begin AND ja.create_time<$day_end and d.pid!='' AND a.is_online_gs=1 AND a.payment_type<>1 AND a.payment_type<>0";
            $sql.=" group by d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_start_count']=$l['daily_new_start_count'];
            }
				
			// daily_active_device  当日活跃的设备
            $sql="SELECT d.pid,COUNT(DISTINCT a.device_uuid) as daily_active_device";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time >= $day_begin AND a.create_time < $day_end and d.pid!='' AND a.is_online_gs=1";
            $sql.=" group by d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_active_device']=$l['daily_active_device'];
            }
            
            //daily_times_nums  当日按次游戏的次数
            $sql="SELECT d.pid,COUNT(DISTINCT a.id) as daily_times_nums";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid = d.device_uuid";
            $sql.=" WHERE a.create_time >= $day_begin AND a.payment_type=2 AND a.create_time < $day_end AND d.pid != '' AND a.is_online_gs=1 ";
            $sql.=" GROUP BY d.pid";
			$list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_times_nums']=$l['daily_times_nums'];
            }
            
            //daily_payment_nums 当日购买包月次数
            $sql="SELECT d.pid,COUNT(DISTINCT pc.id) as daily_payment_nums";
            $sql.=" FROM july_payment_coin as pc RIGHT JOIN july_chargepoint as c on c.id=pc.chargepoint_id RIGHT JOIN july_device as d ON pc.device_uuid = d.device_uuid";
            $sql.=" WHERE pc.create_time >= $day_begin AND pc.create_time < $day_end AND c.type=0 AND d.pid != '' ";
            $sql.=" GROUP BY d.pid";
			$list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_payment_nums']=$l['daily_payment_nums'];
            }
            
            //daily_new_times_nums  当日新增按次游戏的次数
            $sql="SELECT d.pid,COUNT(DISTINCT a.id) as daily_new_times_nums";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid = d.device_uuid JOIN july_account as ja on ja.id=a.account_id";
            $sql.=" WHERE a.create_time >= $day_begin AND a.payment_type=2 AND a.create_time < $day_end and ja.create_time>=$day_begin AND ja.create_time<$day_end AND d.pid != '' AND a.is_online_gs=1 ";
            $sql.=" GROUP BY d.pid";
			$list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_times_nums']=$l['daily_new_times_nums'];
            }
				
			//daily_new_payment_nums 当日购买包月次数
            $sql="SELECT d.pid,COUNT(DISTINCT pc.id) as daily_new_payment_nums";
            $sql.=" FROM july_payment_coin as pc RIGHT JOIN july_chargepoint as c on c.id=pc.chargepoint_id RIGHT JOIN july_device as d ON pc.device_uuid = d.device_uuid JOIN july_account as ja on ja.id=pc.account_id";
            $sql.=" WHERE pc.create_time >= $day_begin AND pc.create_time < $day_end and ja.create_time>=$day_begin AND ja.create_time<$day_end AND c.type=0 AND d.pid != '' ";
            $sql.=" GROUP BY d.pid";
			$list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['daily_new_payment_nums']=$l['daily_new_payment_nums'];
            }
				
			// weekly_active_user  最近7天活跃用户
			$week_begin = $day_end - 86400 * 7;
            $sql="SELECT d.pid,COUNT(DISTINCT a.account_id) as weekly_active_user";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time >= $week_begin AND a.create_time < $day_end and d.pid!='' AND a.is_online_gs=1";
            $sql.=" GROUP BY d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['weekly_active_user']=$l['weekly_active_user'];
            }
				
			// monthly_active_user 最近30天活跃用户
			$month_begin = $day_end - 86400 * 30;
            $sql="SELECT d.pid,COUNT(DISTINCT a.account_id) as monthly_active_user";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time >= $month_begin AND a.create_time < $day_end and d.pid!='' AND a.is_online_gs=1";
            $sql.=" GROUP BY d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['monthly_active_user']=$l['monthly_active_user'];
            }
				
			// accumulated_active_user  历史累计活跃用户
            $sql="SELECT d.pid,COUNT(DISTINCT a.account_id) as accumulated_active_user,COUNT(DISTINCT a.device_uuid) as accumulated_active_device,IFNULL(SUM(a.gs_last_report_time - a.gs_start_time), 0) as accumulated_play_time";
            $sql.=" FROM july_history_account_game_time as a RIGHT JOIN july_device as d ON a.device_uuid=d.device_uuid ";
            $sql.=" WHERE a.create_time < $day_end AND d.pid!='' AND a.is_online_gs=1 AND a.gs_last_report_time - a.gs_start_time < 86400";
            $sql.=" GROUP BY d.pid";
            $list=$m->query($sql);
            foreach($list as $l)
            {
                $val[$day_date."_".$l['pid']]['date'] = $day_date;
                $val[$day_date."_".$l['pid']]['pid'] = $l['pid'];
                $val[$day_date."_".$l['pid']]['accumulated_active_user']=$l['accumulated_active_user'];
                $val[$day_date."_".$l['pid']]['accumulated_active_device']=$l['accumulated_active_device'];
                $val[$day_date."_".$l['pid']]['accumulated_play_time']=$l['accumulated_play_time'];
            }

            //填充没有的字段值，避免sql语句报错	
            foreach($val as $key=>$row)
            {
                if(empty($row['daily_new_account']))
                {
                    $val[$key]['daily_new_account']=0;
                }
                if(empty($row['daily_new_device']))
                {
                    $val[$key]['daily_new_device']=0;
                }
                if(empty($row['daily_active_user']))
                {
                    $val[$key]['daily_active_user']=0;
                }
                if(empty($row['daily_play_time']))
                {
                    $val[$key]['daily_play_time']=0;
                }
                if(empty($row['max_concurrent_user']))
                {
                    $val[$key]['max_concurrent_user']=0;
                }
                if(empty($row['daily_trial_count']))
                {
                    $val[$key]['daily_trial_count']=0;
                }
                if(empty($row['daily_start_count']))
                {
                    $val[$key]['daily_start_count']=0;
                }
                if(empty($row['daily_active_device']))
                {
                    $val[$key]['daily_active_device']=0;
                }
                if(empty($row['weekly_active_user']))
                {
                    $val[$key]['weekly_active_user']=0;
                }
                if(empty($row['monthly_active_user']))
                {
                    $val[$key]['monthly_active_user']=0;
                }
                if(empty($row['accumulated_active_user']))
                {
                    $val[$key]['accumulated_active_user']=0;
                }
                if(empty($row['accumulated_active_device']))
                {
                    $val[$key]['accumulated_active_device']=0;
                }
                if(empty($row['accumulated_play_time']))
                {
                    $val[$key]['accumulated_play_time']=0;
                }
                if(empty($row['daily_new_active_user']))
                {
                    $val[$key]['daily_new_active_user']=0;
                }
				if(empty($row['daily_new_play_time']))
                {
                    $val[$key]['daily_new_play_time']=0;
                }
				if(empty($row['daily_new_trial_count']))
                {
                    $val[$key]['daily_new_trial_count']=0;
                }
				if(empty($row['daily_new_start_count']))
                {
                    $val[$key]['daily_new_start_count']=0;
                }
				if(empty($row['daily_times_nums']))
                {
                    $val[$key]['daily_times_nums']=0;
                }
				if(empty($row['daily_payment_nums']))
                {
                    $val[$key]['daily_payment_nums']=0;
                }
                if(empty($row['daily_new_times_nums']))
                {
                    $val[$key]['daily_new_times_nums']=0;
                }
                if(empty($row['daily_new_payment_nums']))
                {
                    $val[$key]['daily_new_payment_nums']=0;
                }
            }

			$now = time();
			$m_pri=M();
            foreach($val as $row)
            {
              $sql="REPLACE INTO `july_stat_pid`(date,pid,daily_new_active_user,daily_new_play_time,daily_new_trial_count,daily_new_start_count,daily_times_nums,daily_payment_nums,daily_new_times_nums,daily_new_payment_nums,daily_new_account,daily_new_device,daily_active_user,daily_play_time,max_concurrent_user,daily_trial_count,daily_start_count,daily_active_device,weekly_active_user,monthly_active_user,accumulated_active_user,accumulated_active_device,accumulated_play_time,update_time)";
        	  $sql.="VALUES({$day_date},'{$row['pid']}',{$row['daily_new_active_user']},{$row['daily_new_play_time']},{$row['daily_new_trial_count']},{$row['daily_new_start_count']},{$row['daily_times_nums']},{$row['daily_payment_nums']},{$row['daily_new_times_nums']},{$row['daily_new_payment_nums']},{$row['daily_new_account']},{$row['daily_new_device']},{$row['daily_active_user']},{$row['daily_play_time']},{$row['max_concurrent_user']},{$row['daily_trial_count']},{$row['daily_start_count']},{$row['daily_active_device']},{$row['weekly_active_user']},{$row['monthly_active_user']},{$row['accumulated_active_user']},{$row['accumulated_active_device']},{$row['accumulated_play_time']},{$now})";
			  $m_pri->execute($sql);
            }
			$day_begin += 86400;
		}
		return $this->respond(0, "success");
	}
	
	//更新device中的region_id和isp_id
	public function update_device_ip(){
		$m = M('','','DB_CONFIG2');
		$device_ips = $m->table('july_device')->field('id,ip')->where("ip != '' and region_id = 0 and isp_id = 0")->limit(100)->select();
		$m = M();
		foreach($device_ips as $val){
			$data = update_ip_info($val['ip']);
			if ($data === false)
				continue;
			$update = array('id'=>$val['id'],
							'region_id'=>$data['region_id'],
							'region'=>$data['region'],
							'isp_id'=>$data['isp_id'],
							'isp'=>$data['isp']
							);
			$m->table('july_device')->save($update);
			usleep(150000);
			Log::write("ip: ".$val['ip']." region: ".$data['region']." isp: ".$data['isp'], Log::INFO);
		}
		return $this->respond(0, 'success');
	}
	
	//更新nettest中的province_id和isp_id
	public function update_nettest_ip(){
		$m = M('','','DB_CONFIG2');
		$account_ips = $m->table('july_nettest')->field('id,account_ip')->where("account_ip != '' and province_id = 0 and isp_id = 0")->limit(100)->select();
		$m = M();
		foreach($account_ips as $val){
			$data = update_ip_info($val['account_ip']);
			if ($data === false)
				continue;
			$update = array('id'=>$val['id'],
							'province_id'=>$data['region_id'],
							'province'=>$data['region'],
							'isp_id'=>$data['isp_id'],
							'isp'=>$data['isp']
							);
			$m->table('july_nettest')->save($update);
			usleep(150000);
			Log::write("ip: ".$val['account_ip']." region: ".$data['region']." isp: ".$data['isp'], Log::INFO);
		}
		return $this->respond(0, 'success');
	}
	
	//更新用户的积分,积分为30天内的积分总和
	public function update_arena_account_integral(){
		$m = M('','','DB_CONFIG2');
		$today = date('Ymd',time());
		$last_month_day = date('Ymd',strtotime('-1 month'));
		$integrals = $m->table('july_stat_integral')->where('date <= '.$today.' and date >'.$last_month_day)->select();
		if($integrals === false)
			return $this->respond(-1,'');
		$data = array();
		foreach($integrals as $val){
			$data[$val['account_id'].'_'.$val['game_id']]['game_id'] = $val['game_id'];
			$data[$val['account_id'].'_'.$val['game_id']]['account_id'] = $val['account_id'];
			if( empty($data[$val['account_id'].'_'.$val['game_id']]['integral']) )
				$data[$val['account_id'].'_'.$val['game_id']]['integral'] = 0;
			$data[$val['account_id'].'_'.$val['game_id']]['integral'] = $data[$val['account_id'].'_'.$val['game_id']]['integral'] + $val['integral'];
		}
		$m = M();
		foreach($data as $r){
			$where = array('account_id'=>$r['account_id'],
							'game_id'=>$r['game_id']
							);
			$update = array('integral'=>$r['integral']);
			$m->table('july_arena_account')->where($where)->save($update);
		}
		return $this->respond(0,'success');		
	}
}
