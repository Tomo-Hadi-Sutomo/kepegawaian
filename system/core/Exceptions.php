<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Exceptions{
	public $ob_level;
	public $levels = array(E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parsing Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Runtime Notice');
	public function __construct(){
		$this->ob_level = ob_get_level();
	}
	public function log_exception($severity, $message, $filepath, $line){
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
		log_message('error', 'Severity: ' . $severity . ' --> ' . $message . ' ' . $filepath . ' ' . $line);
	}
	public function show_404($page = '', $log_error = TRUE){
		if(is_cli()) {
			$heading = 'Not Found';
			$message = 'The controller/method pair you requested was not found.';
		} else {
			$heading = '404 Page Not Found';
			$message = 'The page you requested was not found.';
		}
		if($log_error) {
			log_message('error', $heading . ': ' . $page);
		}
		echo $this->show_error($heading, $message, 'error_404', 404);
		exit(4);
	}
	public function show_error($heading, $message, $template = 'error_general', $status_code = 500){
		$templates_path = config_item('error_views_path');
		if(empty($templates_path)) {
			$templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
		}
		if(is_cli()) {
			$message = "\t" . (is_array($message) ? implode("\n\t", $message) : $message);
			$template = 'cli' . DIRECTORY_SEPARATOR . $template;
		} else {
			set_status_header($status_code);
			$message = '<p>' . (is_array($message) ? implode('</p><p>', $message) : $message) . '</p>';
			$template = 'html' . DIRECTORY_SEPARATOR . $template;
		}
		if(ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include($templates_path . $template . '.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
	public function show_exception($exception){
		$templates_path = config_item('error_views_path');
		if(empty($templates_path)) {
			$templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
		}
		$message = $exception->getMessage();
		if(empty($message)) {
			$message = '(null)';
		}
		if(is_cli()) {
			$templates_path .= 'cli' . DIRECTORY_SEPARATOR;
		} else {
			$templates_path .= 'html' . DIRECTORY_SEPARATOR;
		}
		if(ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include($templates_path . 'error_exception.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
	public function show_php_error($severity, $message, $filepath, $line){
		$templates_path = config_item('error_views_path');
		if(empty($templates_path)) {
			$templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
		}
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
		if(!is_cli()) {
			$filepath = str_replace('\\', '/', $filepath);
			if(FALSE !== strpos($filepath, '/')) {
				$x = explode('/', $filepath);
				$filepath = $x[count($x) - 2] . '/' . end($x);
			}
			$template = 'html' . DIRECTORY_SEPARATOR . 'error_php';
		} else {
			$template = 'cli' . DIRECTORY_SEPARATOR . 'error_php';
		}
		if(ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include($templates_path . $template . '.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}
