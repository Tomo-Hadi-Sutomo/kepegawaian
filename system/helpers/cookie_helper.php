<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('set_cookie')) {
	function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE){
		get_instance()->input->set_cookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httponly);
	}
}
if(!function_exists('get_cookie')) {
	function get_cookie($index, $xss_clean = NULL){
		is_bool($xss_clean) OR $xss_clean = (config_item('global_xss_filtering') === TRUE);
		$prefix = isset($_COOKIE[$index]) ? '' : config_item('cookie_prefix');
		return get_instance()->input->cookie($prefix . $index, $xss_clean);
	}
}
if(!function_exists('delete_cookie')) {
	function delete_cookie($name, $domain = '', $path = '/', $prefix = ''){
		set_cookie($name, '', '', $domain, $path, $prefix);
	}
}
