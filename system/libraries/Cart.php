<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Cart{
	public $product_id_rules = '\.a-z0-9_-';
	public $product_name_rules = '\w \-\.\:';
	public $product_name_safe = TRUE;
	protected $CI;
	protected $_cart_contents = array();
	public function __construct($params = array()){
		$this->CI =& get_instance();
		$config = is_array($params) ? $params : array();
		$this->CI->load->driver('session', $config);
		$this->_cart_contents = $this->CI->session->userdata('cart_contents');
		if($this->_cart_contents === NULL) {
			$this->_cart_contents = array('cart_total' => 0, 'total_items' => 0);
		}
		log_message('info', 'Cart Class Initialized');
	}
	public function insert($items = array()){
		if(!is_array($items) OR count($items) === 0) {
			log_message('error', 'The insert method must be passed an array containing data.');
			return FALSE;
		}
		$save_cart = FALSE;
		if(isset($items['id'])) {
			if(($rowid = $this->_insert($items))) {
				$save_cart = TRUE;
			}
		} else {
			foreach($items as $val) {
				if(is_array($val) && isset($val['id'])) {
					if($this->_insert($val)) {
						$save_cart = TRUE;
					}
				}
			}
		}
		if($save_cart === TRUE) {
			$this->_save_cart();
			return isset($rowid) ? $rowid : TRUE;
		}
		return FALSE;
	}
	protected function _insert($items = array()){
		if(!is_array($items) OR count($items) === 0) {
			log_message('error', 'The insert method must be passed an array containing data.');
			return FALSE;
		}
		if(!isset($items['id'], $items['qty'], $items['price'], $items['name'])) {
			log_message('error', 'The cart array must contain a product ID, quantity, price, and name.');
			return FALSE;
		}
		$items['qty'] = (float)$items['qty'];
		if($items['qty'] == 0) {
			return FALSE;
		}
		if(!preg_match('/^[' . $this->product_id_rules . ']+$/i', $items['id'])) {
			log_message('error', 'Invalid product ID.  The product ID can only contain alpha-numeric characters, dashes, and underscores');
			return FALSE;
		}
		if($this->product_name_safe && !preg_match('/^[' . $this->product_name_rules . ']+$/i' . (UTF8_ENABLED ? 'u' : ''), $items['name'])) {
			log_message('error', 'An invalid name was submitted as the product name: ' . $items['name'] . ' The name can only contain alpha-numeric characters, dashes, underscores, colons, and spaces');
			return FALSE;
		}
		$items['price'] = (float)$items['price'];
		if(isset($items['options']) && count($items['options']) > 0) {
			$rowid = md5($items['id'] . serialize($items['options']));
		} else {
			$rowid = md5($items['id']);
		}
		$old_quantity = isset($this->_cart_contents[$rowid]['qty']) ? (int)$this->_cart_contents[$rowid]['qty'] : 0;
		$items['rowid'] = $rowid;
		$items['qty'] += $old_quantity;
		$this->_cart_contents[$rowid] = $items;
		return $rowid;
	}
	public function update($items = array()){
		if(!is_array($items) OR count($items) === 0) {
			return FALSE;
		}
		$save_cart = FALSE;
		if(isset($items['rowid'])) {
			if($this->_update($items) === TRUE) {
				$save_cart = TRUE;
			}
		} else {
			foreach($items as $val) {
				if(is_array($val) && isset($val['rowid'])) {
					if($this->_update($val) === TRUE) {
						$save_cart = TRUE;
					}
				}
			}
		}
		if($save_cart === TRUE) {
			$this->_save_cart();
			return TRUE;
		}
		return FALSE;
	}
	protected function _update($items = array()){
		if(!isset($items['rowid'], $this->_cart_contents[$items['rowid']])) {
			return FALSE;
		}
		if(isset($items['qty'])) {
			$items['qty'] = (float)$items['qty'];
			if($items['qty'] == 0) {
				unset($this->_cart_contents[$items['rowid']]);
				return TRUE;
			}
		}
		$keys = array_intersect(array_keys($this->_cart_contents[$items['rowid']]), array_keys($items));
		if(isset($items['price'])) {
			$items['price'] = (float)$items['price'];
		}
		foreach(array_diff($keys, array('id', 'name')) as $key) {
			$this->_cart_contents[$items['rowid']][$key] = $items[$key];
		}
		return TRUE;
	}
	protected function _save_cart(){
		$this->_cart_contents['total_items'] = $this->_cart_contents['cart_total'] = 0;
		foreach($this->_cart_contents as $key => $val) {
			if(!is_array($val) OR !isset($val['price'], $val['qty'])) {
				continue;
			}
			$this->_cart_contents['cart_total'] += ($val['price'] * $val['qty']);
			$this->_cart_contents['total_items'] += $val['qty'];
			$this->_cart_contents[$key]['subtotal'] = ($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']);
		}
		if(count($this->_cart_contents) <= 2) {
			$this->CI->session->unset_userdata('cart_contents');
			return FALSE;
		}
		$this->CI->session->set_userdata(array('cart_contents' => $this->_cart_contents));
		return TRUE;
	}
	public function total(){
		return $this->_cart_contents['cart_total'];
	}
	public function remove($rowid){
		unset($this->_cart_contents[$rowid]);
		$this->_save_cart();
		return TRUE;
	}
	public function total_items(){
		return $this->_cart_contents['total_items'];
	}
	public function contents($newest_first = FALSE){
		$cart = ($newest_first) ? array_reverse($this->_cart_contents) : $this->_cart_contents;
		unset($cart['total_items']);
		unset($cart['cart_total']);
		return $cart;
	}
	public function get_item($row_id){
		return (in_array($row_id, array('total_items', 'cart_total'), TRUE) OR !isset($this->_cart_contents[$row_id])) ? FALSE : $this->_cart_contents[$row_id];
	}
	public function has_options($row_id = ''){
		return (isset($this->_cart_contents[$row_id]['options']) && count($this->_cart_contents[$row_id]['options']) !== 0);
	}
	public function product_options($row_id = ''){
		return isset($this->_cart_contents[$row_id]['options']) ? $this->_cart_contents[$row_id]['options'] : array();
	}
	public function format_number($n = ''){
		return ($n === '') ? '' : number_format((float)$n, 2, '.', ',');
	}
	public function destroy(){
		$this->_cart_contents = array('cart_total' => 0, 'total_items' => 0);
		$this->CI->session->unset_userdata('cart_contents');
	}
}
