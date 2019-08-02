<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Driver_Library{
	protected $valid_drivers = array();
	protected $lib_name;
	public function __get($child){
		return $this->load_driver($child);
	}
	public function load_driver($child){
		$prefix = config_item('subclass_prefix');
		if(!isset($this->lib_name)) {
			$this->lib_name = str_replace(array('CI_', $prefix), '', get_class($this));
		}
		$child_name = $this->lib_name . '_' . $child;
		if(!in_array($child, $this->valid_drivers)) {
			$msg = 'Invalid driver requested: ' . $child_name;
			log_message('error', $msg);
			show_error($msg);
		}
		$CI = get_instance();
		$paths = $CI->load->get_package_paths(TRUE);
		$class_name = $prefix . $child_name;
		$found = class_exists($class_name, FALSE);
		if(!$found) {
			foreach($paths as $path) {
				$file = $path . 'libraries/' . $this->lib_name . '/drivers/' . $prefix . $child_name . '.php';
				if(file_exists($file)) {
					$basepath = BASEPATH . 'libraries/' . $this->lib_name . '/drivers/' . $child_name . '.php';
					if(!file_exists($basepath)) {
						$msg = 'Unable to load the requested class: CI_' . $child_name;
						log_message('error', $msg);
						show_error($msg);
					}
					include_once($basepath);
					include_once($file);
					$found = TRUE;
					break;
				}
			}
		}
		if(!$found) {
			$class_name = 'CI_' . $child_name;
			if(!class_exists($class_name, FALSE)) {
				foreach($paths as $path) {
					$file = $path . 'libraries/' . $this->lib_name . '/drivers/' . $child_name . '.php';
					if(file_exists($file)) {
						include_once($file);
						break;
					}
				}
			}
		}
		if(!class_exists($class_name, FALSE)) {
			if(class_exists($child_name, FALSE)) {
				$class_name = $child_name;
			} else {
				$msg = 'Unable to load the requested driver: ' . $class_name;
				log_message('error', $msg);
				show_error($msg);
			}
		}
		$obj = new $class_name();
		$obj->decorate($this);
		$this->$child = $obj;
		return $this->$child;
	}
}
class CI_Driver{
	protected static $_reflections = array();
	protected $_parent;
	protected $_methods = array();
	protected $_properties = array();
	public function decorate($parent){
		$this->_parent = $parent;
		$class_name = get_class($parent);
		if(!isset(self::$_reflections[$class_name])) {
			$r = new ReflectionObject($parent);
			foreach($r->getMethods() as $method) {
				if($method->isPublic()) {
					$this->_methods[] = $method->getName();
				}
			}
			foreach($r->getProperties() as $prop) {
				if($prop->isPublic()) {
					$this->_properties[] = $prop->getName();
				}
			}
			self::$_reflections[$class_name] = array($this->_methods, $this->_properties);
		} else {
			list($this->_methods, $this->_properties) = self::$_reflections[$class_name];
		}
	}
	public function __call($method, $args = array()){
		if(in_array($method, $this->_methods)) {
			return call_user_func_array(array($this->_parent, $method), $args);
		}
		throw new BadMethodCallException('No such method: ' . $method . '()');
	}
	public function __get($var){
		if(in_array($var, $this->_properties)) {
			return $this->_parent->$var;
		}
	}
	public function __set($var, $val){
		if(in_array($var, $this->_properties)) {
			$this->_parent->$var = $val;
		}
	}
}
