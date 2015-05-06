<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/*
 * 系统节点信息管理
*/
class NodeController extends BaseController
{
	public function index()
	{
		$cachefile = MODULE_PATH  . '/filecache/role_all_node.php';
		if (! file_exists($cachefile)) {
			$public_model = D('Public');
			$menu_cache = $publicModel->make_all_menu_cache();
		}
		$menu_arr = include ($cachefile);
		$this->parent_menu_arr=$menu_arr;
		//$index_url后台首页
		$this->index_url=U('Index/index');
		//添加顶级菜单
		$this->add_url=U('add',array('level'=>1));
		//节点管理
		$this->node_index=U('index');

		$this->display();
	}
	public function add()
	{
		if($_POST)
		{
			$node_model=D("Nodes");
			$res=$node_model->add_data($_POST);
			if($res)
			{

				$this->success("菜单添加成功");
			}else
				$this->error("菜单添加失败".$node_model->getError());

		}else
		{
			$level=I("level");
			$pid=I("pid");
			$cachefile = MODULE_PATH  . '/filecache/role_all_node.php';
			$menu_arr=include $cachefile;
			$l_arr=array(1=>'parent',2=>'child',3=>'func');
			$biaoshi=$l_arr[$level];
			if($pid)
			{
				foreach ($menu_arr[$biaoshi][$pid] as $key => $value) {
					$sort_arr[] = $value['sort'];
				}
			}else
			{
				foreach ($menu_arr[$biaoshi] as $key => $value) {
					$sort_arr[] = $value['sort'];
				}
			}
			$sort_max = max($sort_arr);
			if (empty($sort_max)) {
				$sort_max = 1;
			} else {
				if( $level==3)
				{
					$sort_max=$sort_max+1;
				}else
					$sort_max = $sort_max + 10;
			}
			if($level==2 || $level==3)
			{

				$node_model=D("Nodes");
				$node_parent_info=$node_model->get_info_by_id($pid);
				$this->node_parent_info=$node_parent_info;
				$this->level=$level;
				$this->pid=$pid;
			}
			else
				$this->level=1;

			$this->sort=$sort_max;
			$this->display();
		}
	}
	/*
	 * 节点修改
	*/
	public function modify()
	{
		$node_model=D("Nodes");
		if($_POST['dosubmit']==1)
		{
			$res=$node_model->update_data($_POST);
			if($res!==false)
			{
				$this->success("节点信息更新成功");
			}else
				$this->error("节点信息更新失败".$node_model->getError());
		}else{
			$node_id=I('id');
			$node_info=$node_model->get_info_by_id($node_id);
			$node_pid=$node_info['pid'];
			if($node_pid>0)
			{
				$parent_node_info=$node_model->get_info_by_id($node_pid);
				$this->parent_node_info=$parent_node_info;
			}
			$this->node_info=$node_info;
			$this->display();

		}
	}
	public function delete($id)
	{
		$node_model=D("Nodes");
		$node_id=I("id");
		$level=I("level");
		$res=$node_model->delete_data($node_id);


		if($res)
		{
			//重新生成全部的缓存菜单
			$public_model = D('Public');
			$menu_cache = $public_model->make_all_menu_cache();
			//生成个角色组的缓存菜单
			$role_model=D("Roles");
			$role_data=$role_model->get_all_role_data();
			$cnt=count($role_data);
			for($i=0;$i<$cnt;$i++)
			{
				$role_id=$role_data[$i]['id'];
				$public_model->get_system_group_menu_cache (1, $role_id);
			}
			//生成权限缓存文件
			$base_controller=A("Base");
			$base_controller->make_access_cache();
			$this->success("节点删除成功");
		}else
			$this->error("节点删除失败");
	}

}
