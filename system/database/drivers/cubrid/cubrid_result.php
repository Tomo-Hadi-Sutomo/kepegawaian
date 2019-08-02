<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_cubrid_result extends CI_DB_result{
	public function num_rows(){
		return is_int($this->num_rows) ? $this->num_rows : $this->num_rows = cubrid_num_rows($this->result_id);
	}
	public function num_fields(){
		return cubrid_num_fields($this->result_id);
	}
	public function list_fields(){
		return cubrid_column_names($this->result_id);
	}
	public function field_data(){
		$retval = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = cubrid_field_name($this->result_id, $i);
			$retval[$i]->type = cubrid_field_type($this->result_id, $i);
			$retval[$i]->max_length = cubrid_field_len($this->result_id, $i);
			$retval[$i]->primary_key = (int)(strpos(cubrid_field_flags($this->result_id, $i), 'primary_key') !== FALSE);
		}
		return $retval;
	}
	public function free_result(){
		if(is_resource($this->result_id) OR (get_resource_type($this->result_id) === 'Unknown' && preg_match('/Resource id #/', strval($this->result_id)))) {
			cubrid_close_request($this->result_id);
			$this->result_id = FALSE;
		}
	}
	public function data_seek($n = 0){
		return cubrid_data_seek($this->result_id, $n);
	}
	protected function _fetch_assoc(){
		return cubrid_fetch_assoc($this->result_id);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return cubrid_fetch_object($this->result_id, $class_name);
	}
}
