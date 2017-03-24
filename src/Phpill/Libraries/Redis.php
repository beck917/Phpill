<?php

/**
 * @author Beck Xu <beck917@gmail.com>
 * @date 2015-12-24
 * @copyright 足球魔方
 */

namespace Phpill\Libraries;

/**
 * Description of Redis
 *
 * @author Beck Xu <beck917@gmail.com>
 */
class Redis {

    public static $_instance = array();
    private $conn = null;
    public $code_type = self::CODE_TYPE_JSON;

    CONST CODE_TYPE_JSON = 1;
    CONST CODE_TYPE_NIL = 2;
    CONST CODE_TYPE_PHPSER = 3;
    CONST CODE_TYPE_MSGPACK = 4;
    CONST CODE_TYPE_JSON_OBJECT = 5;

    private $cur_timestamp;
    private $local_cache;

    public function __construct($config = FALSE) {
        $name = "";
        if (is_string($config)) {
            $name = $config;

            // Test the config group name
            if (($config = \Phpill::config('redis.' . $config)) === NULL)
                throw new \Phpill_Exception('cache.undefined_group', $name);
        }

        if (is_array($config)) {
            // Append the default configuration options
            $config += \Phpill::config('redis.default');
        } else {
            // Load the default group
            $config = \Phpill::config('redis.default');
        }

        // Cache the config in the object
        $this->config = $config;

        $this->conn = new \Redis();
        if (PHP_SAPI == 'cli') {
            $this->conn->connect($this->config['host'], $this->config['port'], 1);
        } else {
            $this->conn->pconnect($this->config['host'], $this->config['port'], 1);
        }

        if ($this->config['auth']) {
            $this->conn->auth($this->config['auth']);
        }

        $this->code_type = $this->config['serialize'];
        $this->cur_timestamp = time();

        $this->local_cache = LocalCache::instance("redis:" . $name);
    }

    /**
     * Returns a singleton instance of Redis.
     *
     * @param   array  configuration
     * @return  Redis
     */
    public static function instance($config = 'default') {
        if (!isset(Redis::$_instance[$config])) {
            // Create a new instance
            Redis::$_instance[$config] = new Redis($config);
        }

        return Redis::$_instance[$config];
    }

    public function encode($value) {
        switch ($this->code_type) {
            case self::CODE_TYPE_PHPSER:
                return serialize($value);
            case self::CODE_TYPE_NIL:
                return $value;
            case self::CODE_TYPE_JSON:
                return json_encode($value);
            case self::CODE_TYPE_JSON_OBJECT:
                return json_encode($value);
            case self::CODE_TYPE_MSGPACK:
                return msgpack_pack($value);
            default:
                return $value;
        }
    }

    public function decode($value) {
        switch ($this->code_type) {
            case self::CODE_TYPE_PHPSER:
                return unserialize($value);
            case self::CODE_TYPE_NIL:
                return $value;
            case self::CODE_TYPE_JSON:
                return json_decode($value, true);
            case self::CODE_TYPE_JSON_OBJECT:
                return json_decode($value);
            case self::CODE_TYPE_MSGPACK:
                return msgpack_unpack($value);
            default:
                return $value;
        }
    }

    public function exprie($key, $time) {
        $this->conn->expire($key, $time);
    }

    public function get($key) {
        if ($data = $this->conn->get($key)) {
            $data = $this->decode($data);
        }
        return $data;
    }

    public function delete($key) {
        $ret = $this->conn->delete($key);
        return $ret;
    }

    /**
     * array('nx', 'ex' => 30)
     * @param string $key
     * @param string $value
     * @param array $option
     */
    public function set($key, $value, $option = null) {
        $encode_value = $this->encode($value);
        return $this->conn->set($key, $encode_value, $option);
    }

    public function incr($key) {

        return $this->conn->incr($key);
    }

    public function mget($key) {
        return $this->conn->mget($key);
    }

    public function setEx($key, $seconds, $value) {
        $encode_value = $this->encode($value);

        $this->conn->setex($key, $seconds, $encode_value);
    }

    public function setNx($key, $value) {
        $encode_value = $this->encode($value);

        $this->conn->setnx($key, $encode_value);
    }

    public function setMyEx($key, $value, $expire, $expire_plus = 10) {
        if (empty($expire)) {
            return false;
        }
        $data = array($value, $this->cur_timestamp + $expire);

        //设置key自动过期，但时间大于伪过期,节省内存
        $ok = $this->conn->set($key, $this->encode($data), array('ex' => $expire + $expire_plus));

        if ($ok) {
            $this->unlock($key);
        }

        return $ok;
    }

    public function ttl($key) {
        return $this->conn->ttl($key);
    }

    public function getMyEx($key) {
        $data = $this->get($key);

        if ($data == false) {
            return false;
        }

        $exnx_flg = 0;
        //过期的话就更新
        if ($data[1] <= $this->cur_timestamp) {
            $ok = $this->lock($key);
            if ($ok) {
                $exnx_flg = 1;
            }
        }

        return array("data" => $data[0], "ex_flg" => $exnx_flg);
    }

    public function lock($key, $ex = 30) {
        $ok = $this->conn->set("$:lock" . $key, 1, array('nx', 'ex' => $ex));

        return $ok;
    }

    public function unlock($key) {
        return $this->delete("$:lock" . $key);
    }

    //================hash 操作函数==========================
    public function hExists($key, $field) {
        return $this->conn->hExists($key, $field);
    }

    public function hGetAll($key) {
        $data = $this->conn->hGetAll($key);
        $decoded_data = array();
        foreach ($data as $k => $v) {
            if ($data[$k]) {
                $decoded_data[$k] = $this->decode($v);
                //yield $decoded_data[$k];
            }
        }
        return $decoded_data;
    }

    public function hGet($key, $field) {
        if ($data = $this->conn->hGet($key, $field)) {
            $data = $this->decode($data);
            //$this->local_cache->set($key."^!".$field, $data);
        }
        return $data;
    }

    public function hDel($key, $field) {
        return $this->conn->hDel($key, $field);
    }

    public function hmGet($key, $fields) {
        $data = $this->conn->hMGet($key, $fields);
        $decoded_data = array();

        if ($data === false) {
            return $decoded_data;
        }

        foreach ($data as $k => $v) {
            if ($data[$k]) {
                $decoded_data[$k] = $this->decode($v);
                //yield $decoded_data[$k];
                //$this->local_cache->set($key."^!".$k, $decoded_data[$k]);
            }
        }

        return $decoded_data;
    }

    public function hmSet($key, $fields) {
        $datas = array();
        foreach ($fields as $keys => $data) {
            $data = $this->encode($data);

            $datas[$keys] = $data;
        }
        return $this->conn->hMSet($key, $datas);
    }

    public function hSet($key, $field, $value) {
        $encode_value = $this->encode($value);
        return $this->conn->hSet($key, $field, $encode_value);
    }

    public function hIncrBy($key, $field, $integer) {
        return $this->conn->hIncrBy($key, $field, $integer);
    }

    //================hash 操作函数==========================
    //================事务==================================
    public function multi() {
        return $this->conn->multi();
    }
    
    public function exec() {
        return $this->conn->exec();
    }
    
    public function discard() {
        return $this->conn->discard();
    }
    //================事务==================================
    //================有序集合===============================
    public function zAdd($key, $score, $value) {
        return $this->conn->zAdd($key, $score, $value);
    }

    public function zInter($key, $score, $value, $func) {
        return $this->conn->zAdd($key, $score, $value, $func);
    }

    public function zRemRangeByScore($key, $min, $max) {
        return $this->conn->zRemRangeByScore($key, $min, $max);
    }

    public function zCard($key) {
        return $this->conn->zCard($key);
    }

    public function zRange($prefix, $min, $max, $withScore = true) {

        return $this->conn->zRange($prefix, $min, $max, $withScore);
    }

    //================有序集合===============================
    /**
      public function send($command_name, ...$params)
      {
      $this->conn->script($command_name, ...$params);
      }
     */
    //================列表===============================
    public function lPush($key, $value) {
        $value = $this->encode($value);
        return $this->conn->lPush($key, $value);
    }

    //===============集合===================
    public function sAdd($key, $value) {
        return $this->conn->sAdd($key, $value);
    }

    public function sdiff($key1, $key2) {
        return $this->conn->sdiff($key1, $key2);
    }

    public function smembers($key1) {
        return $this->conn->smembers($key1);
    }

    public function pipeline() {
        return $this->conn->pipeline();
    }
    
	public function __call($name, $args)
	{
		return $this->conn->rawCommand($name, ...$args);
	}
}
