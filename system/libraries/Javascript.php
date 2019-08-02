<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Javascript{
	protected $_javascript_location = 'js';
	public function __construct($params = array()){
		$defaults = array('js_library_driver' => 'jquery', 'autoload' => TRUE);
		foreach($defaults as $key => $val) {
			if(isset($params[$key]) && $params[$key] !== '') {
				$defaults[$key] = $params[$key];
			}
		}
		extract($defaults);
		$this->CI =& get_instance();
		$this->CI->load->library('Javascript/' . $js_library_driver, array('autoload' => $autoload));
		$this->js =& $this->CI->$js_library_driver;
		log_message('info', 'Javascript Class Initialized and loaded. Driver used: ' . $js_library_driver);
	}
	public function blur($element = 'this', $js = ''){
		return $this->js->_blur($element, $js);
	}
	public function change($element = 'this', $js = ''){
		return $this->js->_change($element, $js);
	}
	public function click($element = 'this', $js = '', $ret_false = TRUE){
		return $this->js->_click($element, $js, $ret_false);
	}
	public function dblclick($element = 'this', $js = ''){
		return $this->js->_dblclick($element, $js);
	}
	public function error($element = 'this', $js = ''){
		return $this->js->_error($element, $js);
	}
	public function focus($element = 'this', $js = ''){
		return $this->js->_focus($element, $js);
	}
	public function hover($element = 'this', $over = '', $out = ''){
		return $this->js->_hover($element, $over, $out);
	}
	public function keydown($element = 'this', $js = ''){
		return $this->js->_keydown($element, $js);
	}
	public function keyup($element = 'this', $js = ''){
		return $this->js->_keyup($element, $js);
	}
	public function load($element = 'this', $js = ''){
		return $this->js->_load($element, $js);
	}
	public function mousedown($element = 'this', $js = ''){
		return $this->js->_mousedown($element, $js);
	}
	public function mouseout($element = 'this', $js = ''){
		return $this->js->_mouseout($element, $js);
	}
	public function mouseover($element = 'this', $js = ''){
		return $this->js->_mouseover($element, $js);
	}
	public function mouseup($element = 'this', $js = ''){
		return $this->js->_mouseup($element, $js);
	}
	public function output($js){
		return $this->js->_output($js);
	}
	public function ready($js){
		return $this->js->_document_ready($js);
	}
	public function resize($element = 'this', $js = ''){
		return $this->js->_resize($element, $js);
	}
	public function scroll($element = 'this', $js = ''){
		return $this->js->_scroll($element, $js);
	}
	public function unload($element = 'this', $js = ''){
		return $this->js->_unload($element, $js);
	}
	public function addClass($element = 'this', $class = ''){
		return $this->js->_addClass($element, $class);
	}
	public function animate($element = 'this', $params = array(), $speed = '', $extra = ''){
		return $this->js->_animate($element, $params, $speed, $extra);
	}
	public function fadeIn($element = 'this', $speed = '', $callback = ''){
		return $this->js->_fadeIn($element, $speed, $callback);
	}
	public function fadeOut($element = 'this', $speed = '', $callback = ''){
		return $this->js->_fadeOut($element, $speed, $callback);
	}
	public function slideUp($element = 'this', $speed = '', $callback = ''){
		return $this->js->_slideUp($element, $speed, $callback);
	}
	public function removeClass($element = 'this', $class = ''){
		return $this->js->_removeClass($element, $class);
	}
	public function slideDown($element = 'this', $speed = '', $callback = ''){
		return $this->js->_slideDown($element, $speed, $callback);
	}
	public function slideToggle($element = 'this', $speed = '', $callback = ''){
		return $this->js->_slideToggle($element, $speed, $callback);
	}
	public function hide($element = 'this', $speed = '', $callback = ''){
		return $this->js->_hide($element, $speed, $callback);
	}
	public function toggle($element = 'this'){
		return $this->js->_toggle($element);
	}
	public function toggleClass($element = 'this', $class = ''){
		return $this->js->_toggleClass($element, $class);
	}
	public function show($element = 'this', $speed = '', $callback = ''){
		return $this->js->_show($element, $speed, $callback);
	}
	public function compile($view_var = 'script_foot', $script_tags = TRUE){
		$this->js->_compile($view_var, $script_tags);
	}
	public function clear_compile(){
		$this->js->_clear_compile();
	}
	public function external($external_file = '', $relative = FALSE){
		if($external_file !== '') {
			$this->_javascript_location = $external_file;
		} elseif($this->CI->config->item('javascript_location') !== '') {
			$this->_javascript_location = $this->CI->config->item('javascript_location');
		}
		if($relative === TRUE OR strpos($external_file, 'http://') === 0 OR strpos($external_file, 'https://') === 0) {
			$str = $this->_open_script($external_file);
		} elseif(strpos($this->_javascript_location, 'http://') !== FALSE) {
			$str = $this->_open_script($this->_javascript_location . $external_file);
		} else {
			$str = $this->_open_script($this->CI->config->slash_item('base_url') . $this->_javascript_location . $external_file);
		}
		return $str . $this->_close_script();
	}
	public function inline($script, $cdata = TRUE){
		return $this->_open_script() . ($cdata ? "\n// <![CDATA[\n" . $script . "\n// ]]>\n" : "\n" . $script . "\n") . $this->_close_script();
	}
	public function update($element = 'this', $speed = '', $callback = ''){
		return $this->js->_updater($element, $speed, $callback);
	}
	public function generate_json($result = NULL, $match_array_type = FALSE){
		if($result !== NULL) {
			if(is_object($result)) {
				$json_result = is_callable(array($result, 'result_array')) ? $result->result_array() : (array)$result;
			} elseif(is_array($result)) {
				$json_result = $result;
			} else {
				return $this->_prep_args($result);
			}
		} else {
			return 'null';
		}
		$json = array();
		$_is_assoc = TRUE;
		if(!is_array($json_result) && empty($json_result)) {
			show_error('Generate JSON Failed - Illegal key, value pair.');
		} elseif($match_array_type) {
			$_is_assoc = $this->_is_associative_array($json_result);
		}
		foreach($json_result as $k => $v) {
			if($_is_assoc) {
				$json[] = $this->_prep_args($k, TRUE) . ':' . $this->generate_json($v, $match_array_type);
			} else {
				$json[] = $this->generate_json($v, $match_array_type);
			}
		}
		$json = implode(',', $json);
		return $_is_assoc ? '{' . $json . '}' : '[' . $json . ']';
	}
	protected function _open_script($src = ''){
		return '<script type="text/javascript" charset="' . strtolower($this->CI->config->item('charset')) . '"' . ($src === '' ? '>' : ' src="' . $src . '">');
	}
	protected function _close_script($extra = "\n"){
		return '</script>' . $extra;
	}
	protected function _is_associative_array($arr){
		foreach(array_keys($arr) as $key => $val) {
			if($key !== $val) {
				return TRUE;
			}
		}
		return FALSE;
	}
	protected function _prep_args($result, $is_key = FALSE){
		if($result === NULL) {
			return 'null';
		} elseif(is_bool($result)) {
			return ($result === TRUE) ? 'true' : 'false';
		} elseif(is_string($result) OR $is_key) {
			return '"' . str_replace(array('\\', "\t", "\n", "\r", '"', '/'), array('\\\\', '\\t', '\\n', "\\r", '\"', '\/'), $result) . '"';
		} elseif(is_scalar($result)) {
			return $result;
		}
	}
}
