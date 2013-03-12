<?php
keke_lang_class::load_lang_class('keke_custom_field_class');
class keke_custom_field_class {
	static function get_input_typelist(){
		global $_lang;
		$_input_type_list = array(
			'text'=>$_lang['text'],
			'radio'=>$_lang['radio'],
			'select'=>$_lang['select'],
			'check'=>$_lang['check'],
		);
		return $_input_type_list;
	}
	static function get_valid_typelist(){
		global $_lang;
		$_input_valid_list = array(
			'int'=>$_lang['int'],
			'float'=>$_lang['float'],
			'digit'=>$_lang['digit'],
			'date'=>$_lang['time'],
			'time'=>$_lang['timestamp'],
			'tel'=>$_lang['tel'],
			'ip'=>'IP',
			'url'=>'URL',
			'idCard'=>$_lang['idcard'],
			'email'=>$_lang['mail'],
		);
		return $_input_valid_list;
	}
	static function get_field_list($table,$model_id=""){
		$tab = $model_id?"{$table}_{$model_id}":$table;
		$field_obj = new Keke_witkey_field_class();
		if ($model_id){
			$field_obj->setWhere("field_table = '$tab' or field_table = '{$table}_all' order by listorder");
		}
		else{
			$field_obj->setWhere("field_table = '$tab' order by listorder");
		}
		$field_list = $field_obj->query_keke_witkey_field();
		return $field_list;
	}
	static function field_html($table,$model_id="",$tplname="",$obj_id=0)
	{
		$field_list = self::get_field_list($table,$model_id);
		if ($obj_id){
			$field_data = self::get_field_data($table,$model_id,$obj_id);
		}
		$tplname = $tplname?$tplname:"default";
		global $model_list,$_K;
		if (file_exists("task/tpl/".$_K['template']."/".$model_list[$model_id]['model_']."/field_{$tplname}.htm")){
			$tplfile = "task/tpl/".$_K['template']."/".$model_list[$model_id]['model_']."/field_$tplname";
		}
		elseif (file_exists("task/tpl/default/".$model_list[$model_id]['model_']."/field_{$tplname}.htm")){
			$tplfile = "task/tpl/default/".$model_list[$model_id]['model_']."/field_$tplname";
		}
		else {
			$tplfile = "field_$tplname";
		}
		require keke_tpl_class::template ( $tplfile );
	}
	static function set_field_data($table,$model_id="",$obj_id,$data){
		$field_list = self::get_field_list($table,$model_id);
		$olddata = self::get_field_data($table,$model_id,$obj_id);
		$table = $model_id?"{$table}_{$model_id}":$table;
		$field_data_obj = new Keke_witkey_fielddata_class();
		if(!empty($field_list)){
		foreach ($field_list as $f){
			$field_data_obj->_data_id = null;
			$data_value = '';
			if ($f['field_type']!="check"){
				$data_value = $data['field_'.$f[field_id]];
			}
			elseif ($data['field_'.$f[field_id]]){
				$data_value = implode(",", $data['field_'.$f[field_id]]);
			}
			$field_data_obj->setData_value($data_value);
			if ($olddata[$f[field_id]]){
				if ($olddata[$f[field_id]]!=$data_value){
					$field_data_obj->setWhere("obj_type = '$table' and obj_id = $obj_id and field_id={$f['field_id']}");
					$field_data_obj->edit_keke_witkey_fielddata();
				}
			}
			else{
				$field_data_obj->setField_id($f['field_id']);
				$field_data_obj->setObj_id($obj_id);
				$field_data_obj->setObj_type($table);
				$field_data_obj->create_keke_witkey_fielddata();
			}
		}
		}
	}
	static function get_field_data($table,$model_id="",$obj_id){
		$table = $model_id?"{$table}_{$model_id}":$table;
		$field_data_obj = new Keke_witkey_fielddata_class();
		$field_data_obj->setWhere("obj_type = '$table' and obj_id = $obj_id");
		$datalist = $field_data_obj->query_keke_witkey_fielddata();
		$r = array();
		if ($datalist){
			foreach ($datalist as $data){
				$r[$data['field_id']] = $data['data_value'];
			}
		}
		return $r;
	}
	static function getConfig($key){
		$field_data_obj = new Keke_witkey_fielddata_class();
		$field_data_obj->setWhere("obj_type = 'config_{$key}'");
		$fdata = $field_data_obj->query_keke_witkey_fielddata();
		return $fdata[0]['data_value'];
	}
	static function setConfig($key,$value){
		$field_data_obj = new Keke_witkey_fielddata_class();
		$field_data_obj->setWhere("obj_type = 'config_{$key}'");
		$fdata = $field_data_obj->query_keke_witkey_fielddata();
		$field_data_obj->setObj_type("config_{$key}");
		$field_data_obj->setData_value($value);
		if ($fdata) {
			$field_data_obj->setWhere("data_id = '{$fdata['data_id']}'");
			$res = $field_data_obj->edit_keke_witkey_fielddata();
		}
		else {
			$res = $field_data_obj->create_keke_witkey_fielddata();
		}
		return $res;
	}
}
?>