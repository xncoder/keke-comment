<?php
final class sreward_time_class extends time_base_class {
	function __construct() {
		parent::__construct ();
	}
	function validtaskstatus() {
		$this->task_hand_end ();
		$this->task_vote_end ();
		$this->task_choose_end ();
		$this->task_notice_end ();
		$this->task_agreement_sign ();
		$this->task_agreement_freeze (); 
	}
	public function task_hand_end() {
		$task_list = db_factory::query ( sprintf ( " select * from %switkey_task where task_status=2 and  sub_time < '%s' and model_id = '1' and task_union!=2", TABLEPRE, time () ) );
		if (is_array ( $task_list )) {
			foreach ( $task_list as $k => $v ) {
				$task_hand_obj = new sreward_task_class($v);
				$task_hand_obj->time_hand_end ();
			}
		}
	}
	public function task_vote_end() {
		$task_list = db_factory::query ( sprintf ( " select * from %switkey_task where task_status=4 and  sp_end_time < '%s' and model_id = '1' and task_union!=2", TABLEPRE, time () ) );
		if (is_array ( $task_list )) {
			foreach ( $task_list as $k => $v ) {
				$task_vote_obj = new sreward_task_class( $v );
				$task_vote_obj->time_vote_end ();
			}
		}
	}
	public function task_choose_end() {
		$task_list = db_factory::query ( sprintf ( " select * from %switkey_task where task_status=3 and  end_time < '%s' and model_id = '1' and task_union!=2 ", TABLEPRE, time () ) );
		if (is_array ( $task_list )) {
			foreach ( $task_list as $k => $v ) {
				$task_choose_obj = new sreward_task_class( $v );
				$task_choose_obj->time_choose_end ();
			}
		}
	}
	public function task_notice_end() {
		$task_list = db_factory::query ( sprintf ( " select * from %switkey_task where task_status=5 and  sp_end_time < '%s' and model_id = '1' and task_union!=2", TABLEPRE, time () ) );
		if (is_array ( $task_list )) {
			foreach ( $task_list as $k => $v ) {
				$task_notice_obj = new sreward_task_class( $v );
				$task_notice_obj->time_notice_end ();
			}
		}
	}
	public function task_agreement_sign() {
		global $model_list, $_K, $_lang;
		$config = unserialize ( $model_list [1] ['config'] );
		$sql = " select a.agree_id,a.agree_status,a.seller_status,a.buyer_status,a.seller_uid,a.buyer_uid,a.task_id,a.on_time,b.task_title from %switkey_agreement a left join %switkey_task b on a.task_id=b.task_id where 
				a.model_id=1 and b.task_status=6 and a.on_time<'%d' and ( a.buyer_status=1 or a.seller_status=1)";
		$agree_list = db_factory::query ( sprintf ( $sql, TABLEPRE, TABLEPRE, time () - intval ( $config ['agree_sign_time'] ) * 24 * 3600 ) );
		if (! empty ( $agree_list )) {
			$msg_obj = new keke_msg_class ();
			foreach ( $agree_list as $k => $v ) {
				$ginfo = kekezu::get_user_info ( $v ['seller_uid'] );
				$winfo = kekezu::get_user_info ( $v ['buyer_uid'] );
				$agree_obj = new sreward_task_agreement ( $v ['agree_id'] );
				$url = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=agreement&agree_id=" . $v ['agree_id'] . "\">" . $v ['agree_title'] . "</a>";
				if ($v ['seller_status'] == 1) {
					$v1 = array ($_lang ['agree_status'] => $_lang ['over_time_system_auto_sign'], $_lang ['agree_url'] => $url );
					$v2 = array ($_lang ['agree_status'] => $_lang ['agree_has_signed_please_confirm'], $_lang ['agree_url'] => $url );
					$agree_obj->set_agreement_status ( 'seller_status', 2 ); 
					db_factory::execute ( sprintf ( " update %switkey_agreement set seller_accepttime='%s' where seller_uid='%d' and agree_id ='%d'", TABLEPRE, time (), $v ['seller_uid'], $v ['agree_id'] ) );
				} elseif ($v ['buyer_status'] == 1) {
					$v1 = array ($_lang ['agree_status'] => $_lang ['agree_has_signed_please_confirm'], $_lang ['agree_url'] => $url );
					$v2 = array ($_lang ['agree_status'] => $_lang ['over_time_system_auto_sign'], $_lang ['agree_url'] => $url );
					$agree_obj->set_agreement_status ( 'buyer_status', 2 ); 
					db_factory::execute ( sprintf ( " update %switkey_agreement set buyer_accepttime='%s' where buyer_uid ='%d' and agree_id='%d'", TABLEPRE, time (), $v ['buyer_uid'], $v ['agree_id'] ) );
				}
				$agree_obj->set_agreement_status ( 'agree_status', 2 ); 
				$msg_obj->send_message ( $ginfo ['uid'], $ginfo ['username'], "agreement", $_lang ['agree_title_2'], $v1, $ginfo ['email'], $ginfo ['mobile'] );
				$msg_obj->send_message ( $winfo ['uid'], $winfo ['username'], "agreement", $_lang ['agree_title_2'], $v2, $winfo ['email'], $winfo ['mobile'] );
			}
		}
	}
	public function task_agreement_freeze() {
		global $model_list, $_K, $_lang;
		$config = unserialize ( $model_list [1] ['config'] );
		$sql = " select a.agree_id,a.agree_status,a.seller_status,a.buyer_status,a.seller_uid,a.buyer_uid,a.task_id,a.on_time,b.task_title from %switkey_agreement a left join %switkey_task b on a.task_id=b.task_id where 
				a.model_id=1 and a.seller_status>1 and a.buyer_status>1 and b.task_status=6 and a.on_time<'%d'";
		$agree_list = db_factory::query ( sprintf ( $sql, TABLEPRE, TABLEPRE, time () - intval ( $config ['agree_complete_time'] ) * 24 * 3600 ) );
		if (! empty ( $agree_list )) {
			$msg_obj = new keke_msg_class ();
			foreach ( $agree_list as $k => $v ) {
				$ginfo = kekezu::get_user_info ( $v ['seller_uid'] );
				$winfo = kekezu::get_user_info ( $v ['buyer_uid'] );
				db_factory::execute ( sprintf ( " update %switkey_task set task_status=13 where task_id='%d'", TABLEPRE, $v ['task_id'] ) );
				db_factory::execute ( sprintf ( " update %switkey_agreement set agree_status=4 where agree_id='%d'", TABLEPRE, $v ['agree_id'] ) );
				$url = "<a href=\"" . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $v ['task_id'] . "\">" . $v ['task_title'] . "</a>";
				$v1 = array ($_lang ['agree_action'] => $_lang ['agree_g_ac'], $_lang ['agree_reason'] => $_lang ['agree_freeze_reason'], $_lang ['agree_task_title'] => $url );
				$v2 = array ($_lang ['agree_action'] => $_lang ['agree_w_ac'], $_lang ['agree_reason'] => $_lang ['agree_freeze_reason'], $_lang ['agree_task_title'] => $url );
				$msg_obj->send_message ( $ginfo ['uid'], $ginfo ['username'], "task_freeze", $_lang ['agree_title_1'], $v1, $ginfo ['email'], $ginfo ['mobile'] );
				$msg_obj->send_message ( $winfo ['uid'], $winfo ['username'], "task_freeze", $_lang ['agree_title_1'], $v2, $winfo ['email'], $winfo ['mobile'] );
			}
		}
	}
}