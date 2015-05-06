<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class AccountController extends BaseController
{
	public function index()
	{
		$this->nickname = I('nickname','');
		$this->id = I('id',0);
		$this->device_uuid=I('device_uuid','');
		$this->bind_phone = I('bind_phone',0);
		$this->bind_email = I('bind_email',0);
		$this->level = I('level',0);
		//查询条件
		$where = '1=1 ';
		if($this->id && $this->id!='ID'){
			$where .= ' and july_account.`id` = \''.$this->id.'\'';
		}
		if($this->device_uuid && $this->device_uuid!='设备UUID'){
			$where .= ' and d.`device_uuid` = \''.$this->device_uuid.'\'';
		}
		if($this->nickname && $this->nickname!='昵称'){
			$where .= ' and july_account.`nickname` like \'%'.$this->nickname.'%\'';
		}
		if($this->bind_phone && $this->bind_phone!='手机'){
			$where .= ' and july_account.`bind_phone` like \'%'.$this->bind_phone.'%\'';
		}
		if($this->bind_email && $this->bind_email!='邮箱'){
			$where .= ' and july_account.`bind_email` like \'%'.$this->bind_email.'%\'';
		}
		if($this->level && $this->level!='级别'){
			$where .= ' and july_account.`level` = \''.$this->level.'\'';
		}
		$account_model = M('account');
		if($this->device_uuid && $this->device_uuid!='设备UUID')
		{
			$count=$account_model->join("LEFT JOIN july_device d on july_account.id=d.bind_account")->where($where)->count();
		}else
			$count=$account_model->where($where)->count();

		$page =  new \Think\Page($count, 15);
		$this->pages = $page->show();
		$this->Account = $account_model->field("july_account.*,d.device_uuid,d.client_type,d.last_login_time")
		->join("LEFT JOIN july_device d on july_account.id=d.bind_account")
		->where($where)->order('july_account.id desc')->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->display();
	}
	public function edit()
	{
		$account_model = D('Account');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$account_id = I('account_id',0);
			if($account_id){
				//$isedit = $db->data($infos)->where(array('id'=>$account_id))->save();				
				$isedit=$account_model->save_data($account_id,$infos);
				$newid = $account_id;
				if($newid){
					systemlog(2,'account',$account_model->GetLastSql(),'修改帐号，编号：'.$newid);
					$url=U("account/index");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id');
			$this->row = $account_model->get_info_by_id($id);
			$this->display();
		}
	}
}
