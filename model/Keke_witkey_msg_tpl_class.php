<?php
class Keke_witkey_msg_tpl_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_tpl_id;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_msg_tpl_class(){
	public function getTpl_id(){
	public function setTpl_id($value){
	 
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
    function create_keke_witkey_msg_tpl(){
        $data =  array();
        if(!is_null($this->_tpl_id)){
            $data['tpl_id']=$this->_tpl_id;
        }
        if(!is_null($this->_tpl_code)){
            $data['tpl_code']=$this->_tpl_code;
        }
        if(!is_null($this->_content)){
            $data['content']=$this->_content;
        }
        if(!is_null($this->_send_type)){
            $data['send_type']=$this->_send_type;
        }
        if(!is_null($this->_listorder)){
            $data['listorder']=$this->_listorder;
        }
        return $this->_tpl_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);
    }

	/**
	/**
	/**
	/**

	 
	 
}
?>