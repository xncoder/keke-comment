<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$refer = parse_url($_SERVER['HTTP_REFERER']);
$refer_do = array('do'=>null);
isset($refer['query']) and parse_str($refer['query'],$refer_do);
!$refer_do['do']&&$do='logout' and $refer_do['do']='logout';
$_SESSION ['uid'] = '';
$_SESSION ['username'] = '';
$_SESSION['auid']="";
$_SESSION['user_info']="";
unset($uid);
unset($_SESSION);
if (isset ( $_COOKIE ['user_login'] )) {
	setcookie ( 'user_login', '' );
}
if (isset ( $_COOKIE ['prom_cke'] )) {
	setcookie ( 'prom_cke', '' );
}
$synhtml = keke_user_class::user_synlogout();
unset($_COOKIE);
unset($_COOKIE['username']);
session_destroy();
in_array($refer_do['do'],array('user','release','shop_release','logout','register_wizard')) and  $jump = 'index.php' or $jump =$_SERVER['HTTP_REFERER']; 
setcookie('rememberme','',time()-1);
kekezu::show_msg ( $_lang['info_notice'], $jump,1,$_lang['logout_success'].$synhtml,'alert_right');