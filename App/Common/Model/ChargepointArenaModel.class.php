<?php
namespace Common\Model;
use Think\Model;
class ChargepointArenaModel extends Model
{
	public function get_info_by_arena_id($arena_id)
	{
		$memcache_key='chargepoint_arena_arena_id_'.$arena_id;
		$res=S($memcache_key);
		if($res===false)
		{
			$condition['arena_id']=$arena_id;
			$res=$this->where($condition)->find();
			if($res!==false)
			{
				S($memcache_key,$res);
				return $res;
			}
			return false;
		}
		return $res;
	}
}