<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class CI_DB_forge{
	public $fields = array();
	public $keys = array();
	public $primary_keys = array();
	public $db_char_set = '';
	protected $db;
	protected $_create_database = 'CREATE DATABASE %s';
	protected $_drop_database = 'DROP DATABASE %s';
	protected $_create_table = "%s %s (%s\n)";
	protected $_create_table_if = 'CREATE TABLE IF NOT EXISTS';
	protected $_create_table_keys = FALSE;
	protected $_drop_table_if = 'DROP TABLE IF EXISTS';
	protected $_rename_table = 'ALTER TABLE %s RENAME TO %s;';
	protected $_unsigned = TRUE;
	protected $_null = '';
	protected $_default = ' DEFAULT ';
	public function __construct(&$db){
		$this->db =& $db;
		log_message('info', 'Database Forge Class Initialized');
	}
	public function create_database($db_name){
		if($this->_create_database === FALSE) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		} elseif(!$this->db->query(sprintf($this->_create_database, $this->db->escape_identifiers($db_name), $this->db->char_set, $this->db->dbcollat))) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unable_to_drop') : FALSE;
		}
		if(!empty($this->db->data_cache['db_names'])) {
			$this->db->data_cache['db_names'][] = $db_name;
		}
		return TRUE;
	}
	public function drop_database($db_name){
		if($this->_drop_database === FALSE) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		} elseif(!$this->db->query(sprintf($this->_drop_database, $this->db->escape_identifiers($db_name)))) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unable_to_drop') : FALSE;
		}
		if(!empty($this->db->data_cache['db_names'])) {
			$key = array_search(strtolower($db_name), array_map('strtolower', $this->db->data_cache['db_names']), TRUE);
			if($key !== FALSE) {
				unset($this->db->data_cache['db_names'][$key]);
			}
		}
		return TRUE;
	}
	public function add_key($key, $primary = FALSE){
		if($primary === TRUE && is_array($key)) {
			foreach($key as $one) {
				$this->add_key($one, $primary);
			}
			return $this;
		}
		if($primary === TRUE) {
			$this->primary_keys[] = $key;
		} else {
			$this->keys[] = $key;
		}
		return $this;
	}
	public function add_field($field){
		if(is_string($field)) {
			if($field === 'id') {
				$this->add_field(array('id' => array('type' => 'INT', 'constraint' => 9, 'auto_increment' => TRUE)));
				$this->add_key('id', TRUE);
			} else {
				if(strpos($field, ' ') === FALSE) {
					show_error('Field information is required for that operation.');
				}
				$this->fields[] = $field;
			}
		}
		if(is_array($field)) {
			$this->fields = array_merge($this->fields, $field);
		}
		return $this;
	}
	public function create_table($table, $if_not_exists = FALSE, array $attributes = array()){
		if($table === '') {
			show_error('A table name is required for that operation.');
		} else {
			$table = $this->db->dbprefix . $table;
		}
		if(count($this->fields) === 0) {
			show_error('Field information is required.');
		}
		$sql = $this->_create_table($table, $if_not_exists, $attributes);
		if(is_bool($sql)) {
			$this->_reset();
			if($sql === FALSE) {
				return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
			}
		}
		if(($result = $this->db->query($sql)) !== FALSE) {
			isset($this->db->data_cache['table_names']) && $this->db->data_cache['table_names'][] = $table;
			if(!empty($this->keys)) {
				for($i = 0, $sqls = $this->_process_indexes($table), $c = count($sqls); $i < $c; $i++) {
					$this->db->query($sqls[$i]);
				}
			}
		}
		$this->_reset();
		return $result;
	}
	public function drop_table($table_name, $if_exists = FALSE){
		if($table_name === '') {
			return ($this->db->db_debug) ? $this->db->display_error('db_table_name_required') : FALSE;
		}
		if(($query = $this->_drop_table($this->db->dbprefix . $table_name, $if_exists)) === TRUE) {
			return TRUE;
		}
		$query = $this->db->query($query);
		if($query && !empty($this->db->data_cache['table_names'])) {
			$key = array_search(strtolower($this->db->dbprefix . $table_name), array_map('strtolower', $this->db->data_cache['table_names']), TRUE);
			if($key !== FALSE) {
				unset($this->db->data_cache['table_names'][$key]);
			}
		}
		return $query;
	}
	public function rename_table($table_name, $new_table_name){
		if($table_name === '' OR $new_table_name === '') {
			show_error('A table name is required for that operation.');
			return FALSE;
		} elseif($this->_rename_table === FALSE) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}
		$result = $this->db->query(sprintf($this->_rename_table, $this->db->escape_identifiers($this->db->dbprefix . $table_name), $this->db->escape_identifiers($this->db->dbprefix . $new_table_name)));
		if($result && !empty($this->db->data_cache['table_names'])) {
			$key = array_search(strtolower($this->db->dbprefix . $table_name), array_map('strtolower', $this->db->data_cache['table_names']), TRUE);
			if($key !== FALSE) {
				$this->db->data_cache['table_names'][$key] = $this->db->dbprefix . $new_table_name;
			}
		}
		return $result;
	}
	public function add_column($table, $field, $_after = NULL){
		is_array($field) OR $field = array($field);
		foreach(array_keys($field) as $k) {
			if($_after !== NULL && is_array($field[$k]) && !isset($field[$k]['after'])) {
				$field[$k]['after'] = $_after;
			}
			$this->add_field(array($k => $field[$k]));
		}
		$sqls = $this->_alter_table('ADD', $this->db->dbprefix . $table, $this->_process_fields());
		$this->_reset();
		if($sqls === FALSE) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}
		for($i = 0, $c = count($sqls); $i < $c; $i++) {
			if($this->db->query($sqls[$i]) === FALSE) {
				return FALSE;
			}
		}
		return TRUE;
	}
	public function drop_column($table, $column_name){
		$sql = $this->_alter_table('DROP', $this->db->dbprefix . $table, $column_name);
		if($sql === FALSE) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}
		return $this->db->query($sql);
	}
	public function modify_column($table, $field){
		is_array($field) OR $field = array($field);
		foreach(array_keys($field) as $k) {
			$this->add_field(array($k => $field[$k]));
		}
		if(count($this->fields) === 0) {
			show_error('Field information is required.');
		}
		$sqls = $this->_alter_table('CHANGE', $this->db->dbprefix . $table, $this->_process_fields());
		$this->_reset();
		if($sqls === FALSE) {
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}
		for($i = 0, $c = count($sqls); $i < $c; $i++) {
			if($this->db->query($sqls[$i]) === FALSE) {
				return FALSE;
			}
		}
		return TRUE;
	}
	protected function _create_table($table, $if_not_exists, $attributes){
		if($if_not_exists === TRUE && $this->_create_table_if === FALSE) {
			if($this->db->table_exists($table)) {
				return TRUE;
			} else {
				$if_not_exists = FALSE;
			}
		}
		$sql = ($if_not_exists) ? sprintf($this->_create_table_if, $this->db->escape_identifiers($table)) : 'CREATE TABLE';
		$columns = $this->_process_fields(TRUE);
		for($i = 0, $c = count($columns); $i < $c; $i++) {
			$columns[$i] = ($columns[$i]['_literal'] !== FALSE) ? "\n\t" . $columns[$i]['_literal'] : "\n\t" . $this->_process_column($columns[$i]);
		}
		$columns = implode(',', $columns) . $this->_process_primary_keys($table);
		if($this->_create_table_keys === TRUE) {
			$columns .= $this->_process_indexes($table);
		}
		$sql = sprintf($this->_create_table . '%s', $sql, $this->db->escape_identifiers($table), $columns, $this->_create_table_attr($attributes));
		return $sql;
	}
	protected function _create_table_attr($attributes){
		$sql = '';
		foreach(array_keys($attributes) as $key) {
			if(is_string($key)) {
				$sql .= ' ' . strtoupper($key) . ' ' . $attributes[$key];
			}
		}
		return $sql;
	}
	protected function _drop_table($table, $if_exists){
		$sql = 'DROP TABLE';
		if($if_exists) {
			if($this->_drop_table_if === FALSE) {
				if(!$this->db->table_exists($table)) {
					return TRUE;
				}
			} else {
				$sql = sprintf($this->_drop_table_if, $this->db->escape_identifiers($table));
			}
		}
		return $sql . ' ' . $this->db->escape_identifiers($table);
	}
	protected function _alter_table($alter_type, $table, $field){
		$sql = 'ALTER TABLE ' . $this->db->escape_identifiers($table) . ' ';
		if($alter_type === 'DROP') {
			return $sql . 'DROP COLUMN ' . $this->db->escape_identifiers($field);
		}
		$sql .= ($alter_type === 'ADD') ? 'ADD ' : $alter_type . ' COLUMN ';
		$sqls = array();
		for($i = 0, $c = count($field); $i < $c; $i++) {
			$sqls[] = $sql . ($field[$i]['_literal'] !== FALSE ? $field[$i]['_literal'] : $this->_process_column($field[$i]));
		}
		return $sqls;
	}
	protected function _process_fields($create_table = FALSE){
		$fields = array();
		foreach($this->fields as $key => $attributes) {
			if(is_int($key) && !is_array($attributes)) {
				$fields[] = array('_literal' => $attributes);
				continue;
			}
			$attributes = array_change_key_case($attributes, CASE_UPPER);
			if($create_table === TRUE && empty($attributes['TYPE'])) {
				continue;
			}
			isset($attributes['TYPE']) && $this->_attr_type($attributes);
			$field = array('name' => $key, 'new_name' => isset($attributes['NAME']) ? $attributes['NAME'] : NULL, 'type' => isset($attributes['TYPE']) ? $attributes['TYPE'] : NULL, 'length' => '', 'unsigned' => '', 'null' => '', 'unique' => '', 'default' => '', 'auto_increment' => '', '_literal' => FALSE);
			isset($attributes['TYPE']) && $this->_attr_unsigned($attributes, $field);
			if($create_table === FALSE) {
				if(isset($attributes['AFTER'])) {
					$field['after'] = $attributes['AFTER'];
				} elseif(isset($attributes['FIRST'])) {
					$field['first'] = (bool)$attributes['FIRST'];
				}
			}
			$this->_attr_default($attributes, $field);
			if(isset($attributes['NULL'])) {
				if($attributes['NULL'] === TRUE) {
					$field['null'] = empty($this->_null) ? '' : ' ' . $this->_null;
				} else {
					$field['null'] = ' NOT NULL';
				}
			} elseif($create_table === TRUE) {
				$field['null'] = ' NOT NULL';
			}
			$this->_attr_auto_increment($attributes, $field);
			$this->_attr_unique($attributes, $field);
			if(isset($attributes['COMMENT'])) {
				$field['comment'] = $this->db->escape($attributes['COMMENT']);
			}
			if(isset($attributes['TYPE']) && !empty($attributes['CONSTRAINT'])) {
				switch(strtoupper($attributes['TYPE'])) {
					case 'ENUM':
					case 'SET':
						$attributes['CONSTRAINT'] = $this->db->escape($attributes['CONSTRAINT']);
					default:
						$field['length'] = is_array($attributes['CONSTRAINT']) ? '(' . implode(',', $attributes['CONSTRAINT']) . ')' : '(' . $attributes['CONSTRAINT'] . ')';
						break;
				}
			}
			$fields[] = $field;
		}
		return $fields;
	}
	protected function _process_column($field){
		return $this->db->escape_identifiers($field['name']) . ' ' . $field['type'] . $field['length'] . $field['unsigned'] . $field['default'] . $field['null'] . $field['auto_increment'] . $field['unique'];
	}
	protected function _attr_type(&$attributes){
	}
	protected function _attr_unsigned(&$attributes, &$field){
		if(empty($attributes['UNSIGNED']) OR $attributes['UNSIGNED'] !== TRUE) {
			return;
		}
		$attributes['UNSIGNED'] = FALSE;
		if(is_array($this->_unsigned)) {
			foreach(array_keys($this->_unsigned) as $key) {
				if(is_int($key) && strcasecmp($attributes['TYPE'], $this->_unsigned[$key]) === 0) {
					$field['unsigned'] = ' UNSIGNED';
					return;
				} elseif(is_string($key) && strcasecmp($attributes['TYPE'], $key) === 0) {
					$field['type'] = $key;
					return;
				}
			}
			return;
		}
		$field['unsigned'] = ($this->_unsigned === TRUE) ? ' UNSIGNED' : '';
	}
	protected function _attr_default(&$attributes, &$field){
		if($this->_default === FALSE) {
			return;
		}
		if(array_key_exists('DEFAULT', $attributes)) {
			if($attributes['DEFAULT'] === NULL) {
				$field['default'] = empty($this->_null) ? '' : $this->_default . $this->_null;
				$attributes['NULL'] = TRUE;
				$field['null'] = empty($this->_null) ? '' : ' ' . $this->_null;
			} else {
				$field['default'] = $this->_default . $this->db->escape($attributes['DEFAULT']);
			}
		}
	}
	protected function _attr_unique(&$attributes, &$field){
		if(!empty($attributes['UNIQUE']) && $attributes['UNIQUE'] === TRUE) {
			$field['unique'] = ' UNIQUE';
		}
	}
	protected function _attr_auto_increment(&$attributes, &$field){
		if(!empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === TRUE && stripos($field['type'], 'int') !== FALSE) {
			$field['auto_increment'] = ' AUTO_INCREMENT';
		}
	}
	protected function _process_primary_keys($table){
		$sql = '';
		for($i = 0, $c = count($this->primary_keys); $i < $c; $i++) {
			if(!isset($this->fields[$this->primary_keys[$i]])) {
				unset($this->primary_keys[$i]);
			}
		}
		if(count($this->primary_keys) > 0) {
			$sql .= ",\n\tCONSTRAINT " . $this->db->escape_identifiers('pk_' . $table) . ' PRIMARY KEY(' . implode(', ', $this->db->escape_identifiers($this->primary_keys)) . ')';
		}
		return $sql;
	}
	protected function _process_indexes($table){
		$sqls = array();
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
			$sqls[] = 'CREATE INDEX ' . $this->db->escape_identifiers($table . '_' . implode('_', $this->keys[$i])) . ' ON ' . $this->db->escape_identifiers($table) . ' (' . implode(', ', $this->db->escape_identifiers($this->keys[$i])) . ');';
		}
		return $sqls;
	}
	protected function _reset(){
		$this->fields = $this->keys = $this->primary_keys = array();
	}
}
