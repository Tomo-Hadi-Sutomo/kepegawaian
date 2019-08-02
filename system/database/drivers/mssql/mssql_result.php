<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_mssql_result extends CI_DB_result{
	public function num_rows(){
		return is_int($this->num_rows) ? $this->num_rows : $this->num_rows = mssql_num_rows($this->result_id);
	}
	public function num_fields(){
		return mssql_num_fields($this->result_id);
	}
	public function list_fields(){
		$field_names = array();
		mssql_field_seek($this->result_id, 0);
		while($field = mssql_fetch_field($this->result_id)) {
			$field_names[] = $field->name;
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$field = mssql_fetch_field($this->result_id, $i);
			$retval[$i] = new stdClass();
			$retval[$i]->name = $field->name;
			$retval[$i]->type = $field->type;
			$retval[$i]->max_length = $field->max_length;
		}
		return $retval;
	}
	public function free_result(){
		if(is_resource($this->result_id)) {
			mssql_free_result($this->result_id);
			$this->result_id = FALSE;
		}
	}
	public function data_seek($n = 0){
		return mssql_data_seek($this->result_id, $n);
	}
	protected function _fetch_assoc(){
		return mssql_fetch_assoc($this->result_id);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		$row = mssql_fetch_object($this->result_id);
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
