<?php
define ( "IN_KEKE", TRUE );
error_reporting ( 0 );
$i_model = 1;
require_once 'app_comm.php';
$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_space where Field='union_user' ");
$col_info = $col_info[0];
if($col_info){
	if($col_info["Type"]!="tinyint(1)"){
		db_factory::execute("alter  table ".TABLEPRE."witkey_space  change column  union_user union_user tinyint(1)");
	}
}
else{
	db_factory::execute("alter table ".TABLEPRE."witkey_space add union_user tinyint(1) null default null ");
}
$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_space where Field='union_assoc' ");
$col_info = $col_info[0];
if($col_info){
	if($col_info["Type"]!="tinyint(1)"){
		db_factory::execute("alter  table ".TABLEPRE."witkey_space  change column  union_assoc union_assoc tinyint(1)");
	}
}
else{
	db_factory::execute("alter table ".TABLEPRE."witkey_space add union_assoc tinyint(1) null default null ");
}
$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_space where Field='union_rid' ");
$col_info = $col_info[0];
if($col_info){
	if($col_info["Type"]!="tinyint(1)"){
		db_factory::execute("alter  table ".TABLEPRE."witkey_space  change column  union_rid union_rid tinyint(1)");
	}
}
else{
	db_factory::execute("alter table ".TABLEPRE."witkey_space add union_rid tinyint(1) null default null ");
}

db_factory::execute("delete from  ".TABLEPRE."witkey_msg_config where config_id =136");

$file_obj = new keke_file_class();
$file_obj->delete_files(S_ROOT."/data/data_cache/");
$file_obj->delete_files(S_ROOT."/data/tpl_c/");
kekezu::show_msg("操作提示",$_K[siteurl],3,"程序更新成功","success");