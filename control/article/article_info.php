<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
/**
 * 文章模块--文章首页
 * this not free,powered by keke-tech
 * @author xl
 * @charset:GBK  last-modify 2011-12-5-上午10:37:07
 * @version V2.0
 */

$art_id = intval($art_id)+0;
$art_cat_id and $art_cat_id = intval($art_cat_id);
$year and $year = intval($year);
$art_id or kekezu::show_msg(kekezu::lang("operate_notice"),"index.php?do=article",2,kekezu::lang("test"),"warning");

$static and $pre_url = $_K['siteurl'].'/';

$sql  = "select a.* ,b.cat_name from %switkey_article as a
              left join %switkey_article_category as b  on a.art_cat_id = b.art_cat_id where a.art_id='%d'";

$art_info = db_factory::get_one(sprintf($sql,TABLEPRE,TABLEPRE,$art_id));


$where = " and 1=1";
if($year){
      $where .= " and year(from_unixtime(pub_time)) = '$year'";
} else{
    $art_cat_id and $where .= " and  art_cat_id  = $art_cat_id";
}

//获取当前文章的上一篇和下一篇文章信息
$art_up_info = db_factory::get_one(sprintf("select  art_id ,art_cat_id,art_title  from %switkey_article  where art_id<'%d'  %s order by art_id desc limit 0,1",TABLEPRE,$art_id,$where));
$art_down_info = db_factory::get_one(sprintf("select art_id ,art_cat_id,art_title  from %switkey_article  where art_id>'%d' %s order by art_id asc limit 0,1",TABLEPRE,$art_id,$where));

if(!$_COOKIE["article_".$art_id]){
	//更改浏览数
	$sqlplus = "update %switkey_article set views = views+1 where art_id = %d";
	db_factory::execute(sprintf($sqlplus,TABLEPRE,$art_id));
}
//设置cookie过期时间
setcookie("article_".$art_id,"exist_".$art_id,time()+3600*24, COOKIE_PATH, COOKIE_DOMAIN,NULL,TRUE );

$page_title=$art_info['art_title'].$art_info['seo_title'].'- '.$_K['html_title'];
$page_keyword = $art_info['seo_keyword']. '-' .$kekezu->_sys_config['seo_keyword'];
$page_description = $art_info['seo_desc']. '-' .$kekezu->_sys_config['seo_desc'];

require keke_tpl_class::template("tpl/".$_K ['template']."/".$do."/".$view);
