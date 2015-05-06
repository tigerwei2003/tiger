<?php
return array(
		//'配置项'=>'配置值'
		'DB_HOST'  => 'dsybill001.mysql.rds.aliyuncs.com',
		'DB_PORT'  => '3333',
		'DB_USER'  => 'dsyadmin1024',
		'DB_PWD'   => 'TouchTheSky1024',
		'DB_NAME'  => 'cloudgaming',
		'DB_PREFIX'=> 'july_',
		'DB_CHARSET'=>'utf8',
		'DB_TYPE'=>'mysql', 
		'DB_CONFIG2' => 'mysql://dsyread:ReadIt2Me@rdsbzjvnb2uvyai.mysql.rds.aliyuncs.com:3306/cloudgaming',
		// 动视云memcached配置
		'MEMCACHED_STATUS'=>true,
		'MEMCACHED_LOG'=>true,
		'DATA_CACHE_PREFIX'=>'online_',
		// 动视云redis配置
		'ENABLE_REDIS' => true,
		'REDIS_DATA_CACHE_PREFIX'=>'online_',
		// 动视云阿里云OSS配置
		'ENABLE_OSS' => true,
		'OSS_KEY_PREFIX' => '', // 线上必须为空
		// 擂台赛ALS服务器地址
		'ALS_HOST'  => '121.41.119.143',
		'ALS_PORT'  => '8083',
		
		//'配置项'=>'配置值'
		'VAR_MODULE'            =>  'c',     // 默认模块获取变量
		'VAR_CONTROLLER'        =>  'm',    // 默认控制器获取变量
		'VAR_ACTION'            =>  'a',    // 默认操作获取变量
		
		'LOG_RECORD' => true, // 开启日志记录
		'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR,WARN,NOTICE,INFO', // 只记录EMERG ALERT CRIT ERR 错误
		'LOG_FILE_SIZE' => 209715200, // 200MB日志文件切分一次
);

