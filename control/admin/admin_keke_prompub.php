<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
kekezu::admin_check_role(136);
require $template_obj->template ( "control/admin/tpl/admin_{$do}_{$view}" );