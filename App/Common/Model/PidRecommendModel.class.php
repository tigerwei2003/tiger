<?php
namespace Common\Model;
use Think\Model;
class PidRecommendModel extends Model
{
	public function get_data_by_recommend_id($recommend_id)
	{
		$condition['recommend_id']=$recommend_id;
		$res=$this->where($condition)->select();
		return $res;
	}
	public function delete_data_by_recommend_id($recommend_id)
	{
		$condition['recommend_id']=$recommend_id;
		$data=$this->where($condition)->select();
		$res=$this->where($condition)->delete();
		if($res!==false)
		{
			if($data)
			{
				$cnt=count($data);
				for($i=0;$i<$cnt;$i++)
				{
					$pid=$data[$i]['pid'];
					$memcache_key='pid_recommend_data_by_pid_'.$pid;
					S($memcache_key,null);

				}
			}

		}
		return $res;
	}
	public function add_data($data)
	{
		$pid=$data['pid'];
		$res=$this->add($data);
		if($res)
		{
			$memcache_key='pid_recommend_data_by_pid_'.$pid;
			S($memcache_key,null);
		}
		return $res;
	}
}