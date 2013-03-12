<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
keke_lang_class::package_init("user");
$do and keke_lang_class::loadlang($do);
// {index:管理面板, setting:个人设置, finance:财务管理,employer:雇主, witkey:伯乐, trans:交易维权, message:信息中心,collect:收藏, payitem:增值服务}
$views = array('index','setting','finance','employer','witkey','trans','message','collect','payitem');
if($task_open==0&&$shop_open==0){
	unset($views['employer'],$views['witkey'],$views['trans']);
}
$views = array_merge($views);
!in_array($view,$views) && $view =$views[0];
// 载入各自的语言文件
($view || $op=='basic' )and keke_lang_class::loadlang("{$do}_{$view}");
$view == 'setting' and  keke_lang_class::loadlang("{$do}_{$op}");
$op  and keke_lang_class::loadlang("{$do}_{$view}_{$op}");
$_K['is_rewrite'] = 0 ;
$uid or header ( "location:index.php?do=login" ); 
$user_info=$kekezu->_userinfo;
$origin_url="index.php?do=$do&view=$view";
$page_title=$_lang['user_center'];
// 用户信息对应witkey_space表
$user_type = intval($user_info['user_type']);
// 1: 个人用户 2：企业用户
if($user_type==2){
    $nav_setting=array(
        "index"=>array($_lang['manage_tpl'],"meter"),
        "setting"=>array($_lang['company_config'],"cog"));
}else{
    $nav_setting=array(
        "index"=>array($_lang['manage_tpl'],"meter"),
        "setting"=>array($_lang['person_config'],"cog"));
}
// 为导航菜单语言赋值
$nav=array(
    "finance"=>array($_lang['finance_manage'],"chart-line2"),
    "employer"=>array($_lang['employer_buyer'],"buyer"),
    "witkey"=>array($_lang['witkey_seller'],"seller"),
    "trans"=>array($_lang['process_right'],"hand-1"),
    "message"=>array($_lang['info_center'],"sound-high"),
    "collect"=>array($_lang['my_collect'],"star-fav"),
    "payitem"=>array($_lang['add_service'],"bookmark-2"));
if($task_open==0||$shop_open==0){
    if($task_open==0){
        $nav['employer'][0]=$_lang['buyer'];
        $nav['witkey'][0]=$_lang['seller'];
    }
if($shop_open==0){
    $nav['employer'][0]=$_lang['employer'];
    $nav['witkey'][0]=$_lang['witkey'];
}
if($task_open==0&&$shop_open==0){
    unset($nav['employer'],$nav['witkey'],$nav['trans']);
}
}
$nav = array_merge($nav_setting,$nav);
// 需要认证
$user_type==1 and $w=" auth_code!='enterprise' " or ($user_type==2 and $w=" auth_code!='realname' "  or $w='');
 isset($w) and $auth_item_list = keke_auth_base_class::get_auth_item ( null, null, 1 ,$w);
 $footer_load = 1;
function item_show($item_type) {
	global $task_open, $shop_open;
	$show = true;
	if ($task_open||$shop_open) {
		switch ($item_type) {
			case 'task' :
				$task_open or $show=false;
			case 'work' :
				break;
			case 'task_service' :
				$task_open|$shop_open or $show=false;
				break;
		}
	}else{
		$show=false;
	}
	return $show;
}
require 'user/user_'.$view.'.php';
