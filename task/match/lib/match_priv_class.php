<?php
/**
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * @desc 权限悬赏控制类
 * @version 2011-08-25 13:27:34
 */
class match_priv_class extends keke_privission_class{
	
	public static function get_instance($model_id) {
		static $obj = null;
		if ($obj == null) {
			$obj = new match_priv_class($model_id);
		}
		return $obj;
	}
	
	public function __construct($model_id){
		parent::__construct($model_id);
	}
	
	/**
	 * 获取指定模型下指定类型用户的操作权限
	 * @param int $task_id 任务编号
	 * @param int $mode_id 模型编号
	 * @param $user_info 用户信息
	 * @param int $role 用户角色   默认为1=>威客
	 * @return boolean
	 */
	public static function get_priv($task_id,$mode_id,$user_info,$role='1') {
		return parent::get_priv($task_id,$mode_id, $user_info,$role);
	}
}

?>