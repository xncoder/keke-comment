<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
if(intval($art_id)){
	$art_info = db_factory::get_one(sprintf("select * from %switkey_article where art_id='%d'",TABLEPRE,$art_id));
	$art_up_info = db_factory::get_one(sprintf("select  art_id ,art_cat_id,art_title  from %switkey_article  where art_id<'%d' and cat_type='".$art_info['cat_type']."'  %s order by art_id desc limit 0,1",TABLEPRE,$art_id,$where));
	$art_down_info = db_factory::get_one(sprintf("select art_id ,art_cat_id,art_title  from %switkey_article  where art_id>'%d'  and cat_type='".$art_info['cat_type']."' %s order by art_id asc limit 0,1",TABLEPRE,$art_id,$where));
	if(!$_COOKIE["article_".$art_id]){
		$sqlplus = "update %switkey_article set views = views+1 where art_id = %d";
		db_factory::execute(sprintf($sqlplus,TABLEPRE,$art_id));
	}
	setcookie("article_".$art_id,"exist_".$art_id,time()+3600*24, COOKIE_PATH, COOKIE_DOMAIN,NULL,TRUE );
	$page_title=$art_info['art_title'].$art_info['seo_title'].'- '.$_K['html_title'];
	$page_keyword = $art_info['seo_keyword'];
	$page_description = $art_info['seo_desc'];
}else{
	kekezu::show_msg(kekezu::lang("operate_notice"),"index.php",2,"系统繁忙，找不到您要的内容","warning");
}
require keke_tpl_class::template('single');