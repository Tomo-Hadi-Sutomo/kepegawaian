<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_sqlsrv_result extends CI_DB_result{
	public $scrollable;
	public function __construct(&$driver_object){
		parent::__construct($driver_object);
		$this->scrollable = $driver_object->scrollable;
	}
	public function num_rows(){
		if(!in_array($this->scrollable, array(FALSE, SQLSRV_CURSOR_FORWARD, SQLSRV_CURSOR_DYNAMIC), TRUE)) {
			return parent::num_rows();
		}
		return is_int($this->num_rows) ? $this->num_rows : $this->num_rows = sqlsrv_num_rows($this->result_id);
	}
	public function num_fields(){
		return @sqlsrv_num_fields($this->result_id);
	}
	public function list_fields(){
		$field_names = array();
		foreach(sqlsrv_field_metadata($this->result_id) as $offset => $field) {
			$field_names[] = $field['Name'];
		}
		return $field_names;
	}
	public function field_data(){
		$retval = array();
		foreach(sqlsrv_field_metadata($this->result_id) as $i => $field) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $field['Name'];
			$retval[$i]->type = $field['Type'];
			$retval[$i]->max_length = $field['Size'];
		}
		return $retval;
	}
	public function free_result(){
		if(is_resource($this->result_id)) {
			sqlsrv_free_stmt($this->result_id);
			$this->result_id = FALSE;
		}
	}
	protected function _fetch_assoc(){
		return sqlsrv_fetch_array($this->result_id, SQLSRV_FETCH_ASSOC);
	}
	protected function _fetch_object($class_name = 'stdClass'){
		return sqlsrv_fetch_object($this->result_id, $class_name);
	}
}
