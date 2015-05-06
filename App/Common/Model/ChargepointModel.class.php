<?php
namespace Common\Model;
use Think\Model;
class ChargepointModel extends Model
{
	public function get_info_by_id($id)
	{
		$memcache_key='chargepoint_info_id_'.$id;
		$res=S($memcache_key);
		if($res===false)
		{
			$condition['id']=$id;
			$info=$this->where($condition)->find();
			if($info!==false)
			{
				S($memcache_key,$info);
				return $info;
			}
			return false;
		}
		return $res;
	}
}