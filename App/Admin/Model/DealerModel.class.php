<?php
namespace Admin\Model;
use Think\Model;
class DealerModel extends Model
{
	public function get_data()
	{
		return $this->select();
	}
	public function add_data($arr)
	{
		if(!$arr)
		{
			return false;
		}
		$res=$this->add($arr);
		if(!$res)
		{
			return false;
		}
		return $res;
	}
	public function get_info_by_id($id,$field=null)
	{
		$memcache_key='dealer_info_by_id_'.$id;
		$info=S($memcache_key);
		if($info===false)
		{
			$condition['id']=$id;
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
	public function delete_data_by_where($condition)
	{
		if($condition)
		{
			$res=$this->where($condition)->delete();
			if($res)
			{
				if(isset($condition['id']))
				{
					$memcache_key='dealer_info_by_id_'.$condition['id'];
					S($memcache_key,null);
				}
				return true;
			}
			return false;
		}
		return false;
	}
	public function save_data($data)
	{

		$res=$this->save($data);
		if($res)
		{
			if(isset($data['id']))
			{
				$memcache_key='dealer_info_by_id_'.$data['id'];
				S($memcache_key,null);
			}
		}
		return $res;
	}
}