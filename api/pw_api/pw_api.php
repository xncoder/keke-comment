<?php
error_reporting(0);
define('P_W','admincp');
define("IN_KEKE",true);
$close_allow_fxx = 1;
require_once '../../app_comm.php';
require_once(S_ROOT.'./config/config_pw.php');


define('R_P',S_ROOT.'/');
define('D_P',R_P);

require_once(S_ROOT.'/api/pw_api/security.php');
require_once(S_ROOT.'/api/pw_api/pw_common.php');
require_once(S_ROOT . '/keke_client/pw_client/class_db.php');

$ucdb =  new UcDB(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBPCONNECT, UC_DBCHARSET);

require_once(S_ROOT.'/api/pw_api/class_base.php');

$api = new api_client();

$response = $api->run($_POST + $_GET);


//KEKE_DEBUG and  file_put_contents('pw_log.txt', var_export($response,true),FILE_APPEND);
if ($response) {
	
	echo $api->dataFormat($response);
	
}

?>