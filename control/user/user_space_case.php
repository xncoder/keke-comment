<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$shows = array("list","add");
in_array($show,$shows) or $show="list";
switch ($show){
	case "add":
		$indus_arr = kekezu::get_indus_by_index ( 1 ); 
		$indus_p_arr=$kekezu->_indus_p_arr;
		$indus_c_arr=$kekezu->_indus_c_arr;
		if($sbt_action){
			$case_obj=keke_table_class::get_instance("witkey_shop_case");
			$conf['start_time']=strtotime($conf['start_time']);
			$conf['end_time']=strtotime($conf['end_time']);
			$conf['shop_id']  =$shop_info['shop_id'];
			if($_FILES['case_pic']['name']){
				$case_pic = keke_file_class::upload_file('case_pic');
				$case_pic&&$conf['case_pic']=$case_pic;
			}
			$res=$case_obj->save($conf,$pk);
			$res and kekezu::show_msg($_lang['case_operate_success'],$ac_url."&show=list#userCenter",3,'','success') or kekezu::show_msg($_lang['case_operate_fail'],$ac_url."&show=add&case_id=$case_id#userCenter",3,'','warning');
		}else{
			$case_id and $case_info=db_factory::get_one(sprintf(" select * from %switkey_shop_case where case_id='%d'",TABLEPRE,$case_id));
		}
		switch ($ac){
			case "show_indus":
				$str_html="<option value=''>".$_lang['please_select']."</option>";
				if ($indus_pid && $indus_arr [$indus_pid])
					foreach ($indus_arr [$indus_pid] as $v) {
						isset($indus_id)&&$indus_id==$v['indus_id'] and $selected=" selected " or $selected="";
						$str_html .="<option value='".$v['indus_id']."' ".$selected.">".$v['indus_name']."</option>";
					}
				echo $str_html;
				die ();
				break;
			case "show_service":
				$service_list=db_factory::query(sprintf(" select * from %switkey_service where shop_id='%d'",TABLEPRE,$shop_info['shop_id']));
				$str_html="<option value=''>".$_lang['please_select']."</option>";
				foreach ($service_list as $v) {
					$case_info['service_id']==$v['service_id'] and $selected=" selected " or $selected="";
					$str_html .="<option value='".$v['service_id']."' ".$selected.">".strip_tags($v['title'])."</option>";
				}
				echo $str_html;
				die ();
				break;
		}
		break;
	case "list":
		if($ac=='del'){
			$res=db_factory::execute(sprintf(" delete from %switkey_shop_case where case_id='%d'",TABLEPRE,$case_id));
			$res and kekezu::echojson($_lang['delete_success'],"1") or kekezu::echojson($_lang['delete_fail'],"0");
			die();
		}else{
			$indus_c_arr=$kekezu->_indus_c_arr;
			$case_obj=new Keke_witkey_shop_case_class();
			$page_obj=$kekezu->_page_obj;
			$where=" shop_id='{$shop_info['shop_id']}' order by on_time desc ";
			intval($page) or $page='1';
			intval($page_size) or $page_size='4';
			$url=$ac_url."&show=list&page_size=$page_size&page=$page";
			$case_obj->setWhere($where);
			$count=intval($case_obj->count_keke_witkey_shop_case());
			$pages=$page_obj->getPages($count, $page_size, $page, $url,'#userCenter');
			$case_obj->setWhere($where.$pages['where']);
			$case_list=$case_obj->query_keke_witkey_shop_case();
		}
		break;
}
require keke_tpl_class::template ("user/" . $do ."_".$op. "_" . $opp );
