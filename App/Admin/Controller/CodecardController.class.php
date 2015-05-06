<?php 
namespace Admin\Controller;
use Admin\Controller\BaseController;

defined('THINK_PATH') or exit;

class CodecardController extends BaseController {
	private $mDb;
	private $mPrefix;
	
	public function __construct() {
		parent::__construct();

		$this->mDb = M();
		$this->mPrefix = 'july_';
	}
	
	public function card_mul() {
		$this->display('card_mul');
	}
	
	final public function card_edit() { 
		if(IS_POST) {
			$url  = U("card_edit");
			$data = $this->get_create_data();
			@extract($data);
			
			if(!$data) { $this->error('关联游戏不能为空',$url); }
			if(!$codenum) { $this->error('生成数量非法',$url); }
			if(!$card_id_prefix || !is_int($card_id_prefix)) { $this->error('批次数量非法',$url); }
			
			for($i = 0;$i < $codenum;$i ++) {
				
			}
			
		} else {
			$this->dealerlist = $this->get_channel();
			$this->gamepace   = $this->get_gamepack();
			
			$this->display('card_edit');
		}
	}
	
	/*
	 * 编辑/新增多选卡分类
	 */
	final public function card_type_edit() { echo microtime();
		if(IS_POST) { 
			$url  = U("card_type_edit");
			$data = $this->get_type_data();
			@extract($data);
			unset($data); 
			
			if(!$source) { $this->error('备注不能为空',$url); }
			if(!$card_type_name) { $this->error('分类名称不能为空',$url); }
			if(!$games) { $this->error('游戏点最少选择一个',$url); }
			
			$game_info = $this->game_decode($games);
			@extract($game_info);
			unset($game_info);
			unset($games);
			
			if($error == 1) { $this->error('系统故障，请重新操作',$url); };
			
			$this->mDb->startTrans();
			if(!$cart_id) {
				$sql = "INSERT INTO {$this->mPrefix}mulcard_type(`type_name`,`point_type`,`charge_point`,`spare`,`create_time`)
						VALUES('$card_type_name','$point_type','$ser_game','$source','".time()."')";
				if(!$this->mDb->execute($sql)) {
					$this->mDb->rollback();
					return false;
				}
				
				$last_id = $this->mDb->getLastInsID();
				$sql = "INSERT INTO {$this->mPrefix}mulcard_type_detail(`type_id`,`point_id`,`point_name`) 
						VALUES".$this->insert_suffix($gamers,$last_id);
				if(!$this->mDb->execute($sql)) {
					$this->mDb->rollback();
					return false;
				}
			} else {
				
			}
			
			$this->mDb->commit();
			$this->success('保存成功！',$url);
		} else {
			$this->gamepace  = $this->get_gamepack();
			$this->pointtype = $this->get_game_type();
		
			$this->display('card_type_edit');
		}
	}
	
	/*
	 * 批量插入SQL
	 */
	private function insert_suffix($rs,$last_id=0) { 
		if(!$rs || !$last_id) { return false; }
		
		$sql_suffix = '';
		foreach($rs as $row) {
			$sql_suffix .= "('$last_id','{$row['id']}','{$row['name']}'),";
		}
		return rtrim($sql_suffix,',');
	}
	
	/*
	 * 游戏数组解码
	 */
	private function game_decode($games) {
		$rs = array();
		
		foreach($games as $key=>$value) {
			$vals = explode('|',$value);
			if(count($vals) == 2) {
				$id   = $vals[0] ? intval(trim($vals[0])) : 0;
				$name = $vals['1'] ? urldecode(trim($vals[1])) : '';
				
				if($id > 0 && $name) {
					$rs[$key]['id'] = $id;
					$rs[$key]['name'] = $name;
				}
			}
		}
		
		$ser_game = serialize($rs);
		return array(
					'ser_game'=>$ser_game,
					'gamers'   =>$rs,
					'error'	  => count($games) == count($rs) ? 0 : 1
		);
	}
		
	/*
	 * 获得分类管理页面提交过来的数据
	 */
	private function get_type_data() {
		return array(
					'card_type_name'=>I('post.card_type_name',''),
					'point_type'    =>I('post.point_type',0),
					'source'        =>I('post.source',''),
					'games'         =>I('post.game_id','')
		);
	}
	/*
	/*
	 * 获得创建卡页面提交过来的数据
	*/
	private function get_create_data() {
		return array(
				'pid'           =>I('post.pid',0),
				'card_id_prefix'=>I('post.card_id_prefix',0),
				'codenum'       =>I('post.codenum',0),
				'source'        =>I('post.source',''),
				'expire_time'   =>I('post.expire_time',''),
				'games'         =>I('post.game_id','')
		);
	}
	
	/*
	 * Ajax方法，用于编辑多选卡分类显示内容
	*/
	public function get_type_point($cid=0) {
		exit(json_encode($this->get_gamepack($cid)));
	}
	
	/*
	 * 获得渠道
	*/
	private function get_channel() {
		return M("dealer")->where("`id`>=10")->order("id DESC")->select();
	}
	
	/*
	 * 获得包月游戏包
	*/
	private function get_gamepack($type=0) {
		return M("chargepoint")->field("id,name")->where("type='$type'")->select();
	}
	
	/*
	 * 游戏点
	 */
	private function get_game_type($type=0) {
		return array(
						'0'=>array('id'=>0,'name'=>'游戏包'),
						'1'=>array('id'=>1,'name'=>'存档'),
						'2'=>array('id'=>2,'name'=>'虚拟币'),
						'3'=>array('id'=>3,'name'=>'单次游戏'),
						'4'=>array('id'=>4,'name'=>'街机投币'),
						'5'=>array('id'=>5,'name'=>'擂台赛'),
					);
	}
}