<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_sqlite_driver extends CI_DB{
	public $dbdriver = 'sqlite';
	protected $_random_keyword = array('RANDOM()', 'RANDOM()');
	public function db_connect($persistent = FALSE){
		$error = NULL;
		$conn_id = ($persistent === TRUE) ? sqlite_popen($this->database, 0666, $error) : sqlite_open($this->database, 0666, $error);
		isset($error) && log_message('error', $error);
		return $conn_id;
	}
	public function version(){
		return isset($this->data_cache['version']) ? $this->data_cache['version'] : $this->data_cache['version'] = sqlite_libversion();
	}
	public function affected_rows(){
		return sqlite_changes($this->conn_id);
	}
	public function insert_id(){
		return sqlite_last_insert_rowid($this->conn_id);
	}
	public function field_data($table){
		if(($query = $this->query('PRAGMA TABLE_INFO(' . $this->protect_identifiers($table, TRUE, NULL, FALSE) . ')')) === FALSE) {
			return FALSE;
		}
		$query = $query->result_array();
		if(empty($query)) {
			return FALSE;
		}
		$retval = array();
		for($i = 0, $c = count($query); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $query[$i]['name'];
			$retval[$i]->type = $query[$i]['type'];
			$retval[$i]->max_length = NULL;
			$retval[$i]->default = $query[$i]['dflt_value'];
			$retval[$i]->primary_key = isset($query[$i]['pk']) ? (int)$query[$i]['pk'] : 0;
		}
		return $retval;
	}
	public function error(){
		$error = array('code' => sqlite_last_error($this->conn_id));
		$error['message'] = sqlite_error_string($error['code']);
		return $error;
	}
	protected function _execute($sql){
		return $this->is_write_type($sql) ? sqlite_exec($this->conn_id, $sql) : sqlite_query($this->conn_id, $sql);
	}
	protected function _trans_begin(){
		return $this->simple_query('BEGIN TRANSACTION');
	}
	protected function _trans_commit(){
		return $this->simple_query('COMMIT');
	}
	protected function _trans_rollback(){
		return $this->simple_query('ROLLBACK');
	}
	protected function _escape_str($str){
		return sqlite_escape_string($str);
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = "SELECT name FROM sqlite_master WHERE type='table'";
		if($prefix_limit !== FALSE && $this->dbprefix != '') {
			return $sql . " AND 'name' LIKE '" . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return FALSE;
	}
	protected function _replace($table, $keys, $values){
		return 'INSERT OR ' . parent::_replace($table, $keys, $values);
	}
	protected function _truncate($table){
		return 'DELETE FROM ' . $table;
	}
	protected function _close(){
		sqlite_close($this->conn_id);
	}
}
