<?php
return array(

		'COMPANY_IP'=>'124.207.55.164',
		//邮件配置
		'EMAIL_CONFIG' =>  array(
				'SMTP_HOST' => 'ssl://smtp.exmail.qq.com', // SMTP服务器
				'SMTP_PORT' => '465', // SMTP服务器端口,使用465端口必须要求PHP开启openssl扩展
				'SMTP_USER' => 'noreply@51ias.com', // SMTP服务器用户名
				'SMTP_PASS' => 'dsydsy123', // SMTP服务器密码
				'FROM_EMAIL' => 'noreply@51ias.com', // 发件人EMAIL
				'FROM_NAME' => '动视云游戏', // 发件人名称
				'REPLY_EMAIL' => '', // 回复EMAIL（留空则为发件人EMAIL）
				'REPLY_NAME' => '', //回复名称（留空则为发件人名称）
				'MAIL_CHARSET' =>'utf-8'//设置邮件编码
		),
		//动视云游戏图片上传配置
		'IMG_UPLOAD_CONFIG' =>  array(
				'GAME'=>array('ORIGIN_PATH'=>"game/big/"), //游戏截图存放主文件夹
				'TITLE'=>array('ORIGIN_PATH'=>"game/title/"), //游戏封面图存放主文件夹
				'CONTROL'=>array('ORIGIN_PATH'=>"game/control/"), //游戏操控图存放主文件夹
				'RECO'=>array('ORIGIN_PATH'=>"reco/"), //推荐项的图片存放主文件夹
				'GAMEPACK'=>array('ORIGIN_PATH'=>"gamepack/"), //游戏包的图片存放主文件夹
		),
		'DSY_IMG_UPLOAD_DIR' =>'client/pic/',
		'REAL_WWW_ROOT'=>'/usr/share/nginx/html/gloudapi2/',//服务器的根目录（即DocmentRoot）
		'CDN_DSY_IMG_HOST' =>"http://pic2.51ias.com/",//CDN域名
		'UPLOAD_DIR' => 'Upload/',
		'SHOW_PAGE_TRACE'=>false,		
		// 每个级别对应的经验值
		'LEVEL_EXP' => array(
				'0',    	// 0
				'1000',  	// 1
				'3100',  	// 2
				'6400',  	// 3
				'11000', 	// 4
				'17000', 	// 5
				'24500', 	// 6
				'33600', 	// 7
				'44400', 	// 8
				'57000', 	// 9
				'71500', 	// 10
				'89000', 	// 11
				'109700',	// 12
				'133800',	// 13
				'161500',	// 14
				'193000',	// 15
				'228500',	// 16
				'268200',	// 17
				'312300',	// 18
				'361000',	// 19
				'414500',	// 20
				'475000',   // 21
				'542800',  	// 22
				'618200',  	// 23
				'701500',  	// 24
				'793000', 	// 25
				'893000', 	// 26
				'1001800', 	// 27
				'1119700', 	// 28
				'1247000', 	// 29
				'1384000', 	// 30
				'1534000', 	// 31
				'1697400', 	// 32
				'1874600',	// 33
				'2066000',	// 34
				'2272000',	// 35
				'2493000',	// 36
				'2729400',	// 37
				'2981600',	// 38
				'3250000',	// 39
				'3535000',	// 40
				'3841000',	// 41
				'4168500',  // 42
				'4518000',  // 43
				'4890000',  // 44
				'5285000',  // 45
				'5703500', 	// 46
				'6146000', 	// 47
				'6613000', 	// 48
				'7105000', 	// 49
				'7622500', 	// 50
		),

		// 每个用户在单个游戏上自建存档序列的最大数目
		'MAX_SERIAL_NUM_PER_GAME' => 3,
		// 游戏存档目录。权限：chmod 0744 GAME_SAVE_DIR
		'GAME_SAVE_DIR_LINUX' => "/mnt/uds/",
		'GAME_SAVE_DIR_WIN' => "d:\\gamesaves\\",
		// 最大上传文件大小
		'MAX_UPLOAD_FILE_SIZE' => 50*1024*1024,
		// 低于这个等级的游戏才能被用户看见
		'VISIBLE_GAME_LEVEL' => 100,
		// 最近玩过的游戏，最多显示N个
		'MAX_RECENT_GAMES' => 9,
		// 单个帐号最多绑定几个设备？
		'SINGLE_ACCOUNT_MULTI_DEVICE' => 5,
		// 限制只允许大麦盒子运行
		'DAMAI_ONLY' => 0,
		// 动视云memcached配置
		/*'DATA_CACHE_TYPE' => 'Gloudmemcached',
		'MEMCACHED_OCS' => true,
		'MEMCACHED_HOST' => 'e609c47b3e4e11e4.m.cnhzalicm10pub001.ocs.aliyuncs.com',
		'MEMCACHED_USER' => 'e609c47b3e4e11e4',
		'MEMCACHED_PWD' => 'a021_18dd',
		'DATA_CACHE_TIME' => 1000,*/
		
		'DATA_CACHE_TYPE'=>'Memcache',
		
		// 动视云redis配置
		'REDIS_DATA_CACHE_TYPE' => 'Redis',
		'REDIS_HOST'=>'2fe4ef45c89f11e4.m.cnhza.kvstore.aliyuncs.com',
		'REDIS_PORT'=>6379,
		'REDIS_USER'=>'2fe4ef45c89f11e4',
		'REDIS_PWD'=>'dsyRedis15',
		'REDIS_DATA_CACHE_TIME' => 3600,
		// 动视云阿里云OSS配置
		'OSS_ENDPOINT' => 'http://oss.aliyuncs.com',
		'OSS_KEY' => 'wdDW1iCBmjqxmU6t',
		'OSS_SECRET' => 'bAPepFdYmGWYaFsLQm5y4zAerwIO98',
		'OSS_UDS_BUCKET' => 'uds',
		'OSS_PIC_BUCKET' => 'gphoto',
		'SEO_TITLE' 		=> '格来云游戏',
		'SEO_DISCREPTION'	=> '格来云游戏',
		'SEO_KEYWORDS' 		=> '格来云游戏',
		'SEO_COPYRIGHT'		=> 'Copyright &copy; 2014 www.51ias.com',
);

