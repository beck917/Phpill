<?php
/**
 * Define the website environment status. When this flag is set to TRUE, some
 * module demonstration controllers will result in 404 errors. For more information
 * about this option, read the documentation about deploying Phpill.
 *
 * @see http://docs.phpillphp.com/installation/deployment
 */
define('IN_PRODUCTION', FALSE);

/**
 * Website application directory. This directory should contain your application
 * configuration, controllers, models, views, and other resources.
 *
 * This path can be absolute or relative to this file.
 */
$phpill_application = '../Application';

/**
 * Phpill modules directory. This directory should contain all the modules used
 * by your application. Modules are enabled and disabled by the application
 * configuration file.
 *
 * This path can be absolute or relative to this file.
 */
$phpill_modules = '../Modules';

/**
 * Phpill system directory. This directory should contain the core/ directory,
 * and the resources you included in your download of Phpill.
 *
 * This path can be absolute or relative to this file.
 */
$phpill_system = '../System';

$phpill_server = '../Servers';
$phpill_root = '../';

/**
 * Test to make sure that Phpill is running on PHP 5.2 or newer. Once you are
 * sure that your environment is compatible with Phpill, you can comment this
 * line out. When running an application on a new server, uncomment this line
 * to check the PHP version quickly.
 */
version_compare(PHP_VERSION, '5.3', '<') and exit('Phpill requires PHP 5.3 or newer.');

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Turning off display_errors will effectively disable Phpill error display
 * and logging. You can turn off Phpill errors in application/config/config.php
 */
ini_set('display_errors', TRUE);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file has a
 * different extension.
 */
define('EXT', '.php');

//
// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------
// $Id: index.php 3701 2008-11-22 20:42:18Z Geert $
//

// Define the front controller name and docroot
define('DOCROOT', getcwd().DIRECTORY_SEPARATOR);
define('PHPILL',  basename(__FILE__));

// If the front controller is a symlink, change to the real docroot
is_link(PHPILL) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($phpill_application)).'/');
define('MODPATH', str_replace('\\', '/', realpath($phpill_modules)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($phpill_system)).'/');
define('SERPATH', str_replace('\\', '/', realpath($phpill_server)).'/');
define('ROOTPATH', str_replace('\\', '/', realpath($phpill_root)).'/');

// Clean up
unset($phpill_application, $phpill_modules, $phpill_system, $phpill_server);

// Initialize Phpill
require SYSPATH.'Core/Bootstrap'.EXT;
