<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$bank_arr=keke_glob_class::get_bank();
$ac_url=$origin_url."&op=$op&opp=$opp";
if($rebind){
	if($bank_id){
		if($bank_a_id){
			 db_factory::execute(sprintf(" delete from %switkey_auth_bank where bank_a_id='%d'",TABLEPRE,$bank_a_id));
			 db_factory::execute(sprintf(" delete from %switkey_auth_record where ext_data='%d'",TABLEPRE,$bank_a_id));
		}
		$res=db_factory::execute(sprintf(" delete from %switkey_member_bank where bank_id='%d'",TABLEPRE,$bank_id));
		$res and kekezu::show_msg($_lang['unbind_successful'],$ac_url."#userCenter",3,'','success') or kekezu::show_msg($_lang['unbind_fail'],$ac_url."#userCenter",3,'','warning');
	}else{
		kekezu::show_msg($_lang['please_select_an_account'],$ac_url."#userCenter",3,'','warning');
	}
}else{
	$account_list = db_factory::query(sprintf(" select * from %switkey_member_bank where uid = '%d' and bind_status='1'",TABLEPRE,$uid));
	$auth_list    = kekezu::get_table_data('bank_a_id,bank_id',"witkey_auth_bank"," uid='$uid' and auth_status!=2",'','','','bank_id',null);
}
require keke_tpl_class::template ( "user/" . $do . "_" . $op."_".$opp );