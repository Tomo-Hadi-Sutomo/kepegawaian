<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_sqlite_result extends CI_DB_result{
	public function num_rows(){
		return is_int($this->num_rows) ? $this->num_rows : $this->num_rows = @sqlite_num_rows($this->result_id);
	}
	public function num_fields(){
		return @sqlite_num_fields($this->result_id);
	}
	public function list_fields(){
		$field_names = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$field_names[$i] = sqlite_field_name($this->result_id, $i);
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = sqlite_field_name($this->result_id, $i);
			$retval[$i]->type = NULL;
			$retval[$i]->max_length = NULL;
		}
		return $retval;
	}
	public function data_seek($n = 0){
		return sqlite_seek($this->result_id, $n);
	}
	protected function _fetch_assoc(){
		return sqlite_fetch_array($this->result_id);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return sqlite_fetch_object($this->result_id, $class_name);
	}
}
