<?php
/**
 * Memcache-based Cache driver.
 *
 * $Id: Memcache.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Cache
 * @author     Phpill Team
 * @copyright  (c) 2007-2008 Phpill Team
 * @license    http://phpillphp.com/license.html
 */
namespace Phpill\Libraries\Drivers\Cache;
class Redis extends \Phpill\Libraries\Drivers\Cache {

	// Cache backend object and flags
	public $backend;
	protected $flags;

	public function __construct($tmp, $config)
	{
		//if ( ! extension_loaded('redis'))
		//	throw new Phpill_Exception('cache.extension_not_loaded', 'redis');
		
		if(PATH_SEPARATOR == ':'){
			$this->backend = new \Redis();
		} else {
			//$this->backend = new RedisServer();
		}
		$this->flags =  0;

		// Add the server to the pool
		$this->backend->connect($config['host'], $config['port'])
			or Phpill::log('error', 'Cache: Connection failed: '.$config['host']);
	}

	public function find($tag)
	{
		return FALSE;
	}

	public function get($id)
	{
		return (($return = $this->backend->get($id)) === FALSE) ? NULL : $return;
	}
	
	public function getMulti($keys)
	{
		throw new Phpill_Exception('core.driver_implements', 'redis_cache', get_class($this));
		return;
	}
	
	public function add($id, $data, $tags, $lifetime = 86400)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		return $this->backend->SetNX($id, $data);
	}  

	public function set($id, $data, $tags, $lifetime = 0)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');
		if (empty($lifetime)) {
			return $this->backend->set($id, $data);
		} else {
			return $this->backend->setEx($id, $lifetime, $data);
		}
	}
	
	public function setMulti($data, $tags, $lifetime)
	{
		throw new Phpill_Exception('core.driver_implements', 'redis_cache', get_class($this));
		return;
	}
	
	public function hMGet($key, $fields)
	{
		return $this->backend->hMGet($key, $fields);
	}
	
	public function hGet($key, $field)
	{
		return $this->backend->hGet($key, $field);
	}
	
	public function hSet($key, $field, $value, $tags = NULL)
	{
		return $this->backend->hSet($key, $field, $value);
	}
	
	public function hMSet($key, $fields, $tags, $lifetime)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		// Memcache driver expects unix timestamp
		if ($lifetime !== 0)
		{
			$lifetime += time();
		}

		return $this->backend->hMSet($key, $fields);
	}
    
    public function hSetNx($key, $field, $value, $tags, $lifetime)
    {
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		// Memcache driver expects unix timestamp
		if ($lifetime !== 0)
		{
			$lifetime += time();
		}

		return $this->backend->hSetNx($key, $field, $value);
    }
    
    public function hGetAll($hash_key)
    {
        return $this->backend->hGetAll($hash_key);
    }
	
	public function hDel($key, $field)
	{
		return $this->backend->hDel($key, $field);
	}

	public function delete($id, $tag = FALSE)
	{
		if ($tag == FALSE)
			return $this->backend->Del($id);

		return TRUE;
	}

	public function delete_expired()
	{
		return TRUE;
	}
	
	/**
	 *
	 * @return  int
	 */
	public function rPush($key, $value)
	{
		return $this->backend->rPush($key, $value);
	}
	
	/**
	 *
	 * @return 
	 */
	public function hIncrBy($key, $field, $num = 1)
	{
		return $this->backend->hIncrBy($key, $field, $num);
	}
	
	public function lPop($key)
	{
		return $this->backend->lPop($key);
	}
	
	public function multi($flg = RedisServer::MULTI)
	{
		return $this->backend->multi($flg);
	}
	
	public function exec()
	{
		return $this->backend->exec();
	}
	
	public function getResultCode()
	{
		return $this->backend->getResultCode();
	}

} // End Cache Redis Driver