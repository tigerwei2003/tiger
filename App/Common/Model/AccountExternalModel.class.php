<?php
namespace Common\Model;
use Think\Model;
class AccountExternalModel extends Model
{
	/*
	 * 查询信息
	*/
	public function find_account($partner_type, $paccount)
	{
		$condition['partner_type'] = $partner_type;
		$condition['paccount'] = $paccount;
		$info = $this->where($condition)->find();
		if($info)
		{
			return $info;
		}
		return false;
	}
	/*
	 * 添加、更新账号信息
	*/
	public function save_data($account_id, $partner_type, $paccount)
	{
		$data['partner_type'] = $partner_type;
		$data['paccount'] = $paccount;
		$data['update_time'] = time();
		
		$condition['account_id'] = $account_id;
		$res = $this->where($condition)->save($data);
		if($res)
			return true;
		
		$data['create_time'] = time();
		$data['account_id'] = $account_id;
		$res = $this->add($data);
		if($res)
			return true;
		return false;
	}

}
