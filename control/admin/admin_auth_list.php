<?php defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$auth_code or kekezu::admin_show_msg ( $_lang['error_param'], "index.php?do=auth",3,'','warning');
$auth_code and require S_ROOT.'./auth/'.$auth_dir.'/control/admin/auth_list.php';