<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Cache_redis extends CI_Driver{
	protected static $_default_config = array('socket_type' => 'tcp', 'host' => '127.0.0.1', 'password' => NULL, 'port' => 6379, 'timeout' => 0);
	protected $_redis;
	protected $_serialized = array();
	public function __construct(){
		if(!$this->is_supported()) {
			log_message('error', 'Cache: Failed to create Redis object; extension not loaded?');
			return;
		}
		$CI =& get_instance();
		if($CI->config->load('redis', TRUE, TRUE)) {
			$config = array_merge(self::$_default_config, $CI->config->item('redis'));
		} else {
			$config = self::$_default_config;
		}
		$this->_redis = new Redis();
		try {
			if($config['socket_type'] === 'unix') {
				$success = $this->_redis->connect($config['socket']);
			} else {
				$success = $this->_redis->connect($config['host'], $config['port'], $config['timeout']);
			}
			if(!$success) {
				log_message('error', 'Cache: Redis connection failed. Check your configuration.');
			}
			if(isset($config['password']) && !$this->_redis->auth($config['password'])) {
				log_message('error', 'Cache: Redis authentication failed.');
			}
		} catch(RedisException $e) {
			log_message('error', 'Cache: Redis connection refused (' . $e->getMessage() . ')');
		}
		$serialized = $this->_redis->sMembers('_ci_redis_serialized');
		empty($serialized) OR $this->_serialized = array_flip($serialized);
	}
	public function get($key){
		$value = $this->_redis->get($key);
		if($value !== FALSE && isset($this->_serialized[$key])) {
			return unserialize($value);
		}
		return $value;
	}
	public function save($id, $data, $ttl = 60, $raw = FALSE){
		if(is_array($data) OR is_object($data)) {
			if(!$this->_redis->sIsMember('_ci_redis_serialized', $id) && !$this->_redis->sAdd('_ci_redis_serialized', $id)) {
				return FALSE;
			}
			isset($this->_serialized[$id]) OR $this->_serialized[$id] = TRUE;
			$data = serialize($data);
		} elseif(isset($this->_serialized[$id])) {
			$this->_serialized[$id] = NULL;
			$this->_redis->sRemove('_ci_redis_serialized', $id);
		}
		return $this->_redis->set($id, $data, $ttl);
	}
	public function delete($key){
		if($this->_redis->delete($key) !== 1) {
			return FALSE;
		}
		if(isset($this->_serialized[$key])) {
			$this->_serialized[$key] = NULL;
			$this->_redis->sRemove('_ci_redis_serialized', $key);
		}
		return TRUE;
	}
	public function increment($id, $offset = 1){
		return $this->_redis->incr($id, $offset);
	}
	public function decrement($id, $offset = 1){
		return $this->_redis->decr($id, $offset);
	}
	public function clean(){
		return $this->_redis->flushDB();
	}
	public function cache_info($type = NULL){
		return $this->_redis->info();
	}
	public function get_metadata($key){
		$value = $this->get($key);
		if($value !== FALSE) {
			return array('expire' => time() + $this->_redis->ttl($key), 'data' => $value);
		}
		return FALSE;
	}
	public function is_supported(){
		return extension_loaded('redis');
	}
	public function __destruct(){
		if($this->_redis) {
			$this->_redis->close();
		}
	}
}
