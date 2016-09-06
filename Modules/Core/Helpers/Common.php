<?php
namespace Modules\Core\Helpers;
class Common {

	/**
	 * 一致性哈希定位
	 * 支持冗余级别，但目前没太大作用
	 * @param string $id
	 * @param int $num
	 * @param int $level
	 * @return int
	 */
	public static function hashLocate($id, $num, $level = 0) {
		$val = md5($id);
		$val = hexdec($val[0] . $val[1] . $val[2] . $val[3]);
		return (floor($val / 65536 * $num) + $level) % $num;
	}

	public static function sha1($secret_key, $uid, $timestamp) {
		return sha1($secret_key . $uid . $timestamp);
	}

	/**
	 * 
	 * @return RedisServer
	 */
	public static function createRedis()
	{
		$cache_config = 'sort';
		$cache = Cache::instance($cache_config);

		$redis_driver = $cache->driver;
		$redis = $redis_driver->backend;
		return $redis;
	}
	
	/**
	 * 
	 * 获取奖励当中的key的值
	 */
	public static function awards_prop_key($key, $data) {
		$props = arr::_serial_to_array($data);

		foreach ($props AS $k => $v) {
			if ($k === $key) {
				return $v;
			}
		}
	}

	/**
	 * 	获得通用唯一标识
	 */
	public static function getUUID() {
		//Linux下使用uuid_create win下用uniqid
		if (PATH_SEPARATOR == ':') {
			return uuid_create();
		} else {
			return uniqid('', TRUE);
		}
	}

	/**
	 * 	二进制压缩
	 */
	public static function pack($data) {
		//Linux下使用uuid_create win下用uniqid
		if (0 && PATH_SEPARATOR == ':') {
			//linux
			return msgpack_pack($data);
		} else {
			//win
			return json_encode($data);
		}
	}

	/**
	 * 	二进制解压
	 */
	public static function unpack($data, $flg = true) {
		//Linux下使用uuid_create win下用uniqid
		if (0 && PATH_SEPARATOR == ':') {
			//linux
			return msgpack_unpack($data);
		} else {
			//win
			return json_decode($data, TRUE);
		}
	}

	public static function packStream($datas) {
		if (PATH_SEPARATOR == ':') {
			//linux
			$unpacker = new MessagePackUnpacker();
			$buffer = "";
			$nread = 0;
			foreach ($datas as $key => $msg) {
				$buffer = $buffer . $msg;
				while (true) {
					if ($unpacker->execute($buffer, $nread)) {
						$msg = $unpacker->data();
						$unpacker->reset();
						$buffer = substr($buffer, $nread);
						$nread = 0;

						if (!empty($buffer)) {
							continue;
						}
					}
					$datas[$key] = $msg;
					break;
				}
			}
		} else {
			foreach ($datas as $key => $msg) {
				$datas[$key] = self::unpack($msg);
			}
		}
		return $datas;
	}

	public static function awards($role_id, $awards_props, $sync = false) {
		//奖励属性
		$role = Role::create($role_id);
		foreach ($awards_props as $key => $vl) {
			$role->increment($key, $vl, $sync);
		}
	}

	/**
	 * 返回16进制crc32,减少存储空间
	 * $test = sprintf("%u", crc32('13412123')); 需要float (float)$test,所以这里
	 * 用了其他的方式
	 * 
	 * crc32在64位直接输出正数.32位平台大部分是负数,需要转换
	 */
	public static function crc32hex($key) {
		$i = crc32($key);
		if (0 > $i) {
			// Implicitly casts i as float, and corrects this sign.
			$i += 0x100000000;
		}
		$hex = dechex($i);
		return $hex;
	}

	/**
	 * 创建相对唯一的id,和role_id组成联合主键
	 */
	public static function createUniqueId() {
		$timestamp = microtime(true);
		//播更好的随机数种子
		mt_srand();
		$rnd = mt_rand();
		$key = $timestamp . ':' . $rnd;

		//也可以直接用uniqid
		//$key = uniqid();

		$crc_id = crc32($key);
		if (0 > $crc_id) {
			// Implicitly casts i as float, and corrects this sign.
			//修正32位server
			$crc_id += 0x100000000;
		}
		return $crc_id;
	}

	public static function redirect_json($msg, $params = array()) {
		$re_array = array('message' => $msg) + $params;

		echo json_encode($re_array);
		die();
	}

	public static function redirect_error_json($msg, $params = array()) {
		$re_array = array('message' => $msg, 'error' => 1) + $params;

		echo json_encode($re_array);
		die();
	}

	public static function setVar($key, $vl) {
		$var = &PEAR::getStaticProperty('_APP', $key);
		$var = $vl;
	}

	public static function getVar($key) {
		$var = PEAR::getStaticProperty('_APP', $key);
		return $var;
	}

	/**
	 * 完美兼容一维数组和多维迭代器
	 * @param String $name
	 * @param Array $data
	 */
	public static function transform($name, $data) {
		$trans = Phpill::config('transform.' . $name);
		$output = array();

		$i = 0;
		foreach ($data as $k => $v) {
			//一维数组
			if (!is_array($v)) {
				foreach ($trans as $key => $vl) {
					if (isset($data[$key])) {
						if (is_array($vl)) {
							$output[$vl[0]] = eval('return ' . $vl[1] . '("' . $data[$key] . '");');
						} else {
							if (is_numeric($data[$key]) && $data[$key] <= 2147483647 && $key != 'uid') {
								$output[$vl] = (int) $data[$key];
							} else {
								$output[$vl] = $data[$key];
							}
						}
					} else {
						$output[$vl] = '';
					}
				}
				break;
			} else {
				//二维数组

				foreach ($trans as $key => $vl) {
					//TODO 低效率
					$key_new = explode('|', $key, 2);
					if (is_numeric($key_new[0])) {
						$key = $key_new[1];
					}
					if (!empty($v[0]) && is_array($v[0])) {	//有修改  YUYA
						if (isset($v[0][$key])) {
							if (is_array($vl)) {
								$output[$i][$vl[0]] = eval('return ' . $vl[1] . "('" . $v[0][$key] . "');");
							} else {
								$output[$i][$vl] = $v[0][$key];
							}
						} else {
							if (is_array($vl)) {
								$output[$i][$vl[0]] = '';
							} else {
								$output[$i][$vl] = '';
							}
						}
					} else {
						if (isset($v[$key])) {
							if (is_array($vl)) {
								$output[$i][$vl[0]] = eval('return ' . $vl[1] . "('" . $v[$key] . "');");
							} else {
								$output[$i][$vl] = $v[$key];
							}
						} else {
							if (is_array($vl)) {
								$output[$i][$vl[0]] = '';
							} else {
								$output[$i][$vl] = '';
							}
						}
					}
				}
				$i++;
			}
		}
		return array($name => $output);
	}

	/**
	 * 获取分表后的真实表名
	 */
	public static function getTableName($table_pre, $id) {
		//crc32 hash
		$table_cut = Phpill::config('table.cut');

		if ($table_cut[$table_pre] === 0) {
			$table_name = $table_pre;
		} else {
			if (Phpill::config('base.cut_database_num') != 0) {
				$table_name = $table_pre . '_' . floor($id / Phpill::config('base.cut_database_num')) % $table_cut[$table_pre];
			} else {
				//$table_name = $table_pre.'_'.$id % $table_cut[$table_pre];
				$table_name = $table_pre . '_' . common::hashLocate($id, $table_cut[$table_pre]);
			}
		}

		return $table_name;
	}
	
	public static function random_new($rand_data, $rand_key = 'rand_pct') {
		if (empty($rand_data)) {
			return false;
		}

		$result = false;
		$sum = 0;
		foreach ($rand_data as $data) {
			$sum += $data[$rand_key];
		}
		$rand_num = mt_rand(1, $sum);
		$max_num = 0;
		foreach ($rand_data as $id => $data) {
			$max_num += $data[$rand_key];
			if($rand_num <= $max_num){
				$data['id'] = $id;
				$result = $data;
				break;
			}
		}
		return $result;
	}

	/**
	 * 生成随机
	 * @param unknown_type $rand_data
	 * @param unknown_type $rand_key
	 */
	public static function random($rand_data, $rand_key = 'rand_pct', $sum = 10000) {
		if (empty($rand_data)) {
			return false;
		}

		$result = false;
		$rand_num = mt_rand(1, $sum);
		foreach ($rand_data as $id => $data) {
			if ($rand_num <= $data[$rand_key]) {
				if (!isset($data['id'])) {
					$data['id'] = $id;
				}
				$result = $data;
				break;
			}
		}
		return $result;
	}

	/**
	 * 生成随机
	 * @param unknown_type $rand_data
	 * @param unknown_type $rand_key
	 */
	public static function random_prize($rand_data) {
		if (empty($rand_data)) {
			return array();
		}

		$sum = array_sum($rand_data);
		$rand_num = mt_rand(1, $sum);
		$max_num = 0;
		foreach ($rand_data as $id => $num) {
			$max_num += $num;
			if($rand_num <= $max_num){
				$result = $id;
				break;
			}
		}
		return $result;
	}

	/**
	 * 获取基础表数据
	 * @param unknown_type $key
	 */
	public static function basic($key) {
		static $basic_data = null;

		if (!isset($basic_data[$key])) {
			$basic_data = array();
			$basic_result = Phpill::config("game_basic:$key");

			$basic_data[$key] = $basic_result['value'];
		}
		return $basic_data[$key];
	}

	public static function endtime() {
		$timestamp = &PEAR::getStaticProperty('_APP', 'microtimestamp');
		Phpill::log('error', microtime(true) - $timestamp . " end " . Router::$current_uri);
	}
	
	public static function validHash($str, $key)
	{
		return sha1(md5(common::encrypt($str, $key).'$#^DGHV^$fdplGF'));
	}

	public static function encrypt($string, $key) {
		$str_len = strlen($string);
		$key_len = strlen($key);
		for ($i = 0; $i < $str_len; $i++) {
			for ($j = 0; $j < $key_len; $j++) {
				$string{$i} = $string{$i}^$key{$j};
			}
		}
		return $string;
	}

	public static function decrypt($string, $key) {
		$str_len = strlen($string);
		$key_len = strlen($key);
		for ($i = 0; $i < $str_len; $i++) {
			for ($j = 0; $j < $key_len; $j++) {
				$string[$i] = $key[$j] ^ $string[$i];
			}
		}
		return $string;
	}
	
	public static function lockStart($role_id)
	{
		$cache = Cache::instance('default');

		$lock = $cache->add('lock:'.$role_id, 1);
		if (empty($lock)) {
			return false;
		}
		$cache->set('lock:'.$role_id, 1, null, 3);
		Event::add('system.shutdown', array('common', 'lockEnd'));
		
		return true;
	}
	
	public static function lockEnd()
	{
		$role_id = Role::getOwnRoleId();
		$cache = Cache::instance('default');
		$cache->delete('lock:'.$role_id);
	}
}

?>