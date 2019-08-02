<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_oci_driver extends CI_DB_pdo_driver{
	public $subdriver = 'oci';
	protected $_reserved_identifiers = array('*', 'rownum');
	protected $_random_keyword = array('ASC', 'ASC');
	protected $_count_string = 'SELECT COUNT(1) AS ';
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'oci:dbname=';
			if(empty($this->hostname) && empty($this->port)) {
				$this->dsn .= $this->database;
			} else {
				$this->dsn .= '//' . (empty($this->hostname) ? '127.0.0.1' : $this->hostname) . (empty($this->port) ? '' : ':' . $this->port) . '/';
				empty($this->database) OR $this->dsn .= $this->database;
			}
			empty($this->char_set) OR $this->dsn .= ';charset=' . $this->char_set;
		} elseif(!empty($this->char_set) && strpos($this->dsn, 'charset=', 4) === FALSE) {
			$this->dsn .= ';charset=' . $this->char_set;
		}
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		$version_string = parent::version();
		if(preg_match('#Release\s(?<version>\d+(?:\.\d+)+)#', $version_string, $match)) {
			return $this->data_cache['version'] = $match[1];
		}
		return FALSE;
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
			$retval[$i]->default = $query[$i]->COLUMN_DEFAULT;
		}
		return $retval;
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT "TABLE_NAME" FROM "ALL_TABLES"';
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
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
	protected function _insert_batch($table, $keys, $values){
		$keys = implode(', ', $keys);
		$sql = "INSERT ALL\n";
		for($i = 0, $c = count($values); $i < $c; $i++) {
			$sql .= '	INTO ' . $table . ' (' . $keys . ') VALUES ' . $values[$i] . "\n";
		}
		return $sql . 'SELECT * FROM dual';
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
		return 'SELECT * FROM (SELECT inner_query.*, rownum rnum FROM (' . $sql . ') inner_query WHERE rownum < ' . ($this->qb_offset + $this->qb_limit + 1) . ')' . ($this->qb_offset ? ' WHERE rnum >= ' . ($this->qb_offset + 1) : '');
	}
}
