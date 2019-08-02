<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_result{
	public $conn_id;
	public $result_id;
	public $result_array = array();
	public $result_object = array();
	public $custom_result_object = array();
	public $current_row = 0;
	public $num_rows;
	public $row_data;
	public function __construct(&$driver_object){
		$this->conn_id = $driver_object->conn_id;
		$this->result_id = $driver_object->result_id;
	}
	public function num_rows(){
		if(is_int($this->num_rows)) {
			return $this->num_rows;
		} elseif(count($this->result_array) > 0) {
			return $this->num_rows = count($this->result_array);
		} elseif(count($this->result_object) > 0) {
			return $this->num_rows = count($this->result_object);
		}
		return $this->num_rows = count($this->result_array());
	}
	public function result($type = 'object'){
		if($type === 'array') {
			return $this->result_array();
		} elseif($type === 'object') {
			return $this->result_object();
		} else {
			return $this->custom_result_object($type);
		}
	}
	public function custom_result_object($class_name){
		if(isset($this->custom_result_object[$class_name])) {
			return $this->custom_result_object[$class_name];
		} elseif(!$this->result_id OR $this->num_rows === 0) {
			return array();
		}
		$_data = NULL;
		if(($c = count($this->result_array)) > 0) {
			$_data = 'result_array';
		} elseif(($c = count($this->result_object)) > 0) {
			$_data = 'result_object';
		}
		if($_data !== NULL) {
			for($i = 0; $i < $c; $i++) {
				$this->custom_result_object[$class_name][$i] = new $class_name();
				foreach($this->{$_data}[$i] as $key => $value) {
					$this->custom_result_object[$class_name][$i]->$key = $value;
				}
			}
			return $this->custom_result_object[$class_name];
		}
		is_null($this->row_data) OR $this->data_seek(0);
		$this->custom_result_object[$class_name] = array();
		while($row = $this->_fetch_object($class_name)) {
			$this->custom_result_object[$class_name][] = $row;
		}
		return $this->custom_result_object[$class_name];
	}
	public function result_object(){
		if(count($this->result_object) > 0) {
			return $this->result_object;
		}
		if(!$this->result_id OR $this->num_rows === 0) {
			return array();
		}
		if(($c = count($this->result_array)) > 0) {
			for($i = 0; $i < $c; $i++) {
				$this->result_object[$i] = (object)$this->result_array[$i];
			}
			return $this->result_object;
		}
		is_null($this->row_data) OR $this->data_seek(0);
		while($row = $this->_fetch_object()) {
			$this->result_object[] = $row;
		}
		return $this->result_object;
	}
	public function result_array(){
		if(count($this->result_array) > 0) {
			return $this->result_array;
		}
		if(!$this->result_id OR $this->num_rows === 0) {
			return array();
		}
		if(($c = count($this->result_object)) > 0) {
			for($i = 0; $i < $c; $i++) {
				$this->result_array[$i] = (array)$this->result_object[$i];
			}
			return $this->result_array;
		}
		is_null($this->row_data) OR $this->data_seek(0);
		while($row = $this->_fetch_assoc()) {
			$this->result_array[] = $row;
		}
		return $this->result_array;
	}
	public function row($n = 0, $type = 'object'){
		if(!is_numeric($n)) {
			is_array($this->row_data) OR $this->row_data = $this->row_array(0);
			if(empty($this->row_data) OR !array_key_exists($n, $this->row_data)) {
				return NULL;
			}
			return $this->row_data[$n];
		}
		if($type === 'object') return $this->row_object($n); elseif($type === 'array') return $this->row_array($n);
		else return $this->custom_row_object($n, $type);
	}
	public function set_row($key, $value = NULL){
		if(!is_array($this->row_data)) {
			$this->row_data = $this->row_array(0);
		}
		if(is_array($key)) {
			foreach($key as $k => $v) {
				$this->row_data[$k] = $v;
			}
			return;
		}
		if($key !== '' && $value !== NULL) {
			$this->row_data[$key] = $value;
		}
	}
	public function custom_row_object($n, $type){
		isset($this->custom_result_object[$type]) OR $this->custom_result_object($type);
		if(count($this->custom_result_object[$type]) === 0) {
			return NULL;
		}
		if($n !== $this->current_row && isset($this->custom_result_object[$type][$n])) {
			$this->current_row = $n;
		}
		return $this->custom_result_object[$type][$this->current_row];
	}
	public function row_object($n = 0){
		$result = $this->result_object();
		if(count($result) === 0) {
			return NULL;
		}
		if($n !== $this->current_row && isset($result[$n])) {
			$this->current_row = $n;
		}
		return $result[$this->current_row];
	}
	public function row_array($n = 0){
		$result = $this->result_array();
		if(count($result) === 0) {
			return NULL;
		}
		if($n !== $this->current_row && isset($result[$n])) {
			$this->current_row = $n;
		}
		return $result[$this->current_row];
	}
	public function first_row($type = 'object'){
		$result = $this->result($type);
		return (count($result) === 0) ? NULL : $result[0];
	}
	public function last_row($type = 'object'){
		$result = $this->result($type);
		return (count($result) === 0) ? NULL : $result[count($result) - 1];
	}
	public function next_row($type = 'object'){
		$result = $this->result($type);
		if(count($result) === 0) {
			return NULL;
		}
		return isset($result[$this->current_row + 1]) ? $result[++$this->current_row] : NULL;
	}
	public function previous_row($type = 'object'){
		$result = $this->result($type);
		if(count($result) === 0) {
			return NULL;
		}
		if(isset($result[$this->current_row - 1])) {
			--$this->current_row;
		}
		return $result[$this->current_row];
	}
	public function unbuffered_row($type = 'object'){
		if($type === 'array') {
			return $this->_fetch_assoc();
		} elseif($type === 'object') {
			return $this->_fetch_object();
		}
		return $this->_fetch_object($type);
	}
	public function num_fields(){
		return 0;
	}
	public function list_fields(){
		return array();
	}
	public function field_data(){
		return array();
	}
	public function free_result(){
		$this->result_id = FALSE;
	}
	public function data_seek($n = 0){
		return FALSE;
	}
	protected function _fetch_assoc(){
		return array();
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return new $class_name();
	}
}
