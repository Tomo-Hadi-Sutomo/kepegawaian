<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_User_agent{
	public $agent = NULL;
	public $is_browser = FALSE;
	public $is_robot = FALSE;
	public $is_mobile = FALSE;
	public $languages = array();
	public $charsets = array();
	public $platforms = array();
	public $browsers = array();
	public $mobiles = array();
	public $robots = array();
	public $platform = '';
	public $browser = '';
	public $version = '';
	public $mobile = '';
	public $robot = '';
	public $referer;
	public function __construct(){
		$this->_load_agent_file();
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
			$this->_compile_data();
		}
		log_message('info', 'User Agent Class Initialized');
	}
	protected function _load_agent_file(){
		if(($found = file_exists(APPPATH . 'config/user_agents.php'))) {
			include(APPPATH . 'config/user_agents.php');
		}
		if(file_exists(APPPATH . 'config/' . ENVIRONMENT . '/user_agents.php')) {
			include(APPPATH . 'config/' . ENVIRONMENT . '/user_agents.php');
			$found = TRUE;
		}
		if($found !== TRUE) {
			return FALSE;
		}
		$return = FALSE;
		if(isset($platforms)) {
			$this->platforms = $platforms;
			unset($platforms);
			$return = TRUE;
		}
		if(isset($browsers)) {
			$this->browsers = $browsers;
			unset($browsers);
			$return = TRUE;
		}
		if(isset($mobiles)) {
			$this->mobiles = $mobiles;
			unset($mobiles);
			$return = TRUE;
		}
		if(isset($robots)) {
			$this->robots = $robots;
			unset($robots);
			$return = TRUE;
		}
		return $return;
	}
	protected function _compile_data(){
		$this->_set_platform();
		foreach(array('_set_robot', '_set_browser', '_set_mobile') as $function) {
			if($this->$function() === TRUE) {
				break;
			}
		}
	}
	protected function _set_platform(){
		if(is_array($this->platforms) && count($this->platforms) > 0) {
			foreach($this->platforms as $key => $val) {
				if(preg_match('|' . preg_quote($key) . '|i', $this->agent)) {
					$this->platform = $val;
					return TRUE;
				}
			}
		}
		$this->platform = 'Unknown Platform';
		return FALSE;
	}
	protected function _set_browser(){
		if(is_array($this->browsers) && count($this->browsers) > 0) {
			foreach($this->browsers as $key => $val) {
				if(preg_match('|' . $key . '.*?([0-9\.]+)|i', $this->agent, $match)) {
					$this->is_browser = TRUE;
					$this->version = $match[1];
					$this->browser = $val;
					$this->_set_mobile();
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	protected function _set_robot(){
		if(is_array($this->robots) && count($this->robots) > 0) {
			foreach($this->robots as $key => $val) {
				if(preg_match('|' . preg_quote($key) . '|i', $this->agent)) {
					$this->is_robot = TRUE;
					$this->robot = $val;
					$this->_set_mobile();
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	protected function _set_mobile(){
		if(is_array($this->mobiles) && count($this->mobiles) > 0) {
			foreach($this->mobiles as $key => $val) {
				if(FALSE !== (stripos($this->agent, $key))) {
					$this->is_mobile = TRUE;
					$this->mobile = $val;
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	protected function _set_languages(){
		if((count($this->languages) === 0) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$this->languages = explode(',', preg_replace('/(;\s?q=[0-9\.]+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE']))));
		}
		if(count($this->languages) === 0) {
			$this->languages = array('Undefined');
		}
	}
	protected function _set_charsets(){
		if((count($this->charsets) === 0) && !empty($_SERVER['HTTP_ACCEPT_CHARSET'])) {
			$this->charsets = explode(',', preg_replace('/(;\s?q=.+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET']))));
		}
		if(count($this->charsets) === 0) {
			$this->charsets = array('Undefined');
		}
	}
	public function is_browser($key = NULL){
		if(!$this->is_browser) {
			return FALSE;
		}
		if($key === NULL) {
			return TRUE;
		}
		return (isset($this->browsers[$key]) && $this->browser === $this->browsers[$key]);
	}
	public function is_robot($key = NULL){
		if(!$this->is_robot) {
			return FALSE;
		}
		if($key === NULL) {
			return TRUE;
		}
		return (isset($this->robots[$key]) && $this->robot === $this->robots[$key]);
	}
	public function is_mobile($key = NULL){
		if(!$this->is_mobile) {
			return FALSE;
		}
		if($key === NULL) {
			return TRUE;
		}
		return (isset($this->mobiles[$key]) && $this->mobile === $this->mobiles[$key]);
	}
	public function is_referral(){
		if(!isset($this->referer)) {
			if(empty($_SERVER['HTTP_REFERER'])) {
				$this->referer = FALSE;
			} else {
				$referer_host = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
				$own_host = parse_url(config_item('base_url'), PHP_URL_HOST);
				$this->referer = ($referer_host && $referer_host !== $own_host);
			}
		}
		return $this->referer;
	}
	public function agent_string(){
		return $this->agent;
	}
	public function platform(){
		return $this->platform;
	}
	public function browser(){
		return $this->browser;
	}
	public function version(){
		return $this->version;
	}
	public function robot(){
		return $this->robot;
	}
	public function mobile(){
		return $this->mobile;
	}
	public function referrer(){
		return empty($_SERVER['HTTP_REFERER']) ? '' : trim($_SERVER['HTTP_REFERER']);
	}
	public function languages(){
		if(count($this->languages) === 0) {
			$this->_set_languages();
		}
		return $this->languages;
	}
	public function charsets(){
		if(count($this->charsets) === 0) {
			$this->_set_charsets();
		}
		return $this->charsets;
	}
	public function accept_lang($lang = 'en'){
		return in_array(strtolower($lang), $this->languages(), TRUE);
	}
	public function accept_charset($charset = 'utf-8'){
		return in_array(strtolower($charset), $this->charsets(), TRUE);
	}
	public function parse($string){
		$this->is_browser = FALSE;
		$this->is_robot = FALSE;
		$this->is_mobile = FALSE;
		$this->browser = '';
		$this->version = '';
		$this->mobile = '';
		$this->robot = '';
		$this->agent = $string;
		if(!empty($string)) {
			$this->_compile_data();
		}
	}
}
