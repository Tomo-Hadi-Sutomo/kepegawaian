<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Router{
	public $config;
	public $routes = array();
	public $class = '';
	public $method = 'index';
	public $directory;
	public $default_controller;
	public $translate_uri_dashes = FALSE;
	public $enable_query_strings = FALSE;
	public function __construct($routing = NULL){
		$this->config =& load_class('Config', 'core');
		$this->uri =& load_class('URI', 'core');
		$this->enable_query_strings = (!is_cli() && $this->config->item('enable_query_strings') === TRUE);
		is_array($routing) && isset($routing['directory']) && $this->set_directory($routing['directory']);
		$this->_set_routing();
		if(is_array($routing)) {
			empty($routing['controller']) OR $this->set_class($routing['controller']);
			empty($routing['function']) OR $this->set_method($routing['function']);
		}
		log_message('info', 'Router Class Initialized');
	}
	protected function _set_routing(){
		if(file_exists(APPPATH . 'config/routes.php')) {
			include(APPPATH . 'config/routes.php');
		}
		if(file_exists(APPPATH . 'config/' . ENVIRONMENT . '/routes.php')) {
			include(APPPATH . 'config/' . ENVIRONMENT . '/routes.php');
		}
		if(isset($route) && is_array($route)) {
			isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
			isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
			unset($route['default_controller'], $route['translate_uri_dashes']);
			$this->routes = $route;
		}
		if($this->enable_query_strings) {
			if(!isset($this->directory)) {
				$_d = $this->config->item('directory_trigger');
				$_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';
				if($_d !== '') {
					$this->uri->filter_uri($_d);
					$this->set_directory($_d);
				}
			}
			$_c = trim($this->config->item('controller_trigger'));
			if(!empty($_GET[$_c])) {
				$this->uri->filter_uri($_GET[$_c]);
				$this->set_class($_GET[$_c]);
				$_f = trim($this->config->item('function_trigger'));
				if(!empty($_GET[$_f])) {
					$this->uri->filter_uri($_GET[$_f]);
					$this->set_method($_GET[$_f]);
				}
				$this->uri->rsegments = array(1 => $this->class, 2 => $this->method);
			} else {
				$this->_set_default_controller();
			}
			return;
		}
		if($this->uri->uri_string !== '') {
			$this->_parse_routes();
		} else {
			$this->_set_default_controller();
		}
	}
	protected function _set_request($segments = array()){
		$segments = $this->_validate_request($segments);
		if(empty($segments)) {
			$this->_set_default_controller();
			return;
		}
		if($this->translate_uri_dashes === TRUE) {
			$segments[0] = str_replace('-', '_', $segments[0]);
			if(isset($segments[1])) {
				$segments[1] = str_replace('-', '_', $segments[1]);
			}
		}
		$this->set_class($segments[0]);
		if(isset($segments[1])) {
			$this->set_method($segments[1]);
		} else {
			$segments[1] = 'index';
		}
		array_unshift($segments, NULL);
		unset($segments[0]);
		$this->uri->rsegments = $segments;
	}
	protected function _set_default_controller(){
		if(empty($this->default_controller)) {
			show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
		}
		if(sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2) {
			$method = 'index';
		}
		if(!file_exists(APPPATH . 'controllers/' . $this->directory . ucfirst($class) . '.php')) {
			return;
		}
		$this->set_class($class);
		$this->set_method($method);
		$this->uri->rsegments = array(1 => $class, 2 => $method);
		log_message('debug', 'No URI present. Default controller set.');
	}
	protected function _validate_request($segments){
		$c = count($segments);
		$directory_override = isset($this->directory);
		while($c-- > 0) {
			$test = $this->directory . ucfirst($this->translate_uri_dashes === TRUE ? str_replace('-', '_', $segments[0]) : $segments[0]);
			if(!file_exists(APPPATH . 'controllers/' . $test . '.php') && $directory_override === FALSE && is_dir(APPPATH . 'controllers/' . $this->directory . $segments[0])) {
				$this->set_directory(array_shift($segments), TRUE);
				continue;
			}
			return $segments;
		}
		return $segments;
	}
	protected function _parse_routes(){
		$uri = implode('/', $this->uri->segments);
		$http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
		foreach($this->routes as $key => $val) {
			if(is_array($val)) {
				$val = array_change_key_case($val, CASE_LOWER);
				if(isset($val[$http_verb])) {
					$val = $val[$http_verb];
				} else {
					continue;
				}
			}
			$key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);
			if(preg_match('#^' . $key . '$#', $uri, $matches)) {
				if(!is_string($val) && is_callable($val)) {
					array_shift($matches);
					$val = call_user_func_array($val, $matches);
				} elseif(strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE) {
					$val = preg_replace('#^' . $key . '$#', $val, $uri);
				}
				$this->_set_request(explode('/', $val));
				return;
			}
		}
		$this->_set_request(array_values($this->uri->segments));
	}
	public function set_class($class){
		$this->class = str_replace(array('/', '.'), '', $class);
	}
	public function fetch_class(){
		return $this->class;
	}
	public function set_method($method){
		$this->method = $method;
	}
	public function fetch_method(){
		return $this->method;
	}
	public function set_directory($dir, $append = FALSE){
		if($append !== TRUE OR empty($this->directory)) {
			$this->directory = str_replace('.', '', trim($dir, '/')) . '/';
		} else {
			$this->directory .= str_replace('.', '', trim($dir, '/')) . '/';
		}
	}
	public function fetch_directory(){
		return $this->directory;
	}
}
