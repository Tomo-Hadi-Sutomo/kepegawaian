<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Table{
	public $rows = array();
	public $heading = array();
	public $auto_heading = TRUE;
	public $caption = NULL;
	public $template = NULL;
	public $newline = "\n";
	public $empty_cells = '';
	public $function = NULL;
	public function __construct($config = array()){
		foreach($config as $key => $val) {
			$this->template[$key] = $val;
		}
		log_message('info', 'Table Class Initialized');
	}
	public function set_template($template){
		if(!is_array($template)) {
			return FALSE;
		}
		$this->template = $template;
		return TRUE;
	}
	public function set_heading($args = array()){
		$this->heading = $this->_prep_args(func_get_args());
		return $this;
	}
	public function make_columns($array = array(), $col_limit = 0){
		if(!is_array($array) OR count($array) === 0 OR !is_int($col_limit)) {
			return FALSE;
		}
		$this->auto_heading = FALSE;
		if($col_limit === 0) {
			return $array;
		}
		$new = array();
		do {
			$temp = array_splice($array, 0, $col_limit);
			if(count($temp) < $col_limit) {
				for($i = count($temp); $i < $col_limit; $i++) {
					$temp[] = '&nbsp;';
				}
			}
			$new[] = $temp;
		} while(count($array) > 0);
		return $new;
	}
	public function set_empty($value){
		$this->empty_cells = $value;
		return $this;
	}
	public function add_row($args = array()){
		$this->rows[] = $this->_prep_args(func_get_args());
		return $this;
	}
	public function set_caption($caption){
		$this->caption = $caption;
		return $this;
	}
	public function generate($table_data = NULL){
		if(!empty($table_data)) {
			if($table_data instanceof CI_DB_result) {
				$this->_set_from_db_result($table_data);
			} elseif(is_array($table_data)) {
				$this->_set_from_array($table_data);
			}
		}
		if(empty($this->heading) && empty($this->rows)) {
			return 'Undefined table data';
		}
		$this->_compile_template();
		if(isset($this->function) && !is_callable($this->function)) {
			$this->function = NULL;
		}
		$out = $this->template['table_open'] . $this->newline;
		if($this->caption) {
			$out .= '<caption>' . $this->caption . '</caption>' . $this->newline;
		}
		if(!empty($this->heading)) {
			$out .= $this->template['thead_open'] . $this->newline . $this->template['heading_row_start'] . $this->newline;
			foreach($this->heading as $heading) {
				$temp = $this->template['heading_cell_start'];
				foreach($heading as $key => $val) {
					if($key !== 'data') {
						$temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
					}
				}
				$out .= $temp . (isset($heading['data']) ? $heading['data'] : '') . $this->template['heading_cell_end'];
			}
			$out .= $this->template['heading_row_end'] . $this->newline . $this->template['thead_close'] . $this->newline;
		}
		if(!empty($this->rows)) {
			$out .= $this->template['tbody_open'] . $this->newline;
			$i = 1;
			foreach($this->rows as $row) {
				if(!is_array($row)) {
					break;
				}
				$name = fmod($i++, 2) ? '' : 'alt_';
				$out .= $this->template['row_' . $name . 'start'] . $this->newline;
				foreach($row as $cell) {
					$temp = $this->template['cell_' . $name . 'start'];
					foreach($cell as $key => $val) {
						if($key !== 'data') {
							$temp = str_replace('<td', '<td ' . $key . '="' . $val . '"', $temp);
						}
					}
					$cell = isset($cell['data']) ? $cell['data'] : '';
					$out .= $temp;
					if($cell === '' OR $cell === NULL) {
						$out .= $this->empty_cells;
					} elseif(isset($this->function)) {
						$out .= call_user_func($this->function, $cell);
					} else {
						$out .= $cell;
					}
					$out .= $this->template['cell_' . $name . 'end'];
				}
				$out .= $this->template['row_' . $name . 'end'] . $this->newline;
			}
			$out .= $this->template['tbody_close'] . $this->newline;
		}
		$out .= $this->template['table_close'];
		$this->clear();
		return $out;
	}
	public function clear(){
		$this->rows = array();
		$this->heading = array();
		$this->auto_heading = TRUE;
		return $this;
	}
	protected function _prep_args($args){
		if(isset($args[0]) && count($args) === 1 && is_array($args[0]) && !isset($args[0]['data'])) {
			$args = $args[0];
		}
		foreach($args as $key => $val) {
			is_array($val) OR $args[$key] = array('data' => $val);
		}
		return $args;
	}
	protected function _set_from_db_result($object){
		if($this->auto_heading === TRUE && empty($this->heading)) {
			$this->heading = $this->_prep_args($object->list_fields());
		}
		foreach($object->result_array() as $row) {
			$this->rows[] = $this->_prep_args($row);
		}
	}
	protected function _set_from_array($data){
		if($this->auto_heading === TRUE && empty($this->heading)) {
			$this->heading = $this->_prep_args(array_shift($data));
		}
		foreach($data as &$row) {
			$this->rows[] = $this->_prep_args($row);
		}
	}
	protected function _compile_template(){
		if($this->template === NULL) {
			$this->template = $this->_default_template();
			return;
		}
		$this->temp = $this->_default_template();
		foreach(array('table_open', 'thead_open', 'thead_close', 'heading_row_start', 'heading_row_end', 'heading_cell_start', 'heading_cell_end', 'tbody_open', 'tbody_close', 'row_start', 'row_end', 'cell_start', 'cell_end', 'row_alt_start', 'row_alt_end', 'cell_alt_start', 'cell_alt_end', 'table_close') as $val) {
			if(!isset($this->template[$val])) {
				$this->template[$val] = $this->temp[$val];
			}
		}
	}
	protected function _default_template(){
		return array('table_open' => '<table border="0" cellpadding="4" cellspacing="0">', 'thead_open' => '<thead>', 'thead_close' => '</thead>', 'heading_row_start' => '<tr>', 'heading_row_end' => '</tr>', 'heading_cell_start' => '<th>', 'heading_cell_end' => '</th>', 'tbody_open' => '<tbody>', 'tbody_close' => '</tbody>', 'row_start' => '<tr>', 'row_end' => '</tr>', 'cell_start' => '<td>', 'cell_end' => '</td>', 'row_alt_start' => '<tr>', 'row_alt_end' => '</tr>', 'cell_alt_start' => '<td>', 'cell_alt_end' => '</td>', 'table_close' => '</table>');
	}
}
