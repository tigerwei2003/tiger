<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;

defined('THINK_PATH') or exit;
/*
 * 多选卡功能基类
 */
abstract class CardbaseController extends BaseController {
	
	const INSERT    = 'Insert';
	const UPDATE    = 'Update';
	const GET_LIST  = 'GetList';
	const GET_EDIT  = 'GetEdit';

	protected function Func($func_name) {
		
		try {
			//
			return $this->$func_name();
		} catch(\Think\Exception $e) { 
			//
			exit("This ".$func_name."() method not found;");
		}
	}
	
	/*
	 * 列表显示页面入口方法
	 */
	final public function Index() {
		$this->Func(self::GET_LIST);
	}
	
	/*
	 * 编辑/添加/显示页面入口方法
	 */
	final public function Edit() {
		$url = U('Index');
		$cid = I('post.card_id',0);
		
		if(IS_POST) { 
			if(!$cid) {
				if(!$this->Func(self::INSERT)) {
					$this->error('添加失败');
				}
			} else {
				if(!$this->Func(self::UPDATE)) {
					$this->error('编辑失败');
				}
			}
		} else {
			$this->Func(self::GET_EDIT);
		}
	}
	
    /*
     * 以id为条件删除记录
     * @param string 表名
     * @param integer or array 
     * @return 执行结果
     */
	protected function DeleteById($table='',$id=0) {
		if(!$table || !$id) {
			return false;
		}

		$db = M();
		$sql = "DELETE FROM $table WHERE ".(is_array($id) ? "id IN(".implode(',',$id).")" : "id='$id'");
		
		return $db->execute($sql);
	}
	
	/*
	 * 抽象方法，数据添加
	 */
	abstract protected function Insert();
	/*
	 * 抽象方法，数据更新
	*/
	abstract protected function Update();
	/*
	 * 抽象方法，展示列表页面
	*/
	abstract protected function GetList();
	/*
	 * 抽象方法，展示编辑/添加页面
	*/
	abstract protected function GetEdit();	
}