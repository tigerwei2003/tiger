<?php
namespace Common\Model;
use Think\Model;
class GamecategoryModel extends Model
{
	public function get_all_data()
	{
		$memcache_admin_key='admin_gamecategory_all';
		$result=S($memcache_admin_key);
		if(!$result)
		{
			//后台系统需要全部数据
			$result=$this->order("weight desc")->select();
			if($result)
			{
				S($memcache_admin_key,$result);
				return $result;
			}
			return false;
		}
		return $result;

	}
	public function get_all_api_data()
	{
		$memcache_api_key='api_gamecategory_all';
		$res=S($memcache_api_key);
		if(!$res)
		{
			//前端接口需要的数据
			$result= $this->field("cat_id,cat_name,summary")->where("status=1")->order("weight desc")->select();
			if($result)
			{
				$res = array();
				foreach ($result as $cat) {
					array_push($res, $cat);
				}
				S($memcache_api_key,$res);
				return $res;
			}
			return false;
		}
		return $res;

	}
	public function save_category_sort($arr)
	{
		$memcache_api_key='api_gamecategory_all';
		$memcache_admin_key='admin_gamecategory_all';
		if(!empty($arr)){
			$data=array();
			$num = $this->count();
			foreach ($arr as $key=>$val){
				$data = $update_condtion = array();
				$data['weight'] = $num--;
				$update_condtion['cat_id'] = intval($val);
				$res=$this->where($update_condtion)->save($data);
				if($res===false)
				{
					return false;
				}
				$memcache_key='gamecategory_cat_id'.$update_condtion['cat_id'];
				S($memcache_key,null);
			}
			S($memcache_admin_key,null);
			S($memcache_api_key,null);
			return true;
		}
		return false;
	}
	public function save_data($cat_id,$data)
	{
		$memcache_api_key='api_gamecategory_all';
		$memcache_admin_key='admin_gamecategory_all';
		$memcache_key='gamecategory_cat_id'.$cat_id;
		$condition['cat_id']=$cat_id;
		$res=$this->where($condition)->save($data);
		if($res)
		{
			S($memcache_admin_key,null);
			S($memcache_api_key,null);
			S($memcache_key,null);
		}
		return $res;
	}
	public function add_data($data)
	{
		$res=$this->add($data);
		if($res)
		{
			$memcache_api_key='api_gamecategory_all';
			$memcache_admin_key='admin_gamecategory_all';
			S($memcache_admin_key,null);
			S($memcache_api_key,null);
		}
		return $res;
	}
	public function delete_data($id)
	{
		$memcache_key='gamecategory_cat_id'.$id;
		$condition['cat_id']=$id;
		$res=$this->where($condition)->delete();
		if($res)
		{
			$memcache_api_key='api_gamecategory_all';
			$memcache_admin_key='admin_gamecategory_all';
			$memcache_key='gamecategory_cat_id'.$id;
			S($memcache_admin_key,null);
			S($memcache_api_key,null);
			S($memcache_key,null);
		}
		return $res;
	}
	public function get_info_by_id($cat_id)
	{
		if(!$cat_id)
		{
			return false;
		}else
		{
			$memcache_key='gamecategory_cat_id'.$cat_id;
			$res=S($memcache_key);
			if(!$res)
			{
				$condition['cat_id']=$cat_id;
				$res=$this->where($condition)->find();
				if($res)
				{
					S($memcache_key,$res);
					return $res;
				}
				return false;
			}
			return $res;
		}
	}
}