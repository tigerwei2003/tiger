<?php
/*自定义常量*/
define("PAGE_NUM","15");    //分页每页条数
return array(
		'URL_MODEL'  => 0, // URL模式
		//权限配置相关项
		'NOT_AUTH_CONTROLLER'=>'Index,DsyAttachment',
		'NOT_AUTH_ROLE'=>'1',//不需要认证的组
		'SEO_TITLE' 		=> '格来云游戏',
		'SEO_DISCREPTION'	=> '格来云游戏',
		'SEO_KEYWORDS' 		=> '格来云游戏',
		'SEO_COPYRIGHT'		=> 'Copyright &copy; 2014 www.51ias.com',
		//模板相关设置
		'TMPL_PARSE_STRING' => array(
				'__HTML__'   => __ROOT__. '/Public/static',
				'__UPLOAD__' => __ROOT__ . '/Upload',
		),
		'TMPL_ACTION_SUCCESS'=>'Public:jump_yes',
		'TMPL_ACTION_ERROR'=>'Public:jump_no',
		/* 'DB_DEPLOY_TYPE'=> 1, // 设置分布式数据库支持
		'DB_RW_SEPARATE'=>true,
	 	'DB_HOST'  => 'gloudtestdb2.mysql.rds.aliyuncs.com,gloudtestdb2.mysql.rds.aliyuncs.com',
		'DB_PORT'  => '3333',
		'DB_USER'  => 'gloudtest',
		'DB_PWD'   => 'GloudRocks2015',
		'DB_NAME'  => 'cloudgaming', 
		'DB_PREFIX'=> 'july_',
		'DB_CHARSET'=>'utf8',
		'DB_TYPE'=>'mysql', */
	
		
);