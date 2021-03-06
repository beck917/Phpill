<?php
/**
 * Cookie helper class.
 *
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2009-2016 Phpill Team
 * @license    GNU General Public License v2.0
 */
namespace Phpill\Helpers;
class Cookie {

	/**
	 * Sets a cookie with the given parameters.
	 *
	 * @param   string   cookie name or array of config options
	 * @param   string   cookie value
	 * @param   integer  number of seconds before the cookie expires
	 * @param   string   URL path to allow
	 * @param   string   URL domain to allow
	 * @param   boolean  HTTPS only
	 * @param   boolean  HTTP only (requires PHP 5.2 or higher)
	 * @return  boolean
	 */
	public static function set($name, $value = NULL, $expire = NULL, $path = NULL, $domain = NULL, $secure = NULL, $httponly = NULL)
	{
		if (headers_sent())
			return FALSE;

		// If the name param is an array, we import it
		is_array($name) and extract($name, EXTR_OVERWRITE);

		// Fetch default options
		$config = \Phpill::config('cookie');

		foreach (array('value', 'expire', 'domain', 'path', 'secure', 'httponly') as $item)
		{
			if ($$item === NULL AND isset($config[$item]))
			{
				$$item = $config[$item];
			}
		}

		// Expiration timestamp
		$expire = ($expire == 0) ? 0 : time() + (int) $expire;

		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Fetch a cookie value, using the Input library.
	 *
	 * @param   string   cookie name
	 * @param   mixed    default value
	 * @param   boolean  use XSS cleaning on the value
	 * @return  string
	 */
	public static function get($name, $default = NULL, $xss_clean = FALSE)
	{
		return \Phpill\Libraries\Input::instance()->cookie($name, $default, $xss_clean);
	}

	/**
	 * Nullify and unset a cookie.
	 *
	 * @param   string   cookie name
	 * @param   string   URL path
	 * @param   string   URL domain
	 * @return  boolean
	 */
	public static function delete($name, $path = NULL, $domain = NULL)
	{
		if ( ! isset($_COOKIE[$name]))
			return FALSE;

		// Delete the cookie from globals
		unset($_COOKIE[$name]);

		// Sets the cookie value to an empty string, and the expiration to 24 hours ago
		return Cookie::set($name, '', -86400, $path, $domain, FALSE, FALSE);
	}

} // End cookie