<?php
 /**
 * @copyright keke-tech
 * @author Monkey
 * @version v 2.0
 * 2010-9-8ÉÏÎç10:14:10
 */

if(!defined('IN_KEKE')) {
	exit('Access Denied');
}
$notify_url=$_K ['siteurl'] . '/js.php?op=payment&pay_op=notify&pay_m='.$payment_config['pay_dir'];;
$return_url = $_K ['siteurl'] . '/js.php?op=payment&pay_op=return&pay_m='.$payment_config['pay_dir'];;
$cancel_url = $_K ['siteurl'] . '/js.php?op=payment&pay_op=return&do=cancel&pay_m='.$payment_config['pay_dir'];;
$v_oid = "charge-{$uid}-{$order_type}-{$obj_id}-{$model_id}";
require_once $template_obj->template ( 'payment/paypal/confirm' );
				