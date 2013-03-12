<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
require $template_obj->template ( "control/admin/tpl/admin_{$do}_{$view}" );
