<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class GameController extends BaseController
{
	public function index()
	{
		$parameter = array();//分页中追加的参数变量
		$parameter['status']= $this->status = I('status',0);
		$parameter['type']= $this->type = I('type',0);
		$parameter['gamename']= $this->gamename = I('gamename','');
		$parameter['gameid']= $this->gameid = I('gameid',0);
		$parameter['save_enabled']= $this->save_enabled = I('save_enabled',"");
		$parameter['gamelevelmin']= $this->gamelevelmin = I('gamelevelmin',"");
		$parameter['gamelevelmax']= $this->gamelevelmax = I('gamelevelmax',"");
		$parameter['gameplayermin']= $this->gameplayermin = I('gameplayermin',"");
		$parameter['gameplayermax']= $this->gameplayermax = I('gameplayermax',"");
		//查询条件
		$where = '1=1 ';
		if($this->status==1){
			$where .= ' and `status` = 1';
		}elseif($this->status==2){
			$where .= ' and `status` = 0';
		}
		if($this->type){
			$where .= ' and `category` = \''.$this->type.'\'';
		}
		if($this->gameid && $this->gameid!='游戏ID'){
			$where .= ' and `game_id` = \''.$this->gameid.'\'';
		}
		if($this->gamename && $this->gamename!='游戏名称'){
			$where .= ' and `game_name` like \'%'.$this->gamename.'%\'';
		}
		//新追加的组合条件
		if($this->save_enabled==1){
			$where .= ' and `save_enabled` = 0';
		}elseif($this->save_enabled==2){
			$where .= ' and `save_enabled` = 1';
		}
		if($this->gamelevelmin && $this->gamelevelmin !="级别大于"){
			$where .= ' and `level` >= \''.$this->gamelevelmin.'\'';
		}
		if($this->gamelevelmax && $this->gamelevelmax !="级别小于"){
			$where .= ' and `level` <= \''.$this->gamelevelmax.'\'';
		}
		if($this->gameplayermin && $this->gameplayermin !="支持人数大于"){
			$where .= ' and `max_player` >= \''.$this->gameplayermin.'\'';
		}
		if($this->gameplayermax && $this->gameplayermax !="支持人数小于"){
			$where .= ' and `max_player` <= \''.$this->gameplayermax.'\'';
		}
		//新加排序
		$order = null;
		$this->click_num = $parameter['click_num']=(int)I('click_num',0);
		$this->field = $parameter['field']= I('field',"game_id");
		if($this->click_num%2==0){
			$order= "$this->field ASC ";
		}else if($this->click_num%2==1){
			$order = " $this->field DESC ";
		}
		//分页
		$db = M('game');
		$page =new \Think\Page($db->where($where)->count(),15,$parameter);
		$this->pages = $page->show();
		$this->Games = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($order)->select();
		//echo $db->getLastSql();
		//游戏类型
		$this->game_type=game_type();
		$this->display();
	}
	public function add()
	{
		$db = D('Game');
		$game_pic_db = M('game_pic');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$infos['title_pic']="";
			$infos['control_pic']="";
			//I('xx')不支持array方式的值,所以用$_POST
			$game_title_pic = $_POST['title'];//游戏主图
			if(!empty($game_title_pic)){
				$infos['title_pic'] = $game_title_pic['imgurl'][0];
			}
			$game_control = $_POST['control']; //游戏控制图
			if(!empty($game_control)){
				$infos['control_pic'] = $game_control['imgurl'][0];
			}
			$infos['desc'] = trim(stripslashes($_POST['desc']));

			$editdate = I('editdate',0);
			if(!$editdate){
				// 游戏的默认信息，大部分游戏都一样，不需要编辑输入。有必要改的话，可以到数据库里改。
				$infos["def_video_width"] = 1280;
				$infos["def_video_height"] = 720;
				$infos["concurrent"] = 100;
				$infos["max_occupied_gs_num"] = 1000000;
				$infos["expected_joinable_preload_gs_num"] = 0;
				$infos["prelogin_gs_num"] = 0;
				$infos["inside_sandbox"] = 1;

				//新添加的游戏
				$newid = $db->add_data($infos);
				//处理游戏套图信息
				$game_pics = $_POST['pics']; //游戏套图
				$game_pics_data = array();
				$num = 0;
				if(!empty($game_pics)){
					$num = count($game_pics['imgurl']);
					for($i=0;$i<$num;$i++){
						$game_pics_data[$i]['game_id'] = $infos['game_id'];
						$game_pics_data[$i]['pic_type'] = 2; // 大图
						$game_pics_data[$i]['pic_file'] = $game_pics['imgurl'][$i];
						$game_pics_data[$i]['create_time'] = time();
					}
					$game_pic_db->addAll($game_pics_data);
					unset($game_pics_data);
				}
				if($newid){
					systemlog(1,'game',$db->GetLastSql(),'新增游戏，编号：'.$newid);
					$this->success('保存成功！');
				}else{
					$this->error('添加失败！');
				}
			}else{
				//编辑游戏信息
				//$isedit = $db->data($infos)->where(array('game_id'=>$editdate))->save();
				$isedit=$db->save_data($infos,$editdate);
				//清空原来的数据(操作量不是特别大暂定清空再次插入数据)
				$game_pics_data_db = $game_pic_db->where(array('game_id'=>$infos['game_id']))->delete();
				//添加套图信息
				//处理游戏套图信息
				$game_pics = $_POST['pics']; //游戏套图（大图）
				$game_pics_data = array();
				$num = 0;
				if(!empty($game_pics)){
					$num = count($game_pics['imgurl']);
					for($i=0;$i<$num;$i++){
						$game_pics_data[$i]['game_id'] = $infos['game_id'];
						$game_pics_data[$i]['pic_type'] = 2; // 大图
						$game_pics_data[$i]['pic_file'] = $game_pics['imgurl'][$i];
						$game_pics_data[$i]['create_time'] = time();
					}
					$game_pic_db->addAll($game_pics_data);
					unset($game_pics_data);
				}
				$newid = $editdate;
				if($isedit!==false){
					systemlog(2,'game',$db->GetLastSql(),'修改游戏，编号：'.$newid);
					$this->success('保存成功！');
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->get_info_by_id($id);
			$this->pic = $this->row['title_pic'];
			$this->control_pic = $this->row['control_pic'];
			$this->game_type_id = $this->row['category'];
			$this->game_type = game_type();
			$game_pic_infos = $pic_url=array();
			$this->pics_file = "";
			$game_pic_infos = $game_pic_db->where(array('game_id'=>$id))->select();
			if($game_pic_infos){
				foreach ($game_pic_infos as $pic_info){
					if($pic_info['pic_type']==2){
						$pic_url[] = $pic_info['pic_file'];//大图
					}
					continue;
				}
				$this->pics_file =implode(",", $pic_url);
				unset($pic_url);
			}
			$this->upload_title_str = dsy_upload_image("title","title",$id,"$this->pic");
			//var_dump($this->pics_file);
			$this->upload_pics_str = dsy_upload_image("game","pics",$id,"$this->pics_file");
			$this->upload_control_str = dsy_upload_image("control","control",$id,"$this->control_pic");
			$this->display();
		}
	}
	/*
	 *
	*/
	public function gamecategory()
	{
		$game_cat_arr = I('game_cat_id',"");
		$gamecategory_model=D("Gamecategory");
		if(!empty($game_cat_arr))
		{
			//保存列表排序信息
			$gamecategory_model->save_category_sort($game_cat_arr);
		}
		//获取所有的游戏类别
		$category_res = $gamecategory_model->get_all_data();
		$link_gamecategory_game_model=D("LinkGamecategoryGame");
		foreach($category_res as $key=>$v)
		{
			$cat_id=$v['cat_id'];
			$game_data=$link_gamecategory_game_model->get_game_by_catid($cat_id);
			$category_res[$key]['game_count'] = count($game_data);
		}
		//游戏类别赋值末班
		$this->gamecategory=$category_res;
		//默认页面上要展示的游戏类别的id
		$default_cat_id = $category_res['0']['cat_id'];
		//游戏类别游戏
		$this->id = I('id', $default_cat_id);
		$this->games = $gamecategory_model->get_info_by_id($default_cat_id);
		$game_id_data = $update_condtion= $data =array();
		$game_id_data = I('game_id_data',"");
		if(!empty($game_id_data)){
			$link_gamecategory_game_model->sava_game_sort($game_id_data,$this->id);
			$jump_url=U('gamecategory',array('id'=>$this->id));
			header("$jump_url");
		}
		//页面右边游戏处理
		$category_info=$gamecategory_model->get_info_by_id($this->id);
		$this->cat_info=$category_info;
		$game_arr=$link_gamecategory_game_model->get_game_by_catid($this->id);
		if($game_arr)
		{
		 $cnt=count($game_arr);
			$game_model=D("Game");
			for ($i=0;$i<$cnt;$i++)
			{
				$game_id=$game_arr[$i]['game_id'];
				$game_info=$game_model->get_info_by_id($game_id);
				$game_arr[$i]['game_info']=$game_info;
			}
		}
		$this->game_arr=$game_arr;
		$this->display();
	}
	public function gamecategory_category_edit()
	{
		$db=D("Gamecategory");
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = I('editdate',0);
			$infos['create_time']=time();
			if(!$editdate){
				$newid = $db->add_data($infos);
				if($newid){
					systemlog(1,'gamecategory',$db->GetLastSql(),'新增游戏类别，编号：'.$infos['cat_id']);
					$url=U("gamecategory",array('id'=>$infos['cat_id']));
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				$isedit=$db->save_data($editdate,$infos);
				$newid = $editdate;
				if($isedit){
					systemlog(2,'gamecategory',$db->GetLastSql(),'修改游戏类别，编号：'.$newid);
					$url=U("gamecategory",array('id'=>$newid));
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			$this->display('gamecategory_category_edit');
		}
	}
	/*游戏类别增加游戏*/
	public function gamecategory_game_add(){
		$db = M('gamecategory');
		$game_db = M('game');
		$link_gamecategory_game_db = D('LinkGamecategoryGame');
		if(isset($_POST['dosubmit'])){
			$info = I('info','');
			$cat_id = I('cat_id',0);
			$infos['create_time']=time();
			$infos['gamecategory_id']=$cat_id;
			if($info){
				$i=0;
				foreach($info as $k=>$v){
					//判断是否已存在
					$isture = $link_gamecategory_game_db->where(array('gamecategory_id'=>$cat_id,'game_id'=>$v))->select();
					if(!$isture){
						$data=array('gamecategory_id'=>$cat_id,'game_id'=>$v);
						$newid = $link_gamecategory_game_db->add_data($data);
						if($newid) {
							$i++;
							systemlog(1,'link_gamecategory_game',$link_gamecategory_game_db->GetLastSql(),'游戏类别【'.$cat_id.'】增加游戏：'.$v);
						}
					}
				}
			}
			if($i>0){
				$url=U("gamecategory",array('id'=>$cat_id));
				$this->success('保存成功！',$url);
			}else{
				$this->error('保存失败！');
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			$ongame = $link_gamecategory_game_db->field('game_id')->where(array('gamecategory_id'=>$id))->select();
			foreach($ongame as $v){
				$isgame[] = $v['game_id'];
			}
			$this->isgame = $isgame;
			$this->gamelist = $game_db->where()->select();
			$this->display('gamecategory_game_add');
		}
	}
	public function gamepack()
	{
		$pack_id = I('pack_id',"");
		$game_pack_list = I('game_pack_list',"");
		$gamepack_model = D('Gamepack');
		if(!empty($pack_id) ){
			$gamepack_model->update_gamepack_sort($pack_id);
		}
		$res = array();
		$res = $gamepack_model->get_all_data();
		if(empty($res)){
			return false;
		}
		$link_gamepack_game_model=D("LinkGamepackGame");
		$cnt=count($res);
		for($i=0;$i<$cnt;$i++)
		{
			$pack_id=$res[$i]['pack_id'];
			$game_data=$link_gamepack_game_model->get_all_game_by_packid($pack_id);
			$count=count($game_data);
			$res[$i]['game_count']=$count;
		}
		$this->gamepack = $res;

		$default_id = $res['0']['pack_id'];
		//游戏包游戏
		$this->id = I('id', $default_id);
		$this->games = $gamepack_model->get_info_by_packid($this->id);
		//$gm_db = M('link_gamepack_game');
		if(!empty($game_pack_list)){
			$res=$link_gamepack_game_model->update_game_sort($game_pack_list,$this->id);
			$url=U("Game/gamepack",array('id'=>$this->id));
			header("location:$url");
		}
		$this->gamepack_game = $gamepack_model->table('july_link_gamepack_game l,july_game g,july_gamepack p')->field('l.id,l.gamepack_id,l.game_id,g.game_id,g.game_name,g.level ,p.pack_id,p.pack_name')->where('l.gamepack_id=p.pack_id and l.game_id=g.game_id and l.gamepack_id='.$this->id)->order('l.weight desc')->select();
		$this->display('gamepack');
	}
	public function gamepack_edit()
	{
		$db = D('Gamepack');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = I('editdate',0);
			$info['head_pic']="";

			$gamepack_pics = $_POST['gamepack'];
			if(!empty($gamepack_pics)){
				$infos['head_pic']=$gamepack_pics['imgurl'][0];
			}
			$infos['create_time']=time();
			if(!$editdate){
				$newid = $db->add_data($infos);
				if($newid){
					systemlog(1,'gamepack',$db->GetLastSql(),'新增游戏包，编号：'.$infos['pack_id']);
					$url=U('Game/gamepack',array('id'=>$infos['pack_id']));
					$this->success("保存成功",$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				$isedit = $db->save_data($infos,$editdate);
				$newid = $editdate;
				if($newid){
					systemlog(2,'gamepack',$db->GetLastSql(),'修改游戏包，编号：'.$newid);
					$url=U("Game/gamepack",array('id'=>$newid));
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->get_info_by_packid($id);
			$this->pic = $this->row['head_pic'];
			$this->upload_gamepack_str = dsy_upload_image("gamepack","gamepack",$id,"$this->pic");
			$this->display();
		}
	}

	public function gamepack_game_add()
	{
		$db = D('Gamepack');
		$game_db = D('Game');
		//$link_gamepack_game_db = M('link_gamepack_game');
		$link_gamepack_game_db=D("LinkGamepackGame");
		if(isset($_POST['dosubmit'])){
			$info = I('info','');
			$pack_id = I('pack_id',0);
			$infos['create_time']=time();
			$infos['gamepack_id']=$pack_id;
			if($info){
				$i=0;
				foreach($info as $k=>$v){
					//判断是否已存在
					//$isture = $link_gamepack_game_db->where(array('gamepack_id'=>$pack_id,'game_id'=>$v))->select();
					$gameid_arr=$link_gamepack_game_db->get_all_gameid_by_packid($pack_id);
					if(!in_array($v,$gameid_arr)){
						$data['gamepack_id']=$pack_id;
						$data['game_id']=$v;
						$newid = $link_gamepack_game_db->add_data($data);
						if($newid) {
							$i++;
							systemlog(1,'link_gamepack_game',$link_gamepack_game_db->GetLastSql(),'游戏包【'.$pack_id.'】增加游戏：'.$v);
						}
					}
				}
			}
			if($i>0){
				$url=U("Game/gamepack",array("id"=>$pack_id));
				$this->success('保存成功！',$url);
			}else{
				$this->error('保存失败！');
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->get_info_by_packid($id);
			$this->gamelist = $game_db->get_all_data();
			$ongame = $link_gamepack_game_db->get_all_gameid_by_packid($id);

			$this->isgame = $ongame;
			$this->display('gamepack_game_add');
		}
	}

	/*推荐管理列表*/
	public function recommends(){
		//查询条件
		$where = '1=1 ';

		$db = M('recommend');
		$page = new \Think\Page($db->where($where)->count(), 15);

		$this->pages = $page->show();
		$Recommends = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('status desc,weight desc')->select();
		foreach($Recommends as $key=>$value)
		{
			switch($value['type']){
				case 1:
					$Recommends[$key]['type']='推荐游戏';
					break;
				case 2:
					$Recommends[$key]['type']='推荐游戏包';
					break;
				case 3:
					$Recommends[$key]['type']='游戏类别';
					break;
				case 4:
					$Recommends[$key]['type']='内嵌页面';
					break;
				case 5:
					$Recommends[$key]['type']='弹出页面';
					break;
			}
			if($value['flag']!=0)
			{
				$temp=$this->jisuan($value['flag']);
				if(in_array(1,$temp))
				{
					$flag.="新品,";
				}
				if(in_array(2,$temp))
				{
					$flag.="热门,";
				}
				if(in_array(4,$temp))
				{
					$flag.="折扣,";
				}
				if(in_array(8,$temp))
				{
					$flag.="活动,";
				}
				if(in_array(16,$temp))
				{
					$flag.="限免,";
				}
				if(in_array(32,$temp))
				{
					$flag.="首发,";
				}
				$Recommends[$key]['flag']=$flag;
			}
		}
		$this->assign('Recommends',$Recommends);
		$this->display('recommends');

	}

	/*编辑推荐*/
	public function recommends_edit(){
		$db = D('Recommend');
		if(isset($_POST['dosubmit'])){

			$infos = I('info','');
			$infos['pic_file']=" ";
			$reco_pic = $_POST['reco']; //推荐图片
			if(!empty($reco_pic)){
				$infos['pic_file'] = $reco_pic['imgurl'][0];
			}
			$flags = I('flag','');
			$flag=0;
			foreach($flags as $f)
			{
				$flag += $f;
			}
			$infos['flag']=$flag;
			$infos['start_time'] = strtotime($infos['start_time']);
			$infos['end_time'] = strtotime($infos['end_time']);
			$editdate = I('editdate',0);
			if(!$editdate){
				$newid = $db->add_data($infos);

				if($newid){
					systemlog(1,'recommend',$db->GetLastSql(),'新增推荐，编号：'.$newid);
					$url=U("Game/recommends");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				$isedit=$db->save_data_by_id($infos,$editdate);
				$newid = $editdate;
				if($newid){
					systemlog(2,'recommend',$db->GetLastSql(),'修改推荐，编号：'.$newid);
					$url=U("Game/recommends");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$row = $db->get_info_by_id($id);
			$this->pic = $row['pic_file'];
			$this->flag=$this->jisuan($row['flag']);
			//模拟自增长id
			if($id==-1){
				$id = $db->max("id");
				$id++;
			}
			$row['autoid']=$id;
			$this->assign('row',$row);
			$this->upload_reco_str = dsy_upload_image("reco","reco",$id,"$this->pic");
			$this->display('recommends_edit');
		}
	}

	//计算flag选取之前的值
	function jisuan($flag)
	{
		$code = decbin($flag);
		$codearr = str_split($code);
		$codearr = array_reverse($codearr);
		$temp = array();
		foreach($codearr as $k=>$v){
			if($v !=0){
				$temp[] = 1<<$k;
			}
		}
		//print_r($temp);
		return $temp;
	}
	/*计费点列表*/
	public function chargepoint(){
		$this->type=I('type','');
		$this->status=I('status','');
		$this->name=I('name','');
		$this->startdate=I('startdate','');
		$this->enddate=I('enddate','');
		$condition=array();
		if($this->type || $this->type==='0'){
			$condition['type']=$this->type;
		}
		if($this->status=='1' || $this->status=='0' ){
			$condition['status']=$this->status;
		}
		if($this->name && $this->name!='计费点名称'){
			$condition['name']=array('like','%'.$this->name.'%');
		}
		if($this->startdate && $this->startdate!='开启时间'){
			$condition['create_time']=array('egt',strtotime($this->startdate));
		}
		if($this->enddate && $this->enddate!='结束时间'){
			$condition['create_time']=array('elt',strtotime($this->enddate)+86400);
		}
		if(!$condition)
			$condition['_string'] = '1=1';
		$db = M('chargepoint');
		$page = new \Think\Page($db->where($condition)->count(),15);
		$this->pages = $page->show();
		$this->chargepoint = $db->where($condition)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();
	}

	/*编辑计费点*/
	public function chargepoint_edit(){
		/* ini_set("display_errors","off");
		error_reporting(0); */
		//获取游戏列表

		$game_model=D("Game");
		$condition['status'] = 1;
		$this->gamelist=$game_model->get_all_data($condition);
		if(isset($_POST['dosubmit'])){
			// 事务
			$model = new \Think\Model();
			$model->startTrans();

			$info = I('info','');	 //获取主表数据
			$pack = I('pack','');	 //获取游戏包数据
			$save = I('save','');    //获取存档数据
			$coin = I('coin','');    //获取虚拟币数据
			$runonce = I('runonce','');    //获取单次启动数据
			$arcade = I('arcade','');    //获取街机投币数据
			$arena = I('arena','');    //获取擂台赛数据
			$editdate = I('editdate',0);
			$info['update_time'] = time();  //更新时间

			if ($pack['deadline_time'] != '')
				$pack['deadline_time'] = strtotime($pack['deadline_time']) + 86399; // 截止时间为所选日期的23:59:59
			if ($pack['deadline_time_increase'] > 0)
				$pack['deadline_time'] = 0; // 如果设置了延长时间，则绝对截至时间就设置为0
			
			// 如果虚拟币为空，则默认为-1，表示无法购买
			if (trim($info['bean']) == "" || $info['bean'] < -1)
				$info['bean'] = -1;
			if (trim($info['coin']) == "" || $info['coin'] < -1)
				$info['coin'] = -1;
			if (trim($info['gold']) == "" || $info['gold'] < -1)
				$info['gold'] = -1;

			$save_file_path = "";
			if($info['type']==1) {
				$GAME_SAVE_DIR = C("GAME_SAVE_DIR_LINUX");
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					$GAME_SAVE_DIR = C("GAME_SAVE_DIR_WIN");   //本地路径
				// 验证游戏id
				$db_game=$game_model->get_info_by_id($save['game_id']);
				if(!$db_game || $db_game['status']!=1)
					$this->error("游戏".$save['game_id']."id为空或不存在");
				$upload = new \Org\Net\UploadFile();// 实例化上传类
				$upload->maxSize  = 50*1024*1024 ;// 设置附件上传大小
				$upload->allowExts  = array('7z');// 设置附件上传类型
				if ($_FILES['upload']['name']) {
					//保存路径建议与主文件平级目录或者平级目录的子目录来保存
					$upload->savePath = $GAME_SAVE_DIR.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.date("Ymd").DIRECTORY_SEPARATOR;
					if (!file_exists($upload->savePath) && !mkdir($upload->savePath, 0744, true))
						$this->error('无法建立用户上传临时目录');
					if(!$upload->upload()) {// 上传错误提示错误信息
						$this->error($upload->getErrorMsg());
					}else{// 上传成功 获取上传文件信息
						$file_info =  $upload->getUploadFileInfo();
						$save_file_path = $file_info[0]['savepath'].$file_info[0]['savename'];
						$save['compressed_size'] = $file_info[0]['size'];
						$save['compressed_md5'] = strtolower(md5_file($save_file_path));
					}
				}
				else if(!$editdate || empty($save['compressed_md5'])) {
					$this->error('请选择上传文件！'.$save['compressed_md5']);
				}
				$save['desc'] = trim(stripslashes($_POST['desc']));
			}
			if($info['type']==0)
				$info['type_name'] = '游戏时间包';
			if($info['type']==1)
				$info['type_name'] = '游戏存档';
			else if($info['type']==2)
				$info['type_name'] = '购买虚拟币';
			else if($info['type']==3)
				$info['type_name'] = '单次游戏';
			else if($info['type']==4)
				$info['type_name'] = '街机投币';
			else if($info['type']==5)
				$info['type_name'] = '擂台赛';

			if(!$editdate){
				$info['create_time'] = time();   //创建时间
				$cp_id = $model->table(C('DB_PREFIX').'chargepoint')->add($info);
				$sub_cp_id = 0;
				if($info['type']==0){
					$pack['chargepoint_id'] = $cp_id;
					$sub_cp_id = $model->table(C('DB_PREFIX').'chargepoint_gamepack')->add($pack);
				}else if($info['type']==1){
					$save['chargepoint_id'] = $cp_id;
					$sub_cp_id = $model->table(C('DB_PREFIX').'chargepoint_gamesave')->add($save);
				}else if($info['type']==2){
					$coin['chargepoint_id'] = $cp_id;
					$sub_cp_id = $model->table(C('DB_PREFIX').'chargepoint_coin')->add($coin);
				}else if($info['type']==3){
					$runonce['chargepoint_id'] = $cp_id;
					$sub_cp_id = $model->table(C('DB_PREFIX').'chargepoint_runonce')->add($runonce);
				}else if($info['type']==4){
					$arcade['chargepoint_id'] = $cp_id;
					$sub_cp_id = $model->table(C('DB_PREFIX').'chargepoint_arcade')->add($arcade);
				}else if($info['type']==5){
					$arena['chargepoint_id'] = $cp_id;
					$sub_cp_id = $model->table(C('DB_PREFIX').'chargepoint_arena')->add($arena);
				}
				if($cp_id && $sub_cp_id){
					systemlog(1,'chargepoint',$model->table(C('DB_PREFIX').'chargepoint')->GetLastSql(),'新增计费点，编号：'.$cp_id);
				}else{
					$model->rollback();
					$this->error('新建计费点失败！');
				}
			}else{
				$ret_cp_update = $model->table(C('DB_PREFIX').'chargepoint')->data($info)->where(array('id'=>$editdate))->save();
				$cp_id = $editdate;
				if($info['type']==0){
					$ret_sub_cp_update = $model->table(C('DB_PREFIX').'chargepoint_gamepack')->data($pack)->where(array('chargepoint_id'=>$editdate))->save();
				}else if($info['type']==1){
					$ret_sub_cp_update = $model->table(C('DB_PREFIX').'chargepoint_gamesave')->data($save)->where(array('chargepoint_id'=>$editdate))->save();
				}else if($info['type']==2){
					$ret_sub_cp_update = $model->table(C('DB_PREFIX').'chargepoint_coin')->data($coin)->where(array('chargepoint_id'=>$editdate))->save();
				}else if($info['type']==3){
					$ret_sub_cp_update = $model->table(C('DB_PREFIX').'chargepoint_runonce')->data($runonce)->where(array('chargepoint_id'=>$editdate))->save();
				}else if($info['type']==4){
					$ret_sub_cp_update = $model->table(C('DB_PREFIX').'chargepoint_arcade')->data($arcade)->where(array('chargepoint_id'=>$editdate))->save();
				}else if($info['type']==5){
					$ret_sub_cp_update = $model->table(C('DB_PREFIX').'chargepoint_arena')->data($arena)->where(array('chargepoint_id'=>$editdate))->save();

					$db_arena = M("arena");
					$game_id = $db_arena->field("game_id")->where("id=".$arena['arena_id'])->find();
					$key = "arena_list_".$game_id['game_id']; 
					
					S($key,null);
					
					
/* 					$cache = S(array('type'=>'Gloudmemcached'));
					$result = $cache->rm($key);
					if($result == false && $cache->getResultCode() != 16) // log error if failed and error code is not 16 (not found)
						$this->memcache_error_log("Failed to delete the cache,key:".$key.",error_code:".$cache->getResultCode());
					else
						Log::write("delete memcache $key ok",Log::ERR); */
				}
				if($cp_id && $ret_cp_update !== FALSE && $ret_sub_cp_update !== FALSE){
					systemlog(2,'chargepoint',$model->table(C('DB_PREFIX').'chargepoint')->GetLastSql(),'修改计费点，编号：'.$cp_id);
				}else{
					$model->rollback();
					$this->error('修改计费点失败！');
				}
			}
			if($info['type']==1) {
				if ($save_file_path) {
					$final_file_dir = $GAME_SAVE_DIR."sale".DIRECTORY_SEPARATOR.$save['game_id'].DIRECTORY_SEPARATOR;
					$final_file_path = $final_file_dir.$cp_id."_".$save['compressed_md5'].".save";
					if (!file_exists($final_file_dir) && !mkdir($final_file_dir, 0744, true)) {
						$model->rollback();
						$this->error('无法建立售卖存档目录');
					}
					if(copy($save_file_path, $final_file_path) === false) {
						$model->rollback();
						$this->error('复制上传文件失败！'.$save_file_path." to ".$final_file_path);
					}
					if (C("ENABLE_OSS") === true) {
						// 同步上传到阿里云OSS
						$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
						if ($ret['ret'] != 0) {
							$model->rollback();
							$this->error('连接阿里云OSS失败！ ret:'.$ret['ret']." msg:".$ret['msg']);
						}
						$client = $ret['msg'];							
						$key = "g"."/".$save['game_id']."/".$cp_id."_".$save['compressed_md5'].".save";
						$ret = $this->multipartUpload($client, C("OSS_UDS_BUCKET"), $key, $final_file_path);
						if ($ret['ret'] != 0) {
							$model->rollback();
							$this->error('上传文件到阿里云OSS失败！ ret:'.$ret['ret']." msg:".$ret['msg']);
						}
					}
				}
			}
			$model->commit();
			$url=U("chargepoint");
			$this->success('保存成功！',$url);
		}else{
			$this->rowpack = $this->rowsave = $this->rowcoin = $this->rowrunonce = $this->rowarcade = $this->rowarena = array();
			$db = M('chargepoint');
			$pack_db = M('chargepoint_gamepack');
			$save_db = M('chargepoint_gamesave');
			$coin_db = M('chargepoint_coin');
			$runonce_db = M('chargepoint_runonce');
			$arcade_db = M('chargepoint_arcade');
			$arena_db = M('chargepoint_arena');
			$id = I('id', -1);
			if ($id != -1) {
				$this->row = $db->find($id);
				if($this->row['type']==0){
					$packdata = $pack_db->where(array('chargepoint_id'=>$id))->select();
					$this->rowpack=$packdata[0];
				}else if($this->row['type']==1){
					$savedata = $save_db->where(array('chargepoint_id'=>$id))->select();
					$this->rowsave=$savedata[0];
				}else if($this->row['type']==2){
					$coindata = $coin_db->where(array('chargepoint_id'=>$id))->select();
					$this->rowcoin=$coindata[0];
				}else if($this->row['type']==3){
					$runoncedata = $runonce_db->where(array('chargepoint_id'=>$id))->select();
					$this->rowrunonce=$runoncedata[0];
				}else if($this->row['type']==4){
					$arcadedata = $arcade_db->where(array('chargepoint_id'=>$id))->select();
					$this->rowarcade=$arcadedata[0];
				}else if($this->row['type']==5){
					$arenadata = $arena_db->where(array('chargepoint_id'=>$id))->select();
					$this->rowarena=$arenadata[0];
				}
			}
			$gamepack_db = M('gamepack');
			$this->gamepacklist = $gamepack_db->select();

			$this->consolegamelist = $game_model->where('category=1')->select();
			$this->arcadegamelist = $game_model->where('category=2')->select();

			$arena_db = M('arena');
			$this->arenalist = $arena_db->select();

			$this->display();
		}
	}
	/*客户端版本管理列表*/
	public function clientver(){
		$this->pid=I("pid",'');
		$this->ver=I("ver",'');
		$this->product = I("product",'');
		$this->client_type = I("client_type",'');
		$where="1=1";
		if($this->product || $this->product == '0'){
			$where .= ' and product = \''.$this->product.'\'';
		}
		if($this->client_type || $this->client_type == '0'){
			$where .= ' and client_type = \''.$this->client_type.'\'';
		}
		if($this->pid!='' && $this->pid!='PID')
		{
			$where.=' and pid = \''.$this->pid.'\'';
		}
		if($this->ver!='' && $this->ver!='版本号')
		{
			$where.=' and ver = \''.$this->ver.'\'';
		}
		$db = M('client_ver');
		$page = new \Think\Page($db->where($where)->count(), 15);
		$this->pages = $page->show();
		$this->Clientver = $db->where($where)->order('ver desc, pid')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display('clientver');
	}
	/*编辑客户端版本*/
	public function clientver_edit(){
		$db=D("ClientVer");
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = intval(I('editdate',0));
			$infos['desc'] = stripslashes($_POST['desc']);
			if(!$editdate){
				$infos['create_time'] = $infos['update_time'] = time();
				$newid = $db->add_data($infos);
				if($newid){
					systemlog(1,'client_ver',$db->GetLastSql(),'新增客户端版本，编号：'.$newid);
					$url=U("Game/clientver");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				$infos['update_time'] = time();
				$isedit=$db->save_data($infos,$editdate);
				$newid = $editdate;
				if($newid){
					systemlog(2,'client_ver',$db->GetLastSql(),'修改客户端版本，编号：'.$newid);
					$url=U("Game/clientver");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->get_info_by_id($id);
			$this->display();
		}
	}
	/*服务器管理列表*/
	public function server(){
		$db = M('server');
		$page = new \Think\Page($db->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->Server = $db->order('`note` DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();
	}
	/*编辑服务器*/
	public function server_edit(){
		$db = D('Server');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = I('editdate',0);
			if(!$editdate){
				$newid = $db->add_data($infos);
				if($newid){
					systemlog(1,'server',$db->GetLastSql(),'新增服务器，编号：'.$newid);
					$url=U("Game/server");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				//$isedit = $db->data($infos)->where(array('sid'=>$editdate))->save();
				$isedit=$db->save_data($infos,$editdate);
				$newid = $editdate;
				if($newid){
					systemlog(2,'server',$db->GetLastSql(),'修改服务器，编号：'.$newid);
					$url=U("Game/server");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->get_info_by_id($id);
			$this->display();
		}
	}
	/*区域管理列表*/
	public function area(){
		$db = M('region');
		$page = new \Think\Page($db->count(), 15);
		$this->pages = $page->show();
		$this->Area = $db->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();
	}
	/*编辑区域*/
	public function area_edit(){
		$db = D('Region');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = intval(I('editdate',0));
			if(!$editdate){
				$newid = $db->add_data($infos);
				if($newid){
					systemlog(1,'region',$db->GetLastSql(),'新增区域，编号：'.$newid);
					$url=U("Game/area");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				$isedit=$db->save_data($infos,$editdate);
				$newid = $editdate;
				if($isedit){
					systemlog(2,'region',$db->GetLastSql(),'修改区域，编号：'.$newid);
					$url=U("Game/area");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->get_info_by_id($id);
			$this->display();
		}
	}
	//硬件信息记录
	public function hardware()
	{
		$db=M('hardware');
		//获取硬件信息下拉
		$this->hardwareoption=$db->field('hardware')->group('hardware')->select();
		//获取产品信息下拉
		$this->productoption=$db->field('product')->group('product')->select();
		//获取型号信息下拉
		$this->modeloption=$db->field('model')->group('model')->select();
		//获取制造商信息下拉
		$this->manuoption=$db->field('manu')->group('manu')->select();
		//获取类型下拉
		$this->typeoption=$db->field('type')->group('type')->select();
		$this->hardware = I('hardware','');
		$this->product = I('product','');
		$this->model = I('model','');
		$this->manu = I('manu','');
		$this->type = I('type',0);
		$where=" 1=1 ";
		$where.=$this->hardware!=""?" and hardware='{$this->hardware}' ":"";
		$where.=$this->product!=""?" and product='{$this->product}' ":"";
		$where.=$this->model!=""?" and model='{$this->model}' ":"";
		$where.=$this->manu!=""?" and manu='{$this->manu}' ":"";
		$where.=$this->type!=0?" and type='{$this->type}' ":"";

		//获取硬件信息列表
		$page = new \Think\Page($db->where($where)->count(), PAGE_NUM);
		$this->pages = $page->show();
		$this->list=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->display();
	}
	/*编辑硬件信息*/
	public function hardware_edit(){
		$db = M('hardware');
		if(isset($_POST['dosubmit'])){
			$infos = I('info','');
			$editdate = I('editdate',0);
			if(!$editdate){
				$newid = $db->add($infos);
				if($newid){
					systemlog(1,'hardware',$db->GetLastSql(),'新增硬件信息，编号：'.$newid);
					$url=U("hardware");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}else{
				$isedit = $db->data($infos)->where(array('id'=>$editdate))->save();
				$newid = $editdate;
				if($newid){
					systemlog(2,'hardware',$db->GetLastSql(),'修改硬件信息，编号：'.$newid);
					$url=U("hardware");
					$this->success('保存成功！',$url);
				}else{
					$this->error('保存失败！');
				}
			}
		}else{
			$id = I('id', -1);
			$this->row = $db->find($id);
			$this->display('hardware_edit');
		}
	}
	public function gamesave(){
		$this->serialid = I('serialid','');
		$this->saveid = I('saveid',0);
		$this->accountid = I('accountid',0);
		$this->gameid = I('gameid',0);
		$this->deviceid = I('deviceid',0);
		$this->gsid = I('gsid',0);
		$this->gsip = I('gsip','');
		$this->status = I('status',0);
		//查询条件
		$where = 's.game_id != -111'; // 强制使用game_id索引
		if($this->saveid && $this->saveid!='存档ID'){
			$where .= ' and `s`.`id` = \''.$this->saveid.'\'';
		}
		if($this->accountid && $this->accountid!='账户ID'){
			$where .= ' and `s`.`account_id` = \''.$this->accountid.'\'';
		}
		if($this->gameid && $this->gameid!='游戏ID'){
			$where .= ' and `s`.`game_id` = \''.$this->gameid.'\'';
		}
		if($this->deviceid && $this->deviceid!='设备ID'){
			$where .= ' and `s`.`device_uuid` = \''.$this->deviceid.'\'';
		}
		if($this->gsid && $this->gsid!='GS ID'){
			$where .= ' and `s`.`gs_id` = \''.$this->gsid.'\'';
		}
		if($this->gsip && $this->gsip!='GS IP'){
			$where .= ' and `s`.`gs_ip` = \''.$this->gsip.'\'';
		}
		if($this->status==1){
			$where .= ' and `s`.`compressed_md5` != \'\'';
		}elseif($this->status==2){
			$where .= ' and `s`.`compressed_md5` = \'\'';
		}
		$db = M('game_save');
		if($this->serialid && $this->serialid!='序列ID'){
			$where .= ' and `ss`.`id` = \''.$this->serialid.'\'';
			$page = new \Think\Page($db->table('july_game_save_serial ss')->join('LEFT JOIN july_game_save s on ss.id=s.serial_id')->where($where)->count(), PAGE_NUM);
		}
		else {
			// 如果没有指定游戏，则count july_game_save就行
			$page = new \Think\Page($db->table('july_game_save s')->where($where)->count(), PAGE_NUM);
		}

		$this->pages = $page->show();
		$saves = $db->table('july_game_save as s')
		->join('LEFT JOIN july_game_save_serial as ss on s.serial_id =  ss.id')
		->join('LEFT JOIN july_game as g on g.game_id = ss.game_id')
		->field('ss.game_id,ss.account_id,ss.id serial_id,ss.name,ss.create_time,ss.delete_time,s.id save_id,s.gs_id,s.gs_ip,s.upload_token,s.derived_from,s.create_time create_time_s,s.device_uuid,s.upload_time,s.game_mode,s.gs_report_time,s.compressed_size,s.compressed_md5,s.derived_count,s.delete_time delete_time_s,s.total_play_time,g.game_name')
		->where($where)->order('s.id desc')
		->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($saves as &$save) {
			$save['create_time'] = $save['create_time']?date('Y-m-d H:i:s', $save['create_time']):'';
			$save['delete_time'] = $save['delete_time']?date('Y-m-d H:i:s', $save['delete_time']):'';
			$save['gs_report_time'] = $save['gs_report_time']?date('Y-m-d H:i:s', $save['gs_report_time']):'';
			$save['create_time_s'] = $save['create_time_s']?date('Y-m-d H:i:s', $save['create_time_s']):'';
			$save['delete_time_s'] = $save['delete_time_s']?date('Y-m-d H:i:s', $save['delete_time_s']):'';
			$save['upload_time'] = $save['upload_time']?date('Y-m-d H:i:s', $save['upload_time']):'';
		}
		$this->gamesaves = $saves;
		$this->display();

	}
}


