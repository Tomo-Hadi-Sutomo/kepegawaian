<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_oci8_forge extends CI_DB_forge{
	protected $_create_database = FALSE;
	protected $_create_table_if = FALSE;
	protected $_drop_database = FALSE;
	protected $_drop_table_if = FALSE;
	protected $_unsigned = FALSE;
	protected function _alter_table($alter_type, $table, $field){
		if($alter_type === 'DROP') {
			return parent::_alter_table($alter_type, $table, $field);
		} elseif($alter_type === 'CHANGE') {
			$alter_type = 'MODIFY';
		}
		$sql = 'ALTER TABLE ' . $this->db->escape_identifiers($table);
		$sqls = array();
		for($i = 0, $c = count($field); $i < $c; $i++) {
			if($field[$i]['_literal'] !== FALSE) {
				$field[$i] = "\n\t" . $field[$i]['_literal'];
			} else {
				$field[$i]['_literal'] = "\n\t" . $this->_process_column($field[$i]);
				if(!empty($field[$i]['comment'])) {
					$sqls[] = 'COMMENT ON COLUMN ' . $this->db->escape_identifiers($table) . '.' . $this->db->escape_identifiers($field[$i]['name']) . ' IS ' . $field[$i]['comment'];
				}
				if($alter_type === 'MODIFY' && !empty($field[$i]['new_name'])) {
					$sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escape_identifiers($field[$i]['name']) . ' ' . $this->db->escape_identifiers($field[$i]['new_name']);
				}
				$field[$i] = "\n\t" . $field[$i]['_literal'];
			}
		}
		$sql .= ' ' . $alter_type . ' ';
		$sql .= (count($field) === 1) ? $field[0] : '(' . implode(',', $field) . ')';
		array_unshift($sqls, $sql);
		return $sqls;
	}
	protected function _attr_auto_increment(&$attributes, &$field){
	}
	protected function _attr_type(&$attributes){
		switch(strtoupper($attributes['TYPE'])) {
			case 'TINYINT':
				$attributes['TYPE'] = 'NUMBER';
				return;
			case 'MEDIUMINT':
				$attributes['TYPE'] = 'NUMBER';
				return;
			case 'INT':
				$attributes['TYPE'] = 'NUMBER';
				return;
			case 'BIGINT':
				$attributes['TYPE'] = 'NUMBER';
				return;
			default:
				return;
		}
	}
}
