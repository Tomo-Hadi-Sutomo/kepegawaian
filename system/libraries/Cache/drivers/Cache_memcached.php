<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Cache_memcached extends CI_Driver{
	protected $_memcached;
	protected $_config = array('default' => array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 1));
	public function __construct(){
		$CI =& get_instance();
		$defaults = $this->_config['default'];
		if($CI->config->load('memcached', TRUE, TRUE)) {
			$this->_config = $CI->config->config['memcached'];
		}
		if(class_exists('Memcached', FALSE)) {
			$this->_memcached = new Memcached();
		} elseif(class_exists('Memcache', FALSE)) {
			$this->_memcached = new Memcache();
		} else {
			log_message('error', 'Cache: Failed to create Memcache(d) object; extension not loaded?');
			return;
		}
		foreach($this->_config as $cache_server) {
			isset($cache_server['hostname']) OR $cache_server['hostname'] = $defaults['host'];
			isset($cache_server['port']) OR $cache_server['port'] = $defaults['port'];
			isset($cache_server['weight']) OR $cache_server['weight'] = $defaults['weight'];
			if($this->_memcached instanceof Memcache) {
				$this->_memcached->addServer($cache_server['hostname'], $cache_server['port'], TRUE, $cache_server['weight']);
			} elseif($this->_memcached instanceof Memcached) {
				$this->_memcached->addServer($cache_server['hostname'], $cache_server['port'], $cache_server['weight']);
			}
		}
	}
	public function get($id){
		$data = $this->_memcached->get($id);
		return is_array($data) ? $data[0] : $data;
	}
	public function save($id, $data, $ttl = 60, $raw = FALSE){
		if($raw !== TRUE) {
			$data = array($data, time(), $ttl);
		}
		if($this->_memcached instanceof Memcached) {
			return $this->_memcached->set($id, $data, $ttl);
		} elseif($this->_memcached instanceof Memcache) {
			return $this->_memcached->set($id, $data, 0, $ttl);
		}
		return FALSE;
	}
	public function delete($id){
		return $this->_memcached->delete($id);
	}
	public function increment($id, $offset = 1){
		return $this->_memcached->increment($id, $offset);
	}
	public function decrement($id, $offset = 1){
		return $this->_memcached->decrement($id, $offset);
	}
	public function clean(){
		return $this->_memcached->flush();
	}
	public function cache_info(){
		return $this->_memcached->getStats();
	}
	public function get_metadata($id){
		$stored = $this->_memcached->get($id);
		if(count($stored) !== 3) {
			return FALSE;
		}
		list($data, $time, $ttl) = $stored;
		return array('expire' => $time + $ttl, 'mtime' => $time, 'data' => $data);
	}
	public function is_supported(){
		return (extension_loaded('memcached') OR extension_loaded('memcache'));
	}
	public function __destruct(){
		if($this->_memcached instanceof Memcache) {
			$this->_memcached->close();
		} elseif($this->_memcached instanceof Memcached && method_exists($this->_memcached, 'quit')) {
			$this->_memcached->quit();
		}
	}
}
