<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_oci8_result extends CI_DB_result{
	public $stmt_id;
	public $curs_id;
	public $limit_used;
	public $commit_mode;
	public function __construct(&$driver_object){
		parent::__construct($driver_object);
		$this->stmt_id = $driver_object->stmt_id;
		$this->curs_id = $driver_object->curs_id;
		$this->limit_used = $driver_object->limit_used;
		$this->commit_mode =& $driver_object->commit_mode;
		$driver_object->stmt_id = FALSE;
	}
	public function num_fields(){
		$count = oci_num_fields($this->stmt_id);
		return ($this->limit_used) ? $count - 1 : $count;
	}
	public function list_fields(){
		$field_names = array();
		for($c = 1, $fieldCount = $this->num_fields(); $c <= $fieldCount; $c++) {
			$field_names[] = oci_field_name($this->stmt_id, $c);
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		for($c = 1, $fieldCount = $this->num_fields(); $c <= $fieldCount; $c++) {
			$F = new stdClass();
			$F->name = oci_field_name($this->stmt_id, $c);
			$F->type = oci_field_type($this->stmt_id, $c);
			$F->max_length = oci_field_size($this->stmt_id, $c);
			$retval[] = $F;
		}
		return $retval;
	}
	public function free_result(){
		if(is_resource($this->result_id)) {
			oci_free_statement($this->result_id);
			$this->result_id = FALSE;
		}
		if(is_resource($this->stmt_id)) {
			oci_free_statement($this->stmt_id);
		}
		if(is_resource($this->curs_id)) {
			oci_cancel($this->curs_id);
			$this->curs_id = NULL;
		}
	}
	protected function _fetch_assoc(){
		$id = ($this->curs_id) ? $this->curs_id : $this->stmt_id;
		return oci_fetch_assoc($id);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		$row = ($this->curs_id) ? oci_fetch_object($this->curs_id) : oci_fetch_object($this->stmt_id);
		if($class_name === 'stdClass' OR !$row) {
			return $row;
		}
		$class_name = new $class_name();
		foreach($row as $key => $value) {
			$class_name->$key = $value;
		}
		return $class_name;
	}
}
