<?php	defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$event_id or kekezu::admin_show_msg ( $_lang['param_error'], "index.php?do=$do&view=event",3,'','warning');
$event_id and $event_info= db_factory::get_one(" select * from ".TABLEPRE."witkey_prom_event where event_id = '$event_id'");
require $template_obj->template ( 'control/admin/tpl/admin_' . $do . '_' . $view );