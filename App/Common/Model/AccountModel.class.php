<?php
namespace Common\Model;
use Think\Model;
class AccountModel extends Model
{
	/*
	 * 根据绑定的手机获取用户信息
	*/
	public function get_info_by_phone($phone)
	{
		$memcache_key='account_info_bind_phone_'.$phone;
		$info=S($memcache_key);
		if(!$info)
		{
			$condition['bind_phone']=$phone;
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
	/*
	 * 根据邮箱获取用户信息
	 */
	public function get_info_by_email($email)
	{
		$memcache_key='account_info_bind_email_'.$email;
		$info=S($memcache_key);
		if(!$info)
		{
			$condition['bind_email']=$email;
			$info=$this->where($condition)->find();
			if($info)
			{
				S($memcache_key,$info);
			}
		}
		return $info;
	}
	/*
	 * 获取账号信息通过id
	*/
	public function get_info_by_id($account_id,$field=null)
	{
		$memcache_key='account_info_id_'.$account_id;
		$info=S($memcache_key);
		if($info===false)
		{
			$condition['id']=$account_id;
			$info=$this->where($condition)->find();
			if($info)
			{
				S($memcache_key,$info);
			}else
				return $info;
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
				$arr[$val]=$info[$val];
			}
			return $arr;
		}
		return $info;
	}
	/*
	 * 更新账号信息
	*/
	public function save_data($account_id,$data)
	{
		$condition['id']=$account_id;
		$res=$this->where($condition)->save($data);
		if($res)
		{
			$this->clear_cache($account_id);
		}
		return $res;

	}
	/*
	 * 清空帐号信息的缓存
	*/
	public function clear_cache($account_id)
	{
		$memcache_key_account_id='account_info_id_'.$account_id;
		S($memcache_key_account_id,null);
	}
	/*
	 * 绑定手机
	*/
	public function bind_phone($account_id,$phone)
	{

		$condition['id']=$account_id;
		$data['bind_phone']=$phone;
		$res=$this->where($condition)->save($data);
		if($res)
		{
			$memcache_key_phone='account_info_bind_phone_'.$phone;
			S($memcache_key_phone,null);
			$this->clear_cache($account_id);
		}
		return $res;
	}

}
