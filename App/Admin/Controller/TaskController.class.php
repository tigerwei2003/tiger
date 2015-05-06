<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class TaskController extends BaseController
{
	protected $task_type_model;
	protected $task_model;
	public function _initialize()
	{
		parent::_initialize();
		$this->task_type_model=D("TaskType");
		$this->task_model=D("Task");
	}
	public function index()
	{
		$count=$this->task_model->count();
		$page =  new \Think\Page($count, 15);
		$this->pages = $page->show();
		$res =$this->task_model->order('id desc')->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->list=$res;
		$this->display();
	}
	public function edit()
	{
		if($_POST['dosubmit'])
		{
			$info=I();
			$id=I("id");
			$res=$this->task_model->save_data($id,$info);
			$url=U("index");
			if($res)
			{
				$this->success("修改成功",$url);
			}else
				$this->error("修改失败",$url);

		}else
		{
			$id=I('id');
			$info=$this->task_model->get_info_by_id($id);
			$this->info=$info;
			$this->input_arr=array('task_name'=>'任务名称','type_id'=>'任务类型','bean'=>'奖励云豆','status'=>'状态','desc'=>'描述');
			$this->status_arr=array('0'=>'禁用','1'=>'公司内部','2'=>'启用');
			$this->task_type_arr=$this->task_type_model->get_all_data();
			$this->display();
		}
	}
	public function add()
	{
		if($_POST['dosubmit'])
		{
			$info=I();
			$res=$this->task_model->add_data($info);
			$url=U("index");
			if($res)
			{
				$this->success("添加成功",$url);
			}else
				$this->error("添加失败",$url);
			
		}else
		{
			$this->input_arr=array('task_name'=>'任务名称','type_id'=>'任务类型','bean'=>'奖励云豆','status'=>'状态','desc'=>'描述');
			$this->status_arr=array('0'=>'禁用','1'=>'公司内部','2'=>'启用');
			$this->task_type_arr=$this->task_type_model->get_all_data();
			$this->display();
		}

	}
	public function delete()
	{
		$id=I("id");
		$res=$this->task_model->delete_data($id);
		$url=U("index");
		if($res)
		{
			$this->success("删除成功",$url);
		}else 
			$this->error("删除失败",$url);			
	}
}