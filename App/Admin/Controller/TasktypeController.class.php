<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class TasktypeController extends BaseController
{
	protected $task_type_model;
	public function _initialize()
	{
		parent::_initialize();
		$this->task_type_model=D("TaskType");
	}

	public function index()
	{
		$res=$this->task_type_model->get_all_data();
		$this->list=$res;
		$this->display();
	}
	public function edit()
	{
		if($_POST['dosubmit'])
		{
			$info=I();
			$id=I("id");
			$res=$this->task_type_model->save_data($id,$info);
			$url=U("index");
			if($res)
			{
				$this->success("修改成功",$url);
			}else
				$this->error("修改失败",$url);

		}else
		{
			$id=I('id');
			$info=$this->task_type_model->get_info_by_id($id);
			$this->info=$info;
			$this->input_arr=array('type_name'=>'类型名称','desc'=>'描述');
			$this->display();
		}
	}
	public function add()
	{
		if($_POST['dosubmit'])
		{
			$info=I();
			$res=$this->task_type_model->add_data($info);
			$url=U("index");
			if($res)
			{
				$this->success("添加成功",$url);
			}else
				$this->error("添加失败",$url);
			
		}else
		{
			$this->input_arr=array('type_name'=>'类型名称','desc'=>'描述');
			$this->display();
		}

	}
	public function delete()
	{
		$id=I("id");
		$res=$this->task_type_model->delete_data($id);
		$url=U("index");
		if($res)
		{
			$this->success("删除成功",$url);
		}else 
			$this->error("删除失败",$url);			
	}
}