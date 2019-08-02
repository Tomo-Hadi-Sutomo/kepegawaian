<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_4d_driver extends CI_DB_pdo_driver{
	public $subdriver = '4d';
	protected $_escape_char = array('[', ']');
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = '4D:host=' . (empty($this->hostname) ? '127.0.0.1' : $this->hostname);
			empty($this->port) OR $this->dsn .= ';port=' . $this->port;
			empty($this->database) OR $this->dsn .= ';dbname=' . $this->database;
			empty($this->char_set) OR $this->dsn .= ';charset=' . $this->char_set;
		} elseif(!empty($this->char_set) && strpos($this->dsn, 'charset=', 3) === FALSE) {
			$this->dsn .= ';charset=' . $this->char_set;
		}
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT ' . $this->escape_identifiers('TABLE_NAME') . ' FROM ' . $this->escape_identifiers('_USER_TABLES');
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
			$sql .= ' WHERE ' . $this->escape_identifiers('TABLE_NAME') . " LIKE '" . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SELECT ' . $this->escape_identifiers('COLUMN_NAME') . ' FROM ' . $this->escape_identifiers('_USER_COLUMNS') . ' WHERE ' . $this->escape_identifiers('TABLE_NAME') . ' = ' . $this->escape($table);
	}
	protected function _field_data($table){
		return 'SELECT * FROM ' . $this->protect_identifiers($table, TRUE, NULL, FALSE) . ' LIMIT 1';
	}
	protected function _update($table, $values){
		$this->qb_limit = FALSE;
		$this->qb_orderby = array();
		return parent::_update($table, $values);
	}
	protected function _delete($table){
		$this->qb_limit = FALSE;
		return parent::_delete($table);
	}
	protected function _limit($sql){
		return $sql . ' LIMIT ' . $this->qb_limit . ($this->qb_offset ? ' OFFSET ' . $this->qb_offset : '');
	}
}
