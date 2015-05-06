<?php
namespace Admin\Controller;
use Admin\Controller\CardbaseController;

defined('THINK_PATH') or exit;

class CheckCardtypeController extends CardbaseController {
	
	final protected function GetList() {
		$where = $this->GetWhereCondition();
		
		$db = M('mulcard_type');
		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$rs = $db->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
		
		if($rs) {
			foreach($rs as $key=>$row) {
				$points = unserialize($row['charge_point']);
				$points_name = '';
				
				if($points) {
					foreach($points as $row_point) {
						$points_name .= $row_point['id'].' : '.htmlspecialchars($row_point['name'],ENT_QUOTES).'<br>';
					}
				}
				$rs[$key]['point_name'] = $points_name;
				$rs[$key]['type_name']  = htmlspecialchars($row['type_name'],ENT_QUOTES); 
				$rs[$key]['spare']      = htmlspecialchars($row['spare'],ENT_QUOTES); 
			}
		} 
		$this->typelist = $rs;
		unset($rs);
			
		$this->display('card_type');
	}
	
	/*
	 * 插入数据库操作
	 */
	final protected function Insert() {
		$data = $this->GetPostData();  //获得表单提过来的数据
		$game_info = $this->CheckPostData($data);
		@extract($data);
		unset($data);
		
		@extract($game_info);
		unset($game_info);   //释放资源
		
		$ser_game = serialize($gamers);
		$db = M();
		
		$db->startTrans();
		$sql = "INSERT INTO ".C('DB_PREFIX')."mulcard_type(`type_name`,`point_type`,`charge_point`,`ids`,`spare`,`create_time`,`admin`)
				VALUES('$card_type_name','$point_type','$ser_game','$ids','$source','".time()."','{$this->username}')";
		if(!$db->execute($sql)) {
			$db->rollback();
			return false;
		}
		systemlog(1,'mulcard_payment_card',$db->GetLastSql(),'新增多选卡类别，类别名：'.$card_type_name);
		
		$last_id = $db->getLastInsID();
		$sql = "INSERT INTO ".C('DB_PREFIX')."mulcard_type_detail(`type_id`,`point_id`,`point_name`)
				VALUES".$this->InsertSqlSuffix($gamers,$last_id);
		if(!$db->execute($sql)) {
			$db->rollback();
			return false;
		}
		
		systemlog(1,'mulcard_payment_card',$db->GetLastSql(),'新增多选卡关联计费点');
		$db->commit();
		return true;
	}
	
	/*
	 * 更新数据库操作
	 */
	final protected function Update() { 		
		$data = $this->GetPostData();   //获得表单提交过来的数据
		$game_info = $this->CheckPostData($data,'edit');
		@extract($data);
		unset($data); 
		
		@extract($game_info);
		unset($game_info);   //释放资源
			
		$ser_game = serialize($gamers);
		$db = M();
		//更新类别表
		$db->startTrans();
		$sql = "UPDATE ".C('DB_PREFIX')."mulcard_type 
				SET `type_name`='$card_type_name',`spare`='$source',`ids`='".implode(',',$games)."',`charge_point`='$ser_game' 
				WHERE id='$card_id'";
		if(!$db->execute($sql)) {
			$db->rollback();
			return false;
		} 
		
		systemlog(2,'mulcard_payment_card',$db->GetLastSql(),'更新多选卡类别，类别名：'.$card_type_name);
		if(!$this->IdIsSame($games,$id_string)) {   //如果计费点有变化
			$gamers = $this->GetGamesChange($games,$card_id);    //获得变化的数组
			$table = C('DB_PREFIX').'mulcard_type_detail';
			
			//如果表单提交了新的计费点，将新的计费点插入数据库
			if($gamers['from_post']) {
				$game_info = $this->GameDecode($gamers['from_post']);
				$sql = "INSERT INTO $table(`type_id`,`point_id`,`point_name`) 
						VALUES".$this->InsertSqlSuffix($game_info['gamers'],$card_id);
				if(!$db->execute($sql)) {
					$db->rollback();
					return false;
				}
				systemlog(1,'mulcard_payment_card',$db->GetLastSql(),'新增多选卡关联计费点');
			}
			
			//如果存在多余的计费点，删除
			if($gamers['from_data']) {
				$games_id = array_keys($gamers['from_data']);
				if(!parent::DeleteById($table,$games_id)) {
					$db->rollback();
					return false;
				}
				systemlog(3,'mulcard_payment_card',$db->GetLastSql(),'删除多选卡关联计费点');
			}
		}
		$db->commit();
		return true;
	}
	
	/*
	 * 显示添加/编辑页面
	 */
	final protected function GetEdit() {
		$this->id = I('get.id',0);
		if($this->id > 0) {
			$rs = $this->GetTypeDetail($this->id); 
			@extract($rs);
			unset($rs);
			
			$this->ids      = $ids;
			$this->typeinfo = $info; 
			$this->gameinfo = $gamers;
			unset($info);
			unset($gamers);
		} 
		$this->gamepack  = $this->GetGamepack($this->typeinfo['point_type']);
		$this->pointtype = $this->GetGameType($this->typeinfo['point_type']);
		
		$this->display('card_type_edit');
	}
	
	/*
	 * 判断选择的计费点变化情况
	 */
	private function GetGamesChange($games=0,$id=0) {
		if(!$games || !$id) {
			return false;
		}
		$rs = M('mulcard_type_detail')->field("id,point_id,point_name")->where("type_id='$id'")->select();
		if($rs) {
			foreach($rs as $key=>$row) {
				//$games_db[$key] = $row['point_id'];
				$games_db[$row['id']] = $row['point_id'];
			}
		}
		$from_post = array_diff($games,$games_db);
		$from_data = array_diff($games_db,$games);
		
		return array(
						'from_post'=>$from_post,
						'from_data'=>$from_data
					);
	}
	
	/*
	 * 获得指定类别的详细信息
	 */
	private function GetTypeDetail($id=0) {
		$rs = M("mulcard_type")->field("id,point_type,ids,type_name,charge_point,spare")->where("id='$id'")->select();

		if(!$rs) {
			return false;
		}
		$gamers = unserialize($rs[0]['charge_point']);
		unset($rs[0]['charge_point']);
		
		if($gamers) {
			foreach($gamers as $key=>$row) {
				$ids[] = $row['id'];
			}
		}
		return array(	
					'ids'   =>$ids,
					'info'  =>$rs[0],
					'gamers'=>$gamers
		);
	}
	
	/*
	 * 批量插入SQL语句
	*/
	private function InsertSqlSuffix($rs,$last_id=0) {
		if(!$rs || !$last_id) { 
			return false; 
		}
	
		$sql_suffix = '';
		foreach($rs as $row) {
			$sql_suffix .= "('$last_id','{$row['id']}','{$row['name']}'),";
		}
		return rtrim($sql_suffix,',');
	}
	
	/*
	 * 获取计费点详细信息
	*/
	private function GameDecode($games,$point_type) {
		$rs = M('chargepoint')->field("id,`name`")->where("id IN(".implode(',',$games).") AND `type`='$point_type'")->select();

		if(!$rs) {
			return false;
		}
		foreach($rs as $key=>$row) {
			$ids .= $row['id'].',';
		}
		return array(
				'ids' => trim($ids,','),
				'gamers' =>$rs,
		);
	}
	
	/*
	 * 多选卡名称排重
	 */
	private function ExcludeDuplicate ($type_name='') { 
		$db = M();
		$sql = "SELECT 1 FROM ".C('DB_PREFIX')."mulcard_type WHERE type_name='$type_name' LIMIT 1";
		return $db->Query($sql);
	}
	
	/*
	 * 类别是否有变化
	 */
	private function IdIsSame ($ids_new,$ids_old) {
		if(!$ids_new || !$ids_old) {
			return false;
		}	
		//$id_array_new = explode(',',$ids_new);
		$id_array_old = explode(',',$ids_old);
		
		return !array_diff($ids_new,$id_array_old) && !array_diff($id_array_old,$ids_new);
	}
	
	/*
	 * Ajax调用方法，用于编辑多选卡分类显示内容
	*/
	public function GetTypePoint($cid=0) {
		exit(json_encode($this->GetGamepack($cid)));
	}
	
	/*
	 * 获得渠道
	*/
	private function GetChannel() {
		return M("dealer")->where("`id`>=10")->order("id DESC")->select();
	}
	
	/*
	 * 获得包月游戏包
	*/
	private function GetGamepack($type=0) { 
		return M("chargepoint")->field("id,name")->where("type='$type'")->select();
	}
	
	private function GetWhereCondition() {}
	
	/*
	 * 校验POST提交的数据
	*/
	private function CheckPostData($data,$op='insert') { 
		@extract($data); 
		
		if(!$card_type_name) {
			$this->error('分类名称不能为空');
		}
		if(!$games) {
			$this->error('游戏点最少选择一个');
		}
		if(!$source) {
			$this->error('备注不能为空');
		}
		if(!$game_info=$this->GameDecode($games,$point_type)) {
			$this->error('获取计费点相关信息失败');
		}
		if($op == 'insert') {
			if($this->ExcludeDuplicate($card_type_name)) {
				$this->error('类别名称重复');
			}
		}
		return $game_info;
	}
	
	/*
	 * 获得创建卡页面提交过来的数据
	*/
	private function GetPostData() {
		return array(
				'card_type_name'=>I('post.card_type_name',''),
				'point_type'    =>I('post.point_type',0),
				'source'        =>I('post.source',''),
				'games'         =>I('post.game_id',''),
				'card_id'       =>I('post.card_id',0),
				'id_string'     =>I('post.id_string','')
		);
	}
	
	/*
	 * 获得游戏点分类
	*/
	private function GetGameType($type=0) {
		$types =  array(
					    '0'=>array('id'=>0,'name'=>'游戏包'),
						'1'=>array('id'=>1,'name'=>'存档'),
						'2'=>array('id'=>2,'name'=>'虚拟币'),
						'3'=>array('id'=>3,'name'=>'单次游戏'),
						'4'=>array('id'=>4,'name'=>'街机投币'),
						'5'=>array('id'=>5,'name'=>'擂台赛'),
					);
		if(isset($type)) {
			return $types[$type];
		}
		return $types;
	}
}