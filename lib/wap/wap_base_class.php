<?php
class wap_base_class {
	public static function is_wrap() {
		return strpos ( $_SERVER ['HTTP_VIA'], "wap" ) > 0 ? true : false; 
	}
	public static function check_login() {
		$uid = intval ( $_SESSION ['uid'] );
		if ($uid) {
			return true;
		} else {
			kekezu::echojson ( array ('a' => 'relogin', 'r' => 'Connection timed out' ), 0 );
			die ();
		}
	}
	public static function update_load_status($uid, $s = 1) {
		if ($s == 1) {
			$s = 'online';
		} elseif ($s == 2) {
			$s = 'offline';
		}
		$uid && db_factory::execute ( sprintf ( " update %switkey_space set client_status='%s' where uid='%d'", TABLEPRE, $s, $uid ) );
	}
	public static function get_task_info($task_id) {
		return db_factory::get_one ( sprintf ( " select * from %switkey_task where task_id='%d'", TABLEPRE, $task_id ) );
	}
	public static function get_task_list() {
		$_D = $_REQUEST;
		$_D ['action'] and $ac = $_D ['action'] or $ac = 'task';
		switch ($ac) {
			case 'task' :
				$ords = array (1 => 'start_time desc ', 2 => 'start_time asc', 3 => 'task_cash asc', 4 => 'task_cash desc' );
				$pre1 = 'select * ';
				$pre2 = 'select count(task_id) as count ';
				$sql = 'from ' . TABLEPRE . 'witkey_task where 1=1 ';
			$_D ['model_id'] and  $sql .= ' and model_id=' . $_D ['model_id'] or $sql .= ' and model_id in(1,3)';
				$i_id = intval ( $_D ['indus_id'] ); 
				$i_id && $sql .= ' and indus_id=' . $i_id;
				$t = trim ( strval ( $_D ['search_key'] ) ); 
				$t && $sql .= ' and task_title like "%' . $t . '%" ';
				if (isset ( $_D ['status'] )) {
					if ($_D ['status'] != "all") {
						$sql .= ' and task_status=' . intval ( $_D ['status'] );
					}
				} else {
					$sql .= " and task_status in(2,3)";
				}
				$puid = intval ( $_D ['u_id'] ); 
				$puid && $sql .= ' and uid=' . $puid;
				$ord = max ( $_D ['order'], 1 ); 
				$sql .= ' order by ' . $ords [$ord];
				$count = db_factory::get_count ( $pre2 . $sql );
				$ls = $_D ['ls']; 
				$le = $_D ['le']; 
				$sql .= ' limit ' . $ls . ',' . $le;
				$data = db_factory::query ( $pre1 . $sql );
				kekezu::echojson ( intval ( $count ), $_D ['ord'], $data );
				break;
			case 'join' :
				self::get_join_task ( $_D );
				break;
			case 'favor' :
				self::get_favor_task($_D);
				break;
		}
		die ();
	}
	static function get_join_task($d) {
		global $uid;
		$c_sql = ' select count(DISTINCT a.task_id) c ';
		$q_sql = ' select DISTINCT a.* ';
		$sql = ' from ' . TABLEPRE . 'witkey_task a left join ' . TABLEPRE . 'witkey_task_work b on a.task_id=b.task_id where 
						b.uid=' . $uid . ' order by a.task_id desc ';
		$ls = $d ['ls']; 
		$le = $d ['le']; 
		$sql .= ' limit ' . $ls . ',' . $le;
		$count = db_factory::get_count ( $c_sql . $sql );
		$data = db_factory::query ( $q_sql . $sql );
		kekezu::echojson ( intval ( $count ), 1, $data );
	}
	static function get_favor_task($d) {
		global $uid,$kekezu;
		$ls = $d ['ls']; 
		$le = $d ['le']; 
		$model_id = max ( $d['model_id'], 1 );
		$obj_type = $kekezu->_model_list [$model_id] ['model_code'];
		$csql = " select count(b.f_id) c ";
		$qsql = " select a.* ";
		$sql  = " from %switkey_favorite b left join %switkey_task a on 
				  a.task_id = b.obj_id where b.uid='%d' and b.obj_type='%s' and keep_type='task' ";
		$count = db_factory::get_count(sprintf($csql.$sql,TABLEPRE,TABLEPRE, $uid, $obj_type));
		$sql.=" limit %d,%d ";
		$data = db_factory::query ( sprintf ( $qsql.$sql, TABLEPRE,TABLEPRE, $uid, $obj_type, $ls, $le ) );
		kekezu::echojson (intval($count), 1, $data );
		die ();
	}
}