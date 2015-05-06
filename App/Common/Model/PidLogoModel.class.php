<?php
namespace Common\Model;
use Think\Model;

class PidLogoModel extends Model{
	
	/*
		获得pid_logo信息
	*/
	public function get_pid_logo($pid = '', $product = '', $client_type = '', $field = null){
		$memcache_key = 'pid_logo_list';
		$infos = S($memcache_key);
		if($infos === false){
			//  获取全部数据
			$infos = $this->select();
			if($infos){
				S($memcache_key, $infos);
			}
			else
				return $infos;
		}
		if($pid != '' || $product != '' || $client_type != ''){
			//  根据条件筛选数据
			$arr = array();
			foreach($infos as $key=>$info){
				if( $pid != '' && $info['pid'] != $pid ){
					unset($infos[$key]);
					continue;
				}
				if( $product != '' && $info['product'] != $product ){
					unset($infos[$key]);
					continue;
				}
				if( $client_type != '' && $info['client_type'] != $client_type ){
					unset($infos[$key]);
					continue;
				}
				$arr[] = $info;
			}
			$infos = $arr;
		}
		if($field){
			//  按规定的格式 输出数据
			$arr = array();
			if(!is_array($field)){
				$field = explode(',', $field);
			}
			foreach($infos as $info){
				foreach($field as $val){
					$arr[$val] = $info[$val];
				}	
			}
			return $arr;
		}
		return $infos;
	}
	
	public function delete_by_where($where)
	{
		$memcache_key='pid_logo_list';

		$result = S($memcache_key);
		if($result)
		{
			S($memcache_key,null);
		}
		$res = $this->where($where)->delete();
		return $res;
	}
}

?>