<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('valid_email')) {
	function valid_email($email){
		return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
	}
}
if(!function_exists('send_email')) {
	function send_email($recipient, $subject, $message){
		return mail($recipient, $subject, $message);
	}
}
