<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_driver extends CI_DB{
	public $dbdriver = 'pdo';
	public $options = array();
	public function __construct($params){
		parent::__construct($params);
		if(preg_match('/([^:]+):/', $this->dsn, $match) && count($match) === 2) {
			$this->subdriver = $match[1];
			return;
		} elseif(preg_match('/([^:]+):/', $this->hostname, $match) && count($match) === 2) {
			$this->dsn = $this->hostname;
			$this->hostname = NULL;
			$this->subdriver = $match[1];
			return;
		} elseif(in_array($this->subdriver, array('mssql', 'sybase'), TRUE)) {
			$this->subdriver = 'dblib';
		} elseif($this->subdriver === '4D') {
			$this->subdriver = '4d';
		} elseif(!in_array($this->subdriver, array('4d', 'cubrid', 'dblib', 'firebird', 'ibm', 'informix', 'mysql', 'oci', 'odbc', 'pgsql', 'sqlite', 'sqlsrv'), TRUE)) {
			log_message('error', 'PDO: Invalid or non-existent subdriver');
			if($this->db_debug) {
				show_error('Invalid or non-existent PDO subdriver');
			}
		}
		$this->dsn = NULL;
	}
	public function db_connect($persistent = FALSE){
		if($persistent === TRUE) {
			$this->options[PDO::ATTR_PERSISTENT] = TRUE;
		}
		try {
			return new PDO($this->dsn, $this->username, $this->password, $this->options);
		} catch(PDOException $e) {
			if($this->db_debug && empty($this->failover)) {
				$this->display_error($e->getMessage(), '', TRUE);
			}
			return FALSE;
		}
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		try {
			return $this->data_cache['version'] = $this->conn_id->getAttribute(PDO::ATTR_SERVER_VERSION);
		} catch(PDOException $e) {
			return parent::version();
		}
	}
	public function affected_rows(){
		return is_object($this->result_id) ? $this->result_id->rowCount() : 0;
	}
	public function insert_id($name = NULL){
		return $this->conn_id->lastInsertId($name);
	}
	public function error(){
		$error = array('code' => '00000', 'message' => '');
		$pdo_error = $this->conn_id->errorInfo();
		if(empty($pdo_error[0])) {
			return $error;
		}
		$error['code'] = isset($pdo_error[1]) ? $pdo_error[0] . '/' . $pdo_error[1] : $pdo_error[0];
		if(isset($pdo_error[2])) {
			$error['message'] = $pdo_error[2];
		}
		return $error;
	}
	protected function _execute($sql){
		return $this->conn_id->query($sql);
	}
	protected function _trans_begin(){
		return $this->conn_id->beginTransaction();
	}
	protected function _trans_commit(){
		return $this->conn_id->commit();
	}
	protected function _trans_rollback(){
		return $this->conn_id->rollBack();
	}
	protected function _escape_str($str){
		$str = $this->conn_id->quote($str);
		return ($str[0] === "'") ? substr($str, 1, -1) : $str;
	}
	protected function _field_data($table){
		return 'SELECT TOP 1 * FROM ' . $this->protect_identifiers($table);
	}
	protected function _truncate($table){
		return 'TRUNCATE TABLE ' . $table;
	}
}
