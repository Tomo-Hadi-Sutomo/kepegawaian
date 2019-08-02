<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Utf8{
	public function __construct(){
		if(defined('PREG_BAD_UTF8_ERROR') && (ICONV_ENABLED === TRUE OR MB_ENABLED === TRUE) && strtoupper(config_item('charset')) === 'UTF-8') {
			define('UTF8_ENABLED', TRUE);
			log_message('debug', 'UTF-8 Support Enabled');
		} else {
			define('UTF8_ENABLED', FALSE);
			log_message('debug', 'UTF-8 Support Disabled');
		}
		log_message('info', 'Utf8 Class Initialized');
	}
	public function clean_string($str){
		if($this->is_ascii($str) === FALSE) {
			if(MB_ENABLED) {
				$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
			} elseif(ICONV_ENABLED) {
				$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
			}
		}
		return $str;
	}
	public function safe_ascii_for_xml($str){
		return remove_invisible_characters($str, FALSE);
	}
	public function convert_to_utf8($str, $encoding){
		if(MB_ENABLED) {
			return mb_convert_encoding($str, 'UTF-8', $encoding);
		} elseif(ICONV_ENABLED) {
			return @iconv($encoding, 'UTF-8', $str);
		}
		return FALSE;
	}
	public function is_ascii($str){
		return (preg_match('/[^\x00-\x7F]/S', $str) === 0);
	}
}
