<?php
namespace Common\Model;
use Think\Model;
class RecommendModel extends Model
{
	protected $cache_flag;
	protected function get_cache_flag_val()
	{
		$this->cache_flag='recommend_cache_flag_';
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
		$this->cache_flag='recommend_cache_flag_';
		S($this->cache_flag,time());
	}

	public function add_data($data)
	{
		$res=$this->add($data);
		if($res)
		{
			/* $memcache_key="recommend_all_api_data";
			 S($memcache_key,null);
			$memcache_key="recommend_all_api_data_company";
			S($memcache_key,null); */
			$this->reset_cache_flag_val();
		}
		return $res;
	}
	public function save_data_by_id($data,$id)
	{
		$condition['id']=$id;
		$res = $this->data($data)->where($condition)->save();
		if($res)
		{
			/* $memcache_key="recommend_all_api_data";
			 $memcache_key_company="recommend_all_api_data_company";
			S($memcache_key_company,null);
			S($memcache_key,null); */
			$this->reset_cache_flag_val();
			$memcache_key='recommend_info_id_'.$id;
			S($memcache_key,null);

			return $res;
		}
		return false;
	}
	public function get_info_by_id($id)
	{
		$memcache_key='recommend_info_id_'.$id;
		$res=S($memcache_key);
		if($res===false)
		{
			$condition['id']=$id;
			$res=$this->where($condition)->find();
			if($res)
			{
				S($memcache_key,$res);
			}
		}
		return $res;
	}
	public function get_all_data()
	{
		$res=$this->select();
		return $res;
	}
	public function get_api_all_data($field=null)
	{
		$client_ip = get_client_ip();
		$company_ip='124.207.55.164';
		if($client_ip==$company_ip)
		{
			$memcache_key="recommend_all_api_data";
		}
		else
		{
			$memcache_key="recommend_all_api_data_company";
		}
		if($field)
		{
			$memcache_key=$memcache_key.$field;
		}
		$cache_flag_val=$this->get_cache_flag_val();
		$memcache_key=md5($cache_flag_val.$memcache_key);
		$result=S($memcache_key);
		if(!$result)
		{
			if($client_ip==$company_ip)
			{
				$condition['status']=array(array('eq',1),array('eq',2),'or');
			}else
			{
				$condition['status']=1;
			}
			$now = time();
			$condition['start_time'] = array('elt',$now);
			$condition['end_time'] = array('egt',$now);
			$condition['pid_limit']=0;
			$min_end_time=$this->where($condition)->min('end_time');
			$save_time=$min_end_time-$now;
			$result=$this->where($condition)->order('weight desc')->select();
			if($result)
			{
				if(!$field)
				{
					S($memcache_key,$result,$save_time);
				}else
				{
					$arr=array();
					$arr=arr_to_arr($result, $field);
					S($memcache_key,$arr,$save_time);
					return $arr;
				}

			}else
				return false;
		}
		return $result;
	}
	/*
	 * 按渠道获取针对该渠道开放的所有推荐
	*/
	public function get_pid_recommend_data_by_pid($pid,$field=null)
	{
		$common_data=$this->get_api_all_data($field);
		$memcache_key='pid_recommend_data_by_pid_'.$pid;
		$pid_recommend_data=S($memcache_key);
		if($pid_recommend_data===false)
		{
			$pid_recommend_data=$this->join('july_pid_recommend ON july_recommend.id=july_pid_recommend.recommend_id')->where("july_pid_recommend.pid='{$pid}'")->field('july_recommend.*')->select();
			if($pid_recommend_data!==false)
			{
				S($memcache_key,$pid_recommend_data);
			}
		}
		if($field)
		{
			$pid_recommend_data=arr_to_arr($pid_recommend_data, $field);
		}
		$res=array_merge($common_data,$pid_recommend_data);
		return $res;
	}

}

