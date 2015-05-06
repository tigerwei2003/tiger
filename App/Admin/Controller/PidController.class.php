<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class PidController extends BaseController
{
	protected $pid_model;
	protected $game_model;
	protected $pid_set_model;
	public function _initialize()
	{
		parent::_initialize();
		$this->pid_model=D("Pid");
		$this->game_model=D("Game");
		$this->pid_set_model=D("PidSet");
	}
	public function set_deny_game()
	{
		if(I('dosubmit')==1)
		{
			$pid_set_info=$this->pid_set_model->get_info_by_pid($pid);
			$info = I('info','');
			if($info)
			{
				$str='';
				foreach ($info as $val)
				{
					$str.=$val.',';
				}
				$str=rtrim($str,',');
				if(!$pid_set_info)
				{
					$data['pid']=I('pid');
					$data['deny_gid']=$str;
					$res=$this->pid_set_model->add_data($data);
				}else
				{
					$pid_set_info['deny_gid']=$str;
					$res=$this->pid_set_model->save_data($pid_set_info);
				}
				if($res)
				{
					$this->success("操作渠道游戏黑名单成功");
				}else
					$this->error("操作失败！");
			}
				
		}else
		{
			$this->pid=I("pid");
			if(!$this->pid)
			{
				$this->error("请选择要操作的渠道");
			}
			$condition['status']=1;
			$this->gamelist = $this->game_model->get_all_data($condition);
			$field='deny_gid';
			$deny_game=$this->pid_set_model->get_info_by_pid($this->pid,$field);
			$this->deny_game=explode(',',$deny_game['deny_gid']);
			$this->display();
		}
	}
}