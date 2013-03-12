<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$cate_obj=new Keke_witkey_shop_cate_class();
if (isset ( $ajax ) && $ajax == "cus_cate") {
	$cate_name = kekezu::unescape($cate_name);
	$cate_obj->setCate_name ( $cate_name );
	$cate_obj->setShop_id ( $shop_info['shop_id']);
	$cate_obj->setType($shop_info['shop_type']);
	$res = $cate_obj->create_keke_witkey_shop_cate();
	if ($res) {
		$cate_obj->setWhere ( ' type = '.$shop_info['shop_type'].' and  shop_id = ' . $shop_info ['shop_id'] );
		$cus_cate_arr = $cate_obj->query_keke_witkey_shop_cate();
		$str = '<option value=\'\'>'.$_lang['please_choose'].'</option>';
		foreach ( $cus_cate_arr as $v ) {
			$str .= "<option value='" . $v ['cate_id']."'";
			if ($cate_name == $v ['cate_name']) {
				$str .= " selected='selected' ";
			}
			$str .= ">{$v['cate_name']}</option>";
		}
		$str .= '<option value="define">'.$_lang['user_defined'].'</option>';
		CHARSET=='gbk' and $str=kekezu::gbktoutf($str);
		echo $str;
	}
}