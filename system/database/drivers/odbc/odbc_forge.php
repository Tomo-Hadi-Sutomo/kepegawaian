<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_odbc_forge extends CI_DB_forge{
	protected $_create_table_if = FALSE;
	protected $_drop_table_if = FALSE;
	protected $_unsigned = FALSE;
	protected function _attr_auto_increment(&$attributes, &$field){
	}
}
