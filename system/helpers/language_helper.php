<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('lang')) {
	function lang($line, $for = '', $attributes = array()){
		$line = get_instance()->lang->line($line);
		if($for !== '') {
			$line = '<label for="' . $for . '"' . _stringify_attributes($attributes) . '>' . $line . '</label>';
		}
		return $line;
	}
}
