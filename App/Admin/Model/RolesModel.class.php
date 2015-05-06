<?php
namespace Admin\Model;
use Think\Model;
class RolesModel extends Model
{
	public function get_all_role_data()
	{
		$role_all_data=$this->select();
		if($role_all_data)
		{
			return $role_all_data;
		}
		return false;
	}
	public function get_info_by_id($role_id)
	{
		$memcache_key='role_id_'.$role_id;
		$role_info=S($memcache_key);
		if($role_info)
		{
			return $role_info;
		}else
		{
			$condition['id']=$role_id;
			$role_info=$this->where($condition)->find();
			if($role_info)
			{
				S($memcache_key,$role_info);
				return $role_info;
			}else
				return false;
		}
	}
	public function add_data($data)
	{
		if(!$data)
		{
			return false;
		}else
		{
			$res=$this->add($data);
			if($res)
			{
				return $res;
			}else
				return false;
		}
	}
	public function update_data($data)
	{
		$id=$data['id'];
		$res=$this->save($data);
		if($res!==false)
		{
			$memcache_key='role_id_'.$id;
			S($memcache_key,null);
			return $res;
		}else
		{
			return false;
		}
	}
	public function delete_data($where)
	{
		if(!is_array($where))
		{
			return false;
		}else
		{
			$res=$this->where($where)->delete();
			if($res)
			{
				$memcache_key='role_id_'.$where['id'];
				S($memcache_key,null);
				return true;
			}
			return false;
		}
	}


}