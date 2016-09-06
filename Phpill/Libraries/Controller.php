<?php
/**
 * Phpill Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2015-2016 Phpill Team
 * @license    http://phpill.com/license.html
 */
namespace Phpill\Libraries;
abstract class Controller {

	// Allow all controllers to run in production by default
	const ALLOW_PRODUCTION = TRUE;
	
	/**
	 *
	 * @var Input 
	 */
	protected $input;
	
	/**
	 *
	 * @var URI 
	 */
	protected $uri;

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		if (\Phpill::$instance == NULL)
		{
			// Set the instance to the first controller loaded
			\Phpill::$instance = $this;
		}

		// URI should always be available
		$this->uri = \Phpill\Libraries\URI::instance();

		// Input should always be available
		$this->input = \Phpill\Libraries\Input::instance();
	}

	/**
	 * Handles methods that do not exist.
	 *
	 * @param   string  method name
	 * @param   array   arguments
	 * @return  void
	 */
	public function __call($method, $args)
	{
		// Default to showing a 404 page
		\Event::run('system.404');
	}

	/**
	 * Includes a View within the controller scope.
	 *
	 * @param   string  view filename
	 * @param   array   array of view variables
	 * @return  string
	 */
	public function _phpill_load_view($phpill_view_filename, $phpill_input_data)
	{
		if ($phpill_view_filename == '')
			return;

		// Buffering on
		ob_start();

		// Import the view variables to local namespace
		extract($phpill_input_data, EXTR_SKIP);

		try
		{
			// Views are straight HTML pages with embedded PHP, so importing them
			// this way insures that $this can be accessed as if the user was in
			// the controller, which gives the easiest access to libraries in views
			include $phpill_view_filename;
		}
		catch (Exception $e)
		{
			// Display the exception using its internal __toString method
			echo $e;
		}

		// Fetch the output and close the buffer
		return ob_get_clean();
	}

} // End Controller Class