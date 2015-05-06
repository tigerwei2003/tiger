<?php
namespace Common\Model;
use Think\Model;
class ChargepointArcadeModel extends Model
{
	public function get_info_by_game_id($game_id)
	{
		$condition['game_id']=$game_id;
		$memcache_key='chargepoint_arcade_game_id_'.$game_id;
		$info=S($memcache_key);
		if($info===false)
		{
			$info=$this->where($condition)->find();
			if($info!==false)
			{
				S($memcache_key,$info);
		
			}
			return $info;
		}
		return $info;
	}
}