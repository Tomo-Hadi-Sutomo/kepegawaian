<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_sqlite_driver extends CI_DB_pdo_driver{
	public $subdriver = 'sqlite';
	protected $_random_keyword = array('RANDOM()', 'RANDOM()');
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'sqlite:';
			if(empty($this->database) && empty($this->hostname)) {
				$this->database = ':memory:';
			}
			$this->database = empty($this->database) ? $this->hostname : $this->database;
		}
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
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\'';
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
			return $sql . ' AND "NAME" LIKE \'' . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _replace($table, $keys, $values){
		return 'INSERT OR ' . parent::_replace($table, $keys, $values);
	}
	protected function _truncate($table){
		return 'DELETE FROM ' . $table;
	}
}
