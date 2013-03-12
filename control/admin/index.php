<?php
// 定义两个宏，在 keke_base_class 中会检查
define ( "ADMIN_KEKE", TRUE );
define ( "IN_KEKE", TRUE );
// 完成预处理 -- 获得用户请求的参数，放在$_R中，包含核心函数
require '../../app_comm.php';

$_K ['is_rewrite'] = 0;
define ( 'ADMIN_ROOT', S_ROOT . './control/admin/' ); 
$_K ['admin_tpl_path'] = S_ROOT . './control/admin/tpl/'; 
// 允许的动作：do
$dos = array ('static','preview','database_manage','permission','prom', 'main', 'side', 'menu', 'tpl', 'index', 'config', 'article',  'art_cat', 'edit_art_cat', 'finance', 'task', 'model', 'tool', 'user', 'login', 'logout', 'button_a', 'user_integration', 'score_config', 'score_rule', 'mark_config', 'mark_rule', 'mark_addico', 'mark_mangeico', 'auth',  'shop', 'group', 'rule', 'case', 'relation_info','nav','msg','trans','keke','payitem');
// 技巧：通过逻辑运算可以减少一些 if 语句的使用
(! empty ( $do ) && in_array ( $do, $dos )) or $do = 'index';
$admin_info = kekezu::get_user_info ( $_SESSION ['uid'] );
if($do != 'login' && $do != 'logout'){
	if(! $_SESSION ['auid'] || ! $_SESSION ['uid'] || $admin_info ['group_id'] == 0){
		echo "<script>window.parent.location.href='index.php?do=login';</script>";
		die();
	}
}
keke_lang_class::package_init("admin");
// 载入admin专用的语言模板 lang/cn/admin/admin_msg.php
// 该文件不存在，所以载入失败，跳过
keke_lang_class::loadlang("admin_$do");
$kekezu->init_lang();
// 载入 lang\cn\admin\admin_msg_{$view}.php
$view and 	keke_lang_class::loadlang("admin_{$do}_$view");
$op and keke_lang_class::loadlang("admin_{$do}_{$view}_{$op}");
keke_lang_class::loadlang("admin_screen_lock");
$language      = $kekezu->_lang;
$menu_arr = array (
'config' => $_lang['global_config'], 
'article' => $_lang['article_manage'],
'task' => $_lang['task_manage'], 
'shop' => $_lang['shop_manage'],
'finance' => $_lang['finance_manage'], 
'user' => $_lang['user_manage'], 
'tool' => $_lang['system_tool'],
'keke'=>$_lang['witkey_union'],
		);
$admin_obj=new keke_admin_class();
// control\admin\admin_msg.php
require ADMIN_ROOT . 'admin_' . $do . '.php';
?>
