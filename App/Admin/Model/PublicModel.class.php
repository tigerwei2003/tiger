<?php
namespace Admin\Model;
use Think\Model;
class PublicModel extends Model
{
	protected $tableName = 'nodes';
	/*
	 * 获取组权限
	* $role_id,用户组id，如果$role_id为false，获取所有用户组
	*/
	function get_nodes ($role_id)
	{
		$m = new Model();
		if ($role_id == false) {
			$select = "select july_nodes.* ,july_accesss.role_id as role_id from july_nodes ,july_accesss where july_nodes.id=july_accesss.node_id";
		} else {
			$select = "select july_nodes.*,july_accesss.role_id as role_id from july_nodes ,july_accesss where july_nodes.id=july_accesss.node_id and  july_accesss.role_id='{$role_id}'";
		}
		return $m->query($select);
	}
	/*
	 * 生成用户组菜单缓存文件
	*/
	function makemenucache ($role_id)
	{
		$groupmenus = $this->get_nodes($role_id);
		foreach ($groupmenus as $row) {
			if ($row['level'] == 1) {
				$menus[$row['role_id']]['parent'][$row['id']] = $row;
			} elseif ($row['level'] == 2) {
				
				$menus[$row['role_id']]['child'][$row['pid']][] = $row;
			} elseif ($row['level'] == 3) {
				$menus[$row['role_id']]['func'][$row['pid']][] = $row;
			}
		}
		foreach ($menus as $group => $row) {
			$cache = var_export($row, true);
			$file = MODULE_PATH  . '/filecache/role_' . $role_id. '_node.php';
			$contents = "<?php if (!defined('THINK_PATH')) exit();\r\n return {$cache} ?>";
			write_cache($file, $contents);
		}
	}
	/*
	 * 生成所有菜单的缓存
	*/
	function make_all_menu_cache ()
	{
		$admin_menu_arr = $this->get_all_menu();
		foreach ($admin_menu_arr as $row) {
			if ($row['level'] == 1) {
				$menus['parent'][$row['id']] = $row;
			} elseif ($row['level'] == 2) {
				$menus['child'][$row['pid']][] = $row;
			} else {
				$menus['func'][$row['pid']][] = $row;
			}
		}
		$cache = var_export($menus, true);
		$file = MODULE_PATH  . '/filecache/role_all_node.php';
		$contents = "<?php if (!defined('THINK_PATH')) exit();\r\n return {$cache} ?>";
		write_cache($file, $contents);
	}
	/*
	 * 获取当前系统中拥有的所有菜单
	*/
	function get_all_menu ()
	{
		$node_model = M('Nodes');
		return $node_model->order(
				"`sort` ASC ,`id` ASC,`pid` ASC")->select();
	}
	/*
	 * 判断系统菜单缓存是否存在，并返回
	* $type  1,强制更新不返回,0判断是否存在缓存文件，如果不存在，生成并返回
	*/
	function get_system_all_menu_cache ($type)
	{

		if ($type == 0) {
			$cachefile = MODULE_PATH  . '/filecache/role_all_node.php';
			if (! file_exists($cachefile)) {
				$this->make_all_menu_cache();
			}
			return include ($cachefile);
		}
		if ($type == 1) {
			$this->make_all_menu_cache();
		}
	}
	/*
	 * 判断某个用户组菜单缓存是否存在，并返回
	* $type  1,强制更新不返回,0判断是否存在缓存文件，如果不存在，生成并返回
	* $groupid用户组id
	*/
	function get_system_group_menu_cache ($type, $groupid)
	{
		if ($type == 0) {
			$cachefile = MODULE_PATH  . '/filecache/role_' . $groupid . '_node.php';
			if (! file_exists($cachefile)) {
				$this->makemenucache($groupid);
			}
			return include ($cachefile);
		}
		if ($type == 1) {
			$this->makemenucache($groupid);
		}
	}
	/*
	 * 判断权限缓存是否存在，并返回
	* $type  1,强制更新不返回,0判断是否存在缓存文件，如果不存在，生成并返回
	*/
	function get_acl_cache ($type)
	{
		$base_action = A('Base');
		if ($type == 0) {
			$cachefile = MODULE_PATH  . '/filecache/groupacl.php';
			if (! file_exists($cachefile)) {
				$base_action->make_access_cache();
			}
			return include ($cachefile);
		}
		if ($type == 1) {
			$base_action->make_access_cache();
		}
	}

}