<?php 
/**
 * Phpill process control file, loaded by the front controller.
 * 
 * $Id: Bootstrap.php 3800 2008-12-17 20:37:06Z zombor $
 *
 * @package    Core
 * @author     Phpill Team
 * @copyright  (c) 2009-2016 Phpill Team
 * @license    GNU General Public License v2.0
 */

define('PHPILL_VERSION',  '1.0');
define('PHPILL_CODENAME', 'accipiter');

// Test of Phpill is running in Windows
define('PHPILL_IS_WIN', DIRECTORY_SEPARATOR === '\\');

// Load core files
require SYSPATH.'Core/Event'.EXT;
require SYSPATH.'Core/Phpill'.EXT;
require MODPATH.'Core/Libraries/vendor/autoload.php';

// Prepare the environment
Phpill::setup();

// Prepare the system
Event::run('system.ready');

// Determine routing
Event::run('system.routing');

// Make the magic happen!
Event::run('system.execute');

// Clean up and exit
Event::run('system.shutdown');