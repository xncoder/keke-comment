<?php	defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$model_id or kekezu::admin_show_msg ( $_lang['error_model_param'], "index.php?do=info",3,'','warning' );
// 获取当前模式信息：简历模式、单人独得等，该信息在 全局配置-模型管理 中修改
$model_info = db_factory::get_one ( " select * from " . TABLEPRE . "witkey_model where model_id = '$model_id'" );
if (! $model_info ['model_status']) {
	header ( "location:index.php?do=config&view=model" );
	die ();
}
// 载入具体task的语言包
keke_lang_class::package_init ( "task_{$model_info ['model_dir']}" );
keke_lang_class::loadlang ( "admin_{$do}_{$view}" );
keke_lang_class::loadlang("task_{$view}");
keke_lang_class::package_init ( "shop" );
keke_lang_class::loadlang("{$model_info [model_dir]}_{$view}");
// model_type分为：shop + type
require S_ROOT . $model_info ['model_type'] . "/" . $model_info ['model_dir'] . "/control/admin/admin_route.php";
