<?php
class Keke_witkey_task_wbdj_class{
	public $_db;
	public $_tablename;
	public $_dbop;
	public $_wbdj_id;	public $_task_id;	public $_wb_platform;	public $_wb_content;	public $_wb_img;	public $_click_price;	public $_prom_url;	public $_pay_amount;	public $_click_count;
	public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
	public $_replace=0;
	public $_where;
	function  keke_witkey_task_wbdj_class(){		$this->_db = new db_factory ( );		$this->_dbop = $this->_db->create(DBTYPE);		$this->_tablename = TABLEPRE."witkey_task_wbdj";	}	 
	public function getWbdj_id(){		return $this->_wbdj_id ;	}	public function getTask_id(){		return $this->_task_id ;	}	public function getWb_platform(){		return $this->_wb_platform ;	}	public function getWb_content(){		return $this->_wb_content ;	}	public function getWb_img(){		return $this->_wb_img ;	}	public function getClick_price(){		return $this->_click_price ;	}	public function getProm_url(){		return $this->_prom_url ;	}	public function getPay_amount(){		return $this->_pay_amount ;	}	public function getClick_count(){		return $this->_click_count ;	}	public function getWhere(){		return $this->_where ;	}	public function getCache_config() {		return $this->_cache_config;	}
	public function setWbdj_id($value){		$this->_wbdj_id = $value;	}	public function setTask_id($value){		$this->_task_id = $value;	}	public function setWb_platform($value){		$this->_wb_platform = $value;	}	public function setWb_content($value){		$this->_wb_content = $value;	}	public function setWb_img($value){		$this->_wb_img = $value;	}	public function setClick_price($value){		$this->_click_price = $value;	}	public function setProm_url($value){		$this->_prom_url = $value;	}	public function setPay_amount($value){		$this->_pay_amount = $value;	}	public function setClick_count($value){		$this->_click_count = $value;	}	public function setWhere($value){		$this->_where = $value;	}	public function setCache_config($_cache_config) {		$this->_cache_config = $_cache_config;	}
	 
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
	 
	/**	 * insert into  keke_witkey_task_wbdj  ,or add new record	 * @return int last_insert_id	 */	function create_keke_witkey_task_wbdj(){		$data =  array();		if(!is_null($this->_wbdj_id)){			$data['wbdj_id']=$this->_wbdj_id;		}		if(!is_null($this->_task_id)){			$data['task_id']=$this->_task_id;		}		if(!is_null($this->_wb_platform)){			$data['wb_platform']=$this->_wb_platform;		}		if(!is_null($this->_wb_content)){			$data['wb_content']=$this->_wb_content;		}		if(!is_null($this->_wb_img)){			$data['wb_img']=$this->_wb_img;		}		if(!is_null($this->_click_price)){			$data['click_price']=$this->_click_price;		}		if(!is_null($this->_prom_url)){			$data['prom_url']=$this->_prom_url;		}		if(!is_null($this->_pay_amount)){			$data['pay_amount']=$this->_pay_amount;		}		if(!is_null($this->_click_count)){			$data['click_count']=$this->_click_count;		}		return $this->_wbdj_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);	}
	/**	 * edit table keke_witkey_task_wbdj	 * @return int affected_rows	 */	function edit_keke_witkey_task_wbdj(){		$data =  array();		if(!is_null($this->_wbdj_id)){			$data['wbdj_id']=$this->_wbdj_id;		}		if(!is_null($this->_task_id)){			$data['task_id']=$this->_task_id;		}		if(!is_null($this->_wb_platform)){			$data['wb_platform']=$this->_wb_platform;		}		if(!is_null($this->_wb_content)){			$data['wb_content']=$this->_wb_content;		}		if(!is_null($this->_wb_img)){			$data['wb_img']=$this->_wb_img;		}		if(!is_null($this->_click_price)){			$data['click_price']=$this->_click_price;		}		if(!is_null($this->_prom_url)){			$data['prom_url']=$this->_prom_url;		}		if(!is_null($this->_pay_amount)){			$data['pay_amount']=$this->_pay_amount;		}		if(!is_null($this->_click_count)){			$data['click_count']=$this->_click_count;		}		if($this->_where){			return $this->_db->updatetable($this->_tablename,$data,$this->getWhere());		}		else{			$where = array('wbdj_id' => $this->_wbdj_id);			return $this->_db->updatetable($this->_tablename,$data,$where);		}	}
	/**	 * query table: keke_witkey_task_wbdj,if isset where return where record,else return all record	 * @return array	 */	function query_keke_witkey_task_wbdj($is_cache=0, $cache_time=0){		if($this->_where){			$sql = "select * from $this->_tablename where ".$this->_where;		}		else{			$sql = "select * from $this->_tablename";		}		if ($is_cache) {			$this->_cache_config ['is_cache'] = $is_cache;		}		if ($cache_time) {			$this->_cache_config ['time'] = $cache_time;		}		if ($this->_cache_config ['is_cache']) {			if (CACHE_TYPE) {				$keke_cache = new keke_cache_class ( CACHE_TYPE );				$id = $this->_tablename . ($this->_where?"_" .substr(md5 ( $this->_where ),0,6):'');				$data = $keke_cache->get ( $id );				if ($data) {					return $data;				} else {					$res = $this->_dbop->query ( $sql );					$keke_cache->set ( $id, $res,$this->_cache_config['time'] );					$this->_where = "";					return $res;				}			}		}else{			$this->_where = "";			return  $this->_dbop->query ( $sql );		}	}
	/**	 * query count keke_witkey_task_wbdj records,if iset where query by where	 * @return int count records	 */	function count_keke_witkey_task_wbdj(){		if($this->_where){			$sql = "select count(*) as count from $this->_tablename where ".$this->_where;		}		else{			$sql = "select count(*) as count from $this->_tablename";		}		$this->_where = "";		return $this->_dbop->getCount($sql);	}
	/**	 * delete table keke_witkey_task_wbdj, if isset where delete by where	 * @return int deleted affected_rows	 */	function del_keke_witkey_task_wbdj(){		if($this->_where){			$sql = "delete from $this->_tablename where ".$this->_where;		}		else{			$sql = "delete from $this->_tablename where wbdj_id = $this->_wbdj_id ";		}		$this->_where = "";		return $this->_dbop->execute($sql);	}

	 
	 
}
?>