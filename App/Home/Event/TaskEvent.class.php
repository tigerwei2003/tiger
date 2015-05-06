<?php
namespace Home\Event;
class TaskEvent
{
	public function check_send($account_id,$task_id)
	{
		$task_model=D("Task");
		$task_type_model=D("TaskType");
		$account_task_model=D("AccountTask");
		$account_model=D("Account");
		$income_coin_model=D("IncomeCoin");
		$task_info=$task_model->get_info_by_id($task_id);
		$task_type_id=$task_info['type_id'];
		$task_type_info=$task_type_model->get_info_by_id($task_type_id);
		if($task_type_info['biaoshi']=='meirirenwu')
		{
			$date=date('Y-m-d');
			$info=$account_task_model->get_info_by_where($account_id,$task_id,$date);	
		}
		else
		{
			$info=$account_task_model->get_info_by_where($account_id,$task_id);
		}
		if(!$info)
		{
			$account_info=$account_model->get_info_by_id($account_id);
			$account_model->startTrans();
			$data['bean']=$task_info['bean']+$account_info['bean'];
			$res=$account_model->save_data($account_id,$data);
			$data2['account_id']=$account_id;
			$data2['bean']=$task_info['bean'];
			$data2['income_type']=2;
			$data2['create_time']=time();
			$res_2=$income_coin_model->add_data($data2);
			if($res && $res_2)
			{
				$account_model->commit();
				$gate_url="";
				$content_arr=array('msg'=>"用户ID：".$account_info['id']."完成".$task_info['task_name'].",获得奖励".$task_info['bean']."云豆");
				$content=json_encode($content_arr);
				$request=new \Org\Net\RequestHandler();
				$httpClient=new \Org\Net\HttpClient();
				$request->setGateURL($gate_url);
				$request->setParameter('id', $account_id);
				$request->setParameter('notify'',$content);
				$url=$request->getRequestUrlNotSign();
				$httpClient->setTimeOut ( 60 );
				$httpClient->setMethod("post");
				// 设置请求内容
				$httpClient->setReqContent ( $reqUrl );
				$httpClient->call ();
				
			}else
				$account_model->rollback();
			
			
		}
	}
}