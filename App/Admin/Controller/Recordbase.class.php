<?php  
namespace Admin\Controller;
defined('THINK_PATH') or exit;

abstract class Recordbase extends BaseController {

	public function __construct() {

	}

	protected function Func($func_name) {
		if(method_exists($this,$func_name)) {
			return $this->$func_name();
		}
		exit("This ".$func_name."() method not found;");
	}

	abstract protected function Insert($value);
	abstract protected function Search($value);
	abstract protected function GetList();
}