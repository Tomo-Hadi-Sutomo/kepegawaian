<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_4d_forge extends CI_DB_pdo_forge{
	protected $_create_database = 'CREATE SCHEMA %s';
	protected $_drop_database = 'DROP SCHEMA %s';
	protected $_create_table_if = 'CREATE TABLE IF NOT EXISTS';
	protected $_rename_table = FALSE;
	protected $_drop_table_if = 'DROP TABLE IF EXISTS';
	protected $_unsigned = array('INT16' => 'INT', 'SMALLINT' => 'INT', 'INT' => 'INT64', 'INT32' => 'INT64');
	protected $_default = FALSE;
	protected function _alter_table($alter_type, $table, $field){
		if(in_array($alter_type, array('ADD', 'DROP'), TRUE)) {
			return parent::_alter_table($alter_type, $table, $field);
		}
		return FALSE;
	}
	protected function _process_column($field){
		return $this->db->escape_identifiers($field['name']) . ' ' . $field['type'] . $field['length'] . $field['null'] . $field['unique'] . $field['auto_increment'];
	}
	protected function _attr_type(&$attributes){
		switch(strtoupper($attributes['TYPE'])) {
			case 'TINYINT':
				$attributes['TYPE'] = 'SMALLINT';
				$attributes['UNSIGNED'] = FALSE;
				return;
			case 'MEDIUMINT':
				$attributes['TYPE'] = 'INTEGER';
				$attributes['UNSIGNED'] = FALSE;
				return;
			case 'INTEGER':
				$attributes['TYPE'] = 'INT';
				return;
			case 'BIGINT':
				$attributes['TYPE'] = 'INT64';
				return;
			default:
				return;
		}
	}
	protected function _attr_unique(&$attributes, &$field){
		if(!empty($attributes['UNIQUE']) && $attributes['UNIQUE'] === TRUE) {
			$field['unique'] = ' UNIQUE';
			$field['null'] = ' NOT NULL';
		}
	}
	protected function _attr_auto_increment(&$attributes, &$field){
		if(!empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === TRUE) {
			if(stripos($field['type'], 'int') !== FALSE) {
				$field['auto_increment'] = ' AUTO_INCREMENT';
			} elseif(strcasecmp($field['type'], 'UUID') === 0) {
				$field['auto_increment'] = ' AUTO_GENERATE';
			}
		}
	}
}
