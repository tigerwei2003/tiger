<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
// 全系统删除操作，仅对超级管理员开放
class DeleteController extends BaseController {

	/*删除游戏*/
	public function games(){
		$game_model = D('Game');
		$id = I('id', -1);
		$iddel = $game_model->delete_info_by_id($id);
		if($iddel){
			systemlog(3,'game',$game_model->GetLastSql(),'删除游戏，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除区域*/
	public function area(){
		$db = D('Region');
		$id = I('id', -1);
		$iddel = $db->delete_info_by_id($id);
		if($iddel){
			systemlog(3,'region',$db->GetLastSql(),'删除区域，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除服务器*/
	public function server(){
		$db = D('Server');
		$id = I('id', -1);
		$iddel = $db->delete_info_by_sid($id);
		if($iddel){
			systemlog(3,'server',$db->GetLastSql(),'删除服务器，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}


	/*删除邀请码*/
	public function code(){
		$db = M('invitation_code');
		$id = I('id', '');
		$iddel = $db->where(array('code'=>$id))->delete();
		if($iddel){
			systemlog(3,'invitation_code',$db->GetLastSql(),'删除邀请码，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除充值卡*/
	public function rechargecard(){
		$db = M('payment_card');
		$id = I('id', '');
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			systemlog(3,'payment_card',$db->GetLastSql(),'删除充值卡，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}


	/*删除管理员*/
	public function user(){
		$db = M('admins');
		$id = I('id', -1);
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			systemlog(3,'admin',$db->GetLastSql(),'删除管理员，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}

	}


	/*删除游戏包*/
	public function gamepack(){
		$db=D("Gamepack");
		$id = I('id', -1);
		//判断是否有用户购买过这个包，如有则禁止删除
		$istrue = M('link_account_gamepack')->where(array('gamepack_id'=>$id))->select();
		if($istrue){
			$this->error('有人购买过这个游戏包，禁止删除！');
		}else{
			//判断包中是否有游戏，如有游戏禁止删除
			$istrue = M('link_gamepack_game')->where(array('gamepack_id'=>$id))->select();
			if($istrue){
				$this->error('游戏包中还存在游戏，禁止删除！');
			}else{
				$iddel = $db->delete_data($id);
				if($iddel){
					systemlog(3,'gamepack',$db->GetLastSql(),'删除游戏包，编号：'.$id);
					$this->success('删除成功！');
				}else{
					$this->error('删除失败！');
				}
			}
		}

	}

	/*删除游戏包中的游戏*/
	public function gamepack_game(){
		$db=D("LinkGamepackGame");
		$id = I('id', -1);
		$games = $db->find($id);
		$pack_id = $games['gamepack_id'];
		$game_id = $games['game_id'];
		$iddel=$db->delete_data($id);
		if($iddel){
			systemlog(3,'link_gamepack_game',$db->GetLastSql(),'删除编号：'.$id.'；游戏包ID：'.$pack_id.'；游戏ID：'.$game_id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}

	}

	/*删除游戏类别*/
	public function gamecategory(){
		$db=D("Gamecategory");
		$id = I('id', -1);
		//判断包中是否有游戏，如有游戏禁止删除
		$istrue = M('link_gamecategory_game')->where(array('gamecategory_id'=>$id))->select();
		if($istrue){
			$this->error('类别中存在游戏，请首先清空然后再删除！');
		}else{
			$iddel = $db->delete_data($id);
			if($iddel){
				systemlog(3,'gamecategory',$db->GetLastSql(),'删除游戏类别，编号：'.$id);
				$this->success('删除成功！');
			}else{
				$this->error('删除失败！');
			}
		}

	}

	/*删除游戏类别中的游戏*/
	public function gamecategory_game(){
		$db=D("Link_GamecategoryGame");
		$id = I('id', -1);
		$games = $db->find($id);
		$cat_id = $games['gamecategory_id'];
		$game_id = $games['game_id'];
		$iddel = $db->delete_data($id);
		if($iddel){
			systemlog(3,'link_gamecategory_game',$db->GetLastSql(),'删除编号：'.$id.'；游戏类别ID：'.$cat_id.'；游戏ID：'.$game_id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}

	}

	/*删除计费点*/
	public function chargepoint(){
		$db = M('chargepoint');
		$pack_db = M('chargepoint_gamepack');
		$save_db = M('chargepoint_gamesave');
		$coin_db = M('chargepoint_coin');
		$runonce_db = M('chargepoint_runonce');
		$arcade_db = M('chargepoint_arcade');
		$arena_db = M('chargepoint_arena');
		$id = I('id', -1);
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			$pack_db->where(array('chargepoint_id'=>$id))->delete();
			$save_db->where(array('chargepoint_id'=>$id))->delete();
			$coin_db->where(array('chargepoint_id'=>$id))->delete();
			$runonce_db->where(array('chargepoint_id'=>$id))->delete();
			$arcade_db->where(array('chargepoint_id'=>$id))->delete();
			$arena_db->where(array('chargepoint_id'=>$id))->delete();
			systemlog(3,'chargepoint',$db->GetLastSql(),'删除计费点，编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除角色*/
	public function role() {
		$db = M('roles');
		$id = I('id', -1);
		$istrue = M('role_user')->where(array('role_id'=>$id))->select();
		if($istrue){
			$this->error('该角色有用户存在，禁止删除！');
		}else{
			$iddel = $db->where(array('id'=>$id))->delete();
			if($iddel){
				systemlog(3,'role',$db->GetLastSql(),'删除角色编号：'.$id);
				$this->success('删除成功！');
			}else{
				$this->error('删除失败！');
			}
		}
	}

	/*删除节点*/
	public function node() {
		$db = M('nodes');
		$id = I('id', -1);
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			systemlog(3,'node',$db->GetLastSql(),'删除节点编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除渠道*/
	public function dealer() {
		$db = M('dealer');
		$id = I('id', -1);
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			systemlog(3,'dealer',$db->GetLastSql(),'删除渠道编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除轮播图*/
	public function ad() {
		$db = M('ad_banner');
		$id = I('id', -1);
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			systemlog(3,'ad_banner',$db->GetLastSql(),'删除轮播图编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除硬件信息*/
	public function hardware() {
		$db = M('hardware');
		$id = I('id', -1);
		$iddel = $db->where(array('id'=>$id))->delete();
		if($iddel){
			systemlog(3,'hardware',$db->GetLastSql(),'删除硬件信息编号：'.$id);
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
	}

	/*删除兑换券*/
	public function exchange()
	{
		$model=new \Think\Model();
		$model->startTrans();

		$id=I('id',-1);
		$where=array();
		$where['id']=$id;
		$type=$model->table(C('DB_PREFIX').'exchange_code')->field("type_id,num,surplus_num")->where($where)->find();
		$iddel=$model->table(C('DB_PREFIX').'exchange_code')->where($where)->delete();
		if(!$iddel)
		{
			$this->error("删除失败!");
		}
		$recordcount=$model->table(C('DB_PREFIX').'exchange_record')->where(array('code_id'=>$id))->count();
		if($recordcount!='0')
		{
			$recorddel=$model->table(C('DB_PREFIX').'exchange_record')->where(array('code_id'=>$id))->delete();
			if(!$recorddel)
			{
				$model->rollback();
				$this->error("删除失败!");
			}
		}

		if($type['num']!=0)
		{
			$typesave=$model->table(C('DB_PREFIX').'exchange_type')->where(array("type_id"=>$type['type_id']))->setDec('num',1);
			if(!$typesave)
			{
				$model->rollback();
				$this->error("删除失败!");
			}
			if($type['surplus_num']!=0)
			{
				$typesave=$model->table(C('DB_PREFIX').'exchange_type')->where(array("type_id"=>$type['type_id']))->setDec('surplus_num',1);
				if(!$typesave)
				{
					$model->rollback();
					$this->error("删除失败!");
				}
			}
		}

		$model->commit();

		$this->success('删除成功!');
	}

	/*删除兑换券类型*/
	public function exchange_type()
	{
		$model=new \Think\Model();
		$model->startTrans();
		$id=I("id",-1);
		$where=array();
		$where['type_id']=$id;
		$typedel=$model->table(C('DB_PREFIX').'exchange_type')->where($where)->delete();
		if(!$typedel)
		{
			$this->error("删除失败!");
		}
		$codecount=$model->table(C('DB_PREFIX').'exchange_code')->where($where)->count();
		if($codecount!='0')
		{
			$codedel=$model->table(C('DB_PREFIX').'exchange_code')->where($where)->delete();
			if(!$codedel){
				$model->rollback();
				$this->error("删除失败!");
			}
		}
		$recordcount=$model->table(C('DB_PREFIX').'exchange_record')->where($where)->count();
		if($codecount!='0')
		{
			$recorddel=$model->table(C('DB_PREFIX').'exchange_record')->where($where)->delete();
			if(!$recorddel){
				$model->rollback();
				$this->error("删除失败!");
			}
		}
		$model->commit();
		$this->success("删除成功!");
	}
	
	/*
	 * 删除多选卡记录
	 */
	public function checkcard() { 
		$id=I("get.id",0);
		if(!$id) {
			$this->error("删除失败!");
		}
		$db = new \Think\Model();
		$sql = "DELETE FROM ".C('DB_PREFIX')."mulcard_payment_card WHERE id='$id'";
		if(!$db->execute($sql)) {
			$this->error("删除失败!");
		}
		$this->success("删除成功!");
	}
	
	/*
	 * 删除多选卡类别
	 */
	public function checkcard_type() {
		
	}
}
