<?php
namespace Common\Model;
use Think\Model;
class PaymentRmbModel extends Model
{
	public function get_data_by_account_id($account_id)
	{
		$memcache_key='payment_rmb_account_id'.$account_id;
		$res=S($memcache_key);
		if($res===false)
		{
			$condition['account_id']=$account_id;
			$res=$this->where($condition)->select();
			if($res!==false)
			{
				S($memcache_key,$res);
			}
			return $res;
		}
		return $res;
			
	}
	public function get_data_by_account_id_limit($account,$offset,$rows,$field=null)
	{

		$condition['account_id']=$account;
		if($field)
		{
			if(is_array($field))
			{
				$field=implode(',', $field);
			}
			$res=$this->field($field)->where($condition)->order("id desc")->limit("$offset,$rows")->select();
		}else
			$res=$this->where($condition)->limit("$offset,$rows")->order("id desc")->select();
		return $res;
	}
}
