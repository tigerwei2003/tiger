<?php
namespace Common\Model;
use Think\Model;
class AccountTaskModel extends Model
{
	public function get_info_by_where($account,$task_id,$date=null)
	{
		if($date)
		{
			$memcache_key='account_task_info_by_account_id_task_id_date_'.$account.$task_id.$date;
		}else
			$memcache_key='account_task_info_by_account_id_task_id_'.$account.$task_id;
		$info=S($memcache_key);
		if($info===false)
		{
			$condition['account_id']=$account;
			$condition['task_id']=$task_id;
			if($date)
			{
				$condition['date']=$date;
			}
			$info=$this->where($condition)->find();
			if($info)
			{
				S($memcache_key,86400);
			}
		}
		return $info;
	}
	public function add_data($data)
	{
		$res=$this->add($data);
		return $res;
	}
}