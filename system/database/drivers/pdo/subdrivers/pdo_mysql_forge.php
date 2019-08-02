<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_mysql_forge extends CI_DB_pdo_forge{
	protected $_create_database = 'CREATE DATABASE %s CHARACTER SET %s COLLATE %s';
	protected $_create_table_if = 'CREATE TABLE IF NOT EXISTS';
	protected $_create_table_keys = TRUE;
	protected $_drop_table_if = 'DROP TABLE IF EXISTS';
	protected $_unsigned = array('TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT', 'REAL', 'DOUBLE', 'DOUBLE PRECISION', 'FLOAT', 'DECIMAL', 'NUMERIC');
	protected $_null = 'NULL';
	protected function _create_table_attr($attributes){
		$sql = '';
		foreach(array_keys($attributes) as $key) {
			if(is_string($key)) {
				$sql .= ' ' . strtoupper($key) . ' = ' . $attributes[$key];
			}
		}
		if(!empty($this->db->char_set) && !strpos($sql, 'CHARACTER SET') && !strpos($sql, 'CHARSET')) {
			$sql .= ' DEFAULT CHARACTER SET = ' . $this->db->char_set;
		}
		if(!empty($this->db->dbcollat) && !strpos($sql, 'COLLATE')) {
			$sql .= ' COLLATE = ' . $this->db->dbcollat;
		}
		return $sql;
	}
	protected function _alter_table($alter_type, $table, $field){
		if($alter_type === 'DROP') {
			return parent::_alter_table($alter_type, $table, $field);
		}
		$sql = 'ALTER TABLE ' . $this->db->escape_identifiers($table);
		for($i = 0, $c = count($field); $i < $c; $i++) {
			if($field[$i]['_literal'] !== FALSE) {
				$field[$i] = ($alter_type === 'ADD') ? "\n\tADD " . $field[$i]['_literal'] : "\n\tMODIFY " . $field[$i]['_literal'];
			} else {
				if($alter_type === 'ADD') {
					$field[$i]['_literal'] = "\n\tADD ";
				} else {
					$field[$i]['_literal'] = empty($field[$i]['new_name']) ? "\n\tMODIFY " : "\n\tCHANGE ";
				}
				$field[$i] = $field[$i]['_literal'] . $this->_process_column($field[$i]);
			}
		}
		return array($sql . implode(',', $field));
	}
	protected function _process_column($field){
		$extra_clause = isset($field['after']) ? ' AFTER ' . $this->db->escape_identifiers($field['after']) : '';
		if(empty($extra_clause) && isset($field['first']) && $field['first'] === TRUE) {
			$extra_clause = ' FIRST';
		}
		return $this->db->escape_identifiers($field['name']) . (empty($field['new_name']) ? '' : ' ' . $this->db->escape_identifiers($field['new_name'])) . ' ' . $field['type'] . $field['length'] . $field['unsigned'] . $field['null'] . $field['default'] . $field['auto_increment'] . $field['unique'] . (empty($field['comment']) ? '' : ' COMMENT ' . $field['comment']) . $extra_clause;
	}
	protected function _process_indexes($table){
		$sql = '';
		for($i = 0, $c = count($this->keys); $i < $c; $i++) {
			if(is_array($this->keys[$i])) {
				for($i2 = 0, $c2 = count($this->keys[$i]); $i2 < $c2; $i2++) {
					if(!isset($this->fields[$this->keys[$i][$i2]])) {
						unset($this->keys[$i][$i2]);
						continue;
					}
				}
			} elseif(!isset($this->fields[$this->keys[$i]])) {
				unset($this->keys[$i]);
				continue;
			}
			is_array($this->keys[$i]) OR $this->keys[$i] = array($this->keys[$i]);
			$sql .= ",\n\tKEY " . $this->db->escape_identifiers(implode('_', $this->keys[$i])) . ' (' . implode(', ', $this->db->escape_identifiers($this->keys[$i])) . ')';
		}
		$this->keys = array();
		return $sql;
	}
}
