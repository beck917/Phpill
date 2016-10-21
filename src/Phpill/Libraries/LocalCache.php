<?php
/**
 * LocalCache
 *
 */
namespace Phpill\Libraries;
class LocalCache
{
    private $_cache = array();
	private static $instance;
	
	/**
	 * 防止命名冲突
	 * @var string
	 */
	private $namespace = '';

    public function __construct($namespace = '')
    {
		$this->namespace = $namespace;
    }
	
    /**
     * 
     * @param type $namespace
     * @return LocalCache
     */
	public static function instance($namespace = '')
	{
		if (self::$instance == NULL)
		{
			// Create a new instance
			self::$instance = new LocalCache($namespace);
		}

		return self::$instance;
	}

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->_cache[$this->namespace.$key]) ? $this->_cache[$this->namespace.$key] : null;
    }

    /**
     * @param $key
     * @param $var
     * @return bool
     */
    public function set($key, $var)
    {
        $this->_cache[$this->namespace.$key] = $var;
        return true;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        unset($this->_cache[$this->namespace.$key]);
        return true;
    }

    /**
     * @return bool
     */
    public function flush()
    {
        $this->_cache = array();
        return true;
    }
}