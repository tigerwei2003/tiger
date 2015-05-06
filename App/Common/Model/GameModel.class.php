<?php
namespace Common\Model;
use Think\Model;
class GameModel extends Model
{
	public function get_info_by_id($id,$field=null)
	{
		$memcache_key='game_info_game_id_'.$id;
		$result=S($memcache_key);
		if($result===false)
		{
			$condition['game_id']=$id;
			$result=$this->where($condition)->find();
			if($result!==false)
			{
				S($memcache_key,$result);
			}else
				return false;

		}
		if($field)
		{
			$arr=array();
			if(!is_array($field))
			{
				$field=explode(',', $field);
			}
			foreach ($field as $val)
			{
				$arr[$val]=$result[$val];
			}
			return $arr;
		}
		return $result;
	}
	public function get_all_data($where=null)
	{
		if($where)
		{
			$result=$this->where($where)->select();
		}else
			$result=$this->select();
		return $result;
	}
	public function  delete_info_by_id($id)
	{
		$memcache_key='game_info_game_id_'.$id;

		$result=S($memcache_key);
		if($result)
		{
			S($memcache_key,null);
		}
		$condition['game_id']=$id;
		$res=$this->where($condition)->delete();
		return $res;
	}
	public function add_data($data)
	{
		$res=$this->add($data);
		return $res;
	}
	public function save_data($data,$id)
	{
		$memcache_key='game_info_game_id_'.$id;

		$result=S($memcache_key);
		if($result)
		{
			S($memcache_key,null);
		}
		$condition['game_id']=$id;
		$res=$this->data($data)->where($condition)->save();
		return $res;
	}

}