<?php defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = "article";
$views = array ("article_index", "article_list", "article_info" );
if(!isset($view)){
	$view = "article_index";
}

$tmp_arr  = get_art_cate();
$cat_arr2 = get_art_cate(359);
function get_art_cate( $pid=1 ) {
	$array = kekezu::get_table_data ( "*", "witkey_article_category", "cat_type='article' and art_cat_pid=$pid", "", "", "", "", null );
	$tmp_arr = array ();
	kekezu::get_tree ( $array, $tmp_arr, "", "", "art_cat_id", "art_cat_pid", "art_cat_name" );
	return $tmp_arr;
}
function get_art_by_year() {
	$sql2 = "select count(a.art_id) as c ,YEAR(FROM_UNIXTIME(a.pub_time)) as y from %switkey_article as a  left join %switkey_article_category as b  \n" . "on a.art_cat_id = b.art_cat_id where b.cat_type='article'\n" . "GROUP BY y";
	return  db_factory::query ( sprintf ( $sql2, TABLEPRE, TABLEPRE ), true, 5*60);
}
function get_art_list($page, $page_size, $url, $where,$static=0) {
	global $kekezu;
	$sql = "select a.* ,b.cat_name from " . TABLEPRE . "witkey_article a left join " . TABLEPRE . "witkey_article_category b on a.art_cat_id=b.art_cat_id where b.cat_type='article'  $where";
	$csql = "select count(a.art_id) as c  from " . TABLEPRE . "witkey_article a left join " . TABLEPRE . "witkey_article_category b on a.art_cat_id=b.art_cat_id where b.cat_type='article'  $where";
	$count = intval ( db_factory::get_count ( $csql,0,null, 10*60 ) );
	$kekezu->_page_obj->setStatic($static);
	$pages = $kekezu->_page_obj->getPages ( $count, $page_size, $page, $url );
	$art_arr = db_factory::query ( $sql . $pages ['where'], 5*60 );
	return array("date"=>$art_arr,"pages"=>$pages);
}
function get_cat_info ($tmp_arr,$art_cat_id) {
	$id = "artilce_list_cat_info";
	$cobj  = new keke_cache_class();
	$t_arr = $cobj->get($id);
	if(!$t_arr){
		$size = sizeof ( $tmp_arr );
		for($i = 0; $i < $size; $i ++) {
			$t_arr [$tmp_arr [$i] ['art_cat_id']] = $tmp_arr [$i];
		}
		$cobj->set($id, $t_arr,null);
	}
   return $t_arr;
}
require S_ROOT . "/control/$do/$view.php";
