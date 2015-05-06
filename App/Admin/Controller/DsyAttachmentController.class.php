<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/**
 * @desc 修改系统中原有的上传适应需求
 * @author chunyingXu
 *
 */
class DsyAttachmentController extends BaseController {
	
    public function index() {
		//import ('ORG.Util.Page');
        $db = M(attachment);
        $this->model = I('model');//复制给js中
		$this->param_val = I('param_field',0);//
		$condition = array(
				'model'=>$this->model,
				'param'=>$this->param_val
		);
		$page = new \Think\Page($db->where($condition)->count(), 20);
		$this->pages = $page->show();
		$images_info= array();
		$images_info = $db->where($condition)->order('uploadtime desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->images = $images_info;
		$this->display('index');
    }
	
	public function not_use() {
		//import ('ORG.Util.Page');
        $db = M('attachment');
		
		$wheres = "`isimage` = 1 and `status` = 0 and `module`=0";
		$page = new \Think\Page($db->where($wheres)->count(), 20);
		$this->pages = $page->show();
		$this->images = $db->where($wheres)->order('uploadtime desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display('index');
		
	}
	
	public function delete() {
	    $id = I('aid', 0);
		if ($id == 0) 
		    $this->error('参数错误，非法操作！');
		$db = M('attachment');
		$rs = $db->find($id);
		if ($rs) {
		    $spath = $rs['filepath'];
			$fname = basename($spath);
			$dir = dirname($spath);
			$files = glob($dir . "/*" . $fname);
			foreach ($files as $f)
			    @unlink($f);
		    $db->delete($id);	
		}
		
		
	}
	
	public function setuse() {
	    $id = I('aid', 0);
		if ($id == 0) 
		    $this->error('参数错误，非法操作！');
			
		$db = M('attachment');
		$rs = $db->find($id);
		if ($rs) {
		    $rs['status'] = 1;
			$db->save($rs);	
		}
	}
	
	
	public function network() {
		$keywords = I('keywords','');	
		$pages = I('pages',0);
		
		if(!empty($keywords)){
			echo $this->get_yahoo_img($keywords,$pages);
		}else{
			$this->display('network');
		}
	}
	
	
	public function img_upload() {
		$this->model = I('model');//复制给js中
		$this->param_val = I('param_field',0);//
		//$model_arr = array('game','gamepack','reco');
		if (isset($_POST['dosubmit'])) {
			//import('ORG.Net.DsyUploadFile');                           //将上传类UploadFile.class.php拷到Lib/Org文件夹下
			$upload = new \Org\Net\DsyUploadFile();
			$game_img_setting =$game_config = array();
			//todo:检查上传的model操作参数是否合法
			$game_thum_setting = C('IMG_UPLOAD_CONFIG');
			$model = strtoupper($this->model);
			$game_config =$game_thum_setting[$model];//传递的模型
			$upload->uploadReplace  = true;                            //如果存在同名文件是否进行覆盖
			$upload->allowExts      = array('jpg','jpeg','png','gif'); //准许上传的文件类型
			$this->data_save_path = C('DSY_IMG_UPLOAD_DIR').$game_config['ORIGIN_PATH']."$this->param_val".'/';
			//保存路径建议与主文件平级目录或者平级目录的子目录来保存 
			$upload->savePath = WEB_ROOT.$this->data_save_path; //文件存放路径
			if (!file_exists($upload->savePath)) {
				@mkdir($upload->savePath, 0777, true);	
			}
			$data = array();
			$data['userid'] = session('userid');
			if (empty($data['userid'])) $data['userid'] = 0;
			$data['module'] = '';
			$data['model']  = $this->model;
			$data['param']  = $this->param_val;
			$data['catid']  = 0;
			$data['downloads'] = 0;
			$data['uploadtime'] = time();
			$data['isimage'] = 1;
			$data['uploadip'] = get_client_ip();
			$data['status'] = 0;
			$db = M('attachment');
	        $rs = array();
			if($upload->upload()){
				$info = $upload->getUploadFileInfo();
				$row = $info[0];
				
				$url_path = C('CDN_DSY_IMG_HOST').'/'.$this->data_save_path.$row['savename'];
				
				if (C("ENABLE_OSS") === true) {
					// 同步上传到阿里云OSS
					$ret = $this->createClient(C("OSS_ENDPOINT"), C("OSS_KEY"), C("OSS_SECRET"));
					if ($ret['ret'] != 0) {
						$rs['id'] = -1;
						$rs['rs'] = -1;
						$rs['msg'] = '连接阿里云OSS失败! ret:'.$ret['ret']." msg:".$ret['msg']; 
						$rs['thumb'] = '';
						$rs['imgurl'] = '';
						echo json_encode($rs);
						return;
					}
					$client = $ret['msg'];
						
					$final_file_path = $upload->savePath.$row['savename'];
					$key = $this->data_save_path.$row['savename'];
					$ret = $this->multipartUpload($client, C("OSS_PIC_BUCKET"), $key, $final_file_path);
					if ($ret['ret'] != 0) {
						$rs['id'] = -1;
						$rs['rs'] = -1;
						$rs['msg'] = '上传文件到阿里云OSS失败! ret:'.$ret['ret']." msg:".$ret['msg']; 
						$rs['thumb'] = '';
						$rs['imgurl'] = '';
						echo json_encode($rs);
						return;
					}
					$url_path = C('CDN_DSY_IMG_HOST').'/'.C('OSS_KEY_PREFIX').$this->data_save_path.$row['savename'];
				}
				$data['filename'] = $row['name'];
				$data['filepath'] = $url_path;
				$data['filesize'] = $row['size'];
				$data['fileext']  = $row['extension'];
				$data['hashcode'] = $row['hash'];
				$db->add($data);
				$rs['id'] = $db->getLastInsID(); //修改为0只是为了返回的预览里不展示为"undefiend"
				$rs['rs'] = 0;
				$rs['msg'] = '上传成功';
				$rs['thumb'] =  $data['filepath'];
				$rs['imgurl'] =	$data['filepath'];
				echo json_encode($rs);
			}else{
				//专门用来获取上传的错误信息
				$rs['id'] = -1;
				$rs['rs'] = -1;
				$rs['msg'] = $upload->getErrorMsg(); 
				$rs['thumb'] = '';
				$rs['imgurl'] = '';
				echo json_encode($rs);        
			}  
		} else {
			$this->display('upload');
		}
	}
	public function upload_file() {
		if (isset($_POST['dosubmit'])) {
	       
			//import('ORG.Net.DsyUploadFile');                           //将上传类UploadFile.class.php拷到Lib/Org文件夹下
			$upload = new \Org\Net\DsyUploadFile();
			
			$upload->maxSize        = '10485760';                    //默认为-1，不限制上传大小   
			$upload->saveRule       = uniqid;                          //上传文件的文件名保存规则
			$upload->uploadReplace  = true;                            //如果存在同名文件是否进行覆盖
			$upload->allowExts      = array('mp3','mp4','zip','rar','7z','doc','docx','xls','xlsx','ppt','pptx','pdf'); //准许上传的文件类型
			//$upload->allowTypes     = array('image/png','image/jpg','image/jpeg','image/gif'); //检测mime类型
			/*
			$upload->thumb          = true;                            //是否开启图片文件缩略图
			$upload->thumbMaxWidth  = '80';
			$upload->thumbMaxHeight = '60';
			$upload->thumbPrefix    = 'thumb_80_60_';      //缩略图文件前缀
			$upload->thumbRemoveOrigin = 0;                //如果生成缩略图，是否删除原图
			*/
			
			//保存路径建议与主文件平级目录或者平级目录的子目录来保存 
			$upload->savePath       = WEB_ROOT.C('DSY_IMG_UPLOAD_DIR'). date("Ym") . "/" . date("d") . "/"; 
			if (!file_exists($upload->savePath)) {
				@mkdir($upload->savePath, 0777, true);	
			}
		   
			$data = array();
			$data['userid'] = session('userid');
			if (empty($data['userid'])) $data['userid'] = 0;
			$data['module'] = '';		
			$data['catid']  = 0;
			$data['downloads'] = 0;
			$data['uploadtime'] = time();
			$data['isimage'] = 1;
			$data['uploadip'] = get_client_ip();
			$data['status'] = 0;
			
			$db = M('attachment');
	        $rs = array();
			
			if($upload->upload()){
				$info = $upload->getUploadFileInfo();
				$row = $info[0];
				$data['filename'] = $row['name'];
				$data['filepath'] = $row['savepath'] . $row['savename'];
				$data['filesize'] = $row['size'];
				$data['fileext']  = $row['extension'];
				$data['hashcode'] = $row['hash'];
				$data['module'] = 1;
				$db->add($data);
				
				
				$rs['id'] = $db->getLastInsID();
				$rs['rs'] = 0;
				$rs['msg'] = '上传成功';
				$rs['fileurl'] = __ROOT__ . '/' .$row['savepath'] . $row['savename'];
				echo json_encode($rs);
			}else{
				//专门用来获取上传的错误信息
				$rs['id'] = -1;
				$rs['rs'] = -1;
				$rs['msg'] = $upload->getErrorMsg(); 
				$rs['fileurl'] = '';
				echo json_encode($rs);        
			}  
		} else {
			$type = I('filestype');
			$this -> filetp = $type;	
			if($type=='mp3'){
				$this -> filetypename = '音乐';
				$this -> filesexts = '*.mp3';
				$this -> showfilesexts = 'mp3';
			}if($type=='mp4'){
				$this -> filetypename = '视频';
				$this -> filesexts = '*.mp4';
				$this -> showfilesexts = 'mp4';
			}if($type=='zip'){
				$this -> filetypename = '压缩';	
				$this -> filesexts = '*.zip; *.rar; *.7z';
				$this -> showfilesexts = 'zip,rar,7z';
			}if($type=='doc'){
				$this -> filetypename = '文档';	
				$this -> filesexts = '*.doc; *.docx; *.xls; *.xlsx; *.ppt;  *.pptx;';
				$this -> showfilesexts = 'doc,docx,xls,xlsx,ppt,pptx';
			}if($type=='pdf'){
				$this -> filetypename = 'PDF';	
				$this -> filesexts = '*.pdf';
				$this -> showfilesexts = 'pdf';
			}
			
			$this->display('upload_file');
		}
	}
	
	//抓取雅虎图片
	private function get_yahoo_img($key, $pages=''){
		$url = 'http://image.yahoo.cn/s?q='. urlencode($key) .'&c=0&s=1&page='.$pages;	
		$contents = file_get_contents($url); //抓取页面源代码
		
		//按<ul class="images">将内容拆分
		$content = explode('<ul class="images">', $contents);
		$ulinfo = explode('</ul>',$content[1]);
		$liinfo = explode('<li>',$ulinfo[0]);
	
		$imagesdata = '';
		foreach($liinfo as $key=>$value) {
			if($key!=0){
				//获取大图URL
				$url_1 = explode("'img_src':'",$value);
				$url = explode("','img_size'",$url_1[1]);
				//echo $url[0].'<br>';
				//获取小图URL
				preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$value,$match);
				//echo $match[1].'<br>';
				//获取图片尺寸大小
				$imgsize = explode('<div class="other">',$value);
				//echo strip_tags($imgsize[1]).'<br><br><br>';
				$imagesdata .= '<li><div class="imgdiv"><div class="select"></div><div class="outline"><img src="'.$match[1].'" height="60"/></div></div><div style="display:none;"><input type="checkbox" name="upfilebigurl" value="'.$url[0].'" /><input type="checkbox" name="upfilesimurl" value="'.$match[1].'" /><input type="checkbox" name="upfile" value="" /></div></li>';
				
			}
		}	
		return $imagesdata;
	}

	//将图片保存到本地服务器
	private function save_net_image($url, $filename=""){ 
		//$url 为空则返回 false; 
		if($url == ""){
			return false;
		} 
		$ext = strrchr($url, ".");//得到图片的扩展名 
		if($ext != ".gif" && $ext != ".jpg" && $ext != ".png"){
			echo "格式不支持！";
			return false;
		} 

		$targetPath =WEB_ROOT. C('DSY_IMG_UPLOAD_DIR'). date("Ym") . "/" . date("d") . "/"; 
		
		if (!file_exists($targetPath)) {
			@mkdir($targetPath, 0777, true);	
		}
		
		if($filename == ""){
			//$filename = time().random(6)."$ext";
			$filename = $targetPath . time().random(3).$ext;
		}
		//以时间戳另起名 
		//开始捕捉 
		ob_start(); 
		readfile($url); 
		$img = ob_get_contents(); 
		ob_end_clean(); 
		$size = strlen($img); 
		$fp2 = fopen($filename , "a"); 
		fwrite($fp2, $img); 
		fclose($fp2); 
		thumb($filename);
		
		$data = array();
		$data['userid'] = session('userid');
		if (empty($data['userid'])) $data['userid'] = 0;
		$data['module'] = '';		
		$data['catid']  = 0;
		$data['downloads'] = 0;
		$data['uploadtime'] = time();
		$data['isimage'] = 1;
		$data['uploadip'] = get_client_ip();
		$data['status'] = 0;
		$data['filename'] = basename($url);
		$data['filepath'] = $filename;
		$data['filesize'] = filesize($filename);
		$data['fileext']  = str_replace(".", "", $ext);
		$data['hashcode'] = time();
			
		$db = M('attachment');
		$db->add($data);
		return $db->getLastInsID();
	}
}

?>       
