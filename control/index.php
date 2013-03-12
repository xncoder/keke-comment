<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = "index";
if ($task_open) {
	$final_task = kekezu::get_classify_indus();
	$task_in = db_factory::get_one ( sprintf ( " select sum(fina_cash) cash from %switkey_finance where fina_action='task_bid' and fina_type='in' ", TABLEPRE ), 1, 600 ); 
	$task_in = str_pad ( number_format ( $task_in ['cash'], 2, ".", "," ), 10, 0, STR_PAD_LEFT );
	$task_count = db_factory::get_one ( sprintf ( " select count(task_id) count from %switkey_task", TABLEPRE ), 1, 600 ); 
	$task_count = str_pad ( intval ( $task_count ['count'] ), 10, 0, STR_PAD_LEFT );
	$sql = " select task_id,task_title,task_cash,uid,username,city,view_num,focus_num,work_num,task_cash_coverage
		 from %switkey_task  where is_top='1' and (task_status='2' or task_status ='3' or task_status ='4' or task_status ='5' or task_status ='6')
		  order by start_time desc limit 0,10";
	$recomm_task = db_factory::query ( sprintf ( $sql, TABLEPRE ), true, 3600 );
}
if ($shop_open) {
	$final_shop = kekezu::get_classify_indus('shop');
	$service_in = db_factory::get_one ( sprintf ( " select sum(fina_cash) cash from %switkey_finance where fina_action='sale_service' and fina_type='in'", TABLEPRE ), 1, 600 ); 
	$service_in = str_pad ( number_format ( $service_in ['cash'], 2, ".", "," ), 10, 0, STR_PAD_LEFT );
	$service_count = db_factory::get_one ( sprintf ( " select count(service_id) count from %switkey_service where service_status='2'", TABLEPRE ), 1, 600 ); 
	$service_count = str_pad ( intval ( $service_count ['count'] ), 10, 0, STR_PAD_LEFT );
	$top_s_3 = db_factory::query ( sprintf ( "select a.username,a.uid,a.indus_id,a.indus_pid,a.seller_good_num,a.seller_total_num,b.shop_name from %switkey_shop b "
		." left join %switkey_space a on a.uid=b.uid  where a.recommend=1 and IFNULL(b.is_close,0)=0 order by a.uid desc limit 0,3", TABLEPRE,TABLEPRE ), 1, 600 );
	$range = range ( 0, 11 );
	$range2 = range(0,17);
	$recomm_service = db_factory::query ( sprintf ( "select service_id,price,unite_price,pic,ad_pic,title from %switkey_service where is_top='1' and service_status='2' order by on_time desc limit 0,12", TABLEPRE ), 1, 600 );
    $hot_range = range(0, 5);
    $hot_service = db_factory::query(sprintf("select * from %switkey_service order by total_sale desc limit 0,5", TABLEPRE), 1, 600);
}
if(isset($op)&&$op=='report_3'){
        $transname = keke_report_class::get_transrights_name($type);
		$title=$transname.$_lang['submit'];
		if($sbt_edit){
		    if (CHARSET == 'gbk') {
			$desc = kekezu::utftogbk ( $tar_content );
		    }else{
		    	$desc = $tar_content;
		    }
		    $report_obj = new Keke_witkey_report_class ();
			$report_obj->setObj ( $obj );			
			$report_obj->setUid ( $uid );
			$report_obj->setUsername ( $username );			
			$report_obj->setOn_time ( time () );			
			$report_obj->setReport_desc ( $desc );
			$report_obj->setReport_type ( 3 );
			$report_obj->setFront_status ( $front_status );
			$report_obj->setReport_file ( $file_url );
			$report_obj->setReport_status ( 1 );
			$report_obj->setIs_hide ( $is_hide );
			$report_id = $report_obj->create_keke_witkey_report ();
		    if ($report_id) {
			 kekezu::keke_show_msg ( '', $transname . $_lang ['submit_success_wait_website_process'], "", 'json' );
		    } else {
			 kekezu::keke_show_msg ( '', $transname . $_lang ['submit_fail'], "error", 'json' );
		    }
		}else{
			require keke_tpl_class::template("report");
		}
			die();	
}
$register = db_factory::get_one ( sprintf ( " select count(uid) count from %switkey_member ", TABLEPRE ), 1, 600 ); 
$register = str_pad ( intval ( $register ['count'] ), 10, 0, STR_PAD_LEFT );
$all_auth = db_factory::get_one ( sprintf ( " select count(record_id) count from %switkey_auth_record where auth_status='1'", TABLEPRE ), 1, 600 ); 
$all_auth = str_pad ( intval ( $all_auth ['count'] ), 10, 0, STR_PAD_LEFT );
$bulletin_arr = db_factory::query(sprintf("select art_id,art_title,listorder,is_recommend,pub_time from %switkey_article where cat_type='bulletin' order by is_recommend desc, listorder asc, pub_time desc limit 0,4",TABLEPRE));
$feed_list = db_factory::query ( "select uid,username,title,feed_time from " . TABLEPRE . "witkey_feed order by feed_time desc limit 0,4", 1, 3600 );
$mode_list = $kekezu->_model_list;
$cash_coverage = kekezu::get_cash_cove ( '', true ); 
$talent_list = db_factory::query ( sprintf ( " select uid,username from %switkey_space where status!=2 order by reg_time desc limit 0,9", TABLEPRE ), 1, 600 );
$income_rank = db_factory::query ( sprintf ( " select sum(a.fina_cash) as cash,a.uid,a.username from %switkey_finance a left join %switkey_space b on a.uid=b.uid where a.fina_type='in' and ( a.fina_action in('task_bid','sale_service') or INSTR(a.fina_action,'prom_')>0) and b.status!=2 group by a.uid order by cash desc limit 0,7 ", TABLEPRE, TABLEPRE ), 1, 600 ); 

// 行业分类
$industry_list = $catsql_list = $cat_task_list = array();
$industry_rst  = db_factory::query ( sprintf ( " Select indus_id,indus_pid,indus_name From %switkey_industry Order by indus_pid, listorder Asc ", TABLEPRE), 1, 600 );

if ( is_array($industry_rst) )
{
	foreach ($industry_rst as $row) 
	{
		if ( empty($row['indus_pid']) ) {
			$industry_list[$row['indus_id']]['name'] = $row['indus_name']; continue;
		}
		if ( isset($industry_list[$row['indus_pid']]) ) {
			$catsql_list[] = sprintf ( " Select task_id,task_title,indus_id From (Select * From %switkey_task Where indus_id='{$row['indus_id']}' Order by start_time Desc Limit 0,5) t{$row['indus_id']} ", TABLEPRE);
			$industry_list[$row['indus_pid']]['cat'][$row['indus_id']] = $row['indus_name'];
		}
	}
	
	if ( !empty($catsql_list) )
	{
		$cat_task_rst = db_factory::query ( implode(' Union ', $catsql_list), 1, 600 );
		if ( is_array($cat_task_rst) ) 
		{
			foreach ($cat_task_rst as $row) {
				$cat_task_list[$row['indus_id']][$row['task_id']] = $row['task_title'];
			}
		}
	}
}


if (isset ( $ajax )) 
{
	switch ($ajax) 
	{
		case "task" :
			$sql2 = " select task_id,task_title,task_cash,uid,username,city,view_num,focus_num,work_num,task_cash_coverage
			 	from %switkey_task  where  (task_status='2' or task_status ='3' ) 
			  	order by start_time desc limit 0,10";
			$task_lastest = db_factory::query ( sprintf ( $sql2, TABLEPRE ), true, 3600 );
			require keke_tpl_class::template ( "ajax/index" );
			die ();
		break;
		case "shop" :
			$service_lastes = db_factory::query ( sprintf ( "select service_id,pic,ad_pic,title,unite_price,price from %switkey_service where   service_status='2' order by on_time desc limit 0,18", TABLEPRE ), 1, 600 );
			require keke_tpl_class::template ( "ajax/index" );
			die ();
			break;
		case 'indus_index' :
			require keke_tpl_class::template ( "ajax/ajax_indus" );
			die ();
			break;
		case 'bid_notice' :
			$dynamic_arr = kekezu::get_feed ( "feedtype='work_accept'", "feed_time desc", 4 ); 
		    require keke_tpl_class::template ( "ajax/index" );
		    die ();
		    break;
		case 'withdraw' :
			$withdraw_arr = db_factory::query(sprintf("select * from %switkey_withdraw where withdraw_status=2 order by process_time desc limit 0,4",TABLEPRE));
		    require keke_tpl_class::template ( "ajax/index" );
		    die ();
		    break;				
	}
}
$page_title = $_K ['html_title'];
require keke_tpl_class::template ( $do );
