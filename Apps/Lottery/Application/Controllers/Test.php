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
class Test extends \System\Libraries\Controller {
	public function test1()
	{

		echo "test";
		$match_model = new \Application\Models\Match();
		$match_model->getMatchId(6534);
		
		$cache = \System\Libraries\Cache::instance();
		//$cache->set("testabc_ph", "test2");
		
		//echo $cache->get("testabc_ph");

		$log = new \Monolog\Logger('name');
		$data = new \Monolog\Handler\StreamHandler(APPPATH.'Logs/app.log', \Monolog\Logger::WARNING);
		$log->pushHandler($data);
		$log->addWarning('Foo');
		
		//new mat

	}
	
	public function index()
	{
		echo "index";
	}
}
