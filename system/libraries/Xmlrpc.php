<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('xml_parser_create')) {
	show_error('Your PHP installation does not support XML');
}
class CI_Xmlrpc{
	public $debug = FALSE;
	public $xmlrpcI4 = 'i4';
	public $xmlrpcInt = 'int';
	public $xmlrpcBoolean = 'boolean';
	public $xmlrpcDouble = 'double';
	public $xmlrpcString = 'string';
	public $xmlrpcDateTime = 'dateTime.iso8601';
	public $xmlrpcBase64 = 'base64';
	public $xmlrpcArray = 'array';
	public $xmlrpcStruct = 'struct';
	public $xmlrpcTypes = array();
	public $valid_parents = array();
	public $xmlrpcerr = array();
	public $xmlrpcstr = array();
	public $xmlrpc_defencoding = 'UTF-8';
	public $xmlrpcName = 'XML-RPC for CodeIgniter';
	public $xmlrpcVersion = '1.1';
	public $xmlrpcerruser = 800;
	public $xmlrpcerrxml = 100;
	public $xmlrpc_backslash = '';
	public $client;
	public $method;
	public $data;
	public $message = '';
	public $error = '';
	public $result;
	public $response = array();
	public $xss_clean = TRUE;
	public function __construct($config = array()){
		$this->xmlrpc_backslash = chr(92) . chr(92);
		$this->xmlrpcTypes = array($this->xmlrpcI4 => '1', $this->xmlrpcInt => '1', $this->xmlrpcBoolean => '1', $this->xmlrpcString => '1', $this->xmlrpcDouble => '1', $this->xmlrpcDateTime => '1', $this->xmlrpcBase64 => '1', $this->xmlrpcArray => '2', $this->xmlrpcStruct => '3');
		$this->valid_parents = array('BOOLEAN' => array('VALUE'), 'I4' => array('VALUE'), 'INT' => array('VALUE'), 'STRING' => array('VALUE'), 'DOUBLE' => array('VALUE'), 'DATETIME.ISO8601' => array('VALUE'), 'BASE64' => array('VALUE'), 'ARRAY' => array('VALUE'), 'STRUCT' => array('VALUE'), 'PARAM' => array('PARAMS'), 'METHODNAME' => array('METHODCALL'), 'PARAMS' => array('METHODCALL', 'METHODRESPONSE'), 'MEMBER' => array('STRUCT'), 'NAME' => array('MEMBER'), 'DATA' => array('ARRAY'), 'FAULT' => array('METHODRESPONSE'), 'VALUE' => array('MEMBER', 'DATA', 'PARAM', 'FAULT'));
		$this->xmlrpcerr['unknown_method'] = '1';
		$this->xmlrpcstr['unknown_method'] = 'This is not a known method for this XML-RPC Server';
		$this->xmlrpcerr['invalid_return'] = '2';
		$this->xmlrpcstr['invalid_return'] = 'The XML data received was either invalid or not in the correct form for XML-RPC. Turn on debugging to examine the XML data further.';
		$this->xmlrpcerr['incorrect_params'] = '3';
		$this->xmlrpcstr['incorrect_params'] = 'Incorrect parameters were passed to method';
		$this->xmlrpcerr['introspect_unknown'] = '4';
		$this->xmlrpcstr['introspect_unknown'] = 'Cannot inspect signature for request: method unknown';
		$this->xmlrpcerr['http_error'] = '5';
		$this->xmlrpcstr['http_error'] = "Did not receive a '200 OK' response from remote server.";
		$this->xmlrpcerr['no_data'] = '6';
		$this->xmlrpcstr['no_data'] = 'No data received from server.';
		$this->initialize($config);
		log_message('info', 'XML-RPC Class Initialized');
	}
	public function initialize($config = array()){
		if(count($config) > 0) {
			foreach($config as $key => $val) {
				if(isset($this->$key)) {
					$this->$key = $val;
				}
			}
		}
	}
	public function server($url, $port = 80, $proxy = FALSE, $proxy_port = 8080){
		if(stripos($url, 'http') !== 0) {
			$url = 'http://' . $url;
		}
		$parts = parse_url($url);
		if(isset($parts['user'], $parts['pass'])) {
			$parts['host'] = $parts['user'] . ':' . $parts['pass'] . '@' . $parts['host'];
		}
		$path = isset($parts['path']) ? $parts['path'] : '/';
		if(!empty($parts['query'])) {
			$path .= '?' . $parts['query'];
		}
		$this->client = new XML_RPC_Client($path, $parts['host'], $port, $proxy, $proxy_port);
	}
	public function timeout($seconds = 5){
		if($this->client !== NULL && is_int($seconds)) {
			$this->client->timeout = $seconds;
		}
	}
	public function method($function){
		$this->method = $function;
	}
	public function request($incoming){
		if(!is_array($incoming)) {
			return;
		}
		$this->data = array();
		foreach($incoming as $key => $value) {
			$this->data[$key] = $this->values_parsing($value);
		}
	}
	public function set_debug($flag = TRUE){
		$this->debug = ($flag === TRUE);
	}
	public function values_parsing($value){
		if(is_array($value) && array_key_exists(0, $value)) {
			if(!isset($value[1], $this->xmlrpcTypes[$value[1]])) {
				$temp = new XML_RPC_Values($value[0], (is_array($value[0]) ? 'array' : 'string'));
			} else {
				if(is_array($value[0]) && ($value[1] === 'struct' OR $value[1] === 'array')) {
					while(list($k) = each($value[0])) {
						$value[0][$k] = $this->values_parsing($value[0][$k]);
					}
				}
				$temp = new XML_RPC_Values($value[0], $value[1]);
			}
		} else {
			$temp = new XML_RPC_Values($value, 'string');
		}
		return $temp;
	}
	public function send_request(){
		$this->message = new XML_RPC_Message($this->method, $this->data);
		$this->message->debug = $this->debug;
		if(!$this->result = $this->client->send($this->message) OR !is_object($this->result->val)) {
			$this->error = $this->result->errstr;
			return FALSE;
		}
		$this->response = $this->result->decode();
		return TRUE;
	}
	public function display_error(){
		return $this->error;
	}
	public function display_response(){
		return $this->response;
	}
	public function send_error_message($number, $message){
		return new XML_RPC_Response(0, $number, $message);
	}
	public function send_response($response){
		return new XML_RPC_Response($this->values_parsing($response));
	}
}
class XML_RPC_Client extends CI_Xmlrpc{
	public $path = '';
	public $server = '';
	public $port = 80;
	public $username;
	public $password;
	public $proxy = FALSE;
	public $proxy_port = 8080;
	public $errno = '';
	public $errstring = '';
	public $timeout = 5;
	public $no_multicall = FALSE;
	public function __construct($path, $server, $port = 80, $proxy = FALSE, $proxy_port = 8080){
		parent::__construct();
		$url = parse_url('http://' . $server);
		if(isset($url['user'], $url['pass'])) {
			$this->username = $url['user'];
			$this->password = $url['pass'];
		}
		$this->port = $port;
		$this->server = $url['host'];
		$this->path = $path;
		$this->proxy = $proxy;
		$this->proxy_port = $proxy_port;
	}
	public function send($msg){
		if(is_array($msg)) {
			return new XML_RPC_Response(0, $this->xmlrpcerr['multicall_recursion'], $this->xmlrpcstr['multicall_recursion']);
		}
		return $this->sendPayload($msg);
	}
	public function sendPayload($msg){
		if($this->proxy === FALSE) {
			$server = $this->server;
			$port = $this->port;
		} else {
			$server = $this->proxy;
			$port = $this->proxy_port;
		}
		$fp = @fsockopen($server, $port, $this->errno, $this->errstring, $this->timeout);
		if(!is_resource($fp)) {
			error_log($this->xmlrpcstr['http_error']);
			return new XML_RPC_Response(0, $this->xmlrpcerr['http_error'], $this->xmlrpcstr['http_error']);
		}
		if(empty($msg->payload)) {
			$msg->createPayload();
		}
		$r = "\r\n";
		$op = 'POST ' . $this->path . ' HTTP/1.0' . $r . 'Host: ' . $this->server . $r . 'Content-Type: text/xml' . $r . (isset($this->username, $this->password) ? 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password) . $r : '') . 'User-Agent: ' . $this->xmlrpcName . $r . 'Content-Length: ' . strlen($msg->payload) . $r . $r . $msg->payload;
		stream_set_timeout($fp, $this->timeout);
		for($written = $timestamp = 0, $length = strlen($op); $written < $length; $written += $result) {
			if(($result = fwrite($fp, substr($op, $written))) === FALSE) {
				break;
			} elseif($result === 0) {
				if($timestamp === 0) {
					$timestamp = time();
				} elseif($timestamp < (time() - $this->timeout)) {
					$result = FALSE;
					break;
				}
			} else {
				$timestamp = 0;
			}
		}
		if($result === FALSE) {
			error_log($this->xmlrpcstr['http_error']);
			return new XML_RPC_Response(0, $this->xmlrpcerr['http_error'], $this->xmlrpcstr['http_error']);
		}
		$resp = $msg->parseResponse($fp);
		fclose($fp);
		return $resp;
	}
}
class XML_RPC_Response{
	public $val = 0;
	public $errno = 0;
	public $errstr = '';
	public $headers = array();
	public $xss_clean = TRUE;
	public function __construct($val, $code = 0, $fstr = ''){
		if($code !== 0) {
			$this->errno = $code;
			$this->errstr = htmlspecialchars($fstr, (is_php('5.4') ? ENT_XML1 | ENT_NOQUOTES : ENT_NOQUOTES), 'UTF-8');
		} elseif(!is_object($val)) {
			error_log("Invalid type '" . gettype($val) . "' (value: " . $val . ') passed to XML_RPC_Response. Defaulting to empty value.');
			$this->val = new XML_RPC_Values();
		} else {
			$this->val = $val;
		}
	}
	public function faultCode(){
		return $this->errno;
	}
	public function faultString(){
		return $this->errstr;
	}
	public function value(){
		return $this->val;
	}
	public function prepare_response(){
		return "<methodResponse>\n" . ($this->errno ? '<fault>
	<value>
		<struct>
			<member>
				<name>faultCode</name>
				<value><int>' . $this->errno . '</int></value>
			</member>
			<member>
				<name>faultString</name>
				<value><string>' . $this->errstr . '</string></value>
			</member>
		</struct>
	</value>
</fault>' : "<params>\n<param>\n" . $this->val->serialize_class() . "</param>\n</params>") . "\n</methodResponse>";
	}
	public function decode($array = NULL){
		$CI =& get_instance();
		if(is_array($array)) {
			while(list($key) = each($array)) {
				if(is_array($array[$key])) {
					$array[$key] = $this->decode($array[$key]);
				} elseif($this->xss_clean) {
					$array[$key] = $CI->security->xss_clean($array[$key]);
				}
			}
			return $array;
		}
		$result = $this->xmlrpc_decoder($this->val);
		if(is_array($result)) {
			$result = $this->decode($result);
		} elseif($this->xss_clean) {
			$result = $CI->security->xss_clean($result);
		}
		return $result;
	}
	public function xmlrpc_decoder($xmlrpc_val){
		$kind = $xmlrpc_val->kindOf();
		if($kind === 'scalar') {
			return $xmlrpc_val->scalarval();
		} elseif($kind === 'array') {
			reset($xmlrpc_val->me);
			$b = current($xmlrpc_val->me);
			$arr = array();
			for($i = 0, $size = count($b); $i < $size; $i++) {
				$arr[] = $this->xmlrpc_decoder($xmlrpc_val->me['array'][$i]);
			}
			return $arr;
		} elseif($kind === 'struct') {
			reset($xmlrpc_val->me['struct']);
			$arr = array();
			while(list($key, $value) = each($xmlrpc_val->me['struct'])) {
				$arr[$key] = $this->xmlrpc_decoder($value);
			}
			return $arr;
		}
	}
	public function iso8601_decode($time, $utc = FALSE){
		$t = 0;
		if(preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})/', $time, $regs)) {
			$fnc = ($utc === TRUE) ? 'gmmktime' : 'mktime';
			$t = $fnc($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
		}
		return $t;
	}
}
class XML_RPC_Message extends CI_Xmlrpc{
	public $payload;
	public $method_name;
	public $params = array();
	public $xh = array();
	public function __construct($method, $pars = FALSE){
		parent::__construct();
		$this->method_name = $method;
		if(is_array($pars) && count($pars) > 0) {
			for($i = 0, $c = count($pars); $i < $c; $i++) {
				$this->params[] = $pars[$i];
			}
		}
	}
	public function createPayload(){
		$this->payload = '<?xml version="1.0"?' . ">\r\n<methodCall>\r\n" . '<methodName>' . $this->method_name . "</methodName>\r\n" . "<params>\r\n";
		for($i = 0, $c = count($this->params); $i < $c; $i++) {
			$p = $this->params[$i];
			$this->payload .= "<param>\r\n" . $p->serialize_class() . "</param>\r\n";
		}
		$this->payload .= "</params>\r\n</methodCall>\r\n";
	}
	public function parseResponse($fp){
		$data = '';
		while($datum = fread($fp, 4096)) {
			$data .= $datum;
		}
		if($this->debug === TRUE) {
			echo "<pre>---DATA---\n" . htmlspecialchars($data) . "\n---END DATA---\n\n</pre>";
		}
		if($data === '') {
			error_log($this->xmlrpcstr['no_data']);
			return new XML_RPC_Response(0, $this->xmlrpcerr['no_data'], $this->xmlrpcstr['no_data']);
		}
		if(strpos($data, 'HTTP') === 0 && !preg_match('/^HTTP\/[0-9\.]+ 200 /', $data)) {
			$errstr = substr($data, 0, strpos($data, "\n") - 1);
			return new XML_RPC_Response(0, $this->xmlrpcerr['http_error'], $this->xmlrpcstr['http_error'] . ' (' . $errstr . ')');
		}
		$parser = xml_parser_create($this->xmlrpc_defencoding);
		$pname = (string)$parser;
		$this->xh[$pname] = array('isf' => 0, 'ac' => '', 'headers' => array(), 'stack' => array(), 'valuestack' => array(), 'isf_reason' => 0);
		xml_set_object($parser, $this);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, TRUE);
		xml_set_element_handler($parser, 'open_tag', 'closing_tag');
		xml_set_character_data_handler($parser, 'character_data');
		$lines = explode("\r\n", $data);
		while(($line = array_shift($lines))) {
			if(strlen($line) < 1) {
				break;
			}
			$this->xh[$pname]['headers'][] = $line;
		}
		$data = implode("\r\n", $lines);
		if(!xml_parse($parser, $data, count($data))) {
			$errstr = sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($parser)), xml_get_current_line_number($parser));
			$r = new XML_RPC_Response(0, $this->xmlrpcerr['invalid_return'], $this->xmlrpcstr['invalid_return']);
			xml_parser_free($parser);
			return $r;
		}
		xml_parser_free($parser);
		if($this->xh[$pname]['isf'] > 1) {
			if($this->debug === TRUE) {
				echo "---Invalid Return---\n" . $this->xh[$pname]['isf_reason'] . "---Invalid Return---\n\n";
			}
			return new XML_RPC_Response(0, $this->xmlrpcerr['invalid_return'], $this->xmlrpcstr['invalid_return'] . ' ' . $this->xh[$pname]['isf_reason']);
		} elseif(!is_object($this->xh[$pname]['value'])) {
			return new XML_RPC_Response(0, $this->xmlrpcerr['invalid_return'], $this->xmlrpcstr['invalid_return'] . ' ' . $this->xh[$pname]['isf_reason']);
		}
		if($this->debug === TRUE) {
			echo '<pre>';
			if(count($this->xh[$pname]['headers'] > 0)) {
				echo "---HEADERS---\n";
				foreach($this->xh[$pname]['headers'] as $header) {
					echo $header . "\n";
				}
				echo "---END HEADERS---\n\n";
			}
			echo "---DATA---\n" . htmlspecialchars($data) . "\n---END DATA---\n\n---PARSED---\n";
			var_dump($this->xh[$pname]['value']);
			echo "\n---END PARSED---</pre>";
		}
		$v = $this->xh[$pname]['value'];
		if($this->xh[$pname]['isf']) {
			$errno_v = $v->me['struct']['faultCode'];
			$errstr_v = $v->me['struct']['faultString'];
			$errno = $errno_v->scalarval();
			if($errno === 0) {
				$errno = -1;
			}
			$r = new XML_RPC_Response($v, $errno, $errstr_v->scalarval());
		} else {
			$r = new XML_RPC_Response($v);
		}
		$r->headers = $this->xh[$pname]['headers'];
		return $r;
	}
	public function open_tag($the_parser, $name){
		$the_parser = (string)$the_parser;
		if($this->xh[$the_parser]['isf'] > 1) return;
		if(count($this->xh[$the_parser]['stack']) === 0) {
			if($name !== 'METHODRESPONSE' && $name !== 'METHODCALL') {
				$this->xh[$the_parser]['isf'] = 2;
				$this->xh[$the_parser]['isf_reason'] = 'Top level XML-RPC element is missing';
				return;
			}
		} elseif(!in_array($this->xh[$the_parser]['stack'][0], $this->valid_parents[$name], TRUE)) {
			$this->xh[$the_parser]['isf'] = 2;
			$this->xh[$the_parser]['isf_reason'] = 'XML-RPC element ' . $name . ' cannot be child of ' . $this->xh[$the_parser]['stack'][0];
			return;
		}
		switch($name) {
			case 'STRUCT':
			case 'ARRAY':
				$cur_val = array('value' => array(), 'type' => $name);
				array_unshift($this->xh[$the_parser]['valuestack'], $cur_val);
				break;
			case 'METHODNAME':
			case 'NAME':
				$this->xh[$the_parser]['ac'] = '';
				break;
			case 'FAULT':
				$this->xh[$the_parser]['isf'] = 1;
				break;
			case 'PARAM':
				$this->xh[$the_parser]['value'] = NULL;
				break;
			case 'VALUE':
				$this->xh[$the_parser]['vt'] = 'value';
				$this->xh[$the_parser]['ac'] = '';
				$this->xh[$the_parser]['lv'] = 1;
				break;
			case 'I4':
			case 'INT':
			case 'STRING':
			case 'BOOLEAN':
			case 'DOUBLE':
			case 'DATETIME.ISO8601':
			case 'BASE64':
				if($this->xh[$the_parser]['vt'] !== 'value') {
					$this->xh[$the_parser]['isf'] = 2;
					$this->xh[$the_parser]['isf_reason'] = 'There is a ' . $name . ' element following a ' . $this->xh[$the_parser]['vt'] . ' element inside a single value';
					return;
				}
				$this->xh[$the_parser]['ac'] = '';
				break;
			case 'MEMBER':
				$this->xh[$the_parser]['valuestack'][0]['name'] = '';
				$this->xh[$the_parser]['value'] = NULL;
				break;
			case 'DATA':
			case 'METHODCALL':
			case 'METHODRESPONSE':
			case 'PARAMS':
				break;
			default:
				$this->xh[$the_parser]['isf'] = 2;
				$this->xh[$the_parser]['isf_reason'] = 'Invalid XML-RPC element found: ' . $name;
				break;
		}
		array_unshift($this->xh[$the_parser]['stack'], $name);
		$name === 'VALUE' OR $this->xh[$the_parser]['lv'] = 0;
	}
	public function closing_tag($the_parser, $name){
		$the_parser = (string)$the_parser;
		if($this->xh[$the_parser]['isf'] > 1) return;
		$curr_elem = array_shift($this->xh[$the_parser]['stack']);
		switch($name) {
			case 'STRUCT':
			case 'ARRAY':
				$cur_val = array_shift($this->xh[$the_parser]['valuestack']);
				$this->xh[$the_parser]['value'] = isset($cur_val['values']) ? $cur_val['values'] : array();
				$this->xh[$the_parser]['vt'] = strtolower($name);
				break;
			case 'NAME':
				$this->xh[$the_parser]['valuestack'][0]['name'] = $this->xh[$the_parser]['ac'];
				break;
			case 'BOOLEAN':
			case 'I4':
			case 'INT':
			case 'STRING':
			case 'DOUBLE':
			case 'DATETIME.ISO8601':
			case 'BASE64':
				$this->xh[$the_parser]['vt'] = strtolower($name);
				if($name === 'STRING') {
					$this->xh[$the_parser]['value'] = $this->xh[$the_parser]['ac'];
				} elseif($name === 'DATETIME.ISO8601') {
					$this->xh[$the_parser]['vt'] = $this->xmlrpcDateTime;
					$this->xh[$the_parser]['value'] = $this->xh[$the_parser]['ac'];
				} elseif($name === 'BASE64') {
					$this->xh[$the_parser]['value'] = base64_decode($this->xh[$the_parser]['ac']);
				} elseif($name === 'BOOLEAN') {
					$this->xh[$the_parser]['value'] = (bool)$this->xh[$the_parser]['ac'];
				} elseif($name == 'DOUBLE') {
					$this->xh[$the_parser]['value'] = preg_match('/^[+-]?[eE0-9\t \.]+$/', $this->xh[$the_parser]['ac']) ? (float)$this->xh[$the_parser]['ac'] : 'ERROR_NON_NUMERIC_FOUND';
				} else {
					$this->xh[$the_parser]['value'] = preg_match('/^[+-]?[0-9\t ]+$/', $this->xh[$the_parser]['ac']) ? (int)$this->xh[$the_parser]['ac'] : 'ERROR_NON_NUMERIC_FOUND';
				}
				$this->xh[$the_parser]['ac'] = '';
				$this->xh[$the_parser]['lv'] = 3;
				break;
			case 'VALUE':
				if($this->xh[$the_parser]['vt'] == 'value') {
					$this->xh[$the_parser]['value'] = $this->xh[$the_parser]['ac'];
					$this->xh[$the_parser]['vt'] = $this->xmlrpcString;
				}
				$temp = new XML_RPC_Values($this->xh[$the_parser]['value'], $this->xh[$the_parser]['vt']);
				if(count($this->xh[$the_parser]['valuestack']) && $this->xh[$the_parser]['valuestack'][0]['type'] === 'ARRAY') {
					$this->xh[$the_parser]['valuestack'][0]['values'][] = $temp;
				} else {
					$this->xh[$the_parser]['value'] = $temp;
				}
				break;
			case 'MEMBER':
				$this->xh[$the_parser]['ac'] = '';
				if($this->xh[$the_parser]['value']) {
					$this->xh[$the_parser]['valuestack'][0]['values'][$this->xh[$the_parser]['valuestack'][0]['name']] = $this->xh[$the_parser]['value'];
				}
				break;
			case 'DATA':
				$this->xh[$the_parser]['ac'] = '';
				break;
			case 'PARAM':
				if($this->xh[$the_parser]['value']) {
					$this->xh[$the_parser]['params'][] = $this->xh[$the_parser]['value'];
				}
				break;
			case 'METHODNAME':
				$this->xh[$the_parser]['method'] = ltrim($this->xh[$the_parser]['ac']);
				break;
			case 'PARAMS':
			case 'FAULT':
			case 'METHODCALL':
			case 'METHORESPONSE':
				break;
			default:
				break;
		}
	}
	public function character_data($the_parser, $data){
		$the_parser = (string)$the_parser;
		if($this->xh[$the_parser]['isf'] > 1) return;
		if($this->xh[$the_parser]['lv'] !== 3) {
			if($this->xh[$the_parser]['lv'] === 1) {
				$this->xh[$the_parser]['lv'] = 2;
			}
			if(!isset($this->xh[$the_parser]['ac'])) {
				$this->xh[$the_parser]['ac'] = '';
			}
			$this->xh[$the_parser]['ac'] .= $data;
		}
	}
	public function addParam($par){
		$this->params[] = $par;
	}
	public function output_parameters(array $array = array()){
		$CI =& get_instance();
		if(!empty($array)) {
			while(list($key) = each($array)) {
				if(is_array($array[$key])) {
					$array[$key] = $this->output_parameters($array[$key]);
				} elseif($key !== 'bits' && $this->xss_clean) {
					$array[$key] = $CI->security->xss_clean($array[$key]);
				}
			}
			return $array;
		}
		$parameters = array();
		for($i = 0, $c = count($this->params); $i < $c; $i++) {
			$a_param = $this->decode_message($this->params[$i]);
			if(is_array($a_param)) {
				$parameters[] = $this->output_parameters($a_param);
			} else {
				$parameters[] = ($this->xss_clean) ? $CI->security->xss_clean($a_param) : $a_param;
			}
		}
		return $parameters;
	}
	public function decode_message($param){
		$kind = $param->kindOf();
		if($kind === 'scalar') {
			return $param->scalarval();
		} elseif($kind === 'array') {
			reset($param->me);
			$b = current($param->me);
			$arr = array();
			for($i = 0, $c = count($b); $i < $c; $i++) {
				$arr[] = $this->decode_message($param->me['array'][$i]);
			}
			return $arr;
		} elseif($kind === 'struct') {
			reset($param->me['struct']);
			$arr = array();
			while(list($key, $value) = each($param->me['struct'])) {
				$arr[$key] = $this->decode_message($value);
			}
			return $arr;
		}
	}
}
class XML_RPC_Values extends CI_Xmlrpc{
	public $me = array();
	public $mytype = 0;
	public function __construct($val = -1, $type = ''){
		parent::__construct();
		if($val !== -1 OR $type !== '') {
			$type = $type === '' ? 'string' : $type;
			if($this->xmlrpcTypes[$type] == 1) {
				$this->addScalar($val, $type);
			} elseif($this->xmlrpcTypes[$type] == 2) {
				$this->addArray($val);
			} elseif($this->xmlrpcTypes[$type] == 3) {
				$this->addStruct($val);
			}
		}
	}
	public function addScalar($val, $type = 'string'){
		$typeof = $this->xmlrpcTypes[$type];
		if($this->mytype === 1) {
			echo '<strong>XML_RPC_Values</strong>: scalar can have only one value<br />';
			return 0;
		}
		if($typeof != 1) {
			echo '<strong>XML_RPC_Values</strong>: not a scalar type (${typeof})<br />';
			return 0;
		}
		if($type === $this->xmlrpcBoolean) {
			$val = (int)(strcasecmp($val, 'true') === 0 OR $val === 1 OR ($val === TRUE && strcasecmp($val, 'false')));
		}
		if($this->mytype === 2) {
			$ar = $this->me['array'];
			$ar[] = new XML_RPC_Values($val, $type);
			$this->me['array'] = $ar;
		} else {
			$this->me[$type] = $val;
			$this->mytype = $typeof;
		}
		return 1;
	}
	public function addArray($vals){
		if($this->mytype !== 0) {
			echo '<strong>XML_RPC_Values</strong>: already initialized as a [' . $this->kindOf() . ']<br />';
			return 0;
		}
		$this->mytype = $this->xmlrpcTypes['array'];
		$this->me['array'] = $vals;
		return 1;
	}
	public function addStruct($vals){
		if($this->mytype !== 0) {
			echo '<strong>XML_RPC_Values</strong>: already initialized as a [' . $this->kindOf() . ']<br />';
			return 0;
		}
		$this->mytype = $this->xmlrpcTypes['struct'];
		$this->me['struct'] = $vals;
		return 1;
	}
	public function kindOf(){
		switch($this->mytype) {
			case 3:
				return 'struct';
			case 2:
				return 'array';
			case 1:
				return 'scalar';
			default:
				return 'undef';
		}
	}
	public function serializedata($typ, $val){
		$rs = '';
		switch($this->xmlrpcTypes[$typ]) {
			case 3:
				$rs .= "<struct>\n";
				reset($val);
				while(list($key2, $val2) = each($val)) {
					$rs .= "<member>\n<name>{$key2}</name>\n" . $this->serializeval($val2) . "</member>\n";
				}
				$rs .= '</struct>';
				break;
			case 2:
				$rs .= "<array>\n<data>\n";
				for($i = 0, $c = count($val); $i < $c; $i++) {
					$rs .= $this->serializeval($val[$i]);
				}
				$rs .= "</data>\n</array>\n";
				break;
			case 1:
				switch($typ) {
					case $this->xmlrpcBase64:
						$rs .= '<' . $typ . '>' . base64_encode((string)$val) . '</' . $typ . ">\n";
						break;
					case $this->xmlrpcBoolean:
						$rs .= '<' . $typ . '>' . ((bool)$val ? '1' : '0') . '</' . $typ . ">\n";
						break;
					case $this->xmlrpcString:
						$rs .= '<' . $typ . '>' . htmlspecialchars((string)$val) . '</' . $typ . ">\n";
						break;
					default:
						$rs .= '<' . $typ . '>' . $val . '</' . $typ . ">\n";
						break;
				}
			default:
				break;
		}
		return $rs;
	}
	public function serialize_class(){
		return $this->serializeval($this);
	}
	public function serializeval($o){
		$ar = $o->me;
		reset($ar);
		list($typ, $val) = each($ar);
		return "<value>\n" . $this->serializedata($typ, $val) . "</value>\n";
	}
	public function scalarval(){
		reset($this->me);
		return current($this->me);
	}
	public function iso8601_encode($time, $utc = FALSE){
		return ($utc) ? strftime('%Y%m%dT%H:%i:%s', $time) : gmstrftime('%Y%m%dT%H:%i:%s', $time);
	}
} 