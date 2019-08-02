<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_mysql_result extends CI_DB_result{
	public function __construct(&$driver_object){
		parent::__construct($driver_object);
		$this->num_rows = mysql_num_rows($this->result_id);
	}
	public function num_rows(){
		return $this->num_rows;
	}
	public function num_fields(){
		return mysql_num_fields($this->result_id);
	}
	public function list_fields(){
		$field_names = array();
		mysql_field_seek($this->result_id, 0);
		while($field = mysql_fetch_field($this->result_id)) {
			$field_names[] = $field->name;
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		for($i = 0, $c = $this->num_fields(); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = mysql_field_name($this->result_id, $i);
			$retval[$i]->type = mysql_field_type($this->result_id, $i);
			$retval[$i]->max_length = mysql_field_len($this->result_id, $i);
			$retval[$i]->primary_key = (int)(strpos(mysql_field_flags($this->result_id, $i), 'primary_key') !== FALSE);
		}
		return $retval;
	}
	public function free_result(){
		if(is_resource($this->result_id)) {
			mysql_free_result($this->result_id);
			$this->result_id = FALSE;
		}
	}
	public function data_seek($n = 0){
		return $this->num_rows ? mysql_data_seek($this->result_id, $n) : FALSE;
	}
	protected function _fetch_assoc(){
		return mysql_fetch_assoc($this->result_id);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return mysql_fetch_object($this->result_id, $class_name);
	}
}
