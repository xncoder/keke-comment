<?php
 /**
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * 2011-8-31 09:58:34
 */
keke_lang_class::load_lang_class('keke_auth_realname_class');
class keke_auth_realname_class extends keke_auth_base_class{
	
	public static function get_instance($auth_code='realname') {
		static $obj = null;
		if ($obj == null) {
			$obj = new keke_auth_realname_class($auth_code);
		}
		return $obj;
	}
	
	public function __construct($auth_code='realname') {
		parent::__construct($auth_code);
		$this->_primary_key     ='realname_a_id';
		$this->_tab_obj         =keke_table_class::get_instance($this->_auth_table_name);
	}
	public static function get_auth_step($auth_step=null,$auth_info=array()){
		$step='step1';
		 if($auth_step){
			$step=$auth_step;	
		}
		elseif($auth_info){
			$auth_info['auth_status'] and $step="step3" or $step="step2";
		}
		return $step;
	}
	/**
	 * 前台申请认证项目
	 * @param $data 外部传入认证数据
	 * @param $file_name 上传文件名 **请保持与数据库字段一致的命名
	 * @param $is_jump true默认跳转页面，false 返回值
	 */
	public function add_auth($data,$file_name = '',$is_jump=true) {
		global $kekezu,$user_info,$_lang;
		
		$data=$this->format_auth_apply($data);//格式化提交数据
		if($file_name){
			$file_size = $_FILES[$file_name][size];
			if ($file_size>UPLOAD_MAXSIZE) {
				kekezu::show_msg ( $this->auth_lang().$_lang['apply_submit_fail'],$_SERVER['HTTP_REFERER'], 1, $this->auth_lang().$_lang['apply_submit_fail_for_picsize_over'], 'alert_error' );
			}else{
				$id_pic = keke_file_class::upload_file($file_name);//认证图片上传
			}
		}
		if (! $id_pic || ! $data ['id_card'] || ! $data ['realname']) {
			kekezu::show_msg ( $this->auth_lang().$_lang['apply_submit_fail'],$_SERVER['HTTP_REFERER'], 1, $this->auth_lang().$_lang['apply_submit_fail_for_info_little'], 'alert_error' );
		} 
		else {
			$id_pic and $data[$file_name]=$id_pic;
			$auth_info=$this->get_user_auth_info($user_info[uid]);
			if($auth_info){
				$success=$this->_tab_obj->save($data,array($this->_primary_key=>$auth_info[$this->_primary_key]));
				$this->set_auth_record_status($user_info['uid'], '0');
			}else{
				$success=$this->_tab_obj->save($data);
			}
		}
		if ($success) {//财务记录
			//更新实名
			db_factory::execute(sprintf(" update %switkey_space set truename='%s',idcard='%s' where uid ='%d'",TABLEPRE,$data[realname],$data[id_card],$data[uid]));
			//认证收费。产生财务记录
			$data['cash'] > 0 and keke_finance_class::cash_out ($data['uid'],$data ['cash'],$this->_auth_name, $data ['cash'], $this->_auth_name, $success );
			
			$data['start_time']==$data['end_time'] and $end_time=$data['end_time'] or $end_time=0;
			$this->add_auth_record($data['uid'], $data['username'], $this->_auth_code,$end_time);//添加进入认证记录
			if($is_jump){
				kekezu::show_msg ( $this->auth_lang().$_lang['apply_submit_success'], "index.php?do=user&view=payitem&op=auth&auth_code=realname&&auth_step=step2&ver=1#userCenter", 1, $this->auth_lang().$_lang['apply_success_and_wait_audit'],'alert_right' );
			}else{
				return true;
			}
		}
	}
}

?>