<?php
namespace Common\Model;
use Think\Model;
class PidSetModel extends Model
{
	public function get_info_by_pid($pid,$field=null)
	{
		$memcache_key='pid_set_info_by_pid_'.$pid;
		$res=S($memcache_key);
		if(!$res)
		{
			$condition['pid']=$pid;
			$res=$this->where($condition)->find();
			if($res)
			{
				S($memcache_key,$res);
			}else
				return $res;
		}
		if($field)
		{
			$res=arr_to_arr($res, $field);
		}
		return $res;
	}
	public function add_data($data)
	{
		$res=$this->add($data);
		return $res;
	}
	public function save_data($data)
	{
		$memcache_key='pid_set_info_by_pid_'.$data['pid'];
		$res=$this->save($data);
		if($res)
		{
			S($memcache_key,null);
		}
		return $res;
	}
}