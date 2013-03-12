<?php
keke_lang_class::load_lang_class ( 'keke_ajax_upload_class' );
class keke_ajax_upload_class {
	private $_ext_url;
	private $_file_name;
	public $_file_type;
	private $_img_width;
	private $_img_height;
	private $_upload_type;
	private $_task_id;
	private $_work_id;
	private $_obj_type;
	private $_obj_id;
	private $_uid;
	private $_username;
	private $_flash;
	private $_max_filesize;
	public static function get_instance($query_string) {
		static $obj = null;
		if ($obj == null) {
			$obj = new keke_ajax_upload_class ( $query_string );
		}
		return $obj;
	}
	function __construct($query_string) {
		global $kekezu;
		$this->_ext_url = explode ( "|", UPLOAD_ALLOWEXT );
		$this->_uid = $kekezu->_uid;
		$this->_username = $kekezu->_username;
		$this->_max_filesize = $kekezu->_sys_config ['max_size'];
		$this->file_info_init ( $query_string );
	}
	public function file_info_init($query_string) {
		$url_data = array ();
		parse_str ( $query_string, $url_data );
		$url_data ['file_name'] and $this->_file_name = $url_data ['file_name'] or $this->_file_name = 'filedata';
		$url_data ['file_type'] and $this->_file_type = $url_data ['file_type'];
		intval($url_data ['img_width']) and $this->_img_width = $url_data ['img_width'];
		intval($url_data ['img_height']) and $this->_img_height = $url_data ['img_height'];
		if(in_array($url_data['task_id'],array('ad','auth','mark','tools')) or intval($url_data['task_id'])>0){
			$this->_task_id = $url_data ['task_id'];
		}
		$url_data ['work_id'] and $this->_work_id = $url_data ['work_id'];
		$url_data ['obj_id'] and $this->_obj_id = $url_data ['obj_id'];
		$url_data ['obj_type'] and $this->_obj_type = $url_data ['obj_type'];
		$url_data ['flash'] and $this->_flash = 1;
	}
	public function upload_file() {
		global $_K;
		global $_lang;
		$file_obj = new Keke_witkey_file_class ();
		if ($this->_img_width) {
			$img_info = getimagesize ( $_FILES [$this->_file_name] ['tmp_name'] );
			if ($img_info) {
				$w = $img_info [0];
				if ($this->_img_width != $w) {
					$err = $_lang ['upload_fail_picture_width_is'] . $w . "," . $_lang ['picture_limit_width'] . $this->_img_width . "," . $_lang ['picture_adjust_and_then_upload'];
					$_K ['charset'] == 'gbk' and $err = kekezu::gbktoutf ( $err );
					echo kekezu::json_encode_k ( array ('err' => $err, 'msg' => 'error' ) );
					die ();
				}
			}
		}
		if ($this->_img_height) {
			$img_info = getimagesize ( $_FILES [$this->_file_name] ['tmp_name'] );
			if ($img_info) {
				$h = $img_info [1];
				if ($this->_img_height != $h) {
					$err = $_lang ['upload_fail_picture_heigth'] . $h . "," . $_lang ['picture_limit_height'] . $this->_img_height . "," . $_lang ['picture_adjust_and_then_upload'];
					$_K ['charset'] == 'gbk' and $err = kekezu::gbktoutf ( $err );
					echo kekezu::json_encode_k ( array ('err' => $err, 'msg' => 'error' ) );
					die ();
				}
			}
		}
		if ($this->_file_type != 'sys') {
			$save_path = UPLOAD_ROOT;
			$rand_name = 1;
		} else {
			$save_path = S_ROOT . '/data/uploads/sys/' . $this->_task_id . '/';
			$rand_name = 1;
		}
		$file_uploads = new keke_upload_class ( $save_path, $this->_ext_url, UPLOAD_MAXSIZE );
		$savename = $file_uploads->run ( $this->_file_name, $rand_name );
		if (is_array ( $savename )) {
			if ($this->_file_type == 'att') {
				$file_pic = 'data/uploads/' . UPLOAD_RULE . $savename [0] [saveName];
			} elseif ($this->_file_type == 'editor') {
				$file_pic = $_K [siteurl] . '/data/uploads/' . UPLOAD_RULE . $savename [0] [saveName];
			} else {
				$file_pic = 'data/uploads/sys/' . $this->_task_id . '/' . $savename [0] [saveName];
			}
			$real_file = $savename [0] [name];
			($this->_flash&&CHARSET == 'gbk') && $real_file = kekezu::utftogbk ( $real_file );
			if ($this->_file_type == 'link') {
				$msg = array ('url' => $file_pic . ',' . $real_file, 'localname' => $real_file, 'id' => '1', 'up_file' => $file_pic );
			} else if ($this->_file_type == 'att' || $this->_file_type == 'sys') {
				$msg = array ('url' => $file_pic, 'localname' => $real_file, 'id' => '1', 'up_file' => $file_pic );
			} else {
				$msg = array ('url' => '!' . $file_pic, 'localname' => $real_file, 'id' => '1', 'up_file' => $file_pic );
			}
			$file_obj->setUid ( $this->_uid );
			$file_obj->setUsername ( $this->_username );
			$file_obj->setTask_id ( intval ( $this->_task_id ) );
			$file_obj->setFile_name ( $real_file );
			$file_obj->setSave_name ( $file_pic );
			$file_obj->setWork_id ( intval ( $this->_work_id ) );
			$file_obj->setObj_id ( intval ( $this->_obj_id ) );
			$file_obj->setObj_type ( $this->_obj_type );
			$file_obj->setOn_time ( time () );
			$res = $file_obj->create_keke_witkey_file ();
			$err = '';
		} else {
			$err = $savename;
			$msg = $savename;
		}
		$_K ['charset'] != 'utf-8' and $msg = kekezu::gbktoutf ( $msg );
		echo kekezu::json_encode_k ( array ('err' => $err, 'msg' => $msg, 'fid' => $res ) );
		die ();
	}
	public function upload_big_file() {
		$file_uploads = new keke_upload_class ( UPLOAD_ROOT, '', 50 * (1024 * 1024) );
		$savename = $file_uploads->run ( $this->_file_name, 1 );
		if (is_array ( $savename )) {
			$echo_str = 'data/uploads/' . UPLOAD_RULE . $savename [0] ['saveName'];
			$filename = $savename [0] ['saveName'];
			$real_file = $savename [0] [name];
			($this->_flash&&CHARSET == 'gbk') && $real_file = kekezu::utftogbk ( $real_file );
			$err = '';
		} else {
			$err = $savename;
		}
		$fid = time ();
		echo kekezu::json_encode_k ( array ('err' => $err, 'path' => $echo_str, 'filename' => $filename, 'localname' => $real_file, 'fid' => $fid ) );
		die ();
	}
	public function upload_and_resize_pic() {
		global $_K;
		$ext = 'jpg|jpeg|gif|png|bmp';
		$filename = $this->_file_name;
		$real_file = $_FILES [$filename] ['name'];
		$filepath = keke_file_class::upload_file ( $filename, $ext, 1 );
		if (! filepath) {
			return false;
		}
		CHARSET == 'gbk' && $real_file = kekezu::utftogbk ( $real_file );
		$file_obj = new Keke_witkey_file_class ();
		$file_obj->setUid ( $this->_uid );
		$file_obj->setUsername ( $this->_username );
		$file_obj->setFile_name ( $real_file );
		$file_obj->setSave_name ( $filepath );
		$file_obj->setObj_id ( intval ( $this->_obj_id ) );
		$file_obj->setObj_type ( $this->_obj_type );
		$file_obj->setOn_time ( time () );
		$fid = $file_obj->create_keke_witkey_file ();
		$size_a = array (100, 100 );
		$size_b = array (210, 210 );
		$result = keke_img_class::resize ( $filepath, $size_a, $size_b, true ); 
		$msg = array ('path' => $filepath, 'filename' => $filename, 'localname' => $real_file, 'fid' => $fid, 'size' => $size_a [0] . ',' . $size_b [0] );
		($this->_flash&&CHARSET == 'gbk') && $msg = kekezu::gbktoutf ( $msg );
		$_K ['charset'] != 'utf-8' and $msg = kekezu::gbktoutf ( $msg );
		echo kekezu::json_encode_k ( $msg );
		die ();
	}
}
?>