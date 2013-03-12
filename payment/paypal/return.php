<?php
/**
 * paypal Ö§¸¶Ìø×ªÒ³Ãæ
 */
define ( "IN_KEKE", true );
require (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require "Paypal.php";
$myPaypal = new Paypal ();

// Log the IPN results
$myPaypal->logIpn = TRUE;
// Enable test mode if needed
//$myPaypal->enableTestMode ();
$valid  = $myPaypal->validateIpn ();
// Check validity and write down it
list ( $_, $charge_type, $uid, $obj_id, $order_id, $model_id ) = explode ( '-', $myPaypal->ipnData ['custom'], 6 );
$fac_obj = new pay_return_fac_class ( $charge_type, $model_id, $uid, $obj_id, $order_id,$myPaypal->ipnData['mc_gross'], 'paypal' );
if ($valid) {
	if ($myPaypal->ipnData ['payment_status'] == 'Completed') {
		$response = $fac_obj->load();
		$fac_obj->return_notify('paypal',$response);
	} else {
		$fac_obj->return_notify( 'paypal');
	}
} else {
	$fac_obj->return_notify( 'paypal');
}