<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_mysql_driver extends CI_DB{
	public $dbdriver = 'mysql';
	public $compress = FALSE;
	public $delete_hack = TRUE;
	public $stricton;
	protected $_escape_char = '`';
	public function __construct($params){
		parent::__construct($params);
		if(!empty($this->port)) {
			$this->hostname .= ':' . $this->port;
		}
	}
	public function db_connect($persistent = FALSE){
		$client_flags = ($this->compress === FALSE) ? 0 : MYSQL_CLIENT_COMPRESS;
		if($this->encrypt === TRUE) {
			$client_flags = $client_flags | MYSQL_CLIENT_SSL;
		}
		$this->conn_id = ($persistent === TRUE) ? mysql_pconnect($this->hostname, $this->username, $this->password, $client_flags) : mysql_connect($this->hostname, $this->username, $this->password, TRUE, $client_flags);
		if($this->database !== '' && !$this->db_select()) {
			log_message('error', 'Unable to select database: ' . $this->database);
			return ($this->db_debug === TRUE) ? $this->display_error('db_unable_to_select', $this->database) : FALSE;
		}
		if(isset($this->stricton) && is_resource($this->conn_id)) {
			if($this->stricton) {
				$this->simple_query('SET SESSION sql_mode = CONCAT(@@sql_mode, ",", "STRICT_ALL_TABLES")');
			} else {
				$this->simple_query('SET SESSION sql_mode =
					REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
					@@sql_mode,
					"STRICT_ALL_TABLES,", ""),
					",STRICT_ALL_TABLES", ""),
					"STRICT_ALL_TABLES", ""),
					"STRICT_TRANS_TABLES,", ""),
					",STRICT_TRANS_TABLES", ""),
					"STRICT_TRANS_TABLES", "")');
			}
		}
		return $this->conn_id;
	}
	public function reconnect(){
		if(mysql_ping($this->conn_id) === FALSE) {
			$this->conn_id = FALSE;
		}
	}
	public function db_select($database = ''){
		if($database === '') {
			$database = $this->database;
		}
		if(mysql_select_db($database, $this->conn_id)) {
			$this->database = $database;
			$this->data_cache = array();
			return TRUE;
		}
		return FALSE;
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		if(!$this->conn_id OR ($version = mysql_get_server_info($this->conn_id)) === FALSE) {
			return FALSE;
		}
		return $this->data_cache['version'] = $version;
	}
	public function affected_rows(){
		return mysql_affected_rows($this->conn_id);
	}
	public function insert_id(){
		return mysql_insert_id($this->conn_id);
	}
	public function field_data($table){
		if(($query = $this->query('SHOW COLUMNS FROM ' . $this->protect_identifiers($table, TRUE, NULL, FALSE))) === FALSE) {
			return FALSE;
		}
		$query = $query->result_object();
		$retval = array();
		for($i = 0, $c = count($query); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $query[$i]->Field;
			sscanf($query[$i]->Type, '%[a-z](%d)', $retval[$i]->type, $retval[$i]->max_length);
			$retval[$i]->default = $query[$i]->Default;
			$retval[$i]->primary_key = (int)($query[$i]->Key === 'PRI');
		}
		return $retval;
	}
	public function error(){
		return array('code' => mysql_errno($this->conn_id), 'message' => mysql_error($this->conn_id));
	}
	protected function _db_set_charset($charset){
		return mysql_set_charset($charset, $this->conn_id);
	}
	protected function _execute($sql){
		return mysql_query($this->_prep_query($sql), $this->conn_id);
	}
	protected function _prep_query($sql){
		if($this->delete_hack === TRUE && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
			return trim($sql) . ' WHERE 1=1';
		}
		return $sql;
	}
	protected function _trans_begin(){
		$this->simple_query('SET AUTOCOMMIT=0');
		return $this->simple_query('START TRANSACTION');
	}
	protected function _trans_commit(){
		if($this->simple_query('COMMIT')) {
			$this->simple_query('SET AUTOCOMMIT=1');
			return TRUE;
		}
		return FALSE;
	}
	protected function _trans_rollback(){
		if($this->simple_query('ROLLBACK')) {
			$this->simple_query('SET AUTOCOMMIT=1');
			return TRUE;
		}
		return FALSE;
	}
	protected function _escape_str($str){
		return mysql_real_escape_string($str, $this->conn_id);
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SHOW TABLES FROM ' . $this->escape_identifiers($this->database);
		if($prefix_limit !== FALSE && $this->dbprefix !== '') {
			return $sql . " LIKE '" . $this->escape_like_str($this->dbprefix) . "%'";
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SHOW COLUMNS FROM ' . $this->protect_identifiers($table, TRUE, NULL, FALSE);
	}
	protected function _from_tables(){
		if(!empty($this->qb_join) && count($this->qb_from) > 1) {
			return '(' . implode(', ', $this->qb_from) . ')';
		}
		return implode(', ', $this->qb_from);
	}
	protected function _close(){
		@mysql_close($this->conn_id);
	}
}
