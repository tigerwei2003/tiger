<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class LogController extends BaseController
{
	public function index(){


		$db = M('system_log');
		$this->m_show = ' btn-info';
		$this->w_show = '';
		$page = new \Think\Page($db->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->loglist = $db->where($wheres)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();

	}


	public function showmore(){
		$id = I('id', -1);
		$db = M('system_log');
		$row = $db->find($id);
		die($row['log_sql']);
	}
}
