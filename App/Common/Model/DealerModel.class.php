<?php
namespace Common\Model;
use Think\Model;
class DealerModel extends Model
{
	public function get_data()
	{
		return $this->select();
	}
}