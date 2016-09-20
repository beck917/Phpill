<?php

/**
 * @author Beck Xu <beck917@gmail.com>
 * @date 2016-09-07
 * @copyright (c) 2009-2016 Phpill Team
 */
namespace Phpill\Core;
final class App {
	public static function run($container = null)
	{
		define('PHPILL_VERSION',  '1.0');
		define('PHPILL_CODENAME', 'accipiter');

		// Test of Phpill is running in Windows
		define('PHPILL_IS_WIN', DIRECTORY_SEPARATOR === '\\');
		
		$phpill_system = __DIR__."/../";
		define('SYSPATH', str_replace('\\', '/', realpath($phpill_system)).'/');
        
        if ($container != null) {
            $container->exec();
        }

		// Load core files
		require SYSPATH.'Core/Phpill'.EXT;
		require SYSPATH.'Core/Event'.EXT;
		
		// Prepare the environment
		\Phpill::setup();

		// Prepare the system
		\Event::run('system.ready');
        
        if (PHP_SAPI == 'cli') // Try and load minion
        {
            class_exists('\Phpill\Modules\Minion\Libraries\Task') OR die('Please enable the Minion module for CLI support.');
            
            set_error_handler(array('\Phpill\Modules\Minion\Libraries\Exception', 'error_handler'));
            set_exception_handler(array('\Phpill\Modules\Minion\Libraries\Exception', 'handler'));
            
            \Phpill\Modules\Minion\Libraries\Task::factory(\Phpill\Modules\Minion\Libraries\CLI::options())->execute();
        } else {
            // Determine routing
            \Event::run('system.routing');
            
            // Make the magic happen!
            \Event::run('system.execute');
        }
		// Clean up and exit
		\Event::run('system.shutdown');
	}
}