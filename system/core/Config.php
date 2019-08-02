<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Config{
	public $config = array();
	public $is_loaded = array();
	public $_config_paths = array(APPPATH);
	public function __construct(){
		$this->config =& get_config();
		if(empty($this->config['base_url'])) {
			if(isset($_SERVER['SERVER_ADDR'])) {
				if(strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE) {
					$server_addr = '[' . $_SERVER['SERVER_ADDR'] . ']';
				} else {
					$server_addr = $_SERVER['SERVER_ADDR'];
				}
				$base_url = (is_https() ? 'https' : 'http') . '://' . $server_addr . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
			} else {
				$base_url = 'http://localhost/';
			}
			$this->set_item('base_url', $base_url);
		}
		log_message('info', 'Config Class Initialized');
	}
	public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE){
		$file = ($file === '') ? 'config' : str_replace('.php', '', $file);
		$loaded = FALSE;
		foreach($this->_config_paths as $path) {
			foreach(array($file, ENVIRONMENT . DIRECTORY_SEPARATOR . $file) as $location) {
				$file_path = $path . 'config/' . $location . '.php';
				if(in_array($file_path, $this->is_loaded, TRUE)) {
					return TRUE;
				}
				if(!file_exists($file_path)) {
					continue;
				}
				include($file_path);
				if(!isset($config) OR !is_array($config)) {
					if($fail_gracefully === TRUE) {
						return FALSE;
					}
					show_error('Your ' . $file_path . ' file does not appear to contain a valid configuration array.');
				}
				if($use_sections === TRUE) {
					$this->config[$file] = isset($this->config[$file]) ? array_merge($this->config[$file], $config) : $config;
				} else {
					$this->config = array_merge($this->config, $config);
				}
				$this->is_loaded[] = $file_path;
				$config = NULL;
				$loaded = TRUE;
				log_message('debug', 'Config file loaded: ' . $file_path);
			}
		}
		if($loaded === TRUE) {
			return TRUE;
		} elseif($fail_gracefully === TRUE) {
			return FALSE;
		}
		show_error('The configuration file ' . $file . '.php does not exist.');
	}
	public function item($item, $index = ''){
		if($index == '') {
			return isset($this->config[$item]) ? $this->config[$item] : NULL;
		}
		return isset($this->config[$index], $this->config[$index][$item]) ? $this->config[$index][$item] : NULL;
	}
	public function slash_item($item){
		if(!isset($this->config[$item])) {
			return NULL;
		} elseif(trim($this->config[$item]) === '') {
			return '';
		}
		return rtrim($this->config[$item], '/') . '/';
	}
	public function site_url($uri = '', $protocol = NULL){
		$base_url = $this->slash_item('base_url');
		if(isset($protocol)) {
			if($protocol === '') {
				$base_url = substr($base_url, strpos($base_url, '//'));
			} else {
				$base_url = $protocol . substr($base_url, strpos($base_url, '://'));
			}
		}
		if(empty($uri)) {
			return $base_url . $this->item('index_page');
		}
		$uri = $this->_uri_string($uri);
		if($this->item('enable_query_strings') === FALSE) {
			$suffix = isset($this->config['url_suffix']) ? $this->config['url_suffix'] : '';
			if($suffix !== '') {
				if(($offset = strpos($uri, '?')) !== FALSE) {
					$uri = substr($uri, 0, $offset) . $suffix . substr($uri, $offset);
				} else {
					$uri .= $suffix;
				}
			}
			return $base_url . $this->slash_item('index_page') . $uri;
		} elseif(strpos($uri, '?') === FALSE) {
			$uri = '?' . $uri;
		}
		return $base_url . $this->item('index_page') . $uri;
	}
	public function base_url($uri = '', $protocol = NULL){
		$base_url = $this->slash_item('base_url');
		if(isset($protocol)) {
			if($protocol === '') {
				$base_url = substr($base_url, strpos($base_url, '//'));
			} else {
				$base_url = $protocol . substr($base_url, strpos($base_url, '://'));
			}
		}
		return $base_url . $this->_uri_string($uri);
	}
	protected function _uri_string($uri){
		if($this->item('enable_query_strings') === FALSE) {
			is_array($uri) && $uri = implode('/', $uri);
			return ltrim($uri, '/');
		} elseif(is_array($uri)) {
			return http_build_query($uri);
		}
		return $uri;
	}
	public function system_url(){
		$x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));
		return $this->slash_item('base_url') . end($x) . '/';
	}
	public function set_item($item, $value){
		$this->config[$item] = $value;
	}
}
