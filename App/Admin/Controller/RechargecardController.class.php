<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/**
 * @author zhuzhipeng
 *
 */
class RechargecardController extends BaseController
{
	/*
	 * 已售充值卡
	*/
	public function index()
	{
		$this->type = I('type','');
		$this->card_id = I('card_id','');
		$this->valid = I('valid',0);
		$this->startdate = I('startdate','');
		$this->enddate = I('enddate','');
		$this->subpid = I('subpid','');
		$this->dealer = I('dealer','');
		$this->sales = I('sales','');

		//基础联表条件
		$where = 'p.chargepoint_id=c.id and p.chargepoint_id=g.chargepoint_id and p.subpid=a.id and d.id=p.pid';

		//如果为游戏中心用户则看分渠道查看
		if(cookie('dealer_id')==1){
			if($this->dealer){
				$where .= ' and p.pid='.$this->dealer;
			}else{
				$where .= ' and p.pid>=10 and p.pid!=99';
			}
		}else{
			if($this->subpid && $this->subpid!=cookie('userid')){
				$users = Userrelation($this->subpid).$this->subpid;
			}else{
				$users = Userrelation(cookie('userid')).cookie('userid');
			}

			$where .= ' and p.subpid in ('.$users.') and p.pid='.cookie('dealer_id');
		}


		if($this->type==1){
			$where .= ' and p.charge_time > 0';
		}elseif($this->type==2){
			$where .= ' and p.charge_time = 0';
		}
		if($this->card_id && $this->card_id!='卡号'){
			$where .= ' and p.card_id = \''.$this->card_id.'\'';
		}
		if($this->sales && $this->sales!='售卡人'){
			$where .= ' and p.sales like \'%'.$this->sales.'%\'';
		}
		if($this->startdate && $this->startdate!='开始日期'){
			$where .= ' and p.valid_time >= \''.strtotime($this->startdate).'\'';
		}
		if($this->enddate && $this->enddate!='结束日期'){
			$where .= ' and p.valid_time <= \''.(strtotime($this->enddate)+86400).'\'';
		}

		$db = M('payment_card');
		//联表
		$table = array('july_payment_card'=>'p','july_chargepoint'=>'c','july_chargepoint_gamepack'=>'g','july_admins'=>'a','july_dealer'=>'d');
		//字段
		$field = 'p.id,p.batch_id,p.card_id,p.create_time,p.expire_time,p.chargepoint_id,p.charge_to_account_id,p.charge_time,p.pid,p.subpid,p.sales,p.remarks,p.valid,p.valid_time,p.valid_date,c.id,c.name,a.id,a.nickname,g.id,g.chargepoint_id,g.left_seconds_increase,g.deadline_time_increase,d.id,d.dealer_name';
		//排序
		$order = 'p.valid_time desc';
		//分页
		$page = new \Think\Page($db->table($table)->where($where)->count(), 15);
		$this->pages = $page->show();
		//查表
		$this->rechargecardlist = $db->table($table)->field($field)->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();

		//下属用户,游戏中心用户则看渠道
	/* 	if(cookie('dealer_id')==1){
			$this->dealerlist = M('dealer')->field('id,dealer_name')->where('`id`>=10 and `id`!=99')->select();
		}else{
			$this->userlist = M('admins')->field('id,nickname')->where('`id` in ('.rtrim(Userrelation(cookie('userid')),',').')')->select();
			//$this->userlist = M('admin')->field('id,nickname')->where(array('parentid'=>cookie('userid')))->select();
		} */
		//echo $db->getLastSql();
		$this->display();


	}
	/*
	 * 充值卡激活
	*/
	public function activation()
	{
		if(isset($_POST['dosubmit']))
		{
			$card_id = I('card_id','');
			$sales = I('sales','');
			$remarks = I('remarks','');
			$rechargecard_model=D("Common/PaymentCard");
			$where=array('card_id'=>$card_id);
			$isok = $rechargecard_model->get_info_by_where($where);
			if($isok){
				if($isok['subpid']){
					$this->error('该卡已被激活！');
				}else if($isok['charge_time']>0){
					$this->error('该卡已被使用！');
				}else if($isok['expire_time']<time()){
					$this->success('该卡已过期！');
				}else{
					//整理数据
					$info['sales'] = $sales;
					$info['subpid'] = cookie('userid');
					$info['remarks'] = $remarks;
					$info['valid'] = 1;
					$info['valid_time'] = time();
					$info['valid_date'] = date('Ymd');
					$isedit = $rechargecard_model->update_data_by_where($info,$where);
					if($isedit){
						systemlog(2,'payment_card',$rechargecard_model->getLastSql(),'激活充值卡：'.$card_id);
						$url=U('activation');
						$this->success("激活成功！",$url);
					}else{
						$this->error('激活失败！');
					}
				}
			}else{
				$this->error('充值卡不存在！');
			}
		}
		$this->display();
	}
	/*售卡统计*/
	public function countchart(){
		$db = M('payment_card');
		$this->dealerlist = M('dealer')->field('id,dealer_name')->where('`id`>=10 and `id`!=99 ')->select();
		$this->dealer = I('dealer','');

		$users = Userrelation(cookie('userid')).cookie('userid');
		if(cookie('dealer_id')==1){
			if($this->dealer){
				$wheres = ' `pid`='.$this->dealer.' and ';
			}else{
				$wheres = '`pid` >=10  and  `pid`!=99 and ';
			}

		}else{
			$wheres = '`subpid` in ('.$users.')  and  ';
		}

		/********************************近半年销售***********************************/
		//获取近半月的第一天
		$months = months();
		$yuefen = months(1);
		foreach($months as $k=>$v){
			$mon=getthemonth(date($v.'-01'));
			//查询当月数据
			//总销量
			$monthwhere = $wheres.' `valid_date`>='.$mon[0].' and  `valid_date`<='.$mon[1];
			$xiaoshouliang[] = $db->where($monthwhere)->count();

			$xiaoshou_yueka[] = $db->where($monthwhere.' and `chargepoint_id`=8')->count();
			$xiaoshou_bannian[] = $db->where($monthwhere.' and `chargepoint_id`=7')->count();
			$xiaoshou_nianka[] = $db->where($monthwhere.' and `chargepoint_id`=6')->count();
		}
		$this->yuefen = '\''.$yuefen[0].'\',\''.$yuefen[1].'\',\''.$yuefen[2].'\',\''.$yuefen[3].'\',\''.$yuefen[4].'\',\''.$yuefen[5].'\',';
		$this->xiaoshouliang = rtrim(implode(',',$xiaoshouliang), ",");
		$this->xiaoshou_yueka = rtrim(implode(',',$xiaoshou_yueka), ",");
		$this->xiaoshou_bannian = rtrim(implode(',',$xiaoshou_bannian), ",");
		$this->xiaoshou_nianka = rtrim(implode(',',$xiaoshou_nianka), ",");

		/*******************************当月售卡比例**********************************/
		//获取当月起始日期
		$monthday=getthemonth(date("Y-m-d"));
		//查询当月数据
		$monthwhere = $wheres.' `valid_date`>='.$monthday[0].' and  `valid_date`<='.$monthday[1];
		$this->yueka = $db->where($monthwhere.' and `chargepoint_id`=8')->count();
		$this->nianka = $db->where($monthwhere.' and `chargepoint_id`=6')->count();
		$this->bannianka = $db->where($monthwhere.' and `chargepoint_id`=7')->count();


		/**************************近半月销卡与用户开卡比例*****************************/
		//获取最近半月日期
		$days = array();
		$jihuoshu = array();
		$shiyongshu = array();
		for($i=0;$i<=14;$i++){
			//分日统计
			$dayswhere = $wheres.' `valid_date`='.date('Ymd',time()-86400*$i);
			$jihuo = $db->where($dayswhere)->count();
			$shiyong = $db->where($dayswhere.' and `charge_time`>0')->count();
			$jihuoshu[] = $jihuo;
			$shiyongshu[] = $shiyong;
			//每日
			$days[] = date('Ymd',time()-86400*$i);
		}
		$this->days = rtrim(implode(',',array_reverse($days)), ",");
		$this->jihuoshu = rtrim(implode(',',array_reverse($jihuoshu)), ",");
		$this->shiyongshu = rtrim(implode(',',array_reverse($shiyongshu)), ",");


		$this->display('countchart');
	}
}
