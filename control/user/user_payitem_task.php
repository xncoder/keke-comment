<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$payitem_time = "1325811866,1325811866";
$payitem_standard = keke_payitem_class::payitem_standard (); 
$payitem_type_arr = keke_glob_class::get_payitem_arr ();
$payitem_arr = explode ( ',', $payitem_time );
foreach ( $payitem_arr as $k => $v ) {
	if ($v > time ()) {
		$sy_time_str = $v - time ();
		$sy_time_desc [$payitem_type_arr [$k]] = kekezu::time2Units ( $sy_time_str );
	} else {
		$sy_time_desc [$payitem_type_arr [$k]] = '0Ìì';
	}
}
$sy_time_arr = array ("top" => "", "urgent" => "" ); 
$sql = sprintf ( "select * from %switkey_payitem where item_type ='task' and find_in_set('%s',model_code)", TABLEPRE, $model_list ['1'] ['model_code'] );
$payitem_list = db_factory::query ( $sql );
require keke_tpl_class::template ( "user/user_" . $op . $show );