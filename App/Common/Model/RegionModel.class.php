<?php
namespace Common\Model;
use Think\Model;
class RegionModel extends Model
{
	protected $cache_flag;
	protected function get_cache_flag_val()
	{
		$this->cache_flag='region_cache_flag_';
		$val=S($this->cache_flag);
		if(!$val)
		{
			$val=time();
			S($this->cache_flag,$val);
		}
		return $val;
	}
	public function reset_cache_flag_val()
	{
		$this->cache_flag='region_cache_flag_';
		S($this->cache_flag,time());
	}

	public function get_all_data($arr=null)
	{
		if(is_array($arr))
		{
			$res=$this->where($arr)->select();
		}
		else
		{
			$res=$this->select();
		}
		return $res;
	}
	public function add_data($data)
	{
		$res= $this->add($data);
		if($res)
		{
			/* $memcache_key='region_all_data';
			S($memcache_key,null); */
			$this->reset_cache_flag_val();
		}
		return $res;
	}
	public function get_all_region_data($field=null)
	{
		$cache_flag=$this->get_cache_flag_val();
		$memcache_key='region_all_data';
		if($field)
		{
			$memcache_key=md5($cache_flag.$memcache_key.$field);
		}else
		{
			$memcache_key=md5($cache_flag.$memcache_key);
		}
        $res=S($memcache_key);
		if($res===false)
		{
			$condition['pid_limit']=0;
			if($field)
			{
				$res=$this->field($field)->where($condition)->select();
			}
			else
				$res=$this->where($condition)->select();
			if($res)
			{
				S($memcache_key,$res);
			}
		}
		return $res;
	}
	/*
	 * 根据pid获取针对该pid开放的渠道
	*/
	public function get_region_data_by_pid($pid,$field=null)
	{
		$memcache_key='pid_region_data_by_pid_'.$pid;
		$common_data=$this->get_all_region_data($field);
		$pid_region_data=S($memcache_key);
		if($pid_region_data===false)
		{
			$pid_region_data=$this->join('july_pid_region ON july_region.id=july_pid_region.region_id')->where("july_pid_region.pid='{$pid}'")->field('july_region.*')->select();
			if($pid_region_data!==false)
			{
				S($memcache_key,$pid_region_data);
			}
		}
		if($field)
		{
			$pid_region_data=arr_to_arr($pid_region_data, $field);
		}
		$res=array_merge($common_data,$pid_region_data);
		return $res;
	}


	public function save_data($data,$id)
	{
		$condition['id']=$id;
		$res=$this->data($data)->where($condition)->save();
		if($res)
		{
			$memcache_key='region_info_id_'.$id;
			S($memcache_key,null);
			$this->reset_cache_flag_val();
		}
		return $res;
	}
	public function get_info_by_id($id,$field=null)
	{
		$memcache_key='region_info_id_'.$id;
		$info=S($memcache_key);
		if($info===false)
		{
			$condition['id']=$id;
			$info=$this->where($condition)->find();
			if($info){
				S($memcache_key,$info);
			}else
				return $info;
		}
		if($field)
		{
			$arr=array();
			$arr=arr_to_arr($info, $field);
			return $arr;
		}
		return $info;
	}
	public function delete_info_by_id($id)
	{
		$condition['id']=$id;
		$res=$this->where($condition)->delete();
		if($res)
		{
			$memcache_key='region_info_id_'.$id;
			S($memcache_key,null);
			$this->reset_cache_flag_val();
		}
		return $res;
	}
}