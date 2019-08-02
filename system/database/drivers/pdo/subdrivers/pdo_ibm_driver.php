<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_ibm_driver extends CI_DB_pdo_driver{
	public $subdriver = 'ibm';
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'ibm:';
			if(empty($this->hostname) && empty($this->HOSTNAME) && empty($this->port) && empty($this->PORT)) {
				if(isset($this->DSN)) {
					$this->dsn .= 'DSN=' . $this->DSN;
				} elseif(!empty($this->database)) {
					$this->dsn .= 'DSN=' . $this->database;
				}
				return;
			}
			$this->dsn .= 'DRIVER=' . (isset($this->DRIVER) ? '{' . $this->DRIVER . '}' : '{IBM DB2 ODBC DRIVER}') . ';';
			if(isset($this->DATABASE)) {
				$this->dsn .= 'DATABASE=' . $this->DATABASE . ';';
			} elseif(!empty($this->database)) {
				$this->dsn .= 'DATABASE=' . $this->database . ';';
			}
			if(isset($this->HOSTNAME)) {
				$this->dsn .= 'HOSTNAME=' . $this->HOSTNAME . ';';
			} else {
				$this->dsn .= 'HOSTNAME=' . (empty($this->hostname) ? '127.0.0.1;' : $this->hostname . ';');
			}
			if(isset($this->PORT)) {
				$this->dsn .= 'PORT=' . $this->port . ';';
			} elseif(!empty($this->port)) {
				$this->dsn .= ';PORT=' . $this->port . ';';
			}
			$this->dsn .= 'PROTOCOL=' . (isset($this->PROTOCOL) ? $this->PROTOCOL . ';' : 'TCPIP;');
		}
	}
	public function field_data($table){
		$sql = 'SELECT "colname" AS "name", "typename" AS "type", "default" AS "default", "length" AS "max_length",
				CASE "keyseq" WHEN NULL THEN 0 ELSE 1 END AS "primary_key"
			FROM "syscat"."columns"
			WHERE LOWER("tabschema") = ' . $this->escape(strtolower($this->database)) . '
				AND LOWER("tabname") = ' . $this->escape(strtolower($table)) . '
			ORDER BY "colno"';
		return (($query = $this->query($sql)) !== FALSE) ? $query->result_object() : FALSE;
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT "tabname" FROM "syscat"."tables"
			WHERE "type" = \'T\' AND LOWER("tabschema") = ' . $this->escape(strtolower($this->database));
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
			$sql .= ' AND "tabname" LIKE \'' . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SELECT "colname" FROM "syscat"."columns"
			WHERE LOWER("tabschema") = ' . $this->escape(strtolower($this->database)) . '
				AND LOWER("tabname") = ' . $this->escape(strtolower($table));
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
		$sql .= ' FETCH FIRST ' . ($this->qb_limit + $this->qb_offset) . ' ROWS ONLY';
		return ($this->qb_offset) ? 'SELECT * FROM (' . $sql . ') WHERE rownum > ' . $this->qb_offset : $sql;
	}
}
