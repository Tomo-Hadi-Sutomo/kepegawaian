<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_URI{
	public $keyval = array();
	public $uri_string = '';
	public $segments = array();
	public $rsegments = array();
	protected $_permitted_uri_chars;
	public function __construct(){
		$this->config =& load_class('Config', 'core');
		if(is_cli() OR $this->config->item('enable_query_strings') !== TRUE) {
			$this->_permitted_uri_chars = $this->config->item('permitted_uri_chars');
			if(is_cli()) {
				$uri = $this->_parse_argv();
			} else {
				$protocol = $this->config->item('uri_protocol');
				empty($protocol) && $protocol = 'REQUEST_URI';
				switch($protocol) {
					case 'AUTO':
					case 'REQUEST_URI':
						$uri = $this->_parse_request_uri();
						break;
					case 'QUERY_STRING':
						$uri = $this->_parse_query_string();
						break;
					case 'PATH_INFO':
					default:
						$uri = isset($_SERVER[$protocol]) ? $_SERVER[$protocol] : $this->_parse_request_uri();
						break;
				}
			}
			$this->_set_uri_string($uri);
		}
		log_message('info', 'URI Class Initialized');
	}
	protected function _set_uri_string($str){
		$this->uri_string = trim(remove_invisible_characters($str, FALSE), '/');
		if($this->uri_string !== '') {
			if(($suffix = (string)$this->config->item('url_suffix')) !== '') {
				$slen = strlen($suffix);
				if(substr($this->uri_string, -$slen) === $suffix) {
					$this->uri_string = substr($this->uri_string, 0, -$slen);
				}
			}
			$this->segments[0] = NULL;
			foreach(explode('/', trim($this->uri_string, '/')) as $val) {
				$val = trim($val);
				$this->filter_uri($val);
				if($val !== '') {
					$this->segments[] = $val;
				}
			}
			unset($this->segments[0]);
		}
	}
	protected function _parse_request_uri(){
		if(!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
			return '';
		}
		$uri = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
		$query = isset($uri['query']) ? $uri['query'] : '';
		$uri = isset($uri['path']) ? $uri['path'] : '';
		if(isset($_SERVER['SCRIPT_NAME'][0])) {
			if(strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
				$uri = (string)substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			} elseif(strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
				$uri = (string)substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}
		}
		if(trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
			$query = explode('?', $query, 2);
			$uri = $query[0];
			$_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
		} else {
			$_SERVER['QUERY_STRING'] = $query;
		}
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		if($uri === '/' OR $uri === '') {
			return '/';
		}
		return $this->_remove_relative_directory($uri);
	}
	protected function _parse_query_string(){
		$uri = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
		if(trim($uri, '/') === '') {
			return '';
		} elseif(strncmp($uri, '/', 1) === 0) {
			$uri = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
			$uri = $uri[0];
		}
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		return $this->_remove_relative_directory($uri);
	}
	protected function _parse_argv(){
		$args = array_slice($_SERVER['argv'], 1);
		return $args ? implode('/', $args) : '';
	}
	protected function _remove_relative_directory($uri){
		$uris = array();
		$tok = strtok($uri, '/');
		while($tok !== FALSE) {
			if((!empty($tok) OR $tok === '0') && $tok !== '..') {
				$uris[] = $tok;
			}
			$tok = strtok('/');
		}
		return implode('/', $uris);
	}
	public function filter_uri(&$str){
		if(!empty($str) && !empty($this->_permitted_uri_chars) && !preg_match('/^[' . $this->_permitted_uri_chars . ']+$/i' . (UTF8_ENABLED ? 'u' : ''), $str)) {
			show_error('The URI you submitted has disallowed characters.', 400);
		}
	}
	public function segment($n, $no_result = NULL){
		return isset($this->segments[$n]) ? $this->segments[$n] : $no_result;
	}
	public function rsegment($n, $no_result = NULL){
		return isset($this->rsegments[$n]) ? $this->rsegments[$n] : $no_result;
	}
	public function uri_to_assoc($n = 3, $default = array()){
		return $this->_uri_to_assoc($n, $default, 'segment');
	}
	public function ruri_to_assoc($n = 3, $default = array()){
		return $this->_uri_to_assoc($n, $default, 'rsegment');
	}
	protected function _uri_to_assoc($n = 3, $default = array(), $which = 'segment'){
		if(!is_numeric($n)) {
			return $default;
		}
		if(isset($this->keyval[$which], $this->keyval[$which][$n])) {
			return $this->keyval[$which][$n];
		}
		$total_segments = "total_{$which}s";
		$segment_array = "{$which}_array";
		if($this->$total_segments() < $n) {
			return (count($default) === 0) ? array() : array_fill_keys($default, NULL);
		}
		$segments = array_slice($this->$segment_array(), ($n - 1));
		$i = 0;
		$lastval = '';
		$retval = array();
		foreach($segments as $seg) {
			if($i % 2) {
				$retval[$lastval] = $seg;
			} else {
				$retval[$seg] = NULL;
				$lastval = $seg;
			}
			$i++;
		}
		if(count($default) > 0) {
			foreach($default as $val) {
				if(!array_key_exists($val, $retval)) {
					$retval[$val] = NULL;
				}
			}
		}
		isset($this->keyval[$which]) OR $this->keyval[$which] = array();
		$this->keyval[$which][$n] = $retval;
		return $retval;
	}
	public function assoc_to_uri($array){
		$temp = array();
		foreach((array)$array as $key => $val) {
			$temp[] = $key;
			$temp[] = $val;
		}
		return implode('/', $temp);
	}
	public function slash_segment($n, $where = 'trailing'){
		return $this->_slash_segment($n, $where, 'segment');
	}
	public function slash_rsegment($n, $where = 'trailing'){
		return $this->_slash_segment($n, $where, 'rsegment');
	}
	protected function _slash_segment($n, $where = 'trailing', $which = 'segment'){
		$leading = $trailing = '/';
		if($where === 'trailing') {
			$leading = '';
		} elseif($where === 'leading') {
			$trailing = '';
		}
		return $leading . $this->$which($n) . $trailing;
	}
	public function segment_array(){
		return $this->segments;
	}
	public function rsegment_array(){
		return $this->rsegments;
	}
	public function total_segments(){
		return count($this->segments);
	}
	public function total_rsegments(){
		return count($this->rsegments);
	}
	public function uri_string(){
		return $this->uri_string;
	}
	public function ruri_string(){
		return ltrim(load_class('Router', 'core')->directory, '/') . implode('/', $this->rsegments);
	}
}
