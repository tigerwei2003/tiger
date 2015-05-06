<?php
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
//define('APP_DEBUG',True);
//定义环境为测试环境
define('APP_STATUS','develop');
define('BIND_MODULE','Home');
// 定义应用目录
define('APP_PATH','./App/');
//定义网站根目录
define('WEB_ROOT',dirname(__FILE__).DIRECTORY_SEPARATOR);
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';
// 亲^_^ 后面不需要任何代码了 就是如此简单
