<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_odbc_forge extends CI_DB_pdo_forge{
	protected $_unsigned = FALSE;
	protected function _attr_auto_increment(&$attributes, &$field){
	}
}
