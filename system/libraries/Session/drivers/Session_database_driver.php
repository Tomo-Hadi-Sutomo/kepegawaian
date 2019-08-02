<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Session_database_driver extends CI_Session_driver implements SessionHandlerInterface{
	protected $_db;
	protected $_row_exists = FALSE;
	protected $_platform;
	public function __construct(&$params){
		parent::__construct($params);
		$CI =& get_instance();
		isset($CI->db) OR $CI->load->database();
		$this->_db = $CI->db;
		if(!$this->_db instanceof CI_DB_query_builder) {
			throw new Exception('Query Builder not enabled for the configured database. Aborting.');
		} elseif($this->_db->pconnect) {
			throw new Exception('Configured database connection is persistent. Aborting.');
		} elseif($this->_db->cache_on) {
			throw new Exception('Configured database connection has cache enabled. Aborting.');
		}
		$db_driver = $this->_db->dbdriver . (empty($this->_db->subdriver) ? '' : '_' . $this->_db->subdriver);
		if(strpos($db_driver, 'mysql') !== FALSE) {
			$this->_platform = 'mysql';
		} elseif(in_array($db_driver, array('postgre', 'pdo_pgsql'), TRUE)) {
			$this->_platform = 'postgre';
		}
		if(!isset($this->_config['save_path']) && ($this->_config['save_path'] = config_item('sess_table_name'))) {
			log_message('debug', 'Session: "sess_save_path" is empty; using BC fallback to "sess_table_name".');
		}
	}
	public function open($save_path, $name){
		if(empty($this->_db->conn_id) && !$this->_db->db_connect()) {
			return $this->_fail();
		}
		return $this->_success;
	}
	public function read($session_id){
		if($this->_get_lock($session_id) !== FALSE) {
			$this->_db->reset_query();
			$this->_session_id = $session_id;
			$this->_db->select('data')->from($this->_config['save_path'])->where('id', $session_id);
			if($this->_config['match_ip']) {
				$this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
			}
			if(!($result = $this->_db->get()) OR ($result = $result->row()) === NULL) {
				$this->_row_exists = FALSE;
				$this->_fingerprint = md5('');
				return '';
			}
			$result = ($this->_platform === 'postgre') ? base64_decode(rtrim($result->data)) : $result->data;
			$this->_fingerprint = md5($result);
			$this->_row_exists = TRUE;
			return $result;
		}
		$this->_fingerprint = md5('');
		return '';
	}
	public function write($session_id, $session_data){
		$this->_db->reset_query();
		if($this->_lock === FALSE) {
			return $this->_fail();
		} elseif($session_id !== $this->_session_id) {
			if(!$this->_release_lock() OR !$this->_get_lock($session_id)) {
				return $this->_fail();
			}
			$this->_row_exists = FALSE;
			$this->_session_id = $session_id;
		}
		if($this->_row_exists === FALSE) {
			$insert_data = array('id' => $session_id, 'ip_address' => $_SERVER['REMOTE_ADDR'], 'timestamp' => time(), 'data' => ($this->_platform === 'postgre' ? base64_encode($session_data) : $session_data));
			if($this->_db->insert($this->_config['save_path'], $insert_data)) {
				$this->_fingerprint = md5($session_data);
				$this->_row_exists = TRUE;
				return $this->_success;
			}
			return $this->_fail();
		}
		$this->_db->where('id', $session_id);
		if($this->_config['match_ip']) {
			$this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
		}
		$update_data = array('timestamp' => time());
		if($this->_fingerprint !== md5($session_data)) {
			$update_data['data'] = ($this->_platform === 'postgre') ? base64_encode($session_data) : $session_data;
		}
		if($this->_db->update($this->_config['save_path'], $update_data)) {
			$this->_fingerprint = md5($session_data);
			return $this->_success;
		}
		return $this->_fail();
	}
	public function close(){
		return ($this->_lock && !$this->_release_lock()) ? $this->_fail() : $this->_success;
	}
	public function destroy($session_id){
		if($this->_lock) {
			$this->_db->reset_query();
			$this->_db->where('id', $session_id);
			if($this->_config['match_ip']) {
				$this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
			}
			if(!$this->_db->delete($this->_config['save_path'])) {
				return $this->_fail();
			}
		}
		if($this->close() === $this->_success) {
			$this->_cookie_destroy();
			return $this->_success;
		}
		return $this->_fail();
	}
	public function gc($maxlifetime){
		$this->_db->reset_query();
		return ($this->_db->delete($this->_config['save_path'], 'timestamp < ' . (time() - $maxlifetime))) ? $this->_success : $this->_fail();
	}
	protected function _get_lock($session_id){
		if($this->_platform === 'mysql') {
			$arg = md5($session_id . ($this->_config['match_ip'] ? '_' . $_SERVER['REMOTE_ADDR'] : ''));
			if($this->_db->query("SELECT GET_LOCK('" . $arg . "', 300) AS ci_session_lock")->row()->ci_session_lock) {
				$this->_lock = $arg;
				return TRUE;
			}
			return FALSE;
		} elseif($this->_platform === 'postgre') {
			$arg = "hashtext('" . $session_id . "')" . ($this->_config['match_ip'] ? ", hashtext('" . $_SERVER['REMOTE_ADDR'] . "')" : '');
			if($this->_db->simple_query('SELECT pg_advisory_lock(' . $arg . ')')) {
				$this->_lock = $arg;
				return TRUE;
			}
			return FALSE;
		}
		return parent::_get_lock($session_id);
	}
	protected function _release_lock(){
		if(!$this->_lock) {
			return TRUE;
		}
		if($this->_platform === 'mysql') {
			if($this->_db->query("SELECT RELEASE_LOCK('" . $this->_lock . "') AS ci_session_lock")->row()->ci_session_lock) {
				$this->_lock = FALSE;
				return TRUE;
			}
			return FALSE;
		} elseif($this->_platform === 'postgre') {
			if($this->_db->simple_query('SELECT pg_advisory_unlock(' . $this->_lock . ')')) {
				$this->_lock = FALSE;
				return TRUE;
			}
			return FALSE;
		}
		return parent::_release_lock();
	}
}
