<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_postgre_driver extends CI_DB{
	public $dbdriver = 'postgre';
	public $schema = 'public';
	protected $_random_keyword = array('RANDOM()', 'RANDOM()');
	public function __construct($params){
		parent::__construct($params);
		if(!empty($this->dsn)) {
			return;
		}
		$this->dsn === '' OR $this->dsn = '';
		if(strpos($this->hostname, '/') !== FALSE) {
			$this->port = '';
		}
		$this->hostname === '' OR $this->dsn = 'host=' . $this->hostname . ' ';
		if(!empty($this->port) && ctype_digit($this->port)) {
			$this->dsn .= 'port=' . $this->port . ' ';
		}
		if($this->username !== '') {
			$this->dsn .= 'user=' . $this->username . ' ';
			$this->password === NULL OR $this->dsn .= "password='" . $this->password . "' ";
		}
		$this->database === '' OR $this->dsn .= 'dbname=' . $this->database . ' ';
		foreach(array('connect_timeout', 'options', 'sslmode', 'service') as $key) {
			if(isset($this->$key) && is_string($this->key) && $this->key !== '') {
				$this->dsn .= $key . "='" . $this->key . "' ";
			}
		}
		$this->dsn = rtrim($this->dsn);
	}
	public function db_connect($persistent = FALSE){
		$this->conn_id = ($persistent === TRUE) ? pg_pconnect($this->dsn) : pg_connect($this->dsn);
		if($this->conn_id !== FALSE) {
			if($persistent === TRUE && pg_connection_status($this->conn_id) === PGSQL_CONNECTION_BAD && pg_ping($this->conn_id) === FALSE) {
				return FALSE;
			}
			empty($this->schema) OR $this->simple_query('SET search_path TO ' . $this->schema . ',public');
		}
		return $this->conn_id;
	}
	public function reconnect(){
		if(pg_ping($this->conn_id) === FALSE) {
			$this->conn_id = FALSE;
		}
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		if(!$this->conn_id OR ($pg_version = pg_version($this->conn_id)) === FALSE) {
			return FALSE;
		}
		return isset($pg_version['server']) ? $this->data_cache['version'] = $pg_version['server'] : parent::version();
	}
	public function is_write_type($sql){
		if(preg_match('#^(INSERT|UPDATE).*RETURNING\s.+(\,\s?.+)*$#is', $sql)) {
			return FALSE;
		}
		return parent::is_write_type($sql);
	}
	public function escape($str){
		if(is_php('5.4.4') && (is_string($str) OR (is_object($str) && method_exists($str, '__toString')))) {
			return pg_escape_literal($this->conn_id, $str);
		} elseif(is_bool($str)) {
			return ($str) ? 'TRUE' : 'FALSE';
		}
		return parent::escape($str);
	}
	public function affected_rows(){
		return pg_affected_rows($this->result_id);
	}
	public function insert_id(){
		$v = pg_version($this->conn_id);
		$v = isset($v['server']) ? $v['server'] : 0;
		$table = (func_num_args() > 0) ? func_get_arg(0) : NULL;
		$column = (func_num_args() > 1) ? func_get_arg(1) : NULL;
		if($table === NULL && $v >= '8.1') {
			$sql = 'SELECT LASTVAL() AS ins_id';
		} elseif($table !== NULL) {
			if($column !== NULL && $v >= '8.0') {
				$sql = 'SELECT pg_get_serial_sequence(\'' . $table . "', '" . $column . "') AS seq";
				$query = $this->query($sql);
				$query = $query->row();
				$seq = $query->seq;
			} else {
				$seq = $table;
			}
			$sql = 'SELECT CURRVAL(\'' . $seq . "') AS ins_id";
		} else {
			return pg_last_oid($this->result_id);
		}
		$query = $this->query($sql);
		$query = $query->row();
		return (int)$query->ins_id;
	}
	public function field_data($table){
		$sql = 'SELECT "column_name", "data_type", "character_maximum_length", "numeric_precision", "column_default"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = ' . $this->escape(strtolower($table));
		if(($query = $this->query($sql)) === FALSE) {
			return FALSE;
		}
		$query = $query->result_object();
		$retval = array();
		for($i = 0, $c = count($query); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $query[$i]->column_name;
			$retval[$i]->type = $query[$i]->data_type;
			$retval[$i]->max_length = ($query[$i]->character_maximum_length > 0) ? $query[$i]->character_maximum_length : $query[$i]->numeric_precision;
			$retval[$i]->default = $query[$i]->column_default;
		}
		return $retval;
	}
	public function error(){
		return array('code' => '', 'message' => pg_last_error($this->conn_id));
	}
	public function order_by($orderby, $direction = '', $escape = NULL){
		$direction = strtoupper(trim($direction));
		if($direction === 'RANDOM') {
			if(!is_float($orderby) && ctype_digit((string)$orderby)) {
				$orderby = ($orderby > 1) ? (float)'0.' . $orderby : (float)$orderby;
			}
			if(is_float($orderby)) {
				$this->simple_query('SET SEED ' . $orderby);
			}
			$orderby = $this->_random_keyword[0];
			$direction = '';
			$escape = FALSE;
		}
		return parent::order_by($orderby, $direction, $escape);
	}
	protected function _db_set_charset($charset){
		return (pg_set_client_encoding($this->conn_id, $charset) === 0);
	}
	protected function _execute($sql){
		return pg_query($this->conn_id, $sql);
	}
	protected function _trans_begin(){
		return (bool)pg_query($this->conn_id, 'BEGIN');
	}
	protected function _trans_commit(){
		return (bool)pg_query($this->conn_id, 'COMMIT');
	}
	protected function _trans_rollback(){
		return (bool)pg_query($this->conn_id, 'ROLLBACK');
	}
	protected function _escape_str($str){
		return pg_escape_string($this->conn_id, $str);
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = \'' . $this->schema . "'";
		if($prefix_limit !== FALSE && $this->dbprefix !== '') {
			return $sql . ' AND "table_name" LIKE \'' . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SELECT "column_name"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = ' . $this->escape(strtolower($table));
	}
	protected function _update($table, $values){
		$this->qb_limit = FALSE;
		$this->qb_orderby = array();
		return parent::_update($table, $values);
	}
	protected function _update_batch($table, $values, $index){
		$ids = array();
		foreach($values as $key => $val) {
			$ids[] = $val[$index]['value'];
			foreach(array_keys($val) as $field) {
				if($field !== $index) {
					$final[$val[$field]['field']][] = 'WHEN ' . $val[$index]['value'] . ' THEN ' . $val[$field]['value'];
				}
			}
		}
		$cases = '';
		foreach($final as $k => $v) {
			$cases .= $k . ' = (CASE ' . $val[$index]['field'] . "\n" . implode("\n", $v) . "\n" . 'ELSE ' . $k . ' END), ';
		}
		$this->where($val[$index]['field'] . ' IN(' . implode(',', $ids) . ')', NULL, FALSE);
		return 'UPDATE ' . $table . ' SET ' . substr($cases, 0, -2) . $this->_compile_wh('qb_where');
	}
	protected function _delete($table){
		$this->qb_limit = FALSE;
		return parent::_delete($table);
	}
	protected function _limit($sql){
		return $sql . ' LIMIT ' . $this->qb_limit . ($this->qb_offset ? ' OFFSET ' . $this->qb_offset : '');
	}
	protected function _close(){
		pg_close($this->conn_id);
	}
}
