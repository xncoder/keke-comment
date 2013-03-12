<?php
/**
 * @author       Administrator
 * std_obj 为类的成员对象。用来保存release_info发布信息,att_info附加项信息
 */
keke_lang_class::load_lang_class('keke_shop_release_class');
abstract class keke_shop_release_class {
	public $_uid;
	public $_username;
	public $_user_info; //当前用户信息
	public $_kf_info; //客服信息
	

	public $_pay_item; //雇主付费项
	

	public $_priv; //发布权限获取
	

	public $_model_id; //模型编号
	public $_model_info; //模型信息
	public $_service_config; //服务配置
	public $_inited = false;
	public $_service_obj; //服务对象
	

	public $_std_obj; //成员对象，存放商品发布信息。保存入session来实现跨页面传递商品数据
	function __construct($model_id) {
		global $kekezu;
		$this->_service_obj = new Keke_witkey_service_class ();
		$this->_model_id = $model_id;
		$this->_model_info = $kekezu->_model_list [$model_id];
		$this->_std_obj = new stdClass ();
		$this->_std_obj->_release_info = array (); //商品发布信息
		$this->init ();
	
	}
	function init() {
		if (! $this->_inited) {
			$this->user_info_init ();
			$this->pay_item_init ();
			$this->get_rand_kf ();
		
		//$this->priv_init ();权限待定
		}
		$this->_inited = true;
	}
	/**
	 * 获取附加费用配置
	 * @return   void
	 */
	public function pay_item_init() {
		$this->_pay_item = keke_payitem_class::get_payitem_config ( 'employer', $this->_model_info ['model_code'] );
	}
	/**
	 * 随机抽取客服
	 * @return   void
	 */
	public function get_rand_kf() {
		$this->_kf_info = kekezu::get_rand_kf ();
	}
/**
	 * 获取任务绑定父级行业（没有则读取全行业）
	 * @return   void
	 */
	public function get_bind_indus() {
		global $kekezu;
		if ($this->_model_info ['indus_bid']) {
			$bind_indus = implode ( ',', array_filter ( explode ( ',', $this->_model_info ['indus_bid'] ) ) );
			return kekezu::get_table_data ( '*', "witkey_industry", "indus_id in (select indus_pid from " . TABLEPRE . "witkey_industry where indus_id in({$bind_indus}))", 'listorder desc', '', '', 'indus_id', null );
		} else {
			return $this->_indus_arr = $kekezu->_indus_p_arr;
		
		}
	}
	/**
	 * 初始化当前用户信息
	 */
	function user_info_init() {
		global $user_info, $uid, $username;
		$this->_user_info = $user_info;
		$this->_uid = $uid;
		$this->_username = $username;
	}
	/**
	 * 获取发布子行业信息(也可用于异步获取)
	 * @param int $indus_pid 父级行业
	 * @param string $ajax异步请求名
	 */
	public function get_service_indus($indus_pid = '', $ajax = '') {
		global $kekezu;
		global $_lang;
		if ($indus_pid > 0) {
			$indus_ids = kekezu::get_table_data ( '*', "witkey_industry", " indus_pid = $indus_pid", 'listorder desc', '', '', 'indus_id', null );
			switch ($ajax == 'show_indus') {
				case "0" :
					return $indus_ids;
					break;
				case "1" :
					$option .= '<option value=""> '.$_lang['please_choose_son_industry'].' </option>';
					foreach ( $indus_ids as $v ) {
						$option .= '<option value=' . $v [indus_id] . '>' . $v [indus_name] . '</option>';
					}
					CHARSET == 'gbk' and $option = kekezu::gbktoutf ( $option );
					echo $option;
					die ();
					break;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 商品附件入库
	 * @param $service_id 商品编号
	 * @param $title 商品标题
	 */
	function save_service_file($service_id, $title) {
		$release_info = $this->_std_obj->_release_info;
		if ($release_info ['file_ids']) {
			$file_obj = new Keke_witkey_file_class ();
			$file_arr = array_filter ( explode ( ',', $release_info ['file_ids'] ) );
			foreach ( $file_arr as $v ) {
				$file_obj->setFile_id ( $v );
				$file_obj->setUid ( $this->_uid );
				$file_obj->setUsername ( $this->_username );
				$file_obj->setObj_id ( $service_id );
				$file_obj->setTask_title ( $title );
				$file_obj->edit_keke_witkey_file ();
			}
		}
	}
	/**
	 * 发送消息    商品发布成功后提示用户
	 * @param int $service_id 商品编号
	 * @param int $service_status 商品状态 默认发布成功
	 */
	public function notify_user($service_id, $service_status = '2') {
		global $_K;
		global $_lang;
		$service_obj = $this->_service_obj;
		$model_code = $this->_model_info ['model_code'];
		switch($model_code){
			case "goods":
				$status_arr = goods_shop_class::get_goods_status (); //商品状态数组
				break;
			case "service":
				$status_arr = service_shop_class::get_service_status (); //商品状态数组
				break;
		}
		$message_obj = new keke_msg_class ();
		//$url = '<a href ="index.php?do=service&sid='.$service_id.'" target="_blank" >'. $service_obj->getTitle () .' </a>';
		$url = "<a href=\"" . $_K [siteurl] . "/index.php?do=service&sid=" . $service_id . "\">" . $service_obj->getTitle () . "</a>";
		
		$v = array ($_lang['service_type'] => $this->_model_info ['model_name'], $_lang['goods_link'] => $url, $_lang['goods_status'] => $status_arr [$service_status], $_lang['pub_time'] => date ( 'Y-m-d H:i:s', $service_obj->getOn_time () ) );
		$message_obj->send_message ( $this->_uid, $this->_username, "service_pub", $this->_model_info ['model_name'] . $_lang['release_tips'], $v, $this->_user_info ['email'], $this->_user_info ['mobile'] );
	}
	
	/**
	 * 计算商品增值服务费用
	 * @param float $service_cash 商品金额（无附加等）
	 * @return float $payitem_cash;
	 */
	public function get_payitem_cash($service_cash) {
		$payitem_cash = number_format ( $service_cash + $this->_std_obj->_att_cash, 2 ); //商品金额+增值费用
		return $payitem_cash;
	}
	/**
	 * 商品发布状态设置以及商品花费金额与金币的计算
	 * 返回根据后台配置的审核金额得出的当前商品可发布状态
	 * @param $service_cash  商品金额(不含增值费用)
	 */
	public function set_service_status($service_cash) {
		$audit_cash = $this->_service_config ['audit_cash']; //任务审核金额
		if ($audit_cash) { //需要审核
			if ($service_cash >= $audit_cash) { //大于审核金额
				$service_status = '2'; //无需
			} else {
				$service_status = '1'; //审核
			}
		} else {
			$service_status = '2'; //无需
		}
		$this->_service_obj->setService_status ( $service_status ); //设置商品状态
	}
	/**
	 * 商品发布通用块设置
	 * 用来处理各商品通用的设置
	 */
	public function public_pubservice() {
		$std_obj = $this->_std_obj; //成员对象
		$release_info = $std_obj->_release_info; //商品发布信息
		$service_obj = $this->_service_obj; //商品对象
		
		
		$txt_service_title = kekezu::str_filter ( $release_info ['txt_title'] ); //商品标题
		$service_obj->setTitle ( $txt_service_title );
		$service_obj->setModel_id ( $this->_model_id ); //设定商品类型
		
 		if($release_info[submit_method]=='inside'){
 			
 			$service_obj->setFile_path($release_info[file_path_2]);
 		}  
		$tar_content = kekezu::str_filter ( $release_info ['tar_content'] );
		$service_obj->setContent ( $tar_content );
		$service_obj->setIndus_id ( $release_info [indus_id] );
		$service_obj->setIndus_pid ( $release_info ['indus_pid'] );
		
		$shop_id = db_factory::get_count ( sprintf ( " select shop_id from %switkey_shop where uid ='%d'", TABLEPRE, $this->_uid ) );
		$service_obj->setShop_id ( $shop_id ); //店铺编号
		$service_obj->setUid ( $this->_uid );
		$service_obj->setUsername ( $this->_username );
		
		$service_obj->setPrice ( $release_info ['txt_price'] ); //价格
		$service_obj->setUnite_price ( $release_info ['unite_price'] ); //价格单位
		$service_obj->setService_time ( $release_info ['service_time'] ); //工时
		$service_obj->setUnit_time ( $release_info ['unit_time'] ); //工时单位
		 

		$service_obj->setProfit_rate ( $this->_service_config ['service_profit'] ); //当前比例
		

		/** 展示图片记录**/
		$release_info ['pic_patch'] and $service_obj->setPic ( $release_info ['pic_patch'] );
		$payitem_arr[top] = 1000000000;
		/**增值服务项录入**/
		if(is_array($std_obj->_att_info)){
			$att_info = array_filter ( $std_obj->_att_info ); //增值项信息
			$att_ids = implode ( ",", array_keys ( $att_info ) ); //增值项编号串
			$service_obj->setPay_item ( $att_ids );
			foreach ($att_info as $k=>$v) {  
				$v[item_code]=='top' and $payitem_arr[top] = time()+3600*24*$v[item_num];   
				$v[item_code]=='urgent' and $payitem_arr[urgent] = time()+3600*24*$v[item_num]; 
			}
		}
		$service_obj->setAtt_cash ( floatval ( $std_obj->_att_cash ) ); //增值项金额
		 
	
		
		
		$payitem_time =serialize($payitem_arr);
		
		$service_obj->setPayitem_time($payitem_time);
		$service_obj->setOn_time ( time () ); //商品发布时间
	}
	
	/**
	 * 商品信息成功产生的追加操作
	 */
	public function update_service_info($service_id, $obj_name) {
		global $_K;
		global $_lang;
		$std_obj = $this->_std_obj;
		$release_info = $std_obj->_release_info; //商品信息
		$att_info = $std_obj->_att_info; //增值信息
		$user_info = $this->_user_info; //用户信息
		$service_obj = $this->_service_obj; //商品对象
		if ($service_id) {
			$service_status = $service_obj->getService_status (); //商品状态
			switch ($service_status) {
				case "2" : //成功
					//增值服务记录
					$att_info = $this->create_payitem_reocrd ( $service_id, $att_info, $release_info ); 
					$service_title = $service_obj->getTitle (); 
					$feed_arr = array ("feed_username" => array ("content" => $this->_username, "url" => "index.php?do=space&member_id=$this->_uid" ), "action" => array ("content" => $_lang['has_pub_goods'], "url" => "" ), "event" => array ("content" => "$service_title", "url" => "index.php?do=service&sid=$service_id" ) );
					kekezu::save_feed ( $feed_arr, $this->_uid, $this->_username, 'pub_service', $service_id );
					
					//	kekezu::feed_add ( '<a href="index.php?do=space&member_id=' . $this->_uid . '" target="_blank">' . $this->_username . '</a>发布了商品 <a target="_blank" href="index.php?do=service_info&sid=' . $service_id . '">' . $service_obj->getTitle(). '</a>', $this->_uid, $this->_username, 'pub_service', $service_id );
					//发送消息
					$this->notify_user ( $service_id, '2' );
					$status = '2';
					
					break;
				case "1" : //进入审核
					//增值服务记录
					$att_info = $this->create_payitem_reocrd ( $service_id, $att_info, $release_info );
					//发送消息
					$this->notify_user ( $service_id, '1' );
					$status = '1';
					break;
			}
			
			db_factory::execute(' update '.TABLEPRE.'witkey_shop set on_sale=on_sale+1 where shop_id='.$service_obj->getShop_id());
		}
		$this->del_service_obj ( $obj_name ); //清除缓存
		header ( "Location:" . $_K ['siteurl'] . "/index.php?do=shop_release&model_id=$this->_model_id&r_step=step4&service_id=$service_id&status=" . $status );
	}
	/**
	 * 获取商品基本配置
	 * @return   void
	 */
	public abstract function get_service_config();
	/**
	 * 商品发布函数
	 */
	public abstract function pub_service();
	/**
	 * 增值服务记录产生
	 * @param int $service_id	商品编号
	 * @param array $att_info 增值项信息
	 * @return array $att_info 此处返回增值项信息是为后台订单详细产生做铺垫
	 */
	public function create_payitem_reocrd($service_id, $att_info = array(), $release_info) {
		/**增值服务记录产生**/
		if (! empty ( $att_info )) {
			$payitem_list = keke_payitem_class::get_payitem_config (); //可购买服务
	
			foreach ( $att_info as $k => $v ) {
				$remain = keke_payitem_class::payitem_exists ( $this->_uid, $v ['item_code'], $payitem_list [$v ['item_code']] ['item_type'] );
				$remain or keke_payitem_class::payitem_cost ( $v ['item_code'], $v[item_num],'', 'buy', $service_id, $service_id ); //买
				//用
				$v ['record_id'] = $pay_id = keke_payitem_class::payitem_cost ( $v ['item_code'], $v[item_num], 'service', 'spend', $service_id, $service_id );
				$pay_id and db_factory::execute ( sprintf ( " update %switkey_service set point='%s',city='%s' where service_id = '%d'", TABLEPRE, $release_info ['point'], $release_info ['province'], $service_id ) );
			}
		}
		return $att_info;
	}
	/**
	 * 附加费用保存
	 * @param int $item_id      增值服务项编号
	 * @param string $item_code 增值项代码
	 * @param string $item_name 增值服务名称
	 * @param float $item_cash  增值服务金额
	 * @param string $obj_name  商品信息session 保存名
	 * @return void
	 */
	public function save_pay_item($item_id, $item_code, $item_name, $item_cash, $obj_name,$item_num) {
		$att_info = $this->_std_obj->_att_info; //增值服务数组
		if ($att_info [$item_id] ['item_cash'] != $item_cash) { //不存在某项或其值改变时保存
			CHARSET == 'gbk' and $item_name = kekezu::utftogbk ( $item_name );
			$att_info [$item_id] ['item_code'] = $item_code;
			$att_info [$item_id] ['item_name'] = $item_name;
			$att_info [$item_id] ['item_cash'] = $item_cash;
			$att_info [$item_id] ['item_num'] = $item_num;
		}
		$this->_std_obj->_att_info = array_filter ( $att_info ); //重新保存增值项信息
		$this->save_service_obj ( array (), $obj_name ); //重新保存session
	
		foreach ($att_info as $k=>$v) {
			$total_cash +=$v[item_cash] ;
		} 
		kekezu::echojson(keke_curren_class::output( $total_cash, 2 ));
	
		die ();
	}
	/**
	 * 获取商品增值费用信息 
	 *@return $att_info;
	 */
	public function get_pay_item() {
		return $this->_std_obj->_att_info; //增值服务信息
	}
	/**
	 * 移除付费项	 
	 * @param $item_id  待移除项id
	 * @param $obj_name 商品信息session 保存名
	 */
	public function remove_pay_item($item_id, $obj_name) {
		$att_info = $this->_std_obj->_att_info; //增值服务数组
		if ($att_info [$item_id]) {
			unset ( $att_info [$item_id] );
		}
		$this->_std_obj->_att_info = array_filter ( $att_info ); //重新保存增值服务信息
		$this->save_service_obj ( array (), $obj_name ); //重新保存商品session
		
		foreach ($this->_std_obj->_att_info  as $k=>$v) {
			$total_cash +=$v[item_cash] ;
		} 
	
		kekezu::echojson ( number_format($total_cash,2), 1 );
	
		die ();
		
	}
	
	
	
	
	
	
	
	/**
	 * 计算增值服务费总额
	 * @param array $att_info 增值服务数组信息
	 * @return float 增值服务总金额
	 */
	public function solve_pay_item($att_info = array()) {
		$att_cash = '0';
		if (is_array ( $att_info )) {
			foreach ( $att_info as $v ) {
				$att_cash += floatval ( $v ['item_cash'] );
			}
		}
		return $att_cash;
	}
	/**
	 * 保存当前发布信息
	 * @param array $release_info 商品发布信息
	 * @param string $obj_name    外部传入的商品对象session名
	 * @return   void
	 */
	public function save_service_obj($release_info = array(), $obj_name) {
		global $kekezu;
		if ($release_info ['p_step'] == 'step2') {
			/** 广告图片记录**/

			if ($_POST['file_ids']){
				$pic = kekezu::escape($_POST['file_ids']);

				$release_info['pic_patch'] = $pic;//大图

 			}
		}
		empty ( $release_info ) or $this->_std_obj->_release_info = $release_info; //将商品信息保存入成员对象中
		$this->_std_obj->_att_cash = $this->solve_pay_item ( $this->_std_obj->_att_info ); //增值服务总额
		$_SESSION [$obj_name] = serialize ( $this->_std_obj ); //将对象保存入session
		
	}
	/**
	 * 获取商品信息
	 * @param string $obj_name    外部传入的商品对象session名
	 * @return   void
	 */
	public function get_service_obj($obj_name) {
		$_SESSION [$obj_name] and $this->_std_obj = unserialize ( $_SESSION [$obj_name] );
	}
	
	/**
	 * 销毁商品对象
	 * @return   void
	 */
	public function del_service_obj($obj_name) {
		if (isset ( $_SESSION [$obj_name] )) {
			unset ( $_SESSION [$obj_name] );
		}
		if (isset ( $_SESSION ['formhash'] )) {
			unset ( $_SESSION ['formhash'] );
		}
	}
	/**
	 * 页面进入权限判断
	 * @param string $r_step 商品当前步骤
	 * @param int $model_id 商品模型
	 * @param array $relese_info 商品发布信息
	 * @param int $service_id  发布完成的商品编号  ，第四步有用
	 */
	public function check_access($r_step, $model_id, $release_info, $service_id = null, $output = 'normal') {
		global $_lang;
		switch ($r_step) {
			case "step1" :
				break;
			case "step2" : //没有进过第一步
				$release_info ['step1'] or kekezu::keke_show_msg ( "index.php?do=shelves&model_id=$model_id", $_lang['no_choose_goods_model_notice'], "error", $output );
				break;
			
			case "step3" : //没有进过第二步
				if (! $release_info ['step2'] && ! $release_info ['step1']) { //没进过前2步
					kekezu::keke_show_msg ( "index.php?do=shelves&model_id=$model_id", $_lang['no_choose_goods_model_notice'], "error", $output );
				} elseif (! $release_info ['step2']) {
					kekezu::keke_show_msg ( "index.php?do=shelves&model_id=$model_id&r_step=step2", $_lang['no_input_goods_need_notice'], "error", $output );
				}
				break;
			case "step4" : //无法查到刚才的商品记录。此页面10分钟有效
				$sql = sprintf ( " select service_status,service_id from %switkey_service where service_id = '%d' and on_time>%d", TABLEPRE, $service_id, time () - 600 );
				$service_info = db_factory::get_one ( $sql );
				$service_info or kekezu::keke_show_msg ( "index.php?do=shelves", $_lang['page_expired_notice'], "error", $output );
				return $service_info;
				break;
		}
	}
	/**
	 * 报价单位
	 */
	public static function get_price_unit() {
		global $_lang;
		return array ($_lang['ge'], $_lang['pieces'], $_lang['times'], $_lang['copy'] );
	}
	/**
	 * 工时单位
	 */
	public static function get_service_unit() {
		global $_lang;
		return array ($_lang['hour'], $_lang['day'], $_lang['week'], $_lang['month'] );
	
	}
}