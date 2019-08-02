<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Hooks{
	public $enabled = FALSE;
	public $hooks = array();
	protected $_objects = array();
	protected $_in_progress = FALSE;
	public function __construct(){
		$CFG =& load_class('Config', 'core');
		log_message('info', 'Hooks Class Initialized');
		if($CFG->item('enable_hooks') === FALSE) {
			return;
		}
		if(file_exists(APPPATH . 'config/hooks.php')) {
			include(APPPATH . 'config/hooks.php');
		}
		if(file_exists(APPPATH . 'config/' . ENVIRONMENT . '/hooks.php')) {
			include(APPPATH . 'config/' . ENVIRONMENT . '/hooks.php');
		}
		if(!isset($hook) OR !is_array($hook)) {
			return;
		}
		$this->hooks =& $hook;
		$this->enabled = TRUE;
	}
	public function call_hook($which = ''){
		if(!$this->enabled OR !isset($this->hooks[$which])) {
			return FALSE;
		}
		if(is_array($this->hooks[$which]) && !isset($this->hooks[$which]['function'])) {
			foreach($this->hooks[$which] as $val) {
				$this->_run_hook($val);
			}
		} else {
			$this->_run_hook($this->hooks[$which]);
		}
		return TRUE;
	}
	protected function _run_hook($data){
		if(is_callable($data)) {
			is_array($data) ? $data[0]->{$data[1]}() : $data();
			return TRUE;
		} elseif(!is_array($data)) {
			return FALSE;
		}
		if($this->_in_progress === TRUE) {
			return;
		}
		if(!isset($data['filepath'], $data['filename'])) {
			return FALSE;
		}
		$filepath = APPPATH . $data['filepath'] . '/' . $data['filename'];
		if(!file_exists($filepath)) {
			return FALSE;
		}
		$class = empty($data['class']) ? FALSE : $data['class'];
		$function = empty($data['function']) ? FALSE : $data['function'];
		$params = isset($data['params']) ? $data['params'] : '';
		if(empty($function)) {
			return FALSE;
		}
		$this->_in_progress = TRUE;
		if($class !== FALSE) {
			if(isset($this->_objects[$class])) {
				if(method_exists($this->_objects[$class], $function)) {
					$this->_objects[$class]->$function($params);
				} else {
					return $this->_in_progress = FALSE;
				}
			} else {
				class_exists($class, FALSE) OR require_once($filepath);
				if(!class_exists($class, FALSE) OR !method_exists($class, $function)) {
					return $this->_in_progress = FALSE;
				}
				$this->_objects[$class] = new $class();
				$this->_objects[$class]->$function($params);
			}
		} else {
			function_exists($function) OR require_once($filepath);
			if(!function_exists($function)) {
				return $this->_in_progress = FALSE;
			}
			$function($params);
		}
		$this->_in_progress = FALSE;
		return TRUE;
	}
}
