<?php
namespace Common\Model;
use Think\Model;
class TmpBindModel extends Model
{
	public function get_num_by_ip($ip)
	{
		$date=date('Y-m-d');
		$memcache_key='tmp_bind_ip_sendnum_'.$ip.$date;
		if(S($memcache_key))
		{
			return S($memcache_key);
		}
		$condition['ip']=$ip;
		$condition['date']=date('Y-m-d');
		$num=$this->where($condition)->count();
		if($num)
		{
			S($memcache_key,$num,86400);
			return $num;
		}
		return false;
	}
	public function get_num_by_phone_or_email($phone)
	{
		$date=date('Y-m-d');
		$memcache_key='tmp_bind_phone_or_email_sendnum_'.$phone.$date;
		if(S($memcache_key))
		{
			return S($memcache_key);
		}
		$condition['phone_or_email']=$phone;
		$condition['date']=date('Y-m-d');
		$num=$this->where($condition)->count();
		if($num)
		{
			S($memcache_key,$num,86400);
			return $num;
		}
		return false;
	}
	public function get_last_row($where=null)
	{
		if(!$where)
			$where=1;
		$data=$this->where($where)->order("id desc")->find();
		return $data;
	}
	public function add_data($data)
	{
		$res=$this->add($data);
		if($res)
		{
			$date=$data['date'];
			$ip=$data['ip'];
			$phone=$data['phone_or_email'];
			$ip_mem_key='tmp_bind_ip_sendnum_'.$ip.$date;
			$phone_mem_key='tmp_bind_phone_or_email_sendnum_'.$phone.$date;
			$ip_mem_res=S($ip_mem_key);
			$phone_res=S($phone_mem_key);
			if($ip_mem_res)
			{
				S($ip_mem_key,$ip_mem_res+1);
			}
			if($phone_res)
			{
				S($phone_mem_key,$phone_res+1);
			}
		}
		return $res;
	}
	public function save_send_code($where,$data)
	{
		$res=$this->where($where)->save($data);
		return $res;
	}
	public function save_data($data)
	{
		return $this->save($data);
	}
}