<?php
class Keke_witkey_article_category_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_art_cat_id;	public $_art_cat_pid;	public $_cat_name;	public $_listorder;	public $_is_show;	public $_on_time;	public $_cat_type;	public $_art_index;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_article_category_class(){		$this->_db = new db_factory ( );		$this->_dbop = $this->_db->create(DBTYPE);		$this->_tablename = TABLEPRE."witkey_article_category";	}	 
	public function getArt_cat_id(){		return $this->_art_cat_id ;	}	public function getArt_cat_pid(){		return $this->_art_cat_pid ;	}	public function getCat_name(){		return $this->_cat_name ;	}	public function getListorder(){		return $this->_listorder ;	}	public function getIs_show(){		return $this->_is_show ;	}	public function getOn_time(){		return $this->_on_time ;	}	public function getCat_type(){		return $this->_cat_type ;	}	public function getArt_index(){		return $this->_art_index ;	}	public function getWhere(){		return $this->_where ;	}	public function getCache_config() {		return $this->_cache_config;	}
	public function setArt_cat_id($value){		$this->_art_cat_id = $value;	}	public function setArt_cat_pid($value){		$this->_art_cat_pid = $value;	}	public function setCat_name($value){		$this->_cat_name = $value;	}	public function setListorder($value){		$this->_listorder = $value;	}	public function setIs_show($value){		$this->_is_show = $value;	}	public function setOn_time($value){		$this->_on_time = $value;	}	public function setCat_type($value){		$this->_cat_type = $value;	}	public function setArt_index($value){		$this->_art_index = $value;	}	public function setWhere($value){		$this->_where = $value;	}	public function setCache_config($_cache_config) {		$this->_cache_config = $_cache_config;	}
	 
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
	 
	/**	 * insert into  keke_witkey_article_category  ,or add new record	 * @return int last_insert_id	 */	function create_keke_witkey_article_category(){		$data =  array();		if(!is_null($this->_art_cat_id)){			$data['art_cat_id']=$this->_art_cat_id;		}		if(!is_null($this->_art_cat_pid)){			$data['art_cat_pid']=$this->_art_cat_pid;		}		if(!is_null($this->_cat_name)){			$data['cat_name']=$this->_cat_name;		}		if(!is_null($this->_listorder)){			$data['listorder']=$this->_listorder;		}		if(!is_null($this->_is_show)){			$data['is_show']=$this->_is_show;		}		if(!is_null($this->_on_time)){			$data['on_time']=$this->_on_time;		}		if(!is_null($this->_cat_type)){			$data['cat_type']=$this->_cat_type;		}		if(!is_null($this->_art_index)){			$data['art_index']=$this->_art_index;		}		return $this->_art_cat_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);	}
	/**	 * edit table keke_witkey_article_category	 * @return int affected_rows	 */	function edit_keke_witkey_article_category(){		$data =  array();		if(!is_null($this->_art_cat_id)){			$data['art_cat_id']=$this->_art_cat_id;		}		if(!is_null($this->_art_cat_pid)){			$data['art_cat_pid']=$this->_art_cat_pid;		}		if(!is_null($this->_cat_name)){			$data['cat_name']=$this->_cat_name;		}		if(!is_null($this->_listorder)){			$data['listorder']=$this->_listorder;		}		if(!is_null($this->_is_show)){			$data['is_show']=$this->_is_show;		}		if(!is_null($this->_on_time)){			$data['on_time']=$this->_on_time;		}		if(!is_null($this->_cat_type)){			$data['cat_type']=$this->_cat_type;		}		if(!is_null($this->_art_index)){			$data['art_index']=$this->_art_index;		}		if($this->_where){			return $this->_db->updatetable($this->_tablename,$data,$this->getWhere());		}		else{			$where = array('art_cat_id' => $this->_art_cat_id);			return $this->_db->updatetable($this->_tablename,$data,$where);		}	}
	/**	 * query table: keke_witkey_article_category,if isset where return where record,else return all record	 * @return array	 */	function query_keke_witkey_article_category($is_cache=0, $cache_time=0){		if($this->_where){			$sql = "select * from $this->_tablename where ".$this->_where;		}		else{			$sql = "select * from $this->_tablename";		}		if ($is_cache) {			$this->_cache_config ['is_cache'] = $is_cache;		}		if ($cache_time) {			$this->_cache_config ['time'] = $cache_time;		}		if ($this->_cache_config ['is_cache']) {			if (CACHE_TYPE) {				$keke_cache = new keke_cache_class ( CACHE_TYPE );				$id = $this->_tablename . ($this->_where?"_" .substr(md5 ( $this->_where ),0,6):'');				$data = $keke_cache->get ( $id );				if ($data) {					return $data;				} else {					$res = $this->_dbop->query ( $sql );					$keke_cache->set ( $id, $res,$this->_cache_config['time'] );					$this->_where = "";					return $res;				}			}		}else{			$this->_where = "";			return  $this->_dbop->query ( $sql );		}	}
	/**	 * query count keke_witkey_article_category records,if iset where query by where	 * @return int count records	 */	function count_keke_witkey_article_category(){		if($this->_where){			$sql = "select count(*) as count from $this->_tablename where ".$this->_where;		}		else{			$sql = "select count(*) as count from $this->_tablename";		}		$this->_where = "";		return $this->_dbop->getCount($sql);	}
	/**	 * delete table keke_witkey_article_category, if isset where delete by where	 * @return int deleted affected_rows	 */	function del_keke_witkey_article_category(){		if($this->_where){			$sql = "delete from $this->_tablename where ".$this->_where;		}		else{			$sql = "delete from $this->_tablename where art_cat_id = $this->_art_cat_id ";		}		$this->_where = "";		return $this->_dbop->execute($sql);	}

	 
	 
}
?>