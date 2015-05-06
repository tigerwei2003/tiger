<?php
namespace Admin\Controller;
//use Admin\Controller\BaseController;
use Admin\Controller\StaFactory;

defined('THINK_PATH') or exit;

class RecordController extends BaseController {
	
	public function gametimes() {
		$class_name = ACTION_NAME;
		if(!$obj = StaFactory :: Factory(ucfirst($class_name))) { 
			die('No object');
		}
	
		$obj->GetFunc($obj::GET_LIST);
		
	}
	
	public function gamepack() {
		$class_name = ACTION_NAME;
		if(!$obj = StaFactory :: Factory(ucfirst($class_name))) {
			die('No object');
		}
		
		$obj->GetFunc($obj::GET_LIST);
	}

}