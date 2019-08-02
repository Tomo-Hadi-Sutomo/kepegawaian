<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('site_url')) {
	function site_url($uri = '', $protocol = NULL){
		return get_instance()->config->site_url($uri, $protocol);
	}
}
if(!function_exists('base_url')) {
	function base_url($uri = '', $protocol = NULL){
		return get_instance()->config->base_url($uri, $protocol);
	}
}
if(!function_exists('current_url')) {
	function current_url(){
		$CI =& get_instance();
		return $CI->config->site_url($CI->uri->uri_string());
	}
}
if(!function_exists('uri_string')) {
	function uri_string(){
		return get_instance()->uri->uri_string();
	}
}
if(!function_exists('index_page')) {
	function index_page(){
		return get_instance()->config->item('index_page');
	}
}
if(!function_exists('anchor')) {
	function anchor($uri = '', $title = '', $attributes = ''){
		$title = (string)$title;
		$site_url = is_array($uri) ? site_url($uri) : (preg_match('#^(\w+:)?//#i', $uri) ? $uri : site_url($uri));
		if($title === '') {
			$title = $site_url;
		}
		if($attributes !== '') {
			$attributes = _stringify_attributes($attributes);
		}
		return '<a href="' . $site_url . '"' . $attributes . '>' . $title . '</a>';
	}
}
if(!function_exists('anchor_popup')) {
	function anchor_popup($uri = '', $title = '', $attributes = FALSE){
		$title = (string)$title;
		$site_url = preg_match('#^(\w+:)?//#i', $uri) ? $uri : site_url($uri);
		if($title === '') {
			$title = $site_url;
		}
		if($attributes === FALSE) {
			return '<a href="' . $site_url . '" onclick="window.open(\'' . $site_url . "', '_blank'); return false;\">" . $title . '</a>';
		}
		if(!is_array($attributes)) {
			$attributes = array($attributes);
			$window_name = '_blank';
		} elseif(!empty($attributes['window_name'])) {
			$window_name = $attributes['window_name'];
			unset($attributes['window_name']);
		} else {
			$window_name = '_blank';
		}
		foreach(array('width' => '800', 'height' => '600', 'scrollbars' => 'yes', 'menubar' => 'no', 'status' => 'yes', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0') as $key => $val) {
			$atts[$key] = isset($attributes[$key]) ? $attributes[$key] : $val;
			unset($attributes[$key]);
		}
		$attributes = _stringify_attributes($attributes);
		return '<a href="' . $site_url . '" onclick="window.open(\'' . $site_url . "', '" . $window_name . "', '" . _stringify_attributes($atts, TRUE) . "'); return false;\"" . $attributes . '>' . $title . '</a>';
	}
}
if(!function_exists('mailto')) {
	function mailto($email, $title = '', $attributes = ''){
		$title = (string)$title;
		if($title === '') {
			$title = $email;
		}
		return '<a href="mailto:' . $email . '"' . _stringify_attributes($attributes) . '>' . $title . '</a>';
	}
}
if(!function_exists('safe_mailto')) {
	function safe_mailto($email, $title = '', $attributes = ''){
		$title = (string)$title;
		if($title === '') {
			$title = $email;
		}
		$x = str_split('<a href="mailto:', 1);
		for($i = 0, $l = strlen($email); $i < $l; $i++) {
			$x[] = '|' . ord($email[$i]);
		}
		$x[] = '"';
		if($attributes !== '') {
			if(is_array($attributes)) {
				foreach($attributes as $key => $val) {
					$x[] = ' ' . $key . '="';
					for($i = 0, $l = strlen($val); $i < $l; $i++) {
						$x[] = '|' . ord($val[$i]);
					}
					$x[] = '"';
				}
			} else {
				for($i = 0, $l = strlen($attributes); $i < $l; $i++) {
					$x[] = $attributes[$i];
				}
			}
		}
		$x[] = '>';
		$temp = array();
		for($i = 0, $l = strlen($title); $i < $l; $i++) {
			$ordinal = ord($title[$i]);
			if($ordinal < 128) {
				$x[] = '|' . $ordinal;
			} else {
				if(count($temp) === 0) {
					$count = ($ordinal < 224) ? 2 : 3;
				}
				$temp[] = $ordinal;
				if(count($temp) === $count) {
					$number = ($count === 3) ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64) : (($temp[0] % 32) * 64) + ($temp[1] % 64);
					$x[] = '|' . $number;
					$count = 1;
					$temp = array();
				}
			}
		}
		$x[] = '<';
		$x[] = '/';
		$x[] = 'a';
		$x[] = '>';
		$x = array_reverse($x);
		$output = "<script type=\"text/javascript\">\n" . "\t//<![CDATA[\n" . "\tvar l=new Array();\n";
		for($i = 0, $c = count($x); $i < $c; $i++) {
			$output .= "\tl[" . $i . "] = '" . $x[$i] . "';\n";
		}
		$output .= "\n\tfor (var i = l.length-1; i >= 0; i=i-1) {\n" . "\t\tif (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");\n" . "\t\telse document.write(unescape(l[i]));\n" . "\t}\n" . "\t//]]>\n" . '</script>';
		return $output;
	}
}
if(!function_exists('auto_link')) {
	function auto_link($str, $type = 'both', $popup = FALSE){
		if($type !== 'email' && preg_match_all('#(\w*://|www\.)[^\s()<>;]+\w#i', $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
			$target = ($popup) ? ' target="_blank"' : '';
			foreach(array_reverse($matches) as $match) {
				$a = '<a href="' . (strpos($match[1][0], '/') ? '' : 'http://') . $match[0][0] . '"' . $target . '>' . $match[0][0] . '</a>';
				$str = substr_replace($str, $a, $match[0][1], strlen($match[0][0]));
			}
		}
		if($type !== 'url' && preg_match_all('#([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])#i', $str, $matches, PREG_OFFSET_CAPTURE)) {
			foreach(array_reverse($matches[0]) as $match) {
				if(filter_var($match[0], FILTER_VALIDATE_EMAIL) !== FALSE) {
					$str = substr_replace($str, safe_mailto($match[0]), $match[1], strlen($match[0]));
				}
			}
		}
		return $str;
	}
}
if(!function_exists('prep_url')) {
	function prep_url($str = ''){
		if($str === 'http://' OR $str === '') {
			return '';
		}
		$url = parse_url($str);
		if(!$url OR !isset($url['scheme'])) {
			return 'http://' . $str;
		}
		return $str;
	}
}
if(!function_exists('url_title')) {
	function url_title($str, $separator = '-', $lowercase = FALSE){
		if($separator === 'dash') {
			$separator = '-';
		} elseif($separator === 'underscore') {
			$separator = '_';
		}
		$q_separator = preg_quote($separator, '#');
		$trans = array('&.+?;' => '', '[^\w\d _-]' => '', '\s+' => $separator, '(' . $q_separator . ')+' => $separator);
		$str = strip_tags($str);
		foreach($trans as $key => $val) {
			$str = preg_replace('#' . $key . '#i' . (UTF8_ENABLED ? 'u' : ''), $val, $str);
		}
		if($lowercase === TRUE) {
			$str = strtolower($str);
		}
		return trim(trim($str, $separator));
	}
}
if(!function_exists('redirect')) {
	function redirect($uri = '', $method = 'auto', $code = NULL){
		if(!preg_match('#^(\w+:)?//#i', $uri)) {
			$uri = site_url($uri);
		}
		if($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE) {
			$method = 'refresh';
		} elseif($method !== 'refresh' && (empty($code) OR !is_numeric($code))) {
			if(isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
				$code = ($_SERVER['REQUEST_METHOD'] !== 'GET') ? 303 : 307;
			} else {
				$code = 302;
			}
		}
		switch($method) {
			case 'refresh':
				header('Refresh:0;url=' . $uri);
				break;
			default:
				header('Location: ' . $uri, TRUE, $code);
				break;
		}
		exit;
	}
}
