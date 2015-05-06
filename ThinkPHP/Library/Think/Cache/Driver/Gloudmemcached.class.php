<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;

defined('THINK_PATH') or exit();
/**
 * Memcached缓存驱动
 */
class Gloudmemcached extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($options=array()) {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if ( !extension_loaded('memcached') ) {
                E(L('_NOT_SUPPERT_').':memcached');
            }
        }

        $options = array_merge(array (
            'host'        =>  C('MEMCACHED_HOST') ? C('MEMCACHED_HOST') : '127.0.0.1',
            'port'        =>  C('MEMCACHED_PORT') ? C('MEMCACHED_PORT') : 11211,
            'username'	  =>  C('MEMCACHED_USER') ? C('MEMCACHED_USER') : NULL,
            'password'	  =>  C('MEMCACHED_PWD') ? C('MEMCACHED_PWD') : NULL,
            'ocs'	  =>  C('MEMCACHED_OCS') ? C('MEMCACHED_OCS') : NULL,
            'timeout'     =>  C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
            'en_memc'     =>  C('MEMCACHED_STATUS') ? C('MEMCACHED_STATUS') : false,
            'persistent'  =>  false,
        ),$options);

        $this->options      =   $options;
        if($this->options['en_memc']){
	        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
	        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');        
	        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
	        $func               =   $options['persistent'] ? 'pconnect' : 'connect';
	        $this->handler      =   new \Memcached;
	        if($options['persistent'] && $options['timeout'] !== false)
				$this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $options['timeout']);
			if($options['ocs'])
			{
				$this->handler->setOption(\Memcached::OPT_COMPRESSION, false);
				$this->handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
				$this->handler->addServer($options['host'], $options['port']);
				$this->handler->setSaslAuthData($options['username'],$options['password']);
			}
			else
			{
				$this->handler->addServer($options['host'], $options['port']);
			}
        }
    }

 /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
    	if($this->options['en_memc'] === false)
    		return false;
        N('cache_read',1);
        return $this->handler->get($this->options['prefix'].$name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null) {
    	if($this->options['en_memc'] === false)
    		return false;
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        if($this->handler->set($name, $value, $expire)) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }
        return false;
    }
    
    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function add($name, $value, $expire = null) {
    	if($this->options['en_memc'] === false)
    		return false;
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        if($this->handler->add($name, $value, $expire)) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }
        return false;
    }
    
	/**
	 * 向已存在元素后追加数据
     * @access: public
     * @param: string $ key
     * @param:$value value
     * @return : true OR false
    **/
    public function append($name, $value ,$expire = NULL){
    	if($this->options['en_memc'] === false)
    		return false;
    	N('cache_write',1);
    	if(is_null($expire))
    		$expire = $this->options['expire'];
   		$name = $this->options['prefix'].$name;
   		if($this->handler->append($name, $value, $expire)){
   			if($this->options['length']>0){
   				$this->queue($name);
   			}
   			return true;
   		}
   		return false;
    }
    
    /** 
     * @todo replace 根据key替换值
     * @parem $key key值 
     * @return true or false
    */
    public function replace($name,$value, $expire = NULL){
    	if($this->options['en_memc'] === false)
    		return false;
    	N('cache_write',1);
        if(is_null($expire))
        	$expire = $this->options['expire'];
       	$name = $this->options['prefix'].$name;
       	if($this->handler->replace($name,$value,$expire)){
       		if($this->options['length']>0){
       			$this->queue($name);
       		}
       		return true;
       	}
       	return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @param boolen $get_before_rm 表明是否要先get,如果get数据为false时,直接返回true
     * @return boolen
     */
    public function rm($name, $ttl = false, $get_before_rm = false) {
    	if($this->options['en_memc'] === false)
    		return false;
        $name   =   $this->options['prefix'].$name;
        if($get_before_rm){
        	if($this->handler->get($name) === false)
        		return true;
        }
        return $ttl === false ? $this->handler->delete($name) : $this->handler->delete($name, $ttl);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flush();
    }
    
        /**
     * @todo 缓存memcached最后一次的操作结果
    */
    public function getResultCode()
    {
    	if($this->options['en_memc'] === false)
    		return false;
        return $this->handler->getResultCode();
            
    }
}
