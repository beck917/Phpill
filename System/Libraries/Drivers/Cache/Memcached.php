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
namespace System\Libraries\Drivers\Cache;
class Memcached extends \System\Libraries\Drivers\Cache {

	// Cache backend object and flags
	protected $backend;
	protected $flags;

	public function __construct($tmp, $config)
	{
		if ( ! extension_loaded('memcached'))
			throw new \Phpill_Exception('cache.extension_not_loaded', 'memcached');

		$this->backend = new \Memcached();
		$this->flags =  0;
		
		//$this->backend->setOption(Memcached::OPT_COMPRESSION, false);
		//$this->backend->setOption(Memcached::OPT_BINARY_PROTOCOL, true);//有些版本有bug

		// Add the server to the pool
		$this->backend->addServer($config['host'], $config['port'])
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
	
	public function getCas($id, &$token)
	{
		$null = null;
		return (($return = $this->backend->get($id, $null, $token)) === FALSE) ? NULL : $return;
	}
	
	public function getMulti($keys)
	{
		// Change slashes to colons
		$null = null;
		return (($return = $this->backend->getMulti($keys, $null, Memcached::GET_PRESERVE_ORDER)) === FALSE) ? NULL : $return;
	}
	
	public function cas($cas, $id, $data, $tags, $lifetime)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		// Memcache driver expects unix timestamp
		if ($lifetime !== 0)
		{
			$lifetime += time();
		}

		return $this->backend->cas($cas, $id, $data, $lifetime);
	}
	
	public function add($id, $data, $tags, $lifetime)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		// Memcache driver expects unix timestamp
		if ($lifetime !== 0)
		{
			$lifetime += time();
		}

		return $this->backend->add($id, $data, $lifetime);
	}

	public function set($id, $data, $tags, $lifetime)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		// Memcache driver expects unix timestamp
		if ($lifetime !== 0)
		{
			$lifetime += time();
		}

		return $this->backend->set($id, $data, $lifetime);
	}
	
	public function setMulti($data, $tags, $lifetime)
	{
		count($tags) and Phpill::log('error', 'Cache: Tags are unsupported by the memcache driver');

		// Memcache driver expects unix timestamp
		if ($lifetime !== 0)
		{
			$lifetime += time();
		}

		return $this->backend->setMulti($data, $lifetime);
	}

	public function delete($id, $tag = FALSE)
	{
		if ($id === TRUE)
			return $this->backend->flush();

		if ($tag == FALSE)
			return $this->backend->delete($id);

		return TRUE;
	}

	public function delete_expired()
	{
		return TRUE;
	}
	
	public function getResultCode()
	{
		return $this->backend->getResultCode();
	}

} // End Cache Memcache Driver