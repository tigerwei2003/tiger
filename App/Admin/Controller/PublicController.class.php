<?php
namespace Admin\Controller;
use Think\Controller;
/*
 * 一些公用的方法可以放到这里
*/

class PublicController extends Controller
{

	public function login()
	{
		if (isset($_POST['dosubmit'])) {
			//获取POST值
			$username = trim(I('username',''));
			$password = I('password','');
			//实例化Admin模型类
			$admin_model=D("Admins");
			//获取用户的信息
			$user_info=$admin_model->get_userinfo_by_username($username);
			if($user_info){
				//核对密码
				$realpwd = $user_info['encrypt'] ? password(trim($password),$user_info['encrypt']) : md5($password);
				if($realpwd==$user_info['password']){
					//登陆成功写入session，同时保存到cookie中
					session('username',$username);
					session('nickname',$user_info['nickname']);
					session('userid',$user_info['id']);
					session('roleid',$user_info['roleid']);
					session('dealer_id',$user_info['dealer_id']);
					cookie('userid',$user_info['id'],36000);
					cookie('username',$username,36000);
					cookie('nickname',$user_info['nickname'],36000);
					cookie('roleid',$user_info['roleid'],36000);
					cookie('dealer_id',$user_info['dealer_id'],36000);
					//构造用户更新数据
					$info['update_time'] = time();
					$info['ip'] = get_client_ip();
					//加入随机字符串重组多重加密密码
					$passwordinfo = password($password);
					$info['password'] = $passwordinfo['password'];
					$info['encrypt'] = $passwordinfo['encrypt'];
					$info['id']=$user_info['id'];

					$res=$admin_model->update_data($info);
					if(!$res)
					{
						$this->error("用户信息更新失败！");
					}
					$this->success('您已成功登陆系统！',U('Index/index'));
				}else{
					$this->error('用户名或者密码错误！');
				}

			}else{
				$this->error('没有该用户信息！');
			}
		} else {
			$login_url=U('Public/login');
			$this->login_url=$login_url;
			$this->display('login');
		}
	}
	/*
	 *
	*/
	public function header()
	{
		$role_id = cookie('roleid');
		$not_auth_role_str = C('NOT_AUTH_ROLE'); //不需要验证的用户角色
		$not_auth_role_arr=explode(',', $not_auth_role_str);
		$public_model=D('Public');

		$role_model=D('Roles');
		$role_info=$role_model->get_info_by_id($role_id);

		if (in_array($role_info['name'], $not_auth_role_arr) || (cookie('username')=='admin')) {
			$menu_arr=$public_model->get_system_all_menu_cache(0);
		} else {
			$menu_arr=$public_model->get_system_group_menu_cache(0, $role_id);
		}

		//用户信息编辑url
		$userinfo_edit_url=U('Admin/edit',array('id'=>cookie('userid')));
		//用户推出url
		$logout_url=U('Public/logout');
		$seo_title=C('SEO_TITLE');
		$this->seo_title=$seo_title;
		$this->nickname=cookie('nickname');
		$this->username=isset($username)?cookie('nickname'):cookie('username');
		$this->userinfo_edit_url=$userinfo_edit_url;
		$this->logout_url=$logout_url;
		$this->menu_parent_arr=$menu_arr;
		
	}
	public function footer()
	{

	}
	public function logout()
	{
		if (session() || cookie()) {
			session(null);
			cookie(null);
			$this->jumpUrl=U('login');
			$this->success('退出成功！');
		} else {
			$this->error('已经登出！');
		}
	}
}

