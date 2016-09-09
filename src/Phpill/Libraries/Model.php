<?php 
/**
 * Model base class.
 *
 * $Id: Model.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2009-2016 Phpill Team
 * @license    GNU General Public License v2.0
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