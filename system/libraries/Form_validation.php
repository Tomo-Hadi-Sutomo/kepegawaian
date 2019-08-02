<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Form_validation{
	protected $CI;
	protected $_field_data = array();
	protected $_config_rules = array();
	protected $_error_array = array();
	protected $_error_messages = array();
	protected $_error_prefix = '<p>';
	protected $_error_suffix = '</p>';
	protected $error_string = '';
	protected $_safe_form_data = FALSE;
	public $validation_data = array();
	public function __construct($rules = array()){
		$this->CI =& get_instance();
		if(isset($rules['error_prefix'])) {
			$this->_error_prefix = $rules['error_prefix'];
			unset($rules['error_prefix']);
		}
		if(isset($rules['error_suffix'])) {
			$this->_error_suffix = $rules['error_suffix'];
			unset($rules['error_suffix']);
		}
		$this->_config_rules = $rules;
		$this->CI->load->helper('form');
		log_message('info', 'Form Validation Class Initialized');
	}
	public function set_rules($field, $label = '', $rules = array(), $errors = array()){
		if($this->CI->input->method() !== 'post' && empty($this->validation_data)) {
			return $this;
		}
		if(is_array($field)) {
			foreach($field as $row) {
				if(!isset($row['field'], $row['rules'])) {
					continue;
				}
				$label = isset($row['label']) ? $row['label'] : $row['field'];
				$errors = (isset($row['errors']) && is_array($row['errors'])) ? $row['errors'] : array();
				$this->set_rules($row['field'], $label, $row['rules'], $errors);
			}
			return $this;
		}
		if(!is_string($field) OR $field === '' OR empty($rules)) {
			return $this;
		} elseif(!is_array($rules)) {
			if(!is_string($rules)) {
				return $this;
			}
			$rules = preg_split('/\|(?![^\[]*\])/', $rules);
		}
		$label = ($label === '') ? $field : $label;
		$indexes = array();
		if(($is_array = (bool)preg_match_all('/\[(.*?)\]/', $field, $matches)) === TRUE) {
			sscanf($field, '%[^[][', $indexes[0]);
			for($i = 0, $c = count($matches[0]); $i < $c; $i++) {
				if($matches[1][$i] !== '') {
					$indexes[] = $matches[1][$i];
				}
			}
		}
		$this->_field_data[$field] = array('field' => $field, 'label' => $label, 'rules' => $rules, 'errors' => $errors, 'is_array' => $is_array, 'keys' => $indexes, 'postdata' => NULL, 'error' => '');
		return $this;
	}
	public function set_data(array $data){
		if(!empty($data)) {
			$this->validation_data = $data;
		}
		return $this;
	}
	public function set_message($lang, $val = ''){
		if(!is_array($lang)) {
			$lang = array($lang => $val);
		}
		$this->_error_messages = array_merge($this->_error_messages, $lang);
		return $this;
	}
	public function set_error_delimiters($prefix = '<p>', $suffix = '</p>'){
		$this->_error_prefix = $prefix;
		$this->_error_suffix = $suffix;
		return $this;
	}
	public function error($field, $prefix = '', $suffix = ''){
		if(empty($this->_field_data[$field]['error'])) {
			return '';
		}
		if($prefix === '') {
			$prefix = $this->_error_prefix;
		}
		if($suffix === '') {
			$suffix = $this->_error_suffix;
		}
		return $prefix . $this->_field_data[$field]['error'] . $suffix;
	}
	public function error_array(){
		return $this->_error_array;
	}
	public function error_string($prefix = '', $suffix = ''){
		if(count($this->_error_array) === 0) {
			return '';
		}
		if($prefix === '') {
			$prefix = $this->_error_prefix;
		}
		if($suffix === '') {
			$suffix = $this->_error_suffix;
		}
		$str = '';
		foreach($this->_error_array as $val) {
			if($val !== '') {
				$str .= $prefix . $val . $suffix . "\n";
			}
		}
		return $str;
	}
	public function run($group = ''){
		$validation_array = empty($this->validation_data) ? $_POST : $this->validation_data;
		if(count($this->_field_data) === 0) {
			if(count($this->_config_rules) === 0) {
				return FALSE;
			}
			if(empty($group)) {
				$group = trim($this->CI->uri->ruri_string(), '/');
				isset($this->_config_rules[$group]) OR $group = $this->CI->router->class . '/' . $this->CI->router->method;
			}
			$this->set_rules(isset($this->_config_rules[$group]) ? $this->_config_rules[$group] : $this->_config_rules);
			if(count($this->_field_data) === 0) {
				log_message('debug', 'Unable to find validation rules');
				return FALSE;
			}
		}
		$this->CI->lang->load('form_validation');
		foreach($this->_field_data as $field => &$row) {
			if($row['is_array'] === TRUE) {
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
			} elseif(isset($validation_array[$field])) {
				$this->_field_data[$field]['postdata'] = $validation_array[$field];
			}
		}
		foreach($this->_field_data as $field => &$row) {
			if(empty($row['rules'])) {
				continue;
			}
			$this->_execute($row, $row['rules'], $row['postdata']);
		}
		$total_errors = count($this->_error_array);
		if($total_errors > 0) {
			$this->_safe_form_data = TRUE;
		}
		empty($this->validation_data) && $this->_reset_post_array();
		return ($total_errors === 0);
	}
	protected function _prepare_rules($rules){
		$new_rules = array();
		$callbacks = array();
		foreach($rules as &$rule) {
			if($rule === 'required') {
				array_unshift($new_rules, 'required');
			} elseif($rule === 'isset' && (empty($new_rules) OR $new_rules[0] !== 'required')) {
				array_unshift($new_rules, 'isset');
			} elseif(is_string($rule) && strncmp('callback_', $rule, 9) === 0) {
				$callbacks[] = $rule;
			} elseif(is_callable($rule)) {
				$callbacks[] = $rule;
			} elseif(is_array($rule) && isset($rule[0], $rule[1]) && is_callable($rule[1])) {
				$callbacks[] = $rule;
			} else {
				$new_rules[] = $rule;
			}
		}
		return array_merge($callbacks, $new_rules);
	}
	protected function _reduce_array($array, $keys, $i = 0){
		if(is_array($array) && isset($keys[$i])) {
			return isset($array[$keys[$i]]) ? $this->_reduce_array($array[$keys[$i]], $keys, ($i + 1)) : NULL;
		}
		return ($array === '') ? NULL : $array;
	}
	protected function _reset_post_array(){
		foreach($this->_field_data as $field => $row) {
			if($row['postdata'] !== NULL) {
				if($row['is_array'] === FALSE) {
					isset($_POST[$field]) && $_POST[$field] = $row['postdata'];
				} else {
					$post_ref =& $_POST;
					if(count($row['keys']) === 1) {
						$post_ref =& $post_ref[current($row['keys'])];
					} else {
						foreach($row['keys'] as $val) {
							$post_ref =& $post_ref[$val];
						}
					}
					$post_ref = $row['postdata'];
				}
			}
		}
	}
	protected function _execute($row, $rules, $postdata = NULL, $cycles = 0){
		if(is_array($postdata) && !empty($postdata)) {
			foreach($postdata as $key => $val) {
				$this->_execute($row, $rules, $val, $key);
			}
			return;
		}
		$rules = $this->_prepare_rules($rules);
		foreach($rules as $rule) {
			$_in_array = FALSE;
			if($row['is_array'] === TRUE && is_array($this->_field_data[$row['field']]['postdata'])) {
				if(!isset($this->_field_data[$row['field']]['postdata'][$cycles])) {
					continue;
				}
				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			} else {
				$postdata = is_array($this->_field_data[$row['field']]['postdata']) ? NULL : $this->_field_data[$row['field']]['postdata'];
			}
			$callback = $callable = FALSE;
			if(is_string($rule)) {
				if(strpos($rule, 'callback_') === 0) {
					$rule = substr($rule, 9);
					$callback = TRUE;
				}
			} elseif(is_callable($rule)) {
				$callable = TRUE;
			} elseif(is_array($rule) && isset($rule[0], $rule[1]) && is_callable($rule[1])) {
				$callable = $rule[0];
				$rule = $rule[1];
			}
			$param = FALSE;
			if(!$callable && preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
				$rule = $match[1];
				$param = $match[2];
			}
			if(($postdata === NULL OR $postdata === '') && $callback === FALSE && $callable === FALSE && !in_array($rule, array('required', 'isset', 'matches'), TRUE)) {
				continue;
			}
			if($callback OR $callable !== FALSE) {
				if($callback) {
					if(!method_exists($this->CI, $rule)) {
						log_message('debug', 'Unable to find callback validation rule: ' . $rule);
						$result = FALSE;
					} else {
						$result = $this->CI->$rule($postdata, $param);
					}
				} else {
					$result = is_array($rule) ? $rule[0]->{$rule[1]}($postdata) : $rule($postdata);
					if($callable !== FALSE) {
						$rule = $callable;
					}
				}
				if($_in_array === TRUE) {
					$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
				} else {
					$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
				}
			} elseif(!method_exists($this, $rule)) {
				if(function_exists($rule)) {
					$result = ($param !== FALSE) ? $rule($postdata, $param) : $rule($postdata);
					if($_in_array === TRUE) {
						$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
					} else {
						$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
					}
				} else {
					log_message('debug', 'Unable to find validation rule: ' . $rule);
					$result = FALSE;
				}
			} else {
				$result = $this->$rule($postdata, $param);
				if($_in_array === TRUE) {
					$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
				} else {
					$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
				}
			}
			if($result === FALSE) {
				if(!is_string($rule)) {
					$line = $this->CI->lang->line('form_validation_error_message_not_set') . '(Anonymous function)';
				} else {
					$line = $this->_get_error_message($rule, $row['field']);
				}
				if(isset($this->_field_data[$param], $this->_field_data[$param]['label'])) {
					$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}
				$message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']), $param);
				$this->_field_data[$row['field']]['error'] = $message;
				if(!isset($this->_error_array[$row['field']])) {
					$this->_error_array[$row['field']] = $message;
				}
				return;
			}
		}
	}
	protected function _get_error_message($rule, $field){
		if(isset($this->_field_data[$field]['errors'][$rule])) {
			return $this->_field_data[$field]['errors'][$rule];
		} elseif(isset($this->_error_messages[$rule])) {
			return $this->_error_messages[$rule];
		} elseif(FALSE !== ($line = $this->CI->lang->line('form_validation_' . $rule))) {
			return $line;
		} elseif(FALSE !== ($line = $this->CI->lang->line($rule, FALSE))) {
			return $line;
		}
		return $this->CI->lang->line('form_validation_error_message_not_set') . '(' . $rule . ')';
	}
	protected function _translate_fieldname($fieldname){
		if(sscanf($fieldname, 'lang:%s', $line) === 1 && FALSE === ($fieldname = $this->CI->lang->line($line, FALSE))) {
			return $line;
		}
		return $fieldname;
	}
	protected function _build_error_msg($line, $field = '', $param = ''){
		if(strpos($line, '%s') !== FALSE) {
			return sprintf($line, $field, $param);
		}
		return str_replace(array('{field}', '{param}'), array($field, $param), $line);
	}
	public function has_rule($field){
		return isset($this->_field_data[$field]);
	}
	public function set_value($field = '', $default = ''){
		if(!isset($this->_field_data[$field], $this->_field_data[$field]['postdata'])) {
			return $default;
		}
		if(is_array($this->_field_data[$field]['postdata'])) {
			return array_shift($this->_field_data[$field]['postdata']);
		}
		return $this->_field_data[$field]['postdata'];
	}
	public function set_select($field = '', $value = '', $default = FALSE){
		if(!isset($this->_field_data[$field], $this->_field_data[$field]['postdata'])) {
			return ($default === TRUE && count($this->_field_data) === 0) ? ' selected="selected"' : '';
		}
		$field = $this->_field_data[$field]['postdata'];
		$value = (string)$value;
		if(is_array($field)) {
			foreach($field as &$v) {
				if($value === $v) {
					return ' selected="selected"';
				}
			}
			return '';
		} elseif(($field === '' OR $value === '') OR ($field !== $value)) {
			return '';
		}
		return ' selected="selected"';
	}
	public function set_radio($field = '', $value = '', $default = FALSE){
		if(!isset($this->_field_data[$field], $this->_field_data[$field]['postdata'])) {
			return ($default === TRUE && count($this->_field_data) === 0) ? ' checked="checked"' : '';
		}
		$field = $this->_field_data[$field]['postdata'];
		$value = (string)$value;
		if(is_array($field)) {
			foreach($field as &$v) {
				if($value === $v) {
					return ' checked="checked"';
				}
			}
			return '';
		} elseif(($field === '' OR $value === '') OR ($field !== $value)) {
			return '';
		}
		return ' checked="checked"';
	}
	public function set_checkbox($field = '', $value = '', $default = FALSE){
		return $this->set_radio($field, $value, $default);
	}
	public function required($str){
		return is_array($str) ? (empty($str) === FALSE) : (trim($str) !== '');
	}
	public function regex_match($str, $regex){
		return (bool)preg_match($regex, $str);
	}
	public function matches($str, $field){
		return isset($this->_field_data[$field], $this->_field_data[$field]['postdata']) ? ($str === $this->_field_data[$field]['postdata']) : FALSE;
	}
	public function differs($str, $field){
		return !(isset($this->_field_data[$field]) && $this->_field_data[$field]['postdata'] === $str);
	}
	public function is_unique($str, $field){
		sscanf($field, '%[^.].%[^.]', $table, $field);
		return isset($this->CI->db) ? ($this->CI->db->limit(1)->get_where($table, array($field => $str))->num_rows() === 0) : FALSE;
	}
	public function min_length($str, $val){
		if(!is_numeric($val)) {
			return FALSE;
		}
		return ($val <= mb_strlen($str));
	}
	public function max_length($str, $val){
		if(!is_numeric($val)) {
			return FALSE;
		}
		return ($val >= mb_strlen($str));
	}
	public function exact_length($str, $val){
		if(!is_numeric($val)) {
			return FALSE;
		}
		return (mb_strlen($str) === (int)$val);
	}
	public function valid_url($str){
		if(empty($str)) {
			return FALSE;
		} elseif(preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches)) {
			if(empty($matches[2])) {
				return FALSE;
			} elseif(!in_array(strtolower($matches[1]), array('http', 'https'), TRUE)) {
				return FALSE;
			}
			$str = $matches[2];
		}
		if(preg_match('/^\[([^\]]+)\]/', $str, $matches) && !is_php('7') && filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE) {
			$str = 'ipv6.host' . substr($str, strlen($matches[1]) + 2);
		}
		return (filter_var('http://' . $str, FILTER_VALIDATE_URL) !== FALSE);
	}
	public function valid_email($str){
		if(function_exists('idn_to_ascii') && sscanf($str, '%[^@]@%s', $name, $domain) === 2) {
			$str = $name . '@' . idn_to_ascii($domain);
		}
		return (bool)filter_var($str, FILTER_VALIDATE_EMAIL);
	}
	public function valid_emails($str){
		if(strpos($str, ',') === FALSE) {
			return $this->valid_email(trim($str));
		}
		foreach(explode(',', $str) as $email) {
			if(trim($email) !== '' && $this->valid_email(trim($email)) === FALSE) {
				return FALSE;
			}
		}
		return TRUE;
	}
	public function valid_ip($ip, $which = ''){
		return $this->CI->input->valid_ip($ip, $which);
	}
	public function alpha($str){
		return ctype_alpha($str);
	}
	public function alpha_numeric($str){
		return ctype_alnum((string)$str);
	}
	public function alpha_numeric_spaces($str){
		return (bool)preg_match('/^[A-Z0-9 ]+$/i', $str);
	}
	public function alpha_dash($str){
		return (bool)preg_match('/^[a-z0-9_-]+$/i', $str);
	}
	public function numeric($str){
		return (bool)preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
	}
	public function integer($str){
		return (bool)preg_match('/^[\-+]?[0-9]+$/', $str);
	}
	public function decimal($str){
		return (bool)preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
	}
	public function greater_than($str, $min){
		return is_numeric($str) ? ($str > $min) : FALSE;
	}
	public function greater_than_equal_to($str, $min){
		return is_numeric($str) ? ($str >= $min) : FALSE;
	}
	public function less_than($str, $max){
		return is_numeric($str) ? ($str < $max) : FALSE;
	}
	public function less_than_equal_to($str, $max){
		return is_numeric($str) ? ($str <= $max) : FALSE;
	}
	public function in_list($value, $list){
		return in_array($value, explode(',', $list), TRUE);
	}
	public function is_natural($str){
		return ctype_digit((string)$str);
	}
	public function is_natural_no_zero($str){
		return ($str != 0 && ctype_digit((string)$str));
	}
	public function valid_base64($str){
		return (base64_encode(base64_decode($str)) === $str);
	}
	public function prep_for_form($data){
		if($this->_safe_form_data === FALSE OR empty($data)) {
			return $data;
		}
		if(is_array($data)) {
			foreach($data as $key => $val) {
				$data[$key] = $this->prep_for_form($val);
			}
			return $data;
		}
		return str_replace(array("'", '"', '<', '>'), array('&#39;', '&quot;', '&lt;', '&gt;'), stripslashes($data));
	}
	public function prep_url($str = ''){
		if($str === 'http://' OR $str === '') {
			return '';
		}
		if(strpos($str, 'http://') !== 0 && strpos($str, 'https://') !== 0) {
			return 'http://' . $str;
		}
		return $str;
	}
	public function strip_image_tags($str){
		return $this->CI->security->strip_image_tags($str);
	}
	public function encode_php_tags($str){
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}
	public function reset_validation(){
		$this->_field_data = array();
		$this->_error_array = array();
		$this->_error_messages = array();
		$this->error_string = '';
		return $this;
	}
}
