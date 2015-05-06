<?php
namespace Common\Model;
use Think\Model;
class ArenaGameModel extends Model
{
	public function get_all_data()
	{
		 $res=$this->select();
		 return $res;
	}
	public function api_get_all_data()
	{
		$memcache_key='arena_game_game_list';
		$game_list=S($memcache_key);
		if(!$game_list)
		{
			$game_model=D("Game");
			$game_list=$this->select();
			$cnt=count($game_list);
			for($i=0;$i<$cnt;$i++)
			{
				$game_id=$game_list[$i]['game_id'];
				$game_info=$game_model->get_info_by_id($game_id);
				$game_list[$i]['game_name']=$game_info['game_name'];
			}
			
			if(is_array($game_list))
			{
				S($memcache_key,$game_list);
				return $game_list;
			}
			return false;
		}
		return $game_list;
	}
}