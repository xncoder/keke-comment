<?php
class Keke_witkey_task_taobao_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_taobao_id;
	public $_click_count;
	public $_pay_amount;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_task_taobao_class(){
	public function getTaobao_id(){
	public function getClick_count(){
		return $this->_click_count ;
	}
	public function getPay_amount(){
		return $this->_pay_amount;
	}
	public function setTaobao_id($value){
	public function setPay_amount($value){
		return $this->_pay_amount=$value;
	}
	public function setClick_count($value){
		return $this->_click_count=$value ;
	}
	 
	public  function __set($property_name, $value) {
		$this->$property_name = $value;
	}
	public function __get($property_name) {
		if (isset ( $this->$property_name )) {
			return $this->$property_name;
		} else {
			return null;
		}
	}
	 
	/**
		if(!is_null($this->_pay_amount)){
			$data['pay_amount']=$this->_pay_amount;
		}
		if(!is_null($this->_click_count)){
			$data['click_count']=$this->_click_count;
		}
	/**
		if(!is_null($this->_pay_amount)){
			$data['pay_amount']=$this->_pay_amount;
		}
		if(!is_null($this->_click_count)){
			$data['click_count']=$this->_click_count;
		}
	/**
	/**
	/**

	 
	 
}
?>