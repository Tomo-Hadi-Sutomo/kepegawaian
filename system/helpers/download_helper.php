<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('force_download')) {
	function force_download($filename = '', $data = '', $set_mime = FALSE){
		if($filename === '' OR $data === '') {
			return;
		} elseif($data === NULL) {
			if(!@is_file($filename) OR ($filesize = @filesize($filename)) === FALSE) {
				return;
			}
			$filepath = $filename;
			$filename = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $filename));
			$filename = end($filename);
		} else {
			$filesize = strlen($data);
		}
		$mime = 'application/octet-stream';
		$x = explode('.', $filename);
		$extension = end($x);
		if($set_mime === TRUE) {
			if(count($x) === 1 OR $extension === '') {
				return;
			}
			$mimes =& get_mimes();
			if(isset($mimes[$extension])) {
				$mime = is_array($mimes[$extension]) ? $mimes[$extension][0] : $mimes[$extension];
			}
		}
		if(count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT'])) {
			$x[count($x) - 1] = strtoupper($extension);
			$filename = implode('.', $x);
		}
		if($data === NULL && ($fp = @fopen($filepath, 'rb')) === FALSE) {
			return;
		}
		if(ob_get_level() !== 0 && @ob_end_clean() === FALSE) {
			@ob_clean();
		}
		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Expires: 0');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $filesize);
		header('Cache-Control: private, no-transform, no-store, must-revalidate');
		if($data !== NULL) {
			exit($data);
		}
		while(!feof($fp) && ($data = fread($fp, 1048576)) !== FALSE) {
			echo $data;
		}
		fclose($fp);
		exit;
	}
}
