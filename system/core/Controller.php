<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Controller{
	private static $instance;
	public function __construct(){
		self::$instance =& $this;
		foreach(is_loaded() as $var => $class) {
			$this->$var =& load_class($class);
		}
		$this->load =& load_class('Loader', 'core');
		$this->load->initialize();
		log_message('info', 'Controller Class Initialized');
	}
	public static function &get_instance(){
		return self::$instance;
	}
}
