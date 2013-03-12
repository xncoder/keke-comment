<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$member_id = intval ( $member_id );
$language = $kekezu->_lang;
keke_lang_class::package_init ( $do );
$member_info = kekezu::get_user_info ( $member_id );
// e:enterprise 有6个router
$e_route_arr = array ("index", "statistic", "goods", "member", "intr", "case", "task" ); 
// 对应菜单的首字母缩写：首页、公司介绍、成员、xgrw(悬赏职位)、商品展示、成功案例、gstj(诚信档案)
$e_banner_keys = array ('index' => 'sy', 'intr' => 'gsjs', 'member' => 'qycy', 'task' => 'xgrw', 'goods' => 'spzs', 'case' => 'cgal', 'statistic' => 'gstj' );
// P:Person 有4个router
$p_route_arr = array ("index", "info", "goods", "statistic" );
// 是否开启商品展示
if($shop_open==0 || TRUE){
	unset($e_route_arr[2],$p_route_arr[2]);
}
// 是否开始xgrw
if($task_open==0){
	unset($e_route_arr[6]);
}
if($task_open==0&&$shop_open==0){
	unset($e_route_arr[1],$p_route_arr[3]);
}
$shop_obj = new Keke_witkey_shop_class ();
$shop_obj->setWhere ( "uid = " . intval ( $member_id ) );
$p_shop_info = $shop_obj->query_keke_witkey_shop ();
if (! $p_shop_info) { 
    // 横向权限验证,如果访问当前页面的用户为主用户，则跳转到空间设置页面
	$jump_url = $member_id == $uid ? 'index.php?do=user&view=setting&op=space' : 'index.php';
	kekezu::show_msg ( $_lang ['this_user_no_open_space'], $jump_url );
}
$p_shop_info = $p_shop_info ['0'];
$e_shop_info = $p_shop_info;
$banner_column = 'banner'; 
if (! $view || $view == 'index') { 
	$banner_column = 'homebanner'; 
	$e_route_arr = array_slice ( $e_route_arr, 0, 5 ); 
}
if ($e_shop_info [$banner_column]) {
	$banner_arr = unserialize ( $p_shop_info [$banner_column] );
} else {
	$banner_arr = array ('sy' => 'tpl/default/img/enterprise/banner_img.jpg', 'gsjs' => 'tpl/default/img/enterprise/banner_img.jpg', 'qycy' => 'tpl/default/img/enterprise/qycy_banner.jpg', 'xgrw' => 'tpl/default/img/enterprise/rw_banner.jpg', 'spzs' => 'tpl/default/img/enterprise/sp_banner.jpg', 'cgal' => 'tpl/default/img/enterprise/suc_banner.jpg', 'gstj' => 'tpl/default/img/enterprise/gstj_banner.jpg' );
}
if ($ac == 'up_pic') {
	$banner_keys = $e_banner_keys; 
	$img_type = $banner_keys [$view] ? $banner_keys [$view] : 'sy'; 
	$ext = '.jpg,.jpeg,.png,.gif';
	if ($sbt) {
		if ($p_shop_info ['shop_type'] != 2 || $member_id != $uid) { 
			kekezu::echojson ( $_lang ['insufficient_permissions'], '0', array ('type' => $img_type, 'file' => $file_name ) );
			die ();
		}
		if ($view == 'index' || ! $view) { 
			$banner_arr [$slide_index] = $file_name;
		} else {
			$banner_arr [$img_type] = $file_name;
		}
		$banner = serialize ( $banner_arr );
		$sql = sprintf ( "update %switkey_shop set %s='%s' where shop_id=%d", TABLEPRE, $banner_column, $banner, $e_shop_info ['shop_id'] );
		$result = db_factory::execute ( $sql );
		kekezu::echojson ( '', $result ? '1' : '0', array ('type' => $img_type, 'file' => $file_name ) );
		die ();
	} else {
		$title = $_lang ['change_the_slide'];
		require keke_tpl_class::template ( SKIN_PATH . "/space/e_uppic" );
		die ();
	}
}
// shop_type 为 2，表示为企业用户
$p_shop_info ['shop_type'] == 2 and $type = "e" or $type = "p";
// 等价于
// if($p_shop_info['shop_type'] == 2){
//      $type = "e";
// }else{
//      $type = "p";
// }
if ($e_shop_info ['shop_backstyle']) { 
	$shop_backstyle = unserialize ( $e_shop_info ['shop_backstyle'] );
}
$bgimg = "resource/img/system/img_pw.jpg";
$shop_background = file_exists($e_shop_info['shop_background'])?$e_shop_info['shop_background']:$bgimg;
if ($uid == $e_shop_info ['uid'] && $ac == 'custom') {
	switch ($t) {
		case 'style' : 
			if ($type == 'p') {
				$space_style = keke_glob_class::get_p_space_style (); 
				$style_arr = keke_glob_class::get_p_space_name (); 
			} else {
				$space_style = keke_glob_class::get_e_space_style (); 
				$style_arr = keke_glob_class::get_e_space_name (); 
			}
			if ($sbt == 1) {
				if ($space_style [$skin]) {
					$res = db_factory::execute ( sprintf ( " update %switkey_shop set shop_skin='%s' where shop_id='%d'", TABLEPRE, $skin, $e_shop_info ['shop_id'] ) );
					$res and kekezu::echojson ( '', 1 ) or kekezu::echojson ( '', 0 );
					die ();
				}
				kekezu::echojson ( '', 0 );
			}
			$skin or $skin = 'default';
			break;
		case 'bground' : 
			$bg_repeat = array ('no-repeat' => $_lang ['not_repeat'], 'repeat-x' => $_lang ['x_repeat'], 'repeat-y' => $_lang ['y_repeat'], 'repeat' => $_lang ['default'] ); 
			$bg_scroll = array ('scroll' => $_lang ['scroll'], 'fixed' => $_lang ['fixed'] ); 
			$bg_position = array ('left' => $_lang ['upper_left_corner'], 'center' => $_lang ['center'], 'right' => $_lang ['upper_right_corner'] ); 
			if ($sbt == 1) {
				$bgstyle = array ();
				array_key_exists ( strval ( $repeat ), $bg_repeat ) && $bgstyle ['repeat'] = strval ( $repeat );
				array_key_exists ( strval ( $scroll ), $bg_scroll ) && $bgstyle ['scroll'] = strval ( $scroll );
				array_key_exists ( strval ( $position ), $bg_position ) && $bgstyle ['position'] = strval ( $position ) . ' top';
				$bgstyle && $shop_backstyle = serialize ( $bgstyle );
				db_factory::execute(sprintf(" update %switkey_shop set shop_backstyle='%s' where shop_id='%d'",TABLEPRE,$shop_backstyle,$e_shop_info['shop_id']));
			}
			if ($ajax) {
				$fieldss = array ('logo', 'shop_background', 'banner' );
				if (! $fields || ! in_array ( $fields, $fieldss )) {
					kekezu::echojson ( $_lang ['fail_set'], "0" );
				}
				$fid = db_factory::get_count(sprintf(" select file_id from %switkey_file where save_name='%s'",TABLEPRE,$e_shop_info['shop_background']));
				kekezu::del_att_file($fid);
				$fields && isset ( $filePath ) and $res = db_factory::execute ( sprintf ( " update %switkey_shop set %s='%s' where shop_id='%d'", TABLEPRE, $fields, $filePath, $e_shop_info ['shop_id'] ) );
				$res and kekezu::echojson ( $_lang ['successfully_set'],1) or kekezu::echojson ( $_lang ['fail_set'],0 );
			}
			if ($rever && $rever == 'change') {
				$sql = sprintf ( "update %switkey_shop set shop_background=null where shop_id=%d", TABLEPRE, $e_shop_info ['shop_id'] );
				$result = db_factory::execute ( $sql );
				if($result){
					$fid = db_factory::get_count(sprintf(" select file_id from %switkey_file where save_name='%s'",TABLEPRE,$e_shop_info['shop_background']));
					kekezu::del_att_file($fid);
					kekezu::echojson ( $_lang ['successfully_set'], "1" );die();
				}
				kekezu::echojson ( $_lang ['fail_set'], "0" );die();
			}
			break;
	}
	require keke_tpl_class::template ( 'space/space_custom' );
	die ();
}
if($shop_backstyle){
	$shop_backstyle = implode ( ' ', array_values ( $shop_backstyle ) ); 
}
in_array ( $view, $p_shop_info ['shop_type'] == 2 ? $e_route_arr : $p_route_arr ) or $view = "index";
$ip = kekezu::get_ip ();
if ($_COOKIE ['ip'] != 1) {
	db_factory::execute ( sprintf ( " update %switkey_shop set views=views+1 where uid=%d", TABLEPRE, $member_id ) );
	setcookie ( "ip", 1, time () + 3600 * 24, COOKIE_PATH, COOKIE_DOMAIN,NULL,TRUE );
}
// 先载入space的语言文件
keke_lang_class::package_init ( "space" );
// 模板在 e/p_$view 目录下
keke_lang_class::loadlang ( "{$type}_{$view}" );
// 导航栏赋值
$p_shop_nav = array ("index" => $_lang ['p_home'],"goods" => $_lang ['goods_display'] , "statistic" => $_lang ['user_credit'], "info" => $_lang ['person_info'] );
$e_shop_nav = array ("index" => $_lang ['home'], /*"goods" => $_lang ['goods_display'], */"task" => $_lang ['relation_task'],"case" => $_lang ['success_case'],"intr" => $_lang ['company_info'], "member" => $_lang ['e_member'],    "statistic" => $_lang ['company_total'] );
// 后台没有设置开关，手工关闭
if($shop_open==0 || TRUE){
	unset($p_shop_nav['goods'],$e_shop_nav['goods']);
}
if($task_open==0){
	unset($e_shop_nav['task']);
}
if($task_open==0&&$shop_open==0){
	unset($e_shop_nav['statistic'],$p_shop_nav['statistic']);
}
$footer_load = false;
$p_url = kekezu::build_space_url($member_id);
require S_ROOT . "control/space/{$type}_{$view}.php";
