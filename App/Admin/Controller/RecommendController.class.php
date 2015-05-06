<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class RecommendController extends BaseController
{
	protected $recommend_model;
	protected $pid_model;
	protected $pid_recommend_model;
	public function _initialize()
	{
		parent::_initialize();
		$this->recommend_model=D("Recommend");
		$this->pid_model=D("Pid");
		$this->pid_recommend_model=D("PidRecommend");
	}
	public function set_pid()
	{
		if(I("dosubmit")==1)
		{
			$recommend_id=I("id");
			$info=I("info");
			if($info)
			{
				$res=$this->pid_recommend_model->delete_data_by_recommend_id($recommend_id);
				if($res!==false)
				{
					foreach ($info as $val)
					{
						$data['pid']=$val;
						$data['recommend_id']=$recommend_id;
						$result=$this->pid_recommend_model->add_data($data);
						if(!$result)
						{
							$this->error("操作错误");
						}
					}
					$arr['pid_limit']=1;
					$res=$this->recommend_model->save_data_by_id($arr,$recommend_id);
					if($res)
					{
						$this->success("设置成功");
					}else
						$this->error("修改推荐是否限制渠道访问失败");
				}
				else
					$this->error("删除数据失败");
			}
			else
			{
				$arr['pid_limit']=0;
				$res=$this->recommend_model->save_data_by_id($arr,$recommend_id);
				if($res)
				{
					$this->success("操作成功");
				}
				else
					$this->error("操作失败");

			}

		}else
		{
			$this->recommend_info=$this->recommend_model->get_info_by_id(I("id"));
			$this->recommend_id=I("id");
			$this->pid_data=$this->pid_model->get_all_data();
			$limit_pid_arr=$this->pid_recommend_model->get_data_by_recommend_id($this->recommend_id);
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