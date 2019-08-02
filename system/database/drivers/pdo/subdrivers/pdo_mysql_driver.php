<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_DB_pdo_mysql_driver extends CI_DB_pdo_driver{
	public $subdriver = 'mysql';
	public $compress = FALSE;
	public $stricton;
	protected $_escape_char = '`';
	public function __construct($params){
		parent::__construct($params);
		if(empty($this->dsn)) {
			$this->dsn = 'mysql:host=' . (empty($this->hostname) ? '127.0.0.1' : $this->hostname);
			empty($this->port) OR $this->dsn .= ';port=' . $this->port;
			empty($this->database) OR $this->dsn .= ';dbname=' . $this->database;
			empty($this->char_set) OR $this->dsn .= ';charset=' . $this->char_set;
		} elseif(!empty($this->char_set) && strpos($this->dsn, 'charset=', 6) === FALSE) {
			$this->dsn .= ';charset=' . $this->char_set;
		}
	}
	public function db_connect($persistent = FALSE){
		if(isset($this->stricton)) {
			if($this->stricton) {
				$sql = 'CONCAT(@@sql_mode, ",", "STRICT_ALL_TABLES")';
			} else {
				$sql = 'REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                                        @@sql_mode,
                                        "STRICT_ALL_TABLES,", ""),
                                        ",STRICT_ALL_TABLES", ""),
                                        "STRICT_ALL_TABLES", ""),
                                        "STRICT_TRANS_TABLES,", ""),
                                        ",STRICT_TRANS_TABLES", ""),
                                        "STRICT_TRANS_TABLES", "")';
			}
			if(!empty($sql)) {
				if(empty($this->options[PDO::MYSQL_ATTR_INIT_COMMAND])) {
					$this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET SESSION sql_mode = ' . $sql;
				} else {
					$this->options[PDO::MYSQL_ATTR_INIT_COMMAND] .= ', @@session.sql_mode = ' . $sql;
				}
			}
		}
		if($this->compress === TRUE) {
			$this->options[PDO::MYSQL_ATTR_COMPRESS] = TRUE;
		}
		if(is_array($this->encrypt)) {
			$ssl = array();
			empty($this->encrypt['ssl_key']) OR $ssl[PDO::MYSQL_ATTR_SSL_KEY] = $this->encrypt['ssl_key'];
			empty($this->encrypt['ssl_cert']) OR $ssl[PDO::MYSQL_ATTR_SSL_CERT] = $this->encrypt['ssl_cert'];
			empty($this->encrypt['ssl_ca']) OR $ssl[PDO::MYSQL_ATTR_SSL_CA] = $this->encrypt['ssl_ca'];
			empty($this->encrypt['ssl_capath']) OR $ssl[PDO::MYSQL_ATTR_SSL_CAPATH] = $this->encrypt['ssl_capath'];
			empty($this->encrypt['ssl_cipher']) OR $ssl[PDO::MYSQL_ATTR_SSL_CIPHER] = $this->encrypt['ssl_cipher'];
			empty($ssl) OR $this->options += $ssl;
		}
		if(($pdo = parent::db_connect($persistent)) !== FALSE && !empty($ssl) && version_compare($pdo->getAttribute(PDO::ATTR_CLIENT_VERSION), '5.7.3', '<=') && empty($pdo->query("SHOW STATUS LIKE 'ssl_cipher'")->fetchObject()->Value)) {
			$message = 'PDO_MYSQL was configured for an SSL connection, but got an unencrypted connection instead!';
			log_message('error', $message);
			return ($this->db->db_debug) ? $this->db->display_error($message, '', TRUE) : FALSE;
		}
		return $pdo;
	}
	public function db_select($database = ''){
		if($database === '') {
			$database = $this->database;
		}
		if(FALSE !== $this->simple_query('USE ' . $this->escape_identifiers($database))) {
			$this->database = $database;
			$this->data_cache = array();
			return TRUE;
		}
		return FALSE;
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
	protected function _trans_begin(){
		$this->conn_id->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
		return $this->conn_id->beginTransaction();
	}
	protected function _trans_commit(){
		if($this->conn_id->commit()) {
			$this->conn_id->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
			return TRUE;
		}
		return FALSE;
	}
	protected function _trans_rollback(){
		if($this->conn_id->rollBack()) {
			$this->conn_id->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
			return TRUE;
		}
		return FALSE;
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
