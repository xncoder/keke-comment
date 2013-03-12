<?php
class Keke_witkey_skill_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_skill_id;	public $_indus_id;	public $_skill_name;	public $_listorder;	public $_on_time;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_skill_class(){		$this->_db = new db_factory ( );		$this->_dbop = $this->_db->create(DBTYPE);		$this->_tablename = TABLEPRE."witkey_skill";	}	 
	public function getSkill_id(){		return $this->_skill_id ;	}	public function getIndus_id(){		return $this->_indus_id ;	}	public function getSkill_name(){		return $this->_skill_name ;	}	public function getListorder(){		return $this->_listorder ;	}	public function getOn_time(){		return $this->_on_time ;	}	public function getWhere(){		return $this->_where ;	}	public function getCache_config() {		return $this->_cache_config;	}
	public function setSkill_id($value){		$this->_skill_id = $value;	}	public function setIndus_id($value){		$this->_indus_id = $value;	}	public function setSkill_name($value){		$this->_skill_name = $value;	}	public function setListorder($value){		$this->_listorder = $value;	}	public function setOn_time($value){		$this->_on_time = $value;	}	public function setWhere($value){		$this->_where = $value;	}	public function setCache_config($_cache_config) {		$this->_cache_config = $_cache_config;	}
	 
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
	 
	/**	 * insert into  keke_witkey_skill  ,or add new record	 * @return int last_insert_id	 */	function create_keke_witkey_skill(){		$data =  array();		if(!is_null($this->_skill_id)){			$data['skill_id']=$this->_skill_id;		}		if(!is_null($this->_indus_id)){			$data['indus_id']=$this->_indus_id;		}		if(!is_null($this->_skill_name)){			$data['skill_name']=$this->_skill_name;		}		if(!is_null($this->_listorder)){			$data['listorder']=$this->_listorder;		}		if(!is_null($this->_on_time)){			$data['on_time']=$this->_on_time;		}		return $this->_skill_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);	}
	/**	 * edit table keke_witkey_skill	 * @return int affected_rows	 */	function edit_keke_witkey_skill(){		$data =  array();		if(!is_null($this->_skill_id)){			$data['skill_id']=$this->_skill_id;		}		if(!is_null($this->_indus_id)){			$data['indus_id']=$this->_indus_id;		}		if(!is_null($this->_skill_name)){			$data['skill_name']=$this->_skill_name;		}		if(!is_null($this->_listorder)){			$data['listorder']=$this->_listorder;		}		if(!is_null($this->_on_time)){			$data['on_time']=$this->_on_time;		}		if($this->_where){			return $this->_db->updatetable($this->_tablename,$data,$this->getWhere());		}		else{			$where = array('skill_id' => $this->_skill_id);			return $this->_db->updatetable($this->_tablename,$data,$where);		}	}
	/**	 * query table: keke_witkey_skill,if isset where return where record,else return all record	 * @return array	 */	function query_keke_witkey_skill($is_cache=0, $cache_time=0){		if($this->_where){			$sql = "select * from $this->_tablename where ".$this->_where;		}		else{			$sql = "select * from $this->_tablename";		}		if ($is_cache) {			$this->_cache_config ['is_cache'] = $is_cache;		}		if ($cache_time) {			$this->_cache_config ['time'] = $cache_time;		}		if ($this->_cache_config ['is_cache']) {			if (CACHE_TYPE) {				$keke_cache = new keke_cache_class ( CACHE_TYPE );				$id = $this->_tablename . ($this->_where?"_" .substr(md5 ( $this->_where ),0,6):'');				$data = $keke_cache->get ( $id );				if ($data) {					return $data;				} else {					$res = $this->_dbop->query ( $sql );					$keke_cache->set ( $id, $res,$this->_cache_config['time'] );					$this->_where = "";					return $res;				}			}		}else{			$this->_where = "";			return  $this->_dbop->query ( $sql );		}	}
	/**	 * query count keke_witkey_skill records,if iset where query by where	 * @return int count records	 */	function count_keke_witkey_skill(){		if($this->_where){			$sql = "select count(*) as count from $this->_tablename where ".$this->_where;		}		else{			$sql = "select count(*) as count from $this->_tablename";		}		$this->_where = "";		return $this->_dbop->getCount($sql);	}
	/**	 * delete table keke_witkey_skill, if isset where delete by where	 * @return int deleted affected_rows	 */	function del_keke_witkey_skill(){		if($this->_where){			$sql = "delete from $this->_tablename where ".$this->_where;		}		else{			$sql = "delete from $this->_tablename where skill_id = $this->_skill_id ";		}		$this->_where = "";		return $this->_dbop->execute($sql);	}

	 
	 
}
?>