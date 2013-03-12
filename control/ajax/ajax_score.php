<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$ac_arr = array ("login" => $_lang['login'], "register" => $_lang['register'], "update_pic" => $_lang['update_pic'], "edit_userinfo" => $_lang['improve_user_info'], "edit_withdrawinfo" => $_lang['improve_bank_accout'], "buy_vip" => $_lang['buy_vip'], "online_pay" => $_lang['online_pay'], "withdraw" => $_lang['wit_operate'], "pub_task" => $_lang['pub_task'], "view_task" => $_lang['view_task'], "collect_task" => $_lang['collect_task'], 
"task_comment" => $_lang['task_comment'], "task_apply" => $_lang['task_apply'], "task_pubwork" => $_lang['task_pubwork'], "task_bid" => $_lang['task_bid'], "work_accept" => $_lang['work_accept'], "view_space" => $_lang['view_space'], "user_mark" => $_lang['user_mark'], "access_shop" =>$_lang['access_shop'], "buy_service" => $_lang['buy_service'], "create_service" => $_lang['create_service'], "service_comment" => $_lang['service_comment'], "create_shop" => $_lang['create_shop'],"user_comment"=>$_lang['user_comment'] );
$ty_arr = array ("1" => $_lang['first_time'], "2" => $_lang['every_time'], "3" => $_lang['once_every_day'] );
if ($op == "getscore") {
	if (isset ( $_COOKIE ['score_log'] )) {
		$score_str = $_COOKIE ['score_log'];
		$score_arr = explode('-',$score_str);
		$ac_str = $ac_arr [$score_arr [1]];
		$ty_str = $ty_arr [$score_arr [2]];
		$msg = "
            <div class=\"popupmenu_layer\">
			<p>$ac_str</p>
			<p class=\"btn_line\">
		{$_lang['add_exp']} <strong>+$score_arr[0]</strong> 
			</p>
			<p>
				$ty_str
			</p>
	</div>";
		setcookie ( "score_log", "" );
		echo $msg = keke_tpl_class::xml_out($msg);
	}
	die ();
}