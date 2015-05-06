<?php
namespace Common\Model;
use Think\Model;
class ServerModel extends Model
{
	public function add_data($data)
	{
		return $this->add($data);
	}
	public function save_data($data,$sid)
	{
		$condition['sid']=$sid;
		$res=$this->data($data)->where($condition)->save();
		return $res;
	}
	public function get_info_by_id($sid)
	{
		$condition['sid']=$sid;
		return $this->where($condition)->find();
	}
	public function delete_info_by_sid($id)
	{
		$condition['sid']=$id;
		$res=$this->where($condition)->delete();
		return $res;
	}
}