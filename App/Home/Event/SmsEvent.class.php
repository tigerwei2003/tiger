<?php
namespace Home\Event;
class SmsEvent
{
	public function SmsSend($mobile_arr,$content,$sid=""){
		$response_data = \Org\Util\Sms_0::sendMessage($mobile_arr, $content,$sid);
		//$response_data = \Org\Util\Sms_1::sendMessage($mobile_arr, $content,$sid);
		return $response_data;
	}
}