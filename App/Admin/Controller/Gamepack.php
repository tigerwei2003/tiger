<?php
namespace Admin\Controller;
defined('THINK_PATH') or exit;

class Gamepack extends Recordbase {
	
	private $mAccount;
	private $mPackId;
	private $mStartDate;
	private $mndData;
	
	public function __construct() {
		parent::__construct();
		
		if(IS_POST) {
			$this->mAccount   = I('post.account',0);
			$this->mPackId    = I('post.packid',0);
			$this->mStartDate = I('post.startdate','');
			$this->mEnddate   = I('post.enddate','');
		}
	}
	
	public function GetFunc($func_name) {
		parent :: Func($func_name);
	}
	
	final protected function Insert($value) {}
	final protected function Search($value) {}
	
	final protected function GetList() {
		$table = "{$this->mPrefix}link_account_gamepack";
		$table_join = $table." agp LEFT JOIN {$this->mPrefix}gamepack gp ON agp.gamepack_id=gp.pack_id";
		$where = $this->GetWhereCondition();
		
		$pages = parent :: GetPage($table_join,$where);
		$this->pages = $pages['page'];
		
		$this->display();
	}
	
	private function GetWhereCondition() {
		$where = "1";
		
		if($this->mAccount && $this->mAccount != '账户'){
			$where .= " AND agp.account_id='{$this->mAccount}'";
		}
		if($this->mPackid && $this->mPackid != '游戏包ID'){
			$where .= " AND agp.gamepack_id='{$this->mPackid}'";
		}
		if($this->mStartDate && $this->mStartDate != '开始日期'){
			$where .= " AND agp.create_time>='".strtotime($this->mStartDate)."'";
		}
		if($this->mndData && $this->mndData != '结束日期'){
			$where .= " AND agp.create_time<='".(strtotime($this->mndData)+86400)."'";
		}
		
		return $where;
	}
}