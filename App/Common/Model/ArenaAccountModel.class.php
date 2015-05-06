<?php
namespace Common\Model;
use Think\Model;
class ArenaAccountModel extends Model
{
	public function get_info_by_game_account($account_id,$game_id)
	{
		$condition['account_id']=$account_id;
		$condition['game_id']=$game_id;
		$info=$this->where($condition)->find();
		return $info;
	}
	public function add_data($data)
	{
		return $this->add($data);
	}
	
	//获取擂台的账号信息
	public function get_arena_account_by_account_game($account_id,$game_id){
		$memcache_key='arena_account_info_by_account_id_game_id_'.$account_id.$game_id;
		$info=S($memcache_key);
		if($info === false){
			$account_model=D("Account");
			$account_info=$account_model->get_info_by_id($account_id);
            if(!$account_info)
            	return false;
            $account=$this->get_info_by_game_account($account_id,$game_id);
            if(!$account){
            	$condition['account_id'] = $account_id;
            	$condition['game_id'] = $game_id;
            	$new_arena_account = $this->add_data($condition);
            	if(!$new_arena_account)
            		return false;
            	$account=$condition;
            }
            $account['nickname']=$account_info['nickname'];
            $account['bean_num']=$account_info['bean'];
            $account['coin_num']=$account_info['gift_coin_num'];
            $account['gold_num']=$account_info['gold'];
            $account['avatar']=$account_info['avatar'];
            S($memcache_key,$account);
            return $account;	  
		}
		return $info;
	}
	
	//
	
}