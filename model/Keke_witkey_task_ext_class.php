<?php
class Keke_witkey_task_ext_class extends Keke_witkey_task_class{

	function query_keke_witkey_task($is_cache = 0, $cache_time = 0){//查询witkey_task(a)表和witkey_task_delay(b)表中相同task_id的记录，并（根据条件）统计总的延时金额
		if($this->_where){
			$sql = "
				 SELECT a.*, (select count(b.delay_cash)
			  from ".TABLEPRE."witkey_task_delay b 
			  where a.task_id = b.task_id and b.delay_status>0)  
			  as delay_cash  FROM  $this->_tablename 
			  a  where ".$this->_where; 

		}
		else{
			$sql = " SELECT a.*, (select count(b.delay_cash)
			  from ".TABLEPRE."witkey_task_delay b 
			  where a.task_id = b.task_id )  
			  as delay_cash  FROM  $this->_tablename 
			  a  "; 
		}
		$this->_where = "";
		return $this->_dbop->query($sql);
	}

	 
	function count_keke_witkey_task($is_cache = 0, $cache_time = 0){ //统计witkey_task_delay表和witkey_task表中都出现的记录（on a.task_id = b.task_id）条数，但会返回witkey_task_delay表的所有行（left join ）
		if($this->_where){
			$sql = "select count(*) as count
				 from $this->_tablename a left join ".TABLEPRE."witkey_task_delay b
				 on a.task_id = b.task_id 
				 where ".$this->_where; 
		}
		else{
			$sql = "select count(*) as count from $this->_tablename
				  a left join ".TABLEPRE."witkey_task_delay b
				 on a.task_id = b.task_id 
				 "; 
		}
		$this->_where = "";
		return $this->_dbop->getCount($sql);
	}
		
	function query_keke_witkey_task_industry(){
		if($this->_where){
			$sql = "select * from $this->_tablename
				 a left join ".TABLEPRE."witkey_industry b 
				 on a.indus_id = b.indus_id 
				 where ".$this->_where; 
		}
		else{
			$sql = "select * from $this->_tablename
				 a left join ".TABLEPRE."witkey_industry b 
				 on a.indus_id = b.indus_id";
		}
		$this->_where = "";
		return $this->_dbop->query($sql);
	}
	function query_keke_witkey_tl_task(){
		if($this->_where){
			$sql = "
				 SELECT a.*,c.*, (select count(b.delay_cash)
			  from ".TABLEPRE."witkey_task_delay b 
			  where a.task_id = b.task_id )  
			  as delay_cash  FROM  $this->_tablename 
			  a left join ".TABLEPRE."witkey_tl_task c  on a.task_id = c.task_id where ".$this->_where; 
				

		}
		else{
			$sql = " SELECT a.*,c.*, (select count(b.delay_cash)
			  from ".TABLEPRE."witkey_task_delay b
			  where a.task_id = b.task_id )
			  as delay_cash  FROM  $this->_tablename
			  a left join ".TABLEPRE."witkey_tl_task c  on a.task_id = c.task_id ";
		}
		$this->_where = "";
		return $this->_dbop->query($sql);
	}

	function count_keke_witkey_tl_task(){
		if($this->_where){
			$sql = "select count(*) as count from $this->_tablename
				  a left join ".TABLEPRE."witkey_task_delay b
				 on a.task_id = b.task_id left join ".TABLEPRE."witkey_tl_task c  on a.task_id = c.task_id
				 where ".$this->_where; 
		}
		else{
			$sql = "select count(*) as count from $this->_tablename
				  a left join ".TABLEPRE."witkey_task_delay b
				 on a.task_id = b.task_id  left join ".TABLEPRE."witkey_tl_task c  on a.task_id = c.task_id
				 "; 
		}
		$this->_where = "";
		return $this->_dbop->getCount($sql);
	}
	function count_keke_witkey_task_industry(){
		if($this->_where){
			$sql = "select count(*) as count from $this->_tablename
				  a left join ".TABLEPRE."witkey_industry b 
				 on a.indus_id = b.indus_id 
				 where ".$this->_where; 
		}
		else{
			$sql = "select count(*) as count from $this->_tablename
				  a left join ".TABLEPRE."witkey_industry b 
				 on a.indus_id = b.indus_id";
		}
		$this->_where = "";
		return $this->_dbop->getCount($sql);
	}
}