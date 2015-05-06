<?php
namespace Common\Model;
use Think\Model;
class IncomeCoinModel extends Model{
	/*
	 * $data 添加的数组
	 */
	public function add_data($data)
	{
		 $res=$this->add($data);
		 return $res;
	}
}