<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class AdminController extends BaseController
{
	public function index()
	{
		$admin_model=D("Admins");
		$parameter=I("post.");
		if($parameter)
		{
			if($parameter['username'] && ($parameter['username']!='用户名'))
			{
				$condition['username']=array('like',"%".$parameter['username']."%");
			}
			if($parameter['nickname'] && ($parameter['nickname']!='使用者'))
			{
				$condition['nickname']=array('like',"%".$parameter['nickname']."%");
			}
			if($parameter['roleid'])
			{
				$condition['roleid']=$parameter['roleid'];
			}
	
		}else
			$condition=1;
		$count = $admin_model->where($condition)->count();// 查询满足要求的总记录数
		$Page = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		if($condition!=1)
		{
			foreach($parameter as $key=>$val) {
				$Page->parameter   .=   "$key=".urlencode($val).'&';
			}
		}
		$show = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$list = $admin_model->where($condition)->limit($Page->firstRow.','.$Page->listRows)->select();
		//echo M()->getLastSql();
		//获取所有角色数据
		$role_model=D("Roles");
		$role_data=$role_model->get_all_role_data();
		$this->role_data=$role_data;
		$cnt=count($list);
		for($i=0;$i<$cnt;$i++)
		{
			$role_id=$list[$i]['roleid'];
			$role_info=$role_model->get_info_by_id($role_id);
			$list[$i]['role_remark']=$role_info['remark'];
		}
	
		$this->list=$list;
		$this->page=$show;
		if(!isset($parameter['roleid']))
		{
			$parameter['roleid']='';
		}
		$this->user_wh=$parameter;
		$this->display(); 

	}
	public function password()
	{
		$admin_model=D("Admins");
		if($_POST)
		{
			$passwordinfo = password(I('password'));
			$data['id']=I('id');
			$data['password']=$passwordinfo['password'];
			$data['encrypt']=$passwordinfo['encrypt'];
			$data['update_time']=time();
			$res=$admin_model->update_data($data);
			if($res)
			{
				$this->success("用户密码修改成功");
			}
			else{
				$this->error("用户密码修改失败");
			}
		}else
		{
			$user_id=I('id');
			$user_info=$admin_model->get_userinfo_by_id($user_id);
			if($user_info)
			{
				$this->row=$user_info;
			}
			$this->display();
			
		}
	}
	public function edit()
	{
		$admin_model=D("Admins");
		if(I("dosubmit")==1)
		{
			$info['nickname']=$_POST['nickname'];
			$info['email']=$_POST['email'];
			$info['mobile']=$_POST['mobile'];
			$info['roleid']=$_POST['roleid'];
			$info['id']=$_POST['id'];
			$info['update_time']=time();
			$res=$admin_model->update_data($info);
			if($res)
			{
				$this->success("用户信息更新成功");
			}else
			{
				$this->error("用户信息更新失败");
			}
		}else
		{
			$id=I('id');
			$user_info=$admin_model->get_userinfo_by_id($id);
			$role_model=D("Roles");
			$role_data=$role_model->get_all_role_data();
			
			$this->row=$user_info;
			$this->role_data=$role_data;
			$this->display();
		}
	}
	public function add()
	{
		if(I("dosubmit")==1)
		{
			$admin_model=D("Admins");
			//加入随机字符串重组多重加密密码
			$passwordinfo = password(I('password'));
			$info['password'] = $passwordinfo['password'];
			$info['encrypt'] = $passwordinfo['encrypt'];
			$info['input_time'] = time();
			$info['ip'] = get_client_ip();
			$info['username'] = trim($_POST['username']);
			$info['nickname']=$_POST['nickname'];
			$info['email']=$_POST['email'];
			$info['mobile']=$_POST['mobile'];
			$info['roleid']=$_POST['roleid'];
			$res=$admin_model->add_data($info);
			if($res)
			{
				$this->success("用户添加成功");
			}else
			{
				$this->error("用户添加失败");
			}
		}else 
		{
			$role_model=D("Roles");
			$role_data=$role_model->get_all_role_data();
			$this->role_data=$role_data;
			$this->display();
		}
	}
	public function delete()
	{
		$admin_model=D("Admins");
		$id=I('id');
		$user_info=$admin_model->get_userinfo_by_id($id);
		if($user_info['username']=='admin')
		{
			$this->error("超级管理员不能删除");
		}else
		{
			$where['id']=$id;
			$res=$admin_model->delete_data($where);
			if($res)
			{
				$this->success("删除用户成功");
			}else 
			{
				$this->error("删除用户失败");
			}
		}
	}
}
