<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
	switch ($ajax){
		case "load":
			if($work_id){
				$file_ids = db_factory::get_count(sprintf(" select work_file from %switkey_task_work where work_id='%d'",TABLEPRE,$work_id));
				$file_list = keke_task_class::get_work_file($file_ids);
			}
			break;
		case "download":
			keke_file_class::file_down($file_name, $file_path);
			break;
		case "delete":
			$res = keke_file_class::del_att_file($file_id, $filepath);
			$res and kekezu::echojson ( '', '1' ) or kekezu::echojson ( '', '0' );
			die ();
			break;
		case "del":
			if(strtolower($_SERVER['REQUEST_METHOD'])!='post' || !isset($fid) || !isset($filepath)){	
				kekezu::echojson ( '', '0' );die();
			}
			$fid = intval($fid);
			$size = kekezu::escape($size);
			$res = keke_file_class::del_att_file($fid,$filepath,$size);
			$res and kekezu::echojson ( '', 1 ) or kekezu::echojson ( '', '0' );
			die ();
		case "goods_filedown":
			$service_info = db_factory::get_one(sprintf(" select file_path,submit_method,uid from %switkey_service where service_id='%d'",TABLEPRE,$_GET['sid']));
			$has_buy = keke_shop_class::check_has_buy($_GET['sid'], $user_info['uid']);
			if($has_buy['order_status']=='confirm'||$service_info['uid']==$user_info['uid']){
				$file_list = explode(",",$service_info['file_path']);
			}
			break;
		case "sreward_filedown":
		   $sql = sprintf("select file_ids from  %switkey_agreement a left join %switkey_task_work b on a.work_id = b.work_id 
		    where a.buyer_uid=$uid  and b.task_id = %d",TABLEPRE,TABLEPRE,$_GET['task_id']);
		   $file_ids = db_factory::query($sql);
		   $file_ids[0][file_ids] and $file_arr = db_factory::query(sprintf("select * from %switkey_file where file_id in (%s)",TABLEPRE,$file_ids[0][file_ids]));	   
			break;
		case "help_search":
			$keyword and $search_list = db_factory::query(sprintf(" select a.art_title,a.content from %switkey_article a left join %switkey_article_category b on a.art_cat_id=b.art_cat_id where b.cat_type='help' and INSTR(a.art_title,'%s')",TABLEPRE,TABLEPRE,$keyword));
		break;
	}
require keke_tpl_class::template("ajax/ajax_".$view);
