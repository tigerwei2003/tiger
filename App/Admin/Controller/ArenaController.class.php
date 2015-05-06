<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class ArenaController extends BaseController
{
	public function index()
	{
		//游戏列表
		$this->gamelist = M("arena_game")->table("july_arena_game as ag")->join("july_game as g on g.game_id = ag.game_id")->field("ag.game_id,g.game_name")->select();
		//区域列表
		$this->regionlist = M("region")->select();
			
		$this->status = I('status','');
		$this->game_id = I("game_id","");
		$this->region_id = I('region_id','');
		$this->gs_id = I('gs_id','');
		$this->gs_ip = I('gs_ip','');

		$where = " 1=1 ";

		if($this->status || $this->status == '0'){
			$where .= ' and `a`.`status` = \''.$this->status.'\'';
		}
		if($this->game_id){
			$where .= ' and `a`.`game_id` = \''.$this->game_id.'\'';
		}
		if($this->region_id){
			$where .= ' and `a`.`region_id` = \''.$this->region_id.'\'';
		}
		if($this->gs_id && $this->gs_id != 'GSID'){
			$where .= ' and `a`.`gs_id` = \''.$this->gs_id.'\'';
		}
		if($this->gs_ip && $this->gs_ip != 'GSIP'){
			$where .= ' and `a`.`gs_ip` = \''.$this->gs_ip.'\'';
		}
		$db = M('arena');
		$page = new \Think\Page($db->table("july_arena as a")->join("july_game as g on a.game_id = g.game_id")->join("july_region as r on r.id=a.region_id")->where($where)->count(), 15);

		$this->pages = $page->show();
		$this->arenalist = $db->field("a.id,a.status,a.arena_name,a.arena_pic,a.game_id,g.game_name,a.max_player,a.max_queue_num,a.min_skill_level,a.region_id,r.name as region_name,a.gsd_id,a.gs_id,a.gs_ip,a.gs_port,a.gs_pid,a.gs_last_hb_time,a.open_time,a.close_time,a.live_url")
								->table("july_arena as a")
								->join("july_game as g on a.game_id = g.game_id")
								->join("july_region as r on r.id=a.region_id")
								->where($where)->order('id')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();
	}
	public function arena_edit()
	{
		$db = D('Arena');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$infos['arena_rule'] = trim(stripslashes($_POST['arena_rule']));
	
			$editdate = I('editdate',0);
	
			if(C("ENABLE_OSS") === true){
				$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
				if ($ret['ret'] != 0) {
					$this->error("连接阿里云OSS失败!");
				}
				$client = $ret['msg'];
	
				if($_FILES['arena_pic']['name'] != "")
				{
					if($editdate != 0)
						$filename = "arena_".$editdate;
					else
						$filename = "arena_".(intval($db->max('id')) + 1);
					$name_suffix = explode(".",$_FILES['arena_pic']['name']);
					$arena_pic_key = "a/arena/arena/".$filename.$name_suffix[1];
					$ret = $this->multipartUpload($client, C("OSS_PIC_BUCKET"), $arena_pic_key, $_FILES['arena_pic']['tmp_name']);
					if ($ret['ret'] != 0) {
						$this->error("上传文件到阿里云OSS失败!");
					}
					$infos['arena_pic'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX").$arena_pic_key;
				}
			}
			if(!$editdate){
				$newid = $db->add_data($infos);
	
				if($newid){
					systemlog(1,'arena',$db->GetLastSql(),'新增擂台信息，编号：'.$newid);
					$url=U('index');
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
	
				//$isedit = $db->data($infos)->where(array('id'=>$editdate))->save();
				$isedit=$db->save_data($infos,$editdate);
				$newid = $editdate;
				if($newid){
					systemlog(2,'arena',$db->GetLastSql(),'修改擂台信息，编号：'.$newid);
					$url=U('index');
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			//获取游戏列表
			$this->gamelist = M("arena_game")->table("july_arena_game as ag")->join("july_game as g on g.game_id = ag.game_id")->field("ag.game_id,g.game_name")->select();
			//获取区域信息
			$this->regionlist = M("region")->field("id,name")->select();
			$this->display('arena_edit');
		}
	}
	//擂台管理---battle管理
	public function arena_battle()
	{
		$this->arena_battle_show = 'class="active"';
		//擂台列表
		$this->arenalist = M('arena')->select();
		//游戏列表
		$this->gamelist = M('game')->select();
			
		$this->game_id = I("game_id","");
		$this->arena_id = I('arena_id','');
		$this->player = I('player','');
		$this->id = I("id",'');
	
		$where = " b.player1 != 0 and b.player2 !=0 ";
		if($this->game_id){
			$where .= ' and `b`.`game_id` = \''.$this->game_id.'\'';
		}
		if($this->arena_id){
			$where .= ' and `b`.`arena_id` = \''.$this->arena_id.'\'';
		}
		if($this->player && $this->player != 'PLAYER'){
			$where .= ' and (`b`.`player1` = \''.$this->player.'\' OR `b`.`player2` = \''.$this->player.'\')';
		}
		if($this->id && $this->id != 'BATTLEID'){
			$where .= ' and `b`.`id` = \''.$this->id.'\'';
		}
		$db = M('arena_battle');
		$page = new \Think\Page($db->table("july_arena_battle as b")
				->join("july_arena as a on b.arena_id=a.id")
				->join("july_game as g on b.game_id=g.game_id")->where($where)->count(), 15);
	
	
	
		$this->pages = $page->show();
		$battlelist = $db->field("b.id,b.arena_id,a.arena_name,b.game_id,g.game_name,b.player1,b.player2,b.name1,b.name2,b.score1,b.score2,b.active_time1,b.active_time2,b.phase,b.create_time,b.start_time,b.end_time")
		->table("july_arena_battle as b")
		->join("july_arena as a on b.arena_id=a.id")
		->join("july_game as g on b.game_id=g.game_id")
		->where($where)->order('b.id desc')->limit($page->firstRow.','.$page->listRows)->select();
		/* echo $db->getLastSql();
			exit; */
	
		//获取player_support的count();
		$db_support = M("arena_support");
		foreach($battlelist as $key=>$val)
		{
			$condition = array();
			$condition['battle_id'] = $battleid;
			$condition['player_index'] = 1;
			$p1 = $db_support->where($condition)->count();
			if($p1 === false)
				return false;
			$condition['player_index'] = 2;
			$p2 = $db_support->where($condition)->count();
			if($p1 === false)
				return false;
			$battlelist[$key]['support1'] = $p1;
			$battlelist[$key]['support2'] = $p2;
		}
	
		$this->assign("battlelist",$battlelist);
		$this->display("arena_battle");
	}
	//擂台账号列表
	public function arena_account(){
	
		//游戏列表
		$this->games = M('arena_game')->field('ag.game_id,g.game_name')->table('july_arena_game as ag')->join('july_game as g on g.game_id = ag.game_id')->select();
			
		$this->game_id = I('game_id','');
		$this->account_id = I('account_id','');
		$this->rank = I('rank','');
			
		$where = ' 1=1 ';
		if($this->game_id != ''){
			$where .= ' and `aa`.`game_id` = \''.$this->game_id.'\'';
		}
		if($this->account_id != '' && $this->account_id != 'ACCOUNT_ID'){
			$where .= ' and `aa`.`account_id` = \''.$this->account_id.'\'';
		}
		if($this->rank != '' && $this->rank != 'rank'){
			$where .= ' and `aa`.`rank` = \''.$this->rank.'\'';
		}
			
		$arena_account_model = M('arena_account');
	
		$page = new \Think\Page($arena_account_model->table('july_arena_account as aa')->join('july_account as a on a.id = aa.account_id')->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->account_list = $arena_account_model->field('aa.account_id,a.nickname,g.game_name,aa.skill_level,aa.total_battles,aa.total_wins,aa.max_combo_num,aa.first_battle_time,aa.last_battle_time,aa.rank')
		->table('july_arena_account as aa')
		->join('july_account as a on a.id = aa.account_id')
		->join('july_game as g on aa.game_id = g.game_id')
		->where($where)->order('aa.rank asc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->display();
	}
	//擂台管理---game管理
	public function arena_game(){
		$this->game_status = I('game_status','');
		$this->game_id = I("game_id",'');
		$where = " 1=1 ";
		if($this->game_status || $this->game_status == "0"){
			$where .= ' and `ag`.`game_status` = \''.$this->game_status.'\'';
		}
		if($this->game_id){
			$where .= ' and `ag`.`game_id` = \''.$this->game_id.'\'';
		}
		$db = M("arena_game");
		$page = new \Think\Page($db->table("july_arena_game as ag")->where($where)->count(), 15);
		$this->pages = $page->show();
		$this->gamelist = $db->table("july_arena_game as ag")->join("july_game as g on ag.game_id = g.game_id")->field("ag.game_id,g.game_name,ag.game_status,ag.game_pic,ag.bg_pic")->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		//游戏下拉框
		$this->games = $db->table("july_arena_game as ag")->join("july_game as g on ag.game_id = g.game_id")->field("ag.game_id,g.game_name")->select();
		$this->display("arena_game");
	}
	//擂台统计
	public function chart_arena(){
		//获取擂台信息
		$arena_model = M('arena');
		$this->arenalist=$arena_model->field('id,arena_name')->select();
	
		$this->arena_id=I('arena_id','');
		$this->startdate=I('startdate','');
		$this->enddate=I('enddate','');
	
		$where = '1=1 ';
		if($this->arena_id){
			$where .= ' and sa.`arena_id` = \''.$this->arena_id.'\'';
		}
		if($this->startdate && $this->startdate!="开始时间"){
			$where .= ' and sa.date >= \''.$this->startdate.'\'';
		}
		if($this->enddate && $this->enddate!="结束时间"){
			$where .= ' and sa.date <= \''.$this->enddate.'\'';
		}
	
		$db=M('stat_arena');
		//图标数据
		$data = $db->table('july_stat_arena as sa')->join('july_arena as a on a.id = sa.arena_id')
		->field("sa.date,sum(sa.daily_user_nums) as daily_user_nums,sum(sa.daily_battle_nums) as daily_battle_nums,max(sa.max_concurrent_user) as max_concurrent_user,sum(sa.daily_consume_coin) as daily_consume_coin,sum(sa.daily_join_queue_nums) as daily_join_queue_nums,sum(sa.daily_leave_queue_nums) as daily_leave_queue_nums,sum(sa.daily_join_queue_failed_nums) as daily_join_queue_failed_nums,sum(sa.daily_account_timeout_nums) as daily_account_timeout_nums")
		->where($where)->order('date DESC')->group('date')->limit(0,15)->select();
		$date_7 = '';
		$data = array_reverse($data);
		foreach($data as $v){
			$daily_user_nums[] = $v['daily_user_nums'];
			$daily_battle_nums[] = $v['daily_battle_nums'];
			$max_concurrent_user[] = $v['max_concurrent_user'];
			$daily_consume_coin[] = $v['daily_consume_coin'];
			$daily_join_queue_nums[] = $v['daily_join_queue_nums'];
			$daily_leave_queue_nums[] = $v['daily_leave_queue_nums'];
			$daily_join_queue_failed_nums[] = $v['daily_join_queue_failed_nums'];
			$daily_account_timeout_nums[] = $v['daily_account_timeout_nums'];
			$date_7  .=  '\''.substr($v['date'],4,2).'-'.substr($v['date'],6,2).'\',';
		}
		$this->yy = rtrim($date_7, ",");  //Y坐标
		$this->d1 = rtrim(implode(",",$daily_user_nums), ",");
		$this->d2 = rtrim(implode(",",$daily_battle_nums), ",");
		$this->d3 = rtrim(implode(",",$max_concurrent_user), ",");
		$this->d4 = rtrim(implode(",",$daily_consume_coin), ",");
		$this->d5 = rtrim(implode(",",$daily_join_queue_nums), ",");
		$this->d6 = rtrim(implode(",",$daily_leave_queue_nums), ",");
		$this->d7 = rtrim(implode(",",$daily_join_queue_failed_nums), ",");
		$this->d8 = rtrim(implode(",",$daily_account_timeout_nums), ",");
	
		//每日统计详细
		$page = new \Think\Page($db->table('july_stat_arena as sa')->where($where)->count(), 15);
		$this->pages = $page->show();
		$this->datalist = $db->table('july_stat_arena as sa')->join('july_arena as a on a.id = sa.arena_id')
		->field('sa.date,sa.arena_id,a.arena_name,sa.daily_user_nums,sa.daily_battle_nums,sa.max_concurrent_user,sa.daily_consume_coin,sa.daily_join_queue_nums,sa.daily_leave_queue_nums,sa.daily_join_queue_failed_nums,sa.daily_account_timeout_nums,sa.update_time')
		->where($where)->order('date DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->display('arena_index');
	
	}
	//擂台管理---game修改
	public function arena_game_edit(){
		$db = M('arena_game');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = I('editdate',0);
			if(C("ENABLE_OSS") === true){
				$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
				if ($ret['ret'] != 0) {
					$this->error("连接阿里云OSS失败!");
				}
				$client = $ret['msg'];
	
				if($infos['status'] != '2'){
					if($_FILES['game_pic']['name'] != ""){
						$name_suffix = explode(".",$_FILES['game_pic']['name']);
						$game_pic_key = "a/arena/game/".$infos['game_id']."/small/small_".$infos['game_id'].".".$name_suffix[1];
						$ret = $this->multipartUpload($client, C("OSS_PIC_BUCKET"), $game_pic_key, $_FILES['game_pic']['tmp_name']);
						if ($ret['ret'] != 0) {
							$this->error("上传文件到阿里云OSS失败!");
						}
						$infos['game_pic'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX").$game_pic_key;
					}
				}
				else{
					$infos['game_pic'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX")."a/arena/game/expect.png";
				}
				if($infos['status'] != '2'){
					if($_FILES['bg_pic']['name'] != ""){
						$name_suffix = explode(".",$_FILES['bg_pic']['name']);
						$bg_pic_key = "a/arena/game/".$infos['game_id']."/bg/bg_".$infos['game_id'].".".$name_suffix[1];
						$ret = $this->multipartUpload($client, C("OSS_PIC_BUCKET"), $bg_pic_key, $_FILES['bg_pic']['tmp_name']);
						if ($ret['ret'] != 0) {
							$this->error("上传文件到阿里云OSS失败!");
						}
						$infos['bg_pic'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX").$bg_pic_key;
					}
				}
				else{
					$infos['bg_pic'] = "http://pic2.51ias.com/".C("OSS_KEY_PREFIX")."a/arena/game/expect_bg.png";
				}
			}
			if(!$editdate){
				$newid = $db->add($infos);
				if($newid){
					systemlog(1,'arena_game',$db->GetLastSql(),'新增游戏信息，编号：'.$newid);
					$url=U("Arena/arena_game");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				//修改擂台信息时,清除memcache
				$cache = S(array('type'=>'Gloudmemcached'));
				$key = "arena_info_arena_".$editdate;
				$result = $cache->rm($key);
	
				$isedit = $db->data($infos)->where(array('game_id'=>$editdate))->save();
				$newid = $editdate;
				if($newid){
					systemlog(2,'arena_game',$db->GetLastSql(),'修改游戏信息，编号：'.$newid);
					$url=U("Arena/arena_game");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}
		else
		{
			$game_id = I('game_id', -1);
			$this->row = $db->find($game_id);
			//获取游戏列表
			$this->gamelist = M("game")->field("game_id,game_name")->select();
				
			$this->display('arena_game_edit');
		}
	}
	//擂台赛---支持者列表
	public function arena_support()
	{
		$this->id = I("id","");
		$this->player_index = I("player_index",'');
	
		$where = " a.battle_id = ".$this->id;
		if($this->player_index)
		{
			$where .= ' and a.player_index = \''.$this->player_index.'\'';
		}
	
		$db_support = M("arena_support");
	
		$page = new \Think\Page($db_support->table("july_arena_support as a")->join("july_account as c on c.id=a.account_voters")->where($where)->count(), PAGE_NUM);
		$page->parameter = array_map("id",$this->id);
		$this->pages = $page->show();
		$this->supportlist = $db_support->field("a.id,a.battle_id,a.account_voters,a.player_index,a.create_time,c.nickname")->table("july_arena_support as a")->join("july_account as c on c.id=a.account_voters")->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->display("arena_support");
	}
	//擂台赛---擂主列表
	public function arena_rank()
	{
		$this->arena_battle_show = 'class="active"';
		//擂台列表
		$this->arenalist = M('arena')->field('id,arena_name')->select();
			
		$this->id = I("id","");
		$this->account_id = I("account_id",'');
		$this->date = I("date",'');
		$this->rank = I("rank",'');
	
		$where = " r.arena_id = ".$this->id;
		if($this->account_id && $this->account_id != "ACCOUNT_ID")
		{
			$where .= ' and r.account_id = \''.$this->account_id.'\'';
		}
		if($this->date && $this->date != "DATE")
		{
			$where .= ' and r.date = \''.$this->date.'\'';
		}
		if($this->rank && $this->rank != "RANK")
		{
			$where .= ' and r.rank = \''.$this->rank.'\'';
		}
	
		$db_rank = M("arena_rank");
		$page =new \Think\Page($db_rank->table("july_arena_rank as r")->join("july_account as c on c.id=r.account_id")->where($where)->count(), PAGE_NUM);
		$page->parameter = array_map("id",$this->id);
		$this->pages = $page->show();
		$this->ranklist = $db_rank->field("r.arena_id,r.date,r.rank,r.account_id,r.update_time,c.nickname,r.over_nums,r.nums")
		->table("july_arena_rank as r")->join("july_account as c on c.id=r.account_id")
		->where($where)->limit($page->firstRow.','.$page->listRows)->order("r.date DESC")->select();
		$this->display("arena_rank");
	}
	
	//擂台赛---排队列表
	public function arena_queue()
	{
		//擂台列表
		$this->arenalist = M('arena')->select();
			
		$this->id = I("id","");
		$this->account_id = I("account_id","");
	
		$where = " q.arena_id = ".$this->id;
		if($this->account_id && $this->account_id != "ACCOUNT_ID")
		{
			$where .= ' and q.account_id = \''.$this->account_id.'\'';
		}
		$db_queue = M("arena_queue");
		$page = new \Think\Page($db_queue->table("july_arena_queue as q")->join("july_account as c on c.id=q.account_id")->where($where)->count(), PAGE_NUM);
		$page->parameter = array_map("id",$this->id);
		$this->pages = $page->show();
		$this->queuelist = $db_queue->field("q.id,q.arena_id,q.account_id,c.nickname as account_name,q.enqueue_time,a.arena_name")
									->table("july_arena_queue as q")->join("july_account as c on c.id=q.account_id")->join("july_arena as a on a.id=q.arena_id")
									->where($where)->limit($page->firstRow.','.$page->listRows)->select();

		$this->display("arena_queue");
	}
	
	public function arena_watcher(){
		$this->arena_show = 'class="active"';
  		//擂台列表
  		$this->arenalist=M('arena')->field('id,arena_name')->where('status = 1')->select();
  		$this->id = I('id','');
	  	$this->display('arena_watcher');
	}
	
	//擂台赛实时数据
	public function arena_data(){
		$id = I('id','');
		$battle = M('arena_battle')->table('july_arena_battle as ab')->join('july_arena as a on a.id = ab.arena_id')
					->field('ab.id,a.arena_name,ab.player1,ab.player2,ab.name1,ab.name2,ab.status1,ab.status2')->where('arena_id='.$id)->order('id desc')->limit(1)->select();
			
		//支持人数
		$redis = S(array('type'=>'Gloudredis'));
		$battle[0]['support1'] = (string)$redis->hlen("support_".$battle[0]['id']."_1");
		if($battle[0]['support1'] == '')
			$battle[0]['support1'] = 0;
		$battle[0]['support2'] = (string)$redis->hlen("support_".$battle[0]['id']."_2");
		if($battle[0]['support2'] == '')
			$battle[0]['support2'] = 0;
		//队列人数
		$cache = S(array('type'=>'Gloudmemcached'));
		$key = "arena_info_queue_".$id;
		$queue_list = $cache->get($key);
		if($queue_list === false){
			$arena_queue_model = M('arena_queue');
			$queue_list = $arena_queue_model->where('arena_id='.$id)->select();
		}
		$battle[0]['queue_nums'] = 0;
		if( count($queue_list) > 0 )
			$battle[0]['queue_nums'] = count($queue_list);
		//观众人数
		$hot = $redis->zRange("active_user_arenaid_".$id,'-1','-1');
		$hot = unserialize($hot[0]);
		$battle[0]['hot'] = "".intval($hot[0]);
			
		echo json_encode($battle);
	}
	
	//擂台观众人数
	public function arena_audience_nums(){
		$id = I('id','');
		$type = I('type','');
		
		$redis = S(array('type'=>'Gloudredis'));
        $count = $redis->zcount('arena_hot_nums_'.$id,'-inf','+inf');
        for($i=0;$i < $count;$i++){
                $hot_nums = $redis->zrange('arena_hot_nums_'.$id,$i,$i);
                $hot_nums = unserialize($hot_nums[0]);
                if($type == 'y'){
                	$data[] = $hot_nums[0];	
                }elseif($type == 'x'){
                	$data[] = $hot_nums[1];
                }else{
                	$data[] = $hot_nums;
                }
                
        }
        echo json_encode($data);
	}
	
	//擂台最近50条评论
	public function get_recent_comments(){
		//获取擂台列表
		$this->arenalist=M('arena')->field('id,arena_name')->where('status = 1')->select();
		
		$this->id = I('arena_id','4');
		$json = file_get_contents("http://c4test.51ias.com:8000/web/get_recent_comments?id=$this->id");
		$this->comment_list = json_decode($json, true);
		$this->display('arena_comment');
	}
	
	//发布吐槽
	public function push_comments(){
		//获取擂台列表
		$this->arenalist=M('arena')->field('id,arena_name')->where('status = 1')->select();	
			
		$info = I('info','');
		if($info != ''){
			$info['color'] = $info['color'] == '' ? '123123' : base_convert($info['color'], 16,10);
			file_get_contents("http://c4test.51ias.com:8000/web/publish_comments?bc={$info['bc']}&id={$info['arena_id']}&color={$info['color']}&type={$info['arena_id']}");
		}
		$this->display('arena_push_comments');
	}
}
