<?php
namespace Common\Model;
use \Think\Model;
class LinkGamecategoryGameModel extends Model
{
	
	public function add_data($data)
	{
		if(!isset($data['gamecategory_id']) || !isset($data['game_id']))
		{
			return false;
		}else
		{
			$res=$this->add($data);
			if($res)
			{
				$memcache_key='link_gamecategory_game_gamecategory_id_'.$data['gamecategory_id'];
				S($memcache_key,null);
			}
			return $res;
		}
	}
	public function delete_data($id)
	{
		$condition['id']=$id;
		$info=$this->where($condition)->find();
		if($info)
		{
			$cat_id=$info['gamecategory_id'];
			$game_id=$info['game_id'];
			$res=$this->where($condition)->delete();
			if($res)
			{
			$memcache_key='link_gamecategory_game_gamecategory_id_'.$cat_id;
			S($memcache_key,null);
			}
			return $res;
		}else
			return false;
	}
	public function get_game_by_catid($cat_id)
	{
		if(!$cat_id)
		{
			return false;
		}
		$memcache_key='link_gamecategory_game_gamecategory_id_'.$cat_id;
		$res=S($memcache_key);
		if(!$res)
		{
			$condition['gamecategory_id']=$cat_id;
			$res=$this->where($condition)->order("weight desc")->select();
			if($res)
			{
				/* 		$cnt=count($res);
				 $game_model=D("Game");
				for($i=0;$i<$cnt;$i++)
				{
				$game_id=$res[$i]['game_id'];
				$game_info=$game_model->get_info_by_id($game_id);
				$res[$i]['game_info']=$game_info;
				} */
				S($memcache_key,$res);
				return $res;
			}
			return false;
		}
		return $res;
	}
	public function sava_game_sort($game_id_data,$cat_id)
	{
		$g_num = $this->where("gamecategory_id=$cat_id")->count();//获取最大的weight
		$memcache_key='link_gamecategory_game_gamecategory_id_'.$cat_id;
		S($memcache_key,null);
		foreach($game_id_data as $key =>$val){
			$data = $update_condtion = array();
			$update_condtion['game_id'] = intval($val);
			$update_condtion['gamecategory_id']=$cat_id;
			$data['weight'] = $g_num--;
			$res=$this->data($data)->where($update_condtion)->save();
			
			if($res===false)
			{
				
				return false;
			}
		}
		return true;
	}
	public function get_category_by_game_id($gameid)
	{
		$memcache_key='link_gamecategory_game_data_by_game_id_'.$gameid;
		$res=S($memcache_key);
		if($res===false)
		{
			$condition['game_id']=$gameid;
			$res=$this->field("gamecategory_id")->where($condition)->select();
			if($res!==false)
			{
				S($memcache_key,$res);
			}
			return $res;
		}
		return $res;
	}
}