<?php
/**
 * @author Beck Xu <beck917@gmail.com>
 * @date 2016-09-01
 * @copyright 足球魔方
 */
namespace Application\Controllers;
/**
 * Description of Test
 *
 * @author Beck Xu <beck917@gmail.com>
 */
class Test extends \Phpill\Libraries\Controller {
	public function test1()
	{

		echo "test";  
		//$match_model = new \Application\Example\Models\Match(); 
		//$match_model->getMatchId(6534);
		
		echo \Modules\Core\Helpers\Common::getUUID();
		
		//$cache = \System\Libraries\Cache::instance();
		//$cache->set("testabc_ph", "test2");
		
		//echo $cache->get("testabc_ph");

		//$log = new \Monolog\Logger('name');
		//$data = new \Monolog\Handler\StreamHandler(APPPATH.'Logs/app.log', \Monolog\Logger::WARNING);
		//$log->pushHandler($data); 
		//$log->addWarning('Foo');
	}
	
	public function index()
	{
		echo "index";
	}
}
