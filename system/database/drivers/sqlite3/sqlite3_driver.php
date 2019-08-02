<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_sqlite3_driver extends CI_DB{
	public $dbdriver = 'sqlite3';
	protected $_random_keyword = array('RANDOM()', 'RANDOM()');
	public function db_connect($persistent = FALSE){
		if($persistent) {
			log_message('debug', 'SQLite3 doesn\'t support persistent connections');
		}
		try {
			return (!$this->password) ? new SQLite3($this->database) : new SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);
		} catch(Exception $e) {
			return FALSE;
		}
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		$version = SQLite3::version();
		return $this->data_cache['version'] = $version['versionString'];
	}
	public function affected_rows(){
		return $this->conn_id->changes();
	}
	public function insert_id(){
		return $this->conn_id->lastInsertRowID();
	}
	public function list_fields($table){
		if(isset($this->data_cache['field_names'][$table])) {
			return $this->data_cache['field_names'][$table];
		}
		if(($result = $this->query('PRAGMA TABLE_INFO(' . $this->protect_identifiers($table, TRUE, NULL, FALSE) . ')')) === FALSE) {
			return FALSE;
		}
		$this->data_cache['field_names'][$table] = array();
		foreach($result->result_array() as $row) {
			$this->data_cache['field_names'][$table][] = $row['name'];
		}
		return $this->data_cache['field_names'][$table];
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
		return array('code' => $this->conn_id->lastErrorCode(), 'message' => $this->conn_id->lastErrorMsg());
	}
	protected function _execute($sql){
		return $this->is_write_type($sql) ? $this->conn_id->exec($sql) : $this->conn_id->query($sql);
	}
	protected function _trans_begin(){
		return $this->conn_id->exec('BEGIN TRANSACTION');
	}
	protected function _trans_commit(){
		return $this->conn_id->exec('END TRANSACTION');
	}
	protected function _trans_rollback(){
		return $this->conn_id->exec('ROLLBACK');
	}
	protected function _escape_str($str){
		return $this->conn_id->escapeString($str);
	}
	protected function _list_tables($prefix_limit = FALSE){
		return 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\'' . (($prefix_limit !== FALSE && $this->dbprefix != '') ? ' AND "NAME" LIKE \'' . $this->escape_like_str($this->dbprefix) . '%\' ' . sprintf($this->_like_escape_str, $this->_like_escape_chr) : '');
	}
	protected function _replace($table, $keys, $values){
		return 'INSERT OR ' . parent::_replace($table, $keys, $values);
	}
	protected function _truncate($table){
		return 'DELETE FROM ' . $table;
	}
	protected function _close(){
		$this->conn_id->close();
	}
}
