<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$page_obj = $kekezu->_page_obj;
$action or $action = 'basic';
$third_nav=array("basic"=>array($_lang['finance_detail'],$_lang['finance_record_stats']),
				"charge"=>array($_lang['recharge_record'],$_lang['recharge_record_stats']),
				"withdraw"=>array($_lang['withdraw_record'],$_lang['withdraw_record_stats']));
$where = " uid = '$uid' ";
intval ( $page_size ) or $page_size = '10';
intval ( $page ) or $page = '1';
$url = $origin_url . "&op=$op&action=$action&page_size=$page_size&page=$page";
switch ($action) {
	case "basic" :
		$fina_obj = new Keke_witkey_finance_class ();
		$action_arr = keke_glob_class::get_finance_action (); 
		$ord_arr = array (" a.fina_id desc " =>$_lang['finance_id_desc'], " a.fina_id asc " =>$_lang['finance_id_asc'], " a.fina_time desc " =>$_lang['pay_time_desc'], " a.fina_time asc " => $_lang['pay_time_asc']);
		$fina_count =kekezu::get_table_data(" sum(fina_cash) as cash, sum(fina_credit) as credit,fina_type ","witkey_finance"," $where    and fina_action not in ('withdraw','offline_recharge','offline_charge','online_charge','online_recharge','withdraw_fail')",""," fina_type","","fina_type");
		$sql = ' select a.*,b.task_title,c.title from '.TABLEPRE.'witkey_finance a left join '
				.TABLEPRE.'witkey_task b on a.obj_id=b.task_id left join '.TABLEPRE
				.'witkey_service c on a.obj_id=c.service_id ';
		$where =" where a.uid=".$uid."  and a.fina_action not in ('withdraw','offline_recharge','offline_charge','online_charge','online_recharge','withdraw_fail')";
		intval($fina_id) and $where .= " and a.fina_id = $fina_id ";
		$fina_type and $where .= " and a.fina_type = '$fina_type' ";
		$ord and $where .= " order by $ord " or $where .= " order by a.fina_id desc ";
		$count = intval(db_factory::get_count(' select count(fina_id) from '.TABLEPRE.'witkey_finance where uid='.$uid.' and fina_action not in ("withdraw","offline_recharge","offline_charge","online_charge","online_recharge","withdraw_fail")'));
		$pages = $page_obj->getPages ( $count, $page_size, $page, $url, '#userCenter' );
		$fina_arr = db_factory::query($sql.$where.$pages['where']);
		break;
	case "charge" :
		$charge_obj=new Keke_witkey_order_charge_class();
		$order_type_arr=keke_glob_class::get_charge_type();
		$bank_arr=keke_glob_class::get_bank();
		$status_arr = keke_order_class::get_order_status();
		$ord_arr = array (" order_id desc " =>$_lang['recharge_id_desc'], " order_id asc " => $_lang['recharge_id_asc'], " pay_time desc " =>$_lang['recharge_time_desc'], " pay_time asc " =>$_lang['recharge_time_asc']);
		$fina_count =kekezu::get_table_data(" sum(pay_money) as cash,order_status ","witkey_order_charge"," $where ",""," order_status","","order_status");
		$order_id && $order_id != $_lang['please_input_recharge_id'] and $where .= " and order_id = '$order_id' ";
		$order_type and $where .= " and order_type = '$order_type' ";
		$ord and $where .= " order by $ord " or $where .= " order by order_id desc ";
		$charge_obj->setWhere ( $where );
		$count = intval ( $charge_obj->count_keke_witkey_order_charge());
		$pages = $page_obj->getPages ( $count, $page_size, $page, $url, '#userCenter' );
		$charge_obj->setWhere ( $where . $pages ['where'] );
		$charge_arr=$charge_obj->query_keke_witkey_order_charge();
		break;
	case "withdraw" :
		$status_arr  = keke_glob_class::withdraw_status();
		$withdraw_obj=new Keke_witkey_withdraw_class();
		$fina_count =kekezu::get_table_data(" sum(withdraw_cash) as cash,withdraw_status ","witkey_withdraw"," $where ",""," withdraw_status","","withdraw_status");
		$ord_arr = array (" withdraw_id desc " => $_lang['withdraw_id_desc'], " withdraw_id asc " =>$_lang['withdraw_id_asc'], " applic_time desc " =>$_lang['apply_time_desc'], " applic_time asc " => $_lang['apply_time_asc'] );
		$withdraw_id && $withdraw_id != $_lang['please_input_withdraw_id'] and $where .= " and withdraw_id = '$withdraw_id' ";
		$ord and $where .= " order by $ord " or $where .= " order by withdraw_id desc ";
		$withdraw_obj->setWhere ( $where );
		$count = intval ( $withdraw_obj->count_keke_witkey_withdraw());
		$pages = $page_obj->getPages ( $count, $page_size, $page, $url, '#userCenter' );
		$withdraw_obj->setWhere ( $where . $pages ['where'] );
		$withdraw_arr=$withdraw_obj->query_keke_witkey_withdraw();
		$bank_arr = keke_glob_class::get_bank();
		$online   = keke_glob_class::get_online_pay();
		$bank_arr = array_merge($online,$bank_arr);
		break;
}
require keke_tpl_class::template ( "user/" . $do . "_" . $view . "_" . $op );
