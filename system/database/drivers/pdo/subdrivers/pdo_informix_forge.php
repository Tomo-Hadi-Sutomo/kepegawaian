<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_informix_forge extends CI_DB_pdo_forge{
	protected $_rename_table = 'RENAME TABLE %s TO %s';
	protected $_unsigned = array('SMALLINT' => 'INTEGER', 'INT' => 'BIGINT', 'INTEGER' => 'BIGINT', 'REAL' => 'DOUBLE PRECISION', 'SMALLFLOAT' => 'DOUBLE PRECISION');
	protected $_default = ', ';
	protected function _alter_table($alter_type, $table, $field){
		if($alter_type === 'CHANGE') {
			$alter_type = 'MODIFY';
		}
		return parent::_alter_table($alter_type, $table, $field);
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
			case 'BYTE':
			case 'TEXT':
			case 'BLOB':
			case 'CLOB':
				$attributes['UNIQUE'] = FALSE;
				if(isset($attributes['DEFAULT'])) {
					unset($attributes['DEFAULT']);
				}
				return;
			default:
				return;
		}
	}
	protected function _attr_unique(&$attributes, &$field){
		if(!empty($attributes['UNIQUE']) && $attributes['UNIQUE'] === TRUE) {
			$field['unique'] = ' UNIQUE CONSTRAINT ' . $this->db->escape_identifiers($field['name']);
		}
	}
	protected function _attr_auto_increment(&$attributes, &$field){
	}
}
