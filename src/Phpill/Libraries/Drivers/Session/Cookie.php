<?php 
/**
 * Session cookie driver.
 *
 * $Id: Cookie.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2009-2016 Phpill Team
 * @license    GNU General Public License v2.0
 */
namespace Phpill\Libraries\Drivers\Session;
class Cookie implements \Phpill\Libraries\Drivers\Session {

	protected $cookie_name;
	protected $encrypt; // Library

	public function __construct()
	{
		$this->cookie_name = \Phpill::config('session.name').'_data';

		if (\Phpill::config('session.encryption'))
		{
			$this->encrypt = Encrypt::instance();
		}

		\Phpill::log('debug', 'Session Cookie Driver Initialized');
	}

	public function open($path, $name)
	{
		return TRUE;
	}

	public function close()
	{
		return TRUE;
	}

	public function read($id)
	{
		$data = (string) \Phpill\Helpers\Cookie::get($this->cookie_name);

		if ($data == '')
			return $data;

		return empty($this->encrypt) ? base64_decode($data) : $this->encrypt->decode($data);
	}

	public function write($id, $data)
	{
		$data = empty($this->encrypt) ? base64_encode($data) : $this->encrypt->encode($data);

		if (strlen($data) > 4048)
		{
			\Phpill::log('error', 'Session ('.$id.') data exceeds the 4KB limit, ignoring write.');
			return FALSE;
		}

		return \Phpill\Helpers\Cookie::set($this->cookie_name, $data, \Phpill::config('session.expiration'));
	}

	public function destroy($id)
	{
		return \Phpill\Helpers\Cookie::delete($this->cookie_name);
	}

	public function regenerate()
	{
		if(session_id() === '') session_regenerate_id(true);

		// Return new id
		return session_id();
	}

	public function gc($maxlifetime)
	{
		return TRUE;
	}

} // End Session Cookie Driver Class