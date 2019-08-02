<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_sqlsrv_forge extends CI_DB_forge{
	protected $_create_table_if = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE ID = object_id(N'%s') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\nCREATE TABLE";
	protected $_drop_table_if = "IF EXISTS (SELECT * FROM sysobjects WHERE ID = object_id(N'%s') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\nDROP TABLE";
	protected $_unsigned = array('TINYINT' => 'SMALLINT', 'SMALLINT' => 'INT', 'INT' => 'BIGINT', 'REAL' => 'FLOAT');
	protected function _alter_table($alter_type, $table, $field){
		if(in_array($alter_type, array('ADD', 'DROP'), TRUE)) {
			return parent::_alter_table($alter_type, $table, $field);
		}
		$sql = 'ALTER TABLE ' . $this->db->escape_identifiers($table) . ' ALTER COLUMN ';
		$sqls = array();
		for($i = 0, $c = count($field); $i < $c; $i++) {
			$sqls[] = $sql . $this->_process_column($field[$i]);
		}
		return $sqls;
	}
	protected function _attr_type(&$attributes){
		if(isset($attributes['CONSTRAINT']) && strpos($attributes['TYPE'], 'INT') !== FALSE) {
			unset($attributes['CONSTRAINT']);
		}
		switch(strtoupper($attributes['TYPE'])) {
			case 'MEDIUMINT':
				$attributes['TYPE'] = 'INTEGER';
				$attributes['UNSIGNED'] = FALSE;
				return;
			case 'INTEGER':
				$attributes['TYPE'] = 'INT';
				return;
			default:
				return;
		}
	}
	protected function _attr_auto_increment(&$attributes, &$field){
		if(!empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === TRUE && stripos($field['type'], 'int') !== FALSE) {
			$field['auto_increment'] = ' IDENTITY(1,1)';
		}
	}
}
