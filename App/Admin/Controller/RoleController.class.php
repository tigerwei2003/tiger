<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class RoleController extends BaseController
{
	public function index()
	{
		$role_model=D("Roles");
		$count = $role_model->count();// 查询满足要求的总记录数
		$Page = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$list = $role_model->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板
	}
	/*
	 * 添加角色信息
	*/
	public function add()
	{
		if($_POST['dosubmit']==1)
		{
			$role_model=D("Roles");
			$data=I();
			$res=$role_model->add_data($data);
			if($res)
			{
				$this->success("添加角色成功");
			}else{
				$this->error("添加角色失败".$role_model->getError());
			}
		}else
		{
			$this->display();
		}
	}
	public function modify()
	{
		$role_model=D("Roles");
		if($_POST['dosubmit']==1)
		{
			$res=$role_model->update_data($_POST);
			if($res)
			{
				$this->success("角色信息修改成功");
			}else
			{
				$this->error("角色信息修改失败".$role_model->getError());
			}

		}else
		{
			$role_id=I('id');
			$role_info=$role_model->get_info_by_id($role_id);
			$this->role_info=$role_info;
			$this->display();
		}

	}
	public function delete()
	{
		$role_id=I('id');
		//删除该用户组的权限菜单缓存文件
		$cache_group_menu = MODULE_PATH  . '/filecache/role_' . $role_id. '_node.php';
		if(file_exists($cache_group_menu))
		{
			unlink($cache_group_menu);
		}
		$where['id']=$role_id;
		//删除数据表access表中的该角色组的信息
		$access_model=D("Accesss");
		$where1['role_id']=$role_id;
		$access_model->delete_data($where1);
		//删除role表中的数据
		$role_model=D("Roles");
		$res=$role_model->delete_data($where);
		if($res)
		{
			//重新生成权限缓存文件
			$acl = $this->make_access_cache();
			$this->success("角色删除成功");
		}else
		{
			$this->error("角色删除失败");
		}
	}
	public function priv()
	{
		if($_POST)
		{
			$access_model=D("Accesss");
			$node_model=D("Nodes");
			$node_id_arr=$_POST['quanxian'];
			$role_id=$_POST['role_id'];
			//删除该角色原来的权限
			$condition['role_id']=$role_id;
			$access_model->where($condition)->delete();
			$num=count($node_id_arr);
			for($i=0;$i<$num;$i++)
			{
				$node_id=$node_id_arr[$i];
				$node_info=$node_model->get_info_by_id($node_id);
				$level=$node_info['level'];
				//构造权限数组
				$data['role_id']=$role_id;
				$data['node_id']=$node_id;
				$data['level']=$node_info['level'];
		 		if($level==1)
				{ 
					$access_model->add($data);
			 	}
				if(($level==2) || ($level==3))
				{
					$condition3['role_id']=$role_id;
					$condition3['node_id']=$node_info['pid'];
					$access_fid_info=$access_model->where($condition3)->find();
					if(!empty($access_fid_info))
					{
						$access_model->add($data);
					}
				} 
			}
			//生成该角色组的缓存菜单
			$public_model = D('Public');
			$public_model->makemenucache($role_id);
			//生成权限缓存
			$this->make_access_cache();
			$this->success("授权成功");
		}else
		{
			$role_id=I("roleid");
			$cache_group_menu = MODULE_PATH  . '/filecache/role_' . $role_id. '_node.php';
			if (! file_exists($cache_group_menu)) {
				// 生成其他组菜单缓存
				$public_model = D('Public');
				$public_model->makemenucache($role_id);
			}
			$menu_group_arr = include ($cache_group_menu);
			$menu_group_id_arr = array();
			foreach ($menu_group_arr['parent'] as $key1 => $value1) {
				$menu_group_id_arr[] = (int) $key1;
				foreach ($menu_group_arr['child'][$key1] as $key2 => $value2) {
					$menu_group_id_arr[] = (int) $value2['id'];
					$key3 = $value2['id'];
					foreach ($menu_group_arr['func'][$key3] as $key4 => $value4) {
						$menu_group_id_arr[] = (int) $value4['id'];
					}
				}
			}
			
			
			$cache_all_menu = MODULE_PATH  . '/filecache/role_all_node.php';
			if (! file_exists($cache_all_menu)) {
				$public_model = D('Public');
				$public_model->make_all_menu_cache();
			}
			$menu_all_arr = include ($cache_all_menu);

			$menu_group_id_str = implode(',', $menu_group_id_arr);
			$this->assign('menu_group_id_str', $menu_group_id_str);
			$this->assign('menu_group_id_arr', $menu_group_id_arr);
			$this->assign('parent_menu_arr', $menu_all_arr);
			$this->assign('role_id', $role_id);
			$this->display();
		}
	}
}
