<?php
namespace Common\Model;
use Think\Model;
class ArenaModel extends Model{

	public function add_data($data)
	{
		$res=$this->add($data);
		return $res;
	}
	public function save_data($data,$id)
	{
		$memcache_key='arena_info_id_'.$id;
		$condition['id']=$id;
		$res=$this->data($data)->where($condition)->save();
		if ($res)
		{
			S($memcache_key,null);
		}
		return $res;

	}
	public function get_info_by_id($id)
	{
		$memcache_key='arena_info_id_'.$id;
		$info=S($memcache_key);
		if(!$info)
		{
			$condition['id']=$id;
			$info=$this->field("game_id,status,arena_integral_id,gs_ip,gs_port,bitrate,live_url,hd_live_url,fluent_live_url,open_time,close_time,max_queue_num")->where($condition)->find();
			if($info)
			{
				S($memcache_key,$info);
				return $info;
			}
			return $info;
		}
		return $info;
	}
	public function api_get_all_data()
	{
		$memcache_key='api_arena_list';
		$list=S($memcache_key);
		if(!$list)
		{
			$chargepoint_arena_model=D("ChargepointArena");
			$chargepoint_model=D("Chargepoint");
			$condition['status'] = array('neq','-1');
			$res=$this->field("id,status,arena_type,arena_name,arena_pic,max_number,live_url,hd_live_url,fluent_live_url,bitrate,game_id,arena_pic,region_id,nettest_ip,nettest_port,open_time,close_time,min_skill_level")
						->where($condition)->select();
			$data=array();
			if($res!==false)
			{
				if($res)
				{
					foreach ($res as $val)
					{
						$arena_id=$val['id'];
						$chargepoint_arena_info=$chargepoint_arena_model->get_info_by_arena_id($arena_id);
						$chargepoint_id=$chargepoint_arena_info['chargepoint_id'];
						$chargepoint_info=$chargepoint_model->get_info_by_id($chargepoint_id);
						$coin=$chargepoint_info['coin'];
						$val['coin']=$coin;
						array_push($data, $val);	
					}
				}
				S($memcache_key,$data);
				return $data;
			}
			return false;
		}
		return $list;
		
	}

}