<?php
namespace Home\Controller;
use Home\Controller\BaseController;
class AnonyController extends BaseController
{
	/*
	 检查客户端版本信息，获取需要升级的版本。
	请求格式：形如http://localhost/api.php?m=Anony&a=client_ver&pid=???&ver=???
	pid是长度不超过64字节的字符串，代表一个唯一的渠道。
	ver是一个32位整数，客户端版本。
	返回格式：该渠道的最新客户端版本。形如：
	["pid":"gloud","ver":"123","desc":"new version on 20140415","url":"http://cdn.51ias.com/client/client_20140415.apk"]

	pid是长度不超过64字节的字符串，代表一个唯一的渠道。
	ver是一个32位整数，表示该渠道的最新版本。
	desc是长度不超过1024字节的字符串，是最新版本的描述。
	url是长度不超过1024字节的字符串，是最新版本的下载链接。
	force_update是一个32位整数，1表示强制升级，其他值表示普通升级。
	*/
	public function client_ver()
	{
		$pid = I("pid", '');
		$ver = I("ver", '');
		$product = I("product",'0');
		$client_type = I("client_type",0);
		//$clientIp = get_client_ip();
		if ($pid == '' || $ver =='')
			return $this->respond(-100, "invalid request");
		$ret_ver = array();
		//根据条件获取版本信息
		$client_ver_model=D("ClientVer");
		$ver_info=$client_ver_model->api_get_ver($pid,$ver,$product,$client_type);
		if (!$ver_info)
			$ret_ver['current'] = array('ver' => '0', 'name' => '', 'desc' => '', 'url' => '', 'force_update' => '0');
		else
			$ret_ver['current'] = $ver_info;
		//获取最新应用版本信息
		$new_ver_info = $client_ver_model->api_get_new_ver($pid,$product,$client_type);
		if (!$new_ver_info)
			$ret_ver['latest'] = array('ver' => '0', 'name' => '', 'desc' => '', 'url' => '', 'force_update' => '0');
		else
			$ret_ver['latest'] = $new_ver_info;

		$ret_ver['current']['desc'] = htmlspecialchars_decode($ret_ver['current']['desc']);
		$ret_ver['latest']['desc'] = htmlspecialchars_decode($ret_ver['latest']['desc']);

		//当product为擂台时.过滤html标签
		if($product == '1'){
			$ret_ver['current']['desc'] = str_replace("<p>","",$ret_ver['current']['desc']);
			$ret_ver['current']['desc'] = str_replace("</p>","",$ret_ver['current']['desc']);
			$ret_ver['current']['desc'] = str_replace("\r\n\r\n","\n",$ret_ver['current']['desc']);
			$ret_ver['current']['desc'] = str_replace("\r\n","\n",$ret_ver['current']['desc']);
			$ret_ver['latest']['desc'] = str_replace("<p>","",$ret_ver['latest']['desc']);
			$ret_ver['latest']['desc'] = str_replace("</p>","",$ret_ver['latest']['desc']);
			$ret_ver['latest']['desc'] = str_replace("\r\n\r\n","\n",$ret_ver['latest']['desc']);
			$ret_ver['latest']['desc'] = str_replace("\r\n","\n",$ret_ver['latest']['desc']);
		}
		return $this->respond_ex(0, 'success', 'ver', $ret_ver);
	}
	
	
	/*
		各种汇报接口。
		客户端汇报连接GSM失败：		http://localhost/api.php?m=Anony&a=report&deviceid=???&type=gsmfail&gsmid=1&gsmip=xxx.xxx.xxx.xxx
		客户端汇报测速结果：		http://localhost/api.php?m=Anony&a=report&deviceid=???&type=nettest&stsip=xxx.xxx.xxx.xxx&stsport=8081&ping=??&kbps=???
                                                                    stsip:测速的服务器,stsport:端口号,ping:延迟,kbps=测速带宽  
		客户端汇报硬件信息：		http://localhost/api.php?m=Anony&a=report&deviceid=???&type=hwinfo&model=???&hardware=???&product=???&display=???&manu=???&cpucores=???
		python汇报上传存档失败：	http://localhost/api.php?m=Anony&a=report&deviceid=???&type=saveuploadfail&saveid=???&md5=???&size=???&gsid=???&gameid=???uploadtoken=???
		GSD汇报GS进程崩溃:			http://localhost/api.php?m=Anony&a=report&type=gscrash&gsdid=xxx&gsid=xxx&exitcode=xxx
		deviceid是长度不超过64字节的字符串，代表一个唯一的设备。
		返回格式：
		{"ret":0,"msg":"success"}

		ret是一个32位整数，具体值定义见下方。
		msg是一个长度不超过128字节的字符串。
		ret定义：
		0: 成功
		-100:无效请求
		-101:sql执行错误
		-102:没有设备ID
		非零值均表示失败。
	*/
	public function report(){
		$deviceid = I('deviceid','');
		$type = I('type','');
        if($deviceid== ''){
            return $this->respond(-100,"invalid request. deviceid is empty.");
        }
		if($type=='nettest'){
            //测速接口
            $data=array(
                'device_uuid'=>$deviceid,
                'stsip'=>I('stsip',''),
                'account_ip'=>get_client_ip(),
                'stsport'=>I('stsport',0),
                'region_id'=>I('region_id',0),   //大厅会汇报region_id和isp_id
                'ping'=>I('ping',0),
                'kbps'=>I('kbps',0),
                'create_time'=>time(),
                'start_time'=>I('start_time',0),
                'end_time'=>I('end_time',0)
            );
            $db = M('nettest');
            $result=$db->data($data)->add();
            if(!$result)
            {
                return $this->respond(-101,"SQL error.");
            }
        }
        elseif($type=='hwinfo')
        {
            $m=M('device');
            //判断设备是否存在在设备表中
            $device=$m->where("device_uuid='{$deviceid}'")->find();
            if(!$device)
            {
                return $this->respond(-102,"Without this device");
            }
            // 硬件汇报接口
            $hardware=I('hardware','');
            $product=I('product','');
            $model=I('model','');
            $manu=I('manu','');
            $data=array(
                'android_hardware'=>$hardware,
                'android_product'=>$product,
                'android_model'=>$model,
                'android_manu'=>$manu,
                'android_display'=>I('display',''),
                'user_agent'=>I('ua','')
            );
            $hardware_db=M('hardware');
            $type=$hardware_db->field('type,device_name')->where("hardware='{$hardware}' and product='{$product}' and model='{$model}' and manu='{$manu}'")->find();
            if($type)
            {
                $data['type']=$type['type'];
                $data['model']=$type['device_name'];
            }
            $exist=$m->where($data)->find();
            if(!$exist)
            {
                $result=$m->where("device_uuid='{$deviceid}'")->save($data);
                if($result === false)
                {
                    return $this->respond(-101,"SQL error.");
                } 
            }
        }
        else if($type=='gsmfail')
        {
            return $this->respond(-101,"gsmfail");
        }
        else
        {
            return $this->respond(-100,"invalid request. type is empty.");
        }
		// TODO: 以上汇报都应该存到数据库。上传存档失败应该有报警短信
		return $this->respond(0, "success");
	}
	
	//获取指定区域ID的上次测速结果和指定测速服务器IP上次的测速结果
	public function get_nettest(){
		$deviceid = I('deviceid','');
		$logintoken = I('logintoken','');
		$region_id = I('region_id',0);
		$province_id = I('province_id',0);
		$isp_id = I('isp_id',0);
		$stsip = I('stsip',0);
		$stsport = I('stsport',0);

		$ret = $this->check_device_account_logintoken($deviceid, $logintoken);
		if ($ret['ret'] != 0)
			return $this->respond($ret['ret'], $ret['msg']);
		$db_device = $ret['msg'];
		$account_id = $db_device['bind_account'];
		
		$condition = array();
		if($region_id != 0)
			$condition['n.region_id'] = $region_id;
		if($isp_id != 0)
			$condition['n.isp_id'] = $isp_id;
		if($province_id != 0)
			$condition['n.province_id'] = $province_id;
		if($stsip != 0 && $stsport != 0){
			$condition['n.stsip'] = $stsip;
			$condition['n.stsport'] = $stsport;
		}
		if($deviceid != '' && $logintoken != '')
			$condition['n.device_uuid'] = $deviceid;
		
		$last_nettest = M('nettest')->table('july_nettest as n')->join('july_region as r on n.region_id = r.id','left')
									->field('n.account_ip,n.region_id,r.name as region_name,n.isp_id,n.province_id,n.stsip,n.stsport,n.ping,n.kbps,n.create_time')->where($condition)->order('n.id desc')->find();
		if($last_nettest === false)
			return $this->respond(-106,'查询测速记录失败，请稍后再试。');
		if(count($last_nettest) <= 0 )
			$last_nettest = (object)array();
			
		return $this->respond_ex(0,'success','last_nettest',$last_nettest);
	}
}
