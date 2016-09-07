<?php 
/**
 * Cache driver interface.
 *
 * $Id: Cache.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Cache
 * @author     Phpill Team
 * @copyright  (c) 2007-2008 Phpill Team
 * @license    http://phpillphp.com/license.html
 */
namespace Phpill\Libraries\Drivers;
abstract Class Cache {

	/**
	 * Set a cache item.
	 */
	public function set($id, $data, $tags, $lifetime){}

	/**
	 * Find all of the cache ids for a given tag.
	 */
	public function find($tag){}

	/**
	 * Get a cache item.
	 * Return NULL if the cache item is not found.
	 */
	public function get($id){}
	
	public function add($id, $data, $tags, $lifetime){}
	
	public function getMulti($keys){}
	public function setMulti($data, $tags, $lifetime){}
	public function rPush($key, $value){}
	public function lPop($key){}
	public function multi($flg = RedisServer::MULTI){}
	public function exec(){}
	public function hMGet($key, $fields){}
	public function hMSet($key, $fields, $tags, $lifetime){}
    public function hGetAll($key){}
    public function hSetNx($key, $field, $value, $tags, $lifetime){}
    public function hDel($key, $field){}
	public function hIncrBy($key, $field, $num){}
	/**
	 * Delete cache items by id or tag.
	 */
	public function delete($id, $tag = FALSE){}

	/**
	 * Deletes all expired cache items.
	 */
	public function delete_expired(){}
	
	public function getCas($id, &$token)
	{
		return $this->get($id);
	}
	
	public function cas($cas, $id, $data, $tags, $lifetime)
	{
		return $this->set($id, $data, $tags, $lifetime);
	}
	
	public function getResultCode()
	{
		return 0;
	}

} // End Cache Driver