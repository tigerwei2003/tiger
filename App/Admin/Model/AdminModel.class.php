<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model
{
	/*
	 * 获取后台用户的信息通过用用户名
	*/
	public function get_userinfo_by_username($username)
	{
		if(!$username)
		{
			return false;
		}else
		{
			$condition['username']=$username;
			$user_info=$this->where($condition)->find();

			if($user_info)
			{
				return $user_info;
			}else
				return false;

		}
	}
	/*
	 * 通过id获取用户信息
	*/
	public function get_userinfo_by_id($id)
	{
		if(!$id)
		{
			return false;
		}else
		{
			$condition['id']=$id;
			$user_info=$this->where($condition)->find();
			if($user_info)
			{
				return $user_info;
			}
			return false;
		}

	}
	/*
	 * 更新用户信息
	* $arr 中必须含有主键
	*/
	public function update_data(array $arr)
	{
		if(!is_array($arr))
		{
			return false;
		}else
		{
			$res=$this->save($arr);
			if($res)
			{
				return $res;
			}
			return false;

		}
	}
	public function add_data($arr)
	{
		if(!is_array($arr))
		{
			return false;
		}else
		{
			$res=$this->add($arr);
			if($res)
			{
				return $res;
			}
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
			return $res;
		}
	}
}