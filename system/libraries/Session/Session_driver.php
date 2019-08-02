<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class CI_Session_driver implements SessionHandlerInterface{
	protected $_config;
	protected $_fingerprint;
	protected $_lock = FALSE;
	protected $_session_id;
	protected $_success, $_failure;
	public function __construct(&$params){
		$this->_config =& $params;
		if(is_php('7')) {
			$this->_success = TRUE;
			$this->_failure = FALSE;
		} else {
			$this->_success = 0;
			$this->_failure = -1;
		}
	}
	protected function _cookie_destroy(){
		return setcookie($this->_config['cookie_name'], NULL, 1, $this->_config['cookie_path'], $this->_config['cookie_domain'], $this->_config['cookie_secure'], TRUE);
	}
	protected function _get_lock($session_id){
		$this->_lock = TRUE;
		return TRUE;
	}
	protected function _release_lock(){
		if($this->_lock) {
			$this->_lock = FALSE;
		}
		return TRUE;
	}
	protected function _fail(){
		ini_set('session.save_path', config_item('sess_save_path'));
		return $this->_failure;
	}
}
