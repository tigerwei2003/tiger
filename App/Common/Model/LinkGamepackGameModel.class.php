<?php
namespace Common\Model;
use Think\Model;
class LinkGamepackGameModel extends Model
{
	public function get_all_game_by_packid($packid)
	{
		$memcache_key='link_gamepack_game_gamepack_id_'.$packid;
		$data=S($memcache_key);
		if($data===false)
		{
			$condition['gamepack_id']=$packid;
			$res=$this->where($condition)->order("weight desc")->select();
			if($res!==false)
			{
				S($memcache_key,$res);
			}
			return $res;
		}
		return $data;
	}
	public function get_all_gameid_by_packid($packid)
	{
		$arr=array();
		$condition['gamepack_id']=$packid;
		$res=$this->where($condition)->select();
		$cnt=count($res);
		for($i=0;$i<$cnt;$i++)
		{
			$game_id=$res[$i]['game_id'];
			$arr[]=$game_id;
		}
		if($arr)
		{
			return $arr;
		}
		return false;
	}
	public function add_data($data)
	{
		$packid=$data['gamepack_id'];
		$memcache_key='link_gamepack_game_gamepack_id_'.$packid;
		$res=$this->add($data);
		if($res)
		{
			S($memcache_key,null);				
		}
		return $res;
	}
	public function delete_data($id)
	{
		$condition['id']=$id;
		$games = $this->find($id);
		$pack_id = $games['gamepack_id'];
		$game_id = $games['game_id'];
		if(!$pack_id || !$game_id)
		{
			return false;
		}else
		{
			$res=$this->where($condition)->delete();
			if($res)
			{
				$memcache_key='link_gamepack_game_gamepack_id_'.$pack_id;
				S($memcache_key,null);
			}
			return $res;
		}
	}
	public function update_game_sort($data,$pack_id)
	{
		$g_num = $this->where("gamepack_id=$pack_id")->count();//获取最大的weight
		foreach($data as $key=>$val){

			$data2 = $update_condtion = array();
			$update_condtion['game_id'] = intval($val);
			$update_condtion['gamepack_id']=$pack_id;
			$data2['weight'] = $g_num--;
			$res=$this->where($update_condtion)->save($data2);
			if($res===false)
			{
				return false;
			}
		}
		$memcache_key='link_gamepack_game_gamepack_id_'.$pack_id;
		S($memcache_key,null);
		return true;
	}
}


