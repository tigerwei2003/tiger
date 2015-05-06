<?php
namespace Common\Model;
use Think\Model;
class TaskModel extends Model
{
	public function get_all_data($field=null)
	{
		$res=$this->select();
		if($res===false)
		{
			return false;
		}
		if($field)
		{
			$arr=arr_to_arr($res, $field);
			return $arr;
		}
		return $res;
	}
	/*
	 * $id 主键ID
	*/
	public function get_info_by_id($id,$field=null)
	{
		$memcache_key='task_type_info_by_id_'.$id;
		$info=S($memcache_key);
		if($info===false)
		{
			$condition['id']=$id;
			$info=$this->where($condition)->find();
			if($info)
			{
				S($memcache_key,$info);
			}else
				return $info;
		}
		if($field)
		{
			$arr=arr_to_arr($info, $field);
			return $arr;
		}
		return $info;
	}
	/*
	 * $data 需要添加的数组信息
	* 添加信息
	*/
	public function  add_data($data)
	{
		$res=$this->add($data);
		return $res;
	}
	/*
	 * $id 主键ID
	 * $data
	 */
	public function save_data($id,$data)
	{
		$memcache_key='task_type_info_by_id_'.$id;
		$condition['id']=$id;
		$res=$this->where($condition)->save($data);
		if($res)
		{
			S($memcache_key,null);
		}
		return $res;
	}
	/*
	 * $id 主键ID
	 */
	public function delete_data($id)
	{
		$memcache_key='task_type_info_by_id_'.$id;
		$condition['id']=$id;
		$res=$this->where($condition)->delete();
		if($res)
		{
			S($memcache_key,null);
		}
		return $res;
	}
}