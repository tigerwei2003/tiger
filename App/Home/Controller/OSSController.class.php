<?php
namespace Home\Controller;
use Home\Controller\BaseController;
//API接口文件
class OSSController extends BaseController {
    public function index(){
		$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }

	// Sample of delete object
	function deleteObject(OSSClient $client, $bucket, $key) {
		$client->deleteObject(array(
			'Bucket' => $bucket,
			'Key' => $key,
		));
	}
    
	public function upload_all_to_oss() {
	
		set_time_limit(1800);
		
		echo "connecting to aliyun oss... \n<br>";
		$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$client = $ret['msg'];
			
		//$ret = $this->multipartUpload($client, C("OSS_UDS_BUCKET"), "hex/test/1/Gloud.apk", "e:\\cache\\Gloud.apk");
		//if ($ret['ret'] != 0)
		//	return $this->respond($ret['ret'], $ret['msg']);
		
		$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");
		
		echo "GAME_SAVE_DIR $GAME_SAVE_DIR \n<br>";
		
		echo "connecting to database... \n<br>";
		
		$start_time = time();
		
		$db_serial = M('game_save_serial');
		$db_save = M('game_save');
		
		$condition = array();
		$condition['delete_time'] = 0;
		$condition['oss_upload_done'] = 0;
		$db_serial_list = $db_serial->where($condition)->order('id desc')->limit(10000)->select();
		if ($db_serial_list === false)
			return $this->respond(-1, "select db_serial failed");
			
		echo "serial count: ".count($db_serial_list)." \n<br> ";
		
		foreach ($db_serial_list as $serial) {
			$accountid = $serial['account_id'];
			$gameid = $serial['game_id'];
			$serialid = $serial['id'];
			
			$condition = array();
			$condition['account_id'] = $accountid;
			$condition['game_id'] = $gameid;
			$condition['serial_id'] = $serialid;
			$condition['delete_time'] = 0;
			$condition['oss_upload_done'] = 0;
			$condition['_string'] = "upload_time<>0";
			$db_save_list = $db_save->where($condition)->order('id desc')->limit(1)->select();
			if ($db_save_list === false)
				return $this->respond(-1, "select db_save serial:$serialid failed");
				
			echo "serial $serialid. save count: ".count($db_save_list)." \n<br>";

			foreach ($db_save_list as $save) {
				$saveid = $save['id'];
				$compressed_md5 = $save['compressed_md5'];
				
				$local_file = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($accountid/1000000000).DIRECTORY_SEPARATOR.
							intval($accountid/1000000).DIRECTORY_SEPARATOR.intval($accountid/1000).DIRECTORY_SEPARATOR.
							$accountid.DIRECTORY_SEPARATOR.$gameid.DIRECTORY_SEPARATOR.$serialid.DIRECTORY_SEPARATOR.
							$saveid."_".$compressed_md5.".save";
				
				$key = "u"."/".intval($accountid/1000000000)."/".intval($accountid/1000000)."/".intval($accountid/1000)."/".
						$accountid."/".$gameid."/".$serialid."/".$saveid."_".$compressed_md5.".save";
						
				echo "upload $local_file to $key... \n<br>";
				
				$real_key = "u"."/".intval($accountid/1000000000)."/".intval($accountid/1000000)."/".intval($accountid/1000)."/".
						$accountid."/".$gameid."/".$serialid."/".$saveid."_".$compressed_md5.".save";
				
				$ret = $this->checkObject($client, C("OSS_UDS_BUCKET"), $real_key);
				if ($ret['ret'] != 0) {
					echo "key does not exist on OSS. begin upload. ret:".$ret['ret']." msg:".$ret['msg']." \n<br>";
				
					$ret = $this->multipartUpload($client, C("OSS_UDS_BUCKET"), $key, $local_file);
					if ($ret['ret'] != 0)
						return $this->respond($ret['ret'], $ret['msg']);
				}
				else 
					echo "$real_key already exists on OSS. skip upload. \n<br>";
				
				// update oss_upload_done in save table
				$condition = array();
				$condition['id'] = $saveid;
				$condition['delete_time'] = 0;
				$condition['oss_upload_done'] = 0;
				$data = array();
				$data['oss_upload_done'] = 1;
				$db_ret = $db_save->where($condition)->save($data);
				if ($db_ret === false)
					return $this->respond(-1, "update db_save serial:$serialid save:$saveid failed.".$db_save->getLastSql());
			}
			
			// update oss_upload_done in serial table
			$condition = array();
			$condition['id'] = $serialid;
			$condition['delete_time'] = 0;
			$condition['oss_upload_done'] = 0;
			$data = array();
			$data['oss_upload_done'] = 1;
			$db_ret = $db_serial->where($condition)->save($data);
			if ($db_ret === false)
				return $this->respond(-1, "update db_serial serial:$serialid failed. ".$db_serial->getLastSql());
		}
		
		return $this->respond(0, "success. used time:".(time()-$start_time));
	}
	
	public function testdown() {
		set_time_limit(120);
		
		$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$client = $ret['msg'];
		
		
		$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");
			
		// 阿里云OSS上的路径
		$accountid = 20;
		$gameid = 2;
		$serialid = 77;
		$saveid = 238130;
		$compressed_md5 = "0140a2dad9f07a08dd0a04e8fdae48bd";
		$compressedname = $saveid."_".strtolower($compressed_md5).".save";;
		$key = "u"."/".intval($accountid/1000000000)."/".intval($accountid/1000000)."/".intval($accountid/1000)."/".$accountid."/".$gameid."/".$serialid."/".$compressedname;
		$path = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($accountid/1000000000).DIRECTORY_SEPARATOR.intval($accountid/1000000).DIRECTORY_SEPARATOR.intval($accountid/1000).DIRECTORY_SEPARATOR.$accountid.DIRECTORY_SEPARATOR.$gameid.DIRECTORY_SEPARATOR.$serialid.DIRECTORY_SEPARATOR.$compressedname;
		
		// Read and write for owner, read for everybody else
		$final_dir = dirname($path);
		if (!file_exists($final_dir) && !mkdir($final_dir, 0744, true))
			return $this->respond_404_if_failed(-207, "Failed create $final_dir");
				
		$ret = $this->getObjectAsFile($client, C("OSS_UDS_BUCKET"), $key, $path);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
			
		//if (filesize($path) != $compressed_size)
			//return $this->respond(-105, "$path size ".filesize($path)." doesn't match ".$compressed_size);
		
		// 检查MD5是否匹配
		$file_md5 = strtolower(md5_file($path));
		if ($file_md5 != $compressed_md5)
			return $this->respond(-106, "$path md5 $file_md5 doesn't match $compressed_md5");
			
		return $this->respond(0, "success");
	}
	
	public function update_client_ver() {
		$target_ver_str = "1.2.7";
		$name = "1.2.7(Beta)";
		$url_prefix = "http://cdn2.51ias.com/";
		$single_force_update = I('single',0);
		$total_force_update = I('all',0);
		$single_desc = "<p>格来云游戏v1.2.7(Beta)：</p>

<p>1、主界面改版，操作更加便捷。</p>

<p>2、改进游戏存档管理，支持存档组显示，支持购买存档。</p>

<p>3、支持炬力芯片的设备，比如天敏T2.</p>

<p>更新日期：2015-03-03</p>
";
		$total_desc = "<p>格来云游戏v1.2.7(Beta)：</p>

<p>1、主界面改版，操作更加便捷。</p>

<p>2、改进游戏存档管理，支持存档组显示，支持购买存档。</p>

<p>3、支持炬力芯片的设备，比如天敏T2.</p>

<p>更新日期：2015-03-03</p>
";

		$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$client = $ret['msg'];
		
		$ret = $this->listObjects($client, 'client', 'update/'.$target_ver_str);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$objs = $ret['msg'];
		
		$db = M('client_ver');
		
		foreach ($objs as $obj) {
			$is_single = strstr($obj->getKey(), 'single/') !== FALSE ? 1 : 0;
			$is_apk = strstr($obj->getKey(), '.apk') !== FALSE ? 1 : 0;
			list($package, $ver_str, $ver, $pid) = split("-", $obj->getKey(), 4);
			list($pid, $extension) = split('[.]', $pid, 2);
			$package = strstr($package, "cn.gloud");
			if ($is_apk == 0 || strcmp($ver_str, $target_ver_str) !== 0)
				continue;
			echo 'key: ' . $obj->getKey() . " single: $is_single apk: $is_apk ver: $ver pid: $pid package: $package \n<br>";
			
			$force_update = $total_force_update;
			if ($is_single == 1)
				$force_update = $single_force_update;
			$desc = $total_desc;
			if ($is_single == 1)
				$desc = $single_desc;
			
			$condition = array();
			$condition['pid'] = $pid;
			$condition['ver'] = $ver;
			$ret = $db->where($condition)->select();
			if ($ret) {
				echo "found. begin update.\n<br>";
				$update = array();
				$update['desc'] = $desc;
				$update['name'] = $name;
				$update['url'] = $url_prefix.$obj->getKey();
				$update['force_update'] = $force_update;
				$update['update_time'] = time();
				$update['note'] = "updated by PHP.";
				$ret = $db->where($condition)->save($update);
				if ($ret === false)
					return $this->respond(-1, "fail to update ".$obj->getKey());
			}
			else {
				echo "not found. begin insert.\n<br>";
				$data = array();
				$data['pid'] = $pid;
				$data['ver'] = $ver;
				$data['desc'] = $desc;
				$data['name'] = $name;
				$data['url'] = $url_prefix.$obj->getKey();
				$data['force_update'] = $force_update;
				$data['create_time'] = time();
				$data['update_time'] = time();
				$data['note'] = "inserted by PHP.";
				$ret = $db->add($data);
				if ($ret === false)
					return $this->respond(-1, "fail to insert ".$obj->getKey());
			}
		}
		return $this->respond(0, "success");
	}
    
}
