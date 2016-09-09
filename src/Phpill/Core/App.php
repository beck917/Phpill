<?php

/**
 * @author Beck Xu <beck917@gmail.com>
 * @date 2016-09-07
 * @copyright (c) 2009-2016 Phpill Team
 */
namespace Phpill\Core;
final class App {
	public static function run()
	{
		define('PHPILL_VERSION',  '1.0');
		define('PHPILL_CODENAME', 'accipiter');

		// Test of Phpill is running in Windows
		define('PHPILL_IS_WIN', DIRECTORY_SEPARATOR === '\\');
		
		$phpill_system = __DIR__."/../";
		define('SYSPATH', str_replace('\\', '/', realpath($phpill_system)).'/');

		// Load core files
		require SYSPATH.'Core/Phpill'.EXT;
		require SYSPATH.'Core/Event'.EXT;
		
		// Prepare the environment
		\Phpill::setup();

		// Prepare the system
		\Event::run('system.ready');

		// Determine routing
		\Event::run('system.routing');

		// Make the magic happen!
		\Event::run('system.execute');

		// Clean up and exit
		\Event::run('system.shutdown');
	}
}