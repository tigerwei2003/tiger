<?php
return array(
		//'配置项'=>'配置值'
		/* 'DB_DEPLOY_TYPE'=> 1, // 设置分布式数据库支持
		'DB_RW_SEPARATE'=>true, */
	 	/*'DB_HOST'  => 'gloudtest.mysql.rds.aliyuncs.com',
		'DB_PORT'  => '3333',
		'DB_USER'  => 'dsytest',
		'DB_PWD'   => 'Testify8760',
		'DB_NAME'  => 'cloudgaming_test', 
		'DB_CONFIG2' => 'mysql://dsytest:Testify8760@gloudtest.mysql.rds.aliyuncs.com:3333/cloudgaming_test',*/
		'DB_HOST'  => 'localhost',
		'DB_PORT'  => '3306',
		'DB_USER'  => 'root',
		'DB_PWD'   => '123456',
		'DB_NAME'  => 'cloudgaming_test', 
		'DB_CONFIG2' => 'mysql://root:123456@localhost:3306/cloudgaming_test',
	/*	'DB_HOST'  => 'gloudtestdb2.mysql.rds.aliyuncs.com',
		'DB_PORT'  => '3333',
		'DB_USER'  => 'gloudtest',
		'DB_PWD'   => 'GloudRocks2015',
		'DB_NAME'  => 'cloudgaming', */
	/* 	'DB_HOST'  => 'rdsbzjvnb2uvyai.mysql.rds.aliyuncs.com',
		'DB_PORT'  => '3306',
		'DB_USER'  => 'dsyread',
		'DB_PWD'   => 'ReadIt2Me',
		'DB_NAME'  => 'cloudgaming', */
		'DB_PREFIX'=> 'july_',
		'DB_CHARSET'=>'utf8',
		'DB_TYPE'=>'mysql', 
		// 动视云memcached配置
		'MEMCACHED_STATUS'=>true,
		'MEMCACHED_LOG'=>true,
		'DATA_CACHE_PREFIX'=>'develop_',
		// 动视云redis配置
		'ENABLE_REDIS' => true,
		'REDIS_DATA_CACHE_PREFIX'=>'develop_',
		// 动视云阿里云OSS配置
		'ENABLE_OSS' => true,
		'OSS_KEY_PREFIX' => 'test_', // 线上必须为空
		// 擂台赛ALS服务器地址
		'ALS_HOST'  => '121.40.52.62',
		'ALS_PORT'  => '8083',
		
 		//'配置项'=>'配置值'
		'VAR_MODULE'            =>  'c',     // 默认模块获取变量
		'VAR_CONTROLLER'        =>  'm',    // 默认控制器获取变量
		'VAR_ACTION'            =>  'a',    // 默认操作获取变量 
		
		'LOG_RECORD' => true, // 开启日志记录
		'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR,WARN,NOTICE,INFO', // 只记录EMERG ALERT CRIT ERR 错误
		'LOG_FILE_SIZE' => 209715200, // 200MB日志文件切分一次


);
