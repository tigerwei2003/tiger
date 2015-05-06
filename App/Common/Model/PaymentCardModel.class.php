<?php
namespace Common\Model;
use Think\Model;
class PaymentCardModel extends Model
{
	/*
	 * 根据条件查找充值卡信息
	*/
	public function get_info_by_where($para)
	{
		if(!$para)
		{
			return false;
		}else
		{
			$info=$this->where($para)->find();
			if($info)
			{
				return $info;
			}
			return false;
		}
	}
	public function update_data_by_where($data,$where)
	{
		if(!$where)
		{
			$res=$this->save($data);
			if(!$res)
			{
				return false;
			}
			return $res;
		}
		$res=$this->where($where)->save($data);
		if(!$res)
		{
			return fasle;
		}
		return $res;
	}
}