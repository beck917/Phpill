<?php 
/**
 * Session cache driver.
 *
 * Cache library config goes in the session.storage config entry:
 * $config['storage'] = array(
 *     'driver' => 'apc',
 *     'requests' => 10000
 * );
 * Lifetime does not need to be set as it is
 * overridden by the session expiration setting.
 *
 * $Id: Cache.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2009-2016 Phpill Team
 * @license    GNU General Public License v2.0
 */
namespace Phpill\Libraries\Drivers\Session;
class Cache implements \Phpill\Libraries\Drivers\Session {

	protected $cache;
	protected $encrypt;

	public function __construct()
	{
		// Load Encrypt library
		if (\Phpill::config('session.encryption'))
		{
			$this->encrypt = new Encrypt;
		}

		\Phpill::log('debug', 'Session Cache Driver Initialized');
	}

	public function open($path, $name)
	{
		$config = \Phpill::config('session.storage');

		if (empty($config))
		{
			// Load the default group
			$config = \Phpill::config('cache.default');
		}
		elseif (is_string($config))
		{
			$name = $config;

			// Test the config group name
			if (($config = \Phpill::config('cache.'.$config)) === NULL)
				throw new \Phpill_Exception('cache.undefined_group', $name);
		}

		$config['lifetime'] = (\Phpill::config('session.expiration') == 0) ? 86400 : \Phpill::config('session.expiration');
		$this->cache = new \Phpill\Libraries\Cache($config);

		return is_object($this->cache);
	}

	public function close()
	{
		return TRUE;
	}

	public function read($id)
	{
		$id = 'session_'.$id;
		if ($data = $this->cache->get($id))
		{
			return \Phpill::config('session.encryption') ? $this->encrypt->decode($data) : $data;
		}

		// Return value must be string, NOT a boolean
		return '';
	}

	public function write($id, $data)
	{
		$id = 'session_'.$id;
		$data = \Phpill::config('session.encryption') ? $this->encrypt->encode($data) : $data;

		return $this->cache->set($id, $data);
	}

	public function destroy($id)
	{
		$id = 'session_'.$id;
		return $this->cache->delete($id);
	}

	public function regenerate()
	{
		if(session_id() === '') session_regenerate_id(true);

		// Return new session id
		return session_id();
	}

	public function gc($maxlifetime)
	{
		// Just return, caches are automatically cleaned up
		return TRUE;
	}

} // End Session Cache Driver
