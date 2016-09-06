<?php
/**
 * @author Beck Xu <beck917@gmail.com>
 * @date 2016-09-05
 * @copyright 足球魔方
 */
namespace Application\Example\Models;
class Match extends \Phpill\Libraries\Model {
	protected $table_name;

	public function __construct()
	{
		$this->table_name = "match";//lcfirst(__CLASS__)

		parent::__construct();
	}
	
	public function getMatchId($match_id)
	{
		$this->db->where('match_id', $match_id);
		$data = $this->db->get($this->table_name);
		echo $this->db->last_query();
		
		//var_dump($data->current());
		//$this->db->query("test");
		
		//$this->db->query("select * from dt_match");
		/**
		$this->db->begin();
		try {
			$this->db->query("test");
			$this->db->commit();
		} catch (\System\Libraries\Phpill_Database_Exception $e) {
			echo $e->getMessage();
			$this->db->rollback();
		}
		 * 
		 */
		
		return $data;
	}
	
}
