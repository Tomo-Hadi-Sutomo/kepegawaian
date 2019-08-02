<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_firebird_driver extends CI_DB_pdo_driver{
	public $subdriver = 'firebird';
	protected $_random_keyword = array('RAND()', 'RAND()');
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'firebird:';
			if(!empty($this->database)) {
				$this->dsn .= 'dbname=' . $this->database;
			} elseif(!empty($this->hostname)) {
				$this->dsn .= 'dbname=' . $this->hostname;
			}
			empty($this->char_set) OR $this->dsn .= ';charset=' . $this->char_set;
			empty($this->role) OR $this->dsn .= ';role=' . $this->role;
		} elseif(!empty($this->char_set) && strpos($this->dsn, 'charset=', 9) === FALSE) {
			$this->dsn .= ';charset=' . $this->char_set;
		}
	}
	public function field_data($table){
		$sql = 'SELECT "rfields"."RDB$FIELD_NAME" AS "name",
				CASE "fields"."RDB$FIELD_TYPE"
					WHEN 7 THEN \'SMALLINT\'
					WHEN 8 THEN \'INTEGER\'
					WHEN 9 THEN \'QUAD\'
					WHEN 10 THEN \'FLOAT\'
					WHEN 11 THEN \'DFLOAT\'
					WHEN 12 THEN \'DATE\'
					WHEN 13 THEN \'TIME\'
					WHEN 14 THEN \'CHAR\'
					WHEN 16 THEN \'INT64\'
					WHEN 27 THEN \'DOUBLE\'
					WHEN 35 THEN \'TIMESTAMP\'
					WHEN 37 THEN \'VARCHAR\'
					WHEN 40 THEN \'CSTRING\'
					WHEN 261 THEN \'BLOB\'
					ELSE NULL
				END AS "type",
				"fields"."RDB$FIELD_LENGTH" AS "max_length",
				"rfields"."RDB$DEFAULT_VALUE" AS "default"
			FROM "RDB$RELATION_FIELDS" "rfields"
				JOIN "RDB$FIELDS" "fields" ON "rfields"."RDB$FIELD_SOURCE" = "fields"."RDB$FIELD_NAME"
			WHERE "rfields"."RDB$RELATION_NAME" = ' . $this->escape($table) . '
			ORDER BY "rfields"."RDB$FIELD_POSITION"';
		return (($query = $this->query($sql)) !== FALSE) ? $query->result_object() : FALSE;
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT "RDB$RELATION_NAME" FROM "RDB$RELATIONS" WHERE "RDB$RELATION_NAME" NOT LIKE \'RDB$%\' AND "RDB$RELATION_NAME" NOT LIKE \'MON$%\'';
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
			return $sql . ' AND "RDB$RELATION_NAME" LIKE \'' . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SELECT "RDB$FIELD_NAME" FROM "RDB$RELATION_FIELDS" WHERE "RDB$RELATION_NAME" = ' . $this->escape($table);
	}
	protected function _update($table, $values){
		$this->qb_limit = FALSE;
		return parent::_update($table, $values);
	}
	protected function _truncate($table){
		return 'DELETE FROM ' . $table;
	}
	protected function _delete($table){
		$this->qb_limit = FALSE;
		return parent::_delete($table);
	}
	protected function _limit($sql){
		if(stripos($this->version(), 'firebird') !== FALSE) {
			$select = 'FIRST ' . $this->qb_limit . ($this->qb_offset > 0 ? ' SKIP ' . $this->qb_offset : '');
		} else {
			$select = 'ROWS ' . ($this->qb_offset > 0 ? $this->qb_offset . ' TO ' . ($this->qb_limit + $this->qb_offset) : $this->qb_limit);
		}
		return preg_replace('`SELECT`i', 'SELECT ' . $select, $sql);
	}
	protected function _insert_batch($table, $keys, $values){
		return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
	}
}
