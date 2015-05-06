<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Log;
use Think\Model;
//require_once 'AliyunOss/aliyun.php';
Vendor('AliyunOss.aliyun');

use Aliyun\OSS\OSSClient;
use Aliyun\OSS\Exceptions\OSSException;
use Aliyun\Common\Exceptions\ClientException;
class BaseController extends Controller
{
	public function _initialize(){
		header("Content-Type:text/html; charset=utf-8");
		/* 对用户传入的变量进行转义操作。 */
	/* 	if (! get_magic_quotes_gpc()) {
			if (! empty($_GET)) {
				$_GET = addslashes_deep($_GET);
			}
			if (! empty($_POST)) {
				$_POST = addslashes_deep($_POST);
			}
			$_COOKIE = addslashes_deep($_COOKIE);
			$_REQUEST = addslashes_deep($_REQUEST);
		} */

		if(!cookie('userid')){

			if(ACTION_NAME!='login' && ACTION_NAME!='backpassword'){
				$login_url=U('Public/login');
				$this->error('您尚未登陆，请先登录！',$login_url);
				exit;
			}
		}
		//不用认证的控制器
		$not_auth_contraller=C('NOT_AUTH_CONTROLLER');
		$not_auth_arr=explode(',', $not_auth_contraller);
		//不需要认证的角色默认只有超级管理员不需要认证
		$not_auth_role=C('NOT_AUTH_ROLE');
		$not_auth_role_arr=explode(',', $not_auth_role);


		$now_contraller=CONTROLLER_NAME;
		if(cookie('username')!='admin')
		{
			if(!in_array(cookie('roleid'), $not_auth_role_arr))
			{
				if (!in_array($now_contraller, $not_auth_arr) ) {

					$access=CONTROLLER_NAME. '+' . ACTION_NAME;
					$access_cache_file=MODULE_PATH . '/filecache/groupacl.php';
					if (! file_exists($access_cache_file)) {
						$acl = $this->make_access_cache();
					}
					$acl = include $access_cache_file;
					$role_id = cookie('roleid');
					$role_acl = $acl[$role_id];
					$groupacl = array_values($role_acl);
					if (! in_array($access, $groupacl)) {
						$this->error("您没有权限访问该菜单");
					}

				}
			}
		}
		$public_controller=A('Public');
		$public_controller->header();
		$controller_name=CONTROLLER_NAME;
		$action_name=ACTION_NAME;
		$node_info=$this->get_menu_info($controller_name, $action_name); 
		$this->node_info_header=$node_info;
	   
		
	}


	public function make_access_cache()
	{
		$role_model=D('Roles');
		$role_data=$role_model->get_all_role_data();
		$acl_arr = array();
		foreach ($role_data as $row) {
			$role_acl = $this->master_acl($row['id']);
			$acl_cache[$row['id']] = $role_acl;

		}
		$cache = var_export($acl_cache, true);
		$file = MODULE_PATH . '/filecache/groupacl.php';
		$contents = "<?php if (!defined('THINK_PATH')) exit();\r\n return {$cache} ?>";
		write_cache($file, $contents);

	}

	function master_acl ($role_id = 0)
	{
	 $aclArray = array();
	 $node_model=D("Nodes");
        $access_model =D("Accesss");
      
        $where['role_id']=$role_id;
        $where['level']=array('Gt',1);
        $control=$access_model->get_data_by_where($where);
        
        $c_num = count($control);
        for ($j = 0; $j < $c_num; $j ++) {
            
            $condition2['id'] = $control[$j]['node_id'];
            $arr = $node_model->where($condition2)->find();
            $node_url = $arr['url'];
            $menuarr = explode('/', $node_url);
            $menuname = $menuarr[0];
            $childmenuname = $menuarr[1];
            $aclArray[] = $menuname . '+' . $childmenuname;
        }
        return $aclArray;
	}
	protected function get_menu_info($controller_name,$action_name)
	{
		$controller_name=ucfirst($controller_name);
		$action_name=strtolower($action_name);
		$url=$controller_name.'/'.$action_name;
		$node_model=D("Nodes");
		$node_info=$node_model->get_node_info_by_url($url);
		
		return $node_info;
	}
	function memcache_error_log($msg)
	{
		if(C('MEMCACHED_LOG'))
			Log::write("memcache_error_log,msg:$msg",Log::ERR);
	}
	
	function return_ex($ret, $msg) {
		return array('ret'=>$ret, 'msg'=>$msg);
	}
	// Sample of create client
	function createClient($endPoint, $accessKeyId, $accessKeySecret) {
		try {
			return $this->return_ex(0,
					OSSClient::factory(array(
							'Endpoint' => $endPoint,
							'AccessKeyId' => $accessKeyId,
							'AccessKeySecret' => $accessKeySecret,
					)));
		} catch (OSSException $ex) {
			return $this->return_ex(-800, "createClient failed. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-801, "createClient failed. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}
	
	function checkObject($client, $bucket, $key) {
		$key = C('OSS_KEY_PREFIX').$key;
		try {
			$object = $client->getObjectMetadata(array(
				'Bucket' => $bucket,
				'Key' => $key,
			));
			return $this->return_ex(0, "success");
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "getObjectAsFile failed. key: $key. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "getObjectAsFile failed. key: $key. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}
	
	function deleteObject(OSSClient $client, $bucket, $key) {
		$key = C('OSS_KEY_PREFIX').$key;
		try {
			$client->deleteObject(array(
				'Bucket' => $bucket,
				'Key' => $key,
			));
			return $this->return_ex(0, 'success');
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "deleteObject failed. key: $key. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "deleteObject failed. key: $key. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}
	
	// Sample of get object
	function getObjectAsFile($client, $bucket, $key, $fileName) {
		$key = C('OSS_KEY_PREFIX').$key;
		try {
			$object = $client->getObject(array(
					'Bucket' => $bucket,
					'Key' => $key,
			));
				
			file_put_contents($fileName, $object->getObjectContent());
			return $this->return_ex(0, "success");
		} catch (OSSException $ex) {
			return $this->return_ex(-900, "getObjectAsFile failed. key: $key. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-901, "getObjectAsFile failed. key: $key. ClientExcetpion, Message: " . $ex->getMessage());
		}
	}
	
	// Sample of multipart upload
	function multipartUpload($client, $bucket, $key, $fileName) {
		$key = C('OSS_KEY_PREFIX').$key;
		$partSize = 5 * 1024 * 1024; // 5M for each part，阿里云要求每个块不小于5M
	
		//echo "Start uploading $fileName to $key \n";
	
		if (!file_exists($fileName))
			return $this->return_ex(-1, "$fileName does not exist");
			
		$content_type = "application/octet-stream";
		$file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		if ($file_ext == "jpeg" || $file_ext == "jpg")
			$content_type = "image/jpeg";
		else if ($file_ext == "png")
			$content_type = "image/png";
		else if ($file_ext == "gif")
			$content_type = "image/gif";
		else if ($file_ext == "html" || $file_ext == "htm")
			$content_type = "text/html";
	
		try {
			// Init multipart upload
			$uploadId = $client->initiateMultipartUpload(array(
					'Bucket' => $bucket,
					'Key' => $key,
					'ContentType' => $content_type,
			))->getUploadId();
	
			// upload parts
			$fileSize = filesize($fileName);
			$partCount = (int) ($fileSize / $partSize);
			if ($fileSize % $partSize > 0) {
				$partCount += 1;
			}
	
			$partETags = array();
			for ($i = 0; $i < $partCount ; $i++) {
				$uploadPartSize = ($i + 1) * $partSize > $fileSize ? $fileSize - $i * $partSize : $partSize;
				$file = fopen($fileName, 'r');
				fseek($file, $i * $partSize);
				$partETag = $client->uploadPart(array(
						'Bucket' => $bucket,
						'Key' => $key,
						'UploadId' => $uploadId,
						'PartNumber' => $i + 1,
						'PartSize' => $uploadPartSize,
						'Content' => $file,
				))->getPartETag();
				$partETags[] = $partETag;
	
				fseek($file, $i * $partSize);
				$data = fread($file, $uploadPartSize);
				$real_md5 = md5($data);
	
				//echo "Completed: part:$i/$partCount size:$uploadPartSize PartNumber:".$partETag['PartNumber']. " ETag:".$partETag['ETag']." realmd5:$real_md5\n";
	
				if (strtolower($partETag['ETag']) != $real_md5)
					return $this->return_ex(-2, "md5 not match");
			}
	
			//echo "call completeMultipartUpload $key \n";
			$result =  $client->completeMultipartUpload(array(
					'Bucket' => $bucket,
					'Key' => $key,
					'UploadId' => $uploadId,
					'PartETags' => $partETags,
			));
		} catch (OSSException $ex) {
			return $this->return_ex(-1, "multipartUpload failed. OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage());
		} catch (ClientException $ex) {
			return $this->return_ex(-2, "multipartUpload failed. ClientExcetpion, Message: " . $ex->getMessage());
		}
	
		return $this->return_ex(0, "Completed: " . $result->getETag());
	}
}

