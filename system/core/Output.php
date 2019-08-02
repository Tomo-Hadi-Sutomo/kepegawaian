<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Output{
	public $final_output;
	public $cache_expiration = 0;
	public $headers = array();
	public $mimes = array();
	protected $mime_type = 'text/html';
	public $enable_profiler = FALSE;
	protected $_zlib_oc = FALSE;
	protected $_compress_output = FALSE;
	protected $_profiler_sections = array();
	public $parse_exec_vars = TRUE;
	protected static $func_override;
	public function __construct(){
		$this->_zlib_oc = (bool)ini_get('zlib.output_compression');
		$this->_compress_output = ($this->_zlib_oc === FALSE && config_item('compress_output') === TRUE && extension_loaded('zlib'));
		isset(self::$func_override) OR self::$func_override = (extension_loaded('mbstring') && ini_get('mbstring.func_override'));
		$this->mimes =& get_mimes();
		log_message('info', 'Output Class Initialized');
	}
	public function get_output(){
		return $this->final_output;
	}
	public function set_output($output){
		$this->final_output = $output;
		return $this;
	}
	public function append_output($output){
		$this->final_output .= $output;
		return $this;
	}
	public function set_header($header, $replace = TRUE){
		if($this->_zlib_oc && strncasecmp($header, 'content-length', 14) === 0) {
			return $this;
		}
		$this->headers[] = array($header, $replace);
		return $this;
	}
	public function set_content_type($mime_type, $charset = NULL){
		if(strpos($mime_type, '/') === FALSE) {
			$extension = ltrim($mime_type, '.');
			if(isset($this->mimes[$extension])) {
				$mime_type =& $this->mimes[$extension];
				if(is_array($mime_type)) {
					$mime_type = current($mime_type);
				}
			}
		}
		$this->mime_type = $mime_type;
		if(empty($charset)) {
			$charset = config_item('charset');
		}
		$header = 'Content-Type: ' . $mime_type . (empty($charset) ? '' : '; charset=' . $charset);
		$this->headers[] = array($header, TRUE);
		return $this;
	}
	public function get_content_type(){
		for($i = 0, $c = count($this->headers); $i < $c; $i++) {
			if(sscanf($this->headers[$i][0], 'Content-Type: %[^;]', $content_type) === 1) {
				return $content_type;
			}
		}
		return 'text/html';
	}
	public function get_header($header){
		$headers = array_merge(array_map('array_shift', $this->headers), headers_list());
		if(empty($headers) OR empty($header)) {
			return NULL;
		}
		for($c = count($headers) - 1; $c > -1; $c--) {
			if(strncasecmp($header, $headers[$c], $l = self::strlen($header)) === 0) {
				return trim(self::substr($headers[$c], $l + 1));
			}
		}
		return NULL;
	}
	public function set_status_header($code = 200, $text = ''){
		set_status_header($code, $text);
		return $this;
	}
	public function enable_profiler($val = TRUE){
		$this->enable_profiler = is_bool($val) ? $val : TRUE;
		return $this;
	}
	public function set_profiler_sections($sections){
		if(isset($sections['query_toggle_count'])) {
			$this->_profiler_sections['query_toggle_count'] = (int)$sections['query_toggle_count'];
			unset($sections['query_toggle_count']);
		}
		foreach($sections as $section => $enable) {
			$this->_profiler_sections[$section] = ($enable !== FALSE);
		}
		return $this;
	}
	public function cache($time){
		$this->cache_expiration = is_numeric($time) ? $time : 0;
		return $this;
	}
	public function _display($output = ''){
		$BM =& load_class('Benchmark', 'core');
		$CFG =& load_class('Config', 'core');
		if(class_exists('CI_Controller', FALSE)) {
			$CI =& get_instance();
		}
		if($output === '') {
			$output =& $this->final_output;
		}
		if($this->cache_expiration > 0 && isset($CI) && !method_exists($CI, '_output')) {
			$this->_write_cache($output);
		}
		$elapsed = $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end');
		if($this->parse_exec_vars === TRUE) {
			$memory = round(memory_get_usage() / 1024 / 1024, 2) . 'MB';
			$output = str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsed, $memory), $output);
		}
		if(isset($CI) && $this->_compress_output === TRUE && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
			ob_start('ob_gzhandler');
		}
		if(count($this->headers) > 0) {
			foreach($this->headers as $header) {
				@header($header[0], $header[1]);
			}
		}
		if(!isset($CI)) {
			if($this->_compress_output === TRUE) {
				if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
					header('Content-Encoding: gzip');
					header('Content-Length: ' . self::strlen($output));
				} else {
					$output = gzinflate(self::substr($output, 10, -8));
				}
			}
			echo $output;
			log_message('info', 'Final output sent to browser');
			log_message('debug', 'Total execution time: ' . $elapsed);
			return;
		}
		if($this->enable_profiler === TRUE) {
			$CI->load->library('profiler');
			if(!empty($this->_profiler_sections)) {
				$CI->profiler->set_sections($this->_profiler_sections);
			}
			$output = preg_replace('|</body>.*?</html>|is', '', $output, -1, $count) . $CI->profiler->run();
			if($count > 0) {
				$output .= '</body></html>';
			}
		}
		if(method_exists($CI, '_output')) {
			$CI->_output($output);
		} else {
			echo $output;
		}
		log_message('info', 'Final output sent to browser');
		log_message('debug', 'Total execution time: ' . $elapsed);
	}
	public function _write_cache($output){
		$CI =& get_instance();
		$path = $CI->config->item('cache_path');
		$cache_path = ($path === '') ? APPPATH . 'cache/' : $path;
		if(!is_dir($cache_path) OR !is_really_writable($cache_path)) {
			log_message('error', 'Unable to write cache file: ' . $cache_path);
			return;
		}
		$uri = $CI->config->item('base_url') . $CI->config->item('index_page') . $CI->uri->uri_string();
		if(($cache_query_string = $CI->config->item('cache_query_string')) && !empty($_SERVER['QUERY_STRING'])) {
			if(is_array($cache_query_string)) {
				$uri .= '?' . http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
			} else {
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
		$cache_path .= md5($uri);
		if(!$fp = @fopen($cache_path, 'w+b')) {
			log_message('error', 'Unable to write cache file: ' . $cache_path);
			return;
		}
		if(flock($fp, LOCK_EX)) {
			if($this->_compress_output === TRUE) {
				$output = gzencode($output);
				if($this->get_header('content-type') === NULL) {
					$this->set_content_type($this->mime_type);
				}
			}
			$expire = time() + ($this->cache_expiration * 60);
			$cache_info = serialize(array('expire' => $expire, 'headers' => $this->headers));
			$output = $cache_info . 'ENDCI--->' . $output;
			for($written = 0, $length = self::strlen($output); $written < $length; $written += $result) {
				if(($result = fwrite($fp, self::substr($output, $written))) === FALSE) {
					break;
				}
			}
			flock($fp, LOCK_UN);
		} else {
			log_message('error', 'Unable to secure a file lock for file at: ' . $cache_path);
			return;
		}
		fclose($fp);
		if(is_int($result)) {
			chmod($cache_path, 0640);
			log_message('debug', 'Cache file written: ' . $cache_path);
			$this->set_cache_header($_SERVER['REQUEST_TIME'], $expire);
		} else {
			@unlink($cache_path);
			log_message('error', 'Unable to write the complete cache content at: ' . $cache_path);
		}
	}
	public function _display_cache(&$CFG, &$URI){
		$cache_path = ($CFG->item('cache_path') === '') ? APPPATH . 'cache/' : $CFG->item('cache_path');
		$uri = $CFG->item('base_url') . $CFG->item('index_page') . $URI->uri_string;
		if(($cache_query_string = $CFG->item('cache_query_string')) && !empty($_SERVER['QUERY_STRING'])) {
			if(is_array($cache_query_string)) {
				$uri .= '?' . http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
			} else {
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
		$filepath = $cache_path . md5($uri);
		if(!file_exists($filepath) OR !$fp = @fopen($filepath, 'rb')) {
			return FALSE;
		}
		flock($fp, LOCK_SH);
		$cache = (filesize($filepath) > 0) ? fread($fp, filesize($filepath)) : '';
		flock($fp, LOCK_UN);
		fclose($fp);
		if(!preg_match('/^(.*)ENDCI--->/', $cache, $match)) {
			return FALSE;
		}
		$cache_info = unserialize($match[1]);
		$expire = $cache_info['expire'];
		$last_modified = filemtime($filepath);
		if($_SERVER['REQUEST_TIME'] >= $expire && is_really_writable($cache_path)) {
			@unlink($filepath);
			log_message('debug', 'Cache file has expired. File deleted.');
			return FALSE;
		} else {
			$this->set_cache_header($last_modified, $expire);
		}
		foreach($cache_info['headers'] as $header) {
			$this->set_header($header[0], $header[1]);
		}
		$this->_display(self::substr($cache, self::strlen($match[0])));
		log_message('debug', 'Cache file is current. Sending it to browser.');
		return TRUE;
	}
	public function delete_cache($uri = ''){
		$CI =& get_instance();
		$cache_path = $CI->config->item('cache_path');
		if($cache_path === '') {
			$cache_path = APPPATH . 'cache/';
		}
		if(!is_dir($cache_path)) {
			log_message('error', 'Unable to find cache path: ' . $cache_path);
			return FALSE;
		}
		if(empty($uri)) {
			$uri = $CI->uri->uri_string();
			if(($cache_query_string = $CI->config->item('cache_query_string')) && !empty($_SERVER['QUERY_STRING'])) {
				if(is_array($cache_query_string)) {
					$uri .= '?' . http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
				} else {
					$uri .= '?' . $_SERVER['QUERY_STRING'];
				}
			}
		}
		$cache_path .= md5($CI->config->item('base_url') . $CI->config->item('index_page') . ltrim($uri, '/'));
		if(!@unlink($cache_path)) {
			log_message('error', 'Unable to delete cache file for ' . $uri);
			return FALSE;
		}
		return TRUE;
	}
	public function set_cache_header($last_modified, $expiration){
		$max_age = $expiration - $_SERVER['REQUEST_TIME'];
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $last_modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$this->set_status_header(304);
			exit;
		} else {
			header('Pragma: public');
			header('Cache-Control: max-age=' . $max_age . ', public');
			header('Expires: ' . gmdate('D, d M Y H:i:s', $expiration) . ' GMT');
			header('Last-modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
		}
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
