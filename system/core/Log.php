<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Log{
	protected $_log_path;
	protected $_file_permissions = 0644;
	protected $_threshold = 1;
	protected $_threshold_array = array();
	protected $_date_fmt = 'Y-m-d H:i:s';
	protected $_file_ext;
	protected $_enabled = TRUE;
	protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4);
	protected static $func_override;
	public function __construct(){
		$config =& get_config();
		isset(self::$func_override) OR self::$func_override = (extension_loaded('mbstring') && ini_get('mbstring.func_override'));
		$this->_log_path = ($config['log_path'] !== '') ? $config['log_path'] : APPPATH . 'logs/';
		$this->_file_ext = (isset($config['log_file_extension']) && $config['log_file_extension'] !== '') ? ltrim($config['log_file_extension'], '.') : 'php';
		file_exists($this->_log_path) OR mkdir($this->_log_path, 0755, TRUE);
		if(!is_dir($this->_log_path) OR !is_really_writable($this->_log_path)) {
			$this->_enabled = FALSE;
		}
		if(is_numeric($config['log_threshold'])) {
			$this->_threshold = (int)$config['log_threshold'];
		} elseif(is_array($config['log_threshold'])) {
			$this->_threshold = 0;
			$this->_threshold_array = array_flip($config['log_threshold']);
		}
		if(!empty($config['log_date_format'])) {
			$this->_date_fmt = $config['log_date_format'];
		}
		if(!empty($config['log_file_permissions']) && is_int($config['log_file_permissions'])) {
			$this->_file_permissions = $config['log_file_permissions'];
		}
	}
	public function write_log($level, $msg){
		if($this->_enabled === FALSE) {
			return FALSE;
		}
		$level = strtoupper($level);
		if((!isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold)) && !isset($this->_threshold_array[$this->_levels[$level]])) {
			return FALSE;
		}
		$filepath = $this->_log_path . 'log-' . date('Y-m-d') . '.' . $this->_file_ext;
		$message = '';
		if(!file_exists($filepath)) {
			$newfile = TRUE;
			if($this->_file_ext === 'php') {
				$message .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
			}
		}
		if(!$fp = @fopen($filepath, 'ab')) {
			return FALSE;
		}
		flock($fp, LOCK_EX);
		if(strpos($this->_date_fmt, 'u') !== FALSE) {
			$microtime_full = microtime(TRUE);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
			$date = $date->format($this->_date_fmt);
		} else {
			$date = date($this->_date_fmt);
		}
		$message .= $this->_format_line($level, $date, $msg);
		for($written = 0, $length = self::strlen($message); $written < $length; $written += $result) {
			if(($result = fwrite($fp, self::substr($message, $written))) === FALSE) {
				break;
			}
		}
		flock($fp, LOCK_UN);
		fclose($fp);
		if(isset($newfile) && $newfile === TRUE) {
			chmod($filepath, $this->_file_permissions);
		}
		return is_int($result);
	}
	protected function _format_line($level, $date, $message){
		return $level . ' - ' . $date . ' --> ' . $message . "\n";
	}
	protected static function strlen($str){
		return (self::$func_override) ? mb_strlen($str, '8bit') : strlen($str);
	}
	protected static function substr($str, $start, $length = NULL){
		if(self::$func_override) {
			isset($length) OR $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
			return mb_substr($str, $start, $length, '8bit');
		}
		return isset($length) ? substr($str, $start, $length) : substr($str, $start);
	}
}
