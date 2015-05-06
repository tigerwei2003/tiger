<?php

defined('THINK_PATH') or exit;

/*
 * 简单工厂，临时使用
 */
class StaFactory {
	static public function Factory($name) {  
		if(!$name) { return false; }
		
		$class_name = "Admin\\Controller\\$name";
		if(import($name,dirname(__FILE__),'.php')) { 
			if(class_exists($class_name,false)) { 
				return new $class_name;
			}
		}
		
		return false;
	}
}

/*
 * 统计部分的抽象父类
 */
abstract class Recordbase extends BaseController {
	
	const GET_LIST = 'GetList';
	const INSERT   = 'Insert';
	const SEARCH   = 'Search';
	
	protected $mPrefix = 'july_';
	protected $mDb;
	
	public function __construct() {
		parent::__construct();
		
		$this->mDb = new \Think\Model();
	}
	
	protected function Func($func_name) {
		if(method_exists($this,$func_name)) {
			return $this->$func_name();
		}
		exit("This ".$func_name."() method not found;");
	}
	
	protected function GetPage($table='',$where='') {
		$db = M();
		$page = new \Think\Page($db->table($table)->where($where)->count(), PAGE_NUM);
		
		return array(
					 'page'=>$page->show(),
					 'first'=>$page->firstRow,
					 'num'=>$page->listRows
					);
	}
	
	abstract protected function Insert($value);
	abstract protected function Search($value);
	abstract protected function GetList();
}