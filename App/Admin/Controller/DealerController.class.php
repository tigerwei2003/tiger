<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class DealerController extends BaseController
{
	public function index()
	{
		$dealer_model = M('dealer');
		$page = new \Think\Page($dealer_model->count(), 15);
		$this->pages = $page->show();
		$this->dealer = $dealer_model->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();
	}
	public function add()
	{
		if($_POST['dosubmit']==1)
		{
			$dealer_model=D("Dealer");
			$res=$dealer_model->add_data($_POST);
			if($res)
			{
				$this->success("渠道添加成功");
			}
			else
			{
				$this->error("渠道添加失败");
			}
		}
		$this->display();
			
	}
	public function edit()
	{
		$dealer_model=D("Dealer");
		if($_POST['dosubmit']==1)
		{
			$res=$dealer_model->save_data($_POST);
			if($res)
			{
				$this->success("渠道信息更新成功");
			}else
			{
				$this->error("渠道信息更新失败");
			}
		}
		$id=I("id");;
		$dealer_info=$dealer_model->get_info_by_id($id);
		$this->dealer_info=$dealer_info;
		$this->display();

	}
	public function delete()
	{
        $id=I('id');
        if($id)
        {   
        	$dealer_model=D("Dealer");
        	$where['id']=$id;
        	$res=$dealer_model->delete_data_by_where($where);
        	if($res)
        	{
        		$this->success("渠道信息删除成功");
        	}else
        	{
        		$this->error("渠道信息删除失败");
        	}
        }
        else
        {
        	$this->error("请确认要删除的数据");
        }
	}
}
