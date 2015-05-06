<?php
namespace Admin\Model;
use Think\Model;
class NodeModel extends Model
{
	public function get_all_data()
	{
		return $this->where("status=1")->select();
	}
	public function get_info_by_id($node_id)
	{
		if(!$node_id)
		{
			return false;
		}
		$condition['id']=$node_id;
		$node_info=$this->where($condition)->find();
		if($node_info)
		{
			return $node_info;
		}
		return false;
	}
	public function get_action_by_pid($pid)
	{
		if(!$pid)
		{
			return false;
		}
		$condition['pid']=$pid;
		$result=$this->where($condition)->order("id desc")->select();
		return $result;
	}
	public function add_data($data)
	{
		if($data)
		{
			$res=$this->add($data);
			if($res)
			{
				$public_model=D("Public");
				$public_model->get_system_all_menu_cache(1);
				return $res;
			}
			else
				return false;
		}
		return false;
	}
	/*
	 * 更新node节点信息
	* $data中需要有主键
	*/
	public function update_data($data)
	{
		if($data)
		{

			$res=$this->save($data);
			if($res!==false)
			{
				$public_model=D("Public");
				$public_model->get_system_all_menu_cache(1);
				return $res;
			}else
				return false;
		}
		return false;
	}
	/*
	 * 删除节点信息
	*/
	public function delete_data($node_id)
	{
		$access_model=D("Accesss");
		$node_info=$this->get_info_by_id($node_id);
		if(!$node_info)
		{
			return false;
		}else
		{
			$level=$node_info['level'];
			if($level==3)
			{
				//删除权限表中该节点的数据
				$where_access['node_id']=$node_id;
				$res=$access_model->delete_data($where_access);
				if($res===false)
				{
					return false;
				}
				//删除node表中的数据
				$result=$this->where("id={$node_id}")->delete();
				if($result===false)
				{
				 return false;
				}
				return $result;
			}elseif ($level==2)
			{
				//获取该节点的子节点
				$node_child_arr=$this->get_action_by_pid($node_id);
				$cnt=count($node_child_arr);
				for($i=0;$i<$cnt;$i++)
				{
					$where_access['node_id']=$node_child_arr[$i]['id'];
					$res=$access_model->delete_data($where_access);
					if($res===false)
					{
						return false;
					}
					//删除node表中的数据
					$result=$this->where("id={$node_child_arr[$i]['id']}")->delete();
					if($result===false)
					{
						return false;
					}
				}
				$where_access['node_id']=$node_id;
				$res=$access_model->delete_data($where_access);
				if($res===false)
				{
					return false;
				}
				//删除node表中的数据
				$result=$this->where("id={$node_id}")->delete();
				if($result===false)
				{
					return false;
				}
				return $result;
			}elseif($level==1)
			{
				
				$node_child_arr=$this->get_action_by_pid($node_id);
				$cnt=count($node_child_arr);
				if($cnt>0)
				{
					for($i=0;$i<$cnt;$i++)
					{
						$child_id=$node_child_arr[$i]['id'];
						//获取功能点该菜单下所有的功能点
						$node_func_arr=$this->get_action_by_pid($child_id);
						$num=count($node_func_arr);
						if($num>0)
						{
							for($j=0;$j<$num;$j++)
							{
								$func_id=$node_func_arr[$j]['id'];
								$where_access['node_id']=$func_id;
								$res=$access_model->delete_data($where_access);
								if($res===false)
								{
									return false;
								}
								//删除node表中的数据
								$result=$this->where("id={$func_id}")->delete();
								if($result===false)
								{
									return false;
								}
							}
						}
						$where_access['node_id']=$child_id;
						$res=$access_model->delete_data($where_access);
						if($res===false)
						{
							return false;
						}
						//删除node表中的数据
						$result=$this->where("id={$child_id}")->delete();
						if($result===false)
						{
							return false;
						}
					}
				}
				//删除权限表中该节点的数据
				
				$where_access['node_id']=$node_id;
				$res=$access_model->delete_data($where_access);
				
			
				if($res===false)
				{
					return false;
				}
				//删除node表中的数据
				$result=$this->where("id={$node_id}")->delete();
				if($result===false)
				{
					return false;
				}
				return true;

			}else
				return false;
		}
	}
	/*
	 * 根据控制器和操作名称获取节点信息
	*/
	public function get_node_info_by_url($url)
	{
		if(!$url)
		{
			return false;
		}else
		{
			$condition['url']=$url;
			$node_info=$this->where($condition)->find();
			$node_level=$node_info['level'];
			$pid=$node_info['pid'];
			if($node_level=='2')
			{
				$node_pid_info=$this->get_info_by_id($pid);
				
			}
			if($node_level=='3')
			{
				$node_pid_2_info=$this->get_info_by_id($pid);
				$node_pid_info=$this->get_info_by_id($node_pid_2_info['pid']);
				$node_info['parent_2_title']=$node_pid_2_info['title'];
			}
			if(!isset($node_info['parent_2_title']))
			{
				$node_info['parent_2_title']="";
			}

			$node_info['parent_1_title']=$node_pid_info['title'];
			return $node_info;
		}
	}
}