<?php
class Keke_witkey_member_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_uid;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_member_class(){
	public function getUid(){
	public function setUid($value){
	 
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
    function create_keke_witkey_member(){
        $data =  array();
        if(!is_null($this->_uid)){
            $data['uid']=$this->_uid;
        }
        if(!is_null($this->_username)){
            $data['username']=$this->_username;
        }
        if(!is_null($this->_password)){
            $data['password']=$this->_password;
        }
        if(!is_null($this->_rand_code)){
            $data['rand_code']=$this->_rand_code;
        }
        if(!is_null($this->_email)){
            $data['email']=$this->_email;
        }
        return $this->_uid = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);
    }

	/**
	/**
	/**
	/**

	 
	 
}
?>