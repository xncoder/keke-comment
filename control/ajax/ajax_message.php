<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
switch ($op){
	case "send":
		if ($sbt_edit) {
			$tar_title= kekezu::escape($tar_title);
 			$tar_content = kekezu::escape($tar_content);
			keke_msg_class::send_private_message($tar_title,$tar_content,$to_uid, $to_username,'','json');
		} else{
			$title = $_lang['send_msg'];
			CHARSET=='gbk' and $to_username = kekezu::utftogbk($to_username);
			require keke_tpl_class::template ( 'message' );
		}
		die ();
		break;
}