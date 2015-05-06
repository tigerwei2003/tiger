<?php
namespace Common\Model;
use Think\Model;
class LinkAccountGamepackModel extends Model
{
	public function get_info_by_account_id_gamepack_id($account_id,$gamepack_id)
	{
		$condition['account_id']=$account_id;
		$condition['gamepack_id']=$gamepack_id;
		$info=$this->where($condition)->find();
		return $info;
	}
}