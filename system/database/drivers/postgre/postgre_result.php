<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_postgre_result extends CI_DB_result{
	public function num_rows(){
		return is_int($this->num_rows) ? $this->num_rows : $this->num_rows = pg_num_rows($this->result_id);
	}
	public function num_fields(){
		return pg_num_fields($this->result_id);
	}
	public function list_fields(){
		$field_names = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$field_names[] = pg_field_name($this->result_id, $i);
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = pg_field_name($this->result_id, $i);
			$retval[$i]->type = pg_field_type($this->result_id, $i);
			$retval[$i]->max_length = pg_field_size($this->result_id, $i);
		}
		return $retval;
	}
	public function free_result(){
		if(is_resource($this->result_id)) {
			pg_free_result($this->result_id);
			$this->result_id = FALSE;
		}
	}
	public function data_seek($n = 0){
		return pg_result_seek($this->result_id, $n);
	}
	protected function _fetch_assoc(){
		return pg_fetch_assoc($this->result_id);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return pg_fetch_object($this->result_id, NULL, $class_name);
	}
}
