<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_mysqli_result extends CI_DB_result{
	public function num_rows(){
		return is_int($this->num_rows) ? $this->num_rows : $this->num_rows = $this->result_id->num_rows;
	}
	public function num_fields(){
		return $this->result_id->field_count;
	}
	public function list_fields(){
		$field_names = array();
		$this->result_id->field_seek(0);
		while($field = $this->result_id->fetch_field()) {
			$field_names[] = $field->name;
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		$field_data = $this->result_id->fetch_fields();
		for($i = 0, $c = count($field_data); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $field_data[$i]->name;
			$retval[$i]->type = $field_data[$i]->type;
			$retval[$i]->max_length = $field_data[$i]->max_length;
			$retval[$i]->primary_key = (int)($field_data[$i]->flags & 2);
			$retval[$i]->default = $field_data[$i]->def;
		}
		return $retval;
	}
	public function free_result(){
		if(is_object($this->result_id)) {
			$this->result_id->free();
			$this->result_id = FALSE;
		}
	}
	public function data_seek($n = 0){
		return $this->result_id->data_seek($n);
	}
	protected function _fetch_assoc(){
		return $this->result_id->fetch_assoc();
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return $this->result_id->fetch_object($class_name);
	}
}
