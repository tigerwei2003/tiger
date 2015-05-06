<?php
namespace Common\Model;
use Think\Model;
class PidModel extends Model
{
	public function get_all_data()
	{
		$memcache_key='pid_all_data';
		$res=S($memcache_key);
		if($res===false)
		{
			$res=$this->select();
			if($res)
			{
				S($memcache_key,$res);
			}
		}
		return $res;
	}
}