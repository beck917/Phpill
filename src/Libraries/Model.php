<?php 
/**
 * Model base class.
 *
 * $Id: Model.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2007-2008 Phpill Team
 * @license    http://phpillphp.com/license.html
 */
namespace Phpill\Libraries;
class Model {
	/**
	 * @var Database
	 */
	protected $db;

	/**
	 * Loads the database instance, if the database is not already loaded.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		if ( ! is_object($this->db))
		{
			// Load the default database
			$this->db = \Phpill\Libraries\Database::instance('default');
		}
	}

} // End Model