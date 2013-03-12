<?php
keke_lang_class::load_lang_class('keke_auth_bank_class');
class keke_auth_bank_class extends keke_auth_base_class {
	public static function get_instance($auth_code='bank') {
		static $obj = null;
		if ($obj == null) {
			$obj = new keke_auth_bank_class ( $auth_code );
		}
		return $obj;
	}
	public function __construct($auth_code='bank') {
		parent::__construct ($auth_code);
		$this->_primary_key = 'bank_a_id';
		$this->_tab_obj = keke_table_class::get_instance ( $this->_auth_table_name );
	}
	public static function get_auth_step($auth_step=null,$auth_info=array()){
		$step='step1';
		if ($auth_step)
			$step = $auth_step;
		elseif ($auth_info) {
			if($auth_info ['auth_status']){
				$step = "step4";
			}else{
				$auth_info[1] and $step="step4" or $step='step3';
			}
		}
		return $step;
	}
	public function add_auth($data, $file_name = '') {
		global $kekezu,$user_info,$_lang;
		$data = $this->format_auth_apply ( $data ); 
		$data['pay_to_user_cash']='0';
		$data['user_get_cash']   ='0';
		$data['pay_time']   ='0';
		$data ['bank_name']&&$data ['bank_account'] or kekezu::show_msg ( $this->auth_lang() .$_lang['apply_submit_fail'], $_SERVER ['HTTP_REFERER'], 3, $this->auth_lang() .$_lang['apply_fail_and_info_fail'], 'warning' );
		$success=$this->_tab_obj->save($data);
		if ($success) { 
			$data ['cash'] > 0 and keke_finance_class::cash_out ( $data ['uid'], $data ['cash'], $this->_auth_name, $data ['cash'], $this->_auth_name, $success );
			$data ['start_time'] == $data ['end_time'] and $end_time = $data ['end_time'] or $end_time = 0;
			$this->add_auth_record ( $data ['uid'], $data ['username'], $this->_auth_code, $end_time,$success); 
			kekezu::show_msg ( $this->auth_lang() . $_lang['apply_submit_success'], "index.php?do=user&view=payitem&op=auth&auth_code=bank&ver=1&auth_step=step3&show_id=".$success."#userCenter", 1, $this->auth_lang() . $_lang['apply_success_and_wait_audit'] ,'alert_right');
		}
	}
	public function auth_confirm($auth_info,$user_get_cash){
		global $user_info,$kekezu,$_lang;
		$uid=$user_info['uid'];
		$username=$user_info['username'];
		$auth_id=$auth_info[$this->_primary_key];
		$ac_url="index.php?do=user&view=payitem&op=auth&auth_code=".$this->_auth_code."&ver=1&show_id=".$auth_id."#userCenter";
		$user_get_cash or kekezu::show_msg($_lang['input_your_get_cash'],$ac_url,3,"",'warning');
		$data['user_get_cash']=$user_get_cash;
		$pk[$this->_primary_key]=$auth_id;
		$this->_tab_obj->save($data,$pk);
		$pay_cash = $auth_info['pay_to_user_cash'];
		if (floatval ( $user_get_cash ) ==$pay_cash&&$pay_cash!=0.00) {
			$res=$this->set_auth_status($auth_id,'1');
			$this->set_auth_record_status($uid,'1');
			if ($res) {
				$kekezu->init_prom();
				$kekezu->_prom_obj->dispose_prom_event($this->_auth_name,$uid,$uid);
				 $feed_arr = array(	
			 		"feed_username"=>array("content"=>$username,"url"=>"index.php?do=space&member_id=$uid"),
					"action"=>array("content"=>$_lang['have_passed'],"url"=>""),
					"event"=>array("content"=>$this->auth_lang(),"url"=>"")
			 	);
				kekezu::save_feed($feed_arr, $uid, $username,$this->_auth_name );
				$v_arr = array($_lang['auth_code']=>$this->auth_lang(),$_lang['auth_url']=>$ac_url);
				keke_msg_class::notify_user($uid, $username, 'auth_success',$this->auth_lang().$_lang['success'],$v_arr );
				kekezu::show_msg ( $this->auth_lang().$_lang['success'], "index.php?do=user&view=payitem&op=auth&auth_code=".$this->_auth_code."&ver=1&auth_step=step4&show_id=".$auth_id."#userCenter", 1, $this->auth_lang().$_lang['success'],'alert_right' );
			}
		} else {
			$res=$this->set_auth_status($auth_id,'2');
			$this->set_auth_record_status($uid,'2');
			$v_arr = array($_lang['auth_code']=>$this->auth_lang(),$_lang['auth_url']=>$ac_url);
			keke_msg_class::notify_user($uid, $username, 'auth_fail',$this->auth_lang().$_lang['fail'],$v_arr );
			kekezu::show_msg ( $this->auth_lang().$_lang['fail'], $ac_url, 1, $_lang['input_cash_not_equal_admin_cash'].$this->auth_lang().$_lang['input_cash_not_equal_admin_cash'], 'alert_error' );
		}
	}
}