<?php
namespace Common\Model;
use Think\Model;
class PaymentCoinModel extends Model
{
	public function add_data($data)
	{
		$res=$this->add($data);
		return $res;
	}
	public function get_num_by_account_id($account_id)
	{
		$condition['account_id']=$account_id;
		$res=$this->where($condition)->count();
		return $res;
	}
	public function api_get_list_by_account_id($account_id,$offset,$rows,$field=null)
	{
		$condition['account_id']=$account_id;
		if($field)
		{
			if(is_array($field))
			{
				$field=implode(',', $field);
			}
			$list=$this->field($field)->where($condition)->order("id desc")->limit("$offset,$rows")->select();	
		}else
			$list=$this->where($condition)->order("id desc")->limit("$offset,$rows")->select();
		if($list)
		{
			$chargepoint_model=D("Chargepoint");
			$device_model=D("Device");
			$cnt=count($list);
			for($i=0;$i<$cnt;$i++)
			{
				$device_uuid=$list[$i]['device_uuid'];
				$device_info=$device_model->get_info_by_uuid($device_uuid);
				$list[$i]['device_name']=$device_info['byname'];
				$list[$i]['device_id']=$device_info['id'];
				$chargepoint_id=$list[$i]['chargepoint_id'];
				$chargepoint_info=$chargepoint_model->get_info_by_id($chargepoint_id);
				$list[$i]['chargepoint_type']=$chargepoint_info['type_name'];
				$list[$i]['chargepoint_name']=$chargepoint_info['name'];
			}
		}
		return $list;
	}
}

