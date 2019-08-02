<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_ibase_driver extends CI_DB{
	public $dbdriver = 'ibase';
	protected $_random_keyword = array('RAND()', 'RAND()');
	protected $_ibase_trans;
	public function db_connect($persistent = FALSE){
		return ($persistent === TRUE) ? ibase_pconnect($this->hostname . ':' . $this->database, $this->username, $this->password, $this->char_set) : ibase_connect($this->hostname . ':' . $this->database, $this->username, $this->password, $this->char_set);
	}
	public function version(){
		if(isset($this->data_cache['version'])) {
			return $this->data_cache['version'];
		}
		if(($service = ibase_service_attach($this->hostname, $this->username, $this->password))) {
			$this->data_cache['version'] = ibase_server_info($service, IBASE_SVC_SERVER_VERSION);
			ibase_service_detach($service);
			return $this->data_cache['version'];
		}
		return FALSE;
	}
	public function affected_rows(){
		return ibase_affected_rows($this->conn_id);
	}
	public function insert_id($generator_name, $inc_by = 0){
		return ibase_gen_id('"' . $generator_name . '"', $inc_by);
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
	public function error(){
		return array('code' => ibase_errcode(), 'message' => ibase_errmsg());
	}
	protected function _execute($sql){
		return ibase_query(isset($this->_ibase_trans) ? $this->_ibase_trans : $this->conn_id, $sql);
	}
	protected function _trans_begin(){
		if(($trans_handle = ibase_trans($this->conn_id)) === FALSE) {
			return FALSE;
		}
		$this->_ibase_trans = $trans_handle;
		return TRUE;
	}
	protected function _trans_commit(){
		if(ibase_commit($this->_ibase_trans)) {
			$this->_ibase_trans = NULL;
			return TRUE;
		}
		return FALSE;
	}
	protected function _trans_rollback(){
		if(ibase_rollback($this->_ibase_trans)) {
			$this->_ibase_trans = NULL;
			return TRUE;
		}
		return FALSE;
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SELECT TRIM("RDB$RELATION_NAME") AS TABLE_NAME FROM "RDB$RELATIONS" WHERE "RDB$RELATION_NAME" NOT LIKE \'RDB$%\' AND "RDB$RELATION_NAME" NOT LIKE \'MON$%\'';
		if($prefix_limit !== FALSE && $this->dbprefix !== '') {
			return $sql . ' AND TRIM("RDB$RELATION_NAME") AS TABLE_NAME LIKE \'' . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SELECT TRIM("RDB$FIELD_NAME") AS COLUMN_NAME FROM "RDB$RELATION_FIELDS" WHERE "RDB$RELATION_NAME" = ' . $this->escape($table);
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
			$select = 'FIRST ' . $this->qb_limit . ($this->qb_offset ? ' SKIP ' . $this->qb_offset : '');
		} else {
			$select = 'ROWS ' . ($this->qb_offset ? $this->qb_offset . ' TO ' . ($this->qb_limit + $this->qb_offset) : $this->qb_limit);
		}
		return preg_replace('`SELECT`i', 'SELECT ' . $select, $sql, 1);
	}
	protected function _insert_batch($table, $keys, $values){
		return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
	}
	protected function _close(){
		ibase_close($this->conn_id);
	}
}
