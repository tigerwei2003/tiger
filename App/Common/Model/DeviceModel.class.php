<?php
namespace Common\Model;
use Think\Model;
class DeviceModel extends Model
{
	/*
	 * 获取设备信息
	*
	*/
	public function get_info_by_uuid($uuid,$field=null)
	{
		$memcache_key='device_info_device_uuid_'.$uuid;
		$info=S($memcache_key);
		if($info===false)
		{
			$condition['device_uuid']=$uuid;
			$info=$this->where($condition)->find();
			if($info)
			{
				S($memcache_key,$info);
			}
			else
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
	 * 添加新设备
	*/
	public function add_data($data)
	{
		if(!$data)
		{
			return false;
		}else
		{
			$res=$this->add($data);
			return $res;
		}
	}
	/*
	 * 更新設備信息
	* 这里需要考虑已经缓存的设备信息和账号的已绑定的列表信息，从缓存中删除掉
	*
	*/
	public function save_data($uuid,$data)
	{
		$condition['device_uuid']=$uuid;
		$res=$this->where($condition)->save($data);
		if($res)
		{
			//删除设备缓存信息
			$memcache_key='device_info_device_uuid_'.$uuid;
			S($memcache_key,null);
			//删除账号设备绑定列表缓存
			if(isset($data['bind_account']))
			{
				$memcache_key='device_list_bind_account_'.$data['bind_account'];
				S($memcache_key,null);
			}
		}
		return $res;
	}
	/*
	 * 获取账号绑定的设备列表
	*/
	public function get_bind_device_by_account($account)
	{
		$memcache_key='device_list_bind_account_'.$account;
		$res=S($memcache_key);
		if(!$res)
		{
			$condition['bind_account']=$account;
			$res=$this->where($condition)->order("last_login_time desc")->select();
			if(count($res)>0)
			{
				S($memcache_key,$res);
			}
		}
		return $res;
	}
	/*
	 * 解除设备绑定
	* 需要把设备缓存的信息以及账号绑定设备列表缓存信息都删除掉，重建缓存
	*/
	public function unbind_device($account,$unbind_device)
	{
		$data["bind_account"]=0;
		$condition['device_uuid']=$unbind_device;
		$res=$this->where($condition)->save($data);
		if($res)
		{
			$device_info_key='device_info_device_uuid_'.$unbind_device;

			S($device_info_key,null);

			$device_list_key='device_list_bind_account_'.$account;

			S($device_list_key,null);

		}
		return $res;
	}


}