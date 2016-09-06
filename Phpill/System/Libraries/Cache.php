<?php 
/**
 * Provides a driver-based interface for finding, creating, and deleting cached
 * resources. Caches are identified by a unique string. Tagging of caches is
 * also supported, and caches can be found and deleted by id or tag.
 *
 * $Id: Cache.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Cache
 * @author     Phpill Team
 * @copyright  (c) 2007-2008 Phpill Team
 * @license    http://phpillphp.com/license.html
 */
namespace System\Libraries;
class Cache {

	// For garbage collection
	protected static $loaded;

	// Configuration
	protected $config;

	// Driver object
	public $driver;
	
	public static $instances = array();
	
	CONST RES_SUCCESS = 0;
	CONST RES_FAILURE = 1;
	CONST RES_HOST_LOOKUP_FAILURE = 2;
	CONST RES_UNKNOWN_READ_FAILURE = 7;
	CONST RES_PROTOCOL_ERROR = 8;
	CONST RES_CLIENT_ERROR = 9;
	CONST RES_SERVER_ERROR = 10;
	CONST RES_WRITE_FAILURE = 5;
	CONST RES_DATA_EXISTS = 12;
	CONST RES_NOTSTORED = 14;
	CONST RES_NOTFOUND = 16;
	CONST RES_PARTIAL_READ = 18;
	CONST RES_SOME_ERRORS = 19;
	CONST RES_NO_SERVERS = 20;
	CONST RES_END = 21;
	CONST RES_ERRNO = 25;
	CONST RES_BUFFERED = 31;
	CONST RES_TIMEOUT = 30;
	CONST RES_BAD_KEY_PROVIDED = 32;
	CONST RES_CONNECTION_SOCKET_CREATE_FAILURE = 11;
	CONST RES_PAYLOAD_FAILURE = -1001;

	/**
	 * Returns a singleton instance of Cache.
	 *
	 * @param   array  configuration
	 * @return  Cache
	 */
	public static function instance($config = 'default')
	{
		if ( ! isset(Cache::$instances[$config]))
		{
			// Create a new instance
			Cache::$instances[$config] = new Cache($config);
		}

		return Cache::$instances[$config];
	}

	/**
	 * Loads the configured driver and validates it.
	 *
	 * @param   array|string  custom configuration or config group name
	 * @return  void
	 */
	public function __construct($config = FALSE)
	{
		if (is_string($config))
		{
			$name = $config;

			// Test the config group name
			if (($config = \Phpill::config('cache.'.$config)) === NULL)
				throw new \Phpill_Exception('cache.undefined_group', $name);
		}

		if (is_array($config))
		{
			// Append the default configuration options
			$config += \Phpill::config('cache.default');
		}
		else
		{
			// Load the default group
			$config = \Phpill::config('cache.default');
		}

		// Cache the config in the object
		$this->config = $config;

		// Set driver name
		$driver = "System\\Libraries\\Drivers\\Cache\\".ucfirst($this->config['driver']);

		// Load the driver
		if ( ! \Phpill::auto_load($driver))
			throw new \Phpill_Exception('core.driver_not_found', $this->config['driver'], get_class($this));

		// Initialize the driver
		$this->driver = new $driver($this->config['params'], $this->config);

		// Validate the driver
		if ( ! ($this->driver instanceof Drivers\Cache))
			throw new \Phpill_Exception('core.driver_implements', $this->config['driver'], get_class($this), 'Cache_Driver');

		\Phpill::log('debug', 'Cache Library initialized');

		if (self::$loaded !== TRUE)
		{
			$this->config['requests'] = (int) $this->config['requests'];

			if ($this->config['requests'] > 0 AND mt_rand(1, $this->config['requests']) === 1)
			{
				// Do garbage collection
				$this->driver->delete_expired();

				\Phpill::log('debug', 'Cache: Expired caches deleted.');
			}

			// Cache has been loaded once
			self::$loaded = TRUE;
		}
	}

	/**
	 * Fetches a cache by id. Non-string cache items are automatically
	 * \Application\Helpers\Common::unpackd before the cache is returned. NULL is returned when
	 * a cache item is not found.
	 *
	 * @param   string  cache id
	 * @return  mixed   cached data or NULL
	 */
	public function get($id)
	{
		// Change slashes to colons
		$id = str_replace(array('/', '\\'), '=', $id);

		if ($data = $this->driver->get($id))
		{
			$data = \Application\Helpers\Common::unpack($data);
		}

		return $data;
	}
	
	public function getCas($id, &$token)
	{
		// Change slashes to colons
		$id = str_replace(array('/', '\\'), '=', $id);

		if ($data = $this->driver->getCas($id, $token))
		{
			$data = \Application\Helpers\Common::unpack($data);
		}

		return $data;
	}
	
	public function getMulti($keys)
	{
		if ($this->config['driver'] == 'memcached') {
			if ($keys === array()) {
				return array();
			}
			$datas =  $this->driver->getMulti($keys);
			foreach ($datas as $key => $vl) {
				if ($datas[$key])
				{
					//XXX change 2011/12/125 By Beck
					$datas[$key] = \Application\Helpers\Common::unpack($datas[$key]);
				}
			}
			
			return $datas;
		}
		
		// Change slashes to colons
		$data = array();
		foreach ($keys as $key) {
			$data[$key] = $this->get($key);
		}
		
		return $data;
	}
	
	/**
	 * 
	 * @param type $key
	 * @param type $fields
	 * @return type
	 */
	public function hMGet($key, $fields)
	{
		if ($fields === array()) {
			return array();
		}
		$datas =  $this->driver->hmGet($key, $fields);
		foreach ($datas as $k => $v) {
			if ($datas[$k])
			{
				$datas[$k] = \Application\Helpers\Common::unpack($v);
			}
		}

		return $datas;
	}
    
	public function hGetAll($key)
	{
		$datas =  $this->driver->hGetAll($key);
		foreach ($datas as $k => $v) {
			if ($datas[$k])
			{
				$datas[$k] = \Application\Helpers\Common::unpack($v);
			}
		}

		return $datas;
	}
	
	/**
	 * 
	 * @param type $key
	 * @param type $fields
	 * @param type $tags
	 * @param type $lifetime
	 * @return type
	 * @throws \Phpill_Exception
	 */
	public function hMSet($id, $fields, $tags = NULL, $lifetime = NULL)
	{
		$datas = array();
		foreach ($fields as $key => $data) {
			if (is_resource($data))
				throw new \Phpill_Exception('cache.resources');

			// Change slashes to colons
			$id = str_replace(array('/', '\\'), '=', $id);

			//XXX change 2011/12/125 By Beck
			$data = \Application\Helpers\Common::pack($data);

			// Make sure that tags is an array
			$tags = empty($tags) ? array() : (array) $tags;

			if ($lifetime === NULL)
			{
				// Get the default lifetime
				$lifetime = $this->config['lifetime'];
			}

			$datas[$key] = $data;
		}
		return $this->driver->hMSet($id, $datas, $tags, $lifetime);
	}

	/**
	 * Fetches all of the caches for a given tag. An empty array will be
	 * returned when no matching caches are found.
	 *
	 * @param   string  cache tag
	 * @return  array   all cache items matching the tag
	 */
	public function find($tag)
	{
		if ($ids = $this->driver->find($tag))
		{
			$data = array();
			foreach ($ids as $id)
			{
				// Load each cache item and add it to the array
				if (($cache = $this->get($id)) !== NULL)
				{
					$data[$id] = $cache;
				}
			}

			return $data;
		}

		return array();
	}
	
	public function setMulti($datas, $tags = NULL, $lifetime = NULL)
	{
		if ($this->config['driver'] == 'memcached') {
			foreach ($datas as $key => $data) {
				if (is_resource($data))
					throw new \Phpill_Exception('cache.resources');
		
				// Change slashes to colons
				$key = str_replace(array('/', '\\'), '=', $key);
				
				//XXX change 2011/12/125 By Beck
				$data = \Application\Helpers\Common::pack($data);
		
				// Make sure that tags is an array
				$tags = empty($tags) ? array() : (array) $tags;
		
				if ($lifetime === NULL)
				{
					// Get the default lifetime
					$lifetime = $this->config['lifetime'];
				}
				
				$datas[$key] = $data;
			}
			return $this->driver->setMulti($datas, $tags, $lifetime);
		}
		
		foreach ($datas as $key => $vl) {
			//var_dump($key, $vl);
			$this->set($key, $vl);
		}
		
		return true;
	}
	
	/**
	 * Set a cache item by id. Tags may also be added and a custom lifetime
	 * can be set. Non-string data is automatically serialized.
	 *
	 * @param   string   unique cache id
	 * @param   mixed    data to cache
	 * @param   array    tags for this item
	 * @param   integer  number of seconds until the cache expires
	 * @return  boolean
	 */
	public function cas($cas, $id, $data, $tags = NULL, $lifetime = NULL)
	{
		if (is_resource($data))
			throw new \Phpill_Exception('cache.resources');

		// Change slashes to colons
		$id = str_replace(array('/', '\\'), '=', $id);

		//XXX change 2011/12/125 By Beck
		$data = \Application\Helpers\Common::pack($data);

		// Make sure that tags is an array
		$tags = empty($tags) ? array() : (array) $tags;

		if ($lifetime === NULL)
		{
			// Get the default lifetime
			$lifetime = $this->config['lifetime'];
		}

		return $this->driver->cas($cas, $id, $data, $tags, $lifetime);
	}
	
	public function hGet($key, $field)
	{
		// Change slashes to colons
		$key = str_replace(array('/', '\\'), '=', $key);

		if ($data = $this->driver->hGet($key, $field))
		{
			$data = \Application\Helpers\Common::unpack($data);
		}

		return $data;
	}
   
	public function hSet($key, $field, $value, $tags = NULL)
	{
		if (is_resource($value))
			throw new \Phpill_Exception('cache.resources');

		// Change slashes to colons
		$key = str_replace(array('/', '\\'), '=', $key);

		//XXX change 2011/12/125 By Beck
		$value = \Application\Helpers\Common::pack($value);

		// Make sure that tags is an array
		$tags = empty($tags) ? array() : (array) $tags;

		return $this->driver->hSet($key, $field, $value, $tags);
	}
   
	public function hSetNx($key, $field, $value, $tags = NULL, $lifetime = NULL)
	{
		if (is_resource($value))
			throw new \Phpill_Exception('cache.resources');

		// Change slashes to colons
		$key = str_replace(array('/', '\\'), '=', $key);

		//XXX change 2011/12/125 By Beck
		$value = \Application\Helpers\Common::pack($value);

		// Make sure that tags is an array
		$tags = empty($tags) ? array() : (array) $tags;

		if ($lifetime === NULL)
		{
			// Get the default lifetime
			$lifetime = $this->config['lifetime'];
		}

		return $this->driver->hSetNx($key, $field, $value, $tags, $lifetime);
	}
	
	public function add($id, $data, $tags = NULL, $lifetime = NULL)
	{
		if (is_resource($data))
			throw new \Phpill_Exception('cache.resources');

		// Change slashes to colons
		$id = str_replace(array('/', '\\'), '=', $id);

		//XXX change 2011/12/125 By Beck
		$data = \Application\Helpers\Common::pack($data);

		// Make sure that tags is an array
		$tags = empty($tags) ? array() : (array) $tags;

		if ($lifetime === NULL)
		{
			// Get the default lifetime
			$lifetime = $this->config['lifetime'];
		}

		return $this->driver->add($id, $data, $tags, $lifetime);
	}

	/**
	 * Set a cache item by id. Tags may also be added and a custom lifetime
	 * can be set. Non-string data is automatically serialized.
	 *
	 * @param   string   unique cache id
	 * @param   mixed    data to cache
	 * @param   array    tags for this item
	 * @param   integer  number of seconds until the cache expires
	 * @return  boolean
	 */
	public function set($id, $data, $tags = NULL, $lifetime = NULL)
	{
		if (is_resource($data))
			throw new \Phpill_Exception('cache.resources');

		// Change slashes to colons
		$id = str_replace(array('/', '\\'), '=', $id);

		//XXX change 2011/12/125 By Beck
		$data = \Application\Helpers\Common::pack($data);

		// Make sure that tags is an array
		$tags = empty($tags) ? array() : (array) $tags;

		if ($lifetime === NULL)
		{
			// Get the default lifetime
			$lifetime = $this->config['lifetime'];
		}

		return $this->driver->set($id, $data, $tags, $lifetime);
	}
	
	public function hIncrBy($key, $field, $num = 1)
	{
		return $this->driver->hIncrBy($key, $field, $num);
	}
	
	public function hDel($key, $field)
	{
		return $this->driver->hDel($key, $field);
	}

	/**
	 * Delete a cache item by id.
	 *
	 * @param   string   cache id
	 * @return  boolean
	 */
	public function delete($id)
	{
		// Change slashes to colons
		$id = str_replace(array('/', '\\'), '=', $id);

		return $this->driver->delete($id);
	}

	/**
	 * Delete all cache items with a given tag.
	 *
	 * @param   string   cache tag name
	 * @return  boolean
	 */
	public function delete_tag($tag)
	{
		return $this->driver->delete(FALSE, $tag);
	}

	/**
	 * Delete ALL cache items items.
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return $this->driver->delete(TRUE);
	}
	
	public function getResultCode()
	{
		return $this->driver->getResultCode();
	}
	
	/**
	 *
	 * @return  int
	 */
	public function rPush($key, $value)
	{
		return $this->driver->rPush($key, $value);
	}
	
	/**
	 *
	 * @return 
	 */
	public function lPop($key)
	{
		return $this->driver->lPop($key);
	}
	
	public function multi($flg = RedisServer::MULTI)
	{
		return $this->driver->multi($flg);
	}
	
	public function exec()
	{
		return $this->driver->exec();
	}
	

} // End Cache
