<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$upload_obj=keke_ajax_upload_class::get_instance($_SERVER['QUERY_STRING']);
switch ($upload_obj->_file_type){
	case 'sys':
	case 'editor':
	case 'att':
		$upload_obj->upload_file();
		break;
	case 'big':
		$upload_obj->upload_big_file();
		break;
	case 'service':
		$upload_obj -> upload_and_resize_pic();
		break;
}