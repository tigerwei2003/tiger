<?php
namespace Common\Model;
use Think\Model;
class GamepackModel extends Model
{
	public function update_gamepack_sort($arr)
	{
		$gamepack_arr= array();
		$gamepack_arr = explode(",", $arr);
		$num = $this->count();//获取最大的weight
		foreach($gamepack_arr as $key=>$val){
			$data = $update_condtion = array();
			$update_condtion['pack_id'] =$val;
			$data['weight'] = $num--;
			$res=$this->where($update_condtion)->save($data);
			if($res===false)
			{
				return false;
			}
			$memcache_key='gamepack_pack_id_'.$val;
			S($memcache_key,null);
		}
		$memcache_key='gamepack_api_all_data';
		$res=S($memcache_key,null);
		return true;
	}
	public function api_get_all_data()
	{
		$memcache_key='gamepack_api_all_data';
		$res=S($memcache_key);
		if($res===false)
		{
			$condition['status']=1;
			$res=$this->where($condition)->order("weight desc")->select();
			if($res!==false)
			{
				S($memcache_key,$res);
			}
			return $res;
		}
		return $res;
	}
	public function get_all_data()
	{

		$res=$this->order("weight desc")->select();
		return $res;
	}
	public function get_info_by_packid($pack_id)
	{
		$memcache_key='gamepack_pack_id_'.$pack_id;
		$info=S($memcache_key);
		if(!$info)
		{
			$condition['pack_id']=$pack_id;
			$info=$this->where($condition)->find();
			if($info)
			{
				S($memcache_key,$info);
				return $info;
			}
			return false;
		}
		return $info;
	}
	public function add_data($data)
	{
		$res=$this->add($data);
		if($res)
		{
			$memcache_key='gamepack_api_all_data';
			$res=S($memcache_key,null);
		}
		return $res;
	}
	public function save_data($data,$pack_id)
	{
		$memcache_key='gamepack_pack_id_'.$pack_id;
		$condition['pack_id']=$pack_id;
		$res=$this->data($data)->where($condition)->save();
		if($res)
		{
			S($memcache_key,null);
			$memcache_key='gamepack_api_all_data';
			S($memcache_key,null);
				
		}
		return $res;

	}
	public function delete_data($pack_id)
	{
		$condition['pack_id']=$pack_id;
		$res=$this->where($condition)->delete();
		if($res)
		{
			$memcache_key='gamepack_pack_id_'.$pack_id;
			S($memcache_key,null);
			$memcache_key='gamepack_api_all_data';
			S($memcache_key,null);
		}
		return $res;
	}
}