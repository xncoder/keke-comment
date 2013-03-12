<?php
class Keke_witkey_sign_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_sign_id;	public $_task_id;	public $_uid;	public $_username;	public $_bid_status;	public $_bid_time;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_sign_class(){		$this->_db = new db_factory ( );		$this->_dbop = $this->_db->create(DBTYPE);		$this->_tablename = TABLEPRE."witkey_sign";	}	 
	public function getSign_id(){		return $this->_sign_id ;	}	public function getTask_id(){		return $this->_task_id ;	}	public function getUid(){		return $this->_uid ;	}	public function getUsername(){		return $this->_username ;	}	public function getBid_status(){		return $this->_bid_status ;	}	public function getBid_time(){		return $this->_bid_time ;	}	public function getWhere(){		return $this->_where ;	}	public function getCache_config() {		return $this->_cache_config;	}
	public function setSign_id($value){		$this->_sign_id = $value;	}	public function setTask_id($value){		$this->_task_id = $value;	}	public function setUid($value){		$this->_uid = $value;	}	public function setUsername($value){		$this->_username = $value;	}	public function setBid_status($value){		$this->_bid_status = $value;	}	public function setBid_time($value){		$this->_bid_time = $value;	}	public function setWhere($value){		$this->_where = $value;	}	public function setCache_config($_cache_config) {		$this->_cache_config = $_cache_config;	}
	 
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
	 
	/**	 * insert into  keke_witkey_sign  ,or add new record	 * @return int last_insert_id	 */	function create_keke_witkey_sign(){		$data =  array();		if(!is_null($this->_sign_id)){			$data['sign_id']=$this->_sign_id;		}		if(!is_null($this->_task_id)){			$data['task_id']=$this->_task_id;		}		if(!is_null($this->_uid)){			$data['uid']=$this->_uid;		}		if(!is_null($this->_username)){			$data['username']=$this->_username;		}		if(!is_null($this->_bid_status)){			$data['bid_status']=$this->_bid_status;		}		if(!is_null($this->_bid_time)){			$data['bid_time']=$this->_bid_time;		}		return $this->_sign_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);	}
	/**	 * edit table keke_witkey_sign	 * @return int affected_rows	 */	function edit_keke_witkey_sign(){		$data =  array();		if(!is_null($this->_sign_id)){			$data['sign_id']=$this->_sign_id;		}		if(!is_null($this->_task_id)){			$data['task_id']=$this->_task_id;		}		if(!is_null($this->_uid)){			$data['uid']=$this->_uid;		}		if(!is_null($this->_username)){			$data['username']=$this->_username;		}		if(!is_null($this->_bid_status)){			$data['bid_status']=$this->_bid_status;		}		if(!is_null($this->_bid_time)){			$data['bid_time']=$this->_bid_time;		}		if($this->_where){			return $this->_db->updatetable($this->_tablename,$data,$this->getWhere());		}		else{			$where = array('sign_id' => $this->_sign_id);			return $this->_db->updatetable($this->_tablename,$data,$where);		}	}
	/**	 * query table: keke_witkey_sign,if isset where return where record,else return all record	 * @return array	 */	function query_keke_witkey_sign($is_cache=0, $cache_time=0){		if($this->_where){			$sql = "select * from $this->_tablename where ".$this->_where;		}		else{			$sql = "select * from $this->_tablename";		}		if ($is_cache) {			$this->_cache_config ['is_cache'] = $is_cache;		}		if ($cache_time) {			$this->_cache_config ['time'] = $cache_time;		}		if ($this->_cache_config ['is_cache']) {			if (CACHE_TYPE) {				$keke_cache = new keke_cache_class ( CACHE_TYPE );				$id = $this->_tablename . ($this->_where?"_" .substr(md5 ( $this->_where ),0,6):'');				$data = $keke_cache->get ( $id );				if ($data) {					return $data;				} else {					$res = $this->_dbop->query ( $sql );					$keke_cache->set ( $id, $res,$this->_cache_config['time'] );					$this->_where = "";					return $res;				}			}		}else{			$this->_where = "";			return  $this->_dbop->query ( $sql );		}	}
	/**	 * query count keke_witkey_sign records,if iset where query by where	 * @return int count records	 */	function count_keke_witkey_sign(){		if($this->_where){			$sql = "select count(*) as count from $this->_tablename where ".$this->_where;		}		else{			$sql = "select count(*) as count from $this->_tablename";		}		$this->_where = "";		return $this->_dbop->getCount($sql);	}
	/**	 * delete table keke_witkey_sign, if isset where delete by where	 * @return int deleted affected_rows	 */	function del_keke_witkey_sign(){		if($this->_where){			$sql = "delete from $this->_tablename where ".$this->_where;		}		else{			$sql = "delete from $this->_tablename where sign_id = $this->_sign_id ";		}		$this->_where = "";		return $this->_dbop->execute($sql);	}

	 
	 
}
?>