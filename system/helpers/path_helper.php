<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('set_realpath')) {
	function set_realpath($path, $check_existance = FALSE){
		if(preg_match('#^(http:\/\/|https:\/\/|www\.|ftp|php:\/\/)#i', $path) OR filter_var($path, FILTER_VALIDATE_IP) === $path) {
			show_error('The path you submitted must be a local server path, not a URL');
		}
		if(realpath($path) !== FALSE) {
			$path = realpath($path);
		} elseif($check_existance && !is_dir($path) && !is_file($path)) {
			show_error('Not a valid path: ' . $path);
		}
		return is_dir($path) ? rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $path;
	}
}
