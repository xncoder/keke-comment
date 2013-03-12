<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$sect_info = kekezu::get_table_data ( "*", "witkey_member_ext", " type='sect' and uid='$member_id' ", "", "", "", "k" );
require keke_tpl_class::template(SKIN_PATH."/space/{$type}_{$view}");
