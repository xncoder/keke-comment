<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$opps=array('add','list');
in_array($opp, $opps) or $opp='list';
require "user_" . $op."_".$opp.".php" ;
