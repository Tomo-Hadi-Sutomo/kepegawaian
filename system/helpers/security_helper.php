<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('xss_clean')) {
	function xss_clean($str, $is_image = FALSE){
		return get_instance()->security->xss_clean($str, $is_image);
	}
}
if(!function_exists('sanitize_filename')) {
	function sanitize_filename($filename){
		return get_instance()->security->sanitize_filename($filename);
	}
}
if(!function_exists('do_hash')) {
	function do_hash($str, $type = 'sha1'){
		if(!in_array(strtolower($type), hash_algos())) {
			$type = 'md5';
		}
		return hash($type, $str);
	}
}
if(!function_exists('strip_image_tags')) {
	function strip_image_tags($str){
		return get_instance()->security->strip_image_tags($str);
	}
}
if(!function_exists('encode_php_tags')) {
	function encode_php_tags($str){
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}
}
