<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$str = kekezu::check_secode ( $txt_code );
echo $str;
die ();