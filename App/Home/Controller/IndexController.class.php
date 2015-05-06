<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	
    public function index(){
		
    }
	
	// 用户进入活动页面获取node信息
	public function get_node(){
		$callback = I('callback','');
		$account_id = I('account_id','');
		if($account_id == ''){
			$data = array('ret'=>'-1','msg'=>'参数错误');
		}else{
			$redis = S(array('type'=>'Gloudredis'));
			$key = 'open_frame_'.$account_id;
			$node = $redis->get($key);
			if($node === false)
				$node = 0; // 第一次进入页面,redis中还没有数据
			$data = array('ret'=>'0','msg'=>$node);
		}
		echo $callback.'('.json_encode($data).')';
	}
	
	//用户点击活动节点,存入信息到redis
	public function open_frame(){
		$callback = I('callback','');
		$account_id = I('account_id','');
		if($account_id == ''){
			$data = array('ret'=>'-1','msg'=>'参数错误');
		}else{
			$node = I('node',0);
			$redis = S(array('type'=>'Gloudredis'));
			$key = 'open_frame_'.$account_id;
			if( $redis->set($key, $node, 864000) ) // redis 存入10天
				$data = array('ret'=>0,'msg'=>'success'); //成功
			else
				$data = array('ret'=>'-2','msg'=>'error'); // 失败
		}
		echo $callback.'('.json_encode($data).')';
	}
}