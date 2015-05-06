<?php
namespace Common\Model;
use Think\Model;
class ClientVerModel extends Model
{
	public function add_data($data)
	{
		$res=$this->add($data);
		if($res)
		{
			$namespace_ver_key='home_client_ver_namespace';
			$namespace_res=S($namespace_ver_key);
			if($namespace_res)
			{
				S($namespace_ver_key,null);
			}
		}
		return $res;
	}
	public function save_data($data,$id)
	{
		$condition['id']=$id;
		$res=$this->data($data)->where($condition)->save();
		if($res)
		{
			$namespace_ver_key='home_client_ver_namespace';
			$namespace_res=S($namespace_ver_key);
			if($namespace_res)
			{
				S($namespace_ver_key,null);
			}
		}
		return $res;
	}
	public function get_info_by_id($id)
	{
		$condition['id']=$id;
		$info=$this->where($condition)->find();
		return $info;
	}
	/*
	 * 获取应用版本根据条件
	* 设置一个namespace
	*/
	public function api_get_ver($pid,$ver,$product=0,$client_type=0)
	{
		if ($pid == '' || $ver =='')
			return false;
		$namespace_ver_key='home_client_ver_namespace';
		$namespace_res=S($namespace_ver_key);
		if(!$namespace_res)
		{
			$namespace_res=time();
			S($namespace_ver_key,$namespace_res);
		}
		$memcache_key=$namespace_res.'client_ver_pid_ver_product_client_type'.$pid.$ver.$product.$client_type;
		$ver_info=S($memcache_key);
		if(!$ver_info)
		{
			$condition['pid']=$pid;
			$condition['ver']=$ver;
			$condition['product']=$product;
			$condition['client_type']=$client_type;
			$ver_info=$this->field('ver, name, desc, url, force_update, create_time, update_time')->where($condition)->find();
			if($ver_info)
			{
				S($memcache_key,$ver_info,86400);
				return $ver_info;
			}
			return false;
		}
		return $ver_info;
	}
	/*
	 * 根据条件获取最新的应用版本信息
	 */
	public function api_get_new_ver($pid,$product,$client_type)
	{
		$namespace_ver_key='home_client_ver_namespace';
		$namespace_res=S($namespace_ver_key);
		if(!$namespace_res)
		{
			$namespace_res=time();
			S($namespace_ver_key,$namespace_res);
		}
		// TODO: 暂时禁用新版本的信息缓存
		//$memcache_key=$namespace_res.'client_pid_product_client_type'.$pid.$product.$client_type;
		//$ver_info=S($memcache_key);
		//if(!$ver_info)
		{
			$condition['pid']=$pid;
			$condition['product']=$product;
			$condition['client_type']=$client_type;
			// 公司内网的话，可以升级所有版本；公司外部，只有force_update>0的才会提示升级。
			$clientIp = get_client_ip();
			if ($clientIp != '124.207.55.164')
				$condition['_string'] = 'force_update > 0'; // 1,2才提示升级
			else
				$condition['_string'] = 'force_update > -1'; // 0,1,2均提示升级
			$res=$this->field('ver, name, desc, url, force_update, create_time, update_time')->where($condition)->order("ver desc")->limit("1")->select();
			if($res)
			{
				//S($memcache_key,$res[0],86400);
				return $res[0];
			}
			return false;
		}
		//return $ver_info;
	}
}
