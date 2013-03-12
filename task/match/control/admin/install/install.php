<?php
define( "IN_KEKE" ,true);
	$model_name = "match";
	/*********Table witkey_mark_config start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_mark_config'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_mark_config` (
		`mark_config_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`mark_config_id`)
			,	`obj` char(20) default null 
			,	`good` int(3) default null 
			,	`normal` int(3) default null 
			,	`bad` int(3) default null 
			,	`type` int(1) default null 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_mark_config where Field='mark_config_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_mark_config  change column  mark_config_id mark_config_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_mark_config add mark_config_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_mark_config where Field='obj' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="char(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_mark_config  change column  obj obj char(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_mark_config add obj char(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_mark_config where Field='good' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(3)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_mark_config  change column  good good int(3)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_mark_config add good int(3) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_mark_config where Field='normal' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(3)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_mark_config  change column  normal normal int(3)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_mark_config add normal int(3) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_mark_config where Field='bad' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(3)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_mark_config  change column  bad bad int(3)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_mark_config add bad int(3) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_mark_config where Field='type' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(1)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_mark_config  change column  type type int(1)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_mark_config add type int(1) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	
	db_factory::execute("replace into ".TABLEPRE."witkey_mark_config (`mark_config_id`,`obj`,`good`,`normal`,`bad`,`type`) values ('15','match','100','50',0,'1')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_mark_config (`mark_config_id`,`obj`,`good`,`normal`,`bad`,`type`) values ('16','match','100','50',0,'2')");
					}
	/*********Table witkey_mark_config end **************/
	/*********Table witkey_model start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_model'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_model` (
		`model_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`model_id`)
			,	`model_code` varchar(20) default null 
			,	`model_name` varchar(20) default null 
			,	`model_dir` varchar(20) default null 
			,	`model_type` char(10) default null 
			,	`model_dev` varchar(20) default null 
			,	`model_status` int(11) default null 
			,	`model_desc` text default null 
			,	`on_time` int(11) default null 
			,	`hide_mode` int(11) default null 
			,	`listorder` int(11) default 0 
			,	`model_intro` varchar(255) default null 
			,	`indus_bid` text default null 
			,	`config` text default null 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_id model_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_code' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_code model_code varchar(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_code varchar(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_name' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_name model_name varchar(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_name varchar(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_dir' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_dir model_dir varchar(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_dir varchar(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_type' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="char(10)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_type model_type char(10)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_type char(10) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_dev' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_dev model_dev varchar(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_dev varchar(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_status' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_status model_status int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_status int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_desc' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="text"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_desc model_desc text");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_desc text null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='on_time' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  on_time on_time int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add on_time int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='hide_mode' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  hide_mode hide_mode int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add hide_mode int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='listorder' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  listorder listorder int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add listorder int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='model_intro' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(255)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  model_intro model_intro varchar(255)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add model_intro varchar(255) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='indus_bid' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="text"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  indus_bid indus_bid text");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add indus_bid text null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_model where Field='config' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="text"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_model  change column  config config text");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_model add config text null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	
	db_factory::execute("replace into ".TABLEPRE."witkey_model (`model_id`,`model_code`,`model_name`,`model_dir`,`model_type`,`model_dev`,`model_status`,`model_desc`,`on_time`,`hide_mode`,`listorder`,`model_intro`,`indus_bid`,`config`) values ('12','match','速配任务','match','task','kekezu','1','速配任务',0,0,'0','','','a:8:{s:9:\"task_rate\";s:2:\"10\";s:7:\"deposit\";s:2:\"99\";s:12:\"deposit_rate\";s:2:\"10\";s:8:\"defeated\";s:1:\"1\";s:14:\"task_fail_rate\";s:1:\"5\";s:7:\"min_day\";s:1:\"1\";s:7:\"max_day\";s:2:\"50\";s:7:\"cutdown\";s:1:\"0\";}')");
					}
	/*********Table witkey_model end **************/
	/*********Table witkey_msg_config start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_msg_config'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_msg_config` (
		`config_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`config_id`)
			,	`k` varchar(30) default null 
			,	`obj` char(20) default null 
			,	`desc` varchar(30) default null 
			,	`v` varchar(255) default null 
			,	`on_time` int(11) default null 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_config where Field='config_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_config  change column  config_id config_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_config add config_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_config where Field='k' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(30)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_config  change column  k k varchar(30)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_config add k varchar(30) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_config where Field='obj' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="char(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_config  change column  obj obj char(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_config add obj char(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_config where Field='desc' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(30)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_config  change column  desc desc varchar(30)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_config add desc varchar(30) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_config where Field='v' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(255)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_config  change column  v v varchar(255)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_config add v varchar(255) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_config where Field='on_time' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_config  change column  on_time on_time int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_config add on_time int(11) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	
	db_factory::execute("replace into ".TABLEPRE."witkey_msg_config (`config_id`,`k`,`obj`,`desc`,`v`,`on_time`) values ('102','match_task','task','速配任务','a:2:{s:8:\"send_sms\";i:1;s:10:\"send_email\";i:1;}','1335428501')");
					}
	/*********Table witkey_msg_config end **************/
	/*********Table witkey_msg_tpl start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_msg_tpl'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_msg_tpl` (
		`tpl_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`tpl_id`)
			,	`tpl_code` varchar(30) default 0 
			,	`content` text default null 
			,	`send_type` int(1) default null 
			,	`listorder` int(11) default 0 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_tpl where Field='tpl_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_tpl  change column  tpl_id tpl_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_tpl add tpl_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_tpl where Field='tpl_code' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(30)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_tpl  change column  tpl_code tpl_code varchar(30)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_tpl add tpl_code varchar(30) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_tpl where Field='content' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="text"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_tpl  change column  content content text");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_tpl add content text null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_tpl where Field='send_type' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(1)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_tpl  change column  send_type send_type int(1)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_tpl add send_type int(1) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_msg_tpl where Field='listorder' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_msg_tpl  change column  listorder listorder int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_msg_tpl add listorder int(11) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	
	db_factory::execute("replace into ".TABLEPRE."witkey_msg_tpl (`tpl_id`,`tpl_code`,`content`,`send_type`,`listorder`) values ('163','match_task','<p></p><p>尊敬的 {用户名}：{描述}。</p><p>任务标题：{任务标题}。</p><p>感谢您对{网站名称}的信任。如有特殊情况，请致电客服</p><p></p><br />','1','0')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_msg_tpl (`tpl_id`,`tpl_code`,`content`,`send_type`,`listorder`) values ('164','match_task','<p></p><p></p><p>尊敬的 {用户名}：{描述}。任务编号：{任务编号}。任务链接：{任务链接}。任务结束后您的诚意金将退还。感谢您对{网站名称}的信任。如有特殊情况，请致电客服</p><br />','2','0')");
					}
	/*********Table witkey_msg_tpl end **************/
	/*********Table witkey_priv_item start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_priv_item'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_priv_item` (
		`op_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`op_id`)
			,	`model_id` int(11) default null 
			,	`op_code` varchar(20) default null 
			,	`op_name` varchar(50) default null 
			,	`allow_times` int(1) default null 
			,	`limit_obj` int(111) default null 
			,	`condit` varchar(200) default null 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='op_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  op_id op_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add op_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='model_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  model_id model_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add model_id int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='op_code' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(20)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  op_code op_code varchar(20)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add op_code varchar(20) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='op_name' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(50)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  op_name op_name varchar(50)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add op_name varchar(50) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='allow_times' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(1)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  allow_times allow_times int(1)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add allow_times int(1) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='limit_obj' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(111)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  limit_obj limit_obj int(111)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add limit_obj int(111) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_item where Field='condit' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(200)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_item  change column  condit condit varchar(200)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_item add condit varchar(200) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_item (`op_id`,`model_id`,`op_code`,`op_name`,`allow_times`,`limit_obj`,`condit`) values ('74','12','work_hand','交稿','1','1','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_item (`op_id`,`model_id`,`op_code`,`op_name`,`allow_times`,`limit_obj`,`condit`) values ('75','12','pub','发布任务','1','2','')");
					}
	/*********Table witkey_priv_item end **************/
	/*********Table witkey_priv_rule start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_priv_rule'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_priv_rule` (
		`r_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`r_id`)
			,	`item_id` int(11) default null 
			,	`mark_rule_id` char(5) default null 
			,	`rule` char(5) default null 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_rule where Field='r_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_rule  change column  r_id r_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_rule add r_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_rule where Field='item_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_rule  change column  item_id item_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_rule add item_id int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_rule where Field='mark_rule_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="char(5)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_rule  change column  mark_rule_id mark_rule_id char(5)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_rule add mark_rule_id char(5) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_priv_rule where Field='rule' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="char(5)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_priv_rule  change column  rule rule char(5)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_priv_rule add rule char(5) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('304','74','1','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('305','74','2','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('306','74','3','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('307','74','4','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('308','74','5','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('309','74','6','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('310','75','1','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('311','75','2','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('312','75','3','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('313','75','4','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('314','75','5','')");
					
	db_factory::execute("replace into ".TABLEPRE."witkey_priv_rule (`r_id`,`item_id`,`mark_rule_id`,`rule`) values ('315','75','6','')");
					}
	/*********Table witkey_priv_rule end **************/
	/*********Table witkey_task_match start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_task_match'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_task_match` (
		`mt_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`mt_id`)
			,	`task_id` int(11) default 0 
			,	`hirer_deposit` decimal(10,2) default 0.00 
			,	`deposit_cash` decimal(10,2) default 0.00 
			,	`deposit_credit` decimal(10,2) default 0.00 
			,	`host_amount` decimal(10,2) default 0.00 
			,	`host_cash` decimal(10,2) default null 
			,	`host_credit` decimal(10,2) default null 
			,	`deposit_rate` int(3) default 0 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='mt_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  mt_id mt_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add mt_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='task_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  task_id task_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add task_id int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='hirer_deposit' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  hirer_deposit hirer_deposit decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add hirer_deposit decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='deposit_cash' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  deposit_cash deposit_cash decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add deposit_cash decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='deposit_credit' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  deposit_credit deposit_credit decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add deposit_credit decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='host_amount' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  host_amount host_amount decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add host_amount decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='host_cash' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  host_cash host_cash decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add host_cash decimal(10,2) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='host_credit' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  host_credit host_credit decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add host_credit decimal(10,2) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match where Field='deposit_rate' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(3)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match  change column  deposit_rate deposit_rate int(3)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match add deposit_rate int(3) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	}
	/*********Table witkey_task_match end **************/
	/*********Table witkey_task_match_work start **************/
	$table_exist = db_factory::query("SHOW   TABLES   LIKE   '".TABLEPRE."witkey_task_match_work'");
	
	$table_exist = $table_exist[0]?true:false;
	
	if (!$table_exist){
	
	$res = db_factory::execute("CREATE TABLE IF NOT EXISTS `".TABLEPRE."witkey_task_match_work` (
		`mw_id` int(11) NOT NULL auto_increment 	,PRIMARY KEY  (`mw_id`)
			,	`work_id` int(11) default 0 
			,	`wiki_deposit` decimal(10,2) default 0.00 
			,	`deposit_cash` decimal(10,2) default 0.00 
			,	`deposit_credit` decimal(10,2) default 0.00 
			,	`witkey_contact` varchar(255) default null 
			) ENGINE=MyISAM  DEFAULT CHARSET=".DBCHARSET."");
		
	}else{
	
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match_work where Field='mw_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match_work  change column  mw_id mw_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match_work add mw_id int(11) not null default null auto_increment");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match_work where Field='work_id' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="int(11)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match_work  change column  work_id work_id int(11)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match_work add work_id int(11) null default null ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match_work where Field='wiki_deposit' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match_work  change column  wiki_deposit wiki_deposit decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match_work add wiki_deposit decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match_work where Field='deposit_cash' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match_work  change column  deposit_cash deposit_cash decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match_work add deposit_cash decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match_work where Field='deposit_credit' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="decimal(10,2)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match_work  change column  deposit_credit deposit_credit decimal(10,2) default 0.00");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match_work add deposit_credit decimal(10,2) null default 0.00 ");
		}
		
		$col_info = db_factory::query("show COLUMNS FROM ".TABLEPRE."witkey_task_match_work where Field='witkey_contact' ");
		$col_info = $col_info[0];
		if($col_info){
			if($col_info["Type"]!="varchar(255)"){
				db_factory::execute("alter  table ".TABLEPRE."witkey_task_match_work  change column  witkey_contact witkey_contact varchar(255)");
			}
		}
		else{
			db_factory::execute("alter table ".TABLEPRE."witkey_task_match_work add witkey_contact varchar(255) null default null ");
		}
		
	}
	$table_exist=false;
	if(!$table_exist){
	}
	/*********Table witkey_task_match_work end **************/