<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_mssql_utility extends CI_DB_utility{
	protected $_list_databases = 'EXEC sp_helpdb';
	protected $_optimize_table = 'ALTER INDEX all ON %s REORGANIZE';
	protected function _backup($params = array()){
		return $this->db->display_error('db_unsupported_feature');
	}
}
