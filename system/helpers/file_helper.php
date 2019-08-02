<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('read_file')) {
	function read_file($file){
		return @file_get_contents($file);
	}
}
if(!function_exists('write_file')) {
	function write_file($path, $data, $mode = 'wb'){
		if(!$fp = @fopen($path, $mode)) {
			return FALSE;
		}
		flock($fp, LOCK_EX);
		for($result = $written = 0, $length = strlen($data); $written < $length; $written += $result) {
			if(($result = fwrite($fp, substr($data, $written))) === FALSE) {
				break;
			}
		}
		flock($fp, LOCK_UN);
		fclose($fp);
		return is_int($result);
	}
}
if(!function_exists('delete_files')) {
	function delete_files($path, $del_dir = FALSE, $htdocs = FALSE, $_level = 0){
		$path = rtrim($path, '/\\');
		if(!$current_dir = @opendir($path)) {
			return FALSE;
		}
		while(FALSE !== ($filename = @readdir($current_dir))) {
			if($filename !== '.' && $filename !== '..') {
				$filepath = $path . DIRECTORY_SEPARATOR . $filename;
				if(is_dir($filepath) && $filename[0] !== '.' && !is_link($filepath)) {
					delete_files($filepath, $del_dir, $htdocs, $_level + 1);
				} elseif($htdocs !== TRUE OR !preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename)) {
					@unlink($filepath);
				}
			}
		}
		closedir($current_dir);
		return ($del_dir === TRUE && $_level > 0) ? @rmdir($path) : TRUE;
	}
}
if(!function_exists('get_filenames')) {
	function get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE){
		static $_filedata = array();
		if($fp = @opendir($source_dir)) {
			if($_recursion === FALSE) {
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}
			while(FALSE !== ($file = readdir($fp))) {
				if(is_dir($source_dir . $file) && $file[0] !== '.') {
					get_filenames($source_dir . $file . DIRECTORY_SEPARATOR, $include_path, TRUE);
				} elseif($file[0] !== '.') {
					$_filedata[] = ($include_path === TRUE) ? $source_dir . $file : $file;
				}
			}
			closedir($fp);
			return $_filedata;
		}
		return FALSE;
	}
}
if(!function_exists('get_dir_file_info')) {
	function get_dir_file_info($source_dir, $top_level_only = TRUE, $_recursion = FALSE){
		static $_filedata = array();
		$relative_path = $source_dir;
		if($fp = @opendir($source_dir)) {
			if($_recursion === FALSE) {
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}
			while(FALSE !== ($file = readdir($fp))) {
				if(is_dir($source_dir . $file) && $file[0] !== '.' && $top_level_only === FALSE) {
					get_dir_file_info($source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, TRUE);
				} elseif($file[0] !== '.') {
					$_filedata[$file] = get_file_info($source_dir . $file);
					$_filedata[$file]['relative_path'] = $relative_path;
				}
			}
			closedir($fp);
			return $_filedata;
		}
		return FALSE;
	}
}
if(!function_exists('get_file_info')) {
	function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date')){
		if(!file_exists($file)) {
			return FALSE;
		}
		if(is_string($returned_values)) {
			$returned_values = explode(',', $returned_values);
		}
		foreach($returned_values as $key) {
			switch($key) {
				case 'name':
					$fileinfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileinfo['server_path'] = $file;
					break;
				case 'size':
					$fileinfo['size'] = filesize($file);
					break;
				case 'date':
					$fileinfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileinfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileinfo['writable'] = is_really_writable($file);
					break;
				case 'executable':
					$fileinfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileinfo['fileperms'] = fileperms($file);
					break;
			}
		}
		return $fileinfo;
	}
}
if(!function_exists('get_mime_by_extension')) {
	function get_mime_by_extension($filename){
		static $mimes;
		if(!is_array($mimes)) {
			$mimes = get_mimes();
			if(empty($mimes)) {
				return FALSE;
			}
		}
		$extension = strtolower(substr(strrchr($filename, '.'), 1));
		if(isset($mimes[$extension])) {
			return is_array($mimes[$extension]) ? current($mimes[$extension]) : $mimes[$extension];
		}
		return FALSE;
	}
}
if(!function_exists('symbolic_permissions')) {
	function symbolic_permissions($perms){
		if(($perms & 0xC000) === 0xC000) {
			$symbolic = 's';
		} elseif(($perms & 0xA000) === 0xA000) {
			$symbolic = 'l';
		} elseif(($perms & 0x8000) === 0x8000) {
			$symbolic = '-';
		} elseif(($perms & 0x6000) === 0x6000) {
			$symbolic = 'b';
		} elseif(($perms & 0x4000) === 0x4000) {
			$symbolic = 'd';
		} elseif(($perms & 0x2000) === 0x2000) {
			$symbolic = 'c';
		} elseif(($perms & 0x1000) === 0x1000) {
			$symbolic = 'p';
		} else {
			$symbolic = 'u';
		}
		$symbolic .= (($perms & 0x0100) ? 'r' : '-') . (($perms & 0x0080) ? 'w' : '-') . (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
		$symbolic .= (($perms & 0x0020) ? 'r' : '-') . (($perms & 0x0010) ? 'w' : '-') . (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
		$symbolic .= (($perms & 0x0004) ? 'r' : '-') . (($perms & 0x0002) ? 'w' : '-') . (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
		return $symbolic;
	}
}
if(!function_exists('octal_permissions')) {
	function octal_permissions($perms){
		return substr(sprintf('%o', $perms), -3);
	}
}
