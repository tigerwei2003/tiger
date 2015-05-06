<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Org\Net\UploadFile;
class ToolsController extends BaseController
{
	//账户id验证
	public  function check_actid_exist(){
		$account_id=I('account_id',0);//账户ID
		$db = M('account');//售卖存档表
		$condition = array();
		$condition['id'] = $account_id;
		$db_account = $db->where($condition)->find();
		if(!$db_account)
		{
			echo 404;
			die;
		}
		echo 200;
	}
	//获取id的deviceid和logintoken
	public function get_actid(){
		$account_id=I('account_id',0);//账户ID
		$db = M('device');//售卖存档表
		$condition = array();
		$condition['bind_account'] = $account_id;
		$db_account = $db->field("device_uuid,login_token")->where($condition)->find();
		if($db_account === false)
			echo -1;
		echo json_encode($db_account);
	}
	//存档id验证
	public  function check_saveid_exist(){
		$save_id=I('save_id',0);//存档ID
		// 读取指定游戏存档
		$db = M('game_save');//售卖存档表
		$condition = array();
		$condition['id'] = $save_id;
		$db_gamesave = $db->field('game_id,account_id,serial_id,compressed_md5,compressed_size')->where($condition)->find();
		if($db_gamesave === false || !$db_gamesave)
		{
			echo 404;
			die;
		}
		echo 200;
	}
	//游戏id验证
	public function check_game_id_exist(){
		$game_id=I('game_id',0);//游戏id
		$db=M('game');
		$condition=array();
		$condition['game_id']=$game_id;
		$db_game = $db->where($condition)->find();
		if(!$db_game)
		{
			echo 404;
			die;
		}
		echo 200;
	}
	//存档上传
	public	function upload_show(){
		$this->display('upload_show');
	}
	//获取游戏列表
	public function game_list(){
		$category=I('category',1);//游戏分类id
		$db=M('game');
		$condition = array();
		$conditon['status'] = 1;
		$condition['category'] = $category;
		$db_game=$db->field('game_id,game_name')->where($condition)->select();
		if(!$db_game){
			echo 404;
			return ;
		}
		echo json_encode($db_game);
	}
	
	/*
	上传售卖存档处理
	上传一个存档文件，保存到指定账户下（运营或者运维的账户）。 
	在后台的管理工具中添加界面：上传一个存档文件（必须是7z格式），
	填入目标账户ID，填入游戏ID，点击上传（将存档文件上传到目标账户的目标游戏的第一个存档序列下，
	如果不存在存档序列，则创建一个）。
	**/
	public function	upload_gamesave_do()
	{
		$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");   //本地路径
		$target_account_id = I('account_id','');//目标帐号ID
		$game_id = I('game_id','');//游戏id
		$target_serial_id = I('serial_id',0);// 上传到的存档序列ID，如果是0则新建一个存档序列
		$save_name = I('save_name','上传的存档');//存档的名字
		if(empty($save_name))
			$save_name = '上传的存档';
		
		//验证账户id
		$db = M('account');//账户表
		$condition = array();
		$condition['id'] = $target_account_id;
		$db_account = $db->where($condition)->find();
		if(!$db_account) {
			echo  "<script>alert('账户".$target_account_id." 不存在');history.go(-1);</script>";
			return;
		}
		// 验证游戏id
		$db = M('game');//游戏表
		$condition = array();
		$condition['game_id'] = $game_id;
		$condition['status'] = 1;
		$db_game = $db->where($condition)->find();
		if(!$db_game) {
			echo  "<script>alert('游戏".$game_id." 不存在');history.go(-1);</script>";
			return;
		}
		if($_POST['gssubmit']==1){
			$upload = new UploadFile();// 实例化上传类
			$upload->maxSize  = 20*1024*1024 ;// 设置附件上传大小
			$upload->allowExts  = array('7z');// 设置附件上传类型
			//保存路径建议与主文件平级目录或者平级目录的子目录来保存 
			$upload->savePath = $GAME_SAVE_DIR."temp".DIRECTORY_SEPARATOR.date("Ymd").DIRECTORY_SEPARATOR; 
			
			if (!file_exists($upload->savePath) && !mkdir($upload->savePath, 0744, true)) {
				echo "<script>alert('无法建立用户上传临时目录');history.go(-1);</script>";
				return;	
			}
			if(!$upload->upload()) {// 上传错误提示错误信息
				$this->error($upload->getErrorMsg());
			}else{// 上传成功 获取上传文件信息
				$info =  $upload->getUploadFileInfo();
				
				//echo "<script>alert('上传成功！');history.go(-1));</script>";
				//$this->success('上传成功!','./op.php/Tools/gamesave_list');
				$save_file_path = $info[0]['savepath'].$info[0]['savename'];
			}
		}else{
				echo "<script>alert('非法操作!不能通过其他途径上传！');history.go(-1);</script>";
				return;
		}
		$file_md5 = strtolower(md5_file($save_file_path));
		
		// 事务
		$model = M();
		$model->startTrans();
		
		// 查询用户是否已经有该游戏的存档序列
		$condition = array();
		$condition['account_id'] = $target_account_id;
		$condition['game_id'] = $game_id;
		if ($target_serial_id != 0)
			$condition['id'] = $target_serial_id;
		$exist_serial = $model->table(C('DB_PREFIX').'game_save_serial')->field('id')->where($condition)->order('id desc')->limit(1)->select();
		if ($exist_serial === false) {
			$model->rollback();
			echo  "<script>alert('无法查询存档序列');history.go(-1);</script>";
			return;
		}
		if (count($exist_serial) > 0)
			$target_serial_id = $exist_serial[0]['id'];
		else {
			if ($target_serial_id != 0) {
				$model->rollback();
				echo  "<script>alert('指定的存档序列不存在');history.go(-1);</script>";
				return;
			}
			// 给用户建立新的存档序列和存档
			$data = array();
			$data['account_id'] = $target_account_id;
			$data['game_id'] = $game_id;
			$data['name'] = $save_name;
			$data['create_time'] = time();
			$data['delete_time'] = 0;
			$target_serial_id = $model->table(C('DB_PREFIX').'game_save_serial')->add($data);
			if ($target_serial_id === false){
				$model->rollback();
				echo  "<script>alert('无法创建新的存档序列');history.go(-1);</script>";
				return;
			}
		}
		//游戏存档表
		$data = array();
		$data['account_id'] = $target_account_id;
		$data['game_id'] = $game_id;
		$data['serial_id'] = $target_serial_id;
		$data['gs_id'] = 0;
		$data['gs_ip'] = '';
		$data['upload_token'] = '';
		$data['derived_from'] = 0;
		$data['device_uuid'] = '';
		$data['upload_time'] = time();
		$data['game_mode'] = 0;
		$data['gs_report_time'] = 0;
		$data['compressed_size'] = $info[0]['size'];
		$data['compressed_md5'] = $file_md5;
		$data['derived_count'] = 0;
		$data['create_time'] = time();
		$data['total_play_time'] = 1; // 新购买的存档默认游戏时间为1秒
		$data['delete_time'] = 0;
		$new_save_id = $model->table(C('DB_PREFIX').'game_save')->add($data);
		if ($new_save_id === false) {
			$model->rollback();
			echo  "<script>alert('无法创建新的存档');history.go(-1);</script>";
			return;
		}
		$user_save_path = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($target_account_id/1000000000).DIRECTORY_SEPARATOR.
			intval($target_account_id/1000000).DIRECTORY_SEPARATOR.intval($target_account_id/1000).DIRECTORY_SEPARATOR.
			$target_account_id.DIRECTORY_SEPARATOR.$game_id.DIRECTORY_SEPARATOR.$target_serial_id.DIRECTORY_SEPARATOR;

		if (!file_exists($user_save_path) && !mkdir($user_save_path, 0744, true)) {
			$model->rollback();
			echo "<script>alert('无法建立用户存档目录');history.go(-1);</script>";
			return;
		}
		
		$source_file = $save_file_path;
		$target_file = $user_save_path.$new_save_id."_".$file_md5.".save";

		if(copy($source_file,$target_file) === false) {
			$model->rollback();
			echo  "<script>alert('复制存档失败');history.go(-1);</script>";
			return;
		}
		
		if (C("ENABLE_OSS") === true) {
			// 同步上传到阿里云OSS
			$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
			if ($ret['ret'] != 0) {
				$model->rollback();
				echo  "<script>alert('连接阿里云OSS失败！ ret:".$ret['ret']." msg:".$ret['msg']."');history.go(-1);</script>";
				return;
			}
			$client = $ret['msg'];
				
			$key = "u"."/".intval($target_account_id/1000000000)."/".
				intval($target_account_id/1000000)."/".intval($target_account_id/1000)."/".
				$target_account_id."/".$game_id."/".$target_serial_id."/".$new_save_id."_".$file_md5.".save";
			$ret = $this->multipartUpload($client, C("OSS_UDS_BUCKET"), $key, $target_file);
			if ($ret['ret'] != 0) {
				$model->rollback();
				echo  "<script>alert('上传文件到阿里云OSS失败！ ret:".$ret['ret']." msg:".$ret['msg']."');history.go(-1);</script>";
				return;
			}
		}
		
		$model->commit();
		$msg = "已经将".$info[0]['name']."上传为".$new_save_id."_".$file_md5.".save";
		echo  "<script>alert('".$msg."');history.go(-1);</script>";
		
	}
	//复制售卖存档
	public	function copy_gamesave_show(){
		$this->display('copy_gamesave_show');
	}
	//复制售卖存档处理
	/*
		1、将一个用户的一个存档复制给另一个用户（运维或者运营的账户），
		这样后者可以直接体验前者的存档。
		在后台的管理工具中添加界面：填入任意存档ID，填入目标账户ID，点击复制
		(将目标存档复制到目标账户的第一个存档序列下，如果不存在存档序列，则创建一个）。
	*/
	public  function copy_gamesave_sale() {
		$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");
		
		$save_id=I('save_id',0);//存档ID
		$target_account_id=I('account_id',0);//账户ID

		// 读取指定账户id
		$db = M('account');//账户表
		$condition = array();
		$condition['id'] = $target_account_id;
		$db_account = $db->where($condition)->find();
		if(!$db_account) {
			echo  "<script>alert('账户".$target_account_id." 不存在');history.go(-1);</script>";
			return;
		}
		// 读取指定游戏存档
		$db = M('game_save');//游戏存档表
		$condition = array();
		$condition['id'] = $save_id;
		$db_gamesave = $db->field('game_id,account_id,serial_id,compressed_md5,compressed_size')->where($condition)->find();
		if(!$db_gamesave) {
			echo  "<script>alert('存档".$save_id." 不存在');history.go(-1);</script>";
			return;
		}

		$game_id = $db_gamesave['game_id'];
		$source_account_id = $db_gamesave['account_id'];
		$serial_id = $db_gamesave['serial_id'];
		$file_md5 = $db_gamesave['compressed_md5'];
		$file_size = $db_gamesave['compressed_size'];
		// 得到源游戏存档文件的完整路径
		$save_file_path = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($source_account_id/1000000000).DIRECTORY_SEPARATOR.
			intval($source_account_id/1000000).DIRECTORY_SEPARATOR.intval($source_account_id/1000).DIRECTORY_SEPARATOR.
			$source_account_id.DIRECTORY_SEPARATOR.$game_id.DIRECTORY_SEPARATOR.$serial_id.DIRECTORY_SEPARATOR.
			$save_id."_".$file_md5.".save";
		if (!file_exists($save_file_path)) {
			if (C("ENABLE_OSS") === true) {
				// 本地文件不存在，尝试从阿里云OSS下载
				// Read and write for owner, read for everybody else
				$final_dir = dirname($save_file_path);
				if (!file_exists($final_dir) && !mkdir($final_dir, 0744, true)) {
					echo  "<script>alert('建立源用户的存档目录失败！ dir:".$final_dir."');history.go(-1);</script>";
					return;
				}
				
				$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
				if ($ret['ret'] != 0) {
					echo  "<script>alert('连接阿里云OSS失败！ ret:".$ret['ret']." msg:".$ret['msg']."');history.go(-1);</script>";
					return;
				}
				$client = $ret['msg'];
				
				// 阿里云OSS上的路径
				$key = "u"."/".intval($source_account_id/1000000000)."/".intval($source_account_id/1000000)."/".
					intval($source_account_id/1000)."/".$source_account_id."/".$game_id."/".$serial_id."/".$save_id."_".$file_md5.".save";
				$ret = $this->getObjectAsFile($client, C("OSS_UDS_BUCKET"), $key, $save_file_path);
				if ($ret['ret'] != 0) {
					echo  "<script>alert(' 从阿里云OSS下载存档失败！ ret:".$ret['ret']." msg:".$ret['msg']."');history.go(-1);</script>";
					return;
				}
			}
			else {
				echo  "<script>alert('存档".$save_id." 文件不存在');history.go(-1);</script>";
				return;
			}
		}
		$md5file = strtolower(md5_file($save_file_path));
		if ($file_md5 != $md5file) {
			echo  "<script>alert('存档".$save_id." 文件已损坏');history.go(-1);</script>";
			return;
		}
		
		
		// 事务
		$model = M();
		$model->startTrans();
		
		// 查询用户是否已经有该游戏的存档序列
		$condition = array();
		$condition['account_id'] = $target_account_id;
		$condition['game_id'] = $game_id;
		$exist_serial = $model->table(C('DB_PREFIX').'game_save_serial')->field('id')->where($condition)->order('id desc')->limit(1)->select();
		if ($exist_serial === false) {
			$model->rollback();
			echo  "<script>alert('无法查询存档序列');history.go(-1);</script>";
			return;
		}
		$target_serial_id = 0;
		if (count($exist_serial) > 0)
			$target_serial_id = $exist_serial[0]['id'];
		else {
			// 给用户建立新的存档序列和存档
			$data = array();
			$data['account_id'] = $target_account_id;
			$data['game_id'] = $game_id;
			$data['name'] = '复制存档'.$save_id;
			$data['create_time'] = time();
			$data['delete_time'] = 0;
			$target_serial_id = $model->table(C('DB_PREFIX').'game_save_serial')->add($data);
			if ($target_serial_id === false){
				$model->rollback();
				echo  "<script>alert('无法创建新的存档序列');history.go(-1);</script>";
				return;
			}
		}
		
		$data = array();
		$data['account_id'] = $target_account_id;
		$data['game_id'] = $game_id;
		$data['serial_id'] = $target_serial_id;
		$data['gs_id'] = 0;
		$data['gs_ip'] = '';
		$data['upload_token'] = '';
		$data['derived_from'] = $save_id;
		$data['device_uuid'] = '';
		$data['upload_time'] = time();
		$data['game_mode'] = 0;
		$data['gs_report_time'] = 0;
		$data['compressed_size'] = $file_size;
		$data['compressed_md5'] = $file_md5;
		$data['derived_count'] = 0;
		$data['create_time'] = time();
		$data['delete_time'] = 0;
		$new_save_id = $model->table(C('DB_PREFIX').'game_save')->add($data);
		if ($new_save_id === false) {
			$model->rollback();
			echo  "<script>alert('无法创建新的存档');history.go(-1);</script>";
			return;
		}

		$user_save_path = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR.intval($target_account_id/1000000000).DIRECTORY_SEPARATOR.
			intval($target_account_id/1000000).DIRECTORY_SEPARATOR.intval($target_account_id/1000).DIRECTORY_SEPARATOR.
			$target_account_id.DIRECTORY_SEPARATOR.$game_id.DIRECTORY_SEPARATOR.$target_serial_id.DIRECTORY_SEPARATOR;

		if (!file_exists($user_save_path) && !mkdir($user_save_path, 0744, true)) {
			$model->rollback();
			echo "<script>alert('无法建立目标用户存档目录');history.go(-1);</script>";
			return;
		}
		$source_file = $save_file_path;
		$target_file = $user_save_path.$new_save_id."_".$file_md5.".save";
		if(copy($source_file,$target_file) === false) {
			$model->rollback();
			echo  "<script>alert('复制存档失败');history.go(-1);</script>";
			return;
		}
		if (C("ENABLE_OSS") === true) {
			// 同步上传到阿里云OSS
			$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
			if ($ret['ret'] != 0) {
				$model->rollback();
				echo  "<script>alert('连接阿里云OSS失败！ ret:".$ret['ret']." msg:".$ret['msg']."');history.go(-1);</script>";
				return;
			}
			$client = $ret['msg'];
				
			$key = "u"."/".intval($target_account_id/1000000000)."/".
				intval($target_account_id/1000000)."/".intval($target_account_id/1000)."/".
				$target_account_id."/".$game_id."/".$target_serial_id."/".$new_save_id."_".$file_md5.".save";
			$ret = $this->multipartUpload($client, C("OSS_UDS_BUCKET"), $key, $target_file);
			if ($ret['ret'] != 0) {
				$model->rollback();
				echo  "<script>alert('上传文件到阿里云OSS失败！ ret:".$ret['ret']." msg:".$ret['msg']."');history.go(-1);</script>";
				return;
			}
		}

		$model->commit();
		Log::write("copy_gamesave_sale. copy from $source_file to $target_file ", LOG::INFO);
		$msg = "已经将$save_id 复制为$new_save_id ";
		echo  "<script>alert('".$msg."');history.go(-1);</script>";
	}
	
	//用户头像上传
	public function upload_avatar(){
		if(I('dosubmit',0)){
			$infos = I('info','');
			$key='';
			if(C("ENABLE_OSS") === true && $infos['type'] == 1){
				$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
				if ($ret['ret'] != 0) {
					$this->error("连接阿里云OSS失败!");
				}
				$client = $ret['msg'];
				$name_suffix = explode(".",$_FILES['upload']['name']);
				$key = "a"."/sys_avatar/".md5_file($_FILES['upload']['tmp_name']).".".$name_suffix[1];
				$ret = $this->multipartUpload($client, C("OSS_PIC_BUCKET"), $key, $_FILES['upload']['tmp_name']);
				if ($ret['ret'] != 0) {
					$this->error("上传文件到阿里云OSS失败!");
				}
			}
			$db_avatar = M("avatar");
			$add = array();
			$add['type'] = $infos['type'];
			if($infos['type'] != 1)
				$add['name'] = $infos['name'];
			else
				$add['pic_url'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX").$key;
			$result = $db_avatar->add($add);
			if($result === false)
				$this->error("上传失败!数据库添加失败");
			$this->success("上传成功!");
		}
		else
		{
			$this->display('upload_avatar');	
		}		
	}
	
	//用户充值
	public function recharge(){
		$this->display("recharge");
	}
}
