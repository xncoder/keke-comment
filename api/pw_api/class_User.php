<?php

!defined('P_W') && exit('Forbidden');
//api mode 1

define('API_USER_USERNAME_NOT_UNIQUE', 100);

class User {

	var $base;
	var $db;

	function User($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function getInfo($uids, $fields = array()) {
		if (!$uids) {
			return new ApiResponse(false);
		}
		require_once(R_P.'require/showimg.php');

		$uids = is_numeric($uids) ? array($uids) : explode(",",$uids);

		if (!$fields) $fields = array('uid', 'username', 'icon', 'gender', 'location', 'bday');
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$users = array();
		foreach ($userService->getByUserIds($uids) as $rt) {
			list($rt['icon']) = showfacedesign($rt['icon'], 1, 'm');
			$rt_a = array();
			foreach ($fields as $field) {
				if (isset($rt[$field])) {
					$rt_a[$field] = $rt[$field];
				}
			}
			$users[$rt['uid']] = $rt_a;
		}
		return new ApiResponse($users);
	}

	function alterName($uid, $newname) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userName = $userService->getUserNameByUserId($uid);
		if (!$userName || $userName == $newname) {
			return new ApiResponse(1);
		}
		$existUserId = $userService->getUserIdByUserName($newname);
		if ($existUserId) {
			return new ApiResponse(API_USER_USERNAME_NOT_UNIQUE);
		}
		$userService->update($uid, array('username' => $newname));

		$user = L::loadClass('ucuser', 'user');
		$user->alterName($uid, $userName, $newname);

		return new ApiResponse(1);
	}

	function deluser($uids) {
		$user = L::loadClass('ucuser', 'user');
		$user->delUserByIds($uids);

		return new ApiResponse(1);
	}

	function synlogin($user) {
		global $timestamp,$uc_key;
		list($winduid, $windid, $windpwd) = explode("\t", $this->base->strcode($user, false));
 
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		
		include_once ('../../app_comm.php');
		//kekezu::prom_check();
		//最新登录时间
		$space_obj = new Keke_witkey_space_class();
		$space_obj->setUid($winduid);
		$space_obj->setLast_login_time(time());
		$space_obj->edit_keke_witkey_space();
 
		$uinfo = kekezu::get_user_info($winduid);
		$_SESSION['uid'] = $uinfo['uid'];
		$_SESSION['username'] = $uinfo['username'];
	    return new ApiResponse(1);
		
	}

	function synlogout() {
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		include_once ('../../app_comm.php');
		$_SESSION['uid'] = '';
		$_SESSION['username'] = '';
		setcookie ( 'user_login', '' );
		setcookie ( 'prom_cke', '' );
		setcookie ( 'score_log', '' );
		unset($_COOKIE);
		unset($_SESSION);
		session_destroy();
		return new ApiResponse(1);
	}
    function getusergroup() {
        $usergroup = array();
        $query = $this->db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups ");
        while($rt= $this->db->fetch_array($query)) {
            $usergroup[$rt['gid']] = $rt;
        }
        return new ApiResponse($usergroup);
    }
}
?>