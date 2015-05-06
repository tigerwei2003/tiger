<?php
namespace Admin\Controller;
use Admin\Controller\CardbaseController;

defined('THINK_PATH') or exit;

class CheckcardController extends CardbaseController {
	
	final protected function GetList() { 
		$where = $this->GetWhereCondition();
	
		$db = M('mulcard_payment_card');
		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$rs  = $db->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
		
		//获得类别
		$types    = $this->GetCardType();
		$channels = $this->GetChannel();
		//
		$get_type = $this->TwoArrayToOne($types);
		//$get_channel = $this->TwoArrayToOne($channels,'dealer_name');
		
		if($rs) {
			foreach($rs as $key=>$row) {
				$rs[$key]['type_name'] = $get_type[$row['type']];
				//$rs[$key]['channel']   = $get_channel[$row['pid']];
				$rs[$key]['source']    = htmlspecialchars($row['source'],ENT_QUOTES);
			}
		}
		
		$this->checktypelist = $types; 
		$this->checkcardlist = $rs;
		unset($types);
		unset($rs);
		
		$this->display('card');
	}
	
	/*
	 * 插入数据库操作
	*/
	final protected function Insert() {
		$data = $this->GetPostData(); 
		@extract($data);
		unset($data);
		
		$sql_suffix = '';
		$source = $info['source'];
		$expire_time = strtotime($info['expire_time']);
		for($i = 0;$i < $codenum;$i ++) { 
			$num_length = 10;      //默认随机数长度
			$num_length -= strlen($channel_id);
			
			$card_id   = $channel_id.$batch.random($num_length);   //充值卡号，长度16位
			$card_pass = random(1,'123456789').random(15);          //充值卡密码，长度16位，有隐患
			
			//批量插入sql语句
			$sql_suffix .= "('$channel_id','$batch','$type_id','$card_id','$card_pass','$source','$expire_time','".time()."'),";
		}
		$sql_suffix = rtrim($sql_suffix,',');
		$db = M();
		
		$sql = "INSERT INTO ".C(DB_PREFIX)."mulcard_payment_card(pid,batch_id,type,card_id,card_pass,source,expire_time,create_time) 
				VALUES ".$sql_suffix;

		return $db->execute($sql);
	}
	
	/*
	 * 更新数据库操作
	*/
	final protected function Update() {}
	
	/*
	 * 显示添加/编辑页面
	*/
	final protected function GetEdit() {
		$this->typelist   = $this->GetCardType(); 
		$this->dealerlist = $this->GetChannel(); 
		
		//如果还没有创建类别，跳转到添加/编辑类别页面
		if(count($this->typelist) == 0) {
			$url = U('Checkcardtype/Edit');
			$this->error('还没有添加类别',$url);
		}
		
		$this->display('card_edit');
	}

	/*
	 * 渠道列表
	 */
	private function GetChannel() {
		return M('dealer')->where('`id`>=10')->order('id DESC')->select();
	}
	
	/*
	 * 类别列表
	 */
	private function GetCardType() {
		return M('mulcard_type')->field("id,type_name")->order('id DESC')->select();
	}
	
	/*
	 * 将特定二维数组转换为一维数组
	 */
	private function TwoArrayToOne($rs=array()) {
		if(!$rs) {
			return false;
		}
		//
		$result = array();
		foreach($rs as $key=>$row) {
			$result[$row['id']] = htmlspecialchars($row['type_name'],ENT_QUOTES);
		}
		return $result;
	}
	
	/*
	 * 验证日期是否合法
	 */
	private function IsDate($date) {
		$dates = explode('-',$date);
		if(!checkdate($dates[1],$dates[2],$dates[0])) {
			return false;
		}
		if(strtotime($date) < time()) {
			return false;
		}
		return ture;
	}
	
	/*
	 * 获得查询条件
	 */
	private function GetWhereCondition() {
		$this->GetSearchPostData();
		
		$where = '1';
		if($this->source && $this->source != '来源') {
			$where .= " AND source='{$this->source}'";
		}
		if($this->pid) {
			$where .= " AND pid='{$this->pid}'";
		}
		if($this->cid) {
			$where .= " AND type='{$this->cid}'";
		}
		if($this->cardid && $this->cardid != '卡号') {
			$where .= " AND card_id='{$this->cardid}'";
		}
		if($this->cardpass && $this->cardpass != '密码') {
			$where .= " AND card_pass='{$this->cardpass}'";
		}
		if($this->batch && $this->batch != '批次') {
			$where .= " AND batch_id='{$this->batch}'";
		}
		if($this->startdate && $this->startdate != '开始日期') {
			$where .= " AND create_time>='{$this->startdate}'";
		}
		if($this->enddate && $this->enddate != '结束日期') {
			$where .= " AND create_time<='{$this->enddate}'";
		}
		return $where;
	}
	
	/*
	 * 获得列表页面搜索POST提交的数据
	 */
	private function GetSearchPostData() {
		$this->source    = I('post.source','');
		$this->pid       = I('post.pid',0);
		$this->cid       = I('post.cid',0);
		$this->batch     = I('post.batch','');
		$this->startdate = I('post.startdate','');
		$this->enddate   = I('post.enddate','');
		$this->cardid    = I('post.cardid','');
		$this->cardpass  = I('post.cardpass','');
		
		if($this->IsDate($this->startdate)) {
			$this->startdate = strtotime($this->startdate);
		}
		if($this->IsDate($this->enddate)) {
			$this->enddate = strtotime($this->enddate) + 86400;
		}
	}
	
	/*
	 * 校验POST提交的数据
	 */
	private function CheckPostData($data) {
		@extract($data);
		if($type_id <= 0) {
			$this->error('必须选择类别');
		}
		if($channel_id <= 0) {
			$this->error('必须选择渠道');
		}
		if($batch <= 0 || strlen($batch) != 6) {
			$this->error('批次必须为六位数整数');
		}
		if($codenum <=0) {
			$this->error('生成数量必须为大于0的整数');
		}
		if(!$info['source']) {
			$this->error('备注不能为空');
		}
		if(!$this->IsDate($info['expire_time'])) {
			$this->error('日期格式非法或所选时间小于当前时间');
		}
	}
	
	/*
	 * 获得表单提交的数据
	 */
	private function GetPostData() { 
		$data = array(
					'type_id'=>I('post.type_id',0),
					'channel_id' =>I('post.pid',0),
					'batch'      =>I('post.card_id_prefix',0),
					'codenum'    =>I('codenum',0),
					'info'       =>I('post.info','')
					
		);
		$this->CheckPostData($data);
		//校验完成，返回数据
		return $data;
	}
}