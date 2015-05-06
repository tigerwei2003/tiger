<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class RegionController extends BaseController
{

	protected $region_model;
	protected $pid_model;
	protected $pid_region_model;
	public function _initialize()
	{
		parent::_initialize();
		$this->region_model=D("Region");
		$this->pid_model=D("Pid");
		$this->pid_region_model=D("PidRegion");
	}
	/*
	 * 设置可以访问该区域的渠道
	*/
	public function set_pid()
	{
		if(I("dosubmit")==1)
		{
			$region_id=I("region_id");
			$info=I("info");
			if($info)
			{
				$res=$this->pid_region_model->delete_data_by_region_id($region_id);
				if($res!==false)
				{
					foreach ($info as $val)
					{
						$data['pid']=$val;
						$data['region_id']=$region_id;
						$result=$this->pid_region_model->add_data($data);
						if(!$result)
						{
							$this->error("操作错误");
						}
					}
					$arr['pid_limit']=1;
					$res=$this->region_model->save_data($arr,$region_id);
					if($res)
					{
						$this->success("设置成功");
					}else
						$this->error("修改区域是否限制渠道访问失败");
				}
				else
					$this->error("删除数据失败");
			}
			else
			{
				$arr['pid_limit']=0;
				$res=$this->region_model->save_data($arr,$region_id);
				if($res)
				{
					$this->success("操作成功");
				}
				else
					$this->error("操作失败");

			}

		}else
		{
			$this->region_info=$this->region_model->get_info_by_id(I("id"));
			$this->region_id=I("id");
			$this->pid_data=$this->pid_model->get_all_data();
			$limit_pid_arr=$this->pid_region_model->get_data_by_region_id($this->region_id);
			$cnt=count($limit_pid_arr);
			$arr=array();
			for($i=0;$i<$cnt;$i++)
			{
				$arr[]=$limit_pid_arr[$i]['pid'];
			}
			$this->limit_pid_data=$arr;
			$this->display();
		}
	}
}