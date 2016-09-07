<?php 
/**
 * Xcache Cache driver.
 *
 * $Id: Xcache.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Cache
 * @author     Phpill Team
 * @copyright  (c) 2007-2008 Phpill Team
 * @license    http://phpillphp.com/license.html
 */
namespace Phpill\Libraries\Drivers\Cache;
class Xcache extends \Phpill\Libraries\Drivers\Cache {

	public function __construct()
	{
		if ( ! extension_loaded('xcache'))
			throw new \Phpill_Exception('cache.extension_not_loaded', 'xcache');
	}

	public function get($id)
	{
		if (xcache_isset($id))
			return xcache_get($id);

		return NULL;
	}

	public function set($id, $data, $tags, $lifetime)
	{
		count($tags) and Phpill::log('error', 'Cache: tags are unsupported by the Xcache driver');

		return xcache_set($id, $data, $lifetime);
	}

	public function find($tag)
	{
		Phpill::log('error', 'Cache: tags are unsupported by the Xcache driver');
		return FALSE;
	}

	public function delete($id, $tag = FALSE)
	{
		if ($tag !== FALSE)
		{
			Phpill::log('error', 'Cache: tags are unsupported by the Xcache driver');
			return TRUE;
		}
		elseif ($id !== TRUE)
		{
			if (xcache_isset($id))
				return xcache_unset($id);

			return FALSE;
		}
		else
		{
			// Do the login
			$this->auth();
			$result = TRUE;
			for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
			{
				if (xcache_clear_cache(XC_TYPE_VAR, $i) !== NULL)
				{
					$result = FALSE;
					break;
				}
			}

			// Undo the login
			$this->auth(TRUE);
			return $result;
		}

		return TRUE;
	}

	public function delete_expired()
	{
		return TRUE;
	}

	private function auth($reverse = FALSE)
	{
		static $backup = array();

		$keys = array('PHP_AUTH_USER', 'PHP_AUTH_PW');

		foreach ($keys as $key)
		{
			if ($reverse)
			{
				if (isset($backup[$key]))
				{
					$_SERVER[$key] = $backup[$key];
					unset($backup[$key]);
				}
				else
				{
					unset($_SERVER[$key]);
				}
			}
			else
			{
				$value = getenv($key);

				if ( ! empty($value))
				{
					$backup[$key] = $value;
				}

				$_SERVER[$key] = Phpill::config('cache_xcache.'.$key);
			}
		}
	}

} // End Cache Xcache Driver
