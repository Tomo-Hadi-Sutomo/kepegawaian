<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Jquery extends CI_Javascript{
	public $jquery_code_for_load = array();
	public $jquery_code_for_compile = array();
	public $jquery_corner_active = FALSE;
	public $jquery_table_sorter_active = FALSE;
	public $jquery_table_sorter_pager_active = FALSE;
	public $jquery_ajax_img = '';
	protected $_javascript_folder = 'js';
	public function __construct($params){
		$this->CI =& get_instance();
		extract($params);
		if($autoload === TRUE) {
			$this->script();
		}
		log_message('info', 'Jquery Class Initialized');
	}
	public function corner($element = '', $corner_style = ''){
		$corner_location = '/plugins/jquery.corner.js';
		if($corner_style !== '') {
			$corner_style = '"' . $corner_style . '"';
		}
		return '$(' . $this->_prep_element($element) . ').corner(' . $corner_style . ');';
	}
	public function modal($src, $relative = FALSE){
		$this->jquery_code_for_load[] = $this->external($src, $relative);
	}
	public function effect($src, $relative = FALSE){
		$this->jquery_code_for_load[] = $this->external($src, $relative);
	}
	public function plugin($src, $relative = FALSE){
		$this->jquery_code_for_load[] = $this->external($src, $relative);
	}
	public function ui($src, $relative = FALSE){
		$this->jquery_code_for_load[] = $this->external($src, $relative);
	}
	public function sortable($element, $options = array()){
		if(count($options) > 0) {
			$sort_options = array();
			foreach($options as $k => $v) {
				$sort_options[] = "\n\t\t" . $k . ': ' . $v;
			}
			$sort_options = implode(',', $sort_options);
		} else {
			$sort_options = '';
		}
		return '$(' . $this->_prep_element($element) . ').sortable({' . $sort_options . "\n\t});";
	}
	public function tablesorter($table = '', $options = ''){
		$this->jquery_code_for_compile[] = "\t$(" . $this->_prep_element($table) . ').tablesorter(' . $options . ");\n";
	}
	public function script($library_src = '', $relative = FALSE){
		$library_src = $this->external($library_src, $relative);
		$this->jquery_code_for_load[] = $library_src;
		return $library_src;
	}
	protected function _blur($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'blur');
	}
	protected function _change($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'change');
	}
	protected function _click($element = 'this', $js = '', $ret_false = TRUE){
		is_array($js) OR $js = array($js);
		if($ret_false) {
			$js[] = 'return false;';
		}
		return $this->_add_event($element, $js, 'click');
	}
	protected function _dblclick($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'dblclick');
	}
	protected function _error($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'error');
	}
	protected function _focus($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'focus');
	}
	protected function _hover($element = 'this', $over = '', $out = ''){
		$event = "\n\t$(" . $this->_prep_element($element) . ").hover(\n\t\tfunction()\n\t\t{\n\t\t\t{$over}\n\t\t}, \n\t\tfunction()\n\t\t{\n\t\t\t{$out}\n\t\t});\n";
		$this->jquery_code_for_compile[] = $event;
		return $event;
	}
	protected function _keydown($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'keydown');
	}
	protected function _keyup($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'keyup');
	}
	protected function _load($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'load');
	}
	protected function _mousedown($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'mousedown');
	}
	protected function _mouseout($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'mouseout');
	}
	protected function _mouseover($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'mouseover');
	}
	protected function _mouseup($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'mouseup');
	}
	protected function _output($array_js = array()){
		if(!is_array($array_js)) {
			$array_js = array($array_js);
		}
		foreach($array_js as $js) {
			$this->jquery_code_for_compile[] = "\t" . $js . "\n";
		}
	}
	protected function _resize($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'resize');
	}
	protected function _scroll($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'scroll');
	}
	protected function _unload($element = 'this', $js = ''){
		return $this->_add_event($element, $js, 'unload');
	}
	protected function _addClass($element = 'this', $class = ''){
		$element = $this->_prep_element($element);
		return '$(' . $element . ').addClass("' . $class . '");';
	}
	protected function _animate($element = 'this', $params = array(), $speed = '', $extra = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		$animations = "\t\t\t";
		foreach($params as $param => $value) {
			$animations .= $param . ": '" . $value . "', ";
		}
		$animations = substr($animations, 0, -2);
		if($speed !== '') {
			$speed = ', ' . $speed;
		}
		if($extra !== '') {
			$extra = ', ' . $extra;
		}
		return "$({$element}).animate({\n$animations\n\t\t}" . $speed . $extra . ');';
	}
	protected function _fadeIn($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return "$({$element}).fadeIn({$speed}{$callback});";
	}
	protected function _fadeOut($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return '$(' . $element . ').fadeOut(' . $speed . $callback . ');';
	}
	protected function _hide($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return "$({$element}).hide({$speed}{$callback});";
	}
	protected function _removeClass($element = 'this', $class = ''){
		$element = $this->_prep_element($element);
		return '$(' . $element . ').removeClass("' . $class . '");';
	}
	protected function _slideUp($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return '$(' . $element . ').slideUp(' . $speed . $callback . ');';
	}
	protected function _slideDown($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return '$(' . $element . ').slideDown(' . $speed . $callback . ');';
	}
	protected function _slideToggle($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return '$(' . $element . ').slideToggle(' . $speed . $callback . ');';
	}
	protected function _toggle($element = 'this'){
		$element = $this->_prep_element($element);
		return '$(' . $element . ').toggle();';
	}
	protected function _toggleClass($element = 'this', $class = ''){
		$element = $this->_prep_element($element);
		return '$(' . $element . ').toggleClass("' . $class . '");';
	}
	protected function _show($element = 'this', $speed = '', $callback = ''){
		$element = $this->_prep_element($element);
		$speed = $this->_validate_speed($speed);
		if($callback !== '') {
			$callback = ", function(){\n{$callback}\n}";
		}
		return '$(' . $element . ').show(' . $speed . $callback . ');';
	}
	protected function _updater($container = 'this', $controller = '', $options = ''){
		$container = $this->_prep_element($container);
		$controller = (strpos('://', $controller) === FALSE) ? $controller : $this->CI->config->site_url($controller);
		if($this->CI->config->item('javascript_ajax_img') === '') {
			$loading_notifier = 'Loading...';
		} else {
			$loading_notifier = '<img src="' . $this->CI->config->slash_item('base_url') . $this->CI->config->item('javascript_ajax_img') . '" alt="Loading" />';
		}
		$updater = '$(' . $container . ").empty();\n" . "\t\t$(" . $container . ').prepend("' . $loading_notifier . "\");\n";
		$request_options = '';
		if($options !== '') {
			$request_options .= ', {' . (is_array($options) ? "'" . implode("', '", $options) . "'" : "'" . str_replace(':', "':'", $options) . "'") . '}';
		}
		return $updater . "\t\t$($container).load('$controller'$request_options);";
	}
	protected function _zebraTables($class = '', $odd = 'odd', $hover = ''){
		$class = ($class !== '') ? '.' . $class : '';
		$zebra = "\t\$(\"table{$class} tbody tr:nth-child(even)\").addClass(\"{$odd}\");";
		$this->jquery_code_for_compile[] = $zebra;
		if($hover !== '') {
			$hover = $this->hover("table{$class} tbody tr", "$(this).addClass('hover');", "$(this).removeClass('hover');");
		}
		return $zebra;
	}
	protected function _add_event($element, $js, $event){
		if(is_array($js)) {
			$js = implode("\n\t\t", $js);
		}
		$event = "\n\t$(" . $this->_prep_element($element) . ').' . $event . "(function(){\n\t\t{$js}\n\t});\n";
		$this->jquery_code_for_compile[] = $event;
		return $event;
	}
	protected function _compile($view_var = 'script_foot', $script_tags = TRUE){
		$external_scripts = implode('', $this->jquery_code_for_load);
		$this->CI->load->vars(array('library_src' => $external_scripts));
		if(count($this->jquery_code_for_compile) === 0) {
			return;
		}
		$script = '$(document).ready(function() {' . "\n" . implode('', $this->jquery_code_for_compile) . '});';
		$output = ($script_tags === FALSE) ? $script : $this->inline($script);
		$this->CI->load->vars(array($view_var => $output));
	}
	protected function _clear_compile(){
		$this->jquery_code_for_compile = array();
	}
	protected function _document_ready($js){
		is_array($js) OR $js = array($js);
		foreach($js as $script) {
			$this->jquery_code_for_compile[] = $script;
		}
	}
	protected function _prep_element($element){
		if($element !== 'this') {
			$element = '"' . $element . '"';
		}
		return $element;
	}
	protected function _validate_speed($speed){
		if(in_array($speed, array('slow', 'normal', 'fast'))) {
			return '"' . $speed . '"';
		} elseif(preg_match('/[^0-9]/', $speed)) {
			return '';
		}
		return $speed;
	}
}
