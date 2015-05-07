<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;

class codeController extends BaseController
{
	/*邀请码管理列表*/
	public function code(){
		$this->type = I('type',0);
		$this->creator = I('creator','');
		$this->code = I('code','');
		$where = '1=1 ';
		if($this->type==1){
			$where .= ' and `bind_time` > 0';
		}elseif($this->type==2){
			$where .= ' and `bind_time` = \'0\'';
		}
		if($this->creator && $this->creator!='创建人'){
			$where .= ' and `creator` like \'%'.$this->creator.'%\'';
		}
		if($this->code && $this->code!='激活码'){
			$where .= ' and `code` = \''.$this->code.'\'';
		}

		$db = M('invitation_code');
		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->Codes = $db->where($where)->order('create_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		if(cookie('roleid')>1){
			$this->display('code_mini');
		}else{
			$this->display('code');
		}

	}



	/*编辑邀请码*/
	public function code_edit(){
		set_time_limit(180);
		$db = M('invitation_code');
		$this->code_show = 'class="active"';

		if(isset($_POST['dosubmit'])){

			$infos = I('info','');
			$editdate = I('editdate',0);
			$codenum = I('codenum',0);
			//整理数据
			/*
				foreach($info as $k=>$v){
			$infos[$k] = mysql_real_escape_string($v);
			}
			*/
			if(!$editdate){
				$infos['create_time'] = time();
				$infos['user_expire_time'] = $infos['create_time'] + 86400 * 90; // 90天后到期
				if($codenum){
					for($i=1;$i<=$codenum;$i++){
						$infos['code'] = random(1,'123456789').random(15);
						$newid = $db->add($infos);
						systemlog(1,'invitation_code',$db->GetLastSql(),'新增邀请码，编号：'.$infos['code']);
					}
				}
			}else{
				$isedit = $db->data($infos)->where(array('code'=>$editdate))->save();
				$newid = $editdate;
				systemlog(2,'invitation_code',$db->GetLastSql(),'修改邀请码，编号：'.$newid);
			}
			$url=U("code");
			$this->success('保存成功！',$url);

		}else{
			//生成随机码
			$this->random = random(1,'123456789').random(15);

			$id = I('id', -1);
			$this->row = $db->find($id);
			$this->display('code_edit');
		}
	}


	/*充值卡销售记录*/
	public function rechargecard(){
		//渠道
		$this->dealerlist = M('dealer')->field('id,dealer_name')->where('`id`>=10')->select();

		//计费点
		$this->chargepointlist = M('chargepoint')->field('id,name')->select();

		$this->type = I('type','');
		$this->source = I('source','');
		$this->pid = I('pid','');
		$this->cid = I('cid','');
		$this->batch = I('batch','');
		$this->valid = I('valid',0);
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		$this->cardid = I('cardid','');
		$this->cardpass = I('cardpass','');
		$this->charge_to_account_id = I('charge_to_account_id','');
		//查询条件
		$where = 'p.chargepoint_id=c.id ';
		if($this->type==1){
			$where .= ' and p.charge_time > 0';
		}elseif($this->type==2){
			$where .= ' and p.charge_time = 0';
		}
		if($this->source && $this->source!='来源'){
			$where .= ' and p.source like \'%'.$this->source.'%\'';
		}
		if($this->pid){
			$where .= ' and p.pid = \''.$this->pid.'\'';
		}
		if($this->cid){
			$where .= ' and p.chargepoint_id = \''.$this->cid.'\'';
		}
		if($this->cardid && $this->cardid!='卡号'){
			$where .= ' and p.card_id  = \''.$this->cardid.'\'';
		}
		if($this->cardpass && $this->cardpass!='密码'){
			$where .= ' and p.card_pass  = \''.$this->cardpass.'\'';
		}
		if($this->batch && $this->batch!='批次'){
			$where .= ' and p.batch_id like \'%'.$this->batch.'%\'';
		}
		if($this->batch && $this->batch!='批次'){
			$where .= ' and p.batch_id like \'%'.$this->batch.'%\'';
		}
		if($this->charge_to_account_id && $this->charge_to_account_id!='充值账户'){
			$where .= ' and p.charge_to_account_id = \''.$this->charge_to_account_id.'\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and p.create_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and p.create_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}

		$db = M('payment_card');
		$page = new \Think\Page($db->table('july_payment_card p,july_chargepoint c')->where($where)->count(), PAGE_NUM);

		$this->pages = $page->show();

		$this->rechargecardlist = $db->table('july_payment_card p,july_chargepoint c')
		->field('p.id,p.card_id,p.card_pass,p.source,p.pid,p.batch_id,p.create_time,p.expire_time,p.charge_to_account_id,p.charge_to_device_uuid,p.charge_time,p.valid,c.name')
		->where($where)->order('p.id desc')
		->limit($page->firstRow . ',' . $page->listRows)->select();
		if(cookie('roleid')>1){
			$this->display('rechargecard_mini');
		}else{
			$this->display('rechargecard');
		}

	}

	/*生成充值卡*/
	public function rechargecard_edit(){
		$db = M('payment_card');
		$chargepoint_db = M('chargepoint');

		if(isset($_POST['dosubmit'])){

			$infos = I('info','');
			$card_id = I('card_id',0);
			$codenum = I('codenum',0);
			$pid = I('pid',0);
			$card_id_prefix = I('card_id_prefix',0);
			//整理数据
			/*
				foreach($info as $k=>$v){
			$infos[$k] = mysql_real_escape_string($v);
			}
			*/
			$infos['expire_time'] = strtotime($infos['expire_time']);

			if(!$card_id){
				/*
					if($info['expire_time']<time()){
				$this->error('创建失败，过期日期必须大于生成日期！');
				}else{
				*/
				$infos['create_time'] = time();
				$infos['pid'] = $pid;
				$infos['batch_id'] = $pid.$card_id_prefix;
				if($codenum){
					for($i=1;$i<=$codenum;$i++){
						$infos['card_id'] = $pid.$card_id_prefix.random(10);
						$infos['card_pass'] = random(1,'123456789').random(15);
						while (($newid = $db->add($infos)) === false);
						systemlog(1,'payment_card',$db->GetLastSql(),'新增充值卡，编号：'.$infos['card_id'].' 密码：'.$infos['card_pass']);
					}
				}
				//}
			}else{
				$isedit = $db->data($infos)->where(array('card_id'=>$card_id))->save();
				$newid = $card_id;
				systemlog(2,'payment_card',$db->GetLastSql(),'修改充值卡，编号：'.$newid);
			}
			$url=U("rechargecard");
			$this->success('保存成功！',$url);

		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			$this->dealerlist = M('dealer')->where('`id`>=10')->order('id DESC')->select();
			// 只有游戏包、游戏存档、购买虚拟币三种类型的计费点可以创建对应的充值卡
			$this->chargepointlist = $chargepoint_db->field('id,name')->where('type=0 OR type=1 OR type=2')->order('id DESC')->select();
			$this->display('rechargecard_edit');
		}
	}

	/*兑换券记录*/
	public function exchange(){
		//渠道
		$this->dealerlist = M('dealer')->where('`id`>=10')->order('id DESC')->select();
		//计费点
		$this->chargepointlist = M('chargepoint')->table("july_exchange_type et")->join("LEFT JOIN july_chargepoint as c on c.id=et.chargepoint_id")->field('c.id,c.name')->select();
		//类别
		$this->typelist = M('exchange_type')->field('type_id,type_name')->select();
		$this->pid = I('pid','');
		$this->cid = I('cid','');
		$this->tid = I('tid','');
		$this->type_mark = I('type_mark','');
		$this->card_pass = I('card_pass','');
		$this->source = I('source','');
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		//查询条件
		$where = '1=1';
		if($this->pid){
			$where .= ' and ec.pid = \''.$this->pid.'\'';
		}
		if($this->cid){
			$where .= ' and et.chargepoint_id = \''.$this->cid.'\'';
		}
		if($this->tid)
		{
			$where .= ' and ec.type_id = \''.$this->tid.'\'';
		}
		if($this->type_mark)
		{
			$where .= ' and et.type_mark = \''.$this->type_mark.'\'';
		}
		if($this->source && $this->source!='来源'){
			$where .= ' and et.source like \'%'.$this->source.'%\'';
		}
		if($this->card_pass && $this->card_pass!='卡号'){
			$where .= ' and ec.card_pass  = \''.$this->card_pass.'\'';
		}
		if($this->startdate && $this->startdate!='开启时间'){
			$where .= ' and et.valid_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='过期时间'){
			$where .= ' and et.expire_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}
		$db = M('exchange_code');
		$page = new \Think\Page($db->table('july_exchange_code as ec')
				->join('LEFT JOIN july_exchange_type as et on ec.type_id=et.type_id')
				->join('LEFT JOIN july_chargepoint c on et.chargepoint_id=c.id')
				->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();

		$this->exchangelist = $db->table('july_exchange_code as ec')
		->join('LEFT JOIN july_exchange_type as et on ec.type_id=et.type_id')
		->join('LEFT JOIN july_chargepoint c on et.chargepoint_id=c.id')
		->join('LEFT JOIN july_dealer as d on d.id=ec.pid')
		->field('ec.id,ec.card_id,ec.card_pass,d.dealer_name,et.type_name,et.type_mark,et.source,et.valid_time,et.expire_time,ec.num,ec.surplus_num,c.name')
		->where($where)->order('ec.id desc')->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$this->display('exchange');
	}

	/*生成兑换券*/
	public function exchange_edit(){
		$db = M('exchange_code');
		$chargepoint_db = M('chargepoint');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$codenum = I('codenum',0);
			$type_mark = I('type_mark',0);
			$card_id_prefix = I('card_id_prefix','');
			if($codenum){
				if($type_mark=='2')
				{
					$infos['num']=1;
					$infos['surplus_num']=1;
				}
				else if($type_mark=='1')
				{
					$num=M('exchange_type')->field("num")->where($infos)->find();
					$infos['num']=$num['num'];
					$infos['surplus_num']=$num['num'];
				}
				$infos['create_time']=time();
				for($i=1;$i<=$codenum;$i++){
					$infos['card_id'] = $infos['pid'].$card_id_prefix.random(10);
					$infos['card_pass'] = random(1,'123456789').random(15);
					while (($newid = $db->add($infos)) === false);
				}
			}
			$url=U("exchange");
			$this->success('保存成功！',$url);
		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			//渠道
			$this->dealerlist = M('dealer')->field('id,dealer_name')->where('`id`>=10')->select();
			//兑换券类别
			$this->typelist=M("exchange_type")->field("type_id,type_name")->select();
			$this->display('exchange_edit');
		}
	}

	/*获取兑换券的类别标识*/
	public function exchange_type_mark()
	{
		$type_id=I("type_id",-1);
		$where=array();
		$where['type_id']=$type_id;
		$type_mark=M("exchange_type")->field("type_mark")->where($where)->find();
		echo $type_mark['type_mark'];
	}

	/*产看兑换券类别的使用记录*/
	public function show_record()
	{
		//类别
		$this->typelist = M('exchange_type')->field('type_id,type_name')->select();
		//渠道
		$this->dealerlist = M('dealer')->field('id,dealer_name')->where('`id`>=10')->select();

		$this->type_id=I("type_id",'');
		$this->card_pass=I("card_pass",'');
		$this->device_uuid=I('device_uuid','');
		$this->pid=I('pid','');
		$where="1=1";
		if($this->type_id)
		{
			$where.=' and er.type_id=\''.$this->type_id.'\'';
		}
		if($this->card_pass && $this->card_pass!='卡号')
		{
			$where.=' and ec.card_pass=\''.$this->card_pass.'\'';
		}
		if($this->device_uuid && $this->device_uuid!='设备UUID')
		{
			$where.=' and er.device_uuid=\''.$this->device_uuid.'\'';
		}
		if($this->pid){
			$where.=' and ec.pid=\''.$this->pid.'\'';
		}
		$db=M("exchange_record");
		$page = new \Think\Page($db->table("july_exchange_record as er")
				->join("LEFT JOIN july_exchange_code as ec on er.code_id=ec.id")
				->join("LEFT JOIN july_exchange_type as et on et.type_id=er.type_id")
				->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->recordlist = $db->table("july_exchange_record as er")
		->join("LEFT JOIN july_exchange_code as ec on er.code_id=ec.id")
		->join("LEFT JOIN july_exchange_type as et on et.type_id=er.type_id")
		->join("LEFT JOIN july_account as a on a.id=er.account_id")
		->join("LEFT JOIN july_dealer as d on ec.pid = d.id")
		->field("er.id,d.dealer_name,ec.card_pass,et.type_name,et.type_mark,a.nickname as account_name,er.account_id,er.device_uuid,er.charge_time")
		->where($where)->order("er.charge_time DESC,er.account_id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display('exchange_record');
	}

	/*兑换券的类型*/
	public function exchange_type()
	{
		$db=M("exchange_type");
		$where="1=1";

		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->typelist = $db->table("july_exchange_type as et")
		->join("LEFT JOIN july_chargepoint as c on et.chargepoint_id=c.id")
		->field('et.type_id,et.type_name,et.type_mark,et.source,et.valid_time,et.expire_time,et.num,et.account_make_num,et.surplus_num,c.name')
		->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display('exchange_type');
	}

	/*生成兑换券类别*/
	public function exchange_type_edit(){
		$db = M('exchange_type');
		$chargepoint_db = M('chargepoint');
		if(isset($_POST['dosubmit'])){
			$type_id=I("type_id",0);
			$infos = I('info','');
			$infos['valid_time'] = strtotime($infos['valid_time']);
			$infos['expire_time'] = strtotime($infos['expire_time']);
			if($infos['num']!=0)
			{
				$infos['surplus_num']=$infos['num'];
			}
			$infos['create_time'] = time();
			if(!$type_id)
			{
				$result=$db->add($infos);
			}
			else
			{
				$result = $db->where(array('type_id'=>$type_id))->save($infos);
				$newid = $type_id;
				systemlog(2,'exchange_type',$db->GetLastSql(),'修改兑换券类别,编号:'.$newid);
			}
			$url=U("exchange_type");
			if($result===false)
			{
				$this->error('保存失败！',$url);
			}
			else
			{
				$this->success('保存成功！',$url);
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			//print_r($this->row);
			//渠道
			$this->dealerlist = M('dealer')->where('`id`>=10')->order('id DESC')->select();
			// 只有游戏包、游戏存档、购买虚拟币三种类型的计费点可以创建对应的充值卡
			$this->chargepointlist = $chargepoint_db->field('id,name')->where('type=0 OR type=1 OR type=2')->order('id DESC')->select();
			$this->display('exchange_type_edit');
		}
	}
}
