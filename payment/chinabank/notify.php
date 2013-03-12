<?php
define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');

$pay_arr = kekezu::get_payment_config ( 'chinabank' );
@extract ( $pay_arr );
$key = $chinabank_seller_id;
$v_oid = trim ( $_POST ['v_oid'] ); // �̻����͵�v_oid�������   
$v_pmode = trim ( $_POST ['v_pmode'] ); // ֧����ʽ���ַ�����   
$v_pstatus = trim ( $_POST ['v_pstatus'] ); //֧��״̬ ��20 �ɹ�,30 ʧ��
$v_pstring = trim ( $_POST ['v_pstring'] ); // ֧�������Ϣ
$v_amount = trim ( $_POST ['v_amount'] ); // ����ʵ��֧�����
$v_moneytype = trim ( $_POST ['v_moneytype'] ); //����ʵ��֧������    
$remark1 = trim ( $_POST ['remark1'] ); //��ע�ֶ�1
$remark2 = trim ( $_POST ['remark2'] ); //��ע�ֶ�2
$v_md5str = trim ( $_POST ['v_md5str'] ); //ƴ�պ��MD5У��ֵ  


/* ���¼���md5��ֵ */
$text = "{$v_oid}{$v_pstatus}{$v_amount}{$v_moneytype}{$key}";
$md5string = strtoupper ( md5 ( $text ) );

$total_fee = $_POST ['total_fee']; //��ȡ�ܼ۸�  
// chmod('log.txt',777);
// KEKE_DEBUG and $fp = file_put_contents ( 'log.txt', var_export($_POST,1),FILE_APPEND);//��Ϣ¼��
/* �жϷ�����Ϣ�����֧���ɹ�������֧��������ţ�������һ���Ĵ��� */
if ($v_md5str == $md5string) {
	list ( $_, $charge_type, $uid, $obj_id, $order_id, $model_id ) = explode ( '-', $v_oid, 6 );
	if ($v_pstatus == "20" && $_ == 'charge') {
		/* charge */
		$fac_obj = new pay_return_fac_class ( $charge_type, $model_id, $uid, $obj_id, $order_id, $v_amount, 'chinabank' );
		$fac_obj->load ( );
		echo "ok";
	}
} else {
	echo "error";
}