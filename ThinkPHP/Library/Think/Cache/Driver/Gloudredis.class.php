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
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Gloudredis extends Redis {
/**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && !extension_loaded('redis')) {
            E(L('_NOT_SUPPERT_').':redis');
        }
        
        $options = array_merge(array (
            'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
            'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
            'timeout'       => C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
            'enable_redis'     	=> C('ENABLE_REDIS') ? C('ENABLE_REDIS') : false,
            'persistent'    => false,
        ),$options); // 后面的会覆盖前面的配置项
        
        $this->options =  $options;
        
        if($this->options['enable_redis'])
		{
	        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('REDIS_DATA_CACHE_TIME');
	        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('REDIS_DATA_CACHE_PREFIX');        
	        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
	        $func = $options['persistent'] ? 'pconnect' : 'connect';
	        $this->handler  = new \Redis;
	        $options['timeout'] === false ?
	            $this->handler->$func($options['host'], $options['port']) :
	            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
         	$this->auth(C('REDIS_USER').':'.C('REDIS_PWD'));
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
    	if($this->options['enable_redis'] === false)
    		return false;
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
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
    	if($this->options['enable_redis'] === false)
    		return false;
        N('cache_write',1);
        if(is_null($expire))
            $expire  =  $this->options['expire'];
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire)) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
    	if($this->options['enable_redis'] === false)
    		return false;
        return $this->handler->delete($this->options['prefix'].$name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
    	if($this->options['enable_redis'] === false)
    		return false;
        return $this->handler->flushDB();
    }
    
    /**
     *    lpush 
     */
    public function lpush($name,$value){
    	if($this->options['enable_redis'] === false)
    		return false;
    	$name = $this->options['prefix'].$name;
        return $this->handler->lpush($name,$value);
    }
    /**  rPush 链表的结尾
	*  
	*/
	public function rpush($name,$value){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		return $this->handler->rpush($name,$value);
	}
    /**
     *    add lpop
     */
    public function lpop($name){
    	if($this->options['enable_redis'] === false)
    		return false;
    	$name = $this->options['prefix'].$name;
        return $this->handler->lpop($name);
    }
    /**
     * lrange 
     */
    public function lrange($name,$start,$end){
    	if($this->options['enable_redis'] === false)
    		return false;
    	$name = $this->options['prefix'].$name;
        return $this->handler->lrange($name,$start,$end);    
    }
     /**
     * ltrim 
     */
	 public function ltrim($name,$start,$end){
	 	if($this->options['enable_redis'] === false)
    		return false;
	 	$name = $this->options['prefix'].$name;
        return $this->handler->ltrim($name,$start,$end);    
    }
	/**
	*得到队列的长度
	*/
	public function llen($name){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		return $this->handler->llen($name);   
	} 
	/**
	*清空队列
	***/
	public function clearlist($name){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		$listlen = $this->handler->llen($name);
		if($listlen == 0){
			return false;
		}
		return $this->handler->ltrim($name,$listlen,0);
	}
	
	/************Set***************/
	/**
	 * Sadd
	*/
	public function sadd($name,$value){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		if($this->handler->sadd($name,$value) >= 0){
			return true;
		}
		return false;
	}
	/************SorteSet***************/
	/**
	 * zadd
	*/
	public function zadd($name, $score, $value){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		if($this->handler->zadd($name, $score, $value))
			return true;
		return false;
	}
	
	/**
	 * zrange 
	*/
	public function zrange($name, $start, $stop, $withscores = true){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
	 	return $withscores ? $this->handler->zrange($name, $start, $stop):$this->handler->zrange($name, $start,$stop,$withscores);
	}
	
	/**
	 * zcount
	*/
	public function zcount($name, $min, $max){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		return $this->handler->zcount($name, $min, $max);
	}
	
	/**
	 * zremrangebyscore
	*/
	public function zRemRangeByScore($name, $min, $max){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		return $this->handler->zRemRangeByScore($name, $min, $max);
	}
	
	/**
	 * zRemRangeByRank
	*/
	public function zRemRangeByRank($name, $min, $max){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		return $this->handler->zRemRangeByRank($name, $min, $max);
	}
	
	/**
	 * zRangeByScore
	*/
	public function zRangeByScore($name, $min, $max){
		if($this->options['enable_redis'] === false)
    		return false;
		$name = $this->options['prefix'].$name;
		return $this->handler->zRangeByScore($name, $min, $max);
	}
	
	
	/************HASH***************/
	 /**
     * set hash array
     * 写入hash
     * @access public
     * @param string $name hash_table_name
     * @param string $key hash_table_field
     * @param string $value hash_table_value
     * @return int
     */
    public function hset($name, $key, $value){
    	if($this->options['enable_redis'] === false)
    		return false;
    	$key = $this->options['prefix'].$key;
        if(is_array($value)){
            return $this->handler->hset($name,$key,serialize($value));    
        }
        return $this->handler->hset($name,$key,$value);
    }
  	/**
	 * hkeys
	 * 获取hash中全部的key
	 * @access public
	 * @param string $name hash_table_name
	 * @return array
	 * 
	*/
	public function hkeys($name){
		if($this->options['enable_redis'] === false)
    		return false;
		return $this->handler->hkeys($name);
	}
	/**
	 * hvals
	 * 获取hash中全部的value
	 * @access public
	 * @param string $name hash_table_name
	 * @return array
	 * 
	*/
	public function hvals($name){
		if($this->options['enable_redis'] === false)
    		return false;
		return $this->handler->hvals($name);
	}
	/**
	 * hmset
	 * 批量添加hash—field-value
	 * @access public
	 * @param string $name hash_table_name
	 * @param array  $value
	 * @return bool
	*/
	public function hmset($name, $value){
		if($this->options['enable_redis'] === false)
    		return false;
		if(is_array($value)){
			foreach($value as $key => $val){
				$name = $this->options['prefix'].$key;
				$value[$name] = $val;
			}
			if($this->handler->hmset($name, $value))
				return true;
		}
		return false;
	}
    /**
     * 
     * get hlen
     * 获取hash中的元素个数
     * @access public
     * @param string $name hash_table_name
     * @return int
	*/
	public function hlen($name){
		if($this->options['enable_redis'] === false)
    		return false;
		return $this->handler->hlen($name);	
	}
    /**
	 * get hash array
     * 可以取出二维数组
     * @access public
     * @param string $name hash_table_name
     * @param string $key hash_table_field
     * @param bool   $serialize 是否反序列化
     * @return array或者是hash全部的field+value
     */
    public function hget($name,$key = null,$serialize=false){
    	if($this->options['enable_redis'] === false)
    		return false;
    	if($key){
        	$key = $this->options['prefix'].$key;
            $row = $this->handler->hget($name,$key);
            if($row && $serialize){
                return unserialize($row);
            }
        }elseif($serialize && $key === null){
      	 	$allkeys = $this->handler->hkeys($name);
      	 	if(COUNT($allkeys) > 0)
      	 	{
	      	 	$array = array();
	      	 	foreach($allkeys as $key)
	      	 	{
	      	 		$array[$key] = unserialize($this->handler->hget($name,$key));
	      	 	}
	      	 	return $array;	
      	 	}
      	 	return false;
        }
        return $this->handler->hgetAll($name);
    }
    
   	/**
	 * hmget
	 * 批量获取hash—field-value
	 * @access public 
	 * @param string $name hash_table_name
	 * @param array $fields hash_table_fields
	 * @return array or nil
	*/
	public function hmget($name, $fields){
		if($this->options['enable_redis'] === false)
    		return false;
		if(is_array($fields)){
			foreach($fields as $key=>$val){
				$fields[$key] =  $this->options['prefix'].$val;
			}
			return $this->handler->hmget($name, $fields);
		}
		return false;
	}

    /**
     *  delete hash
     * 	删除hash_table
     * 	@access public
     *  @param string $name hash_table_name
     *  @param string $key  hash_table_field
     * 	@return int 删除的field或者name的数量
     */
    public function hdel($name,$key = null){
    	if($this->options['enable_redis'] === false)
    		return false;
    	if($key){
        	$key = $this->options['prefix'].$key;
            return $this->handler->hdel($name,$key);
        }
        return $this->handler->delete($name);
    }
    
    /**
     * hexists
     * 判断指定hash表中是否存在指定的field
     * @access public
     * @param string $name hash_table_name
	 * @param string $key  hash_table_field
	 * @return bool
     * 
	*/
	public function hexists($name,$key){
		if($this->options['enable_redis'] === false)
    		return false;
		$key = $this->options['prefix'].$key;
		if($this->handler->hexists($name,$key))
			return true;
		return false;
	}

}
