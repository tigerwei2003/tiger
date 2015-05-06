<?php
namespace Common\Model;
use Think\Model;
class ExchangeRecordModel extends Model
{
	public function get_data_by_account_id($account_id)
	{
		$memcache_key='exchange_record_account_id_'.$account_id;
	}
}