<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_cubrid_driver extends CI_DB{
	public $dbdriver = 'cubrid';
	public $auto_commit = TRUE;
	protected $_escape_char = '`';
	protected $_random_keyword = array('RANDOM()', 'RANDOM(%d)');
	public function __construct($params){
		parent::__construct($params);
		if(preg_match('/^CUBRID:[^:]+(:[0-9][1-9]{0,4})?:[^:]+:[^:]*:[^:]*:(\?.+)?$/', $this->dsn, $matches)) {
			if(stripos($matches[2], 'autocommit=off') !== FALSE) {
				$this->auto_commit = FALSE;
			}
		} else {
			empty($this->port) OR $this->port = 33000;
		}
	}
	public function db_connect($persistent = FALSE){
		if(preg_match('/^CUBRID:[^:]+(:[0-9][1-9]{0,4})?:[^:]+:([^:]*):([^:]*):(\?.+)?$/', $this->dsn, $matches)) {
			$func = ($persistent !== TRUE) ? 'cubrid_connect_with_url' : 'cubrid_pconnect_with_url';
			return ($matches[2] === '' && $matches[3] === '' && $this->username !== '' && $this->password !== '') ? $func($this->dsn, $this->username, $this->password) : $func($this->dsn);
		}
		$func = ($persistent !== TRUE) ? 'cubrid_connect' : 'cubrid_pconnect';
		return ($this->username !== '') ? $func($this->hostname, $this->port, $this->database, $this->username, $this->password) : $func($this->hostname, $this->port, $this->database);
	}
	public function reconnect(){
		if(cubrid_ping($this->conn_id) === FALSE) {
			$this->conn_id = FALSE;
		}
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		return (!$this->conn_id OR ($version = cubrid_get_server_info($this->conn_id)) === FALSE) ? FALSE : $this->data_cache['version'] = $version;
	}
	public function affected_rows(){
		return cubrid_affected_rows();
	}
	public function insert_id(){
		return cubrid_insert_id($this->conn_id);
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
		return array('code' => cubrid_errno($this->conn_id), 'message' => cubrid_error($this->conn_id));
	}
	protected function _execute($sql){
		return cubrid_query($sql, $this->conn_id);
	}
	protected function _trans_begin(){
		if(($autocommit = cubrid_get_autocommit($this->conn_id)) === NULL) {
			return FALSE;
		} elseif($autocommit === TRUE) {
			return cubrid_set_autocommit($this->conn_id, CUBRID_AUTOCOMMIT_FALSE);
		}
		return TRUE;
	}
	protected function _trans_commit(){
		if(!cubrid_commit($this->conn_id)) {
			return FALSE;
		}
		if($this->auto_commit && !cubrid_get_autocommit($this->conn_id)) {
			return cubrid_set_autocommit($this->conn_id, CUBRID_AUTOCOMMIT_TRUE);
		}
		return TRUE;
	}
	protected function _trans_rollback(){
		if(!cubrid_rollback($this->conn_id)) {
			return FALSE;
		}
		if($this->auto_commit && !cubrid_get_autocommit($this->conn_id)) {
			cubrid_set_autocommit($this->conn_id, CUBRID_AUTOCOMMIT_TRUE);
		}
		return TRUE;
	}
	protected function _escape_str($str){
		return cubrid_real_escape_string($str, $this->conn_id);
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SHOW TABLES';
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
		cubrid_close($this->conn_id);
	}
}
