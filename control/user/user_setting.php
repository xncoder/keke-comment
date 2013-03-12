<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$ops = array ('basic', 'picture','credit', 'safe','fina_account', 'account_bind','space');
in_array ( $op, $ops ) or $op = "basic";
$sub_nav =array(
	 array (
			"basic" => array ( $_lang['basics'], "contact-card" ),
	 		"picture" => array ( $_lang['head_icon_setting'], "picture" ),
	 ),
	 array (
			"safe" => array ( $_lang['safe_set'], "shield" )
	 ),
	 array (
			"account_bind" => array ( $_lang['accoutn_bind'], "openid" ),
	 ),
	 array (
			"space" => array ($_lang['space_set'], "browser" )
	 )
	 );
require 'user_' . $op . '.php';
