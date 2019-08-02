<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_cubrid_driver extends CI_DB_pdo_driver{
	public $subdriver = 'cubrid';
	protected $_escape_char = '`';
	protected $_random_keyword = array('RANDOM()', 'RANDOM(%d)');
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'cubrid:host=' . (empty($this->hostname) ? '127.0.0.1' : $this->hostname);
			empty($this->port) OR $this->dsn .= ';port=' . $this->port;
			empty($this->database) OR $this->dsn .= ';dbname=' . $this->database;
			empty($this->char_set) OR $this->dsn .= ';charset=' . $this->char_set;
		}
	}
	public function field_data($table){
		if(($query = $this->query('SHOW COLUMNS FROM ' . $this->protect_identifiers($table, TRUE, NULL, FALSE))) === FALSE) {
			return FALSE;
		}
		$query = $query->result_object();
		$retval = array();
		for($i = 0, $c = count($query); $i < $c; $i++) {
			$retval[$i] = new stdClass();
			$retval[$i]->name = $query[$i]->Field;
			sscanf($query[$i]->Type, '%[a-z](%d)', $retval[$i]->type, $retval[$i]->max_length);
			$retval[$i]->default = $query[$i]->Default;
			$retval[$i]->primary_key = (int)($query[$i]->Key === 'PRI');
		}
		return $retval;
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = 'SHOW TABLES';
		if($prefix_limit === TRUE && $this->dbprefix !== '') {
			return $sql . " LIKE '" . $this->escape_like_str($this->dbprefix) . "%'";
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SHOW COLUMNS FROM ' . $this->protect_identifiers($table, TRUE, NULL, FALSE);
	}
	protected function _truncate($table){
		return 'TRUNCATE ' . $table;
	}
	protected function _from_tables(){
		if(!empty($this->qb_join) && count($this->qb_from) > 1) {
			return '(' . implode(', ', $this->qb_from) . ')';
		}
		return implode(', ', $this->qb_from);
	}
}
