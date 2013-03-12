<?php
class keke_file_class {
	
	public static function read_file($file) {
		if (! file_exists ( $file )) {
			return FALSE;
		}
		
		if (function_exists ( 'file_get_contents' )) {
			return file_get_contents ( $file );
		}
		
		if (! $fp = @fopen ( $file, FOPEN_READ )) {
			return FALSE;
		}
		
		flock ( $fp, LOCK_SH );
		
		$data = '';
		if (filesize ( $file ) > 0) {
			$data = & fread ( $fp, filesize ( $file ) );
		}
		
		flock ( $fp, LOCK_UN );
		fclose ( $fp );
		
		return $data;
	}
	
	/**
	 * Write File
	 *
	 * Writes data to the file specified in the path.
	 * Creates a new file if non-existent.
	 *
	 * @access public
	 * @param
	 * string	path to file
	 * @param
	 * string	file data
	 * @return bool
	 */
	
	public static function write_file($path, $data, $mode = 'w') {
		
		try {
			$file = new SplFileInfo ( $path );
			$data_obj = $file->openFile ( $mode );
			$data_obj->fwrite ( $data, strlen ( $data ) );
			$data_obj->fflush ();
		} catch ( Exception $e ) {
			throw new keke_exception ( $e->getMessage () );
		}
		
		unset ( $file, $data_obj, $data );
		return TRUE;
	}
	
	// ------------------------------------------------------------------------
	

	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to TRUE, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @access public
	 * @param
	 * string	path to file
	 * @param
	 * bool	whether to delete any directories found in the path
	 * @return bool
	 */
	
	function delete_files($path, $del_dir = FALSE, $level = 0) {
		// Trim the trailing slash
		$path = rtrim ( $path, DIRECTORY_SEPARATOR );
		
		if (! $current_dir = @opendir ( $path ))
			return;
		
		while ( FALSE !== ($filename = @readdir ( $current_dir )) ) {
			if ($filename != "." and $filename != "..") {
				if (is_dir ( $path . DIRECTORY_SEPARATOR . $filename )) {
					// Ignore empty folders
					if (substr ( $filename, 0, 1 ) != '.') {
						$this->delete_files ( $path . DIRECTORY_SEPARATOR . $filename, $del_dir, $level + 1 );
					}
				} else {
					unlink ( $path . DIRECTORY_SEPARATOR . $filename );
				}
			}
		}
		@closedir ( $current_dir );
		
		if ($del_dir == TRUE and $level > 0) {
			@rmdir ( $path );
		}
	}
	
	// ------------------------------------------------------------------------
	

	/**
	 * Get Filenames
	 *
	 * Reads the specified directory and builds an array containing the
	 * filenames.
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @access public
	 * @param
	 * string	path to source
	 * @param
	 * bool	whether to include the path as part of the filename
	 * @param
	 * bool	internal variable to determine recursion status - do not
	 * use in calls
	 * @return array
	 */
	
	function get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE) {
		static $_filedata = array ();
		
		if ($fp = @opendir ( $source_dir )) {
			// reset the array and make sure $source_dir has a trailing slash on
			// the initial call
			if ($_recursion === FALSE) {
				$_filedata = array ();
				$source_dir = rtrim ( realpath ( $source_dir ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			}
			
			while ( FALSE !== ($file = readdir ( $fp )) ) {
				if (@is_dir ( $source_dir . $file ) && strncmp ( $file, '.', 1 ) !== 0) {
					$this->get_filenames ( $source_dir . $file . DIRECTORY_SEPARATOR, $include_path, TRUE );
				} elseif (strncmp ( $file, '.', 1 ) !== 0) {
					$_filedata [] = ($include_path == TRUE) ? $source_dir . $file : $file;
				}
			}
			return $_filedata;
		} else {
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	

	/**
	 * Get Directory File Information
	 *
	 * Reads the specified directory and builds an array containing the
	 * filenames,
	 * filesize, dates, and permissions
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @access public
	 * @param
	 * string	path to source
	 * @param
	 * bool	whether to include the path as part of the filename
	 * @param
	 * bool	internal variable to determine recursion status - do not
	 * use in calls
	 * @return array
	 */
	
	static function get_dir_file_info($source_dir, $include_path = FALSE, $_recursion = FALSE, $sub_folder = null) {
		static $_filedata = array ();
		$relative_path = $source_dir;
		
		if ($fp = @opendir ( $source_dir )) {
			// reset the array and make sure $source_dir has a trailing slash on
			// the initial call
			if ($_recursion === FALSE) {
				$_filedata = array ();
				$source_dir = rtrim ( realpath ( $source_dir ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			}
			
			while ( FALSE !== ($file = readdir ( $fp )) ) {
				if ($file != 'css' && $file != 'img' && $file != 'js') {
					if (strncmp ( $file, '.', 1 ) !== 0) {
						if ($file) {
						}
						$_filedata [$file] = self::get_file_info ( $source_dir . $file );
						$_filedata [$file] ['relative_path'] = $relative_path;
						$_filedata [$file] ['sub'] = $sub_folder;
						if (@is_dir ( $source_dir . '/' . $file )) {
							$_filedata [$file] ['sub'] = 'index';
							self::get_dir_file_info ( $source_dir . '/' . $file, true, true, $file );
						}
					}
				}
			}
			return $_filedata;
		} else {
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	

	/**
	 * Get File Info
	 *
	 * Given a file and path, returns the name, path, size, date modified
	 * Second parameter allows you to explicitly declare what information you
	 * want returned
	 * Options are: name, server_path, size, date, readable, writable,
	 * executable, fileperms
	 * Returns FALSE if the file cannot be found.
	 *
	 * @access public
	 * @param
	 * string	path to file
	 * @param
	 * mixed	array or comma separated string of information returned
	 * @return array
	 *
	 */
	
	static function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date')) {
		
		if (! file_exists ( $file )) {
			return FALSE;
		}
		
		if (is_string ( $returned_values )) {
			$returned_values = explode ( ',', $returned_values );
		}
		
		foreach ( $returned_values as $key ) {
			switch ($key) {
				case 'name' :
					$fileinfo ['name'] = substr ( strrchr ( $file, DIRECTORY_SEPARATOR ), 1 );
					break;
				case 'server_path' :
					$fileinfo ['server_path'] = $file;
					break;
				case 'size' :
					$fileinfo ['size'] = filesize ( $file );
					break;
				case 'date' :
					$fileinfo ['date'] = filectime ( $file );
					break;
				case 'readable' :
					$fileinfo ['readable'] = is_readable ( $file );
					break;
				case 'writable' :
					// There are known problems using is_weritable on IIS. It
					// may not be reliable - consider fileperms()
					$fileinfo ['writable'] = is_writable ( $file );
					break;
				case 'executable' :
					$fileinfo ['executable'] = is_executable ( $file );
					break;
				case 'fileperms' :
					$fileinfo ['fileperms'] = fileperms ( $file );
					break;
			}
		}
		
		return $fileinfo;
	}
	
	static function get_file_type($file_path, $ext = '') {
		$fp = fopen ( $file_path, 'r' );
		$bin = fread ( $fp, 2 );
		fclose ( $fp );
		$strInfo = @unpack ( "C2chars", $bin );
		$typeCode = intval ( $strInfo ['chars1'] . $strInfo ['chars2'] );
		$fileType = 'unknown';
		$typeCode == '3780' && $fileType = "pdf";
		$typeCode == '6787' && $fileType = "swf";
		$typeCode == '7087' && $fileType = "swf";
		$typeCode == '7784' && $fileType = "midi";
		$typeCode == '7790' && $fileType = "exe";
		$ext == 'txt' && $fileType = "txt";
		in_array ( $typeCode, array ('8297', '8075' ) ) && $fileType = $ext; // rar,zip
		if (in_array ( $typeCode, array ('255216', '7173', '6677', '13780' ) )) { // jpg,gif,bmp,png
			in_array ( $ext, array ('jpg', 'gif', 'bmp', 'png', 'jpeg' ) ) and $fileType = $ext or $fileType = 'jpg';
		}
		if ($typeCode == '208207') { // wps,ppt,dot,xls,doc,docx
			in_array ( $ext, array ('wps', 'ppt', 'dot', 'xls', 'doc', 'docx' ) ) and $fileType = $ext or $fileType = 'doc';
		}
		
		return $fileType;
	}
	// --------------------------------------------------------------------
	

	/**
	 * Get Mime by Extension
	 *
	 * Translates a file extension into a mime type based on config/mimes.php.
	 * Returns FALSE if it can't determine the type, or open the mime config
	 * file
	 *
	 * Note: this is NOT an accurate way of determining file mime types, and is
	 * here strictly as a convenience
	 * It should NOT be trusted, and should certainly NOT be used for security
	 *
	 * @access public
	 * @param
	 * string	path to file
	 * @return mixed
	 */
	
	function get_mime_by_extension($file) {
		$extension = substr ( strrchr ( $file, '.' ), 1 );
		
		global $mimes;
		
		if (! is_array ( $mimes )) {
			if (! require_once (APPPATH . 'config/mimes.php')) {
				return FALSE;
			}
		}
		
		if (array_key_exists ( $extension, $mimes )) {
			if (is_array ( $mimes [$extension] )) {
				// Multiple mime types, just give the first one
				return current ( $mimes [$extension] );
			} else {
				return $mimes [$extension];
			}
		} else {
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	

	/**
	 * Symbolic Permissions
	 *
	 * Takes a numeric value representing a file's permissions and returns
	 * standard symbolic notation representing that value
	 *
	 * @access public
	 * @param
	 * int
	 * @return string
	 */
	
	function symbolic_permissions($perms) {
		if (($perms & 0xC000) == 0xC000) {
			$symbolic = 's'; // Socket
		} elseif (($perms & 0xA000) == 0xA000) {
			$symbolic = 'l'; // Symbolic Link
		} elseif (($perms & 0x8000) == 0x8000) {
			$symbolic = '-'; // Regular
		} elseif (($perms & 0x6000) == 0x6000) {
			$symbolic = 'b'; // Block special
		} elseif (($perms & 0x4000) == 0x4000) {
			$symbolic = 'd'; // Directory
		} elseif (($perms & 0x2000) == 0x2000) {
			$symbolic = 'c'; // Character special
		} elseif (($perms & 0x1000) == 0x1000) {
			$symbolic = 'p'; // FIFO pipe
		} else {
			$symbolic = 'u'; // Unknown
		}
		
		// Owner
		$symbolic .= (($perms & 0x0100) ? 'r' : '-');
		$symbolic .= (($perms & 0x0080) ? 'w' : '-');
		$symbolic .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
		
		// Group
		$symbolic .= (($perms & 0x0020) ? 'r' : '-');
		$symbolic .= (($perms & 0x0010) ? 'w' : '-');
		$symbolic .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
		
		// World
		$symbolic .= (($perms & 0x0004) ? 'r' : '-');
		$symbolic .= (($perms & 0x0002) ? 'w' : '-');
		$symbolic .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
		
		return $symbolic;
	}
	/**
	 * 附件下载
	 */
	static function file_down($file_name, $file_path) {
		keke_lang_class::load_lang_class ( 'keke_file_class' );
		global $_lang;
		$filename = S_ROOT . $file_path;
		if (! file_exists ( $filename ) || strrpos ( $filename, ".php" ) !== false) {
			kekezu::show_msg ( $_lang ['file_not_exist'], $_SERVER ['HTTP_REFERER'], "3" );
		}
		
		$downfilename = str_replace ( ' ', '%20', $file_name );
		
		Header ( "Content-type: application/octet-stream" );
		Header ( "Accept-Ranges: bytes" );
		Header ( "Accept-Length: " . filesize ( $filename ) );
		Header ( "Content-Disposition: attachment; filename=" . $downfilename );
		$file = fopen ( $filename, "r" );
		echo fread ( $file, filesize ( $filename ) );
		fclose ( $file );
		die ();
	
	}
	/**
	 *
	 *
	 * 获取文件夹大小
	 * 
	 * @param unknown_type $dir        	
	 */
	function getdirsize($dir) {
		$handle = opendir ( $dir );
		while ( false !== ($folderorfile = readdir ( $handle )) ) {
			if ($folderorfile != "." && $folderorfile != "..") {
				if (is_dir ( "$dir/$folderorfile" )) {
					$sizeresult += $this->getdirsize ( "$dir/$folderorfile" );
				} else {
					$sizeresult += filesize ( "$dir/$folderorfile" );
				}
			}
		}
		closedir ( $handle );
		return $sizeresult;
	}
	/**
	 * 上传文件删除
	 *
	 * @param $filepath 文件路径        	
	 */
	public static function del_file($filepath, $is_db = true) {
		
		if ($is_db or db_factory::get_count ( sprintf ( " select count(file_id) from %switkey_file where save_name='%s'", TABLEPRE, $filepath ) )) {
			if (is_file ( $filepath )) {
				return unlink ( $filepath );
			} else {
				return false;
			}
		} else {
			return unlink ( $filepath );			
		}
	}
	
	/**
	 * 删除上传的文件
	 * <b>$fid,$filepath这2个参数应当为必须的</b>, 但是考虑到这个函数可能在其他地方用到,为保持兼容,将$filepath改为可选
	 *
	 * @param $fid int        	
	 * @param $filepath string
	 * (只有根据$file_id和$filepath这2个参数才能尽量控制用户删除权限)
	 * @param $del_more string
	 * 删除更多的图片,传图片的size e.g
	 * $del_more='100,200'(分别代表size是100和200的2张图片)
	 */
	static function del_att_file($fid = 0, $filepath = '', $del_more = '') {
		$file_obj = new Keke_witkey_file_class ();
		if ($fid > 0) {
			$where = 'file_id=' . $fid;
			$filepath != '' && $where .= ' and save_name="' . $filepath . '"';
			$file_obj->setWhere ( $where );
			$file_info = $file_obj->query_keke_witkey_file ();
			$file_obj->setWhere ( $where );
			$res = $file_obj->del_keke_witkey_file ();
			$filepath = $file_info [0] ['save_name'];
			if (is_file ( $filepath )) {
				$unlink = unlink ( $filepath );
				if ($del_more != '') {
					$more_name = array ();
					$dirname = dirname ( $filepath );
					$dirname = $dirname . '/';
					$basename = basename ( $filepath );
					$size_arr = explode ( ',', $del_more );
					for($i = 0; $i < sizeof ( $size_arr ); $i ++) {
						unlink ( $dirname . $size_arr [$i] . '_' . $basename );
					}
				}
			}
			return $unlink ? $unlink : $res;
		}
	}
	/**
	 * 删除对象的文件
	 */
	static function del_obj_file($obj_id, $obj_type, $del_more =false) {
		$file_obj = new Keke_witkey_file_class ();
		if ($obj_id && $obj_type) {
			$where = ' obj_id=' . $obj_id . ' and obj_type="' . $obj_type . '"';
			$file_obj->setWhere ( $where );
			$file_info = $file_obj->query_keke_witkey_file ();
			$file_obj->setWhere ( $where );
			$res = $file_obj->del_keke_witkey_file ();
			foreach ( $file_info as $v ) {
				$filepath = $v ['save_name'];
				if (is_file ( $filepath )) {
					$unlink = unlink ( $filepath );
					if ($del_more) {
						$more_name = array ();
						$dirname = dirname ( $filepath );
						$dirname = $dirname . '/';
						$basename = basename ( $filepath );
						$size_arr = explode ( ',', $del_more );
						for($i = 0; $i < sizeof ( $size_arr ); $i ++) {
							unlink ( $dirname . $size_arr [$i] . '_' . $basename );
						}
					}
				}
			}
		}
		return $res;
	}
	/**
	 * 通用文件上传
	 * $folder 自定义上传的文件夹名字 '/data/uploads/sys/'.$folder,这个参数在 后台广告添加页面用到
	 */
	
	public static function upload_file($file_name, $ext = '', $isr = 1, $folder = '', $output = 'normal') {
		global $_lang;
		
		if ($_FILES [$file_name]) {
			$ext == '' && $ext = UPLOAD_ALLOWEXT;
			if ($folder != '') {
				$absolute_path = S_ROOT . '/data/uploads/sys/' . $folder;
				$filepath = 'data/uploads/sys/' . $folder;
			} else {
				$absolute_path = UPLOAD_ROOT;
				$filepath = 'data/uploads/' . UPLOAD_RULE;
			}
			
			$upload_obj = new keke_upload_class ( $absolute_path, explode ( '|', $ext ), UPLOAD_MAXSIZE );
			$files = $upload_obj->run ( $file_name, $isr );
			
			if (! empty ( $files ) && is_array ( $files )) {
				// 获得文件名
				// $ref_name = $files [0] ['name'];
				$file = $files [0] ['saveName'];
				
				return $filepath . $file;
			} else {
				$err = $files;
				switch ($output) {
					case "normal" :
						kekezu::show_msg ( $_lang ['operate_notice'], '', 2, $err, 'warning' );
						break;
					case "json" :
						echo kekezu::json_encode_k ( array ('err' => $err ) );
						die ();
						break;
				}
			}
		}
	}
	/**
	 * 图片输出
	 *
	 * @param $file_path string
	 * ;
	 * @return img binary
	 */
	static function img_output($file_path) {
		header ( "Expires: Sun, 1 Jan 2011 12:00:00 GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . "GMT" );
		header ( "Cache-Control: no-store, no-cache, must-revalidate" );
		header ( "Cache-Control: post-check=0, pre-check=0", false );
		header ( "Pragma: no-cache" );
		header ( "Content-Type: image/jpeg" );
		$img_file = file_get_contents ( $file_path );
		echo $img_file;
	}
	/**
	 * flash代码输出
	 *
	 * @param $src flash
	 * 路径
	 * @param $width 视口宽        	
	 * @param $height 视口高        	
	 */
	static function flash_codeout($src, $width, $height) {
		$str = ' <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
		$str .= ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"';
		$str .= ' width="' . $width . '" height="' . $height . '"/>';
		$str .= ' <param name="movie" value="' . $src . '" />';
		$str .= ' <param name="quality" value="high" />';
		$str .= ' <param name="quality" value="transparent" />';
		$str .= ' <param name="wmode" value="transparent" /> ';
		$str .= ' <param name="SCALE" value="exactfit" />';
		$str .= ' <embed src="' . $src . '" quality="high" width="' . $width . '" height="' . $height . '" ';
		$str .= ' pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"';
		$str .= ' type="application/x-shockwave-flash" wmode="transparent"/></embed></object>';
		return kekezu::k_stripslashes ( $str );
	}

	// function according_size_get_name($filepath,$size=array()){
// if (is_null($size)){
// return false;
// }
// $more_name = array();
// $dirname = dirname($filepath);
// $dirname = $dirname .'/';
// $basename = basename($filepath);
// $size_arr = explode(',', $size);
// for ($i=1;$i<=sizeof($size_arr);$i++){
// $more_name[] = $dirname . $size_arr[$i] . '_' . $basename ;
// }
// return $more_name;
// }
}