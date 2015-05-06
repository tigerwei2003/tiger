<?php
namespace Admin\Model;
use Think\Model;
class AccesssModel extends Model
{
	/*
	 * 根据条件获取角色所有权限的所有的node节点
	*/
	public function get_acl_by_roleId($role_id)
	{
		if(!$role)
		{
			return false;
		}else
		{
			$condition['role_id']=$role_id;
			$condition['level']=array('GT',0);
			$result=$this->where($condition)->select();
			if($result)
			{
				return $result;
			}
			return false;

		}
	}
	/*
	 * 根据角色id和level获取node数据
	*/
	public function get_acl_by_role_level($role_id,$level=null)
	{
		$condition=array();
		if($role_id)
		{
			$condition['role_id']=$role_id;
		}
		if($level)
		{
			$condition['level']=$level;
		}
		if(!$condition)
		{
			$condition=1;
		}
		$result=$this->where($condition)->order("node_id asc")->select();
		return $result;
	}
	public function delete_data($where)
	{
		if(!is_array($where))
		{
			return false;
		}
		$res=$this->where($where)->delete();
		if($res!==false)
		{
			return true;
		}
		return false;
	}
	public function get_data_by_where($where)
	{
		if(!is_array($where))
		{
			return false;
		}else
		{
			$res=$this->where($where)->select();
			return $res;
		}
	}
}