<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;

class SystemController extends BaseController
{
	
	//读取device的pid导入到pid表中
	public function export_pid(){
		$device_model = M('device');
		$pid_list = $device_model->field('pid')->group('pid')->select();

		$pid_model = M('pid');
		foreach($pid_list as $val){
			if($val['pid'] == '')
				continue;
			$pid_model->add($val);
		}
		echo 'suceess';
	}

	//pid_logo 列表
	public function pid_logo(){
		//  pid_list
		$this->pid_list = M('pid')->select();
		
		$this->pid = I('pid','');
		$this->product = I('product','');
		$this->client_type = I('client_type','');

		$where = ' 1=1 ';
		if( $this->pid || $this->pid != '')
			$where .= ' and `pid` = \''.$this->pid.'\'';
		if( $this->product || $this->product != '')
			$where .= ' and `product` = \''.$this->product.'\'';
		if( $this->client_type || $this->client_type != '')
			$where .= ' and `client_type` = \''.$this->client_type.'\'';
			
		$pid_logo_model = M('pid_logo');
		$page = new \Think\Page($pid_logo_model->where($where)->count(),5);
		$this->pages = $page->show();
		$this->list = $pid_logo_model->where($where)->order('id')->limit($page->firstRow . ',' . $page->listRows)->select();;
		$this->display('pid_logo');
	}
	
	public function pid_logo_edit(){
		$info = I('info','');
		
		if($info != ''){
			
			$logo_url = I('editdate','');
			
			if($_FILES['logo_url']['tmp_name'] != ''){  //没有上传图片,不传oss
				if(C("ENABLE_OSS") === true){
					$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
					if ($ret['ret'] != 0) {
						$this->error("连接阿里云OSS失败!");
					}
					$client = $ret['msg'];
		
					if($_FILES['logo_url']['name'] != "")
					{
						$name_suffix = explode(".",$_FILES['logo_url']['name']);
						$logo_url_key = "client/pic/pid/".md5($_FILES['logo_url']['tmp_name']).'.'.$name_suffix[1];
						$ret = $this->multipartUpload($client, C("OSS_PIC_BUCKET"), $logo_url_key, $_FILES['logo_url']['tmp_name']);
						if ($ret['ret'] != 0) {
							$this->error("上传文件到阿里云OSS失败!");
						}
						$info['logo_url'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX").$logo_url_key;
					}
				}	
			}elseif($_FILES['logo_url']['tmp_name'] == '' && $logo_url == ''){  //   既没有上传图片,也不是修改状态.返回错误信息
					$this->error('请上传图片');
			}
			
			$pid_logo_model = D('PidLogo');
			
			if( $logo_url != '' && empty($info['logo_url']) ){
				//  修改时,没有上传新图片,清除以前的图片信息,以便下面重新添入数据库
				$info['logo_url'] = $logo_url;
				$condition = array('logo_url'=>$info['logo_url']);
				$pid_logo_model->delete_by_where($condition);
			}
			$state = 0;
			foreach($info['pid'] as $pid){
				foreach($info['product'] as $product){
					foreach($info['client_type'] as $client_type){
						
						$condition = array('pid'=>$pid,'product'=>$product,'client_type'=>$client_type);
						$pid_logo_model->delete_by_where($condition);
						
						$add = array('pid'=>$pid,'product'=>$product,'client_type'=>$client_type,'logo_url'=>$info['logo_url']);
					 	$ret = M('pid_logo')->add($add);
					 	if($ret === false){
					 		$state = 1;
					 		break;
					 	}
					}
				}
			}
			if($state == 1)
				$this->error('编辑失败');
			else
				$this->success('编辑成功');
		}
		else{
			$this->logo_url = I('logo_url','');
			if($this->logo_url != ''){
				$pid_logo_list = M('pid_logo')->where(array('logo_url'=>$this->logo_url))->select();
				if( count($pid_logo_list) > 0 ){
					$pid = array();
					$product = array();
					$client_type = array();
					foreach($pid_logo_list as $val){
						$pid[$val['pid']] = $val['pid'];
						$product[$val['product']] = $val['product'];
						$client_type[$val['client_type']] = $val['client_type'];
					}
					$this->pid = $pid;
					$this->product = $product;
					$this->client_type = $client_type;
				}
				else{
					$this->error('查询错误');
				}
			}
	  		//pid_list
			$this->pid_list = M('pid')->select();

			$this->display('pid_logo_edit');
		}
	}
	
	//删除  pid_logo  信息
	public function pid_logo_del(){
		$id = I('id','');
		if($id == '')
			$this->error('参数错误');
			
		$pid_logo_model = D('PidLogo');
		$condition = array('id'=>$id);
		$ret = $pid_logo_model->delete_by_where($condition);
		if($ret === false)
			$this->error('删除成功');
		else
			$this->success('删除成功');
	}
	
	//渠道管理
	public function pid(){
		//pid_list
		$this->pid_list = M('pid')->select();
			
		$this->pid = I('pid','');
		
		$where = ' 1=1 ';
		if($this->pid && $this->pid != 'PID')
			$where .= 'and `pid` = \''.$this->pid.'\'';
			
		$pid_model = M('pid');
		$page = new \Think\Page($pid_model->where($where)->count(),15);
		$this->pages = $page->show();
		$this->list = $pid_model->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();;
		$this->display('pid');
	}
	
	//添加渠道
	public function pid_add(){
		$info = I('info','');
		if($info != ''){
			$result = M('pid')->add($info);
			if( $result === false )
				$this->error('添加失败!');
			else
				$this->success('添加成功!');
		}else{
			$this->display('pid_add');
		}
	}
	
	//删除渠道
	public function pid_del(){
		$pid = I('pid','');
		if($pid == '')
			$this->error('参数错误');
		if(M('pid')->where('pid='.$pid)->delete()) {
			if(D('PidLogo')->delete_by_pid($condition = array('pid'=>$pid))){
				$this->success('删除成功');
			}else{
			$this->error('删除失败');	
			}	
		}else{
			$this->error('删除失败');
		}
	}
}
?>