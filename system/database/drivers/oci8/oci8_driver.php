<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_oci8_driver extends CI_DB{
	public $dbdriver = 'oci8';
	public $stmt_id;
	public $curs_id;
	public $commit_mode = OCI_COMMIT_ON_SUCCESS;
	public $limit_used;
	protected $_reset_stmt_id = TRUE;
	protected $_reserved_identifiers = array('*', 'rownum');
	protected $_random_keyword = array('ASC', 'ASC');
	protected $_count_string = 'SELECT COUNT(1) AS ';
	public function __construct($params){
		parent::__construct($params);
		$valid_dsns = array('tns' => '/^\(DESCRIPTION=(\(.+\)){2,}\)$/', 'ec' => '/^(\/\/)?[a-z0-9.:_-]+(:[1-9][0-9]{0,4})?(\/[a-z0-9$_]+)?(:[^\/])?(\/[a-z0-9$_]+)?$/i', 'in' => '/^[a-z0-9$_]+$/i');
		$this->dsn = str_replace(array("\n", "\r", "\t", ' '), '', $this->dsn);
		if($this->dsn !== '') {
			foreach($valid_dsns as $regexp) {
				if(preg_match($regexp, $this->dsn)) {
					return;
				}
			}
		}
		$this->hostname = str_replace(array("\n", "\r", "\t", ' '), '', $this->hostname);
		if(preg_match($valid_dsns['tns'], $this->hostname)) {
			$this->dsn = $this->hostname;
			return;
		} elseif($this->hostname !== '' && strpos($this->hostname, '/') === FALSE && strpos($this->hostname, ':') === FALSE && ((!empty($this->port) && ctype_digit($this->port)) OR $this->database !== '')) {
			$this->dsn = $this->hostname . ((!empty($this->port) && ctype_digit($this->port)) ? ':' . $this->port : '') . ($this->database !== '' ? '/' . ltrim($this->database, '/') : '');
			if(preg_match($valid_dsns['ec'], $this->dsn)) {
				return;
			}
		}
		if(preg_match($valid_dsns['ec'], $this->hostname) OR preg_match($valid_dsns['in'], $this->hostname)) {
			$this->dsn = $this->hostname;
			return;
		}
		$this->database = str_replace(array("\n", "\r", "\t", ' '), '', $this->database);
		foreach($valid_dsns as $regexp) {
			if(preg_match($regexp, $this->database)) {
				return;
			}
		}
		$this->dsn = '';
	}
	public function db_connect($persistent = FALSE){
		$func = ($persistent === TRUE) ? 'oci_pconnect' : 'oci_connect';
		return empty($this->char_set) ? $func($this->username, $this->password, $this->dsn) : $func($this->username, $this->password, $this->dsn, $this->char_set);
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		if(!$this->conn_id OR ($version_string = oci_server_version($this->conn_id)) === FALSE) {
			return FALSE;
		} elseif(preg_match('#Release\s(\d+(?:\.\d+)+)#', $version_string, $match)) {
			return $this->data_cache['version'] = $match[1];
		}
		return FALSE;
	}
	protected function _execute($sql){
		if($this->_reset_stmt_id === TRUE) {
			$this->stmt_id = oci_parse($this->conn_id, $sql);
		}
		oci_set_prefetch($this->stmt_id, 1000);
		return oci_execute($this->stmt_id, $this->commit_mode);
	}
	public function get_cursor(){
		return $this->curs_id = oci_new_cursor($this->conn_id);
	}
	public function stored_procedure($package, $procedure, array $params){
		if($package === '' OR $procedure === '') {
			log_message('error', 'Invalid query: ' . $package . '.' . $procedure);
			return ($this->db_debug) ? $this->display_error('db_invalid_query') : FALSE;
		}
		$sql = 'BEGIN ' . $package . '.' . $procedure . '(';
		$have_cursor = FALSE;
		foreach($params as $param) {
			$sql .= $param['name'] . ',';
			if(isset($param['type']) && $param['type'] === OCI_B_CURSOR) {
				$have_cursor = TRUE;
			}
		}
		$sql = trim($sql, ',') . '); END;';
		$this->_reset_stmt_id = FALSE;
		$this->stmt_id = oci_parse($this->conn_id, $sql);
		$this->_bind_params($params);
		$result = $this->query($sql, FALSE, $have_cursor);
		$this->_reset_stmt_id = TRUE;
		return $result;
	}
	protected function _bind_params($params){
		if(!is_array($params) OR !is_resource($this->stmt_id)) {
			return;
		}
		foreach($params as $param) {
			foreach(array('name', 'value', 'type', 'length') as $val) {
				if(!isset($param[$val])) {
					$param[$val] = '';
				}
			}
			oci_bind_by_name($this->stmt_id, $param['name'], $param['value'], $param['length'], $param['type']);
		}
	}
	protected function _trans_begin(){
		$this->commit_mode = OCI_NO_AUTO_COMMIT;
		return TRUE;
	}
	protected function _trans_commit(){
		$this->commit_mode = OCI_COMMIT_ON_SUCCESS;
		return oci_commit($this->conn_id);
	}
	protected function _trans_rollback(){
		$this->commit_mode = OCI_COMMIT_ON_SUCCESS;
		return oci_rollback($this->conn_id);
	}
	public function affected_rows(){
		return oci_num_rows($this->stmt_id);
	}
	public function insert_id(){
		return $this->display_error('db_unsupported_function');
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT "TABLE_NAME" FROM "ALL_TABLES"';
		if($prefix_limit !== FALSE && $this->dbprefix !== '') {
			return $sql . ' WHERE "TABLE_NAME" LIKE \'' . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		if(strpos($table, '.') !== FALSE) {
			sscanf($table, '%[^.].%s', $owner, $table);
		} else {
			$owner = $this->username;
		}
		return 'SELECT COLUMN_NAME FROM ALL_TAB_COLUMNS
			WHERE UPPER(OWNER) = ' . $this->escape(strtoupper($owner)) . '
				AND UPPER(TABLE_NAME) = ' . $this->escape(strtoupper($table));
	}
	public function field_data($table){
		if(strpos($table, '.') !== FALSE) {
			sscanf($table, '%[^.].%s', $owner, $table);
		} else {
			$owner = $this->username;
		}
		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHAR_LENGTH, DATA_PRECISION, DATA_LENGTH, DATA_DEFAULT, NULLABLE
			FROM ALL_TAB_COLUMNS
			WHERE UPPER(OWNER) = ' . $this->escape(strtoupper($owner)) . '
				AND UPPER(TABLE_NAME) = ' . $this->escape(strtoupper($table));
		if(($query = $this->query($sql)) === FALSE) {
			return FALSE;
		}
		$query = $query->result_object();
		$retval = array();
		for($i = 0, $c = count($query); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $query[$i]->COLUMN_NAME;
			$retval[$i]->type = $query[$i]->DATA_TYPE;
			$length = ($query[$i]->CHAR_LENGTH > 0) ? $query[$i]->CHAR_LENGTH : $query[$i]->DATA_PRECISION;
			if($length === NULL) {
				$length = $query[$i]->DATA_LENGTH;
			}
			$retval[$i]->max_length = $length;
			$default = $query[$i]->DATA_DEFAULT;
			if($default === NULL && $query[$i]->NULLABLE === 'N') {
				$default = '';
			}
			$retval[$i]->default = $default;
		}
		return $retval;
	}
	public function error(){
		if(is_resource($this->curs_id)) {
			$error = oci_error($this->curs_id);
		} elseif(is_resource($this->stmt_id)) {
			$error = oci_error($this->stmt_id);
		} elseif(is_resource($this->conn_id)) {
			$error = oci_error($this->conn_id);
		} else {
			$error = oci_error();
		}
		return is_array($error) ? $error : array('code' => '', 'message' => '');
	}
	protected function _insert_batch($table, $keys, $values){
		$keys = implode(', ', $keys);
		$sql = "INSERT ALL\n";
		for($i = 0, $c = count($values); $i < $c; $i++) {
			$sql .= '	INTO ' . $table . ' (' . $keys . ') VALUES ' . $values[$i] . "\n";
		}
		return $sql . 'SELECT * FROM dual';
	}
	protected function _truncate($table){
		return 'TRUNCATE TABLE ' . $table;
	}
	protected function _delete($table){
		if($this->qb_limit) {
			$this->where('rownum <= ', $this->qb_limit, FALSE);
			$this->qb_limit = FALSE;
		}
		return parent::_delete($table);
	}
	protected function _limit($sql){
		if(version_compare($this->version(), '12.1', '>=')) {
			empty($this->qb_orderby) && $sql .= ' ORDER BY 1';
			return $sql . ' OFFSET ' . (int)$this->qb_offset . ' ROWS FETCH NEXT ' . $this->qb_limit . ' ROWS ONLY';
		}
		$this->limit_used = TRUE;
		return 'SELECT * FROM (SELECT inner_query.*, rownum rnum FROM (' . $sql . ') inner_query WHERE rownum < ' . ($this->qb_offset + $this->qb_limit + 1) . ')' . ($this->qb_offset ? ' WHERE rnum >= ' . ($this->qb_offset + 1) : '');
	}
	protected function _close(){
		oci_close($this->conn_id);
	}
}
