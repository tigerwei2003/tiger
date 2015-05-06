<?php
namespace Common\Model;
use Think\Model;
class ArenaBattleModel extends Model
{
	public function get_last_battle_info_by_arena($arena_id,$field=null)
	{
		$condition['arena_id'] = $arena_id;
		$condition['end_time'] = array("neq",0); // 最近一场已经结束的战斗
		$info = $this->where($condition)->order("id desc")->find();
		if($field)
		{
			$arr=array();
			if(!is_array($field))
			{
				$field=explode(',', $field);
			}
			foreach ($field as $val)
			{
				$arr[$val]=$info[$val];
			}
			return $arr;
		}
		return $info;
	}
	public function get_curr_battle_info_by_arena($arena_id,$field=null)
	{
		$condition['arena_id'] = $arena_id;
		$info = $this->where($condition)->order("id desc")->find();
		if($field)
		{
			$arr=array();
			if(!is_array($field))
			{
				$field=explode(',', $field);
			}
			foreach ($field as $val)
			{
				$arr[$val]=$info[$val];
			}
			return $arr;
		}
		return $info;
	}

}