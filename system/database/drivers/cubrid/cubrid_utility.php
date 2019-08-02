<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_cubrid_utility extends CI_DB_utility{
	public function list_databases(){
		if(isset($this->db->data_cache['db_names'])) {
			return $this->db->data_cache['db_names'];
		}
		return $this->db->data_cache['db_names'] = cubrid_list_dbs($this->db->conn_id);
	}
	protected function _backup($params = array()){
		return $this->db->display_error('db_unsupported_feature');
	}
}
