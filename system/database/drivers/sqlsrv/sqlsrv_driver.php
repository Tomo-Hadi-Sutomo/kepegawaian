<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_sqlsrv_driver extends CI_DB{
	public $dbdriver = 'sqlsrv';
	public $scrollable;
	protected $_random_keyword = array('NEWID()', 'RAND(%d)');
	protected $_quoted_identifier = TRUE;
	public function __construct($params){
		parent::__construct($params);
		if($this->scrollable === NULL) {
			$this->scrollable = defined('SQLSRV_CURSOR_CLIENT_BUFFERED') ? SQLSRV_CURSOR_CLIENT_BUFFERED : FALSE;
		}
	}
	public function db_connect($pooling = FALSE){
		$charset = in_array(strtolower($this->char_set), array('utf-8', 'utf8'), TRUE) ? 'UTF-8' : SQLSRV_ENC_CHAR;
		$connection = array('UID' => empty($this->username) ? '' : $this->username, 'PWD' => empty($this->password) ? '' : $this->password, 'Database' => $this->database, 'ConnectionPooling' => ($pooling === TRUE) ? 1 : 0, 'CharacterSet' => $charset, 'Encrypt' => ($this->encrypt === TRUE) ? 1 : 0, 'ReturnDatesAsStrings' => 1);
		if(empty($connection['UID']) && empty($connection['PWD'])) {
			unset($connection['UID'], $connection['PWD']);
		}
		if(FALSE !== ($this->conn_id = sqlsrv_connect($this->hostname, $connection))) {
			$query = $this->query('SELECT CASE WHEN (@@OPTIONS | 256) = @@OPTIONS THEN 1 ELSE 0 END AS qi');
			$query = $query->row_array();
			$this->_quoted_identifier = empty($query) ? FALSE : (bool)$query['qi'];
			$this->_escape_char = ($this->_quoted_identifier) ? '"' : array('[', ']');
		}
		return $this->conn_id;
	}
	public function db_select($database = ''){
		if($database === '') {
			$database = $this->database;
		}
		if($this->_execute('USE ' . $this->escape_identifiers($database))) {
			$this->database = $database;
			$this->data_cache = array();
			return TRUE;
		}
		return FALSE;
	}
	protected function _execute($sql){
		return ($this->scrollable === FALSE OR $this->is_write_type($sql)) ? sqlsrv_query($this->conn_id, $sql) : sqlsrv_query($this->conn_id, $sql, NULL, array('Scrollable' => $this->scrollable));
	}
	protected function _trans_begin(){
		return sqlsrv_begin_transaction($this->conn_id);
	}
	protected function _trans_commit(){
		return sqlsrv_commit($this->conn_id);
	}
	protected function _trans_rollback(){
		return sqlsrv_rollback($this->conn_id);
	}
	public function affected_rows(){
		return sqlsrv_rows_affected($this->result_id);
	}
	public function insert_id(){
		return $this->query('SELECT SCOPE_IDENTITY() AS insert_id')->row()->insert_id;
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		if(!$this->conn_id OR ($info = sqlsrv_server_info($this->conn_id)) === FALSE) {
			return FALSE;
		}
		return $this->data_cache['version'] = $info['SQLServerVersion'];
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT ' . $this->escape_identifiers('name') . ' FROM ' . $this->escape_identifiers('sysobjects') . ' WHERE ' . $this->escape_identifiers('type') . " = 'U'";
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
			$sql .= ' AND ' . $this->escape_identifiers('name') . " LIKE '" . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_escape_like_str, $this->_escape_like_chr);
		}
		return $sql . ' ORDER BY ' . $this->escape_identifiers('name');
	}
	protected function _list_columns($table = ''){
		return 'SELECT COLUMN_NAME
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = ' . $this->escape(strtoupper($table));
	}
	public function field_data($table){
		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = ' . $this->escape(strtoupper($table));
		if(($query = $this->query($sql)) === FALSE) {
			return FALSE;
		}
		$query = $query->result_object();
		$retval = array();
		for($i = 0, $c = count($query); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $query[$i]->COLUMN_NAME;
			$retval[$i]->type = $query[$i]->DATA_TYPE;
			$retval[$i]->max_length = ($query[$i]->CHARACTER_MAXIMUM_LENGTH > 0) ? $query[$i]->CHARACTER_MAXIMUM_LENGTH : $query[$i]->NUMERIC_PRECISION;
			$retval[$i]->default = $query[$i]->COLUMN_DEFAULT;
		}
		return $retval;
	}
	public function error(){
		$error = array('code' => '00000', 'message' => '');
		$sqlsrv_errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if(!is_array($sqlsrv_errors)) {
			return $error;
		}
		$sqlsrv_error = array_shift($sqlsrv_errors);
		if(isset($sqlsrv_error['SQLSTATE'])) {
			$error['code'] = isset($sqlsrv_error['code']) ? $sqlsrv_error['SQLSTATE'] . '/' . $sqlsrv_error['code'] : $sqlsrv_error['SQLSTATE'];
		} elseif(isset($sqlsrv_error['code'])) {
			$error['code'] = $sqlsrv_error['code'];
		}
		if(isset($sqlsrv_error['message'])) {
			$error['message'] = $sqlsrv_error['message'];
		}
		return $error;
	}
	protected function _update($table, $values){
		$this->qb_limit = FALSE;
		$this->qb_orderby = array();
		return parent::_update($table, $values);
	}
	protected function _truncate($table){
		return 'TRUNCATE TABLE ' . $table;
	}
	protected function _delete($table){
		if($this->qb_limit) {
			return 'WITH ci_delete AS (SELECT TOP ' . $this->qb_limit . ' * FROM ' . $table . $this->_compile_wh('qb_where') . ') DELETE FROM ci_delete';
		}
		return parent::_delete($table);
	}
	protected function _limit($sql){
		if(version_compare($this->version(), '11', '>=')) {
			empty($this->qb_orderby) && $sql .= ' ORDER BY 1';
			return $sql . ' OFFSET ' . (int)$this->qb_offset . ' ROWS FETCH NEXT ' . $this->qb_limit . ' ROWS ONLY';
		}
		$limit = $this->qb_offset + $this->qb_limit;
		if($this->qb_offset && !empty($this->qb_orderby)) {
			$orderby = $this->_compile_order_by();
			$sql = trim(substr($sql, 0, strrpos($sql, $orderby)));
			if(count($this->qb_select) === 0) {
				$select = '*';
			} else {
				$select = array();
				$field_regexp = ($this->_quoted_identifier) ? '("[^\"]+")' : '(\[[^\]]+\])';
				for($i = 0, $c = count($this->qb_select); $i < $c; $i++) {
					$select[] = preg_match('/(?:\s|\.)' . $field_regexp . '$/i', $this->qb_select[$i], $m) ? $m[1] : $this->qb_select[$i];
				}
				$select = implode(', ', $select);
			}
			return 'SELECT ' . $select . " FROM (\n\n" . preg_replace('/^(SELECT( DISTINCT)?)/i', '\\1 ROW_NUMBER() OVER(' . trim($orderby) . ') AS ' . $this->escape_identifiers('CI_rownum') . ', ', $sql) . "\n\n) " . $this->escape_identifiers('CI_subquery') . "\nWHERE " . $this->escape_identifiers('CI_rownum') . ' BETWEEN ' . ($this->qb_offset + 1) . ' AND ' . $limit;
		}
		return preg_replace('/(^\SELECT (DISTINCT)?)/i', '\\1 TOP ' . $limit . ' ', $sql);
	}
	protected function _insert_batch($table, $keys, $values){
		if(version_compare($this->version(), '10', '>=')) {
			return parent::_insert_batch($table, $keys, $values);
		}
		return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
	}
	protected function _close(){
		sqlsrv_close($this->conn_id);
	}
}
