<?php
class Keke_witkey_shop_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_shop_id;
	public $_on_sale;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_shop_class(){
	public function getShop_id(){
	public function getOn_sale(){
		return $this->_on_sale ;
	}
	public function setShop_id($value){
	public function setOn_sale($value){
		$this->_on_sale = $value;
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
		if(!is_null($this->_on_sale)){
			$data['on_sale']=$this->_on_sale;
		}
	/**
		if(!is_null($this->_on_sale)){
			$data['on_sale']=$this->_on_sale;
		}
	/**
	/**
	/**

	 
	 
}
?>