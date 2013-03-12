<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$shows = array("list","add");
in_array($show,$shows) or $show="list";
switch ($show){
	case "add":
		$entry_age=array($_lang['primary_edu'],
						 $_lang['middle_edu'],
						 $_lang['secondary_edu'],
						 $_lang['high_school_edu'],
						 $_lang['college_edu'],
						 $_lang['undergraduate_edu'],
						 $_lang['graduate_edu'],
						 $_lang['master_edu'],
						 $_lang['doctor_edu']
				  );
		if($sbt_action){
			$member_obj=keke_table_class::get_instance("witkey_shop_member");
			if($_FILES['member_pic']['name']){
				$member_pic = keke_file_class::upload_file('member_pic');
				$member_pic&&$conf['member_pic']= $member_pic;
			}
			$conf['entry_age']=strtotime($conf['entry_age']);
			$conf['shop_id']  =$shop_info['shop_id'];
			$res=$member_obj->save($conf,$pk);
			$res and kekezu::show_msg($_lang['members_operation_success'],$ac_url."&show=list#userCenter",3,'','success') or kekezu::show_msg( $_lang['members_operation_fail'],$ac_url."&show=add&member_id=$member_id#userCenter",3,'','warning');
		}else{
			$member_id and $member_info=db_factory::get_one(sprintf(" select * from %switkey_shop_member where member_id='%d'",TABLEPRE,$member_id));
		}
		break;
	case "list":
		if($ac=='del'){
			$res=db_factory::execute(sprintf(" delete from %switkey_shop_member where member_id='%d'",TABLEPRE,$member_id));
			$res and kekezu::echojson( $_lang['delete_success'],"1") or kekezu::echojson( $_lang['delete_fail'],"0");
			die();
		}else{
			$member_obj=new Keke_witkey_shop_member_class();
			$page_obj=$kekezu->_page_obj;
			$where=" shop_id='{$shop_info['shop_id']}' order by member_id desc ";
			intval($page) or $page='1';
			intval($page_size) or $page_size='4';
			$url=$ac_url."&show=list&page_size=$page_size&page=$page";
			$member_obj->setWhere($where);
			$count=intval($member_obj->count_keke_witkey_shop_member());
			$pages=$page_obj->getPages($count, $page_size, $page, $url,'#userCenter');
			$member_obj->setWhere($where.$pages['where']);
			$member_list=$member_obj->query_keke_witkey_shop_member();
		}
		break;
}
require keke_tpl_class::template ("user/" . $do ."_".$op. "_" . $opp );
