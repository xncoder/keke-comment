<?php
class Keke_witkey_template_class{
    public $_db;
    public $_tablename;
    public $_dbop;
    public $_tpl_id;
    public $_tpl_title;
    public $_tpl_desc;
    public $_develop;
    public $_tpl_pic;
    public $_tpl_path;
    public $_is_selected;
    public $_on_time;

    public $_cache_config = array ('is_cache' => 0, 'time' => 0 );
    public $_replace=0;
    public $_where;
    function  keke_witkey_template_class(){
        $this->_db = new db_factory ( );
        $this->_dbop = $this->_db->create(DBTYPE);
        $this->_tablename = TABLEPRE."witkey_template";
    }

    public function getTpl_id(){
        return $this->_tpl_id ;
    }
    public function getTpl_title(){
        return $this->_tpl_title ;
    }
    public function getTpl_desc(){
        return $this->_tpl_desc ;
    }
    public function getDevelop(){
        return $this->_develop ;
    }
    public function getTpl_pic(){
        return $this->_tpl_pic ;
    }
    public function getTpl_path(){
        return $this->_tpl_path ;
    }
    public function getIs_selected(){
        return $this->_is_selected ;
    }
    public function getOn_time(){
        return $this->_on_time ;
    }
    public function getWhere(){
        return $this->_where ;
    }
    public function getCache_config() {
        return $this->_cache_config;
    }

    public function setTpl_id($value){
        $this->_tpl_id = $value;
    }
    public function setTpl_title($value){
        $this->_tpl_title = $value;
    }
    public function setTpl_desc($value){
        $this->_tpl_desc = $value;
    }
    public function setDevelop($value){
        $this->_develop = $value;
    }
    public function setTpl_pic($value){
        $this->_tpl_pic = $value;
    }
    public function setTpl_path($value){
        $this->_tpl_path = $value;
    }
    public function setIs_selected($value){
        $this->_is_selected = $value;
    }
    public function setOn_time($value){
        $this->_on_time = $value;
    }
    public function setWhere($value){
        $this->_where = $value;
    }
    public function setCache_config($_cache_config) {
        $this->_cache_config = $_cache_config;
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
     * insert into  keke_witkey_template  ,or add new record
     * @return int last_insert_id
     */
    function create_keke_witkey_template(){
        $data =  array();

        if(!is_null($this->_tpl_id)){
            $data['tpl_id']=$this->_tpl_id;
        }
        if(!is_null($this->_tpl_title)){
            $data['tpl_title']=$this->_tpl_title;
        }
        if(!is_null($this->_tpl_desc)){
            $data['tpl_desc']=$this->_tpl_desc;
        }
        if(!is_null($this->_develop)){
            $data['develop']=$this->_develop;
        }
        if(!is_null($this->_tpl_pic)){
            $data['tpl_pic']=$this->_tpl_pic;
        }
        if(!is_null($this->_tpl_path)){
            $data['tpl_path']=$this->_tpl_path;
        }
        if(!is_null($this->_is_selected)){
            $data['is_selected']=$this->_is_selected;
        }
        if(!is_null($this->_on_time)){
            $data['on_time']=$this->_on_time;
        }

        return $this->_tpl_id = $this->_db->inserttable($this->_tablename,$data,1,$this->_replace);
    }

    /**
     * edit table keke_witkey_template
     * @return int affected_rows
     */
    function edit_keke_witkey_template(){
        $data =  array();

        if(!is_null($this->_tpl_id)){
            $data['tpl_id']=$this->_tpl_id;
        }
        if(!is_null($this->_tpl_title)){
            $data['tpl_title']=$this->_tpl_title;
        }
        if(!is_null($this->_tpl_desc)){
            $data['tpl_desc']=$this->_tpl_desc;
        }
        if(!is_null($this->_develop)){
            $data['develop']=$this->_develop;
        }
        if(!is_null($this->_tpl_pic)){
            $data['tpl_pic']=$this->_tpl_pic;
        }
        if(!is_null($this->_tpl_path)){
            $data['tpl_path']=$this->_tpl_path;
        }
        if(!is_null($this->_is_selected)){
            $data['is_selected']=$this->_is_selected;
        }
        if(!is_null($this->_on_time)){
            $data['on_time']=$this->_on_time;
        }

        if($this->_where){
            return $this->_db->updatetable($this->_tablename,$data,$this->getWhere());
        }
        else{
            $where = array('tpl_id' => $this->_tpl_id);
            return $this->_db->updatetable($this->_tablename,$data,$where);
        }
    }

    /**
     * query table: keke_witkey_template,if isset where return where record,else return all record
     * @return array
     */
    function query_keke_witkey_template($is_cache=0, $cache_time=0){
        if($this->_where){
            $sql = "select * from $this->_tablename where ".$this->_where;
        }
        else{
            $sql = "select * from $this->_tablename";
        }
        if ($is_cache) {
            $this->_cache_config ['is_cache'] = $is_cache;
        }
        if ($cache_time) {
            $this->_cache_config ['time'] = $cache_time;
        }
        if ($this->_cache_config ['is_cache']) {
            if (CACHE_TYPE) {
                $keke_cache = new keke_cache_class ( CACHE_TYPE );
                $id = $this->_tablename . ($this->_where?"_" .substr(md5 ( $this->_where ),0,6):'');
                $data = $keke_cache->get ( $id );
                if ($data) {
                    return $data;
                } else {
                    $res = $this->_dbop->query ( $sql );
                    $keke_cache->set ( $id, $res,$this->_cache_config['time'] );
                    $this->_where = "";
                    return $res;
                }
            }
        }else{
            $this->_where = "";
            return  $this->_dbop->query ( $sql );
        }
    }

    /**
     * query count keke_witkey_template records,if iset where query by where
     * @return int count records
     */
    function count_keke_witkey_template(){
        if($this->_where){
            $sql = "select count(*) as count from $this->_tablename where ".$this->_where;
        }
        else{
            $sql = "select count(*) as count from $this->_tablename";
        }
        $this->_where = "";
        return $this->_dbop->getCount($sql);
    }

    /**
     * delete table keke_witkey_template, if isset where delete by where
     * @return int deleted affected_rows
     */
    function del_keke_witkey_template(){
        if($this->_where){
            $sql = "delete from $this->_tablename where ".$this->_where;
        }
        else{
            $sql = "delete from $this->_tablename where tpl_id = $this->_tpl_id ";
        }
        $this->_where = "";
        return $this->_dbop->execute($sql);
    }




}
?>
