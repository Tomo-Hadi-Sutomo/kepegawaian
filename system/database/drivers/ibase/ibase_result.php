<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_ibase_result extends CI_DB_result{
	public function num_fields(){
		return ibase_num_fields($this->result_id);
	}
	public function list_fields(){
		$field_names = array();
		for($i = 0, $num_fields = $this->num_fields(); $i < $num_fields; $i++) {
			$info = ibase_field_info($this->result_id, $i);
			$field_names[] = $info['name'];
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$info = ibase_field_info($this->result_id, $i);
			$retval[$i] = new stdClass();
			$retval[$i]->name = $info['name'];
			$retval[$i]->type = $info['type'];
			$retval[$i]->max_length = $info['length'];
		}
		return $retval;
	}
	public function free_result(){
		ibase_free_result($this->result_id);
	}
	protected function _fetch_assoc(){
		return ibase_fetch_assoc($this->result_id, IBASE_FETCH_BLOBS);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		$row = ibase_fetch_object($this->result_id, IBASE_FETCH_BLOBS);
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
