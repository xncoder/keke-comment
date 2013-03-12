<?php
!defined('P_W') && exit('Forbidden');
//api mode 6

class Credit {
	
	var $base;
	var $db;

	function Credit($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get() {
		 return new ApiResponse(pwCreditNames());
	}

	function syncredit($arr) {
		global $config,$mysqli; 
		if (is_array($arr)) {
			foreach ($arr as $uid => $setv) {
				$updateMemberData = array();
				foreach ($setv as $cid => $value) {
					 $value = intval($value);
					 $mysqli->query("UPDATE ".wk('works')." SET userpointsnum='$value' WHERE uid='$uid'");
				}

			}
		}
		return new ApiResponse(1);
	}

	function getvalue($uid) {
		require_once(R_P.'require/credit.php');
		$getv = $credit->get($uid);
		$retv = array();
		foreach ($credit->cType as $key => $value) {
			$retv[$key] = array('title' => $value, 'value' => isset($getv[$key]) ? $getv[$key] : 0);
		}
		return new ApiResponse($retv);
	}
}
?>