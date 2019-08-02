<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Pagination{
	protected $base_url = '';
	protected $prefix = '';
	protected $suffix = '';
	protected $total_rows = 0;
	protected $num_links = 2;
	public $per_page = 10;
	public $cur_page = 0;
	protected $use_page_numbers = FALSE;
	protected $first_link = '&lsaquo; First';
	protected $next_link = '&gt;';
	protected $prev_link = '&lt;';
	protected $last_link = 'Last &rsaquo;';
	protected $uri_segment = 0;
	protected $full_tag_open = '';
	protected $full_tag_close = '';
	protected $first_tag_open = '';
	protected $first_tag_close = '';
	protected $last_tag_open = '';
	protected $last_tag_close = '';
	protected $first_url = '';
	protected $cur_tag_open = '<strong>';
	protected $cur_tag_close = '</strong>';
	protected $next_tag_open = '';
	protected $next_tag_close = '';
	protected $prev_tag_open = '';
	protected $prev_tag_close = '';
	protected $num_tag_open = '';
	protected $num_tag_close = '';
	protected $page_query_string = FALSE;
	protected $query_string_segment = 'per_page';
	protected $display_pages = TRUE;
	protected $_attributes = '';
	protected $_link_types = array();
	protected $reuse_query_string = FALSE;
	protected $use_global_url_suffix = FALSE;
	protected $data_page_attr = 'data-ci-pagination-page';
	protected $CI;
	public function __construct($params = array()){
		$this->CI =& get_instance();
		$this->CI->load->language('pagination');
		foreach(array('first_link', 'next_link', 'prev_link', 'last_link') as $key) {
			if(($val = $this->CI->lang->line('pagination_' . $key)) !== FALSE) {
				$this->$key = $val;
			}
		}
		$this->initialize($params);
		log_message('info', 'Pagination Class Initialized');
	}
	public function initialize(array $params = array()){
		isset($params['attributes']) OR $params['attributes'] = array();
		if(is_array($params['attributes'])) {
			$this->_parse_attributes($params['attributes']);
			unset($params['attributes']);
		}
		if(isset($params['anchor_class'])) {
			empty($params['anchor_class']) OR $attributes['class'] = $params['anchor_class'];
			unset($params['anchor_class']);
		}
		foreach($params as $key => $val) {
			if(property_exists($this, $key)) {
				$this->$key = $val;
			}
		}
		if($this->CI->config->item('enable_query_strings') === TRUE) {
			$this->page_query_string = TRUE;
		}
		if($this->use_global_url_suffix === TRUE) {
			$this->suffix = $this->CI->config->item('url_suffix');
		}
		return $this;
	}
	public function create_links(){
		if($this->total_rows == 0 OR $this->per_page == 0) {
			return '';
		}
		$num_pages = (int)ceil($this->total_rows / $this->per_page);
		if($num_pages === 1) {
			return '';
		}
		$this->num_links = (int)$this->num_links;
		if($this->num_links < 0) {
			show_error('Your number of links must be a non-negative number.');
		}
		if($this->reuse_query_string === TRUE) {
			$get = $this->CI->input->get();
			unset($get['c'], $get['m'], $get[$this->query_string_segment]);
		} else {
			$get = array();
		}
		$base_url = trim($this->base_url);
		$first_url = $this->first_url;
		$query_string = '';
		$query_string_sep = (strpos($base_url, '?') === FALSE) ? '?' : '&amp;';
		if($this->page_query_string === TRUE) {
			if($first_url === '') {
				$first_url = $base_url;
				if(!empty($get)) {
					$first_url .= $query_string_sep . http_build_query($get);
				}
			}
			$base_url .= $query_string_sep . http_build_query(array_merge($get, array($this->query_string_segment => '')));
		} else {
			if(!empty($get)) {
				$query_string = $query_string_sep . http_build_query($get);
				$this->suffix .= $query_string;
			}
			if($this->reuse_query_string === TRUE && ($base_query_pos = strpos($base_url, '?')) !== FALSE) {
				$base_url = substr($base_url, 0, $base_query_pos);
			}
			if($first_url === '') {
				$first_url = $base_url . $query_string;
			}
			$base_url = rtrim($base_url, '/') . '/';
		}
		$base_page = ($this->use_page_numbers) ? 1 : 0;
		if($this->page_query_string === TRUE) {
			$this->cur_page = $this->CI->input->get($this->query_string_segment);
		} elseif(empty($this->cur_page)) {
			if($this->uri_segment === 0) {
				$this->uri_segment = count($this->CI->uri->segment_array());
			}
			$this->cur_page = $this->CI->uri->segment($this->uri_segment);
			if($this->prefix !== '' OR $this->suffix !== '') {
				$this->cur_page = str_replace(array($this->prefix, $this->suffix), '', $this->cur_page);
			}
		} else {
			$this->cur_page = (string)$this->cur_page;
		}
		if(!ctype_digit($this->cur_page) OR ($this->use_page_numbers && (int)$this->cur_page === 0)) {
			$this->cur_page = $base_page;
		} else {
			$this->cur_page = (int)$this->cur_page;
		}
		if($this->use_page_numbers) {
			if($this->cur_page > $num_pages) {
				$this->cur_page = $num_pages;
			}
		} elseif($this->cur_page > $this->total_rows) {
			$this->cur_page = ($num_pages - 1) * $this->per_page;
		}
		$uri_page_number = $this->cur_page;
		if(!$this->use_page_numbers) {
			$this->cur_page = (int)floor(($this->cur_page / $this->per_page) + 1);
		}
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;
		$output = '';
		if($this->first_link !== FALSE && $this->cur_page > ($this->num_links + 1 + !$this->num_links)) {
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, 1);
			$output .= $this->first_tag_open . '<a href="' . $first_url . '"' . $attributes . $this->_attr_rel('start') . '>' . $this->first_link . '</a>' . $this->first_tag_close;
		}
		if($this->prev_link !== FALSE && $this->cur_page !== 1) {
			$i = ($this->use_page_numbers) ? $uri_page_number - 1 : $uri_page_number - $this->per_page;
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, ($this->cur_page - 1));
			if($i === $base_page) {
				$output .= $this->prev_tag_open . '<a href="' . $first_url . '"' . $attributes . $this->_attr_rel('prev') . '>' . $this->prev_link . '</a>' . $this->prev_tag_close;
			} else {
				$append = $this->prefix . $i . $this->suffix;
				$output .= $this->prev_tag_open . '<a href="' . $base_url . $append . '"' . $attributes . $this->_attr_rel('prev') . '>' . $this->prev_link . '</a>' . $this->prev_tag_close;
			}
		}
		if($this->display_pages !== FALSE) {
			for($loop = $start - 1; $loop <= $end; $loop++) {
				$i = ($this->use_page_numbers) ? $loop : ($loop * $this->per_page) - $this->per_page;
				$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, $loop);
				if($i >= $base_page) {
					if($this->cur_page === $loop) {
						$output .= $this->cur_tag_open . $loop . $this->cur_tag_close;
					} elseif($i === $base_page) {
						$output .= $this->num_tag_open . '<a href="' . $first_url . '"' . $attributes . $this->_attr_rel('start') . '>' . $loop . '</a>' . $this->num_tag_close;
					} else {
						$append = $this->prefix . $i . $this->suffix;
						$output .= $this->num_tag_open . '<a href="' . $base_url . $append . '"' . $attributes . '>' . $loop . '</a>' . $this->num_tag_close;
					}
				}
			}
		}
		if($this->next_link !== FALSE && $this->cur_page < $num_pages) {
			$i = ($this->use_page_numbers) ? $this->cur_page + 1 : $this->cur_page * $this->per_page;
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, $this->cur_page + 1);
			$output .= $this->next_tag_open . '<a href="' . $base_url . $this->prefix . $i . $this->suffix . '"' . $attributes . $this->_attr_rel('next') . '>' . $this->next_link . '</a>' . $this->next_tag_close;
		}
		if($this->last_link !== FALSE && ($this->cur_page + $this->num_links + !$this->num_links) < $num_pages) {
			$i = ($this->use_page_numbers) ? $num_pages : ($num_pages * $this->per_page) - $this->per_page;
			$attributes = sprintf('%s %s="%d"', $this->_attributes, $this->data_page_attr, $num_pages);
			$output .= $this->last_tag_open . '<a href="' . $base_url . $this->prefix . $i . $this->suffix . '"' . $attributes . '>' . $this->last_link . '</a>' . $this->last_tag_close;
		}
		$output = preg_replace('#([^:"])//+#', '\\1/', $output);
		return $this->full_tag_open . $output . $this->full_tag_close;
	}
	protected function _parse_attributes($attributes){
		isset($attributes['rel']) OR $attributes['rel'] = TRUE;
		$this->_link_types = ($attributes['rel']) ? array('start' => 'start', 'prev' => 'prev', 'next' => 'next') : array();
		unset($attributes['rel']);
		$this->_attributes = '';
		foreach($attributes as $key => $value) {
			$this->_attributes .= ' ' . $key . '="' . $value . '"';
		}
	}
	protected function _attr_rel($type){
		if(isset($this->_link_types[$type])) {
			unset($this->_link_types[$type]);
			return ' rel="' . $type . '"';
		}
		return '';
	}
}
