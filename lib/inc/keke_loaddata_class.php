<?php
keke_lang_class::load_lang_class('keke_loaddata_class');
class keke_loaddata_class {
	static function readfeed($loadcount, $type = '', $uid = '', $objid = '', $templatename = "", $cachename = "", $cachetime = 300) {
		global $kekezu,$_lang;
		$tag_arr = $kekezu->_tag;
		$tag_info = $tag_arr [$templatename];
		$feed_arr = $cachename ? $kekezu->_cache_obj->get ( "feed_" . $cachename . "_cache" ) : null;
		if (! $feed_arr) {
			$feed_obj = new Keke_witkey_feed_class ();
			$limit = $loadcount ? "limit 0,$loadcount" : "";
			$where = "1=1 ";
			$where .= $type ? "and feedtype='$type' " : "";
			$where .= $uid ? "and uid='$uid' " : "";
			$where .= $objid ? "and obj_id='$objid' " : "";
			$where .= " order by feed_time desc ";
			$feed_obj->setWhere ( $where . $limit );
			$feed_arr = $feed_obj->query_keke_witkey_feed ();
			$temp_arr = array ();
			if (is_array ( $feed_arr )) {
				foreach ( $feed_arr as $v ) {
					$v ['on_time'] = kekezu::get_gmdate ( $v ['feed_time'] );
					$temp_arr [] = $v;
				}
			}
			$feed_arr = $temp_arr;
			$cachename ? $kekezu->_cache_obj->set ( "feed_" . $cachename . "_cache", $feed_arr, $cachetime ) : null;
		}
		$datalist = $feed_arr;
		require keke_tpl_class::parse_code ( htmlspecialchars_decode ( $tag_info [tag_code] ), $tag_info [tag_id] );
	}
	static function readtag($name) { 
		global $kekezu,$_lang;
        $kekezu->_tag or $kekezu->init_tag(); 
		$tag_arr = $kekezu->_tag; 
 		$tag_info = $tag_arr [$name];
	    if ($tag_info ['tag_type'] == 5) {
			echo htmlspecialchars_decode ( $tag_info ['code'] );
		}else{
	 		if ($tag_info ['cache_time']) { 
				$cid = 'db_tag_' . $tag_info ['tag_id'];
				$datalist = $kekezu->_cache_obj->get ( $cid );
				if (! $datalist) {
					$datalist = keke_loaddata_class::loaddata ( $tag_info );
					$kekezu->_cache_obj->set ( $cid, $datalist, $tag_info ['cache_time'] );
				}
				require keke_tpl_class::parse_code ( htmlspecialchars_decode ( $tag_info [tag_code] ), $tag_info [tag_id] );
			} else if ($tag_info) { 
				keke_loaddata_class::previewtag ( $tag_info );
			} else {
				echo $_lang['tag'] . $name . $_lang['not_found'];
			}
		}
	}
	static function gettagHTML($tagid) {
		global $_K, $kekezu; 
		$url = $_K ['siteurl'] . "/control/admin/plu.php?do=previewtag&tagid=" . $tagid; 
		if (function_exists ( "curl_init" )) { 
			$content = kekezu::curl_request($url) ; 
		} else {
			$content = file_get_contents ( $url ); 
		} 
		return $content;
	}
	static function previewtag($tag_info) {
		$datalist = keke_loaddata_class::loaddata ( $tag_info );
		if ($tag_info ['tag_type'] == 5) {
			echo htmlspecialchars_decode ( $tag_info ['code'] );
		} else {
			require keke_tpl_class::parse_code ( htmlspecialchars_decode ( $tag_info [tag_code] ), $tag_info [tag_id] );
		}
	}
	static function preview_feed($tag_info) {
		if ($tag_info) {
			$code = unserialize ( $tag_info [code] );
			$tag_info = array_merge ( $tag_info, $code );
			extract ( $tag_info );
		}
		self::readfeed ( $load_num, $feed_type, $user_id, $obj_id, $tagname, $cache_name, $cache_time );
	}
	static function preview_addgroup($adname,$loadcount) {
		self::adgroup ( $adname,$loadcount);
	}
	static function loaddata($tag_info) {
		global $_K;
		$tag_type = keke_glob_class::get_tag_type ();
		if ($tag_info [tag_type] != 5) {
			$func_name = "load_" . $tag_type [$tag_info ['tag_type']] [2] . "_data";
			$temp_arr = self::$func_name ( $tag_info );
			return $temp_arr;
		}
	}
	static function load_service_data($tag_info) {
		global $_K,$_lang;
		$service_obj = new Keke_witkey_service_class ();
		$model_id = intval($tag_info ['code']);
		$where = ' 1 = 1 and model_id='.$model_id;
		$where .= " order by on_time desc ";
		if ($tag_info ['loadcount']) {
			$where .= " limit 0," . $tag_info ['loadcount'];
		}
		$service_obj->setWhere ( $where );
		$service_arr = $service_obj->query_keke_witkey_service ();
		$temp_arr = array ();
		foreach ( $service_arr as $v ) {
			$a = array ();
			$a ['sid'] = $v ['service_id'];
			$a ['title'] = $v ['title'];
			$a ['uid'] = $v ['uid'];
			$a ['username'] = $v ['username'];
			$a ['content'] = $v ['content'];
			$a ['on_time'] = $v ['on_time'];
			$a ['pic'] = $v ['pic'];
			$a ['url'] = $_K ['siteurl'] . "/index.php?do=servide&sid=" . $a ['sid'];
			$temp_arr [] = $a;
		}
		return $temp_arr;
	}
	static function load_autosql_data($tag_info) {
		$sql = stripslashes ( $tag_info ['tag_sql'] );
		$temp_arr = db_factory::query ( $sql );
		return $temp_arr;
	}
	static function load_category_data($tag_info) {
		global $_K,$_lang;
		$cat_obj = null;
		if ($tag_info ['cat_type'] == 2) {
			$cat_obj = new Keke_witkey_article_category_class ();
			$where = "1=1 ";
			$where .= $tag_info ['cat_cat_ids'] ? "and art_cat_id in ({$tag_info['cat_cat_ids']}) " : $tag_info ['cat_cat_ids'] ? "and art_cat_id in ({$tag_info['cat_cat_ids']}) " : "";
			if ($tag_info ['cat_loadsub']) {
				$where .= $tag_info ['cat_cat_ids'] ? "and art_cat_pid in ({$tag_info['cat_cat_ids']}) " : $tag_info ['cat_cat_ids'] ? "and art_cat_pid = '{$tag_info['cat_cat_id']}' " : "";
			}
			$where .= $tag_info ['cat_onlyrecommend'] ? "and is_show = 1 " : "";
			$where .= " order by listorder ";
			if ($tag_info ['loadcount']) {
				$where .= "limit 0,{$tag_info['loadcount']} ";
			}
			$cat_obj->setWhere ( $where );
			$cat_arr = $cat_obj->query_keke_witkey_article_category ();
			$temp_arr = array ();
			foreach ( $cat_arr as $v ) {
				$a = array ();
				$a ['id']   = $v['art_id'];
				$a ['cat_id'] 	= $v ['art_cat_id'];
				$a ['name'] = $v ['cat_name'];
				$a ['url']  = $_K ['siteurl'] . "/index.php?do=article&view=article_info&art_id=".$v['id']."&art_cat_id=" . $a ['cat_id'];
				$temp_arr [] = $a;
			}
		} else {
			$cat_obj = new Keke_witkey_industry_class ();
			$where = "1=1 ";
			$where .= $tag_info ['cat_cat_ids'] ? "and indus_id in ({$tag_info['cat_cat_ids']})" : $tag_info ['cat_cat_ids'] ? "and indus_id in ({$tag_info['cat_cat_ids']})" : "";
			if ($tag_info ['cat_loadsub']) {
				$where .= $tag_info ['cat_cat_ids'] ? "and indus_pid in ({$tag_info['cat_cat_ids']})" : $tag_info ['cat_cat_ids'] ? "and indus_pid = '{$tag_info['cat_cat_id']}'" : "";
			}
			$where .= $tag_info ['cat_onlyrecommend'] ? "and is_recommend = 1 " : "";
			$where .= " order by listorder ";
			if ($tag_info ['loadcount']) {
				$where .= "limit 0,{$tag_info['loadcount']} ";
			}
			$cat_obj->setWhere ( $where );
			$cat_arr = $cat_obj->query_keke_witkey_industry (true, 5*60);
			$temp_arr = array ();
			foreach ( $cat_arr as $v ) {
				$a = array ();
				$a ['indus_id'] = $v ['indus_id'];
				$a ['indus_pid'] = $v ['indus_pid'];
				$a ['name'] = $v ['indus_name'];
				$a ['intro'] = $v ['indus_intro'];
				$a ['url'] = $_K ['siteurl'] . "/index.php?do=indus&indus_id=" . $a ['id'];
				$temp_arr [] = $a;
			}
		}
		return $temp_arr;
	}
	static function load_task_data($tag_info) {
		global $_K,$_lang;
		$task_obj = new Keke_witkey_task_class ();
		$where = "1=1 ";
		if ($tag_info ['task_ids']) {
			$where .= "and task_id in ({$tag_info['task_ids']})";
		} else {
			if ($tag_info ['task_type']) {
				$where .= "and model_id = '{$tag_info['task_type']}' ";
			}
			if ($tag_info ['task_indus_ids']) {
				$where .= "and indus_id in ({$tag_info['task_indus_ids']}) ";
			} else if ($tag_info ['task_indus_id']) {
				$indus_index = kekezu::get_indus_by_index ( 1, $tag_info ['task_indus_id'] );
				$indus_index = $indus_index [$tag_info ['task_indus_id']];
				$ind_str = $tag_info ['task_indus_id'];
				foreach ( $indus_index as $ind ) {
					$ind_str .= $ind_str ? "," : "";
					$ind_str .= $ind ['indus_id'];
				}
				$where .= "and indus_id in ($ind_str) ";
			}
			if ($tag_info ['task_status']) {
				$where .= "and task_status = '{$tag_info['task_status']}' ";
			} else {
				$where .= "and task_status in (2,3,4,8) ";
			}
			if ($tag_info ['start_time1']) {
				$where .= "and start_time >{$tag_info['start_time1']} ";
			}
			if ($tag_info ['start_time2']) {
				$where .= "and start_time <{$tag_info['start_time2']} ";
			}
			if ($tag_info ['end_time1']) {
				$where .= "and end_time >{$tag_info['end_time1']} ";
			}
			if ($tag_info ['end_time2']) {
				$where .= "and end_time <{$tag_info['end_time2']} ";
			}
			$lefttime = 0;
			if ($tag_info ['left_day']) {
				$lefttime += $tag_info ['left_day'] * 24 * 60 * 60;
			}
			if ($tag_info ['left_hour']) {
				$lefttime += $tag_info ['left_hour'] * 60 * 60;
			}
			if ($lefttime) {
				$where .= "and end_time-" . time () . "<{$lefttime} ";
			}
			if ($tag_info ['task_cash1']) {
				$where .= "and task_cash >{$tag_info['task_cash1']} ";
			}
			if ($tag_info ['task_cash2']) {
				$where .= "and task_cash <{$tag_info['task_cash2']} ";
			}
			if ($tag_info ['prom_cash1']) {
				$where .= "and prom_count >={$tag_info['prom_cash1']} ";
			}
			if ($tag_info ['prom_cash2']) {
				$where .= "and prom_count <={$tag_info['prom_cash2']} ";
			}
			if ($tag_info ['username']) {
				$where .= "and username = '{$tag_info['username']}' ";
			}
		}
		$where .= $tag_info ['open_is_top'] ? "order by istop desc," : "order by ";
		switch ($tag_info ['listorder']) {
			case 1 :
			default :
				$where .= "task_id desc ";
				break;
			case 2 :
				$where .= "task_id asc ";
				break;
			case 3 :
				$where .= "task_cash desc ";
				break;
			case 4 :
				$where .= "task_cash asc ";
				break;
			case 5 :
				$where .= "prom_cash desc ";
				break;
			case 6 :
				$where .= "prom_cash asc ";
				break;
			case 7 :
				$where .= "start_time desc ";
				break;
			case 8 :
				$where .= "start_time asc ";
				break;
			case 9 :
				$where .= "end_time desc ";
				break;
			case 10 :
				$where .= "end_time asc ";
				break;
		}
		if ($tag_info ['loadcount']) {
			$where .= "limit 0,{$tag_info['loadcount']} ";
		}
		$task_obj->setWhere ( $where );
		$task_arr = $task_obj->query_keke_witkey_task ();
		$temp_arr = array ();
		$task_cash_rule = kekezu::get_config_rule ( "witkey_task_cash_cove" );
		if (empty ( $task_arr )) {
			return false;
		}
		foreach ( $task_arr as $v ) {
			$a = array ();
			$a ['id'] = $v ['task_id'];
			$a ['status'] = $v ['task_status'];
			$a ['title'] = $v ['task_title'];
			$a ['indus_id'] = $v ['indus_id'];
			$a ['indus_pid'] = $v ['indus_pid'];
			$a ['uid'] = $v ['uid'];
			$a ['username'] = $v ['username'];
			$a ['starttime'] = $v ['start_time'];
			$a ['endtime'] = $v ['end_time'];
			$a ['cash'] = $v ['task_cash_coverage'] ? $task_cash_rule [$v ['task_cash_coverage']] ['start_cove'] . '-' . $task_cash_rule [$v ['task_cash_coverage']] ['end_cove'] : $v ['task_cash'];
			if ($a ['type'] == 1) {
				$a ['url'] = $_K ['siteurl'] . "/index.php?do=task&task_id=" . $a ['id'];
			} else {
				$a ['url'] = $_K ['siteurl'] . "/index.php?do=task&task_id=" . $a ['id'];
			}
			$a ['time'] = $v ['pub_time'];
			$temp_arr [] = $a;
		}
		return $temp_arr;
	}
	static function load_article_data($tag_info) {
		global $_K;
		$art_obj = new Keke_witkey_article_class ();
		$where = "1=1 ";
		if ($tag_info ['art_ids']) {
			$where .= "and art_id in ({$tag_info['art_ids']}) ";
		} else {
			if ($tag_info ['art_cat_ids']) {
				$where .= "and art_cat_id in ({$tag_info['art_cat_ids']}) ";
			} else if ($tag_info ['art_cat_id']) {
				$where .= "and (art_cat_id = '{$tag_info['art_cat_id']}' or art_cat_id in (select art_cat_id from " . TABLEPRE . "witkey_article_category" . " where art_cat_pid like '%{{$tag_info['art_cat_id']}}%')) ";
			}
			if ($tag_info ['art_time1']) {
				$where .= "and pub_time <{$tag_info['art_time1']} ";
			}
			if ($tag_info ['art_time2']) {
				$where .= "and pub_time >{$tag_info['art_time2']} ";
			}
			if ($tag_info ['art_hasimg']) {
				$where .= "and art_pic !='' ";
			}
			if ($tag_info ['art_iscommend']) {
				$where .= "and is_recommend=1 ";
			}
		}
		switch ($tag_info ['listorder']) {
			case 1 :
			default :
				$where .= "order by art_id desc ";
				break;
			case 2 :
				$where .= "order by art_id asc ";
				break;
			case 3 :
				$where .= "order by pub_time desc ";
				break;
			case 4 :
				$where .= "order by pub_time asc ";
				break;
		}
		if ($tag_info ['loadcount']) {
			$where .= "limit 0,{$tag_info['loadcount']} ";
		}
		$art_obj->setWhere ( $where );
		$art_arr = $art_obj->query_keke_witkey_article (true, 5*60);
		$temp_arr = array ();
		$cat_arr = keke_admin_class::get_article_cate ();
		foreach ( $art_arr as $v ) {
			$a = array ();
			$a ['id'] = $v ['art_id'];
			$a ['catid'] = $v ['art_cat_id'];
			$a ['catname'] = $cat_arr [$v ['art_cat_id']] ['cat_name'];
			$a ['uid'] = $v [uid];
			$a ['username'] = $v ['username'];
			$a ['title'] = $v ['art_title'];
			$a ['content'] = htmlspecialchars_decode ( $v ['content'] );
			$a ['pic'] = $v ['art_pic'];
			$a ['time'] = $v ['pub_time'];
			$a ['url'] = $_K ['siteurl'] . "/index.php?do=article&view=article_info&art_cat_id=".$v['catid']."&art_id=" . $a ['id'];
			$temp_arr [] = $a;
		}
		return $temp_arr;
	}
	static function ad_show($code, $do = 'index',$is_slide=null) {
		global $_lang,$_K;
		$ad_target = db_factory::get_one ( sprintf ( " select * from %switkey_ad_target where code='%s' and is_allow=1", TABLEPRE, $code ),3600*24);
			if ($ad_target) {
			if(intval($is_slide)){
			   return self::get_adgroup_by_target($ad_target['target_id'],$ad_target['name'],$ad_target ['ad_num']);
			}
			$sql = " select a.ad_id,a.ad_name,a.ad_file,a.ad_content,a.ad_url,a.width,a.height,
			a.ad_type,a.ad_position,a.on_time from %switkey_ad a left join %switkey_ad_target b on a.target_id=b.target_id 
			where b.code='%s' and a.is_allow='1' order by a.ad_id desc limit 0,%d";
			$ad_info = db_factory::get_one( sprintf ( $sql, TABLEPRE, TABLEPRE, $code, $ad_target ['ad_num'] ),true,$_K['timespan'] );
			if($ad_info){
					$ad_str .= "<div class='adv'>";
					switch ($ad_info ['ad_type']) {
						case "flash" :
							$ad_str.=keke_file_class::flash_codeout($ad_info ['ad_file'], $ad_info ['width'], $ad_info ['height']);
							break;
						case "text" :
						case "code" :
							$ad_str .= kekezu::k_stripslashes($ad_info ['ad_content']);
							break;
						case "image" :
							$ad_str .= "<a href='" . $ad_info ['ad_url'] . "' target='_blank' title='".$ad_info['ad_name']."'><img src='" . $ad_info ['ad_file']
									 . "' width='".$ad_info ['width']."' height='".$ad_info ['height']."' alt='".$ad_info['ad_name']."' title='".$ad_info['ad_name']."'></a>";
							break;
					}
					self::update_ad($ad_info);
					$ad_str .= "</div>";
				} 
			}
			echo $ad_str;
		}
	static function update_ad($ad_info){
	   global $_K,$kekezu;
	     if((SYS_START_TIME - intval($ad_info['on_time']))>$_K['timespan']){
	      db_factory::execute(sprintf('update %switkey_ad set on_time  = %s where ad_id = %d',TABLEPRE,time()+$_K['timespan'],$ad_info['ad_id']));   
	      db_factory::execute(sprintf("update %switkey_ad set is_allow='0' where ad_id='%d' and  end_time>0 and end_time<%d",TABLEPRE,$ad_info['ad_id'],time()));	
	   }
	}
	static function ad($adid) {
		$ad_arr = kekezu::get_ad ();
		$size = sizeof ( $ad_arr );
		$temp = array ();
		for($i = 0; $i < $size; $i ++) {
			$temp [$ad_arr [$i] ['ad_id']] = $ad_arr [$i];
		}
		$ad_arr = $temp;
		unset ( $temp );
		$ad_info = $ad_arr [$adid];
		if ($ad_info ['ad_type'] == 1) {
			$adstr = '<embed src="' . $ad_info ['ad_file'] . '" quality="high" width="' . $ad_info ['width'] . '" height="' . $ad_info ['height'] . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>';
		} elseif ($ad_info ['ad_type'] == 3) {
			$adstr = htmlspecialchars_decode ( $ad_info ['ad_content'] );
		} else {
			$adstr = '<img src="' . $ad_info ['ad_file'] . '" ';
			$adstr .= $ad_info ['width'] ? "width={$ad_info['width']} " : '';
			$adstr .= $ad_info ['height'] ? "height={$ad_info['height']} " : '';
			$adstr .= '>';
			if ($ad_info ['ad_url']) {
				$adstr = '<a target="_blank" href="' . $ad_info ['ad_url'] . '">' . $adstr . '</a>';
			}
		}
		echo $adstr;
	}
	static function adgroup($adname,$ad_limit_num) {
		global $kekezu,$_K;
		$kekezu->_tag or $kekezu->init_tag();
		$datalist = kekezu::get_ad ( $adname,$ad_limit_num );
		$tag_arr = $kekezu->_tag;
		$tag_info = $tag_arr [$adname];
		require keke_tpl_class::parse_code ( htmlspecialchars_decode ( $tag_info ['tag_code'] ), $tag_info ['tag_id'] );
	}
	static function get_adgroup_by_target($target_id,$target_name,$ad_list_num){
		global $kekezu,$_K;
		$kekezu->_tag or $kekezu->init_tag();
		$datalist = kekezu::get_table_data ( '*', 'witkey_ad', '1=1 and is_allow=1 and target_id = '.intval($target_id), 'listorder', '', $ad_list_num, '', 3600 );
		$tag_arr = $kekezu->_tag;
		$tag_info = $tag_arr [$target_name];
		require keke_tpl_class::parse_code ( htmlspecialchars_decode ( $tag_info ['tag_code'] ), $tag_info ['tag_id'] );
	}
}
?>