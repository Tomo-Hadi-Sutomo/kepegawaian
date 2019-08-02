<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_odbc_driver extends CI_DB_pdo_driver{
	public $subdriver = 'odbc';
	public $schema = 'public';
	protected $_escape_char = '';
	protected $_like_escape_str = " {escape '%s'} ";
	protected $_random_keyword = array('RND()', 'RND(%d)');
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'odbc:';
			if(empty($this->hostname) && empty($this->HOSTNAME) && empty($this->port) && empty($this->PORT)) {
				if(isset($this->DSN)) {
					$this->dsn .= 'DSN=' . $this->DSN;
				} elseif(!empty($this->database)) {
					$this->dsn .= 'DSN=' . $this->database;
				}
				return;
			}
			$this->dsn .= 'DRIVER=' . (isset($this->DRIVER) ? '{' . $this->DRIVER . '}' : '{IBM DB2 ODBC DRIVER}') . ';';
			if(isset($this->DATABASE)) {
				$this->dsn .= 'DATABASE=' . $this->DATABASE . ';';
			} elseif(!empty($this->database)) {
				$this->dsn .= 'DATABASE=' . $this->database . ';';
			}
			if(isset($this->HOSTNAME)) {
				$this->dsn .= 'HOSTNAME=' . $this->HOSTNAME . ';';
			} else {
				$this->dsn .= 'HOSTNAME=' . (empty($this->hostname) ? '127.0.0.1;' : $this->hostname . ';');
			}
			if(isset($this->PORT)) {
				$this->dsn .= 'PORT=' . $this->port . ';';
			} elseif(!empty($this->port)) {
				$this->dsn .= ';PORT=' . $this->port . ';';
			}
			$this->dsn .= 'PROTOCOL=' . (isset($this->PROTOCOL) ? $this->PROTOCOL . ';' : 'TCPIP;');
		}
	}
	public function is_write_type($sql){
		if(preg_match('#^(INSERT|UPDATE).*RETURNING\s.+(\,\s?.+)*$#is', $sql)) {
			return FALSE;
		}
		return parent::is_write_type($sql);
	}
	protected function _escape_str($str){
		$this->db->display_error('db_unsupported_feature');
	}
	protected function _list_tables($prefix_limit = FALSE){
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . $this->schema . "'";
		if($prefix_limit !== FALSE && $this->dbprefix !== '') {
			return $sql . " AND table_name LIKE '" . $this->escape_like_str($this->dbprefix) . "%' " . sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}
		return $sql;
	}
	protected function _list_columns($table = ''){
		return 'SELECT column_name FROM information_schema.columns WHERE table_name = ' . $this->escape($table);
	}
}
